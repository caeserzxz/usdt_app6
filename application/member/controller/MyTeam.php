<?php
/*------------------------------------------------------ */
//-- 我的团队
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\member\controller;
use app\ClientbaseController;


class MyTeam  extends ClientbaseController{	
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
        $this->assign('title', '我的粉丝');
		return $this->fetch('index');
	}
	
   /*------------------------------------------------------ */
	//-- 详细
	/*------------------------------------------------------ */
	public function info(){
        $this->assign('title', '粉丝详细');
		return $this->fetch('info');
	}


}?>