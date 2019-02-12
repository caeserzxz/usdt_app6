<?php

namespace app\member\controller\api;

use app\ApiController;

use app\member\model\UsersModel;
use app\shop\model\OrderModel;
use app\shop\model\BonusModel;
/*------------------------------------------------------ */
//-- 会员相关API
/*------------------------------------------------------ */

class Users extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new UsersModel();
    }
    /*------------------------------------------------------ */
    //-- 获取登陆会员信息
    /*------------------------------------------------------ */
    public function getInfo()
    {
        $return['info'] = $this->userInfo;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 用户登陆
    /*------------------------------------------------------ */
    public function login()
    {
        $this->checkPostLimit('login');//验证请求次数
        $this->checkCode('login',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->login(input());
        if ($res < 1) return $this->error($res);
        return $this->success('登陆成功.');
    }

    /*------------------------------------------------------ */
    //-- 注册用户
    /*------------------------------------------------------ */
    public function register()
    {
        $this->checkCode('register',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->register(input());
        if ($res !== true) return $this->error($res);
        return $this->success('注册成功.');
    }
	/*------------------------------------------------------ */
    //-- 找回用户密码
    /*------------------------------------------------------ */
    public function forgetPwd()
    {
        $this->checkCode('forget_password',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->forgetPwd(input(),$this);
        if ($res !== true) return $this->error($res);		
        return $this->success('密码已重置，请用新密码登陆.');
    }
	/*------------------------------------------------------ */
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPwd()
    {
		$this->checkLogin();//验证登陆
        $res = $this->Model->editPwd(input(),$this);
        if ($res !== true) return $this->error($res);		
        return $this->success('密码已重置，请用新密码登陆.');
    }
    /*------------------------------------------------------ */
    //-- 获取会员中心首页所需数据
    /*------------------------------------------------------ */
    public function getCenterInfo()
    {
        $this->checkLogin();//验证登陆
        $OrderModel = new OrderModel();
        $return['orderStats'] =  $OrderModel->userOrderStats($this->userInfo['user_id']);
        $return['userInfo'] = $this->userInfo;
        $BonusModel = new BonusModel();
        $bonus = $BonusModel->getListByUser();
        $return['unusedNum'] = $bonus['unusedNum'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
}
