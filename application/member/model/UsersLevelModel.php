<?php
namespace app\member\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 会员等级表
/*------------------------------------------------------ */
class UsersLevelModel extends BaseModel
{
	protected $table = 'users_level';
	public  $pk = 'level_id';
	protected static $mkey = 'users_level_list';
	
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm(self::$mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */ 
	public  function getRows(){
		$data = Cache::get(self::$mkey);
		if (empty($data) == false){
			return $data;
		}
		$rows = $this->field('*,level_id as id,level_name as name')->order('min ASC')->select()->toArray();		
		foreach ($rows as $row){
			$data[$row['level_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
	
}
