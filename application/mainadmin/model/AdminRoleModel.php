<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
/**
 * 管理角色模型
 * Class StoreUser
 * @package app\store\model
 */
class AdminRoleModel extends BaseModel
{
	protected $table = 'main_admin_role';
	public  $pk = 'role_id';
	protected static $mkey = 'main_admin_role_list';
	
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm(self::$mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取角色列表
	/*------------------------------------------------------ */ 
	public  function getRows(){
		$data = Cache::get(self::$mkey);
		if (empty($data) == false){
			return $data;
		}
		$rows = $this->field('*,role_id as id,role_name as name')->select()->toArray();		
		foreach ($rows as $row){
			$data[$row['role_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}

}
