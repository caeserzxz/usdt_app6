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
        $BuyTradeMoel = new BuyTradeModel();
        $TradingStageModel = new TradingStageModel();
        # 是否登录
        $user = $userModel->info($this->userInfo['user_id']);
        if (!$user) {
            return $this->ajaxReturn(['code' => 0,'msg' => '请先登录','url' => url('passport/login')]);
        }
        $stage_id = input('stage_id');
        $stage_info = $TradingStageModel->where('id',$stage_id)->find();
        if(empty($stage_info)){
            return $this->ajaxReturn(['code' => 0,'msg' => '','url' => '']);
        }
        if($user['account']['use_integral']<$stage_info['scribe_integral']){
            return $this->ajaxReturn(['code' => 0,'msg' => '信用积分不足,无法预约','url' => '']);
        }
        Db::startTrans();
        $addData = [
            'buy_user_id'=>$user['user_id'],
            'buy_stage_id'=>$stage_id,
            'buy_status'=>0,
            'deduct_integral'=>$stage_info['scribe_integral'],
            'isputaway'=>1,
            'buy_start_time'=>time()
        ];
        $res = $BuyTradeMoel->create($addData);
        if (!$res) {
            Db::rollback();
            return $this->ajaxReturn(['code' => 0,'msg' => '预约失败','url' => '']);
        }

        if($res){
            //预约成功后,扣除用户的信用积分
            $charge['ddb_money']   = -$stage_info['scribe_integral'];
            $charge['change_desc'] = '预约扣除信用积分';
            $charge['change_type'] = 11;
            $res1 =$accountModel->change($charge, $user['user_id'], false);
            if(!$res1){
                Db::rollback();
                return $this->ajaxReturn(['code' => 0,'msg' => '扣除信用积分失败','url' => '']);
            }
        }

        # 预约成功
        Db::commit();
        return $this->ajaxReturn(['code' => 1,'msg' => '下单成功','url' => url('trade/index')]);

    }


}
