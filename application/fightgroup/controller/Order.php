<?php
/*------------------------------------------------------ */
//-- 拼团订单相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\fightgroup\controller;
use app\ClientbaseController;
use app\shop\model\OrderModel;
use app\fightgroup\model\FightGroupListModel;
class Order  extends ClientbaseController{

    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){
        (new FightGroupListModel)->evalFail($this->userInfo['user_id']);//执行失败处理
        $this->assign('title', '我的拼团');
        $this->assign('type', input('type','myInitiate','trim'));
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 订单详情
    /*------------------------------------------------------ */
    public function info(){
        $this->assign('title', '订单详情');
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $OrderModel = new OrderModel();
        $orderInfo = $OrderModel->info($order_id);
        $this->assign('orderInfo', $orderInfo);
        $fgJoin = (new FightGroupListModel)->info($orderInfo['pid']);
        $this->assign('fgJoin', $fgJoin);
        return $this->fetch('info');
    }

}