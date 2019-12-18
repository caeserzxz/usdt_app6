<?php

namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 幻灯片
/*------------------------------------------------------ */

class GoodsTagModel extends BaseModel
{
    protected $table = 'shop_goods_tag';
    public $pk = 'id';
    protected static $mkey = 'shop_goods_tag_list';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache()
    {
        Cache::rm(self::$mkey);
        Cache::rm(self::$mkey . 'all');
        Cache::rm(self::$mkey . 'able');
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
    /*------------------------------------------------------ */
    //-- 获取所有列表
    /*------------------------------------------------------ */
    public static function getAll()
    {
        $mkey = self::$mkey . 'all';
        $rows = Cache::get($mkey);
        if (empty($rows) == false) return $rows;
        $rows = self::order('sort_order DESC')->select()->toArray();
        $list = [];
        foreach ($rows as $key => $val) {
            $list[$val['id']] = $val;
        }
        unset($rows);
        Cache::set($mkey, $list, 3600);
        return $list;
    }

    /*------------------------------------------------------ */
    //-- 获取有商品的标签
    /*------------------------------------------------------ */
    public function getAbleList()
    {
        $mkey = self::$mkey . 'able';
        $list = Cache::get(self::$mkey);
        if (empty($list) == false) return $list;
        $rows = self::where('status', 1)->order('sort_order DESC')->select()->toArray();
        $list = [];
        $GoodsModel = new \app\shop\model\GoodsModel();
        foreach ($rows as $key => $val) {
            $where = [];
            $where[] = ['tag_id', '=', $val['id']];
            $where[] = ['is_delete', '=', 0];
            $where[] = ['is_alone_sale', '=', 1];
            $where[] = ['isputaway', '=', 1];
            $where[] = ['is_promote', '=', 0];
            $hasGoods = $GoodsModel->where($where)->field('goods_id')->find();
            if (empty($hasGoods)) continue;
            $list[$key] = $val;
        }
        Cache::set(self::$mkey, $list, 3600);
        return $list;
    }


}
