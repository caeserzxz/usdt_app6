<?php

namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 系统设置
/*------------------------------------------------------ */
class ShippingTplModel extends BaseModel
{
	protected $table = 'shop_shipping_tpl';
	public  $pk = 'sf_id';
	protected $mkey = 'shipping_tpl_list_';
   /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($supplyer_id){
        Cache::rm($this->mkey.$supplyer_id);
    }
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */
	public function getRows($supplyer_id = 0){
        $supplyer_id = $supplyer_id * 1;
        $mkey = $this->mkey.$supplyer_id;
		$data = Cache::get($mkey);
		if (empty($data) == false) return $data;
        $where[] = ['supplyer_id','=',$supplyer_id];
        $rows = $this->where($where)->order('is_default DESC')->select()->toArray();
		foreach ($rows as $row){
            $row['sf_info'] = json_decode($row['sf_info'],true);
			$data[$row['sf_id']] = $row;
		}
		Cache::set($mkey,$data,600);
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
			$count = $this->where($map)->count();
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
