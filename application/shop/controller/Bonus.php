<?php
/*------------------------------------------------------ */
//-- 优惠券
//-- Author: iqgmy
/*------------------------------------------------------ */

namespace app\shop\controller;

use app\ClientbaseController;
use app\member\model\UsersModel;
use app\shop\model\BonusModel;

class Bonus extends ClientbaseController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new BonusModel();
    }
    /*------------------------------------------------------ */
    //-- 会员优惠券页
    /*------------------------------------------------------ */
    public function index(){
        $this->assign('title', '我的优惠券');
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 领券中心
    /*------------------------------------------------------ */
    public function bonusCenter(){
        $this->assign('title', '领券中心');
        return $this->fetch('bonus_center');
    }

    /*------------------------------------------------------ */
    //-- 可用商品列表
    /*------------------------------------------------------ */
    public function goodsList(){
        $type_id = input('type_id',0,'intval');
        if(empty($type_id)){
            $this->error('参数错误');
        }
        $BonusListModel = new \app\shop\model\BonusModel();
        $bonus = $BonusListModel->find($type_id)->toArray();
        $bonus['_use_start_date'] = date("Y.m.d", $bonus['use_start_date']);
        $bonus['_use_end_date'] = date("Y.m.d", $bonus['use_end_date']);
        $this->assign('bonus',$bonus);
        $this->assign('title', '商品列表');
        return $this->fetch('goods_list');
    }

}