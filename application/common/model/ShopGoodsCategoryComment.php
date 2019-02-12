<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 19-1-19
 * Time: 16:34
 */

namespace app\common\model;

use app\BaseModel;
use app\common\model\concern\SoftDelete;
use think\facade\Cache;

class ShopGoodsCategoryComment extends BaseModel {

    use SoftDelete;

    public $pk = 'id';
    protected $name = 'shop_goods_category_comment';

    public $defaultSoftDelete = 0;

    const CACHE_PREFIX = 'shop_goods_category_comment_mkey_';

    public static function create($data = [], $field = null, $replace = false) {
        if (!$data['create_time']) {
            $data['create_time'] = time();
        }
        return parent::create($data, $field, $replace);
    }

    /**
     * 清理缓存
     *
     * @param $id
     */
    public static function clearCache($id) {
        Cache::rm(static::CACHE_PREFIX . $id);
    }
}