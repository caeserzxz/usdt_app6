<?php

namespace app\favour\controller\api;

use app\ApiController;

use app\favour\model\FavourModel;
use app\favour\model\FavourGoodsModel;
use app\favour\model\FavourGoodsInfoModel;
use app\shop\model\GoodsModel;

/*------------------------------------------------------ */
//-- 限时优惠相关API
/*------------------------------------------------------ */

class Goods extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new FavourGoodsModel();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $list = $this->Model->getGoodsList();
        $return['code'] = 1;
        $return['list'] = $list;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取首页推荐列表
    /*------------------------------------------------------ */
    public function getBestList()
    {
        $time = time();
        $toTime = date("G", $time);
        $cycleList = (new FavourModel)->getCycleList();
        $goodsList = $this->Model->getGoodsList();
        $ableCycle = [];
        //获取需显示的档期
        foreach ($cycleList as $cycle) {
            if ($toTime >= $cycle['start'] && $toTime < $cycle['end']) {
                $cycle['status'] = 1;
                $cycle['_status'] = '抢购中';
                $ableCycle = $cycle;
                break;
            } elseif ($toTime < $cycle['start']) {
                $cycle['status'] = 0;
                $cycle['_status'] = '即将开抢';
            } elseif ($toTime >= $cycle['end']) {
                $cycle['status'] = 2;
                $cycle['_status'] = '已结束';
            }
        }
        $favour_show_num = settings('favour_show_num');
        $goodsArr = [];
        foreach ($goodsList[$ableCycle['name']] as $goods) {
            if ($favour_show_num > 0 && count($goodsArr) >= $favour_show_num) break;
            if ($goods['is_best'] != 1) continue;
            $goodsArr[] = $goods;
        }
        $return['code'] = 1;
        $ableCycle['list'] = $goodsArr;
        $return['data'] = $ableCycle;
        return $this->ajaxReturn($return);
    }

    public function checkIsFavour()
    {
        $goods_id = input('goods_id', 0);
        $this->Model->checkIsFavour($goods_id = $goods_id);
    }

    public function getFavourInfos()
    {
        $fg_id = input('fg_id', 0);
        $sku_id = input('sku_id', 0);
        $result = $this->Model->getFavourInfo($fg_id, $sku_id);
        $where[] = ['fg_id', '=', 28];
        $updateGoodsInfo['goods_number'] = ['DEC', 1];
        $updateGoodsInfo['sale'] = ['INC', 1];
        $res = (new FavourGoodsInfoModel)->upInfo($updateGoodsInfo, $where);
    }


}
