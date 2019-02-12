<?php

namespace app\weixin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 微信消息模板
/*------------------------------------------------------ */
class WeiXinMsgTplModel extends BaseModel
{
	protected $table = 'weixin_msg_tpl';
	public  $pk = 'tpl_id';
	protected static $mkey = 'weixin_msg_tpl';
	/*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm(self::$mkey);
    }
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */ 
	public static function getRows(){
		$data = Cache::get(self::$mkey);
		if (empty($data) == false){
			return $data;
		}
		$rows = $this->select()->toArray();		
		foreach ($rows as $row){
			$data[$row['tpl_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}

}
