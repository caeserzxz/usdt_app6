<?php

namespace app\ddkc\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 交易阶段设计表
/*------------------------------------------------------ */

class TradingStageModel extends BaseModel
{
    protected $table = 'dd_trading_stage';
    public $pk = 'id';
    protected static $mkey = 'dd_trading_stage_list';

    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache()
    {
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
        $rows = $this->field('*')->where('isputaway',1)->order('id DESC')->select()->toArray();
        foreach ($rows as $row) {
            $data[$row['id']] = $row;
        }
        Cache::set(self::$mkey, $data, 86400);
        return $data;
    }
}
