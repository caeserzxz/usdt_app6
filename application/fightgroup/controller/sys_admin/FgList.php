<?php

namespace app\fightgroup\controller\sys_admin;

use think\Db;
use app\AdminController;
use app\fightgroup\model\FightGroupListModel;
use app\fightgroup\model\FightGroupModel;
use app\shop\model\OrderModel;

/**
 * 拼团列表相关
 * Class Index
 * @package app\store\controller
 */
class FgList extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize($isretrun = true)
    {
        parent::initialize();
        $this->Model = new FightGroupListModel();
    }
    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $where = [];
        $search['status'] = input('status',-1,'intval');
        if ($search['status'] > -1){
            $where[] = ['fgl.status','=',$search['status']];
        }
        $search['fg_id'] = input('fg_id',0,'intval');
        if ($search['fg_id'] > 0){
            $where[] = ['fgl.fg_id','=',$search['fg_id']];
        }
        $viewObj = $this->Model->alias('fgl')->join("fightgroup fg", 'fg.fg_id=fgl.fg_id', 'left');
        $viewObj->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left');
        $viewObj->where($where)->field('fgl.*,fg.*,g.goods_name,g.goods_sn,g.is_spec')->order('gid DESC');

        $this->data = $this->getPageList($this->Model, $viewObj);
        $OrderModel = new OrderModel();
        foreach ($this->data['list'] as $key=>$row){
            $where = [];
            $where[] = ['o.order_type','=',2];
            $where[] = ['o.by_id','=',$row['fg_id']];
            $where[] = ['o.pid','=',$row['gid']];
            $where[] = ['o.order_status','in',[0,1]];
            $row['order'] = $OrderModel->alias('o')->join("users u","o.user_id=u.user_id",'left')->where($where)->field('o.order_id,o.user_id,u.nick_name,u.headimgurl')->select()->toArray();
            $row['order_num'] = count($row['order']);
            $this->data['list'][$key] = $row;
        }
        $this->assign("fg_order", lang('fg_order'));
        $this->assign("search", $search);
        $this->assign("data", $this->data);
        if ($runData == false){
            $this->data['content'] = $this->fetch('list')->getContent();
            unset($this->data['list']);
            return $this->success('','',$this->data);
        }
        return true;
    }


    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function info()
    {
        $gid = input('gid',0,'intval');
        if ($gid < 1){
            return $this->error('传参错误.');
        }
        $fgJoin = $this->Model->info($gid);
        $fgInfo = (new FightGroupModel)->info($fgJoin['fg_id']);

        $this->assign("fgJoin", $fgJoin);
        $this->assign("fgInfo", $fgInfo);
        $this->assign("fg_order", lang('fg_order'));
        $where[] = ['order_type','=',2];
        $where[] = ['by_id','=',$fgJoin['fg_id']];
        $where[] = ['pid','=',$fgJoin['gid']];
        $OrderModel = new OrderModel();
        $orderIds = $OrderModel->where($where)->column('order_id');
        $orders = [];
        foreach ($orderIds as $order_id){
            $order = $OrderModel->info($order_id);
            $orders[$order['order_status']][] = $order;
        }
        $this->assign("orders", $orders);
        return $this->fetch('info');
    }
}