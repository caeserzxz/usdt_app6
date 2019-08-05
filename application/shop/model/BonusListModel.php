<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 商品优惠券子表
/*------------------------------------------------------ */
class BonusListModel extends BaseModel
{
	protected $table = 'shop_bonus_list';
	public  $pk = 'bonus_id';
}
