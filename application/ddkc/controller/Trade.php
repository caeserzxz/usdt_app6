<?php
/*------------------------------------------------------ */
//-- 会员登陆注册
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\ddkc\controller;
use app\ClientbaseController;
use think\Db;
use think\facade\Cache;
use think\facade\Session;

use app\member\model\UsersModel;
use app\ddkc\model\BuyTradeModel;
use app\ddkc\model\SellTradeModel;
use app\ddkc\model\TradingStageModel;
use app\ddkc\model\SlideModel;
use app\ddkc\model\PaymentModel;
use think\cache\driver\Redis;

class Trade  extends ClientbaseController{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new BuyTradeModel();
    }
    //*------------------------------------------------------ */
    //-- 叮叮市场
    /*------------------------------------------------------ */
    public function index(){
        $SlideModel = new SlideModel();
        $slide_where [] = ['status','eq',1];
        $slide_where [] = ['img_type','eq',1];
        $slideList = $SlideModel->where($slide_where)->order('sort_order DESC')->select();
        $this->assign('slideList',$slideList);
        $this->assign('title', '叮叮市场');
        return $this->fetch('index');
    }

    //*------------------------------------------------------ */
    //-- 出售DDB
    /*------------------------------------------------------ */
    public function sell_ddb(){
        $this->assign('userInfo',$this->userInfo);
        $this->assign('setting',settings());

        $this->assign('title', 'DDB挂售');
        return $this->fetch('sell_ddb');
    }

    //*------------------------------------------------------ */
    //-- 叮叮管理
    /*------------------------------------------------------ */
    public function dd_wallet(){
        $BuyTradeModel = new BuyTradeModel();
        $b = $BuyTradeModel->getIds('buyHandle');
//        $a = $BuyTradeModel->lottery(3);
//        dump($a);die;
        $this->assign('userInfo',$this->userInfo);
        $this->assign('setting',settings());

        $this->assign('title', '叮叮管理');
        return $this->fetch('dd_wallet');
    }
    //*------------------------------------------------------ */
    //-- 买家详情
    /*------------------------------------------------------ */
    public function buy_detail(){
        $PaymentModel = new PaymentModel();
        $UsersModel = new UsersModel();
        $SellTradeModel = new SellTradeModel();
        $BuyTradeModel = new BuyTradeModel();
        $id = input('id');
        #买入信息
        $buy_info = $BuyTradeModel->where('id',$id)->find();
        #售出信息
        $sell_info = $SellTradeModel->where('id',$buy_info['sell_id'])->find();
        $sell_info['sell_start_time_date'] = date('Y-m-d H:i:s',$sell_info['sell_start_time']);
        $sell_info['matching_time_date'] = date('Y-m-d H:i:s',$sell_info['matching_time']);
        $sell_info['sell_user_info'] =  $UsersModel->info($sell_info['sell_user_id']);
        #获取卖家的银行卡信息
        $sell_info['bank_info'] = $PaymentModel->get_payment($sell_info['sell_user_id'],1);
        #获取卖家的支付宝信息
        $sell_info['alipay_info'] = $PaymentModel->get_payment($sell_info['sell_user_id'],2);
        #获取卖家的微信信息
        $sell_info['wx_info'] = $PaymentModel->get_payment($sell_info['sell_user_id'],3);

        $this->assign('sell_info',$sell_info);
        $this->assign('userInfo',$this->userInfo);
        $this->assign('title', '买家详情');
        return $this->fetch('buy_detail');
    }
    //*------------------------------------------------------ */
    //-- 卖家详情
    /*------------------------------------------------------ */
    public function sell_detail(){
        $UsersModel = new UsersModel();
        $SellTradeModel = new SellTradeModel();
        $BuyTradeModel = new BuyTradeModel();
        $id = input('id');
        #售出信息
        $sell_info = $SellTradeModel->where('id',$id)->find();
        $sell_info['sell_start_time_date'] = date('Y-m-d H:i:s',$sell_info['sell_start_time']);
        $sell_info['matching_time_date'] = date('Y-m-d H:i:s',$sell_info['matching_time']);
        #买入信息
        $buy_info = $BuyTradeModel->where('id',$sell_info['buy_id'])->find();
        $sell_info['buy_user_info'] =  $UsersModel->info($buy_info['buy_user_id']);
        $this->assign('sell_info',$sell_info);
        $this->assign('title', '卖家详情');
        return $this->fetch('sell_detail');
    }



}