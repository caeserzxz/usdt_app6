<?php
namespace app\supplyer\controller;
use app\supplyer\Controller;
use app\shop\controller\sys_admin\Order as mainOrder;
use app\shop\model\OrderLogModel;

/**
 * 订单相关
 * Class Index
 * @package app\store\controller
 */
class Order extends mainOrder
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        $this->initialize_isretrun = false;
        parent::initialize();
        $Controller = new Controller();
        $this->supplyer_id = $Controller->supplyer_id;

    }
    /*------------------------------------------------------ */
    //-- 订单详细页
    /*------------------------------------------------------ */
    public function info()
    {
        $order_id = input('order_id', 0, 'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $orderInfo = $this->Model->info($order_id);
        if ($this->supplyer_id > 0) {
            if ($this->supplyer_id != $orderInfo['supplyer_id']) {
                return $this->error("您无权操作此订单.");
            }
        }

        $orderLog = (new OrderLogModel)->where('order_id', $order_id)->order('log_id DESC')->select()->toArray();
        $this->assign("orderLog", $orderLog);
        $this->assign("orderLang", lang('order'));
        $operating = $this->Model->operating($orderInfo,true);//订单操作权限
        $this->assign("operating", $operating);
        $this->assign('orderInfo', $orderInfo);

        return $this->fetch('info');
    }
}
