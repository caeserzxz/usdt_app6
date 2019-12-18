<?php

namespace app\shop\controller\sys_admin;

use app\AdminController;
use app\shop\model\AdModel;

/*------------------------------------------------------ */
//-- 广告相关
/*------------------------------------------------------ */

class Ad extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new AdModel();
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
        foreach ($data['list'] as $key => $val) {
            $data['list'][$key]['data'] = json_decode($val['data'], true);
        }

        $adType = ['1' => '单图广告', '2' => '双图广告', '3' => '三图广告'];
        $this->assign("adType", $adType);

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
        $WeixinReplyType = $this->getDict('WeixinReplyType');
        $this->assign("WeixinReplyType", $WeixinReplyType);
        $row['data'] = json_decode($row['data'], true);
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
        $data['update_time'] = time();
        if (empty($data['title'])) return $this->error('广告标题未填写！');
        if (empty($data['ad_type'])) return $this->error('广告类型未选择！');

        for ($i = 0; $i < $data['ad_type']; $i++) {
            if (empty($data['data'][$i]['imgurl'])) return $this->error('幻灯片图片未选择！');
            if ($data['data'][$i]['bind_type'] == 'article' || $data['data'][$i]['bind_type'] == 'product') {
                // 文章、商品、活动
                if (empty($data['data'][$i]['ext_id'])) return $this->error('链接类型绑定关联值不可以为空！');
            }
        }
        $data['data'] = json_encode($data['data'], JSON_UNESCAPED_UNICODE);
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
        return $this->success('修改成功.', url('index'));
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
