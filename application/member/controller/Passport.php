<?php
/*------------------------------------------------------ */
//-- 会员登陆注册
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\member\controller;
use app\ClientbaseController;
use think\Db;
use think\facade\Cache;
use think\facade\Session;

class Passport  extends ClientbaseController{
	/*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
		$this->assign('sms_fun', settings('sms_fun'));//获取短信配置
        if (empty($this->userInfo) == false){
            return $this->redirect('member/center/index');//已登陆，跳回会员中心
        }
    }
	/*------------------------------------------------------ */
	//-- 登陆
	/*------------------------------------------------------ */
	public function login(){
        $this->assign('title', '会员登陆');
		return $this->fetch('login');
	}
	/*------------------------------------------------------ */
    //-- 注册
    /*------------------------------------------------------ */
    public function register(){
        $this->assign('title', '会员注册');
        return $this->fetch('register');
    }
    /*------------------------------------------------------ */
    //-- 找回密码
    /*------------------------------------------------------ */
    public function forgetpwd(){
        $this->assign('title', '忘记密码');
        return $this->fetch('forgetpwd');
    }


}?>