<?php
/*------------------------------------------------------ */
//-- 我的团队
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\ddkc\controller;
use app\ClientbaseController;


class Miner  extends ClientbaseController{	
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
		// dump($this->userInfo);die;
        $this->assign('title', '矿机');
		return $this->fetch('index');
	}
	
   	/*------------------------------------------------------ */
	//-- 购买矿机
	/*------------------------------------------------------ */
	public function buy_miner(){
        $this->assign('title', '购买矿机');
		return $this->fetch();
	}
	/*------------------------------------------------------ */
	//-- 矿机订单详情
	/*------------------------------------------------------ */
	public function mining_order_detail(){
        $this->assign('title', '运行中');
		return $this->fetch();
	}
	/*------------------------------------------------------ */
	//-- ZBL矿机订单
	/*------------------------------------------------------ */
	public function zbl_mining_order(){
        $this->assign('title', '我的ZBL矿机');
		return $this->fetch();
	}
	/*------------------------------------------------------ */
	//-- 我的战队
	/*------------------------------------------------------ */
	public function my_mining_team(){
        $this->assign('title', '我的战队');
		return $this->fetch();
	}
}?>