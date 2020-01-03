<?php
/*------------------------------------------------------ */
//-- 下单相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;
use app\shop\model\OrderModel;
use app\mainadmin\model\PaymentModel;


class Flow extends ClientbaseController{
	/*------------------------------------------------------ */
	//-- 购物车页
	/*------------------------------------------------------ */
	public function cart(){
		$this->assign('title', '购物车');
		return $this->fetch('index');
	}
    /*------------------------------------------------------ */
    //-- 结算页
    /*------------------------------------------------------ */
    public function checkOut(){
        $this->assign('title', '结算');
		$this->assign('rec_id', input('rec_id','','trim'));
        $this->assign('settings',settings());
        return $this->fetch('check_out');
    }
    /*------------------------------------------------------ */
    //-- 下单完成
    /*------------------------------------------------------ */
    public function done(){
        $order_id = input('order_id',0,'intval');
		$type = input('type','','trim');
        $this->assign('title', '订单支付');
        $OrderModel = new OrderModel();
        $orderInfo = $OrderModel->info($order_id);
        if (empty($orderInfo) || $orderInfo['user_id'] != $this->userInfo['user_id']){
            return $this->error('订单不存在.');
        }
        if ($orderInfo['order_status'] == 2){
            return $this->error('订单已取消.');
        }
		$goPay = 0;
        $payment = (new PaymentModel)->where('pay_id', $orderInfo['pay_id'])->find();
        if ($type == 'add' && $orderInfo['pay_status'] == config('config.PS_UNPAYED')){
            if ($orderInfo['is_pay'] == 1){
                $goPay = 1;
            }
        }
        $this->assign('settings',settings());
        $this->assign('payment', $payment);
		$this->assign('goPay', $goPay);
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('done');
    }
}?>