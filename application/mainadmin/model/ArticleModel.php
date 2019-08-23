<?php

namespace app\mainadmin\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 文章
/*------------------------------------------------------ */

class ArticleModel extends BaseModel
{
    protected $table = 'main_article';
    public $pk = 'id';
    protected $mkey = 'main_article_mkey_';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($goods_id = 0)
    {
        Cache::rm($this->mkey . $goods_id);
    }
    /*------------------------------------------------------ */
    //-- 获取文章详情
    //-- $id  int 商品id
    /*------------------------------------------------------ */
    public function info($article_id)
    {
        $article = Cache::get($this->mkey . $article_id);
        if (empty($article) == false) return $article;
        $article = $this->where('id', $article_id)->find();
        if (empty($article) == true) return array();
        $article = $article->toArray();
        //绑定链接处理
        if($article['link_type'] == 'article') $article['url'] = url('article/info',array('id'=>$article['ext_id']));
        else if($article['link_type'] == 'goods') $article['url'] = url('goods/info',array('id'=>$article['ext_id']));
        else $article['url'] = $article['link_data'];

        $article['content'] = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', $article['content']);
        Cache::set($this->mkey . $article_id, $article, 600);
        return $article;
    }
    /*------------------------------------------------------ */
    //-- 更新
    /*------------------------------------------------------ */
    public function upInfo(&$data, $where)
    {
        $data['update_time'] = time();
        $res = $this->where($where)->update($data);
        if ($res < 1) return false;
        $this->cleanMemcache($where['goods_id']);
        return true;
    }


}
