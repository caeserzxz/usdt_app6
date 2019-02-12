<?php
/*------------------------------------------------------ */
//-- 商品收藏相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;



class Collect extends ClientbaseController{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
		$this->assign('title', '我的收藏');     
		return $this->fetch('index');
	}
   
}?>