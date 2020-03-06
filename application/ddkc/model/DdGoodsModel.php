<?php
namespace app\ddkc\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 会员等级表
/*------------------------------------------------------ */
class DdGoodsModel extends BaseModel
{
	protected $table = 'dd_mining_goods';
	public  $pk = 'miner_id';
	protected static $mkey = 'dd_goods_list';
	
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
		$rows = $this->field('*,miner_id as id')->order('miner_id DESC')->select()->toArray();		
		foreach ($rows as $row){
			$data[$row['miner_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
	
}
