<?php

namespace app\shop\controller\sys_admin;

use app\AdminController;
use app\shop\model\NavMenuModel;

/*------------------------------------------------------ */
//-- 商城首页底部菜单
/*------------------------------------------------------ */

class NavFoot extends AdminController
{
    protected $type = 2;//导航类型
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new NavMenuModel();
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
        $where[] = ['type', '=', $this->type];
        $this->sqlOrder = 'sort_order DESC';
        $data = $this->getPageList($this->Model, $where);
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
        $this->assign("WeixinReplyType", arrToSel($WeixinReplyType, $row['bind_type']));
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
        $data['data'] = input('type_val', '', 'trim');
        if (empty($data['imgurl'])) return $this->error('图片未选择！');
        if (empty($data['bind_type'])) return $this->error('链接类型未选择！');
        if (empty($data['data'])) return $this->error('链接类型绑定关联未填写！');
        if ($data['bind_type'] == 'article' || $data['bind_type'] == 'product') {
            // 文章、商品
            if (empty($data['ext_id'])) return $this->error('链接类型绑定关联值不可以为空！');

        }
        $data['type'] = $this->type;
        return $data;
    }
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($data)
    {
        $this->Model->cleanMemcache($this->type);
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
        $this->Model->cleanMemcache($this->type);
        return $this->success('修改成功.', url('index'));
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
        $this->Model->cleanMemcache($this->type);
        return $this->success('删除成功.');
    }

}
