<?php
/*------------------------------------------------------ */
//-- 提成处理
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\distribution\model;
use think\Db;

use app\BaseModel;
use app\member\model\UsersModel;
use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;
use app\member\model\AccountLogModel;

use app\weixin\model\WeiXinMsgTplModel;
use app\weixin\model\WeiXinUsersModel;

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
        $send_msg = false;
		$order_operating = '';
		//先计算佣金再执行升级处理
		if ($type == 'add'){//写入分佣		
			if ($DividendInfo['bind_type'] == 1 && $orderInfo['pay_status'] == $OrderModel->config['PS_PAYED']){//支付成功时绑定关系
				$this->UsersModel->regUserBind($orderInfo['user_id']);
			}
			$log = $this->saveLog($orderInfo,$goodsList);//佣金计算				
			if ($orderInfo['pay_status'] == $OrderModel->config['PS_PAYED']){//如果订单状态已支付时调用		
				
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
		}elseif ($type == 'cancel'){//订单取消
            $send_msg = true;
            $order_operating = '订单取消';
			$upData['status'] = $OrderModel->config['DD_CANCELED'];
		}elseif ($type == 'shipping'){//发货
			$upData['status'] = $OrderModel->config['DD_SHIPPED'];
		}elseif ($type == 'unshipping'){//未发货
			$upData['status'] = $OrderModel->config['DD_UNCONFIRMED'];
		}elseif ($type == 'sign'){//签收
			$upData['status'] = $OrderModel->config['DD_SIGN'];			
		}elseif ($type == 'unsign'){//撤销签收
			return $this->returnArrival($orderInfo['order_id'],'unsign',$orderInfo['user_id']);
		}elseif ($type == 'returned'){//退货
			return $this->returnArrival($orderInfo['order_id'],'returned',$orderInfo['user_id']);
		}
		if (empty($upData) == false){//更新分佣状态
			$count = $this->where('order_id',$orderInfo['order_id'])->count();
			if ($count < 1) return true;//如果没有佣金记录不执行
			
			$upData['update_time'] = time();
			$res = $this->where('order_id',$orderInfo['order_id'])->update($upData);

			if ($res < 1) return false;
            if ($send_msg == true){//发送模板消息
                $WeiXinUsersModel = new WeiXinUsersModel();
                $WeiXinMsgTplModel = new WeiXinMsgTplModel();
                $buy_nick_name = $this->UsersModel->where('user_id',$orderInfo['user_id'])->value('nick_name');//获取购买会员昵称
				$rows = $this->where('order_id',$orderInfo['order_id'])->select()->toArray();
				$buy_goods_name = [];
				foreach ($goodsList as $goods){
                    $buy_goods_name[] = $goods['goods_name'];
				}
                $buy_goods_name = join('，',$buy_goods_name);
				foreach ($rows as $row){
                    $row['buy_user_id'] = $orderInfo['user_id'];
                    $sendData['order_sn']       = $orderInfo['order_sn'];
                    $sendData['order_amount']   = $orderInfo['order_amount'];
                    if ($type == 'cancel'){
                        $row['send_scene'] = $row['dividend_bean'] > 0 ? 'dividend_bean_cancel_msg' : 'dividend_cancel_msg';
					}
                    $row['buy_nick_name'] = $buy_nick_name;
                    $row['order_operating'] = $order_operating;
                    $wxInfo = $WeiXinUsersModel->where('user_id', $row['dividend_uid'])->field('wx_openid,wx_nickname')->find();
                    $row['openid'] = $wxInfo['wx_openid'];
                    $row['send_nick_name'] = $wxInfo['wx_nickname'];
                    $row['buy_goods_name'] = $buy_goods_name;
                    $WeiXinMsgTplModel->send($row);//模板消息通知
				}
            }
		}
		if ($type == 'sign'){//签收
			$shop_after_sale_limit = settings('shop_after_sale_limit');
			if ($shop_after_sale_limit == 0){
				return $this->evalArrival($orderInfo['order_id']);
			}
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
		$goods_buy_num = [];//购买商品的数量
        $buy_goods_name = [];
		foreach ($goodsList as $goods){
			$order_goods_ids[] = $goods['goods_id'];
			$order_goods_num += $goods['goods_number'];
			$goods_buy_num[$goods['goods_id']] = $goods['goods_number'];
            $buy_goods_name[] = $goods['goods_name'];
		}
        $buy_goods_name = join('，',$buy_goods_name);
		$DividendInfo = settings('DividendInfo');
		$OrderModel = new OrderModel();
		$OrderGoodsModel = new OrderGoodsModel();
		$DividendRoleModel = new DividendRoleModel();
        $WeiXinUsersModel = new WeiXinUsersModel();
        $WeiXinMsgTplModel = new WeiXinMsgTplModel();
		$nowLevel = 0;//当前处理级别
        $nowLevelOrdinary = 0;//普通分销当前处理级别，普通分销有逐级计算和无限级计算，如果无限级，不满条件将一直最后的上级
		$assignAwardNum = [];//记录已分出去的管理奖金额
		$roleInfo = $DividendRoleModel->info($orderInfo['role_id']);//获取用户下单时分销身份
		$lastRole = $roleInfo['level'];//下级会员分佣身份级别
        $buyUserInfo = $this->UsersModel->info($orderInfo['user_id']);//获取购买会员信息
		$this->where('order_id',$orderInfo['order_id'])->delete();//清理旧的提成记录，重新计算

		do {
			$nowLevel += 1;
            $nowLevelOrdinary += 1;
			$userInfo = $this->UsersModel->info($parentId);//获取会员信息
			
			$parentId = $userInfo['pid'];//优先记录下次循环用户ID
			if ($userInfo['role_id'] < 1){				
				continue;//无身份，跳出
			} 
			
			foreach ($awardList as $key=>$award){
                $awardValue = json_decode($award['award_value'],true);	//奖项内容
                $awardBuyNum = 0;//订购限制商品数量
                if ($award['goods_limit'] == 2){//购买全部指定分销商品
                    $award_limit_buy_goods = explode(',',$award['buy_goods_id']);
                    $isOk = true;
                    foreach ($award_limit_buy_goods as $goods_id){
                        if (in_array($goods_id,$order_goods_ids) == false){//限制商品不存在购买中，失败跳过
                            $isOk =false;
                            continue;
                        }
                        $awardBuyNum += $goods_buy_num[$goods_id];
                    }
                    if ($isOk == false){//不满足购买限制，跳出
                        continue;
                    }
                    $goods_num = count($order_goods_ids) * $award['goods_limit_num'];
                    if ($order_goods_num < $goods_num){
                        continue;
                    }
                }elseif ($award['goods_limit'] == 3){//购买任意指定分销商品
                    $award_limit_buy_goods= explode(',',$award['buy_goods_id']);
                    $isOk = false;
                    foreach ($award_limit_buy_goods as $goods_id){
                        if (in_array($goods_id,$order_goods_ids) == true){//限制商品存在购买中，成功跳出
                            $isOk = true;
                            $awardBuyNum += $goods_buy_num[$goods_id];
                        }
                    }
                    if ($isOk == false){//不满足购买限制，跳出
                        continue;
                    }
                    if ($order_goods_num < $award['goods_limit_num']){
                        continue;
                    }
                }else{//购买任意分销商品
                    $awardBuyNum = $order_goods_num;
                }

				//判断身份是否满足条件
				$limit_role = explode(',',$award['limit_role']);
                $role_name = $DividendRoleModel->info($userInfo['role_id'],true);
				if (in_array($userInfo['role_id'],$limit_role) == false){
                    $sendData['dividend_amount'] = $sendData['dividend_bean'] = 0;
					if($award['award_type'] == 2){//平推奖，如果当前用户不满足条件，此奖项终止
                        $awardVal = $awardValue[$nowLevel];
						unset($awardList[$key]);//移除已结束的奖项
					}elseif($award['award_type'] == 1 && $award['ordinary_type'] == 1) {//普通分销奖，无限级计算时执行
						if ($nowLevel <=3 ){
							$awardVal = $awardValue[$nowLevelOrdinary];
                        }
                        $nowLevelOrdinary -= 1;
                    }
                    if ($awardVal['type'] == 'gold'){
                        $sendData['dividend_amount'] = $awardVal['num'] * $awardBuyNum;
                    }else{
                        $sendData['dividend_bean'] = $awardVal['num'] * $awardBuyNum;
                    }
                    $sendData['buy_user_id']        = $orderInfo['user_id'];
                    $sendData['buy_nick_name']      = $buyUserInfo['nick_name'];
                    $sendData['award_name']         = $award['award_name'];
                    $sendData['level_award_name']   = $awardVal['name'];
                    $sendData['level']              = $nowLevel;
                    $sendData['role_name']          = $role_name;
                    $sendData['order_sn']       = $orderInfo['order_sn'];
                    $sendData['order_amount']   = $orderInfo['order_amount'];
                    $sendData['buy_goods_name']   = $buy_goods_name;
                    $sendData['add_time']           = $orderInfo['add_time'];
                    $sendData['send_nick_name']      = $userInfo['nick_name'];
                    $sendData['send_scene']         = $sendData['dividend_bean'] > 0 ? 'dividend_bean_loss_role_msg' : 'dividend_loss_role_msg';//佣金损失通知
                    $wxInfo = $WeiXinUsersModel->where('user_id', $userInfo['user_id'])->field('wx_openid,wx_nickname')->find();
                    $sendData['openid'] = $wxInfo['wx_openid'];
                    $sendData['send_nick_name'] = $wxInfo['wx_nickname'];
                    $WeiXinMsgTplModel->send($sendData);//模板消息通知
                    continue;
				}

				if($award['award_type'] == 3){//判断管理奖是否享受
					if (empty($awardValue[$userInfo['role_id']])){//没有找到相应奖项级别跳出
						continue;
					}
					if (isset($assignAwardNum[$award['award_id']]) == false){//未定义附值为0
						$assignAwardNum[$award['award_id']] = 0;
					}
                    $endAward = end($awardValue);//获取最后奖项
					if ($assignAwardNum[$award['award_id']] >= $endAward['num']){
						unset($awardList[$key]);//管理奖已达最大分配值，终止，跳出
						continue;
					}
					$awardVal = $awardValue[$userInfo['role_id']];//获取对应角色奖项					
					$roleInfo = $DividendRoleModel->info($userInfo['role_id']);//获取当前用户分销身份
					if ($roleInfo['level'] <= $lastRole){//上级低于下级或平级时跳出
						continue;
					}					
					$lastRole = $roleInfo['level'];					
					$award_num = $awardVal['num'] - $assignAwardNum[$award['award_id']];//计算当前可分值
					if ($award_num <= 0){//已分完终止
						unset($awardList[$key]);//移除已结束的奖项
						continue;
					}
				}else{
					if($award['award_type'] == 1 && $award['ordinary_type'] == 1){//普通分销，无限级计算时，会员判断级别方式不一样
                        if (empty($awardValue[$nowLevelOrdinary])){//没有找到相应奖项级别跳出，并移除奖项
                            unset($awardList[$key]);//移除奖项
                            continue;
                        }
                        $awardVal = $awardValue[$nowLevelOrdinary];
                    }else{
						if (empty($awardValue[$nowLevel])){//没有找到相应奖项级别跳出，并移除奖项
							unset($awardList[$key]);//移除奖项
							continue;
						}
                        $awardVal = $awardValue[$nowLevel];
                    }
				}

				if (empty($award['repeat_goods_id']) == false){//限制复购判断
					$repeat_buy_day = time() - ($DividendInfo['repeat_buy_day'] * 86400);
					$where = [];
					$where[] = ['o.user_id','=',$userInfo['user_id']];
                    $where[] = ['o.add_time','<',$orderInfo['add_time']];
					$where[] = ['o.add_time','>',$repeat_buy_day];
					$where[] = ['o.order_status','=',$OrderModel->config['OS_CONFIRMED']];
					$count = $OrderModel->alias('o')->join($OrderGoodsModel->table().' og','o.order_id = og.order_id AND og.goods_id IN ('.$award['repeat_goods_id'].')')->where($where)->count();
					if ($count < 1){
                        $sendData['dividend_amount'] = $sendData['dividend_bean'] = 0;
                        if (in_array($award['award_type'],[1,2])){//普通分销奖&平推奖
                            if ($awardVal['type'] == 'gold'){
                                $sendData['dividend_amount'] = $awardVal['num'] * $awardBuyNum;
                            }else{
                                $sendData['dividend_bean'] = $awardVal['num'] * $awardBuyNum;
                            }
                        }elseif($award['award_type'] == 3){//管理奖
                            if ($awardVal['type'] == 'gold'){
                                $sendData['dividend_amount'] = $award_num * $awardBuyNum;
                            }else{
                                $sendData['dividend_bean'] = $award_num * $awardBuyNum;
                            }
                        }
                        $sendData['buy_nick_name']      = $buyUserInfo['nick_name'];
                        $sendData['buy_user_id']       = $orderInfo['user_id'];
                        $sendData['award_name']         = $award['award_name'];
                        $sendData['level_award_name']   = $awardVal['name'];
                        $sendData['level']              = $nowLevel;
                        $sendData['role_name']          = $role_name;
                        $sendData['order_sn']       = $orderInfo['order_sn'];
                        $sendData['order_amount']   = $orderInfo['order_amount'];
                        $sendData['order_amount']       = $orderInfo['order_amount'];
                        $sendData['buy_goods_name']   = $buy_goods_name;
                        $sendData['add_time']           = $orderInfo['add_time'];
                        $sendData['send_nick_name']      = $userInfo['nick_name'];
                        $sendData['send_scene']         = $sendData['dividend_bean'] > 0 ? 'dividend_bean_loss_buy_msg' : 'dividend_loss_buy_msg';//佣金损失通知
                        $wxInfo = $WeiXinUsersModel->where('user_id', $userInfo['user_id'])->field('wx_openid,wx_nickname')->find();
                        $sendData['openid'] = $wxInfo['wx_openid'];
                        $sendData['send_nick_name'] = $wxInfo['wx_nickname'];
                        $WeiXinMsgTplModel->send($sendData);//模板消息通知
						 continue;//不满足复购限制，跳出
					}
				}

				//执行奖项处理
                $inArr = [];
                if (in_array($award['award_type'],[1,2])){//普通分销奖&平推奖
                    $dividend_amount += $awardVal['num'] * $awardBuyNum;//计算总佣金
                    if ($awardVal['type'] == 'gold'){
                        $inArr['dividend_amount'] = $awardVal['num'] * $awardBuyNum;
                    }else{
                        $inArr['dividend_bean'] = $awardVal['num'] * $awardBuyNum;
                    }
                }elseif($award['award_type'] == 3){//管理奖
                    $assignAwardNum[$award['award_id']] += $award_num;
                    $dividend_amount += $award_num * $awardBuyNum;//计算总佣金
                    $inArr['dividend_amount'] = $inArr['dividend_bean'] = 0;
                    if ($awardVal['type'] == 'gold'){
                        $inArr['dividend_amount'] = $award_num * $awardBuyNum;
                    }else{
                        $inArr['dividend_bean'] = $award_num * $awardBuyNum;
                    }
                }

				$inArr['order_id'] = $orderInfo['order_id'];
				$inArr['order_sn'] = $orderInfo['order_sn'];
				$inArr['buy_uid']  = $orderInfo['user_id'];
				$inArr['order_amount'] = $orderInfo['order_amount'];
				$inArr['dividend_uid'] = $userInfo['user_id'];
				$inArr['role_id']      = $userInfo['role_id'];
				$inArr['role_name']    = $role_name;
				$inArr['level']        = $nowLevel;
				$inArr['award_id']     = $award['award_id'];
				$inArr['award_name']   = $award['award_name'];
				$inArr['level_award_name']   = $awardVal['name'];	
				$inArr['add_time'] = $inArr['update_time'] = time();
				$res = self::create($inArr);
				if ($res < 1) return false;
				//执行模板消息通知
                $inArr['buy_goods_name']   = $buy_goods_name;
                $inArr['send_scene']  = $inArr['dividend_bean'] > 0 ? 'dividend_bean_add_smg':'dividend_add_msg';//佣金产生通知
                $inArr['buy_user_id'] = $orderInfo['user_id'];
                $inArr['buy_nick_name']      = $buyUserInfo['nick_name'];
                $wxInfo = $WeiXinUsersModel->where('user_id', $userInfo['user_id'])->field('wx_openid,wx_nickname')->find();
                $inArr['openid'] = $wxInfo['wx_openid'];
                $inArr['send_nick_name'] = $wxInfo['wx_nickname'];
                $WeiXinMsgTplModel->send($inArr);//模板消息通知
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
		$roleList = (new DividendRoleModel)->getRows();	
		$LogSysModel = new \app\member\model\LogSysModel();	
		$oldFun = '';
        $DividendInfo = settings('DividendInfo');

        $userRoleLevel = 0;//初始会员身份等级
		do {
			$usersInfo = $this->UsersModel->info($user_id);//获取会员信息
            if ($usersInfo['role_id'] > 0){
                $userRoleLevel = $roleList[$usersInfo['role_id']]['level'];//获取当前会员身份等级
            }
            $upRole = [];
			foreach ($roleList as $role){
                if ($DividendInfo['level_up_type'] == 0){//逐级升时调用
					if ($role['level'] != $userRoleLevel + 1){//身份层级不等于下级级别时，跳过
                        continue;
					}
                }elseif ($role['level'] <= $userRoleLevel ){//当前分销身份低于等于用户现身份，跳过
                    continue;
                }

				$role['upleve_value'] = json_decode($role['upleve_value'],true);
				
				$fun = str_replace('/','\\','/distribution/'.$role['upleve_function']);
				if ($oldFun != $fun){
					$oldFun = $fun;
					$Class = new $fun();
				}
				
				$res = $Class->judgeIsUp($user_id,$role,$orderInfo,$goodsList);//判断是否能升级

				if ($res == false){//当前会员不执行升级，终止
                    continue;//可跨级升级时调用
				}
                $upRole = $role;
				if ($DividendInfo['level_up_type'] == 0){//逐级升时调用
                    break;//跳出循环进行升级操作
				}

			}
			if (empty($upRole) == true){
                break;//没有找到可升级的身份终止
			}
			$upData['last_up_role_time'] = time();
			$upData['role_id'] = $upRole['role_id'];
			$res = $this->UsersModel->upInfo($user_id,$upData);
			if ($res < 1){
				return false;
			}
			$inData['edit_id'] = $user_id;
			$inData['log_info'] = '【'.($usersInfo['role_id']==0?'粉丝':$roleList[$usersInfo['role_id']]['role_name']).'】升级为【'.$upRole['role_name'].'】';
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
	/*------------------------------------------------------ */
	//-- 执行分佣到帐
	//-- order_id int 订单ID
	//-- limit_id int 间隔返佣的记录ID，针对旅游豆
	/*------------------------------------------------------ */ 
	public function evalArrival($order_id = 0,$limit_id = 0){
		$time = time();		
		$OrderModel = new OrderModel();
		if ($order_id > 0){
			$where[] = ['order_id','=',$order_id];
			$where[] = ['status','=',$OrderModel->config['DD_SIGN']];
			$rows = $this->where($where)->select()->toArray();
		}else{
			$settings = settings();
			$limit_time = $settings['shop_after_sale_limit'] * 86400;			
			$where[] = ['status','=',$OrderModel->config['DD_SIGN']];
			$where[] = ['update_time','<',$time - $limit_time];
			$rows = $this->where($where)->select()->toArray();
		}
		
		if (empty($rows)) return true;//没有找到相关佣金记录
		
		$AccountLogModel = new AccountLogModel();
		$log_id = [];
		foreach ($rows as $row){			
			if ($limit_id == 0 && $row['dividend_bean'] > 0){				
				continue;
			}
			$changedata['change_desc'] = '订单佣金到帐';
			$changedata['change_type'] = 4;
			$changedata['by_id'] = $row['order_id'];
			$changedata['balance_money'] = $row['dividend_amount'];
			$changedata['bean_value'] = $row['dividend_bean'];
			$changedata['total_dividend'] = ($row['dividend_amount'] + $row['dividend_bean']);
			$res = $AccountLogModel->change($changedata, $row['dividend_uid'], false);
			if ($res !== true) {
				return false;
			}
			$upDate['status'] = $OrderModel->config['DD_DIVVIDEND'];
			$upDate['limit_id'] = $limit_id;
			$upDate['update_time'] = $time;
			$res = $this->where('log_id',$row['log_id'])->update($upDate);
			if ($res < 1) {
				return false;
			}
            $log_id[] = $row['log_id'];
		}
		return $log_id;
	}
	/*------------------------------------------------------ */
	//-- 退回分佣到帐
	//-- order_id int 订单ID
	/*------------------------------------------------------ */ 
	public function returnArrival($order_id = 0,$type = '',$buy_user_id = 0){
		$time = time();		
		$OrderModel = new OrderModel();
		
		$rows = $this->where('order_id',$order_id)->select()->toArray();
		
		if (empty($rows)) return true;//没有找到相关佣金记录
		
		$AccountLogModel = new AccountLogModel();
        $WeiXinUsersModel = new WeiXinUsersModel();
        $WeiXinMsgTplModel = new WeiXinMsgTplModel();
        $buy_nick_name = $this->UsersModel->where('user_id',$buy_user_id)->value('nick_name');//获取购买会员昵称
		foreach ($rows as $row){
			$upDate['status'] = $type == 'unsign' ? $OrderModel->config['DD_SHIPPED'] : $OrderModel->config['DD_RETURNED'];
			$upDate['limit_id'] = 0;
			$upDate['update_time'] = $time;
			$res = $this->where('log_id',$row['log_id'])->update($upDate);
			if ($res < 1) {
				return false;
			}
			
			if ($row['status'] == $OrderModel->config['DD_DIVVIDEND']){				
				$changedata['change_desc'] = $type == 'unsign' ?'订单撤销签收-退回佣金':'订单退货-退回佣金';
				$changedata['change_type'] = 4;
				$changedata['by_id'] = $row['order_id'];
				$changedata['balance_money'] = $row['dividend_amount'] * -1;
				$changedata['bean_value'] = $row['dividend_bean'] * -1;
				$changedata['total_dividend'] = ($row['dividend_amount'] + $row['dividend_bean']) * -1;
				$res = $AccountLogModel->change($changedata, $row['dividend_uid'], false);
				if ($res !== true) {
					return false;
				}
                //执行模板消息通知
                $row['buy_user_id'] = $buy_user_id;
                $row['buy_nick_name'] = $buy_nick_name;
                $row['order_operating'] = $type == 'unsign' ?'撤销签收':'订单退货';
                $row['send_scene']  = 'dividend_return_msg';//佣金退回通知
                $wxInfo = $WeiXinUsersModel->where('user_id', $row['dividend_uid'])->field('wx_openid,wx_nickname')->find();
                $row['openid'] = $wxInfo['wx_openid'];
                $row['send_nick_name'] = $wxInfo['wx_nickname'];
                $WeiXinMsgTplModel->send($row);//模板消息通知
			}
		}
		return true;
	}
}?>
