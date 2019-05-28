<?php
/*------------------------------------------------------ */
//-- 秒杀相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\second\controller;
use app\ClientbaseController;
use app\second\model\SecondModel;

class Index  extends ClientbaseController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new SecondModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){
        $this->assign('title', '限时秒杀');
        return $this->fetch('index');
    }
}