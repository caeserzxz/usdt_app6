<?php
namespace app\fightgroup\controller\sys_admin;

use app\shop\controller\sys_admin\Order as mainOrder;

use app\fightgroup\model\FightGroupListModel;

/**
 * 订单相关
 * Class Index
 * @package app\store\controller
 */
class FgOrder extends mainOrder
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->order_type = 'fg_order';
    }
//-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        (new FightGroupListModel)->evalFail();
        return parent::index();
    }
}
