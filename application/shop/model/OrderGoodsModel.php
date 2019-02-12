<?php
namespace app\shop\model;
use app\BaseModel;


//*------------------------------------------------------ */
//-- 订单商品表
/*------------------------------------------------------ */
class OrderGoodsModel extends BaseModel
{
	protected $table = 'shop_order_goods';
	public  $pk = 'rec_id';

	
}
