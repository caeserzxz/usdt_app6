<?php
/*------------------------------------------------------ */
//-- 下单相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;
use app\shop\model\OrderModel;

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
        return $this->fetch('check_out');
    }
    /*------------------------------------------------------ */
    //-- 下单完成
    /*------------------------------------------------------ */
    public function done(){
        $order_id = input('order_id',0,'intval');
        $this->assign('title', '订单支付');
        $OrderModel = new OrderModel();
        $orderInfo = $OrderModel->info($order_id);
        if (empty($orderInfo) || $orderInfo['user_id'] != $this->userInfo['user_id']){
            $this->error('订单不存在.');
        }
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('done');
    }
}?>