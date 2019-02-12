<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 系统设置
/*------------------------------------------------------ */
class SettingsModel extends BaseModel
{
	protected $table = 'main_settings';
	public  $pk = 'id';
	protected $mkey = 'settings_list';
   /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm($this->mkey);
    }
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */
	public function getRows(){
		$data = Cache::get($this->mkey);
		if (empty($data) == false) return $data;
		$rows = $this->select()->toArray();
		
		foreach ($rows as $row){
			$data[$row['name']] = $row['data'];
		}
		Cache::set($this->mkey,$data,600);
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
	public function editSave($setting = array()){
		if (empty($setting) == true || is_array($setting) == false ) return false;		
		foreach ($setting as $key=>$val){
			$uparr['name'] = $map['name'] = $key;
			$uparr['data'] = trim(str_replace("'",'"',$val));
			$count = $this->where($map)->count('id');
			if ($count > 0){
				$res = $this->where($map)->update($uparr);
                if($res !== false) $res = 1;
			}else{				
				$res = self::create($uparr);
			}
		}
		$this->cleanMemcache();
		return true;
	}

}
