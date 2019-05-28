<?php
/*------------------------------------------------------ */
//-- 身份商品
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\distribution\controller;
use app\ClientbaseController;
use app\distribution\model\RoleGoodsModel;
use app\distribution\model\RoleOrderModel;
use think\Db;
class RoleGoods  extends ClientbaseController{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new RoleGoodsModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){
        $this->assign('title', 'UI商品');
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 结算页
    /*------------------------------------------------------ */
    public function checkOut()
    {
        $this->checkLogin(false);//验证白名单
        $rg_id = input('rg_id', 0, 'intval');
        if ($rg_id < 1){
            return $this->error('请选择商品后再操作.');
        }
        $this->assign('title', '购买UI商品');
        $rgoods = $this->Model->find($rg_id);
        if (empty($rgoods)){
            return $this->error('没有找到相关商品.');
        }
        if ($rgoods['status'] == 0){
            return $this->error('相关商品已下架.');
        }
        $rgoods['exp_price'] = explode('.',$rgoods['sale_price']);
        $this->assign('rgoods', $rgoods);
        return $this->fetch('check_out');
    }
    /*------------------------------------------------------ */
    //-- 下单完成
    /*------------------------------------------------------ */
    public function done(){
        $order_id = input('order_id',0,'intval');
        $type = input('type','','trim');
        $this->assign('title', '订单支付');
        $RoleOrderModel = new RoleOrderModel();
        $orderInfo = $RoleOrderModel->find($order_id);
        if (empty($orderInfo) || $orderInfo['user_id'] != $this->userInfo['user_id']){
            $this->error('订单不存在.');
        }
        $goPay = 0;
        if ($type == 'add' && $orderInfo['pay_status'] == config('config.PS_UNPAYED')){
            $goPay = 1;
        }

        $this->assign('goPay', $goPay);
        $this->assign('orderInfo', $orderInfo);

        return $this->fetch('done');
    }
}