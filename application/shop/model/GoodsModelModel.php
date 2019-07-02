<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 商品模型
/*------------------------------------------------------ */
class GoodsModelModel extends BaseModel
{
	protected $table = 'shop_goods_model';
	public  $pk = 'id';
	protected $mkey = 'goods_model_mkey';
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
		$rows = $this->select()->toArray();
		if (empty($rows)) return array();
		$AttributeModel = new AttributeModel();
		foreach ($rows as $row){
			$attr_list = $AttributeModel->where('model_id',$row['id'])->order('sort_order DESC')->select()->toArray();
			foreach ($attr_list as $attr_row){
                $attr_row['attr_values_arr'] = explode(',',$attr_row['attr_values']);
				$row['attr_list'][$attr_row['attr_id']] = $attr_row;
			}
			$list[$row['id']] = $row;
		}
		Cache::set($this->mkey,$list,3600);
		return $list;
	}
}
