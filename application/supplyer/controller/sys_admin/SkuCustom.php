<?php
namespace app\supplyer\controller\sys_admin;

use app\shop\controller\sys_admin\SkuCustom as mainSkuCustom;


/**
 * 自定义的商品规格
 * Class Index
 * @package app\store\controller
 */
class SkuCustom extends mainSkuCustom{
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
