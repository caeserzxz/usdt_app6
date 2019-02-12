<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 子商品
/*------------------------------------------------------ */
class GoodsSkuModel extends BaseModel
{
	protected $table = 'shop_goods_sku';
	public  $pk = 'sku_id';


}
