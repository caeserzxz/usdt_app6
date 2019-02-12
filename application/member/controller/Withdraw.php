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
    //-- 添加银行卡
    /*------------------------------------------------------ */
    public function add(){
        $this->assign('title', '添加银行卡');
        return $this->fetch('add');
    }
   


}?>