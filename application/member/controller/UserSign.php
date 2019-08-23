<?php
/*------------------------------------------------------ */
//-- 签到相关
//-- Author: wemk
/*------------------------------------------------------ */
namespace app\member\controller;
use app\ClientbaseController;


class UserSign extends ClientbaseController{	
	/*------------------------------------------------------ */
	//-- 我的钱包
	/*------------------------------------------------------ */
	public function index(){

		if($this->userInfo['user_id']<0){
			$this->error('请先登录！');
		}
		if($this->userInfo['role_id']<1){
			$this->error('请先升级身份！');
		}

        $this->assign('title', '签到');
		return $this->fetch('index');
	}

}?>