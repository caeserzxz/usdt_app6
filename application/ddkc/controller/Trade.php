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
use app\member\model\DdBanRecordModel;

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
        $slideList = $SlideModel::getRows(1);
        $this->assign('settings',settings());
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
//        $b = $BuyTradeModel->getIds('buyHandle');
//        $a = $BuyTradeModel->AutomaticCancellation();
//        $BuyTradeModel->AutoCompletion();
//        $a = $BuyTradeModel->lottery();
//        $a = $BuyTradeModel->BeOverdue();
//        $a = $BuyTradeModel->DailyReset();
//        $a = $BuyTradeModel->Unsealing();
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
        $setting = settings();
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
        #待确认倒计时
        $confirm_time = 0;
        if($sell_info['sell_status']==2){
            $is_confirm = 1;
            $confirm_time = $sell_info['payment_time'] + ($setting['complete_time']*60)  - time();
            if(!($confirm_time>0)){
                $is_confirm =2;
            }
        }else{
            $is_confirm =2;
        }
        #待付款倒计时
        $payment_time = 0;
        if($sell_info['sell_status']==1){
            $is_payment = 1;

            if($sell_info['is_delay']==1){
                $payment_time = $sell_info['matching_time'] + ($setting['cancel_time']*60) + ($setting['delay_time']*60) - time();
            }else{
                $payment_time = $sell_info['matching_time'] + ($setting['cancel_time']*60)  - time();
            }

            if(!($payment_time>0)){
                $is_payment =2;
            }
        }else{
            $is_payment =2;
        }
        $this->assign('is_confirm',$is_confirm);
        $this->assign('confirm_time',$confirm_time);
        $this->assign('is_payment',$is_payment);
        $this->assign('payment_time',$payment_time);

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
        $setting = settings();
        $id = input('id');
        #售出信息
        $sell_info = $SellTradeModel->where('id',$id)->find();
        $sell_info['sell_start_time_date'] = date('Y-m-d H:i:s',$sell_info['sell_start_time']);
        $sell_info['matching_time_date'] = date('Y-m-d H:i:s',$sell_info['matching_time']);
        #买入信息
        $buy_info = $BuyTradeModel->where('id',$sell_info['buy_id'])->find();
        $sell_info['buy_user_info'] =  $UsersModel->info($buy_info['buy_user_id']);
        #待确认倒计时
        $confirm_time = 0;
        if($sell_info['sell_status']==2){
            $is_confirm = 1;
            $confirm_time = $sell_info['payment_time'] + ($setting['complete_time']*60)  - time();
            if(!($confirm_time>0)){
                $is_confirm =2;
            }
        }else{
            $is_confirm =2;
        }
        #待付款倒计时
        $payment_time = 0;
        if($sell_info['sell_status']==1){
            $is_payment = 1;

            if($sell_info['is_delay']==1){
                $payment_time = $sell_info['matching_time'] + ($setting['cancel_time']*60) + ($setting['delay_time']*60) - time();
            }else{
                $payment_time = $sell_info['matching_time'] + ($setting['cancel_time']*60)  - time();
            }

            if(!($payment_time>0)){
                $is_payment =2;
            }
        }else{
            $is_payment =2;
        }
        $this->assign('is_confirm',$is_confirm);
        $this->assign('confirm_time',$confirm_time);
        $this->assign('is_payment',$is_payment);
        $this->assign('payment_time',$payment_time);
        $this->assign('sell_info',$sell_info);
        $this->assign('title', '卖家详情');
        return $this->fetch('sell_detail');
    }

    //*------------------------------------------------------ */
    //-- 投诉
    /*------------------------------------------------------ */
    public function appeal(){
        $UsersModel = new UsersModel();
        $SellTradeModel = new SellTradeModel();
        $BuyTradeModel = new BuyTradeModel();

        $id = input('id');
        #售出信息
        $sell_info = $SellTradeModel->where('id',$id)->find();
        #买入信息
        $buy_info = $BuyTradeModel->where('id',$sell_info['buy_id'])->find();
        #抢购者信息
        $buy_user = $UsersModel->info($buy_info['buy_user_id']);
        $this->assign('sell_info',$sell_info);
        $this->assign('buy_user',$buy_user);

        $this->assign('title', '申诉');
        return $this->fetch('appeal');
    }

}