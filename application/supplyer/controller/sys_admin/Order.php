<?php
namespace app\supplyer\controller\sys_admin;

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
        parent::initialize();
        $this->is_supplyer = true;
        $this->supplyer_id = input('supplyer_id','0','intval');

    }

}
