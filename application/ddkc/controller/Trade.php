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
}