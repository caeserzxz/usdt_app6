<?php
namespace app\second\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 秒杀列表
/*------------------------------------------------------ */
class SecondModel extends BaseModel
{
	protected $table = 'second_list';
	public  $pk = 'sg_id';
	protected $mkey = 'second_list_mkey_';
	/*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */
	public function cleanMemcache($sg_id = 0){
		Cache::rm($this->mkey.$sg_id);
	}

}
