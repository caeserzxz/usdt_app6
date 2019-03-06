<?php
namespace app\distribution\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 分销身份表
/*------------------------------------------------------ */
class DividendAwardModel extends BaseModel
{
	protected $table = 'distribution_dividend_award';
	public  $pk = 'award_id';
	protected static $mkey = 'distribution_dividend_award_list';
	
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
		$rows = $this->field('*,award_id as id,award_name as name')->order('award_id ASC')->select()->toArray();		
		foreach ($rows as $row){
			$data[$row['role_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 获取角色信息
	/*------------------------------------------------------ */ 
	public function info($award_id,$returnName = false){
		$rows = $this->getRows();
		if ($returnName == true){
			return $rows[$award_id]['award_name'];
		}
		return $rows[$award_id];
	}
}
