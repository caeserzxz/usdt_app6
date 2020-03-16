<?php
namespace app\ddkc\model;
use app\BaseModel;
use think\facade\Cache;
use think\cache\driver\Redis;
use app\member\model\UsersModel;
use app\ddkc\model\SellTradeModel;
use app\ddkc\model\TradingStageModel;
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
        $buyCount =  $redis->lSize('buyHandle');;
        $ids = [];
        for ($i=0;$i<=$buyCount-1;$i++){
            $id = $redis->lGet('buyHandle',$i);
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
            $sell_where[] = ['sell_user_id','neq',$v['buy_user_id']];
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

}
