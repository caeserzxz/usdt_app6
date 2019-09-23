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
    public function cleanMemcache($article_id = 0)
    {
        Cache::rm($this->mkey . $article_id);
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
        //绑定类型
        if($article['link_type']=='goods_cat'){//商品分类
            $article['url']= config('config.host_path')."/".url('shop/goods/index',array('cid'=>$article['link_data']));
        }elseif($article['link_type'] == 'goods'){
            $article['link_data']= json_decode($article['link_data'],JSON_UNESCAPED_UNICODE);
        }elseif($article['link_type'] == 'link'){
            $article['url']= $article['link_data'];
        }
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
    /*------------------------------------------------------ */
    //-- 获取首页头条
    /*------------------------------------------------------ */
    public function getHeadline(){
        $headline_max_num = settings('headline_max_num');
        $where[]=['type','=',1];//类型-头条
        $where[]=['is_show','=',1];
        $where[]=['is_best','=',1];
        $list =$this->where($where)->order('sort_order ASC,id DESC')->limit(0,$headline_max_num)->field('id,title')->select()->toArray();
        return $list;
    }
    /*------------------------------------------------------ */
    //-- 获取文章绑定商品列表
    /*------------------------------------------------------ */
    public function getBindGoodsList($goods_type,$goods_ids){
        if ($goods_type == 1) {//普通商品
            $GoodsModel = new \app\shop\model\GoodsModel();
            $where[] = ['goods_id', 'in', $goods_ids];
            $_list = $GoodsModel->where($where)->field("goods_id as id,goods_name,shop_price as show_price,is_spec,min_price,max_price,goods_sn,goods_thumb")->select();
        }
        if ($goods_type == 2) {//拼团商品
            $FightGroupModel = new \app\fightgroup\model\FightGroupModel();
            $where[] = ['fg.fg_id', 'in', $goods_ids];
            $_list = $FightGroupModel->alias('fg')
                ->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left')
                ->where($where)->field("fg.fg_id as id,fg.show_price,g.goods_name,g.shop_price,g.is_spec,g.goods_sn,g.goods_thumb")->select();
        }
        if ($goods_type == 3) {//秒杀商品
            $SecondModel = new \app\second\model\SecondModel;
            $where[] = ['sg.sg_id', 'in', $goods_ids];
            $_list = $SecondModel->alias('sg')
                ->join("shop_goods g", 'sg.goods_id=g.goods_id', 'left')
                ->where($where)->field("sg.sg_id as id,sg.show_price,g.goods_name,g.shop_price,g.is_spec,g.goods_sn,g.goods_thumb")->select();
        }
        return $_list;
    }
}
