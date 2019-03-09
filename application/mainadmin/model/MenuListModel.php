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
        $rows = self::where(['parent'=>0,'status'=>1])->order('sort_order ASC')->select()->toArray();
		foreach ($rows as $row){
			$rowsb = self::where(['parent'=>$row['key'],'group'=>$row['group'],'status'=>1])->order('sort_order ASC')->select()->toArray();			
			if (empty($rowsb) == false){
				foreach ($rowsb as $rowb){
					if (empty($rowb['key']) == false){
						$rowsc = self::where(['parent'=>$rowb['key'],'group'=>$row['group'],'status'=>1])->order('sort_order ASC')->select()->toArray();
						if (empty($rowsc) == false){
							foreach ($rowsc as $keyc=>$rowc){
								if (empty($rowc['key'])) continue;
								$rowsd = self::where(['parent'=>$rowc['key'],'group'=>$row['group'],'status'=>1])->order('sort_order ASC')->select()->toArray();
								if (empty($rowsd) == false) $rowc['submenu'] = $rowsd;
								if (empty($rowc['right']) == false){
									$rowc['_right'] = explode(',',$rowc['right']);
								}
								$rowsc[$keyc] = $rowc;
							}
							$rowb['submenu'] = $rowsc;
						}
					}
					$key = empty($rowb['key']) ? $rowb['id'] : $rowb['key'];
					if (empty($rowb['right']) == false){
						$rowb['_right'] = explode(',',$rowb['right']);
					}
					$row['list'][$key] = $rowb;
				}
			}
			$key = empty($row['key']) ? $row['id'] : $row['key'];
			$data[$key] = $row;
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
