<?php
/*------------------------------------------------------ */
//-- 提成处理
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\distribution\model;
use app\BaseModel;
use think\facade\Cache;
use app\member\model\UsersModel;
use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;


class DividendModel extends BaseModel {
	protected $table = 'distribution_dividend_log'; 
	public $pk = 'log_id';
	public $UsersModel;
	/*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
		$this->UsersModel = new UsersModel();	
		
    }
	/*------------------------------------------------------ */
	//-- 获取等级提成
	/*------------------------------------------------------ */ 
	public function level($Dividend = array()){
		if (empty($Dividend)) $Dividend = settings('DividendInfo');
		if (empty($Dividend['LevelRow'])) return array();
		return $Dividend['LevelRow'];
	}
	
    /*------------------------------------------------------ */
	//-- 计算提成并记录或更新
	/*------------------------------------------------------ */ 
	public function _eval(&$orderInfo,$type = ''){
		//获取订单中的分销商品
		$where[] = ['order_id','=',$orderInfo['order_id']];
		$where[] = ['is_dividend','=',1];
		$goodsList = (new OrderGoodsModel)->where($where)->select();
		if (empty($goodsList)) return true;//没有分销商品不执行
		$DividendInfo = settings('DividendInfo');
		$upData = [];//更新分佣记录状态
		$OrderModel = new OrderModel();
		//先计算佣金再执行升级处理
		if ($type == 'add'){//写入分佣		
			if ($DividendInfo['bind_type'] == 1 && $orderInfo['pay_status'] == $OrderModel->config['PS_PAYED']){//支付成功时绑定关系
				$this->UsersModel->regUserBind($orderInfo['user_id']);
			}
			$log = $this->saveLog($orderInfo,$goodsList);//佣金计算				
			if ($orderInfo['pay_status'] == 1){//如果订单状态已支付时调用				
				$res = $this->evalLevelUp($orderInfo,$goodsList,$orderInfo['user_id']);
				if ($res == false) return false;
			}			
			return $log;
		}elseif ($type == 'pay'){//订单支付成功
			if ($DividendInfo['bind_type'] == 1){//支付成功时绑定关系
				$this->UsersModel->regUserBind($orderInfo['user_id']);
			}
			$res = $this->evalLevelUp($orderInfo,$goodsList,$orderInfo['user_id']);
			if ($res == false) return false;
			return true;
		}elseif ($type == 'cancel'){//订单取消
			$upData['status'] = $OrderModel->config['DD_CANCELED'];
		}elseif ($type == 'shipping'){//发货
			$upData['status'] = $OrderModel->config['DD_SHIPPED'];
		}elseif ($type == 'unshipping'){//未发货
			$upData['status'] = $OrderModel->config['DD_UNCONFIRMED'];
		}elseif ($type == 'sign'){//签收
			$upData['status'] = $OrderModel->config['DD_SIGN'];
		}elseif ($type == 'unsign'){//撤销签收
			$upData['status'] = $OrderModel->config['DD_SHIPPED'];
		}elseif ($type == 'returned'){//退货
			$upData['status'] = $OrderModel->config['DD_RETURNED'];
		}		
		if (empty($upData) == false){//更新分佣状态
			$upData['update_time'] = time();
			$res = $this->where('order_id',$orderInfo['order_id'])->update($upData);
			if ($res < 1) return false;
		}
		return true;
	}
	 /*------------------------------------------------------ */
	//-- 计算提成并记录或更新
	/*------------------------------------------------------ */ 
	public function saveLog(&$orderInfo,&$goodsList){		
		$awardList = (new DividendAwardModel)->select();//获取全部奖项目
		if (empty($awardList)) return false;
		$dividend_amount = 0;//共分出去的佣金
			
		$parentId = $this->UsersModel->where('user_id',$orderInfo['user_id'])->value('pid');//获取购买会员直属上级ID
		if ($parentId < 1)  return ['dividend_amount'=>$dividend_amount];
		$order_goods_ids = [];
		$order_goods_num = 0;
		foreach ($goodsList as $goods){
			$order_goods_ids[] = $goods['goods_id'];
			$order_goods_num += $goods['goods_number'];
		}
		
		$DividendInfo = settings('DividendInfo');
		$OrderModel = new OrderModel();
		$OrderGoodsModel = new OrderGoodsModel();
		$DividendRoleModel = new DividendRoleModel();
		
		$nowLevel = 0;//当前处理级别
		$assignAwardNum = [];//记录已分出去的管理奖金额
		$roleInfo = $DividendRoleModel->info($orderInfo['role_id']);//获取用户下单时分销身份
		$lastRole = $roleInfo['level'];//下级会员分佣身份级别
		
		
		do {
			$nowLevel += 1;
			$userInfo = $this->UsersModel->info($parentId);//获取会员信息
			$parentId = $userInfo['pid'];//优先记录下次循环用户ID
			if ($userInfo['role_id'] < 1){				
				continue;//无身份，跳出
			} 
			foreach ($awardList as $key=>$award){				
				//判断身份是否满足条件
				$award['limit_role'] = explode(',',$award['limit_role']);
				if (in_array($userInfo['role_id'],$award['limit_role']) == false){
					if($award['award_type'] == 2){//平推奖，如果当前用户不满足条件，此奖项终止
						unset($awardList[$key]);//移除已结束的奖项
					}
					continue;
				}
				
				$awardValue = json_decode($award['award_value'],true);	//奖项内容	
				$endAward = end($awardValue);//获取最后奖项		
				
				
				if($award['award_type'] == 3){//判断管理奖是否享受					
					if (empty($awardValue[$userInfo['role_id']])){//没有找到相应奖项级别跳出
						continue;
					}
					if (isset($assignAwardNum[$award['award_id']]) == false){//未定义附值为0
						$assignAwardNum[$award['award_id']] = 0;
					}
					if ($assignAwardNum[$award['award_id']] >= $endAward['num']){
						unset($awardList[$key]);//管理奖已达最大分配值，终止，跳出
						continue;
					}					
					$awardVal = $awardValue[$userInfo['role_id']];//获取对应角色奖项					
					$roleInfo = $DividendRoleModel->info($userInfo['role_id']);//获取当前用户分销身份
					if ($roleInfo['level'] <= $lastRole){//上级低于下级或平级时跳出，同时执行模板消息
						//执行模板通知
						continue;
					}					
					$lastRole = $roleInfo['level'];					
					$award_num = $awardVal['num'] - $assignAwardNum[$award['award_id']];//计算当前可分值
					if ($award_num <= 0){//已分完终止
						unset($awardList[$key]);//移除已结束的奖项
						continue;
					}
				}else{		
					if (empty($awardValue[$nowLevel])){//没有找到相应奖项级别跳出，并移除奖项
						unset($awardList[$key]);//移除奖项
						continue;
					}
					$awardVal = $awardValue[$nowLevel];				
				}
				
				if ($award['goods_limit'] == 2){//购买全部指定分销商品
					$award_limit_buy_goods = explode(',',$award['buy_goods_id']);
					$isOk = true;
					foreach ($award_limit_buy_goods as $goods_id){						
						if (in_array($goods_id,$order_goods_ids) == false){//限制商品不存在购买中，失败跳出						
							$isOk =false;
							continue;
						}
					}
					if ($isOk == false){//不满足购买限制，跳出					
						continue;
					}				
					$goods_num = count($buy_goods_id) * $award['goods_limit_num'];
					if ($order_goods_num < $goods_num){
						continue;
					}
				}elseif ($award['goods_limit'] == 3){//购买任意指定分销商品
					$award_limit_buy_goods= explode(',',$award['buy_goods_id']);
					$isOk = false;
					foreach ($award_limit_buy_goods as $goods_id){
						if (in_array($goods_id,$order_goods_ids) == false){//限制商品存在购买中，成功跳出
							$isOk = true;
							continue;
						}
					}
					if ($isOk == false){//不满足购买限制，跳出
						continue;
					}
					if ($order_goods_num < $award['goods_limit_num']){
						continue;
					}
				}
				
				if ($award['award_type'] > 1 && empty($award['repeat_goods_id']) == false){//限制复购判断
					$repeat_buy_day = time() - ($DividendInfo['repeat_buy_day'] * 86400);
					$where = [];
					$where[] = ['o.user_id','=',$userInfo['user_id']];
					$where[] = ['o.add_time','>',$repeat_buy_day];
					$where[] = ['o.order_status','=',$OrderModel->config['OS_CONFIRMED']];
					$count = $OrderGoodsModel->alias('o')->join($OrderGoodsModel->table().' og','o.order_id = og.order_id AND og.goods_id IN ('.$award['repeat_goods_id'].')')->where($where)->count();
					if ($count < 1){
						//模板消息通知
						 continue;//不满足复购限制，跳出
					}
				}
				
				
				//满足条件执行奖项处理		
				$inArr = [];			
				if (in_array($award['award_type'],[1,2])){//普通分销奖&平推奖
					$dividend_amount += $awardVal['num'];//计算总佣金	
					if ($awardVal['type'] == 'gold'){
						$inArr['dividend_amount'] = $awardVal['num'];
					}else{
						$inArr['dividend_bean'] = $awardVal['num'];
					}				
				}elseif($award['award_type'] == 3){//管理奖				
					$assignAwardNum[$award['award_id']] += $award_num;
					$dividend_amount += $award_num;//计算总佣金
					if ($awardVal['type'] == 'gold'){
						$inArr['dividend_amount'] = $award_num;
					}else{
						$inArr['dividend_bean'] = $award_num;
					}
				}
				$inArr['order_id'] = $orderInfo['order_id'];
				$inArr['order_sn'] = $orderInfo['order_sn'];
				$inArr['buy_uid']  = $orderInfo['user_id'];
				$inArr['order_amount'] = $orderInfo['order_amount'];
				$inArr['dividend_uid'] = $userInfo['user_id'];
				$inArr['role_id']      = $userInfo['role_id'];
				$inArr['role_name']    = $DividendRoleModel->info($userInfo['role_id'],true);
				$inArr['level']        = $nowLevel;
				$inArr['award_id']     = $award['award_id'];
				$inArr['award_name']   = $award['award_name'];
				$inArr['level_award_name']   = $awardVal['name'];	
				$inArr['add_time'] = $inArr['update_time'] = time();
				$res = $this->save($inArr);
				if ($res < 1) return false;
				//执行模板消息通知
			}
			if (empty($awardList) == true){//没有奖项可分了，终止			
				$parentId = 0;
			}
		}while($parentId > 0);
		return ['dividend_amount'=>$dividend_amount];
	}
	/*------------------------------------------------------ */
	//-- 执行升级方案
	/*------------------------------------------------------ */ 
	public function evalLevelUp(&$orderInfo,&$goodsList,$user_id=0){
		//执行分销身份升级处理
		$roleList = (new DividendRoleModel)->order('level ASC')->select();	
		$LogSysModel = new \app\member\model\LogSysModel();	
		$oldFun = '';	
		do {
			$usersInfo = $this->UsersModel->info($user_id);//获取会员信息
			$userRoleLevel = 0;//初始会员身份等级
			if ($usersInfo['role_id'] > 0){
				$userRoleLevel = $roleList[$usersInfo['role_id']]['level'];//获取当前会员身份等级
			}
			
			foreach ($roleList as $role){
				if ($role['level'] <= $userRoleLevel) continue;//当前分销身份低于用户现身份，跳过
				$role['upleve_value'] = json_decode($role['upleve_value'],true);
				
				$fun = str_replace('/','\\','/distribution/'.$role['upleve_function']);
				if ($oldFun != $fun){
					$oldFun = $fun;
					$Class = new $fun();
				}
				
				$res = $Class->judgeIsUp($user_id,$role,$orderInfo,$goodsList);//判断是否能升级
				if ($res == false){//当前会员不执行升级，终止					
					return true;
				}
				break;//跳出循环进行升级操作
			}			
			$upData['last_up_role_time'] = time();
			$upData['role_id'] = $role['role_id'];
			$res = $this->UsersModel->upInfo($user_id,$upData);
			if ($res < 1){
				return false;
			}
			$inData['edit_id'] = $user_id;
			$inData['log_info'] = '【'.($usersInfo['role_id']==0?'粉丝':$roleList[$usersInfo['role_id']]['role_name']).'】升级为【'.$role['role_name'].'】';
			$inData['module'] = request()->path();
			$inData['log_ip'] = request()->ip();
			$inData['log_time'] = time();
			$inData['user_id'] = 0;
			$inData['data'] = base64_encode(serialize($upData));
			$LogSysModel->save($inData);
			$user_id = $usersInfo['pid'];		
		}while($user_id > 0);		
		return true;
	}
	
}?>
