<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 字典调用
/*------------------------------------------------------ */
class PubDictModel extends BaseModel
{
	protected $table = 'main_pub_dict';
	public  $pk = 'id';
	protected static $mkey = 'pub_dict_list';
   /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($dict_group = ''){
        Cache::rm(self::$mkey.$dict_group);
    }
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */
	public static function getRows($dict_group = '',$is_top = false,$is_del = false){
		//$data = Cache::get(self::$mkey.$dict_group);
		if (empty($data)){
			$rows = self::field('*,dict_val AS name,dict_key AS ext_val')->where('dict_group',$dict_group)->order('sort_order,id ASC')->select()->toArray();
			$data = returnRows($rows);
			Cache::set(self::$mkey.$dict_group,$data,300);
		}
		$ndata = array();
		foreach ($data as $key=>$row){
			if ($is_top == false && $row['pid'] == 0) unset($data[$key]);
			elseif ($is_del == false && $row['isdel'] == 1) unset($data[$key]);
			else{
				$ndata[$row['ext_val']] = $row;
			}
		}
		
		return $ndata;
	}
	

}
