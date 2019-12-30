<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
/**
 * 管理权限模型
 * Class StoreUser
 * @package app\store\model
 */
class AdminPrivModel extends BaseModel
{
	protected $table = 'main_admin_priv';
	public  $pk = 'id';
	protected static $mkey = 'main_admin_priv_list';
	
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm(self::$mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取权限列表
	/*------------------------------------------------------ */ 
	public  function getRows(){
		$data = Cache::get(self::$mkey);
		if (empty($data) == false){
			return $data;
		}
		$rows = $this->where('is_del',0)->select()->toArray();
		foreach ($rows as $row){
            $row['right'] = explode(',',strtolower($row['right']));
			$data[$row['group']][] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}

}
