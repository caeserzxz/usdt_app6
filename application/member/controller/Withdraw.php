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
        $this->assign('title', '提现管理');
		return $this->fetch('index');
	}
	/*------------------------------------------------------ */
    //-- 银行卡
    /*------------------------------------------------------ */
    public function bankList(){
        $this->assign('title', '银行卡');
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