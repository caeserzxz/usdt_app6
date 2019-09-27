<?php

namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\MessageModel;
use app\mainadmin\model\UserMessageModel;
use app\mainadmin\model\ArticleModel;
use app\member\model\UsersLevelModel;
use app\distribution\model\DividendRoleModel;
use app\member\model\UsersModel;

/**
 * 站内信管理
 * Class Index
 * @package app\store\controller
 */
class Message extends AdminController
{
    public $_field = '';
    public $_pagesize = '';

    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new MessageModel();
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
        $cid = input('id', 0, 'intval');
        $this->getList(true);
        $this->assign("cgOpt", arrToSel($this->cg_list, $cid));
        return $this->fetch('mainadmin@message/index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $time = time();
        $runJson = input('runJson', 0, 'intval');
        $where = [];
        $search['cid'] = input('cid', 0, 'intval');
        $search['keyword'] = input('keyword', '', 'trim');
        $search['type'] = input('type', '');
        if ($search['cid'] > 0) {
            $where[] = ['cid', 'in', $this->cg_list[$search['cid']]['children']];
        }
        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) $where[] = ['title', 'like', "%" . $search['keyword'] . "%"];
        if ($search['type'] != '') $where[] = ['type', '=', $search['type']];

        $data = $this->getPageList($this->Model, $where, $this->_field, $this->_pagesize);
        foreach ($data['list'] as $key => $row) {
            if($row['start_send_date']>$time){
                $data['list'][$key]['_status']='暂未发送';
            }elseif($row['end_send_date']<$time){
                $data['list'][$key]['_status']='发送结束';
            }elseif ($row['show_end_date']<$time){
                $data['list'][$key]['_status']='已过时';
            }elseif ($row['status']==0){
                $data['list'][$key]['_status']='发送中';
            }elseif ($row['status']==0){
                $data['list'][$key]['_status']='暂定发送';
            }
        }
        $this->assign("MessageType", $this->getDict('MessageType'));//发送类型
        $this->assign("data", $data);
        $this->assign("search", $search);
        if ($runJson == 1) {
            return $this->success('', '', $data);
        } elseif ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data)
    {
        $this->assign("MessageType", $this->getDict('MessageType'));//发送类型
        $DividendRoleModel = new DividendRoleModel();
        $this->assign("roleList", $DividendRoleModel->getRows());//分销身份
        $UsersLevelModel = new UsersLevelModel();
        $this->assign('levelList', $UsersLevelModel->getRows());
        if ($data['message_id'] > 0) {
            if ($data['type'] == 3) {
                $where[] = ['ext_id', '=', $data['message_id']];
                $userList = (new UserMessageModel)->alias('um')
                    ->join('users u', 'um.user_id=u.user_id', 'left')
                    ->field('um.user_id,u.mobile,u.nick_name')
                    ->where($where)->select()->toArray();
                $this->assign('userList', $userList);
            }
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($row)
    {
        if (empty($row['ext_id'])) return $this->error('请选择关联文章.');
        if (empty($row['send_start_date'])) return $this->error('请选择发送起始日期.');
        if (empty($row['send_end_date'])) return $this->error('请选择发送结束日期.');
        if (empty($row['show_end_date'])) return $this->error('请选择显示过期日期.');
        $row['send_start_date'] = strtotime($row['send_start_date']);
        $row['send_end_date'] = strtotime($row['send_end_date']);
        $row['show_end_date'] = strtotime($row['show_end_date']);
        if ($row['send_start_date'] >= $row['send_end_date']) return $this->error('发送结束日期必须大于发送起始日期！');
        if ($row['send_end_date'] >= $row['show_end_date']) return $this->error('显示过期日期必须大于发送结束日期！');
        if ($row['send_end_date']+3*86400*30 < $row['show_end_date']) return $this->error('显示过期日期不能超过发送结束日期三个月！');

        if (isset($row['type']) == false) return $this->error('请选择通知发送类型.');
        switch ($row['type']) {
            case 1:
                if (empty($row['level_id'])) return $this->error('请选择指定会员等级.');
                $row['type_ext_id'] = $row['level_id'];
                break;
            case 2:
                if (empty($row['role_id'])) return $this->error('请选择指定分销身份.');
                $row['type_ext_id'] = $row['role_id'];
                break;
                if (empty($row['user_id'])) return $this->error('请选择指定会员.');
            default:
                $row['type_ext_id'] = '';
                break;
        }
        $row['add_time'] = $row['add_time'] ? strtotime($row['add_time']) : time();
        $row['update_time'] = time();
        return $row;
    }
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($row)
    {
        if ($row['type'] == 3) {//指定会员直接发放
            foreach ($row['user_id'] as $user_id) {
                $this->Model->sendMessage($user_id, 0, $row['message_id'], '', $row['show_end_date']);
            }
        }
        $this->Model->cleanMemcache();
        return $this->success('添加成功.', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前调用
    /*------------------------------------------------------ */
    public function beforeEdit($row)
    {
        return $this->beforeAdd($row);
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($row)
    {
        if ($row['type'] == 3) {//指定会员直接发放
            foreach ($row['user_id'] as $user_id) {
                $this->Model->sendMessage($user_id, 0, $row['message_id'], '', $row['show_end_date']);
            }
        }
        return $this->success('修改成功.');
    }
}
