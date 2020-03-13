<?php
namespace app\ddkc\model;
use app\BaseModel;
use think\facade\Cache;
use app\ddkc\model\TradingStageModel;
use app\ddkc\model\BuyTradeModel;
//*------------------------------------------------------ */
//-- 会员等级表
/*------------------------------------------------------ */
class SellTradeModel extends BaseModel
{
    protected $table = 'dd_sell_trade';
    public  $pk = 'id';
    protected static $mkey = 'dd_sell_trade_list';

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
    //-- 抢购开奖
    /*------------------------------------------------------ */
    public function PanicBuying(){
        $TradingStageModel = new TradingStageModel();
        $time = time();
        $stage_list = $TradingStageModel->getRows();

        $stage_info = [];
        foreach ($stage_list as $k=>$v){
            $start_time = strtotime(date("Y-m-d ".$v['trade_start_time']));
            $end_time =  strtotime(date("Y-m-d ".$v['trade_end_time']));

//            if(){
//
//            }
        }
        dump($stage_list);die;
    }
}
