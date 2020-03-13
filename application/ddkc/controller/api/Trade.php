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
            return $this->ajaxReturn(['code' => 0,'msg' => '场次不存在','url' => '']);
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
            $charge['use_integral']   = -$stage_info['scribe_integral'];
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

    /*------------------------------------------------------ */
    //-- 挂售下单
    /*------------------------------------------------------ */
    public function sell_trade(){
        $userModel = new UsersModel();
        $accountModel = new AccountLogModel();
        $SellTradeMoel = new SellTradeModel();
        $TradingStageModel = new TradingStageModel();
        $settints = settings();
        # 是否登录
        $user = $userModel->info($this->userInfo['user_id']);
        if (!$user) {
            return $this->ajaxReturn(['code' => 0,'msg' => '请先登录','url' => url('passport/login')]);
        }
        $number  = input('number');//出售的ddb
        $stage_id = input('stage_id');
        #场次信息
        $stage_info = $TradingStageModel->where('id',$stage_id)->find();
        if(empty($stage_info)){
            return $this->ajaxReturn(['code' => 0,'msg' => '场次不存在','url' => '']);
        }
        if($user['account']['ddb_money']<$number){
            return $this->ajaxReturn(['code' => 0,'msg' => 'DDB余额不足','url' => '']);
        }
        #扣除手续费后的叮叮
        $service_charge = $number *($settints['service_charge']/100);
        $dingding = $number *(1-($settints['service_charge']/100));

        $daybegin=strtotime(date("Ymd"));
        $dayend=$daybegin+86400;
        #今日售出数量
        $today_sell_num = $SellTradeMoel->where('sell_start_time','between',[$daybegin,$dayend])->count();
        $today_sell_total = $SellTradeMoel->where('sell_start_time','between',[$daybegin,$dayend])->sum('sell_number');
        if($number<$stage_info['trade_min_num']) return $this->ajaxReturn(['code' => 0,'msg' => '该场次最低售出数量为'.$stage_info['trade_min_num'],'url' => '']);
        if($number>$stage_info['trade_max_num']) return $this->ajaxReturn(['code' => 0,'msg' => '该场次最高售出数量为'.$stage_info['trade_max_num'],'url' => '']);
        if($number<$settints['min_number']) return $this->ajaxReturn(['code' => 0,'msg' => '叮叮每次最低挂售'.$settints['min_number'],'url' => '']);
        if($number>$settints['max_number']) return $this->ajaxReturn(['code' => 0,'msg' => '叮叮每次最高挂售'.$settints['max_number'],'url' => '']);
        if($today_sell_num>=$settints['max_second']) return $this->ajaxReturn(['code' => 0,'msg' => '叮叮每天最多挂售'.$settints['max_second'].'次','url' => '']);
        if(($today_sell_total+$number)>$settints['max_total_number']) return $this->ajaxReturn(['code' => 0,'msg' => '叮叮每天最多挂售总额为'.$settints['max_total_number'],'url' => '']);


        Db::startTrans();
        $addData = [
            'sell_user_id'=>$user['user_id'],
            'sell_stage_id'=>$stage_id,
            'sell_number'=>$dingding,
            'sell_status'=>0,
            'sell_start_time'=>time(),
            'old_ddb_money'=>$number,
            'service_charge'=>$settints['service_charge']
        ];
        $res = $SellTradeMoel->create($addData);
        if (!$res) {
            Db::rollback();
            return $this->ajaxReturn(['code' => 0,'msg' => '挂售失败','url' => '']);
        }
        #挂售成功后
        if($res){
            #除手续费
            $charge['ddb_money']   = -$service_charge;
            $charge['change_desc'] = '扣除挂售手续费';
            $charge['change_type'] = 12;
            $res1 =$accountModel->change($charge, $user['user_id'], false);
            if(!$res1){
                Db::rollback();
                return $this->ajaxReturn(['code' => 0,'msg' => '扣除挂售手续费失败','url' => '']);
            }
            #扣除DDB
            $charge['ddb_money']   = -$dingding;
            $charge['change_desc'] = '扣除挂售的DDB';
            $charge['change_type'] = 12;
            $res1 =$accountModel->change($charge, $user['user_id'], false);
            if(!$res1){
                Db::rollback();
                return $this->ajaxReturn(['code' => 0,'msg' => '扣除挂售的DDB失败','url' => '']);
            }

        }

        # 预约成功
        Db::commit();
        return $this->ajaxReturn(['code' => 1,'msg' => '挂售成功','url' => url('trade/index')]);

    }
}
