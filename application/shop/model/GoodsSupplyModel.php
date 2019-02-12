<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 商品供货商
/*------------------------------------------------------ */
class GoodsSupplyModel extends BaseModel
{
	protected $table = 'shop_goods_supply';
	public  $pk = 'id';
	protected $mkey = 'goods_supply_mkey';
     /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm($this->mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
    public function getRows(){	
		$list = Cache::get($this->mkey);	
		if (empty($list) == false) return $list;
		$rows = $this->order('update_time DESC')->select()->toArray();
		if (empty($rows)) return array();
		foreach ($rows as $row){
			$list[$row['id']] = $row;
		}
		Cache::set($this->mkey,$list,3600);
		return $list;
	}
}
