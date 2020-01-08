<?php
/*------------------------------------------------------ */
//-- 钱包相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\member\controller;
use app\ClientbaseController;


class Wallet  extends ClientbaseController{	
	/*------------------------------------------------------ */
	//-- 我的钱包
	/*------------------------------------------------------ */
	public function index(){
        $this->assign('title', '我的钱包');
		return $this->fetch('index');
	}
    /*------------------------------------------------------ */
    //-- 旅游豆转换
    /*------------------------------------------------------ */
    public function change(){
        $this->assign('title', '旅游豆兑换');
        return $this->fetch('change');
    }
	/*------------------------------------------------------ */
    //-- 充值
    /*------------------------------------------------------ */
    public function recharge(){
        $order_id = input('order_id',0,'intval');
        $this->assign('title', '充值');
        return $this->fetch('recharge');
    }
	/*------------------------------------------------------ */
    //-- 余额明细
    /*------------------------------------------------------ */
    public function mylog(){
        $this->assign('title', '帐户明细');
        return $this->fetch('mylog');
    }
    /*------------------------------------------------------ */
    //-- 佣金明细
    /*------------------------------------------------------ */
    public function dividendLog(){
        $this->assign('title', '佣金明细');
        return $this->fetch('dividend_log');
    }
    /*------------------------------------------------------ */
    //-- 充值记录
    /*------------------------------------------------------ */
    public function rechargeLog(){
        $this->assign('title', '充值记录');
        return $this->fetch('recharge_log');
    }
    /*------------------------------------------------------ */
    //-- 排行榜
    /*------------------------------------------------------ */
    public function leaderboard(){
        $this->assign('title', '排行榜');
        return $this->fetch('leaderboard');
    }


}?>