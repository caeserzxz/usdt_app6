<?php

namespace app\shop\controller\sys_admin;

use app\AdminController;
use app\shop\model\PlateModel;

/*------------------------------------------------------ */
//-- 楼层板块
/*------------------------------------------------------ */

class Plate extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new PlateModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false, $is_delete = 0)
    {
        $this->sqlOrder = 'sort_order DESC';
        $data = $this->getPageList($this->Model);
        $this->assign("data", $data);
        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 添加修改前调用
    /*------------------------------------------------------ */
    public function asInfo($row)
    {
        return $row;
    }
    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($data)
    {
        if ($data['id'] < 1) {
            $data['add_time'] = time();
        }
        if (empty($data['name'])) return $this->error('请填板块名称！');
        if (empty($data['key'])) return $this->error('请填写板块标识！');
        return $data;
    }
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($data)
    {
        $this->Model->cleanMemcache();
        return $this->success('添加成功.', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前调用
    /*------------------------------------------------------ */
    public function beforeEdit($data)
    {
        return $this->beforeAdd($data);
    }
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($data)
    {
        $this->Model->cleanMemcache();
        return $this->success('修改成功.',url('index'));
    }

    /*------------------------------------------------------ */
    //-- ajax快速修改
    //-- id int 修改ID
    //-- data array 修改字段
    /*------------------------------------------------------ */
    public function afterAjax($id, $data)
    {
        if (isset($data['status'])) {
            $this->Model->cleanMemcache($id);
        }
    }

    /*------------------------------------------------------ */
    //-- 删除
    /*------------------------------------------------------ */
    public function del()
    {
        $id = input('id', 0, 'intval');
        if ($id < 1) return $this->error('传递参数失败！');
        $data = $this->Model->where('id', $id)->find();
        unlink('.' . $data['imgurl']);
        $res = $data->delete();
        if ($res < 1) return $this->error();
        $this->Model->cleanMemcache();
        return $this->success('删除成功.');
    }

}
