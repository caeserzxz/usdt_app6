<?php
namespace app\distribution\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 分销身份表
/*------------------------------------------------------ */
class DividendRoleModel extends BaseModel
{
	protected $table = 'distribution_dividend_role';
	public  $pk = 'role_id';
	protected static $mkey = 'distribution_dividend_role_list';
	
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
		$rows = $this->field('*,role_id as id,role_name as name')->order('level ASC')->select()->toArray();		
		foreach ($rows as $row){
			$data[$row['role_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}

}
