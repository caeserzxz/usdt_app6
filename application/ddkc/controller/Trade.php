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
        $this->assign('userInfo',$this->userInfo);
        $this->assign('setting',settings());

        $this->assign('title', '叮叮管理');
        return $this->fetch('dd_wallet');
    }
}