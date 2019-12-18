<?php

namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 楼层板块
/*------------------------------------------------------ */

class PlateModel extends BaseModel
{
    protected $table = 'shop_plate';
    public $pk = 'id';
    protected static $mkey = 'shop_plate_list';
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
    public static function getRows()
    {
        $rows = Cache::get(self::$mkey);
        if (empty($rows) == false) return $rows;
        $rows = self::where('status', 1)->order('sort_order DESC')->select()->toArray();
        Cache::set(self::$mkey, $rows, 3600);
        return $rows;
    }

}
