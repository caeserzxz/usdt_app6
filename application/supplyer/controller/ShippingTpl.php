<?php
namespace app\supplyer\controller;
use app\supplyer\Controller;
use app\shop\controller\sys_admin\ShippingTpl as mainShippingTpl;


/**
 * 运费模板管理
 * Class Index
 * @package app\store\controller
 */
class ShippingTpl extends mainShippingTpl
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
