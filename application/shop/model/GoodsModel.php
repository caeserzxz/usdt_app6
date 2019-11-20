<?php

namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 商品表
/*------------------------------------------------------ */

class GoodsModel extends BaseModel
{
    protected $table = 'shop_goods';
    public $pk = 'goods_id';
    protected $mkey = 'shop_goods_mkey_';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($goods_id = 0)
    {
        Cache::rm($this->mkey . $goods_id);
        Cache::rm('shop_goods_prices_' . $goods_id);
        Cache::rm('shop_goods_attribute_val_' . $goods_id);
    }
    /*------------------------------------------------------ */
    //-- 获取商品品牌
    /*------------------------------------------------------ */
    public function getBrandList($cid = 0)
    {
        $BrandModel = new BrandModel();
        return $BrandModel->getRows($cid);
    }
    /*------------------------------------------------------ */
    //-- 获取商品分类
    /*------------------------------------------------------ */
    public function getClassList($pid = false)
    {
        $CategoryModel = new CategoryModel();
        return $CategoryModel->getRows($pid);
    }
    /*------------------------------------------------------ */
    //-- 获取首页商品分类
    /*------------------------------------------------------ */
    public function getIndexClass()
    {
        $mkey = 'IndexClassGoodsList';
        $catList = Cache::get($mkey);
        if (empty($catList) == false) return $catList;
        $CategoryModel = new CategoryModel();
        $catRows = $CategoryModel->getRows();
        $where[] = ['is_index', '=', 1];
        $where[] = ['status', '=', 1];
        $catList = $CategoryModel->where($where)->order('sort_order,pid ASC')->select()->toArray();
        foreach ($catList as $key => $cat) {
            $gwhere = [];
            $gwhere[] = ['cid', 'in', $catRows[$cat['id']]['children']];
            $gwhere[] = ['isputaway', '=', 1];
            $gwhere[] = ['is_delete', '=', 0];
            $gwhere[] = ['store_id', '=', 0];
            $gooodsList = $this->where($gwhere)->order('is_hot desc,sort_order DESC,goods_id desc')->limit(3)->column('goods_id');
            foreach ($gooodsList as $_key => $goods_id) {
                $gooodsList[$_key] = $this->info($goods_id);
            }
            $catList[$key]['gooodsList'] = $gooodsList;
        }
        Cache::set($mkey, $catList, 60);
        return $catList;
    }
    /*------------------------------------------------------ */
    //-- 获取首页推荐商品
    /*------------------------------------------------------ */
    public function getIndexBestGoods()
    {
        $mkey = 'IndexBestGoodsList';
        $goodsList = Cache::get($mkey);
        if (empty($goodsList) == false) return $goodsList;
        $gwhere[] = ['isputaway', '=', 1];
        $gwhere[] = ['is_delete', '=', 0];
        $gwhere[] = ['store_id', '=', 0];
        $gooodsList = $this->where($gwhere)->order('is_best desc,sort_order DESC,goods_id desc')->limit(10)->column('goods_id');
        foreach ($gooodsList as $key => $goods_id) {
            $gooodsList[$key] = $this->info($goods_id);
        }
        Cache::set($mkey, $gooodsList, 60);
        return $gooodsList;
    }
    /*------------------------------------------------------ */
    //-- 获取分类页相关分类信息
    /*------------------------------------------------------ */
    public function getClassToAllSort()
    {
        $mkey = 'ClassToAllSort';
        $list = Cache::get($mkey);
        if (empty($list['rows']) == false) return $list;
        $CategoryModel = new CategoryModel();
        $rows = $CategoryModel->getRows();
        $list['best'] = array();
        $bestids = array();
        foreach ($rows as $row) {
            if ($row['status'] == 0) continue;
            $children = explode(',', $row['children']);
            $row['children'] = array();
            if ($row['is_best'] == 1) {
                if (in_array($row['id'], $bestids) == false && count($children) > 1) {//不存在的,并且有下级才写入
                    $row['children'] = array();
                    foreach ($children as $id) {
                        $rowb = $rows[$id];
                        if ($rowb['status'] == 0) continue;
                        if ($rowb['pid'] == $row['id']) {//必须直属下级才执行
                            $row['children'][] = $id;
                        }
                    }
                    if (empty($row['children']) == false) {//如果没有直属下级不执行
                        $list['best'][] = $row;
                        $bestids = array_merge($bestids, $row['children']);
                    }
                }
            }
            if ($row['pid'] > 0) continue;//非顶级分类不执行
            $row['children'] = array();
            foreach ($children as $id) {
                $rowb = $rows[$id];
                if ($rowb['status'] == 0 || $rowb['pid'] == 0) continue;
                $row['children'][$rowb['pid']][] = $id;
            }
            foreach ($row['children'][$row['id']] as $key => $cid) {
                if (empty($row['children'][$cid]) == false) {
                    unset($row['children'][$row['id']][$key]);
                }
            }
            if (empty($row['children'][$row['id']])) {
                unset($row['children'][$row['id']]);
            }
            $list['rows'][] = $row;
        }
        Cache::set($mkey, $list, 60);
        return $list;
    }
    /*------------------------------------------------------ */
    //-- 获取商品模型
    /*------------------------------------------------------ */
    public function getModelList()
    {
        $GoodsModelModel = new GoodsModelModel();
        return $GoodsModelModel->getRows();
    }

    /*------------------------------------------------------ */
    //-- 获取商品模型类型数据
    /*------------------------------------------------------ */
    public function getAttributeVal($goods_id = 0)
    {
        $AttributeValModel = new AttributeValModel();
        return $AttributeValModel->getRows($goods_id);
    }
    /*------------------------------------------------------ */
    //-- 获取商品图片
    /*------------------------------------------------------ */
    public function getImgsList($goods_id = 0, $is_sku = false, $byKey = false)
    {
        $mkey = 'goods_imgs_list_' . $goods_id . '_' . $is_sku . '_' . $byKey;
        $imgs = Cache::get($mkey);
        if (empty($imgs) == false) {
            return $imgs;
        }
        $where[] = ['goods_id', '=', $goods_id];
        $GoodsImgsModel = new GoodsImgsModel();
        if ($is_sku == true) {
            $where[] = ['sku_val', '<>', ''];
            $rows = $GoodsImgsModel->where($where)->order('sort_order ASC')->select()->toArray();
            if ($byKey == false) return $rows;
            $imgs = array();
            foreach ($rows as $row) {
                $imgs[$row['sku_val']] = $row['goods_thumb'];
            }

        } else {
            $where[] = ['sku_val', '=', ''];
            $imgs = $GoodsImgsModel->where($where)->order('sort_order ASC')->select()->toArray();
        }
        Cache::set($mkey, $imgs, 30);
        return $imgs;
    }
    /*------------------------------------------------------ */
    //-- 获取商品图片，后台调用
    /*------------------------------------------------------ */
    public function getImgsListAdmin($where = array(), $is_sku = false, $byKey = false)
    {
        $GoodsImgsModel = new GoodsImgsModel();
        if ($is_sku == true) {
            $where[] = ['sku_val', '<>', ''];
            $rows = $GoodsImgsModel->where($where)->order('sort_order ASC')->select()->toArray();
            if ($byKey == false) return $rows;
            $list = array();
            foreach ($rows as $row) {
                $list[$row['sku_val']] = $row['goods_thumb'];
            }
            return $list;
        } else {
            $where[] = ['sku_val', '=', ''];
            return $GoodsImgsModel->where($where)->order('sort_order ASC')->select()->toArray();
        }
    }
    /*------------------------------------------------------ */
    //-- 获取商品价格(等级或)
    /*------------------------------------------------------ */
    public function getPrices($goods_id = 0)
    {
        $GoodsPricesModel = new GoodsPricesModel();
        return $GoodsPricesModel->getRows($goods_id);
    }
    /*------------------------------------------------------ */
    //-- 获取商品阶梯价格
    /*------------------------------------------------------ */
    public function getVolumePrice($goods_id = 0)
    {
        $GoodsVolumePriceModel = new GoodsVolumePriceModel();
        return $GoodsVolumePriceModel->getRows($goods_id);
    }
    /*------------------------------------------------------ */
    //-- 是否收藏
    /*------------------------------------------------------ */
    public function isCollect($goods_id = 0, $uid = 0)
    {
        if ($goods_id < 1 || $uid < 1) return 0;
        $GoodsCollectModel = new GoodsCollectModel();
        $where['goods_id'] = $goods_id;
        $where['user_id'] = $uid;
        $where['status'] = 1;
        return $GoodsCollectModel->where($where)->count('collect_id');
    }
    /*------------------------------------------------------ */
    //-- 更新
    /*------------------------------------------------------ */
    public function upInfo(&$data, $where)
    {
        $data['update_time'] = time();
        $res = $this->where($where)->update($data);
        if ($res < 1) return false;
        $this->cleanMemcache($where['goods_id']);
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取商品信息
    //-- $goods_id int 商品id
    //-- $hideSettle bool 是否隐藏供货价，默认隐藏
    /*------------------------------------------------------ */
    public function info($goods_id, $hideSettle = true)
    {
        $goods = Cache::get($this->mkey . $goods_id);
        if (empty($goods)) {
            $goods = $this->where('goods_id', $goods_id)->find();
            if (empty($goods) == true) return array();
            $goods = $goods->toArray();
            $goods['lstSKUArr'] = array();
            $goods['sub_goods'] = array();
            $goods['skuCstom'] = array();
            if ($goods['is_spec'] == 1) {
                $this->getGoodsSku($goods, $hideSettle);
            }
            Cache::set($this->mkey . $goods_id, $goods, 600);
        }

        if ($hideSettle == true) {
            if ($goods['is_spec'] == 1) {
                foreach ($goods['sub_goods'] as $key => $sub) {
                    unset($sub['settle_price']);
                }
            }
            unset($goods['settle_price'], $goods['settle_min_price'], $goods['settle_max_price']);//隐藏供货价
        }

        $goods['is_on_sale'] = 0;
        if ($goods['is_delete']) return $goods;

        $time = time();

        $goods['is_on_sale'] = 0;
        if ($goods['isputaway'] == 1){
            $goods['is_on_sale'] = 1;
        }elseif ($goods['isputaway'] == 2){
            //自动上下架判断
            if ($goods['added_time'] <= $time && $goods['shelf_time'] >= $time) {
                $goods['is_on_sale'] = 1;
            } elseif ($goods['shelf_time'] < $time) {
                $update['isputaway'] = 0;
                $goods['isputaway'] = 0;
            }
        }

        if (empty($update) == false) {
            $where['goods_id'] = $goods_id;
            $this->upInfo($update, $where);
        }

        $goods['sale_price'] = $goods['shop_price'];
        //判断促销是否在时间范围内
        if ($goods['promote_start_date'] >= $time || $goods['promote_end_date'] <= $time) {
            $goods['is_promote'] = 0;
        }
        //促销中处理
        if ($goods['is_promote'] == 1) {
            if ($goods['is_spec'] == 1) {
                $prices = [];
                foreach ($goods['sub_goods'] as $key => $sku) {
                    $sku['is_promote'] = 0;
                    if ($goods['is_promote'] == 1 && $sku['promote_price'] > 0) {
                        $sku['sale_price'] = $sku['promote_price'];
                        $sku['is_promote'] = 1;
                    }
                    $prices[] = $sku['shop_price'];
                    $sku['exp_price'] = explode('.', $sku['sale_price']);
                    $goods['sub_goods'][$key] = $sku;
                }
                $goods['min_price'] = min($prices);
                $goods['max_price'] = max($prices);
            } elseif ($goods['promote_price'] > 0){
                $goods['sale_price'] = $goods['promote_price'];
            }
        }
        //单规格商品最小与最大价格都为销售价
        if ($goods['is_spec'] == 0) {
            $goods['min_price'] = $goods['max_price'] = $goods['sale_price'];
        }

        $goods['exp_min_price'] = explode('.', $goods['min_price']);
        $goods['exp_max_price'] = explode('.', $goods['max_price']);
        $goods['exp_price'] = explode('.', $goods['sale_price']);
        $goods['sale_count'] = $goods['virtual_sale'] + $goods['sale_num'];
        $goods['collect_count'] = $goods['virtual_collect'] + $goods['collect_num'];

        return $goods;
    }
    /*------------------------------------------------------ */
    //-- 获取商品规格及子商品信息
    //-- $goods array 商品信息
    /*------------------------------------------------------ */
    public function getGoodsSku(&$goods)
    {

        if ($goods['is_spec'] == 0) return $goods;
        $lstSKUVal = $products = array();
        $GoodsSkuModel = new GoodsSkuModel();
        $gsrows = $GoodsSkuModel->where('goods_id', $goods['goods_id'])->select()->toArray();
        $skuarr = array();

        foreach ($gsrows as $row) {
            $sku = $row['sku'];
            $skuval[] = $row['sku_val'];
            $row['BuyMaxNum'] = $row['goods_number'];
            $row['sale_price'] = $row['shop_price'];
            $row['exp_price'] = explode('.', $row['sale_price']);
            if ($goods['limit_num'] > 0) {
                $row['BuyMaxNum'] = $row['BuyMaxNum'] > $goods['limit_num'] ? $goods['limit_num'] : $row['BuyMaxNum'];
            }
            if ($row['BuyMaxNum'] > 0) {
                $sku_val = explode(':', $row['sku_val']);
                $_sval = array();
                foreach ($sku_val as $key => $sval) {
                    $_sval[] = $sval;
                    $skuarr[$sval] = 1;
                }
            }

            $goods['sub_goods'][$row['sku_val']] = $row;
        }
        unset($row);
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
        return $goods;
    }

    /**------------------------------------------------------
     *获取商品当前身份和等级价格和下一级别价格
     * $goods_id int 商品ID
     * return $price array
     * ------------------------------------------------------ */
    public function getPriceList($goods_id = 0)
    {
        $prices['level'] = [];
        $prices['role'] = [];
        if ($goods_id < 1) return $prices;
        if (empty($this->userInfo) == true) return $prices;
        $rows =  (new GoodsPricesModel)->where('goods_id',$goods_id)->order('price DESC')->select();
        if (empty($rows)) return $prices;
        $role = (new \app\distribution\model\DividendRoleModel)->getRows();
        $level = (new \app\member\model\UsersLevelModel)->getRows();
        $_prices['role'] = [];
        $_prices['level'] = [];
        foreach ($rows as $row) {
            if ($row['type'] == 'role'){
                $_prices['role'][$row['by_id']] = ['name'=>$role[$row['by_id']]['role_name'],'price'=>$row['price']];
            }else{
                $_prices['level'][$row['by_id']] = ['name'=>$level[$row['by_id']]['level_name'],'price'=>$row['price']];
            }
        }
        //身份价格
        if ($this->userInfo['role_id'] == 0){
            $price = reset($_prices['role']);
            if (empty($price) == false){
                $prices['role'][] = $price;
            }
        }else{
            $is_next = 0;
            foreach ($_prices['role'] as $key=>$row){
                if ($is_next == 1) {
                    $is_next = 0;
                    $prices['role'][] = $row;
                }elseif ($this->userInfo['role_id'] == $key){
                    $prices['role'][] = $row;
                    $is_next = 1;
                }
            }
        }
        //等级价格
        $is_next = 0;
        foreach ($_prices['level'] as $key=>$row){
            if ($is_next == 1) {
                $is_next = 0;
                $prices['level'][] = $row;
            }elseif ($this->userInfo['level']['level_id'] == $key){
                $prices['level'][] = $row;
                $is_next = 1;
            }
        }

        return $prices;
    }
    /*------------------------------------------------------ */
    //-- 计算商品销售价
    /*------------------------------------------------------ */
    public function evalPrice(&$goods, $buyNum = 0, $spec = '')
    {
        $u_price = $vol_price = 0;
        if ($spec) {//多规格商品
            $sub_goods = $goods['sub_goods'][$spec];
            if ($sub_goods['is_promote'] == 1) {
                $goods['promote_price'] = $sub_goods['shop_price'];
            }
            $all_price[] = $sub_goods['sale_price'];
        } else {
            $all_price[] = $goods['sale_price'];
        }

        if (empty($this->userInfo) == false) {//计算会员价格
            $GoodsPricesModel = new GoodsPricesModel();
            if ($goods['level_price_type'] > 0) {
                if ($goods['level_price_type'] == 1) {//会员等级折扣
                    $all_price[] = $goods['shop_price'] * $this->userInfo['level']['level_pro'] / 100;
                } else {
                    $map['goods_id'] = $goods['goods_id'];
                    $map['type'] = 'level';
                    $map['by_id'] = $this->userInfo['level']['level_id'];
                    $u_price = $GoodsPricesModel->where($map)->value('price');
                    if ($u_price > 0) {
                        if ($goods['level_price_type'] == 2) {//自定义折扣
                            $all_price[] = $goods['shop_price'] * $u_price / 100;
                        } elseif ($goods['level_price_type'] == 3) {//指定固定售价(多规格的子商品价格统一售价)
                            $all_price[] = $u_price;
                        }
                    }
                }
            }
            //计算分销身份价格
            if ($this->userInfo['role_id'] > 0) {
                $map['goods_id'] = $goods['goods_id'];
                $map['type'] = 'role';
                $map['by_id'] = $this->userInfo['role_id'];
                $u_price = $GoodsPricesModel->where($map)->value('price');
                if ($u_price > 0) {
                    if ($goods['role_price_type'] == 1) {//自定义折扣
                        $all_price[] = $goods['shop_price'] * $u_price / 100;
                    } elseif ($goods['role_price_type'] == 2) {//指定固定售价(多规格的子商品价格统一售价)
                        $all_price[] = $u_price;
                    }
                }
            }
        }
        unset($map);
        $GoodsVolumePriceModel = new GoodsVolumePriceModel();
        $volume_price = $GoodsVolumePriceModel->where('goods_id', $goods['goods_id'])->select()->toArray();
        //计算阶梯价格
        foreach ($volume_price as $row) {
            if ($buyNum >= $row['number']) {
                if ($row['price'] > 0) {
                    if ($goods['volume_price_type'] == 1) {//指定固定售价
                        $all_price[] = $row['price'];
                    } elseif ($goods['volume_price_type'] == 2) {//指定固定售价(多规格的子商品价格统一售价)
                        $all_price[] = $goods['shop_price'] * $row['price'] / 100;
                    }
                }

            }
        }
        //判断促销价
        if ($goods['is_promote'] == 1 && $goods['promote_price'] > 0) {
            $all_price[] = $goods['promote_price'];
        }

        $arr['min_price'] = min($all_price);
        $arr['u_price'] = $u_price;
        $arr['vol_price'] = $vol_price;
        $arr['promote_price'] = $goods['promote_price'];
        //计算剩下阶梯价格
        foreach ($volume_price as $key => $row) {
            if ($row['volume_price'] > $arr['min_price']) {
                unset($volume_price[$key]);
            }
        }
        $arr['volume_price'] = $volume_price;
        return $arr;
    }
    /*------------------------------------------------------ */
    //-- 订单商品库存&销量处理
    /*------------------------------------------------------ */
    public function evalGoodsStore(&$goodsList = array(), $type = 'addOrder')
    {
        $GoodsSkuModel = new GoodsSkuModel();
        foreach ($goodsList as $grow) {
            $goods = $this->info($grow['goods_id']);
            if ($goods['is_spec'] == 1) {//多规格商品执行
                if ($type == 'cancel') {
                    $sub_data['goods_number'] = ['INC', $grow['goods_number']];
                    $data['sale_num'] = ['DEC', $grow['goods_number']];
                } else {
                    $sub_data['goods_number'] = ['DEC', $grow['goods_number']];
                    $data['sale_num'] = ['INC', $grow['goods_number']];
                }
                $sub_map['goods_id'] = $grow['goods_id'];
                $sub_map['sku_val'] = $grow['sku_val'];
                $res = $GoodsSkuModel->where($sub_map)->update($sub_data);
                if ($res < 1) return false;
                $res = $this->where('goods_id', $grow['goods_id'])->update($data);
                if ($res < 1) return false;
                $this->cleanMemcache($grow['goods_id']);
            } else {
                if ($type == 'cancel') {
                    $data['goods_number'] = ['INC', $grow['goods_number']];
                    $data['sale_num'] = ['DEC', $grow['goods_number']];
                } else {
                    $data['goods_number'] = ['DEC', $grow['goods_number']];
                    $data['sale_num'] = ['INC', $grow['goods_number']];
                }
                $res = $this->where('goods_id', $grow['goods_id'])->update($data);
                if ($res < 1) return false;
            }
            $this->cleanMemcache($grow['goods_id']);

        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 获取搜索记录
    /*------------------------------------------------------ */
    public function searchKeys($keyword = '')
    {   $user_id = $this->userInfo['user_id'];
        if(empty($user_id))return false;
        $keys = Cache::get('searchKeys' . $user_id);
        if (empty($keys)) $keys = array();
        if (empty($keyword) == false) {
            foreach ($keys as $key => $val) {
                if ($val == $keyword) {
                    unset($keys[$key]);
                }
            }
            if (count($keys) >= 10) {
                unset($keys[0]);
            }
            $keys[] = $keyword;
            session('searchKeys', $keys);
            $keys = Cache::set('searchKeys' . $user_id ,$keys,2592000);
        }
        return array_reverse($keys);
    }
    /*------------------------------------------------------ */
    //-- 写入日志
    /*------------------------------------------------------ */
    public static function _log($goods_id, $logInfo = '', $status = '', $opt_source = '', $operator = 0)
    {
        $inArr['goods_id'] = $goods_id;
        $inArr['opt_source'] = $opt_source;
        $inArr['operator'] = $operator;
        $inArr['status'] = $status;
        $inArr['log_info'] = $logInfo;
        $inArr['log_time'] = time();
        return (new GoodsLogModel)::create($inArr);
    }
    /*------------------------------------------------------ */
    //-- 获取首页促销商品
    /*------------------------------------------------------ */
    public function getIndexPromoteList()
    {
        $mkey = 'IndexPromoteList';
        $goodsList = Cache::get($mkey);
        if (empty($goodsList) == false) return $goodsList;
        $gwhere[] = ['isputaway', '=', 1];
        $gwhere[] = ['is_delete', '=', 0];
        $gwhere[] = ['store_id', '=', 0];
        $gwhere[] = ['is_promote', '=', 1];
        $gwhere[] = ['promote_start_date','<=',time()];
        $gwhere[] = ['promote_end_date','>=',time()];
        $gooodsList = $this->where($gwhere)->order('is_best desc,goods_id desc')->limit(4)->column('goods_id');
        foreach ($gooodsList as $key => $goods_id) {
            $gooodsList[$key] = $this->info($goods_id);
        }
        Cache::set($mkey, $gooodsList, 60);
        return $gooodsList;
    }
    /*------------------------------------------------------ */
    //-- 自动上下架商品
    /*------------------------------------------------------ */
    public function autoSale()
    {
        $cache = Cache::init();
        $_redis = $cache->handler();
        $mkey = 'auto_sale';
        $lock_time = $_redis->setnx($mkey,time()+5);
        if ($lock_time == false){
            $lock_time = $_redis->get($mkey);
            if(time()>$lock_time){
                $_redis->del($mkey);
                $lock_time = $_redis->setnx($mkey,time()+5);
                if ($lock_time == false) return false;
            }else{
                return false;
            }
        }

        //自动上架处理
        $where[] = ['isputaway','=',2];
        $where[] = ['added_time','<',time()];
        $where[] = ['shelf_time','>',time()];
        $this->where($where)->update(['isputaway'=>1]);
        //自动下架处理
        $where = [];
        $where[] = ['isputaway','=',1];
        $where[] = ['shelf_time','>',0];
        $where[] = ['shelf_time','<',time()];
        $this->where($where)->update(['isputaway'=>2]);
        return true;
    }
}
