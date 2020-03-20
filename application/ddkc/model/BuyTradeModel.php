<?php
namespace app\ddkc\model;
use think\Db;
use app\BaseModel;
use think\facade\Cache;
use think\cache\driver\Redis;
use app\member\model\UsersModel;
use app\ddkc\model\SellTradeModel;
use app\ddkc\model\TradingStageModel;
use app\member\model\AccountLogModel;
use app\member\model\DdBanRecordModel;
//*------------------------------------------------------ */
//-- 会员等级表
/*------------------------------------------------------ */
class BuyTradeModel extends BaseModel
{
    protected $table = 'dd_buy_trade';
    public  $pk = 'id';
    protected static $mkey = 'dd_buy_trade_list';

    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm(self::$mkey);
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getRows()
    {
        $data = Cache::get(self::$mkey);
        if (empty($data) == false) {
            return $data;
        }
        $rows = $this->field('*')->order('id DESC')->select()->toArray();
        foreach ($rows as $row) {
            $data[$row['id']] = $row;
        }
        Cache::set(self::$mkey, $data, 600);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 开奖
    /*------------------------------------------------------ */
    public function lottery(){
        $TradingStageModel = new TradingStageModel();
        $SellTradeModel = new SellTradeModel();
        $redis = new Redis();
        $HandleName= 'buyHandle';
        $buyCount = $redis->lSize($HandleName);
        if($buyCount==0){
            return '';
        }
        $time = time();
        $stageList = $TradingStageModel->where('is_lottery',0)->select();
        if(empty($stageList))  return '';
        $stageInfo = [];
        #获取当前开奖的区间
        foreach ($stageList as $k=>$v){
            $stageTime = $v['trade_end_time'];
            $stageTime = strtotime(date('Y-m-d '.$stageTime))+(5*60);
            if($time>$stageTime){
                $stageInfo = $v;
            }
        }
        if(empty($stageInfo)){
            return '';
        }
        #获取队列中的抢购记录
        $ids = [];
        for ($i=0;$i<=$buyCount-1;$i++){
//            $id = $redis->lGet('buyHandle',$i);
            $id = $redis->rPop('buyHandle');
            array_push($ids,$id);
        }
        #打乱数组
        shuffle($ids);
        shuffle($ids);
        #循环匹配出售和预约记录
        foreach ($ids as $k=>$v){
            #获取与预约不是同一用户的出售记录
            $buy_info =$this->where('id',$v)->find();
            #随机获取当前区间的记录
            $sell_where[] = ['sell_stage_id','eq',$stageInfo['id']];
            $sell_where[] = ['sell_status','eq',0];
            $sell_where[] = ['sell_user_id','neq',$buy_info['buy_user_id']];

            $sell_info = $SellTradeModel->where($sell_where)->orderRaw('rand()')->find();
            if(empty($sell_info)==false){
                #更新出售表
                $sell_save['buy_id'] = $v;
                $sell_save['matching_time'] = $time;
                $sell_save['sell_status'] = 1;
                $SellTradeModel->where('id',$sell_info['id'])->update($sell_save);

                #更新预约表
                $buy_save['sell_id'] = $sell_info['id'];
                $buy_save['buy_status'] =2;
                $buy_save['panic_end_time'] = $time;
                $this->where('id',$v)->update($buy_save);
            }else{
                #未匹配中
                #更新预约表
                $buy_save['sell_id'] = $sell_info['id'];
                $buy_save['buy_status'] = 3;
                $buy_save['panic_end_time'] = $time;
                $this->where('id',$v)->update($buy_save);
            }
        }
    }

    public function getIds($HandleName){
        $redis = new Redis();
        #获取队列中的抢购记录
        $buyCount =  $redis->lSize($HandleName);;
        $ids = [];
        for ($i=0;$i<=$buyCount-1;$i++){
            $id = $redis->Lget($HandleName,$i);
            array_push($ids,$id);
        }
        return $ids;
    }

    /*------------------------------------------------------ */
    //-- 自动取消
    /*------------------------------------------------------ */
    public  function  AutomaticCancellation(){
        $accountModel = new AccountLogModel();
        $DdBanRecordModel = new DdBanRecordModel();
        $SellTradeModel = new SellTradeModel();
        $setting = settings();
        $time = time();
        #获取所有代付款的订单
        $list = $SellTradeModel->where('sell_status',1)->select();
        if(empty($list)==false){
            Db::startTrans();
            foreach ($list as $k=>$v){
                $is_overtime = 0; //是否超时  0为未超时 1为已超时
                #是否延迟过
                if($v['is_delay']==1){
                    #延迟
                    if(($time-$v['matching_time'])>(($setting['cancel_time']+$setting['delay_time'])*60)){
                        $is_overtime = 1;
                    }
                }else{
                    #未延迟
                    if(($time-$v['matching_time'])>($setting['cancel_time']*60)){
                        $is_overtime = 1;
                    }
                }

                if($is_overtime==1){
                    #已超时,取消订单
                    $sell_save['sell_status'] = 5;
                    $sell_save['cancellation_time'] = $time;
                    $sell_save['sell_end_time'] = $time;
                    $res = $SellTradeModel->where('id',$v['id'])->update($sell_save);
                    if(!$res){
                        Db::rollback();
                    }
                    #返还卖家DDB
                    $charge['ddb_money']   =$v['old_ddb_money'];
                    $charge['change_desc'] = '订单取消,返还DDB';
                    $charge['change_type'] = 12;
                    $res1 =$accountModel->change($charge, $v['sell_user_id'], false);
                    if(!$res1){
                        Db::rollback();
                    }
                    #对买家封号处理
                    $buy_info = $this->where('id',$v['buy_id'])->find();
                    $res2 = $DdBanRecordModel->ban_user($buy_info['buy_user_id'],'交易时间内未付款',$v['id']);
                    if(!$res2){
                        Db::rollback();
                    }

                }
            }
            Db::commit();
        }else{
            return true;
        }

    }
    /*------------------------------------------------------ */
    //-- 自动完成
    /*------------------------------------------------------ */
    public function AutoCompletion(){
        $accountModel = new AccountLogModel();
        $DdBanRecordModel = new DdBanRecordModel();
        $SellTradeModel = new SellTradeModel();
        $setting = settings();
        $time = time();
        #获取所有待确认的订单
        $list = $SellTradeModel->where('sell_status',2)->select();
        if(empty($list)==false){
            Db::startTrans();
            foreach ($list as $k=>$v){
                if(($time-$v['payment_time'])>($setting['complete_time']*60)){
                    #超时自动完成
                    $sell_save['sell_status'] = 4;
                    $sell_save['sell_end_time'] = $time;
                    $res = $SellTradeModel->where('id',$v['id'])->update($sell_save);
                    if(!$res){
                        Db::rollback();
                    }

                    #更新买家账户DDB
                    $buy_info = $this->where('id',$v['buy_id'])->find();
                    $charge['balance_money']   =$v['sell_number'];
                    $charge['change_desc'] = '订单完成';
                    $charge['change_type'] = 13;
                    $res1 =$accountModel->change($charge, $buy_info['buy_user_id'], false);
                    if(!$res1){
                        Db::rollback();
                    }
                }
            }
            Db::commit();
        }else{
            return true;
        }
    }
}
