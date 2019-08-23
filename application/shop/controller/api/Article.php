<?php

namespace app\shop\controller\api;

use app\ApiController;
use app\mainadmin\model\ArticleModel;
use app\mainadmin\model\HeadlineModel;

class Article extends ApiController
{
    /*------------------------------------------------------ */
    //-- 获取头条列表
    /*------------------------------------------------------ */
    public function getHeadlineList()
    {
        $HeadlineModel = new HeadlineModel();
        $children =input('children','','trim');
        if(empty($children)==false){
            $where[]=['cid','in',$children];
        }
        $where[]=['status','=',1];
        $viewObj = $HeadlineModel->alias('hl')->join("main_article a", 'hl.ext_id=a.id');
        $viewObj->field('hl.*,a.img_url,a.click')->order('is_best DESC');
        $data = $this->getPageList($HeadlineModel,$viewObj,'*',10);
        $return['list']=$data['list'];
        $return['page_count'] = $data['page_count'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }


}
