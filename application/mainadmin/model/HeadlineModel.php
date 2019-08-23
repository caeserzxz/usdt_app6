<?php

namespace app\mainadmin\model;

use app\BaseModel;
use think\facade\Cache;
use app\mainadmin\model\ArticleModel;

//*------------------------------------------------------ */
//-- 头条
/*------------------------------------------------------ */

class HeadlineModel extends BaseModel
{
    protected $table = 'main_headline';
    public $pk = 'id';
    protected $mkey = 'headline_mkey_';
    /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($id)
    {
        Cache::rm($this->mkey . $id);
    }

    /*------------------------------------------------------ */
    //-- 获取首页头条
    /*------------------------------------------------------ */
    public function getBestList()
    {
        $headline_max_num = settings('headline_max_num');
        $where[]=['status','=',1];
        $where[]=['is_best','=',1];
        $list =$this->where($where)->order('id ASC')->limit(0,$headline_max_num)->select()->toArray();
        return $list;
    }

    /*------------------------------------------------------ */
    //-- 获取头条信息
    //-- $id 站内信主ID
    /*------------------------------------------------------ */
    public function info($id = 0)
    {
        $id = $id * 1;
        $info = Cache::get($this->mkey . $id);
        if ($info) return $info;
        $info = $this->find($id)->toArray();
        $article = (new ArticleModel)->info($info['ext_id']);
        $info['article'] = $article;
        Cache::set($this->mkey . $id, $info, 60);
        return $info;
    }

}
