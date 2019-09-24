<?php

namespace app\shop\controller\api;

use app\ApiController;
use app\mainadmin\model\ArticleModel;

class Article extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new ArticleModel();
    }
    /*------------------------------------------------------ */
    //-- 获取头条列表
    /*------------------------------------------------------ */
    public function getHeadlineList()
    {
        $children = input('children', '', 'trim');
        if (empty($children) == false) {
            $where[] = ['cid', 'in', $children];
        }
        $where[] = ['type', '=', 1];//类型-头条
        $where[] = ['is_show', '=', 1];
        $this->sqlOrder = "sort_order ASC,id DESC";
        $data = $this->getPageList($this->Model, $where, '*', 10);
        $return['list'] = $data['list'];
        $return['page_count'] = $data['page_count'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 获取绑定商品列表
    /*------------------------------------------------------ */
    public function getGoodsList(){
        $article_id = input('article_id',0,'intval');//文章ID
        $info = $this->Model->info($article_id);
        $goods_type =$info['link_data']['goods_type'];//商品类型
        $goods_ids =$info['link_data']['goods_ids'];//商品ID
        if(empty($goods_ids)){
            $return['list']=[];
        }else{
            $list = $this->Model->getBindGoodsList($goods_type,$goods_ids);
            $return['list']=$list;
        }
        $return['code'] = 1;
        $return['goods_type'] = $goods_type;
        return $this->ajaxReturn($return);
    }



}
