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
use app\mainadmin\model\MessageModel;
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
    public function PanicBuying(){
        $accountModel = new AccountLogModel();
        $TradingStageModel = new TradingStageModel();
        $SellTradeModel = new SellTradeModel();
        $redis = new Redis();

        $setting = settings();
        $time = time();
        $show_end_date = $time+(24*60*60*30);
        $stage_where[] = ['is_lottery','=',0];
        $stage_where[] = ['isputaway','=',1];
        $stageList = $TradingStageModel->where($stage_where)->order('id desc')->select();
        if(empty($stageList))  return '';
        $stageInfo = [];
        #获取当前开奖的区间
        foreach ($stageList as $k=>$v){
            $stageTime = 0;
            $stageTime = $v['trade_start_time'];
            $stageTime = strtotime(date('Y-m-d '.$stageTime))+($setting['lottery_time']*60);
            if($time>$stageTime){
                $stageInfo = $v;
                break;
            }
        }
        if(empty($stageInfo)){
            return '';
        }
        $HandleName= 'buyHandle';
        $buyCount = $redis->lSize($HandleName);
        if(count($buyCount)==0){
            #没抢购,更新当前抢购区间的状态
            $TradingStageModel->where('id',$stageInfo['id'])->update(['is_overdue'=>1]);
            return '';
        }

        #获取队列中的抢购记录
        $ids = [];
        for ($i=0;$i<=$buyCount-1;$i++){
//            $id = $redis->lGet('buyHandle',$i);
            $id = $redis->rPop('buyHandle');
            array_push($ids,$id);
        }
        $MessageModel = new MessageModel();
        #获取所有有指定用户的售出信息
        $set_sell_where = [];
        $set_sell_where[] = ['sell_status','=',0];
        $set_sell_where[] = ['sell_stage_id','=',$stageInfo['id']];
        $set_sell_where[] = ['reserve_user_id','neq',''];
        $set_sell_list = $SellTradeModel->where($set_sell_where)->select();
        if(empty($set_sell_list)==false){
            foreach ($set_sell_list as  $k=>$v){
                $set_where = [];
                $set_where[] = ['id','in',$ids];
                $set_where[] = ['buy_stage_id','=',$stageInfo['id']];
                $set_where[] = ['buy_status','=',0];
                $set_where[] = ['buy_user_id','=',$v['reserve_user_id']];
                $set_buy_info = [];
                $set_buy_info = $this->where($set_where)->find();
                if(empty($set_buy_info)==false){
                    #更新售出表和卖出表
                    $this->up_order($v['id'],$set_buy_info['id'],$set_buy_info['deduct_integral'],$set_buy_info['buy_user_id'],$accountModel,$SellTradeModel);
                    #给中奖的用户发送通知
                    $MessageModel->sendMessage($set_buy_info['buy_user_id'],1,0,'中奖通知','您的预约已中奖,请按时交易',$show_end_date,3);
                    #删除掉已匹配的用户
                    unset($ids[array_search($set_buy_info['id'],$ids)]);
                }
            }
        }

        #获取所有售出数据
        $sell_where = [];
        $sell_where[] = ['sell_status','=',0];
        $sell_where[] = ['sell_stage_id','=',$stageInfo['id']];
        $sell_list = $SellTradeModel->where($sell_where)->select();
        if(!empty($sell_list)){
            foreach ($sell_list as $k=>$v){
                if(empty($ids)){
                    break;
                }
                $buy_id = '0';
                $buy_info = [];
                if(empty($v['ban_user_id'])){
                    #无禁止用户id
                    $buy_id = $ids[array_rand($ids,1)];
                    if(empty($buy_id)==false){
                        #更新售出表和卖出表
                        $buy_info = $this->where('id',$buy_id)->find();
                        if($buy_info['buy_stage_id']!=$v['sell_stage_id']){
                            unset($ids[array_search($buy_info['id'],$ids)]);
                            continue;
                        }
                        if($buy_info['buy_user_id']==$v['sell_user_id']){
                            #如果卖家跟买家是同一个人 则重新匹配
                            $buy_where = [];
                            $buy_where[] = ['buy_stage_id','eq',$stageInfo['id']];
                            $buy_where[] = ['buy_status','eq',0];
                            $buy_where[] = ['id','in',$ids];
                            $buy_info = [];
                            $buy_info = $this->where($buy_where)->orderRaw('rand()')->find();
                            if(empty($buy_info)==false){
                                $this->up_order($v['id'],$buy_info['id'],$buy_info['deduct_integral'],$buy_info['buy_user_id'],$accountModel,$SellTradeModel);
                                #给中奖的用户发送通知
                                $MessageModel->sendMessage($buy_info['buy_user_id'],1,0,'中奖通知','您的预约已中奖,请按时交易',$show_end_date,3);
                                #删除掉已匹配的用户
                                unset($ids[array_search($buy_info['id'],$ids)]);
                            }
                        }else{
                            $this->up_order($v['id'],$buy_info['id'],$buy_info['deduct_integral'],$buy_info['buy_user_id'],$accountModel,$SellTradeModel);
                            #给中奖的用户发送通知
                            $MessageModel->sendMessage($buy_info['buy_user_id'],1,0,'中奖通知','您的预约已中奖,请按时交易',$show_end_date,3);
                            #删除掉已匹配的用户
                            unset($ids[array_search($buy_info['id'],$ids)]);
                        }

                    }
                }else{
                    #有禁止用户id
                    $buy_where = [];
                    $buy_where[] = ['buy_stage_id','eq',$stageInfo['id']];
                    $buy_where[] = ['buy_status','eq',0];
                    $buy_where[] = ['id','in',$ids];
                    $buy_where[] = ['buy_user_id','neq',$v['ban_user_id']];
                    $buy_info = [];
                    $buy_info = $this->where($buy_where)->orderRaw('rand()')->find();
                    if(empty($buy_info)==false){
                        $this->up_order($v['id'],$buy_info['id'],$buy_info['deduct_integral'],$buy_info['buy_user_id'],$accountModel,$SellTradeModel);
                        #给中奖的用户发送通知
                        $MessageModel->sendMessage($buy_info['buy_user_id'],1,0,'中奖通知','您的预约已中奖,请按时交易',$show_end_date,3);
                        #删除掉已匹配的用户
                        unset($ids[array_search($buy_info['id'],$ids)]);
                    }
                }

            }
        }

        if(!empty($ids)){
            #更新剩余预约记录的状态
            foreach ($ids as $k=>$v){
                $up_buy = [];
                $up_buy['buy_status'] = 3;
                $up_buy['panic_end_time'] = $time;
                $this->where('id',$v)->update($up_buy);
                #给未中奖的用户退信用积分
                $buy_info = [];
                $buy_info = $this->where('id',$v)->find();
                $charge = [];
                $charge['use_integral']   = $buy_info['deduct_integral'];
                $charge['change_desc'] = '开奖结束,返还信用积分';
                $charge['change_type'] = 11;
                $accountModel->change($charge, $buy_info['buy_user_id'], false);
                #给未中奖的用户发送通知
                $up_info = [];
                $up_info =  $this->where('id',$v)->find();
                $MessageModel->sendMessage($up_info['buy_user_id'],1,0,'中奖通知','您的预约未中奖',$show_end_date,3);
            }
        }
        #更新开奖区间状态
        $TradingStageModel->where('id',$stageInfo['id'])->update(['is_lottery'=>1]);
    }
    /*------------------------------------------------------ */
    //-- 开奖
    /*------------------------------------------------------ */
    public function PanicBuying_new(){
        $accountModel = new AccountLogModel();
        $TradingStageModel = new TradingStageModel();
        $SellTradeModel = new SellTradeModel();
        $redis = new Redis();

        $setting = settings();
        $time = time();
        $show_end_date = $time+(24*60*60*30);
        $stage_where[] = ['is_lottery','=',0];
        $stage_where[] = ['isputaway','=',1];
        $stageList = $TradingStageModel->where($stage_where)->order('id desc')->select();
        if(empty($stageList))  return '';
        $stageInfo = [];
        #获取当前开奖的区间
        foreach ($stageList as $k=>$v){
            $stageTime = 0;
            $stageTime = $v['trade_start_time'];
            $stageTime = strtotime(date('Y-m-d '.$stageTime))+($setting['lottery_time']*60);
            if($time>$stageTime){
                $stageInfo = $v;
                break;
            }
        }
        if(empty($stageInfo)){
            return '';
        }
        $HandleName= 'buyHandle';
        $buyCount = $redis->lSize($HandleName);
        if(count($buyCount)==0){
            #没抢购,更新当前抢购区间的状态
            $TradingStageModel->where('id',$stageInfo['id'])->update(['is_overdue'=>1]);
            return '';
        }

        #获取队列中的抢购记录
        $ids = [];
        for ($i=0;$i<=$buyCount-1;$i++){
//            $id = $redis->lGet('buyHandle',$i);
            $id = $redis->rPop('buyHandle');
            array_push($ids,$id);
        }
        $MessageModel = new MessageModel();
        #获取所有有指定用户的售出信息
        $set_sell_where = [];
        $set_sell_where[] = ['sell_status','=',0];
        $set_sell_where[] = ['sell_stage_id','=',$stageInfo['id']];
        $set_sell_where[] = ['reserve_user_id','neq',''];
        $set_sell_list = $SellTradeModel->where($set_sell_where)->select();
        if(empty($set_sell_list)==false){
            foreach ($set_sell_list as  $k=>$v){
                $set_where = [];
                $set_where[] = ['id','in',$ids];
                $set_where[] = ['buy_stage_id','=',$stageInfo['id']];
                $set_where[] = ['buy_status','=',0];
                $set_where[] = ['buy_user_id','=',$v['reserve_user_id']];
                $set_buy_info = [];
                $set_buy_info = $this->where($set_where)->find();
                if(empty($set_buy_info)==false){
                    #更新售出表和卖出表
                    $this->up_order($v['id'],$set_buy_info['id'],$set_buy_info['deduct_integral'],$set_buy_info['buy_user_id'],$accountModel,$SellTradeModel);
                    #给中奖的用户发送通知
                    $MessageModel->sendMessage($set_buy_info['buy_user_id'],1,0,'中奖通知','您的预约已中奖,请按时交易',$show_end_date,3);
                    #删除掉已匹配的用户
                    unset($ids[array_search($set_buy_info['id'],$ids)]);
                }
            }
        }

        #获取所有售出数据
        $sell_where = [];
        $sell_where[] = ['sell_status','=',0];
        $sell_where[] = ['sell_stage_id','=',$stageInfo['id']];
        $sell_list = $SellTradeModel->where($sell_where)->select();
        if(!empty($sell_list)){
            foreach ($sell_list as $k=>$v){
                if(empty($ids)){
                    break;
                }
                $buy_id = '0';
                $buy_info = [];
                if(empty($v['ban_user_id'])){
                    #无禁止用户id
                    $buy_id = $ids[array_rand($ids,1)];
                    if(empty($buy_id)==false){
                        #更新售出表和卖出表
                        $buy_info = $this->where('id',$buy_id)->find();
                        if($buy_info['buy_stage_id']!=$v['sell_stage_id']){
                            unset($ids[array_search($buy_info['id'],$ids)]);
                            continue;
                        }
                        if($buy_info['buy_user_id']==$v['sell_user_id']){
                            #如果卖家跟买家是同一个人 则重新匹配
                            $buy_where = [];
                            $buy_where[] = ['buy_stage_id','eq',$stageInfo['id']];
                            $buy_where[] = ['buy_status','eq',0];
                            $buy_where[] = ['id','in',$ids];
                            $buy_info = [];
                            $buy_info = $this->where($buy_where)->orderRaw('rand()')->find();
                            if(empty($buy_info)==false){
                                $this->up_order($v['id'],$buy_info['id'],$buy_info['deduct_integral'],$buy_info['buy_user_id'],$accountModel,$SellTradeModel);
                                #给中奖的用户发送通知
                                $MessageModel->sendMessage($buy_info['buy_user_id'],1,0,'中奖通知','您的预约已中奖,请按时交易',$show_end_date,3);
                                #删除掉已匹配的用户
                                unset($ids[array_search($buy_info['id'],$ids)]);
                            }
                        }else{
                            $this->up_order($v['id'],$buy_info['id'],$buy_info['deduct_integral'],$buy_info['buy_user_id'],$accountModel,$SellTradeModel);
                            #给中奖的用户发送通知
                            $MessageModel->sendMessage($buy_info['buy_user_id'],1,0,'中奖通知','您的预约已中奖,请按时交易',$show_end_date,3);
                            #删除掉已匹配的用户
                            unset($ids[array_search($buy_info['id'],$ids)]);
                        }

                    }
                }else{
                    #有禁止用户id
                    $buy_where = [];
                    $buy_where[] = ['buy_stage_id','eq',$stageInfo['id']];
                    $buy_where[] = ['buy_status','eq',0];
                    $buy_where[] = ['id','in',$ids];
                    $buy_where[] = ['buy_user_id','neq',$v['ban_user_id']];
                    $buy_info = [];
                    $buy_info = $this->where($buy_where)->orderRaw('rand()')->find();
                    if(empty($buy_info)==false){
                        $this->up_order($v['id'],$buy_info['id'],$buy_info['deduct_integral'],$buy_info['buy_user_id'],$accountModel,$SellTradeModel);
                        #给中奖的用户发送通知
                        $MessageModel->sendMessage($buy_info['buy_user_id'],1,0,'中奖通知','您的预约已中奖,请按时交易',$show_end_date,3);
                        #删除掉已匹配的用户
                        unset($ids[array_search($buy_info['id'],$ids)]);
                    }
                }

            }
        }

        if(!empty($ids)){
            #更新剩余预约记录的状态
            foreach ($ids as $k=>$v){
                $up_buy = [];
                $up_buy['buy_status'] = 3;
                $up_buy['panic_end_time'] = $time;
                $this->where('id',$v)->update($up_buy);
                #给未中奖的用户退信用积分
                $buy_info = [];
                $buy_info = $this->where('id',$v)->find();
                $charge = [];
                $charge['use_integral']   = $buy_info['deduct_integral'];
                $charge['change_desc'] = '开奖结束,返还信用积分';
                $charge['change_type'] = 11;
                $accountModel->change($charge, $buy_info['buy_user_id'], false);
                #给未中奖的用户发送通知
                $up_info = [];
                $up_info =  $this->where('id',$v)->find();
                $MessageModel->sendMessage($up_info['buy_user_id'],1,0,'中奖通知','您的预约未中奖',$show_end_date,3);
            }
        }
        #更新开奖区间状态
        $TradingStageModel->where('id',$stageInfo['id'])->update(['is_lottery'=>1]);
    }

    public  function up_order($sell_id,$buy_id,$scribe_integral,$buy_user_id,&$accountModel,&$SellTradeModel){
        $time = time();
        #更新出售表
        $set_sell_save = [];
        $set_sell_save['buy_id'] = $buy_id;
        $set_sell_save['matching_time'] = $time;
        $set_sell_save['sell_status'] = 1;
        $SellTradeModel->where('id',$sell_id)->update($set_sell_save);

        #更新预约表
        $set_buy_save = [];
        $set_buy_save['sell_id'] = $sell_id;
        $set_buy_save['buy_status'] =2;
        $set_buy_save['panic_end_time'] = $time;
        $this->where('id',$buy_id)->update($set_buy_save);

        #返还用户的信用积分
        $charge = [];
        $charge['use_integral']   = $scribe_integral;
        $charge['change_desc'] = '开奖结束,返还信用积分';
        $charge['change_type'] = 11;
        $accountModel->change($charge, $buy_user_id, false);
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

    /*------------------------------------------------------ */
    //-- 预约没有抢购的自动过期
    /*------------------------------------------------------ */
    public function BeOverdue(){
        $accountModel = new AccountLogModel();
        $TradingStageModel = new TradingStageModel();
        $SellTradeModel = new SellTradeModel();

        $time = time();
        $where[] = ['isputaway','=',1];
        $where[] = ['is_overdue','=',0];
        $stageList = $TradingStageModel->where($where)->select();
        if(empty($stageList))  return '';
        #获取当前开奖的区间
        foreach ($stageList as $k=>$v){
            $stageTime = $v['trade_start_time'];
            $stageTime = strtotime(date('Y-m-d '.$stageTime))+(settings('lottery_time')*60)+30;
            if($time>$stageTime){
                $ids  = $this->getIds('buyHandle');
                #获取当前区间没有点击抢购的数据
                $buy_where[] = ['buy_status','=',0];
                $buy_where[] = ['buy_stage_id','=',$v['id']];
                $list = $this->where($buy_where)->select();
               foreach ($list as $key=>$value){
                   #判断当前预约信息的状态
                   if(!in_array($value['id'],$ids)){
                       $buy_save['panic_end_time'] = $time;
                       $buy_save['buy_status'] = 4;
                       $res = $this->where('id',$value['id'])->update($buy_save);
                   }
               }
               #更换当前区间的状态
                $stage_save['is_overdue'] = 1;
                $TradingStageModel->where('id',$v['id'])->update($stage_save);
            }
        }

    }

    /*------------------------------------------------------ */
    //-- 每日重置开奖和过期情况
    /*------------------------------------------------------ */
    public function DailyReset(){
        $TradingStageModel = new TradingStageModel();

        $save['is_lottery'] = 0;
        $save['is_overdue'] = 0;
        $where[] = ['id','>',0];

        $TradingStageModel->where($where)->update($save);
    }

    /*------------------------------------------------------ */
    //-- 解封
    /*------------------------------------------------------ */
    public function Unsealing(){
        $DdBanRecordModel = new DdBanRecordModel();
        $UsersModel = new UsersModel();
        $where[] = ['ban_status','=',0];
        $where[] = ['forever_ban','=',0];
        $list = $DdBanRecordModel->where($where)->select();
        $time = time();
        foreach ($list as $k=>$v){
            #解封时间
            $ban_time = $v['ban_time']+($v['ban_day']*24*60*60);
            if(!($time<$ban_time)){
                #更新封号记录表
                $save['ban_status'] = 1;
                $save['unsealing_time'] = $time;
                $save['manual'] = 1;
                $res  = $DdBanRecordModel->where('id',$v['id'])->update($save);
                #更新用户表状态
                $save_user['is_ban'] = 0;
                $res1 = $UsersModel->where('user_id',$v['user_id'])->update($save_user);
            }

        }
    }
}
