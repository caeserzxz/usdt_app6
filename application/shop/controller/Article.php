<?php
/*------------------------------------------------------ */
//-- 商品收藏相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;
use app\mainadmin\model\ArticleModel;


class Article extends ClientbaseController{
	/*------------------------------------------------------ */
	//-- 文章详情页
	/*------------------------------------------------------ */
	public function info(){
	    $id = input('id',0,'intval');
	    if ($id < 1){
	        return $this->error('传参失败.');
        }
        $info = (new ArticleModel)->find($id);
	    if (empty($info)){
            return $this->error('文章不存在..');
        }
        $info['content'] = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/",'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', $info['content']);

        $this->assign('title','文章');
        $this->assign('info', $info);
		return $this->fetch('info');
	}
    /*------------------------------------------------------ */
    //-- 注册协议
    /*------------------------------------------------------ */
    public function registerAgreement(){

        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/",'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('register_agreement'));

        $this->assign('title','注册协议');
        $this->assign('content', $content);
        return $this->fetch('register');
    }
   
}?>