<?php
namespace app\integral\model;
use app\BaseModel;
use think\facade\Cache;
use app\shop\model\GoodsModel;
use app\shop\model\SkuCustomModel;

//*------------------------------------------------------ */
//-- 积分商品
/*------------------------------------------------------ */
class IntegralGoodsModel extends BaseModel
{

    protected $table = 'integral_goods';
    public $pk = 'ig_id';
    protected $mkey = 'integral_goods_mkey';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($fg_id = 0)
    {
        Cache::rm($this->mkey . $fg_id);
    }
    /*------------------------------------------------------ */
    //-- 获取积分商品信息
    //-- $ig_id int 积分商品id
    //-- $hideSettle bool 是否隐藏供货价
    /*------------------------------------------------------ */
    public function info($ig_id,$hideSettle = true)
    {
        $igInfo = Cache::get($this->mkey . $ig_id);
        if (empty($igInfo)) {
            $igInfo = $this->where('ig_id', $ig_id)->find();
            if (empty($igInfo) == true) return array();
            $igInfo = $igInfo->toArray();
            Cache::set($this->mkey . $ig_id, $igInfo, 30);
        }
        $GoodsModel = new GoodsModel();
        $goods = $GoodsModel->info($igInfo['goods_id'],$hideSettle);
        $goods['imgsList'] = $GoodsModel->getImgsList($goods['goods_id']);//获取图片
        if ($goods['is_spec'] == 1) {//多规格处理
            $igInfo['goods'] = (new IntegralGoodsListModel)->where('ig_id', $ig_id)->select()->toArray();
            $this->getGoodsSku($goods, $igInfo);
            $goods['skuImgs'] = $GoodsModel->getImgsList($goods['goods_id'], true, true);//获取sku图片
        } else {
            $goods['shop_price'] = $goods['sale_price'];
            $goods['exp_sale_price'] = explode('.', $goods['sale_price']);
            $igGoods = (new IntegralGoodsListModel)->where('ig_id', $ig_id)->find()->toArray();
            $goods['ig_number'] = $igGoods['goods_number'];
            $goods['sale_num'] = $igGoods['sale_num'];
            $goods['integral'] = $igGoods['integral'];
        }
        $igInfo['goods'] = $goods;
        unset($goods);
        $time = time();
        $igInfo['is_on_sale'] = 1;//判断是否进行兑换进行中
        if ($igInfo['start_date'] > $time) {
            $igInfo['is_on_sale'] = 0;//未开始
        } elseif ($igInfo['end_date'] < $time) {
            $igInfo['is_on_sale'] = 9;//已结束
        }
        return $igInfo;
    }

    /*------------------------------------------------------ */
    //-- 获取商品规格及子商品信息
    //-- $goods array 商品信息
    //-- $igInfo array 拼团相关
    /*------------------------------------------------------ */
    public function getGoodsSku(&$goods, &$igInfo)
    {
        if ($goods['is_spec'] == 0) return $goods;
        $lstSKUVal = $products = array();
        $skuarr = array();
        $sub_goods = [];

        foreach ($goods['sub_goods'] as $row) {
            $sub_goods[$row['sku_id']] = $row;
        }

        unset($goods['sub_goods']);
        foreach ($igInfo['goods'] as $row) {
            $sku = $row['sku'];
            $_row = $sub_goods[$row['sku_id']];
            $skuval[] = $_row['sku_val'];
            $_row['goods_number'] = $row['goods_number'];
            $_row['BuyMaxNum'] = $row['goods_number'];
            $_row['integral'] = $row['integral'];
            $_row['sale_num'] = $row['sale_num'];
            $_row['exp_sale_price'] = explode('.', $_row['sale_price']);
            if ($igInfo['limit_num'] > 0) {
                $_row['BuyMaxNum'] = $_row['BuyMaxNum'] > $igInfo['limit_num'] ? $igInfo['limit_num'] : $_row['BuyMaxNum'];
            }

            $sku_val = explode(':', $_row['sku_val']);
            $_sval = array();
            foreach ($sku_val as $key => $sval) {
                $_sval[] = $sval;
                $skuarr[$sval] = 1;
            }

            $goods['sub_goods'][$_row['sku_val']] = $_row;
        }
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
            $row['is_show'] = empty($row['lstVal'][1]) ? 0 : 1;
            $row['new_name'] = $row['name'];
            $lstSKUArr[] = $row;
        }
        unset($lstSKUVal, $skuval, $isdef, $skurows);
        $goods['lstSKUArr'] = $lstSKUArr;
        $goods['skuCstom'] = $skuCstom;

        return true;
    }}
