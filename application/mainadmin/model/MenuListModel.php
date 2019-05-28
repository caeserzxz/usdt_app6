<?php

namespace app\mainadmin\model;

use think\facade\Cache;
use app\BaseModel;

/**
 * 后台菜单模型
 * Class StoreUser
 * @package app\store\model
 */
class MenuListModel extends BaseModel
{
    protected $table = 'main_menu_list';
    public $pk = 'id';
    protected $mkey = 'main_menu_list';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache()
    {
        Cache::rm($this->mkey);
        Cache::rm('main_menu_list_no_priv');
    }
    /*------------------------------------------------------ */
    //-- 获取菜单
    /*------------------------------------------------------ */
    public function getList()
    {
        $data = Cache::get($this->mkey);
        if (empty($data) == false) {
            return $data;
        }
        $data = [];
        $_data = [];
        $rows = self::where('status', 1)->order('pid DESC sort_order ASC')->select()->toArray();
        foreach ($rows as $row) {
            $key = $row['pid'] < 1 ? $row['group'] : $row['id'];
            $row['_right'] = explode(',', $row['right']);
            if ($row['pid'] > 0) {
                if (empty($_data[$row['id']]) == false) {
                    $row['submenu'] = $_data[$row['id']];
                    unset($_data[$row['id']]);
                }

                $_data[$row['pid']][$key] = $row;
            } else {
                $row['list'] = $_data[$row['id']];
                $data[$key] = $row;
            }

        }
        Cache::set($this->mkey, $data, 60);
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取不限制权限的菜单
    /*------------------------------------------------------ */
    public function getNoPriv()
    {
        $data = Cache::get('main_menu_list_no_priv');
        if (empty($data) == false) return $data;
        $rows = self::where('right', '')->select()->toArray();
        foreach ($rows as $row) {
            $data[] = $row['group'] . '|' . $row['controller'];
        }
        Cache::set('main_menu_list_no_priv', $data, 60);
        return $data;
    }


}
