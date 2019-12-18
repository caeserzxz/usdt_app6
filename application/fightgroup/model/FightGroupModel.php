<?php

namespace app\fightgroup\model;

use app\BaseModel;
use think\facade\Cache;

use app\shop\model\GoodsModel;
use app\shop\model\SkuCustomModel;

//*------------------------------------------------------ */
//-- 拼团
/*------------------------------------------------------ */

class FightGroupModel extends BaseModel
{
    protected $table = 'fightgroup';
    public $pk = 'fg_id';
    protected $mkey = 'fightgroup_mkey';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($fg_id = 0)
    {
        if ($fg_id > 0) {
            Cache::rm($this->mkey . $fg_id);
        }
        Cache::rm($this->mkey . 'best');
    }
    /*------------------------------------------------------ */
    //-- 获取拼团信息
    //-- $fg_id int 拼团id
    //-- $hideSettle bool 是否隐藏供货价，默认隐藏
    /*------------------------------------------------------ */
    public function info($fg_id,$hideSettle = true)
    {
        $fgInfo = Cache::get($this->mkey . $fg_id);
        if (empty($fgInfo)) {
            $fgInfo = $this->where('fg_id', $fg_id)->find();
            if (empty($fgInfo) == true) return array();
            $fgInfo = $fgInfo->toArray();
            $fgInfo['exp_price'] = explode('.', $fgInfo['show_price']);
            Cache::set($this->mkey . $fg_id, $fgInfo, 30);
        }

        $goods = (new GoodsModel)->info($fgInfo['goods_id'],$hideSettle);
        if ($goods['is_spec'] == 1) {//多规格处理
            $fgInfo['goods'] = (new FightGoodsModel)->where('fg_id', $fg_id)->select()->toArray();
            $this->getGoodsSku($goods, $fgInfo);
        } else {
            $goods['shop_price'] = $goods['sale_price'];
            $goods['exp_sale_price'] = explode('.', $goods['sale_price']);
            $fgGoods = (new FightGoodsModel)->where('fg_id', $fg_id)->find()->toArray();
            $goods['fg_number'] = $fgGoods['fg_number'];
            $goods['sale_num'] = $fgGoods['sale_num'];
            $goods['sale_price'] = $fgGoods['fg_price'];
            $goods['exp_price'] = explode('.', $fgGoods['fg_price']);
        }
        $fgInfo['goods'] = $goods;

        $time = time();
        $fgInfo['is_on_sale'] = 1;//判断是否进行拼团销售中
        if ($fgInfo['start_date'] > $time) {
            $fgInfo['is_on_sale'] = 0;//未开始
        } elseif ($fgInfo['end_date'] < $time) {
            $fgInfo['is_on_sale'] = 9;//已结束
        }

        if ($fgInfo['is_on_sale'] == 1) {
            $goods_num = 0;
            if ($fgInfo['goods']['is_spec'] == 1) {//多规格处理
                foreach ($fgInfo['goods'] as $goods) {
                    $goods_num += $goods['fg_number'];
                }
            } else {
                $goods_num += $fgInfo['goods']['fg_number'];
            }
            if ($goods_num < $fgInfo['success_num']) {//不能发起拼团，可购买
                $fgInfo['is_on_sale'] = 2;
            }
        }

        return $fgInfo;
    }
    /*------------------------------------------------------ */
    //-- 获取首页推荐拼团
    /*------------------------------------------------------ */
    public function getBestList()
    {
        $goodsList = Cache::get($this->mkey . 'best');
        $fightgroup_show_num = settings('fightgroup_show_num');
        $fightgroup_show_num = empty($fightgroup_show_num) ? 300 : $fightgroup_show_num;
        if (empty($goodsList)) {
            $time = time();
            $where[] = ['status', '=', 1];
            $where[] = ['is_best', '=', 1];
            $where[] = ['start_date', '<', $time];
            $where[] = ['end_date', '>', $time];
            $list = $this->where($where)->order('is_best DESC,sort_order DESC')->limit($fightgroup_show_num)->select();
            if (count($list) > 0) $list = $list->toArray();
            $GoodsModel = new GoodsModel();
            $goodsList = [];
            foreach ($list as $key => $_goods) {
                $goods = $GoodsModel->info($_goods['goods_id']);
                if ($goods['is_on_sale'] == 0) {//下架的商品显示
                    continue;
                }
                $_goods['goods_id'] = $goods['goods_id'];
                $_goods['goods_name'] = $goods['goods_name'];
                $_goods['short_name'] = $goods['short_name'];
                $_goods['goods_thumb'] = $goods['goods_thumb'];
                $_goods['is_spec'] = $goods['is_spec'];
                $_goods['exp_price'] = explode('.', $_goods['show_price']);
                $_goods['market_price'] = $goods['market_price'];
                $_goods['shop_price'] = $goods['shop_price'];
                $goodsList[] = $_goods;
            }
            Cache::set($this->mkey . 'best', $goodsList, 60);
        }
        return $goodsList;
    }

    /*------------------------------------------------------ */
    //-- 获取商品图片
    /*------------------------------------------------------ */
    public function getImg($goods_id)
    {
        $GoodsModel = new GoodsModel();
        $imgsList = $GoodsModel->getImgsList($goods_id);//获取图片
        $skuImgs = $GoodsModel->getImgsList($goods_id, true, true);//获取sku图片
        return [$imgsList, $skuImgs];
    }
    /*------------------------------------------------------ */
    //-- 获取商品规格及子商品信息
    //-- $goods array 商品信息
    //-- $fgInfo array 拼团相关
    /*------------------------------------------------------ */
    public function getGoodsSku(&$goods, &$fgInfo)
    {
        if ($goods['is_spec'] == 0) return $goods;
        $lstSKUVal = $products = array();
        $skuarr = array();
        $sub_goods = [];

        $sub_goods = $goods['sub_goods'];

        $fgGoods = array_column($fgInfo['goods'], null, 'sku_id');//重置数组的键
        $goods['fg_number'] = 0;
        foreach ($sub_goods as $key => $row) {
            $sku = $row['sku'];
            $_row = $row;
            $skuval[] = $_row['sku_val'];
            $_row['is_group'] = 0;
            $_row['fg_number'] = 0;
            $_row['BuyMaxNum'] = $row['goods_number'];
            $_row['exp_shop_price'] = explode('.', $_row['shop_price']);
            $goods['goods_number'] += $row['goods_number'];

            if ($fgGoods[$row['sku_id']]) {//规格活动存在
                $_row['is_group'] = 1;
                $_row['fg_number'] = $fgGoods[$row['sku_id']]['fg_number'];
                $goods['fg_number'] += $_row['fg_number'];
                $_row['sale_price'] = $fgGoods[$row['sku_id']]['fg_price'];
                $_row['sale_num'] = $fgGoods[$row['sku_id']]['sale_num'];
                $_row['exp_price'] = explode('.', $fgGoods[$row['sku_id']]['fg_price']);
                $_row['exp_sale_price'] = explode('.', $_row['sale_price']);
                if ($fgInfo['limit_num'] > 0) {
                    $_row['BuyMaxNum'] = $_row['BuyMaxNum'] > $fgInfo['limit_num'] ? $fgInfo['limit_num'] : $fgGoods[$row['sku_id']]['BuyMaxNum'];
                }
            }

            $sku_val = explode(':', $_row['sku_val']);
            $_sval = array();
            foreach ($sku_val as $keys => $sval) {
                $_sval[] = $sval;
                $skuarr[$sval] = 1;
            }
            $sub_goods[$key] = $_row;
        }
        $goods['sub_goods'] = $sub_goods;

        $goods['exp_sale_price'] = explode('.', $goods['sale_price']);
        unset($sub_goods);
        $skuarr = array_keys($skuarr);
        $skuval = $sku . ':' . join(':', $skuval);
        $skuval = explode(':', $skuval);
        $where[] = ['id', 'IN', $skuval];

        $SkuCustomModel = new SkuCustomModel();
        $skurows = $SkuCustomModel->field('id,val,speid')->where($where)->order('id ASC')->select()->toArray();
        $skuCstom = $isdef = array();
        foreach ($skurows as $row) {
            $skuCstom[$row['id']] = $row['val'];
            if ($row['speid'] == 0) {
                $lstSKUVal[$row['id']]['name'] = $row['val'];
            } else {
                $speid = $row['speid'];
                unset($row['speid']);
                $row['issel'] = in_array($row['id'], $skuarr) ? 1 : 0;
                if ($row['issel'] == 1 && empty($isdef[$speid]) == true) {
                    $row['isdef'] = $isdef[$speid] = 1;
                }
                $lstSKUVal[$speid]['lstVal'][] = $row;
            }
        }

        foreach ($lstSKUVal as $row) {
            $row['is_show'] = empty($row['lstVal'][0]) ? 0 : 1;
            $row['new_name'] = $row['name'];
            $lstSKUArr[] = $row;
        }
        unset($lstSKUVal, $skuval, $isdef, $skurows);
        $goods['lstSKUArr'] = $lstSKUArr;
        $goods['skuCstom'] = $skuCstom;
        return true;
    }
}
