<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 商城首页导航菜单
/*------------------------------------------------------ */
class NavMenuModel extends BaseModel
{
	protected $table = 'shop_nav_menu';
	public $pk = 'id';
    protected static $mkey = 'shop_nav_menu_list';
    public $type = 1;
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($type = 1){
        Cache::rm(self::$mkey);
        if ($type) {
            Cache::rm(self::$mkey . '_' . $type);
        }
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public static function getRows($type = 1){
        $mkey = self::$mkey . '_' . $type;
        $rows = Cache::get($mkey);
        if (empty($rows) == false) return $rows;
        $where[] = ['status', '=', 1];
        $where[] = ['type', '=', $type];
        $rows = self::where($where)->order('sort_order DESC')->select()->toArray();
		foreach ($rows as $key=>$row){			
			if($row['bind_type'] == 'article') $row['url'] = url('article/info',array('id'=>$row['ext_id']));
			else if($row['bind_type'] == 'goods') $row['url'] = url('goods/info',array('id'=>$row['ext_id']));
			else $row['url'] = $row['data'];			
			$rows[$key] = $row;
		}
        Cache::set($mkey, $rows, 3600);
		return $rows;
	}
}
