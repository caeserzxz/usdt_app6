<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 商品收藏
/*------------------------------------------------------ */
class GoodsCollectModel extends BaseModel
{
	protected $table = 'shop_goods_collect';
	public  $pk = 'collect_id';
	
}
