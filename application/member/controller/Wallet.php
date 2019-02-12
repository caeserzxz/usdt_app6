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
    //-- 余额明细
    /*------------------------------------------------------ */
    public function mylog(){
        $this->assign('title', '余额明细');
        return $this->fetch('mylog');
    }
    /*------------------------------------------------------ */
    //-- 排行榜
    /*------------------------------------------------------ */
    public function leaderboard(){
        $this->assign('title', '排行榜');
        return $this->fetch('leaderboard');
    }


}?>