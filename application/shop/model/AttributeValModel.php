<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 商品属性
/*------------------------------------------------------ */
class AttributeValModel extends BaseModel
{
	protected $table = 'shop_goods_attribute_val';
	public  $pk = 'attr_id';
	protected $mkey = 'shop_goods_attribute_val_';
   
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
    public function getRows($goods_id){	
		$list = Cache::get($this->mkey.$goods_id);	
		if (empty($list) == false) return $list;
		$rows = $this->where('goods_id',$goods_id)->select()->toArray();
		if (empty($rows)) return array();
		foreach ($rows as $row){
			$list[$row['attr_id']] = $row['attr_value'];
		}
		Cache::set($this->mkey.$goods_id,$list,3600);
		return $list;
	}
   
}
