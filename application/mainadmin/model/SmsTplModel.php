<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 短信模板
/*------------------------------------------------------ */
class SmsTplModel extends BaseModel
{
	protected $table = 'main_sms_tpl';
	public  $pk = 'tpl_id';
	protected static $mkey = 'sms_tpl_list';
   /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm(self::$mkey);
    }
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */
	public static function getRows(){
		$data = Cache::get(self::$mkey);
		if ($data) return $data;
		$rows = self::select()->toArray();
		foreach ($rows as $row){
			$data[$row['send_scene']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
	

}
