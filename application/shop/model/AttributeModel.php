<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 商品属性表
/*------------------------------------------------------ */
class AttributeModel extends BaseModel
{
	protected $table = 'shop_goods_attribute';
	public  $pk = 'attr_id';
	
	
   
}
