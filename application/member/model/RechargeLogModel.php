<?php

namespace app\member\model;
use app\BaseModel;
use think\Db;

use app\member\model\AccountLogModel;
//*------------------------------------------------------ */
//-- 充值日志
/*------------------------------------------------------ */
class RechargeLogModel extends BaseModel
{
	protected $table = 'users_recharge_log';
	public  $pk = 'order_id';
    
 	/*------------------------------------------------------ */
    //-- 订单在线支付成功处理
    /*------------------------------------------------------ */
	public function updatePay($upData = []){
		Db::startTrans();//启动事务
		$upData['check_time'] = time();
		$upData['status'] = 9;
		$order_id = $upData['order_id'];
		$user_id = $upData['user_id'];
		unset($upData['log_id'],$upData['user_id']);
		$res = $this->where('order_id',$order_id)->update($upData);
		if ($res < 1){
			Db::rollback();// 回滚事务
			return false;
		}		
		$AccountLogModel = new AccountLogModel();
		$changedata['change_desc'] = '在线充值到帐';
		$changedata['change_type'] = 6;
		$changedata['by_id'] = $order_id;
		$changedata['balance_money'] = $upData['order_amount'];
		$res = $AccountLogModel->change($changedata, $user_id, false);
		if ($res !== true) {
			Db::rollback();// 回滚事务
			return false;
		}
		Db::commit();// 提交事务
		return true;
	}
}
