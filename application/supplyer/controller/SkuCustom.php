<?php
namespace app\supplyer\controller;
use app\supplyer\Controller;
use app\shop\controller\sys_admin\SkuCustom as mainSkuCustom;


/**
 * 自定义的商品规格
 * Class Index
 * @package app\store\controller
 */
class SkuCustom extends mainSkuCustom
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize(){
        $this->initialize_isretrun = false;
        parent::initialize();
        $Controller = new Controller();
        $this->supplyer_id = $Controller->supplyer_id;

    }

}
