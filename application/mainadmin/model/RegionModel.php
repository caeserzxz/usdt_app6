<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 区域管理
/*------------------------------------------------------ */
class RegionModel extends BaseModel
{
	protected $table = 'main_region';
	public  $pk = 'id';
	protected $mkeySel = 'region_list_by_sel';
   /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm($this->mkeySel);
    }
    /*------------------------------------------------------ */
    //--  获取前端js所需的地区json
    /*------------------------------------------------------ */
    public function rawCitiesData($pid = 100000){
       $rows = $this->field('id,name')->where('pid',$pid)->select()->toArray();
       if (empty($rows)) return false;
       foreach ($rows as $row){
           $_row['name'] = $row['name'];
           $_row['code'] = $row['id'];
           $sub = $this->rawCitiesData($row['id']);
           if (empty($sub) == false){
               $_row['sub'] = $sub;
           }
           $list[] = $_row;
       }
       unset($rows);
       return $pid == 100000 ? json_encode($list,JSON_UNESCAPED_UNICODE):$list;
    }
    /*------------------------------------------------------ */
    //-- 选项列表
    /*------------------------------------------------------ */
    public function info($id = 0){
         return $this->where('id',$id)->find()->toArray();
    }
	/*------------------------------------------------------ */
	//-- 选项列表
	/*------------------------------------------------------ */
	public function getBySel($pid = 0){
		$list = Cache::get($this->mkeySel);
		if (empty($list[$pid]) == false) return $list[$pid];
		$rows = $this->field('id,pid,name')->where('pid',$pid)->select();
		foreach ($rows as $row){
			$list[$row['pid']][$row['id']] = $row['name'];
		}
		Cache::set($this->mkeySel,$list,3600);
		return $list[$pid];
	}
	

}
