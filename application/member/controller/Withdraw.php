<?php
/*------------------------------------------------------ */
//-- 提现相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\member\controller;
use app\ClientbaseController;


class Withdraw  extends ClientbaseController{	
	/*------------------------------------------------------ */
	//-- 提现管理
	/*------------------------------------------------------ */
	public function index(){
	    if (empty($this->userInfo['mobile']) == true){//没有注册手机，须绑定手机后才能操作提现
            return $this->redirect('bindMobile');
        }
        $this->assign('title', '提现管理');
        $this->assign('withdraw_account_type', config('config.withdraw_account_type'));
		return $this->fetch('index');
	}
    /*------------------------------------------------------ */
    //-- 绑定手机
    /*------------------------------------------------------ */
    public function bindMobile(){
        if (empty($this->userInfo['mobile']) == false){
            return $this->redirect('index');
        }
        $this->assign('title', '绑定手机');
        $this->assign('sms_fun', settings('sms_fun'));//获取短信配置
        return $this->fetch('bindMobile');
    }
	/*------------------------------------------------------ */
    //-- 银行卡
    /*------------------------------------------------------ */
    public function bankList(){
        $this->assign('title', '提现方式');
        return $this->fetch('bankList');
    }
	/*------------------------------------------------------ */
    //-- 银行卡
    /*------------------------------------------------------ */
    public function bankAdd(){
        $this->assign('title', '添加银行卡');
        return $this->fetch('bankAdd');
    }
	/*------------------------------------------------------ */
    //-- 添加支付宝
    /*------------------------------------------------------ */
    public function alipayAdd(){
        $this->assign('title', '添加支付宝');
        return $this->fetch('alipayAdd');
    }
   


}?>