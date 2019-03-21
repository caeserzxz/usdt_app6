<?php
/*------------------------------------------------------ */
//-- 会员主页
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\member\controller;
use app\ClientbaseController;

use app\member\model\UsersModel;

class Center  extends ClientbaseController{
  
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
        $this->assign('title', '会员中心');
       $this->assign('isUserIndex', 1);
		return $this->fetch('index');
	}
	/*------------------------------------------------------ */
    //-- 我的分享二维码
    /*------------------------------------------------------ */
    public function myCode(){
		$DividendShareByRole = settings('DividendShareByRole');
		if ($DividendShareByRole == 1 && $this->userInfo['role_id'] < 1){
			return $this->error('请升级身份后再操作.');
		}
        $this->assign('title', '我的二维码');
        return $this->fetch('my_code');
    }
    /*------------------------------------------------------ */
    //-- 会员收货地址页
    /*------------------------------------------------------ */
    public function address(){
        $this->assign('title', '收货地址');
        return $this->fetch('address/index');
    }
    /*------------------------------------------------------ */
    //-- 会员优惠券页
    /*------------------------------------------------------ */
    public function bonus(){
        $this->assign('title', '优惠券');
        return $this->fetch('shop@bonus/index');
    }
    /*------------------------------------------------------ */
    //-- 会员设置页
    /*------------------------------------------------------ */
    public function setting(){
        $this->assign('title', '设置');
        return $this->fetch('setting');
    }
    /*------------------------------------------------------ */
    //-- 修改密码
    /*------------------------------------------------------ */
    public function editPwd(){
        $this->assign('title', '修改密码');
        return $this->fetch('edit_pwd');
    }
    /*------------------------------------------------------ */
    //-- 个人资料
    /*------------------------------------------------------ */
    public function userInfo(){
        $this->assign('title', '个人资料');	
		$superior = (new UsersModel)->getSuperior($this->userInfo['pid']);
		$this->assign('superior', $superior);
        return $this->fetch('user_info');
    }
    /*------------------------------------------------------ */
    //-- 我的钱包
    /*------------------------------------------------------ */
    public function wallet(){
        $this->assign('title', '我的钱包');
        return $this->fetch('wallet');
    }
    /*------------------------------------------------------ */
    //-- 提现
    /*------------------------------------------------------ */
    public function withdraw(){
        $this->assign('title', '提现');
        return $this->fetch('withdraw');
    }
    /*------------------------------------------------------ */
    //-- 添加银行卡
    /*------------------------------------------------------ */
    public function addBankCard(){
        $this->assign('title', '添加银行卡');
        return $this->fetch('add_bank_card');
    }
}?>