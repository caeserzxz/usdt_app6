<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 文章分类
/*------------------------------------------------------ */
class ArticleCategoryModel extends BaseModel
{
	protected $table = 'main_article_category';
	public  $pk = 'id';
	protected $mkey = 'article_category_mkey';
     /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm($this->mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取分类列表
	/*------------------------------------------------------ */
    public function getRows(){	
		$rows = Cache::get($this->mkey);	
		if (empty($rows) == false) return $rows;
		$rows = $this->order('sort_order,pid ASC')->select()->toArray();
		$rows = returnRows($rows);
		Cache::set($this->mkey,$rows,3600);
		return $rows;
	}
}
