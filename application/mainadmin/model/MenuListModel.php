<?php

namespace app\mainadmin\model;
use think\facade\Cache;
use think\Model;
/**
 * 后台菜单模型
 * Class StoreUser
 * @package app\store\model
 */
class MenuListModel extends Model
{
	protected $table = 'main_menu_list';
	/*------------------------------------------------------ */
	//-- 获取菜单
	/*------------------------------------------------------ */
    public function getList()
    { 
		$data = Cache::get('main_menu_list');

		if (empty($data) == false){
			return $data;
		}
        $data = [];
		$_data = [];
        $rows = self::where('status',1)->order('level DESC sort_order ASC')->select()->toArray();
        foreach ($rows as $row){
            $key = empty($row['key']) ? $row['id'] : $row['key'];
            $row['_right'] = explode(',',$row['right']);
            if ($row['level'] == 4){
                $_data[$row['pid']][$key] = $row;
            }elseif ($row['level'] >= 2){
                if (empty($_data[$row['id']]) == false){
                    $row['submenu'] = $_data[$row['id']];
                    unset($_data[$row['id']]);
                }

                $_data[$row['pid']][$key] = $row;
            }else{
                $row['list'] = $_data[$row['id']];
                $data[$key] = $row;
            }

        }
		Cache::set('main_menu_list',$data,60);
        return $data;
    }
	/*------------------------------------------------------ */
	//-- 获取不限制权限的菜单
	/*------------------------------------------------------ */
    public function getNoPriv()
    { 
		$data = Cache::get('main_menu_list_no_priv');
		if (empty($data) == false) return $data;
        $rows = self::where('right','')->select()->toArray();
		foreach ($rows as $row){
			$data[] = $row['group'].'|'.$row['controller'];
		}
		Cache::set('main_menu_list_no_priv',$data,60);
        return $data;
	}
    

}
