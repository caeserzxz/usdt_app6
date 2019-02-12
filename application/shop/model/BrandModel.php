<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 商品品牌
/*------------------------------------------------------ */
class BrandModel extends BaseModel
{
	protected $table = 'shop_goods_brand';
	public  $pk = 'id';
	protected $mkey = 'goods_brand_mkey';
     /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm($this->mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
    public function getRows($cid = 0){
		$rows = Cache::get($this->mkey);
		if (empty($rows) == true){
            $rows = $this->order('sort_order ASC')->select()->toArray();
            $rows = returnRows($rows);
            Cache::set($this->mkey,$rows,3600);
        }
        if ($cid > 0){
            foreach ($rows as $key=>$row){
                if ($row['cid'] != $cid){
                    unset($rows[$key]);
                }
            }
        }
		return $rows;
	}
}
