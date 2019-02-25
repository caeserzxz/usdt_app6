<?php
/*------------------------------------------------------ */
//-- 评论中心
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;



class Comment extends ClientbaseController{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
		$this->assign('title', '评论中心');
		$this->assign('order_id', input('order_id',0,'intval'));         
		return $this->fetch('index');
	}
   /*------------------------------------------------------ */
	//-- 评论页面
	/*------------------------------------------------------ */
	public function comment(){
		$rec_id = input('rec_id',0,'intval');
		if ($rec_id < 1) return $this->error('传参失败.');
		$this->assign('title', '评论');     
		$this->assign('rec_id',$rec_id );
		return $this->fetch('comment');
	}
	/*------------------------------------------------------ */
	//-- 查看评论
	/*------------------------------------------------------ */
	public function view(){
		$rec_id = input('rec_id',0,'intval');
		if ($rec_id < 1) return $this->error('传参失败.');
		$this->assign('title', '查看评价');     
		$this->assign('rec_id',$rec_id );
		return $this->fetch('view');
	}
}?>