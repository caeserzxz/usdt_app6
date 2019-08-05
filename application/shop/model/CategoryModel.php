<?php

namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 商品分类
/*------------------------------------------------------ */

class CategoryModel extends BaseModel
{
    protected $table = 'shop_goods_category';
    public $pk = 'id';
    protected $mkey = 'goods_category_mkey';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache()
    {
        Cache::rm($this->mkey);
    }
    /*------------------------------------------------------ */
    //-- 获取分类列表
    /*------------------------------------------------------ */
    public function getRows($pid = false)
    {

        $rows = Cache::get($this->mkey);
        if (empty($rows) == true) {
            $rows = $this->order('pid ASC,sort_order ASC')->select()->toArray();
            $rows = returnRows($rows);
            Cache::set($this->mkey, $rows, 3600);
        }
        if ($pid !== false) {
            foreach ($rows as $key => $row) {
                if ($row['pid'] != $pid) {
                    unset($rows[$key]);
                }
            }
        }
        return $rows;
    }

    /*------------------------------------------------------ */
    // * 获取某类别及其父级的ID
    //* *@param string $cid  分类ID
    // * @param array $arr 数组
    /*------------------------------------------------------ */
    function getParentCateIds($cid, $arr = array())
    {
        if ($cid < 1)return $arr;
        $row = $this->find($cid)->toArray();
        if (empty($row)) {
            return $arr;
        }
        $arr[] = $row['id'];
        return $this->getParentCateIds($row['pid'],$arr);
    }
}
