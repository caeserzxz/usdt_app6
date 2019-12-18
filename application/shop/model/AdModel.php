<?php

namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 广告
/*------------------------------------------------------ */

class AdModel extends BaseModel
{
    protected $table = 'shop_ad';
    public $pk = 'id';
    protected static $mkey = 'shop_ad_list';
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
        foreach ($rows as $key => $row) {
            $row['data'] = json_decode($row['data'], true);
            foreach ($row['data'] as $sub_key => $sub_row) {
                if ($sub_row['bind_type'] == 'article') $row['data'][$sub_key]['url'] = url('article/info', array('id' => $sub_row['ext_id']));
                else if ($sub_row['bind_type'] == 'goods') $row['data'][$sub_key]['url'] = url('goods/info', array('id' => $sub_row['ext_id']));
                else if ($sub_row['bind_type'] == 'tel') $row['data'][$sub_key]['url'] = "tel:" . $sub_row['type_val'];
                else $row['data'][$sub_key]['url'] = $sub_row['type_val'];
            }
            $rows[$key] = $row;
        }
        Cache::set(self::$mkey, $rows, 3600);
        return $rows;
    }
}
