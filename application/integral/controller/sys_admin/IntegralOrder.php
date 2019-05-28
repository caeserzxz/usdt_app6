<?php
namespace app\integral\controller\sys_admin;

use app\shop\controller\sys_admin\Order as mainOrder;


/**
 * 订单相关
 * Class Index
 * @package app\store\controller
 */
class IntegralOrder extends mainOrder
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->order_type = 'integral_order';//积分订单
    }

}
