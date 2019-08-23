<?php

namespace app\member\controller\api;
use app\ApiController;
use app\mainadmin\model\MessageModel;
use app\mainadmin\model\UserMessageModel;


/*------------------------------------------------------ */
//-- 站内信相关API
/*------------------------------------------------------ */

class Message extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new MessageModel();
    }
    /*------------------------------------------------------ */
    //-- 获取消息列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $time =time();
        $UserMessageModel = new UserMessageModel();
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[]=['show_end_date','>',$time];
        $sqlOrder = "is_see ASC,add_time DESC";
        $data = $this->getPageList($UserMessageModel, $where,'*',10,$sqlOrder);
        foreach ($data['list'] as $key=>$row){
            if($row['message_type']==0){
                $message = $this->Model->info($row['ext_id']);
                $row['message_title'] =$message['title'];
                $row['message_content'] =$message['article']['description'];
                $row['imgUrl'] =$message['article']['img_url'];
                $row['url'] = url('member/message/info',array('id'=>$row['rec_id']));
            }
            $row['_add_time'] = date("Y.m.d",$row['add_time']);
            $return['list'][] = $row;
        }
        $return['page_count'] = $data['page_count'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 消息设为已读
    /*------------------------------------------------------ */
    public function setIsSee(){
        $rec_id = input('rec_id', 0, 'intval');
        $res = $this->Model->setSeen($rec_id);
        if(empty($res)){
            $return['code']=0;
            $return['msg']='更新失败';
            return $this->ajaxReturn($return);
        }
        $return['code']=1;
        $return['msg']='更新成功';
        return $this->ajaxReturn($return);
    }

}
