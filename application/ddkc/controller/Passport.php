<?php
/*------------------------------------------------------ */
//-- 会员登陆注册
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\ddkc\controller;
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
        /*if (empty($this->userInfo) == false){
            return $this->redirect('mining/center/index');//已登陆，跳回会员中心
        }*/
    }
	/*------------------------------------------------------ */
	//-- 登陆
	/*------------------------------------------------------ */
	public function login(){
        $this->assign('title', '会员登录');
		$this->assign('register_status',settings('register_status'));
        $this->assign('logo',settings('logo'));
        return $this->fetch('login');
	}
	/*------------------------------------------------------ */
    //-- 注册
    /*------------------------------------------------------ */
    public function register(){
		$register_status = settings('register_status');
		if ($register_status != 1){
			return $this->error('暂不开放注册.');
		}
        $appType = session('appType');
        if($appType=='IOS'||$appType=='Android'){
            $is_load = 1;
        }else{
            $is_load = 2;
        }
        $this->assign('is_load',$is_load);
        $this->assign('appType',session('appType'));
        $this->assign('recommend',session('share_token'));
		$this->assign('title', '注册');
        return $this->fetch('register');
    }
    /*------------------------------------------------------ */
    //-- 找回密码
    /*------------------------------------------------------ */
    public function forgetpwd(){
        $this->assign('title', '忘记密码');
        return $this->fetch('forgetpwd');
    }
    /*------------------------------------------------------ */
    //-- 文章列表
    /*------------------------------------------------------ */
    public function article(){
        $this->assign('title', '文章');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 文章详情
    /*------------------------------------------------------ */
    public function article_detail(){
        $this->assign('title', '文章详情');
        return $this->fetch();
    }

    public function account_defrosting(){
        $this->assign('title', '账号解封');
        return $this->fetch();
    }
}?>