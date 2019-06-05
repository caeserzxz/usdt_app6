<?php

namespace app\supplyer\model;
use think\facade\Cache;
use app\BaseModel;
/**
 * 后台菜单模型
 * Class StoreUser
 * @package app\store\model
 */
class MenuListModel extends BaseModel
{
	protected $table = 'supplyer_menu_list';
    public  $pk = 'id';
	/*------------------------------------------------------ */
	//-- 获取菜单
	/*------------------------------------------------------ */
    public function getList()
    { 
		//$data = Cache::get('supplyer_menu_list');

		if (empty($data) == false){
			return $data;
		}
        $data = [];
		$_data = [];
        $rows = self::where('status',1)->order('pid DESC sort_order ASC')->select()->toArray();
        foreach ($rows as $row){
            $row['group'] = 'supplyer';
            if (empty($row['ext_upper']) == false){
                $row['controller'] = $row['controller'].'.'.$row['ext_upper'];
            }
            $key = $row['pid'] < 1 ? $row['group'] : $row['id'];
            if ($row['pid'] > 0){
                if (empty($_data[$row['id']]) == false){
                    $row['submenu'] = $_data[$row['id']];
                    unset($_data[$row['id']]);
                }
                $_data[$row['pid']][$key] = $row;
            }else{
                $row['list'] = empty($_data[$row['id']])?'':$_data[$row['id']];
                $data[$key][] = $row;
            }

        }
		Cache::set('supplyer_menu_list',$data,60);
        return $data;
    }


}
