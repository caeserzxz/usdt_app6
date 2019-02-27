<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 幻灯片
/*------------------------------------------------------ */
class SlideModel extends BaseModel
{
	protected $table = 'shop_slide';
	public $pk = 'id';
    protected static $mkey = 'shop_slide_list';
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm(self::$mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
    public static function getRows(){	
		$rows = Cache::get(self::$mkey);	
		if (empty($rows) == false) return $rows;
		$rows = self::where('status',1)->order('sort_order DESC')->select()->toArray();
		foreach ($rows as $key=>$row){			
			if($row['bind_type'] == 'article') $row['url'] = url('article/info',array('id'=>$row['ext_id']));
			else if($row['bind_type'] == 'goods') $row['url'] = url('goods/info',array('id'=>$row['ext_id']));
			else $row['url'] = $row['data'];			
			$rows[$key] = $row;
		}
		Cache::set(self::$mkey,$rows,3600);
		return $rows;
	}
}
