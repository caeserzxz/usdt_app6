<?php

namespace app\shop\controller\sys_admin;

use app\mainadmin\controller\Article as Article;

/**
 * 头条管理
 * Class Index
 * @package app\store\controller
 */
class Headline extends Article
{
    public $_field = '';
    public $_pagesize = '';

    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        $this->type=1;//设置类型为新闻头条
        parent::initialize();
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        return $this->fetch();
    }

}
