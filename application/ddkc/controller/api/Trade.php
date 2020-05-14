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
use app\ddkc\model\PaymentModel;
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
        $SellTradeModel = new SellTradeModel();
        $model = new TradingStageModel;
        $this->sqlOrder = 'id ASC';
        $where_stage[] = ['isputaway' ,'eq' ,1];
        $data = $this->getPageList($model,$where_stage);

        #获取今日起始时间
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $time = time();
        $setting = settings();
        if (count($data['list']) > 0) {
            foreach ($data['list'] as $key => $value) {
                $where = [];
                $img = '';
                $status = 0;
                #1为可预约 2为已预约  3为可抢购 4为抢购中 5为已过期
                $start_time =strtotime(date('Y-m-d '.$value['trade_start_time']));
                $end_time =strtotime(date('Y-m-d '.$value['trade_end_time']));
                #获取今日当前区间的订单
                $where[] = ['buy_stage_id','=',$value['id']];
                $where[] = ['buy_start_time','between',[$beginToday,$endToday]];
                $where[] = ['buy_user_id','=',$this->userInfo['user_id']];
                $buy_info = $this->Model->where($where)->find();
                if(empty($buy_info)){
                    #今日没预约过
                    if($time<($start_time-($setting['stop_booking_time']*60))){
                        #可预约
                        $status = 1;
                        if(empty($setting['subscribe_img1'])){
                            $img = '/static/dingding/images/index08.png';
                        }else{
                            $img = $setting['subscribe_img1'];
                        }
                    }else{
                        #预约已过期
                        $status = 5;
                        if(empty($setting['be_overdue_img'])){
                            $img = '/static/dingding/images/index10.png';
                        }else{
                            $img = $setting['be_overdue_img'];
                        }
                    }
                }else{
                    #今日有预约过
                    if($buy_info['buy_status']==0){
                        $ids = $this->Model->getIds('buyHandle');
                        if(in_array($buy_info['id'],$ids)){
                          #抢购中
                            $status = 4;
                            if(empty($setting['panic_buying_img2'])){
                                $img = '/static/dingding/images/index03.png';
                            }else{
                                $img = $setting['panic_buying_img2'];
                            }
                        }else{
                            if($time>$start_time&&$time<($start_time+($setting['lottery_time']*60))){
                                #可抢购
                                $status = 3;
                                if(empty($setting['panic_buying_img1'])){
                                    $img = '/static/dingding/images/index02.png';
                                }else{
                                    $img = $setting['panic_buying_img1'];
                                }
                            }else{
                                if($time<$start_time){
                                    #已预约
                                    $status = 2;
                                    if(empty($setting['subscribe_img2'])){
                                        $img = '/static/dingding/images/index06.png';
                                    }else{
                                        $img = $setting['subscribe_img2'];
                                    }
                                    if(($start_time-($setting['down_time']*60))<$time){
                                        $data['list'][$key]['down_time_date'] = gmdate('H:i:s',$start_time-$time);
                                        $data['list'][$key]['down_time'] = $start_time-$time;
                                    }
                                }else if($time>($start_time+($setting['lottery_time']*60))){
                                    #预约已过期
                                    $status = 5;
                                    if(empty($setting['be_overdue_img'])){
                                        $img = '/static/dingding/images/index10.png';
                                    }else{
                                        $img = $setting['be_overdue_img'];
                                    }
                                }
                            }
                        }
                    }else if($buy_info['buy_status']==2){
                        #预约过期
                        $status = 5;
                        if(empty($setting['be_overdue_img'])){
                            $img = '/static/dingding/images/index10.png';
                        }else{
                            $img = $setting['be_overdue_img'];
                        }
                    }else if($buy_info['buy_status']==3){
                        #预约过期
                        $status = 5;
                        if(empty($setting['be_overdue_img'])){
                            $img = '/static/dingding/images/index10.png';
                        }else{
                            $img = $setting['be_overdue_img'];
                        }
                    }else if($buy_info['buy_status']==4){
                        #预约过期
                        $status = 5;
                        if(empty($setting['be_overdue_img'])){
                            $img = '/static/dingding/images/index10.png';
                        }else{
                            $img = $setting['be_overdue_img'];
                        }
                    }

                }
                $data['list'][$key]['stage_img'] = $value['stage_img'];
                $data['list'][$key]['img'] = $img;
                $data['list'][$key]['buy_id'] = $buy_info['id'];
                $data['list'][$key]['buy_status'] = $status;
            }
        }
        return $this->ajaxReturn($data);
    }

    /*------------------------------------------------------ */
    //-- 获取交易区间列表2
    /*------------------------------------------------------ */
    public function getTradingStageList2(){
        $SellTradeModel = new SellTradeModel();
        $model = new TradingStageModel;
        $number = input('number');
        $this->sqlOrder = 'id ASC';
        $where_stage[] = ['isputaway' ,'eq' ,1];
        $where_stage[] = ['trade_min_num' ,'ELT' ,$number];
        $where_stage[] = ['trade_max_num' ,'EGT' ,$number];

        $data = $this->getPageList($model,$where_stage);
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
        $PaymentModel = new PaymentModel();
        # 是否登录
        $user = $userModel->info($this->userInfo['user_id']);
        if (!$user) {
            return $this->ajaxReturn(['code' => 0,'msg' => '请先登录','url' => url('passport/login')]);
        }
        $bank_info =  $bank_info = $PaymentModel->get_payment($this->userInfo['user_id'],1);
        if($bank_info['status']!=1){
            return $this->ajaxReturn(['code' => 0,'msg' => '请先上传银行卡收款信息']);
        }
        if($user['role']['role_id']==0){
            return $this->ajaxReturn(['code' => 0,'msg' => '请先实名认证和至少上传两种收款信息']);
        }
        $stage_id = input('stage_id');
        $stage_info = $TradingStageModel->where('id',$stage_id)->find();
        if(empty($stage_info)){
            return $this->ajaxReturn(['code' => 0,'msg' => '场次不存在','url' => '']);
        }
        $stageTime = strtotime(date('Y-m-d '.$stage_info['trade_start_time']))-(settings('stop_booking_time')*60);
        if(time()>$stageTime){
            return $this->ajaxReturn(['code' => 0,'msg' => '预约时间已过,不可预约','url' => '']);
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
        return $this->ajaxReturn(['code' => 1,'msg' => '预约成功','url' => url('trade/index')]);

    }

    /*------------------------------------------------------ */
    //-- 挂售下单
    /*------------------------------------------------------ */
    public function sell_trade(){
        $userModel = new UsersModel();
        $accountModel = new AccountLogModel();
        $SellTradeMoel = new SellTradeModel();
        $TradingStageModel = new TradingStageModel();
        $PaymentModel = new PaymentModel();
        $settints = settings();
        # 是否登录
        $user = $userModel->info($this->userInfo['user_id']);
        if (!$user) {
            return $this->ajaxReturn(['code' => 0,'msg' => '请先登录','url' => url('passport/login')]);
        }
        $bank_info =  $bank_info = $PaymentModel->get_payment($this->userInfo['user_id'],1);
        if($bank_info['status']!=1){
            return $this->ajaxReturn(['code' => 0,'msg' => '请先上传银行卡收款信息']);
        }
        if($user['role']['role_id']==0){
            return $this->ajaxReturn(['code' => 0,'msg' => '请先实名认证和至少上传两种收款信息']);
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
        if(time()>strtotime(date('Y-m-d '.$stage_info['trade_start_time']))){
            return $this->ajaxReturn(['code' => 0,'msg' => '该场次今日交易时间已过','url' => '']);
        }
        #扣除手续费后的叮叮
        $service_charge = $number *($settints['service_charge']/100);
        $dingding = $number *(1-($settints['service_charge']/100));
        $daybegin=strtotime(date("Ymd"));
        $dayend=$daybegin+86400;
        #今日售出数量
        $today_sell_num = $SellTradeMoel->where('sell_start_time','between',[$daybegin,$dayend])->where('sell_user_id',$this->userInfo['user_id'])->count();
        $today_sell_total = $SellTradeMoel->where('sell_start_time','between',[$daybegin,$dayend])->where('sell_user_id',$this->userInfo['user_id'])->sum('sell_number');
        if($dingding<$stage_info['trade_min_num']) return $this->ajaxReturn(['code' => 0,'msg' => '该场次扣除手续费后最低售出数量为'.$stage_info['trade_min_num'],'url' => '']);
        if($dingding>$stage_info['trade_max_num']) return $this->ajaxReturn(['code' => 0,'msg' => '该场次扣除手续费后最高售出数量为'.$stage_info['trade_max_num'],'url' => '']);
        if($dingding<$settints['min_number']) return $this->ajaxReturn(['code' => 0,'msg' => '叮叮扣除手续费后每次最低挂售'.$settints['min_number'],'url' => '']);
        if($dingding>$settints['max_number']) return $this->ajaxReturn(['code' => 0,'msg' => '叮叮扣除手续费后每次最高挂售'.$settints['max_number'],'url' => '']);
        if($today_sell_num>=$settints['max_second']) return $this->ajaxReturn(['code' => 0,'msg' => '叮叮扣除手续费后每天最多挂售'.$settints['max_second'].'次','url' => '']);
        if(($today_sell_total+$dingding)>$settints['max_total_number']) return $this->ajaxReturn(['code' => 0,'msg' => '叮叮扣除手续费后每天最多挂售总额为'.$settints['max_total_number'],'url' => '']);


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
        #获取今日订单
        $data = $this->getPageList($BuyTradeModel,$viewObj);

        $ids = $BuyTradeModel->getIds('buyHandle');
        $time = time();
        $setting = settings();
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
//        $fino = $BuyTradeModel->where('id')->find();
        #查看队列中是否已存在
        $ids = $BuyTradeModel->getIds('buyHandle');
        if(in_array($id,$ids)){
            return $this->ajaxReturn(['code' => 0,'msg' => '不可重复抢购']);
        }
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
            if($sell_info['sell_status']==5){
                return $this->ajaxReturn(['code' => 0,'msg' => '此订单取消','url' => '']);
            }else{
                return $this->ajaxReturn(['code' => 0,'msg' => '此订单已付过款','url' => '']);
            }
        }

        $file = $_FILES['pay_img'];
        $ios_file = input('pay_img');
        if(empty($file['name'])&&empty($ios_file)){
            return $this->ajaxReturn(['code' => 0,'msg' => '请上传付款截图','url' => '']);
        }

        #通过file提交的
        if($file&&empty($ios_file)){
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
            return $this->ajaxReturn(['code' => 0,'msg' => '该订单已完成']);
        }
        if($sell_info['sell_status']==5){
            return $this->ajaxReturn(['code' => 0,'msg' => '该订单已超时取消']);
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

    /*------------------------------------------------------ */
    //-- 获取开奖结果
    /*------------------------------------------------------ */
    public function lottery_results(){
        $TradingStageModel = new TradingStageModel();
        $BuyTradeModel = new BuyTradeModel();
        $setting = settings();
        $time = time();
        $stage_where[] = ['isputaway','=',1];
        $stageList = $TradingStageModel->where($stage_where)->order('id desc')->select();
        #获取今日起始时间
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        #获取当前开奖的区间
        $lottery_status = 0;
        foreach ($stageList as $k=>$v){
            $stageTime = $v['trade_end_time'];
            #结束抢购的时间
            $stratTime = strtotime(date('Y-m-d '.$stageTime));
            #开奖时间
            $centreTime = strtotime(date('Y-m-d '.$stageTime))+($setting['lottery_time']*60);
            #结束时间
            $endTime = strtotime(date('Y-m-d '.$stageTime))+($setting['lottery_time']*60*2);
            if($time>$stratTime&&$time<$centreTime){
                #等待开奖
                #获取今天当前区间的预约
                $buy_where = [];
                $buy_where[] = ['buy_user_id','=',$this->userInfo['user_id']];
                $buy_where[] = ['buy_stage_id','=',$v['id']];
                $buy_where[] = ['buy_start_time','between',[$beginToday,$endToday]];
                $buy_info = [];
                $buy_info =$BuyTradeModel->where($buy_where)->find();
                if($buy_info){
                    $ids = $BuyTradeModel->getIds('buyHandle');
                    if(in_array($buy_info['id'],$ids)){
                        #等待开奖
                        $lottery_status = 1;
                    }
                }
                break;
            }
            if($time>=$centreTime&&$time<$endTime){
                #开奖结束
                $buy_where = [];
                $buy_where[] = ['buy_user_id','=',$this->userInfo['user_id']];
                $buy_where[] = ['buy_stage_id','=',$v['id']];
                $buy_where[] = ['buy_start_time','between',[$beginToday,$endToday]];
                $buy_info = [];
                $buy_info = $BuyTradeModel->where($buy_where)->find();
                if($buy_info){
                    if($buy_info['buy_status']==2){
                        #中奖
                        $lottery_status = 2;
                    }else if($buy_info['buy_status']==3){
                        #未中奖
                        $lottery_status = 3;
                    }
                }
                break;
            }
        }
        $data['buy_info'] = $buy_info;
        $data['lottery_status'] = $lottery_status;

        return $this->ajaxReturn($data);
    }
    /*------------------------------------------------------ */
    //-- 获取开奖结果
    /*------------------------------------------------------ */
    public function lottery_results2(){
        $TradingStageModel = new TradingStageModel();
        $BuyTradeModel = new BuyTradeModel();
        $setting = settings();
        $lottery_status = 0;


        #获取今日起始时间
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $buy_where[] = ['buy_user_id','=',$this->userInfo['user_id']];
        $buy_where[] = ['buy_start_time','between',[$beginToday,$endToday]];
        $list =$BuyTradeModel->where($buy_where)->order('id desc')->select()->toArray();
        if($list){
            foreach ($list as $k=>$v){
             #中奖与未中奖
             if($v['buy_status']==2||$v['buy_status']==3){
                 #是否查看过
                if($v['is_see']==0){
                    if($v['buy_status']==2){
                        #中奖
                        $lottery_status = 2;
                    }else if($v['buy_status']==3){
                        #未中奖
                        $lottery_status = 3;
                    }
                    $data['buy_info'] = $v;
                    break;
                }
             }
             #等待开奖
             if($v['buy_status']==0){
                 $ids = $BuyTradeModel->getIds('buyHandle');
                 if(in_array($v['id'],$ids)){
                     #等待开奖
                     $lottery_status = 1;
                     #距离开奖的时间
                     $stage_time = $TradingStageModel->where('id',$v['buy_stage_id'])->value('trade_start_time');
                     $data['time_difference']= ( strtotime(date('Y-m-d '.$stage_time))+($setting['lottery_time']*60)-time())*1000;
                     $data['buy_info'] = $v;
                     break;
                 }
             }
            }
        }
        $data['lottery_status'] = $lottery_status;
        return $this->ajaxReturn($data);

    }

    /*------------------------------------------------------ */
    //-- 更新当前记录查看状态
    /*------------------------------------------------------ */
    public function update_see_status(){
        $BuyTradeModel = new BuyTradeModel();
        $id = input('id');
        $type= input('type');
        $res = $BuyTradeModel->where('id',$id)->update(['is_see'=>1]);
        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '更新成功']);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '延迟交易成功','url' =>'']);
        }
    }

    /*------------------------------------------------------ */
    //-- 轮询获取开奖结果
    /*------------------------------------------------------ */
    public function polling(){
        $BuyTradeModel = new BuyTradeModel();
        $id = input('id');
        $info =  $BuyTradeModel->where('id',$id)->find();
        return $this->ajaxReturn($info);
    }

    /*------------------------------------------------------ */
    //-- 取消申诉
    /*------------------------------------------------------ */
    public function cancel_appeal(){
        $SellTradeModel = new SellTradeModel();
        $BuyTradeModel = new BuyTradeModel();
        $accountModel = new AccountLogModel();

        $id = input('id');
        $time = time();
        $sell_info = $SellTradeModel->where('id',$id)->find();
        $buy_info = $BuyTradeModel->where('id',$sell_info['buy_id'])->find();
        Db::startTrans();
        $sell_save['sell_status'] = 4;
        $sell_save['sell_end_time'] =$time;
        $res = $SellTradeModel->where('id',$id)->update($sell_save);
        #更新叮叮到买家账户
        if($res){
            #更新买家叮叮
            $charge['balance_money']   = $sell_info['sell_number'];
            $charge['change_desc'] = '交易成功';
            $charge['change_type'] = 13;
            $res1 =$accountModel->change($charge, $buy_info['buy_user_id'], false);
            if(!$res1){
                Db::rollback();
                return $this->ajaxReturn(['code' => 0,'msg' => '更新DDB失败','url' => '']);
            }
        }else{
            Db::rollback();
            return $this->ajaxReturn(['code' => 0,'msg' => '更新订单失败','url' => '']);
        }

        Db::commit();
        return $this->ajaxReturn(['code' => 1,'msg' => '操作成功','url' => url('trade/dd_wallet')]);
    }
}
