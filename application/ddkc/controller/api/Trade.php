<?php

namespace app\ddkc\controller\api;
use think\Db;
use app\ApiController;
use app\member\model\UsersModel;
use app\member\model\AccountLogModel;
use app\ddkc\model\BuyTradeModel;
use app\ddkc\model\SellTradeModel;
use app\ddkc\model\TradingStageModel;
use think\cache\driver\Redis;
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
            'buy_start_time'=>time(),
            'buy_order_sn'=>getOrderSn()
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
            'service_charge'=>$service_charge,
            'sell_order_sn'=>getOrderSn()
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
        return $this->ajaxReturn(['code' => 1,'msg' => '挂售成功','url' => url('trade/dd_wallet')]);

    }

    /*------------------------------------------------------ */
    //-- 获取预约记录
    /*------------------------------------------------------ */
    public function getBuyList(){
        $userModel = new UsersModel();
        $BuyTradeModel = new BuyTradeModel();
        $SellTradeMoel = new SellTradeModel();
        $TradingStageModel = new TradingStageModel();
        $this->order_by = 'id';
        $this->sort_by = 'DESC';
        $where[] =  ['buy_user_id' ,'eq' ,$this->userInfo['user_id']];
        $viewObj = $BuyTradeModel->where($where)->order('id desc');
        $data = $this->getPageList($BuyTradeModel,$viewObj);
        $ids = $BuyTradeModel->getIds('buyHandle');
        $time = time();
        $status = ['待出售','待付款','已付款','申诉中','交易成功','交易失败'];
        foreach ($data['list'] as $k=>$v){

            $stage_info = $TradingStageModel->where('id',$v['buy_stage_id'])->find();
            $start_time =strtotime(date('Y-m-d '.$stage_info['trade_start_time']));
            $end_time =strtotime(date('Y-m-d '.$stage_info['trade_end_time']));
            $data['list'][$k]['stage_name'] = $stage_info['stage_name'];
            $data['list'][$k]['buy_start_time_date'] = date('Y-m-d H:i:s',$v['buy_start_time']);
            if($v['buy_status']==0){
                #已预约
                $data['list'][$k]['is_buying'] =0;
                if(in_array($v['id'],$ids)){
                    #抢购中
                    $data['list'][$k]['is_buying'] =1;
                }else{
                   if($time>$start_time&&$end_time>$time){
                       #可抢购
                       $data['list'][$k]['is_buying'] =2;
                   }
                }
            }
            if($v['buy_status']==2){
                $sell_info = $SellTradeMoel->where('id',$v['sell_id'])->find();
                $data['list'][$k]['status_str'] =$status[$sell_info['sell_status']];
            }
        }
        return $this->ajaxReturn($data);
    }
    /*------------------------------------------------------ */
    //-- 获取售出记录
    /*------------------------------------------------------ */
    public function getSellList(){
        $userModel = new UsersModel();
        $BuyTradeModel = new BuyTradeModel();
        $SellTradeModel = new SellTradeModel();
        $setting = settings();
        $this->order_by = 'id';
        $this->sort_by = 'DESC';
        $type = input('type');
        if($type==1){
        #未出售
            $where[] =  ['sell_status' ,'eq' ,0];
        }else if($type==2){
        #出售中
            $where[] =  ['sell_status' ,'in' ,[1,2,3]];
        }else if($type==3){
        #交易记录
            $where[] =  ['sell_status' ,'in' ,[4,5]];
        }
        $where[] =  ['sell_user_id' ,'eq' ,$this->userInfo['user_id']];
        $viewObj = $SellTradeModel->where($where)->order('id desc');
        $data = $this->getPageList($SellTradeModel,$viewObj);
        foreach ($data['list'] as $k=>$v){
            $data['list'][$k]['sell_start_time_date'] = date('Y-m-d H:i:s',$v['sell_start_time']);
            $data['list'][$k]['matching_time_date'] = date('Y-m-d H:i:s',$v['matching_time']);
            $data['list'][$k]['payment_time_date'] = date('Y-m-d H:i:s',$v['payment_time']);
            $data['list'][$k]['complain_time_date'] = date('Y-m-d H:i:s',$v['complain_time']);
            $data['list'][$k]['sell_end_time_date'] = date('Y-m-d H:i:s',$v['sell_end_time']);
            if($v['sell_status']>0){
                $buy_info = $BuyTradeModel->where('id',$v['buy_id'])->find();
                $buy_user = $userModel->info($buy_info['buy_user_id']);
                $data['list'][$k]['buy_user'] = $buy_user;
            }
            if($v['sell_status']==1){

                #待付款倒计时
                $payment_time = 0;
                if($v['sell_status']==1){
                    $is_payment = 1;

                    if($v['is_delay']==1){
                        $payment_time = $v['matching_time'] + ($setting['cancel_time']*60) + ($setting['delay_time']*60) - time();
                    }else{
                        $payment_time = $v['matching_time'] + ($setting['cancel_time']*60)  - time();
                    }

                    if(!($payment_time>0)){
                        $is_payment =2;
                    }
                }else{
                    $is_payment =2;
                }
                $data['list'][$k]['is_payment'] = $is_payment;
                $data['list'][$k]['payment_times'] = $payment_time;
                $data['list'][$k]['payment_time_gmdate'] = gmdate('H:i:s',$payment_time);
            }
            if($v['sell_status']==2){
                #待确认倒计时
                $confirm_time = 0;
                if($v['sell_status']==2){
                    $is_confirm = 1;
                    $confirm_time = $v['payment_time'] + ($setting['complete_time']*60)  - time();
                    if(!($confirm_time>0)){
                        $is_confirm =2;
                    }
                }else{
                    $is_confirm =2;
                }
                $data['list'][$k]['is_confirm'] = $is_confirm;
                $data['list'][$k]['confirm_time'] = $confirm_time;
                $data['list'][$k]['confirm_time_gmdate'] = gmdate('H:i:s',$confirm_time);
            }

        }
        return $this->ajaxReturn($data);
    }

    /*------------------------------------------------------ */
    //-- 抢购
    /*------------------------------------------------------ */
    public function PanicBuying(){
        $userModel = new UsersModel();
        $BuyTradeModel = new BuyTradeModel();
        $redis = new Redis();
        $id = input('id');
        #将抢购id存入队列
        $res = $redis->rPush('buyHandle',$id);
        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '操作成功','url' => url('trade/dd_wallet')]);
        }
    }

    /*------------------------------------------------------ */
    //-- 打款
    /*------------------------------------------------------ */
    public function make_money(){
        $userModel = new UsersModel();
        $BuyTradeModel = new BuyTradeModel();
        $SellTradeModel = new SellTradeModel();
        $id = input('id');

        $sell_info = $SellTradeModel->where('id',$id)->find();
        if($sell_info['sell_status']==0){
            return $this->ajaxReturn(['code' => 0,'msg' => '当前状态无法付款','url' => '']);
        }else if($sell_info['sell_status']>1){
            return $this->ajaxReturn(['code' => 0,'msg' => '此订单已付过款','url' => '']);
        }

        $file = $_FILES['pay_img'];
        $ios_file = input('pay_img');
        if(empty($file['name'])&&empty($ios_file)){
            return $this->ajaxReturn(['code' => 0,'msg' => '请上传付款截图','url' => '']);
        }

        #通过file提交的
        if($file){
            #上传打款凭证
            $path = upload_img('pay_img');
            if($path){
                $data['pay_img'] = $path;
            }
        }

        #IOS直接上传的地址
        if($ios_file){
            $data['pay_img'] = $ios_file;
        }
        $sell_save['sell_status'] = 2;
        $sell_save['payment_time'] = time();
        $sell_save['pay_img'] = $data['pay_img'];
        $res = $SellTradeModel->where('id',$id)->update($sell_save);
        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '付款成功','url' =>url('trade/dd_wallet')]);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '付款失败']);
        }
    }

    /*------------------------------------------------------ */
    //-- 确认收款
    /*------------------------------------------------------ */
    public  function  surePay(){
        $accountModel = new AccountLogModel();
        $BuyTradeModel = new BuyTradeModel();
        $SellTradeModel = new SellTradeModel();
        $id = input('id');
        $sell_info = $SellTradeModel->where('id',$id)->find();
        $buy_info = $BuyTradeModel->where('id',$sell_info['buy_id'])->find();
        if($sell_info['sell_status']==4){
            return $this->ajaxReturn(['code' => 0,'msg' => '该订单已付款']);
        }

        $sell_save['sell_status'] = 4;
        $sell_save['sell_end_time'] = time();
        Db::startTrans();
        $res = $SellTradeModel->where('id',$id)->update($sell_save);
        if($res){
            #更新买家叮叮
            $charge['balance_money']   = $sell_info['sell_number'];
            $charge['change_desc'] = '交易成功';
            $charge['change_type'] = 13;
            $res1 =$accountModel->change($charge, $buy_info['buy_user_id'], false);
            if(!$res1){
                Db::rollback();
                return $this->ajaxReturn(['code' => 0,'msg' => '更新买家叮叮失败','url' => '']);
            }
        }else{
            Db::rollback();
            return $this->ajaxReturn(['code' => 0,'msg' => '确认收款失败','url' => '']);
        }
        Db::commit();
        return $this->ajaxReturn(['code' => 1,'msg' => '交易成功','url' => url('trade/dd_wallet')]);
    }

    /*------------------------------------------------------ */
    //-- 申诉
    /*------------------------------------------------------ */
    public function appeal(){
        $SellTradeModel = new SellTradeModel();
        $id = input('id');
        $reason = input('reason');

        if(empty($reason)){
            return $this->ajaxReturn(['code' => 0,'msg' => '申诉理由不能为空']);
        }

        $sell_info = $SellTradeModel->where('id',$id)->find();
        if($sell_info['sell_status']==4){
            return $this->ajaxReturn(['code' => 0,'msg' => '该订单已完成,无法申诉']);
        }elseif($sell_info['sell_status']==5){
            return $this->ajaxReturn(['code' => 0,'msg' => '该订单已失败,无法申诉']);
        }

        $sell_save['sell_status'] = 3;
        $sell_save['complain_time'] = time();
        $sell_save['appeal_reason'] = $reason;

        $res = $SellTradeModel->where('id',$id)->update($sell_save);
        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '申诉成功','url' => url('trade/dd_wallet')]);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '申诉失败']);
        }

    }

    /*------------------------------------------------------ */
    //-- 延迟交易
    /*------------------------------------------------------ */
    public function delay(){
        $SellTradeModel = new SellTradeModel();
        $id = input('id');

        $sell_info = $SellTradeModel->where('id',$id)->find();
        if($sell_info['is_delay']==1){
            return $this->ajaxReturn(['code' => 0,'msg' => '每笔订单只能延迟一次']);
        }
        if($sell_info['sell_status']!=1){
            return $this->ajaxReturn(['code' => 0,'msg' => '只有待付款的订单才能延迟交易']);
        }

        $sell_save['is_delay'] = 1;
        $sell_save['delay_time'] = time();

        $res = $SellTradeModel->where('id',$id)->update($sell_save);
        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '延迟交易成功','url' =>'']);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '延迟交易失败']);
        }

    }
}
