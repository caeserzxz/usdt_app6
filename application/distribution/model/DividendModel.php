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
    /*------------------------------------------------------ */
	//-- 计算提成并记录或更新
	/*------------------------------------------------------ */ 
	public function _eval(&$orderInfo,$type = ''){
		//获取订单中的分销商品
		$where[] = ['order_id','=',$orderInfo['order_id']];
		$where[] = ['is_dividend','=',1];
		$goodsList = (new OrderGoodsModel)->where($where)->select();
		if (empty($goodsList)) return true;//没有分销商品不执行
		//先计算佣金再执行升级处理
		if ($type == 'add'){//写入分佣
			$log = $this->saveLog($orderInfo,$goodsList);//佣金计算
			if ($orderInfo['pay_status'] == $OrderModel->config['PS_PAYED']){//如果使用余额支付时调用
				$this->evalLevelUp($orderInfo,$goodsList,$orderInfo['user_id']);
			}
			
			return $log;
		}elseif ($type == 'pay'){//订单支付成功
			$this->evalLevelUp($orderInfo,$orderInfo['user_id']);
		}
		
	}
	 /*------------------------------------------------------ */
	//-- 计算提成并记录或更新
	/*------------------------------------------------------ */ 
	public function saveLog(&$orderInfo,&$goodsList){
		$awardList = (new DividendAwardModel)->select();//获取全部奖项目
		if (empty($awardList)) return false;
		$UsersModel = new UsersModel();		
		$parentId = $UsersModel->where('user_id',$orderInfo['user_id'])->value('pid');//获取购买会员直属上级ID
		if ($parentId < 1) return false;
		$buy_goods_ids = [];
		$buy_num = 0;
		foreach ($goodsList as $goods){
			$buy_goods_ids[] = $goods['goods_id'];
			$buy_num += $goods['goods_number'];
		}
		
		$DividendInfo = settings('DividendInfo');
		$OrderModel = new OrderModel();
		$OrderGoodsModel = new OrderGoodsModel();
		$DividendRoleModel = new DividendRoleModel();
		
		$nowLevel = 0;//当前处理级别
		$maxLevelNum = 0;//当前所有奖项中最高级别数	
		$assignedManageAward = [];//记录已分出去的管理奖金额
		$roleInfo = $DividendRoleModel->info($orderInfo['role_id']);//获取用户下单时分销身份
		$roleLevel = $roleInfo['level'];
		$dividend_amount = 0;//共分出去的佣金
		do {
			$nowLevel += 1;
			$userInfo = $UsersModel->info($parentId);//获取会员信息
			$parentId = $userInfo['pid'];
			if ($userInfo['role_id'] < 1){				
				continue;//无身份，跳出
			} 
			foreach ($awardList as $key=>$award){				
				$award_value = json_decode($award['award_value'],true);	//奖项内容			
				//判断身份是否满足条件
				$award['limit_role'] = explode(',',$award['limit_role']);
				if (in_array($userInfo['role_id'],$award['limit_role']) == false){
					if($award['award_type'] == 2){//平推奖，如果当前用户不满足条件，此奖项终止
						unset($awardList[$key]);//移除已结束的奖项
					}
					continue;
				}		
				if($award['award_type'] == 3){//判断管理奖是否享受	
					if (empty($award_value[$userInfo['role_id']])){//没有找到相应奖项级别跳出
						continue;
					}
					$roleInfo = $DividendRoleModel->info($userInfo['role_id']);//获取当前用户分销身份
					if ($roleInfo['level'] <= $roleLevel){//上级低于下级或平级时跳出，同时执行模板消息
						//执行模板通知
						continue;
					}
					$award_value = $award_value[$userInfo['role_id']];
					$roleLevel = $roleInfo['level'];
					if (isset($assignedManageAward[$award['award_id']]) == false){//未定义附值为0
						$assignedManageAward[$award['award_id']] = 0;
					}
					$award_num = $award_value['num'] - $assignedManageAward[$award['award_id']];//计算当前可分值
					if ($award_num <= 0){//已分完终止
						unset($awardList[$key]);//移除已结束的奖项
						continue;
					}
				}else{		
					if (empty($award_value[$nowLevel])){//没有找到相应奖项级别跳出，并移除奖项
						unset($awardList[$key]);//移除奖项
						continue;
					}			
					
					$award_value = $award_value[$nowLevel];					
					$now_award_num = count($award_value);
					if ($now_award_num > $maxLevelNum) $maxLevelNum = $now_award_num;//附值当前奖项级别数为最大值
				}
				
				if ($award['goods_limit'] == 2){//购买全部指定分销商品
					$limit_buy_goods_id = explode(',',$award['buy_goods_id']);
					foreach ($limit_buy_goods_id as $goods_id){
						if (in_array($goods_id,$buy_goods_ids) == false){//限制商品不存在购买中，跳出
							continue;
						}
					}					
					$goods_num = count($buy_goods_id) * $award['goods_limit_num'];
					if ($buy_num < $goods_num){
						continue;
					}
				}elseif ($award['goods_limit'] == 3){//购买任意指定分销商品
					$limit_buy_goods_id = explode(',',$award['buy_goods_id']);
					$isOk = false;
					foreach ($limit_buy_goods_id as $goods_id){
						if (in_array($goods_id,$buy_goods_ids) == false){//限制商品存在购买中，跳出
							$isOk = true;
							continue;
						}
					}
					if ($isOk == false){//不满足购买限制，跳出
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
					if ($count < 1) continue;//不满足复购限制，跳出
				}
								
				$inArr = [];				
				//满足条件执行奖项处理
				if (in_array($award['award_type'],[1,2])){//普通分销奖&平推奖
					$dividend_amount += $award_value['num'];		
					if ($award_value['type'] == 'gold'){
						$inArr['dividend_amount'] = $award_value['num'];
					}else{
						$inArr['dividend_bean'] = $award_value['num'];
					}				
				}elseif($award['award_type'] == 3){//管理奖				
					$assignedManageAward[$award['award_id']] += $award_num;
					$dividend_amount += $award_num;
					if ($award_value['type'] == 'gold'){
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
				$inArr['level_award_name']   = $award_value['name'];	
				$inArr['add_time'] = $inArr['update_time'] = time();
				$res = $this->save($inArr);
				if ($res < 1) return false;
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
		$roleList = (new DividendRoleModel)->select();
		$oldFun = '';
		foreach ($roleList as $role){
			$fun = str_replace('/','\\','/distribution/'.$role['upleve_function']);
			if ($oldFun != $fun){
				$Class = new $fun();
				$oldFun = $fun;
			}
			$Class->evalUp($orderInfo,$role);//执行升级处理
		}
	}
	
}?>
