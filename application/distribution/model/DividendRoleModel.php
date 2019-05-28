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
            $row['upleve_value'] = json_decode($row['upleve_value'],true);
			$data[$row['role_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 获取角色信息
	/*------------------------------------------------------ */ 
	public function info($role_id,$returnName = false){
		$rows = $this->getRows();
		$rows[0] = ['role_name'=>'粉丝','role_id'=>0,'level'=>0];
		if ($returnName == true){
			return $rows[$role_id]['role_name'];
		}
		return $rows[$role_id];
	}
}
