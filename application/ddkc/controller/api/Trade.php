<?php

namespace app\ddkc\controller\api;
use think\Db;
use app\ApiController;
use app\member\model\UsersModel;
use app\member\model\AccountLogModel;
use app\ddkc\model\BuyTradeModel;
use app\ddkc\model\SellTradeModel;
use app\ddkc\model\TradingStageModel;
/*------------------------------------------------------ */
//-- 会员登陆、注册、找回密码相关API
/*------------------------------------------------------ */

class Trade extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new BuyTradeModel();
    }

    /*------------------------------------------------------ */
    //-- 获取交易区间列表
    /*------------------------------------------------------ */
    public function getTradingStageList(){
        $model = new TradingStageModel;
        $this->sqlOrder = 'id ASC';
        $where[] = ['isputaway' ,'eq' ,1];
        $data = $this->getPageList($model,$where);
        if (count($data['list']) > 0) {
            foreach ($data['list'] as $key => $value) {

            }
        }
        return $this->ajaxReturn($data);
    }

    /*------------------------------------------------------ */
    //-- 预约交易
    /*------------------------------------------------------ */
    public function buy_trade(){
        $userModel = new UsersModel();
        $accountModel = new AccountLogModel();
        $SellTradeModel = new SellTradeModel();
        $TradingStageModel = new TradingStageModel();
        # 是否登录
        $user = $userModel->info($this->userInfo['user_id']);
        if (!$user) {
            return $this->ajaxReturn(['code' => 0,'msg' => '请先登录','url' => url('passport/login')]);
        }
        $stage_id = input('stage_id');
        $stage_info = $TradingStageModel->where('id',$stage_id)->find();


    }

}
