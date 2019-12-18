<?php

namespace app\favour\model;

use app\BaseModel;
use think\facade\Cache;
use  app\favour\model\FavourModel;
use app\shop\model\GoodsModel;

//*------------------------------------------------------ */
//-- 限时优惠--主商品表
/*------------------------------------------------------ */

class FavourGoodsModel extends BaseModel
{
    protected $table = 'favour_goods';
    public $pk = 'fg_id';
    protected $mkey = 'favour_goods_mkey_';

    /*------------------------------------------------------ */
    //-- 清除活动商品缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($fg_id = 0)
    {
        if ($fg_id > 0) {
            Cache::rm($this->mkey . 'info_' . $fg_id);
        }
    }

    public function cleanGoodscache($goods_id = 0)
    {
        Cache::rm($this->mkey . 'is_favour_' . $goods_id);
    }

    /*------------------------------------------------------ */
    //-- 获取活动商品信息
    //-- $fg_id int 活动商品id
    /*------------------------------------------------------ */
    public function info($fg_id)
    {
        $goods = Cache::get($this->mkey . 'info_' . $fg_id);
        if (empty($goods)) {
            $goods = $this->where('fg_id', $fg_id)->find();
            if (empty($goods) == true) return array();
            $goods = $goods->toArray();
            $goods['dateArr'] = explode(',', $goods['date_slot']);
            $goods['weekArr'] = explode(',', $goods['week_slot']);
            $goods['cycleArr'] = explode(',', $goods['time_slot']);
            Cache::set($this->mkey . 'info_' . $fg_id, $goods, 600);
        }
        return $goods;
    }

    /*------------------------------------------------------ */
    //-- 更新
    /*------------------------------------------------------ */
    public function upInfo($fg_id, $data)
    {
        $data['update_time'] = time();
        $res = $this->where('fg_id', $fg_id)->update($data);
        if ($res < 1) return false;
        $this->cleanMemcache($fg_id);
        return true;
    }

    /*------------------------------------------------------ */
    //-- 清空档期记录--当档期间隔或活动开始时间变动时
    /*------------------------------------------------------ */
    public function clearTimeSlot()
    {
        $where[] = ['fg_id', '>', 0];
        $this->where($where)->update(['time_slot' => '']);
    }

    public function getGoodsList()
    {
        $goodsList = Cache::get($this->mkey . 'list');
        if (empty($goodsList)) {
            $time = time();
            $toDay = date("Y-m-d", $time);
            $toWeek = date('w', $time);
            $cycleList = (new FavourModel)->getCycleList();//档期列表

            $where[] = ['fg.status', '=', 1];
            $where[] = ['fa.status', '=', 1];
            $where[] = ['fa.start_date', '<=', $toDay];
            $where[] = ['fa.end_date', '>=', $toDay];

            $list = $this->alias('fg')->join("favour fa", 'fg.fa_id=fa.fa_id', 'inner')
                ->where($where)->field('fg.*,fa.favour_type')->order('fg.is_best DESC,fg.sort_order DESC')->select();
            if (count($list) > 0) $list = $list->toArray();

            $GoodsModel = new GoodsModel();
            $goodsList = [];
            foreach ($list as $key => $_goods) {
                if ($_goods['favour_type'] == 1) {
                    $date_slot = explode(',', $_goods['date_slot']);
                    if (!in_array($toDay, $date_slot)) {
                        continue;
                    }
                } elseif ($_goods['favour_type'] == 2) {
                    $week_slot = explode(',', $_goods['week_slot']);
                    if (!in_array($toWeek, $week_slot)) {
                        continue;
                    }
                } elseif ($_goods['favour_type'] == 3) {
                } else {
                    continue;
                }

                $goods = $GoodsModel->info($_goods['goods_id']);
                if ($goods['is_on_sale'] == 0) {//下架的商品显示
                    continue;
                }

                $_goods['goods_id'] = $goods['goods_id'];
                $_goods['goods_name'] = $goods['goods_name'];
                $_goods['short_name'] = $goods['short_name'];
                $_goods['cover'] = $goods['goods_thumb'];
                $_goods['is_spec'] = $goods['is_spec'];
                $_goods['exp_price'] = explode('.', $_goods['show_price']);
                $_goods['market_price'] = $goods['market_price'];
                $_goods['shop_price'] = $goods['shop_price'];

                //销量
                $_goods['sales'] = $_goods['virtual_sale'] + $_goods['actual_sale'];
                $percent = ($_goods['virtual_sale'] + $_goods['actual_sale']) / ($_goods['virtual_sale'] + $_goods['actual_sale'] + $_goods['stock']) * 100;
                $_goods['percent'] = intval($percent);

                $time_slot = explode(',', $_goods['time_slot']);
                foreach ($cycleList as $cycle) {
                    if (in_array($cycle['value'], $time_slot)) {
                        //已有相同商品，取价格最低
                        if ($goodsList[$cycle['name']][$_goods['goods_id']]) {
                            if ($goodsList[$cycle['name']][$_goods['goods_id']]['show_price'] < $_goods['show_price']) {
                                continue;
                            }
                        }
                        $goodsList[$cycle['name']][$_goods['goods_id']] = $_goods;
                    }
                }
            }
            Cache::set($this->mkey . 'list', $goodsList, 10);
        }
        return $goodsList;
    }

    /*------------------------------------------------------ */
    //-- 获取优惠活动信息
    /*------------------------------------------------------ */
    public function getFavourInfo($fg_id = 0, $sku_id = 0)
    {
        if (empty($fg_id)) return ['code' => 0, 'msg' => '参数不正确.'];
        $time = time();
        $toDay = date("Y-m-d", $time);
        $toWeek = date('w', $time);
        $cycleList = (new FavourModel)->getCycleList();//档期列表
        $cycle = [];
        foreach ($cycleList as $val) {
            if ($val['status'] == 1) {
                $cycle = $val;
                break;
            }
        }
        if (empty($cycle)) return ['code' => 0, 'msg' => '当前时间没有活动进行.'];

        //活动商品
        $favourGoods = $this->info($fg_id);
        if (empty($favourGoods)) return ['code' => 0, 'msg' => '限时优惠活动商品不存在.'];
        $favour = (new FavourModel)->info($favourGoods['fa_id']);

        if (empty($favour)) return ['code' => 0, 'msg' => '限时优惠活动不存在.'];
        if ($favour['status'] == 0) return ['code' => 0, 'msg' => '限时优惠活动未开启.'];
        if (strtotime($toDay) < strtotime($favour['start_date'])) return ['code' => 0, 'msg' => '限时优惠活动未开始.'];
        if (strtotime($toDay) > strtotime($favour['end_date'])) return ['code' => 0, 'msg' => '限时优惠活动已结束.'];

        //判断活动商品状态，日期，档期
        if ($favourGoods['status'] == 0) return ['code' => 0, 'msg' => '限时优惠活动未开启.'];
        switch ($favour['favour_type']) {
            case 1:
                if (in_array($toDay, $favourGoods['dateArr']) == false) return ['code' => 0, 'msg' => '限时优惠活动不在当前日期中.'];
                break;
            case 2:
                if (in_array($toWeek, $favourGoods['weekArr']) == false) return ['code' => 0, 'msg' => '限时优惠活动不在当前日期中.'];
                break;
        }
        if (in_array($cycle['value'], $favourGoods['cycleArr']) == false) return ['code' => 0, 'msg' => '限时优惠活动不在当前档期中.'];
        //获取活动商品信息
        $where[] = ['fg_id', '=', $favourGoods['fg_id']];
        $where[] = ['goods_id', '=', $favourGoods['goods_id']];
        $where[] = ['sku_id', '=', $sku_id];
        $favourGoodsInfo = (new FavourGoodsInfoModel)->where($where)->find();
        if (empty($favourGoodsInfo)) return ['code' => 0, 'msg' => '限时优惠活动商品信息不存在.'];
        $favourGoodsInfo->toArray();
        if ($favourGoodsInfo['goods_number'] <= 0) return ['code' => 0, 'msg' => '限时优惠活动商品已售罄.'];
        $cycle['goods'] = $favourGoods;
        $cycle['goodsInfo'] = $favourGoodsInfo;
        return ['code' => 1, 'msg' => '', 'data' => $cycle];
    }

    /*------------------------------------------------------ */
    //-- 获得已下单的数量
    /*------------------------------------------------------ */
    public function getFavourBuyNum($promInfo, $user_id = 0)
    {
        //已购买数量
        $where[] = ['o.user_id', '=', $this->userInfo['user_id']];
        $where[] = ['og.goods_id', '=', $promInfo['data']['goods']['goods_id']];
        $where[] = ['og.prom_type', '=', 1];
        $where[] = ['og.prom_id', '=', $promInfo['data']['goods']['fg_id']];
        $where[] = ['o.order_status', 'in', [0, 1]];
        if ($promInfo['data']['goods_info']['sku_id'] > 0) {
            $where[] = ['og.sku_id', '=', $promInfo['data']['goods_info']['sku_id']];
        }
        $where[] = ['o.add_time', 'between', [$promInfo['data']['start_time'], $promInfo['data']['end_time']]];
        //已成功下单数量
        $ogNum = (new \app\shop\model\OrderModel)->alias('o')->join('shop_order_goods og', 'o.order_id=og.order_id', 'inner')
            ->where($where)->sum('goods_number');
        return $ogNum;
    }

    /*------------------------------------------------------ */
    //-- 判断商品是否有活动
    /*------------------------------------------------------ */
    public function checkIsFavour($goods_id = 0, $sku_id = 0)
    {
        $mkey = $this->mkey . 'is_favour_' . $goods_id;
        $favourInfo = Cache::get($mkey);
        if (empty($favourInfo)) {
            if (empty($goods_id)) return false;
            $time = time();
            $toDay = date("Y-m-d", $time);
            $toWeek = date('w', $time);
            $cycleList = (new FavourModel)->getCycleList();//档期列表

            //获取对应商品的活动数据
            $where[] = ['fg.goods_id', '=', $goods_id];
            $where[] = ['fg.status', '=', 1];
            $where[] = ['fa.status', '=', 1];
            $where[] = ['fa.start_date', '<=', $toDay];
            $where[] = ['fa.end_date', '>=', $toDay];
            $list = $this->alias('fg')->join("favour fa", 'fg.fa_id=fa.fa_id', 'inner')
                ->where($where)->field('fg.*,fa.favour_type')->order('fg.is_best DESC,fg.sort_order DESC')->select();
            if (count($list) > 0) $list = $list->toArray();

            $curFavourGoods = [];//当前进行
            $nextFavourGoods = [];//即将进行
            foreach ($list as $key => $_goods) {
                if ($_goods['favour_type'] == 1) {
                    $date_slot = explode(',', $_goods['date_slot']);
                    if (!in_array($toDay, $date_slot)) {
                        continue;
                    }
                } elseif ($_goods['favour_type'] == 2) {
                    $week_slot = explode(',', $_goods['week_slot']);
                    if (!in_array($toWeek, $week_slot)) {
                        continue;
                    }
                } elseif ($_goods['favour_type'] == 3) {
                } else {
                    continue;
                }

                $time_slot = explode(',', $_goods['time_slot']);
                foreach ($cycleList as $cycle) {
                    if (in_array($cycle['value'], $time_slot)) {
                        if ($cycle['status'] == 1) {
                            //已有相同商品，取价格最低
                            if ($curFavourGoods) {
                                if ($curFavourGoods['show_price'] < $_goods['show_price']) {
                                    continue;
                                }
                            }
                            $cycle['goods'] = $_goods;
                            $curFavourGoods = $cycle;
                        } elseif ($cycle['status'] == 0) {
                            //已存在预售活动商品
                            if ($nextFavourGoods) {
                                if (strtotime($nextFavourGoods['name']) <= strtotime($cycle['name']) && $nextFavourGoods['goods']['show_price'] < $_goods['show_price']) {
                                    continue;
                                }
                            }
                            $cycle['goods'] = $_goods;
                            $nextFavourGoods = $cycle;
                        }
                    }
                }
            }

            $favourInfo = [];
            if (empty($curFavourGoods) == false) {
                $favourInfo = $curFavourGoods;
            } elseif (empty($nextFavourGoods) == false) {
                $favourInfo = $nextFavourGoods;
            }
            Cache::set($mkey, $favourInfo, 10);
        }
        $favourInfo = $this->checkFavourIsAble($favourInfo, $sku_id);
        return $favourInfo;
    }

    /*------------------------------------------------------ */
    //--检查优惠活动是否有效
    /*------------------------------------------------------ */
    public function checkFavourIsAble($favourInfo, $sku_id = 0)
    {
        if (empty($favourInfo)) return $favourInfo['activity_is_on'] = 0;
        $favourInfo['activity_is_on'] = 1;
        $favourInfo['prom_type'] = 1;
        $favourInfo['prom_id'] = $favourInfo['goods']['fg_id'];
        //计算倒计时时间
        if ($favourInfo['status'] == 1) {//进行中
            $favourInfo['diff_time'] = strtotime($favourInfo['end'] . ":00") - time();
        } elseif ($favourInfo['status'] == 0) {//即将开始
            $favourInfo['diff_time'] = strtotime($favourInfo['start'] . ":00") - time();
        }

        //商品信息
        $goods = (new GoodsModel)->info($favourInfo['goods']['goods_id']);

        $favourInfo['goods']['exp_min_price'] = explode('.', $favourInfo['goods']['show_price']);
        $favourInfo['goods']['exp_max_price'] = $goods['exp_max_price'];
        $favourInfo['goods']['is_spec'] = $goods['is_spec'];

        $where = [];
        $where[] = ['fg_id', '=', $favourInfo['goods']['fg_id']];
        $where[] = ['goods_id', '=', $favourInfo['goods']['goods_id']];
        if ($sku_id > 0) {
            $where[] = ['sku_id', '=', $sku_id];
        }
        $goodsInfo = (new FavourGoodsInfoModel)->where($where)->find();

        if (empty($goodsInfo) == false) {
            $goodsInfo = $goodsInfo->toArray();
            $goodsInfo['exp_price'] = explode('.', $goodsInfo['goods_price']);
            if ($sku_id > 0) {
                $sub_goods = $goods['sub_goods'];
                $temp_key = array_column($sub_goods, 'sku_id');  //键值
                $sub_goods = array_combine($temp_key, $sub_goods);
                $goodsInfo['market_price'] = $sub_goods[$sku_id]['shop_price'];
            } else {
                $goodsInfo['market_price'] = $goods['shop_price'];
            }
        }
        $favourInfo['goods_info'] = $goodsInfo;
        return $favourInfo;
    }

    /*------------------------------------------------------ */
    //-- 商品库存&销量处理
    /*------------------------------------------------------ */
    public function evalFavourStore($cInfo, $type)
    {
        if ($type == 'cancel') {
            $updateGoods['stock'] = ['INC', $cInfo['goods_number']];
            $updateGoods['actual_sale'] = ['DEC', $cInfo['goods_number']];
        } else {
            $updateGoods['stock'] = ['DEC', $cInfo['goods_number']];
            $updateGoods['actual_sale'] = ['INC', $cInfo['goods_number']];
        }
        $res = $this->upInfo($cInfo['prom_id'], $updateGoods);

        $where[] = ['fg_id', '=', $cInfo['prom_id']];
        if ($cInfo['sku_id'] > 0) {
            $where[] = ['sku_id', '=', $cInfo['sku_id']];
        }
        if ($type == 'cancel') {
            $updateGoodsInfo['goods_number'] = ['INC', $cInfo['goods_number']];
            $updateGoodsInfo['sale'] = ['DEC', $cInfo['goods_number']];
        } else {
            $updateGoodsInfo['goods_number'] = ['DEC', $cInfo['goods_number']];
            $updateGoodsInfo['sale'] = ['INC', $cInfo['goods_number']];
        }
        $res = (new FavourGoodsInfoModel)->upInfo($updateGoodsInfo, $where);
    }

}
