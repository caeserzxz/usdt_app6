<?php
namespace app\supplyer\controller;
use app\supplyer\Controller;
use app\shop\controller\sys_admin\Order as mainOrder;


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

}
