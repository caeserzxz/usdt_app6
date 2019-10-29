<?php

namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\HeadlineModel;
use app\mainadmin\model\ArticleModel;

/**
 * 头条管理
 * Class Index
 * @package app\store\controller
 */
class Headline extends AdminController
{
    public $_field = '';
    public $_pagesize = '';

    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new HeadlineModel();
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        return $this->fetch('mainadmin@headline/index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
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
        if ($search['type'] != '') $where[] =['type','=',$search['type']];

        $this->data = $this->getPageList($this->Model, $where, $this->_field, $this->_pagesize);
        $this->assign("data", $this->data);
        $this->assign("search", $search);
        if ($runJson == 1) {
            return $this->success('', '', $this->data);
        } elseif ($runData == false) {
            $this->data['content'] = $this->fetch('list')->getContent();
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data)
    {
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($row)
    {
        $row['add_time'] = $row['add_time'] ? strtotime($row['add_time']) : time();
        $row['update_time'] = time();
        return $row;
    }
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($row)
    {
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
        $this->Model->cleanMemcache();
        return $this->success('修改成功.');
    }
    /*------------------------------------------------------ */
    //-- 删除头条
    /*------------------------------------------------------ */
    public function del()
    {
        $map['id'] = input('id', 0, 'intval');
        if ($map['id'] < 1) return $this->error('传递参数失败！');
        $res = $this->Model->where($map)->delete();
        if ($res < 1) return $this->error();
        $this->Model->cleanMemcache();
        return $this->success('删除成功.');
    }
}
