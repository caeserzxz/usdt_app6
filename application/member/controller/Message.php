<?php
/*------------------------------------------------------ */
//-- 站内信
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\member\controller;
use app\ClientbaseController;
use app\mainadmin\model\MessageModel;
use app\mainadmin\model\UserMessageModel;


class Message extends ClientbaseController{
    /*------------------------------------------------------ */
    //-- 站内信列表
    /*------------------------------------------------------ */
    public function index(){
        (new MessageModel)->autoReceive();//执行自动接收消息
        $this->assign('title', '系统消息');
        return $this->fetch('index');
    }
	/*------------------------------------------------------ */
	//-- 站内信详情页
	/*------------------------------------------------------ */
	public function info(){
	    $id = input('id',0,'intval');
	    if ($id < 1){
	        return $this->error('传参失败.');
        }
        $message = (new MessageModel)->umInfo($id);
	    if (empty($message)){
            return $this->error('消息不存在..');
        }
        if ($message['status']==1){
            return $this->error('消息已过期..');
        }
        $article=$message['article'];
        $this->assign('title','消息详情');
        $this->assign('info', $article);
		return $this->fetch('info');
	}
}?>