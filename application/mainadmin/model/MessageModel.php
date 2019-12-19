<?php

namespace app\mainadmin\model;

use app\BaseModel;
use think\facade\Cache;
use app\mainadmin\model\UserMessageModel;
use app\mainadmin\model\ArticleModel;

//*------------------------------------------------------ */
//-- 站内信
/*------------------------------------------------------ */

class MessageModel extends BaseModel
{
    protected $table = 'main_message';
    public $pk = 'message_id';
    protected $mkey = 'message_info_mkey_';
    /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($message_id=0)
    {
        Cache::rm($this->mkey . $message_id);
        if ($this->userInfo['user_id'] > 0) {
            Cache::rm($this->mkey . '_user_stat' . $this->userInfo['user_id']);
        }
    }

    /*------------------------------------------------------ */
    //-- 获取用户站内信列表
    //-- $uid 用户ID
    /*------------------------------------------------------ */
    public function getListByUser($uid = 0)
    {
        if ($uid < 1) {
            $uid = $this->userInfo['user_id'] * 1;
            if ($uid < 1) return [];
        }
        $time = time();
        $UserMessageModel = new UserMessageModel();
        $where[] = ['user_id', '=', $uid];
        $where[] = ['show_end_date', '>', $time];
        $rows = $UserMessageModel->where($where)->order('is_see ASC,add_time DESC')->select()->toArray();
        foreach ($rows as $row) {
            if ($row['message_type'] == 0) {
                $message = $this->info($row['ext_id']);
                $row['message_title'] = $message['title'];
                $row['message_content'] = $message['article']['description'];
                $row['imgUrl'] = $message['article']['img_url'];
                $row['url'] = url('member/messaege/info', array('rec_id' => $row['rec_id']));
            }
            $row['_add_time'] = date("Y-m-d H:i", $row['add_time']);
            $return[] = $row;
        }
        return $return;
    }


    /*------------------------------------------------------ */
    //-- 获取站内信信息
    //-- $message_id 站内信主ID
    /*------------------------------------------------------ */
    public function info($message_id = 0)
    {
        $message_id = $message_id * 1;
        $info = Cache::get($this->mkey . $message_id);
        if ($info) return $info;
        $info = $this->find($message_id)->toArray();
        $article = (new ArticleModel)->info($info['ext_id']);
        $info['article'] = $article;
        Cache::set($this->mkey . $message_id, $info, 60);
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 获取用户站内信信息
    //-- $rec_id 用户站内信主ID
    /*------------------------------------------------------ */
    public function umInfo($rec_id = 0)
    {
        $UserMessageModel = new UserMessageModel();
        $message = $UserMessageModel->find($rec_id)->toArray();
        if ($message['message_type'] == 0) {
            $info = $this->info($message['ext_id']);
            $message['status'] = $info['status'];
            $message['message_title'] = $info['title'];
            $message['message_content'] = $info['article']['content'];
            $message['article'] = $info['article'];
        }
        return $message;
    }

    /*------------------------------------------------------ */
    //-- 通知发送
    //-- $user_id 用户ID
    //-- $message_type 用户ID
    //-- $ext_id 用户ID
    //--$message_title 标题
    //-- $message_content 内容
    //-- $show_end_date 过期时间，非系统消息默认为3个月
    /*------------------------------------------------------ */
    public function sendMessage($user_id, $message_type, $ext_id = 0, $message_title, $message_content = '', $show_end_date = '')
    {
        if (empty($show_end_date)) {
            $show_end_date = strtotime('+3 month', time());
        }
        $inArr['user_id'] = $user_id;
        $inArr['message_type'] = $message_type;
        $inArr['ext_id'] = $ext_id;
        $inArr['message_title'] = $message_title;
        $inArr['message_content'] = $message_content;
        $inArr['show_end_date'] = $show_end_date;
        $inArr['add_time'] = time();
        $res = (new UserMessageModel)->create($inArr);
        if ($res['rec_id'] < 1) return false;
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取会员站内信汇总
    /*------------------------------------------------------ */
    public function userMessageStats($user_id = 0)
    {
        $mkey = $this->mkey . '_user_stat_' . $user_id;
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        $UserMessageModel = new UserMessageModel();
        $time = time();
        $user_id = $user_id * 1;
        $user = $this->userInfo;
        $where[] = ['user_id', '=', $user_id];
        $where[] = ['is_see', '=', 0];
        $unSeeNum = $UserMessageModel->where($where)->count('rec_id');//未读信息数量

        //已接收的系统站内信
        $whereReceived[] = ['user_id', '=', $user_id];
        $whereReceived[] = ['message_type', '=', 0];
        $message_ids = $UserMessageModel->where($whereReceived)->column('ext_id');

        //未接收的消息数量
        $whereUnReceive[] = ['status', '=', 0];
        $whereUnReceive[] = ['message_id', 'not in', $message_ids];
        $whereUnReceive[] = ['send_start_date', '<', $time];
        $whereUnReceive[] = ['send_end_date', '>', $time];
        $user_level_id = isset($user['level']['level_id'])?$user['level']['level_id']:0;//防止无等级报错
        $user_role_id = $user['role']['role_id']?$user['role']['role_id']:0;
        $whereUnReceiveOr = "type=0 OR (type=1 AND type_ext_id=" . $user_level_id . ") OR (type=2 AND type_ext_id=" . $user_role_id . ")";
        $unReceiveNum = $this->where($whereUnReceive)->where($whereUnReceiveOr)->count('message_id');
        $unSeeNum += $unReceiveNum;
        Cache::set($mkey, $unSeeNum, 300);
        return $unSeeNum;
    }
    /*------------------------------------------------------ */
    //-- 消息设为已读
    /*------------------------------------------------------ */
    public function  setSeen($rec_id=0){
        $user_id = $this->userInfo['user_id'];
        $mkey = $this->mkey . '_user_stat_' . $user_id;
        if ($rec_id < 1) return false;
        $where[]=['rec_id','=',$rec_id];
        $where[]=['user_id','=',$user_id];
        $UserMessageModel = new UserMessageModel();
        $res = $UserMessageModel->where($where)->update(['is_see' => 1]);
        if(empty($res))return false;;
        Cache::dec($mkey);
        return true;
    }

    /*------------------------------------------------------ */
    //-- 自动接收系统消息
    /*------------------------------------------------------ */
    public function autoReceive()
    {
        $user = $this->userInfo;
        if (empty($user)) return false;
        $mkey = $this->mkey . '_user_receive_' . $user['user_id'];
        $info = Cache::get($mkey);
        if (empty($info) == false) return false;
        $UserMessageModel = new UserMessageModel();
        $time = time();
        //已接收的系统站内信
        $whereReceived[] = ['user_id', '=', $user['user_id']];
        $whereReceived[] = ['message_type', '=', 0];
        $message_ids = $UserMessageModel->where($whereReceived)->column('ext_id');

        //未接收的消息数量
        $whereUnReceive[] = ['status', '=', 0];
        $whereUnReceive[] = ['message_id', 'not in', $message_ids];
        $whereUnReceive[] = ['send_start_date', '<', $time];
        $whereUnReceive[] = ['send_end_date', '>', $time];
        $user_level_id = isset($user['level']['level_id'])?$user['level']['level_id']:0;//防止无等级报错
        $whereUnReceiveOr = "type=0 OR (type=1 AND type_ext_id=" .$user_level_id . ") OR (type=2 AND type_ext_id=" . $user['role']['role_id'] . ")";
        $rows = $this->where($whereUnReceive)->where($whereUnReceiveOr)->field('message_id,title,show_end_date')->select()->toArray();
        foreach ($rows as $row) {
            $this->sendMessage($user['user_id'], 0, $row['message_id'], $row['title'], '', $row['show_end_date']);//写入数据
        }
        Cache::set($mkey, 'autoReceive', 300);
    }

}
