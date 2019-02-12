<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 商品价格阶梯
/*------------------------------------------------------ */
class GoodsVolumePriceModel extends BaseModel
{
	protected $table = 'shop_goods_volume_price';
	protected $mkey = 'shop_goods_volume_price_';
   
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
    public function getRows($goods_id){	
		$rows = Cache::get($this->mkey.$goods_id);	
		if (empty($list) == false) return $rows;
		$rows = $this->where('goods_id',$goods_id)->order('number ASC')->select()->toArray();
		if (empty($rows)) return array();
		Cache::set($this->mkey.$goods_id,$rows,3600);
		return $rows;
	}
}
