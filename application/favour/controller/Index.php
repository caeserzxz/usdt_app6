<?php
/*------------------------------------------------------ */
//-- 限时优惠-相关
//-- Author: iqgmy
/*------------------------------------------------------ */

namespace app\favour\controller;

use app\ClientbaseController;
use app\favour\model\FavourModel;
use app\favour\model\FavourGoodsModel;
use app\favour\model\FavourGoodsInfoModel;

class Index extends ClientbaseController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new FavourModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $cycleList = $this->Model->getCycleList();
        $goodsList = (new FavourGoodsModel)->getGoodsList();
        $ableCycleList = [];
        //过滤无效的档期
        foreach ($cycleList as $cycle) {
            if (count($goodsList[$cycle['name']]) <= 0) {
                continue;
            }
            $ableCycleList[] = $cycle;
        }
        $this->assign('cycleList', $ableCycleList);
        $this->assign('title', '限时优惠');
        return $this->fetch('index');
    }
}