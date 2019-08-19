<?php
/*------------------------------------------------------ */
//-- 签到相关
//-- Author: wemk
/*------------------------------------------------------ */
namespace app\member\controller;
use app\ClientbaseController;


class UserSign extends ClientbaseController{	
	/*------------------------------------------------------ */
	//-- 签收页面
	/*------------------------------------------------------ */
	public function index(){
        $this->assign('title', '签到');
		return $this->fetch('index');
	}

}?>