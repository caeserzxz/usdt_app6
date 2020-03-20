<?php
/*------------------------------------------------------ */
//-- 文章
//-- Author: iqgmy
/*------------------------------------------------------ */

namespace app\shop\controller;

use app\ClientbaseController;
use app\mainadmin\model\ArticleModel;
use app\mainadmin\model\ArticleCategoryModel;
use app\mainadmin\model\MessageModel;
use app\mainadmin\model\UserMessageModel;


class Article extends ClientbaseController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new ArticleModel();
    }
    /*------------------------------------------------------ */
    //-- 文章详情页
    /*------------------------------------------------------ */
    public function info()
    {
        $id = input('id', 0, 'intval');
        if ($id < 1) {
            return $this->error('传参失败.');
        }
        $info = $this->Model->info($id);
        if (empty($info)) {
            return $this->error('文章不存在..');
        }
        $upData['click'] = ['inc', 1];
        $where[] = ['id', '=', $id];
        $this->Model->upInfo($upData, $where);
        $this->assign('title', '文章详情');
        $this->assign('info', $info);
        return $this->fetch('info');
    }
    /*------------------------------------------------------ */
    //-- 注册协议
    /*------------------------------------------------------ */
    public function registerAgreement()
    {

        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('register_agreement'));

        $this->assign('title', '注册协议');
        $this->assign('content', $content);
        return $this->fetch('register');
    }
    /*------------------------------------------------------ */
    //-- 关于我们
    /*------------------------------------------------------ */
    public function aboutUs()
    {

        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('about_us'));

        $this->assign('title', '关于我们');
        $this->assign('content', $content);
        return $this->fetch('other');
    }
    /*------------------------------------------------------ */
    //-- 分佣说明
    /*------------------------------------------------------ */
    public function dividendDirections()
    {
        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('dividend_directions'));
        $this->assign('title', '分佣说明');
        $this->assign('content', $content);
        return $this->fetch('other');
    }
    /*------------------------------------------------------ */
    //-- 身份升级说明
    /*------------------------------------------------------ */
    public function roleDirections()
    {
        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('role_directions'));
        $this->assign('title', '身份升级说明');
        $this->assign('content', $content);
        return $this->fetch('other');
    }
    /*------------------------------------------------------ */
    //-- 购买身份商品协议
    /*------------------------------------------------------ */
    public function roleGoodsDirections()
    {
        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('role_goods_directions'));
        $this->assign('title', '商品购买协议');
        $this->assign('content', $content);
        return $this->fetch('other');
    }
    /*------------------------------------------------------ */
    //-- 头条列表
    /*------------------------------------------------------ */
    public function headline()
    {
        $catList = (new ArticleCategoryModel)->getRows();
        foreach ($catList as $key => $cat) {
            $where = [];
            if ($cat['pid'] > 0) {
                unset($catList[$key]);
                continue;
            }
            $children = explode(',', $cat['children']);
            $where[] = ['cid', 'in', $children];
            $where[]=['type','=',1];//类型-头条
            $where[] = ['is_show', '=', 1];
            $has = $this->Model->where($where)->count('id');
            if (empty($has)) {//没有该分类下的头条
                unset($catList[$key]);
                continue;
            }
        }
        $this->assign('title', '新闻头条');
        $this->assign('catList', $catList);
        return $this->fetch('headline');
    }
    /*------------------------------------------------------ */
    //-- 头条详情
    /*------------------------------------------------------ */
    public function headlineInfo()
    {
        $id = input('id', 0, 'intval');
        $HeadlineModel = new \app\shop\model\HeadlineModel();
        $info = $HeadlineModel->info($id);
        if ($id < 1) {
            return $this->error('传参失败.');
        }
        if (empty($info)) {
            return $this->error('文章已被删除..');
        }
        $this->assign('title', '头条详情');
        $this->assign('info', $info['article']);
        return $this->fetch('info');
    }
    /*------------------------------------------------------ */
    //-- 新手教程
    /*------------------------------------------------------ */
    public function newUserCourse()
    {

        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('new_user_course'));

        $this->assign('title', '新手教程');
        $this->assign('content', $content);
        return $this->fetch('register');
    }
    /*------------------------------------------------------ */
    //-- 叮叮快讯
    /*------------------------------------------------------ */
    public function newsFlash()
    {
        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('news_flash'));

        $this->assign('title', '叮叮快讯');
        $this->assign('content', $content);
        return $this->fetch('register');
    }
    /*------------------------------------------------------ */
    //-- 消息通知内容
    /*------------------------------------------------------ */
    public function messageInfo()
    {
        $UserMessageModel = new UserMessageModel();
        $MessageModel = new MessageModel();

        $id = input('id',0,'intval');
        if ($id < 1) return $this->error('传参失败.');
             
        $info = $UserMessageModel->where(['rec_id' => $id,'user_id' => $this->userInfo['user_id']])->find();
        $parMess = $MessageModel->where(['message_id' => $info['ext_id']])->find();

        if (empty($info) || empty($parMess) ) return $this->error('消息不存在..');
        if ($info['show_end_date'] < time() || $parMess['show_end_date'] < time()) return $this->error('消息已过期..');
        if ($parMess['status']) return $this->error('消息已失效..');

        $content = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', $info['message_content']);

        $this->assign('title', '消息通知');
        $this->assign('content', $content);
        return $this->fetch('register');
    }
}

?>