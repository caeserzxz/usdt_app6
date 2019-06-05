<?php
namespace app\supplyer\controller\sys_admin;

use app\shop\controller\sys_admin\Goods as mainGoods;


/**
 * 商品相关
 * Class Index
 * @package app\store\controller
 */
class Goods extends mainGoods
{
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
    public function initialize(){
   		$this->is_supplyer = true;//此值为真值时为平台端的供货商管理
        $this->supplyer_id = input('supplyer_id','0','intval');
        $this->assign('goods_status',config('config.goods_status'));
        parent::initialize();
    }

    //*------------------------------------------------------ */
    //-- 审核相关列表
    /*------------------------------------------------------ */
    public function wait_check(){
        $this->ext_status = 10;
        $this->getList(true);
        return $this->fetch('index');
    }
}
