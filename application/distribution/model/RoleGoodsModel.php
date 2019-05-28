<?php
namespace app\distribution\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 身份商品表
/*------------------------------------------------------ */
class RoleGoodsModel extends BaseModel
{
	protected $table = 'distribution_role_goods';
	public  $pk = 'rg_id';
	protected static $mkey = 'distribution_role_goods_list';
	
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
        $rows = Cache::get(self::$mkey);
		if (empty($rows) == false){
			return $rows;
		}
		$rows = $this->order('sort_order DESC')->select()->toArray();
		Cache::set(self::$mkey,$rows,600);
		return $rows;
	}

}
