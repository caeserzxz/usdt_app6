<?php
/*------------------------------------------------------ */
//-- 订单相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;
use app\shop\model\OrderModel;



class Order extends ClientbaseController{
	/*------------------------------------------------------ */
	//-- 订单首页
	/*------------------------------------------------------ */
	public function index(){
		$this->assign('title', '我的订单');
        $this->assign('type', input('type','all','trim'));
		return $this->fetch('index');
	}
    /*------------------------------------------------------ */
    //-- 订单详情
    /*------------------------------------------------------ */
    public function info(){
        $this->assign('title', '订单详情');
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $OrderModel = new OrderModel();
        $orderInfo = $OrderModel->info($order_id);
        $this->assign('orderInfo', $orderInfo);
        return $this->fetch('info');
    }
	 /*------------------------------------------------------ */
    //-- 订单详情
    /*------------------------------------------------------ */
    public function shippingInfo(){
        $this->assign('title', '物流信息');
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $this->assign('order_id', $order_id);
        return $this->fetch('shipping_info');
    }
}?>