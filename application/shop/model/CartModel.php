<?php

namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 购物车
/*------------------------------------------------------ */

class CartModel extends BaseModel
{
    protected $table = 'shop_cart';
    public $pk = 'rec_id';
    public $is_integral = 0;
 /*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
    }
    /*------------------------------------------------------ */
    //-- 清除购物车缓存
    /*------------------------------------------------------ */
    public function cleanMemcache()
    {
        $user_id = $this->userInfo['user_id'] * 1;
        Cache::rm('CartInfo_'.$user_id.session_id().'0'.$this->is_integral);
        Cache::rm('CartInfo_'.$user_id.session_id().'1'.$this->is_integral);
    }
    /*------------------------------------------------------ */
    //-- 添加购物车处理
    /*------------------------------------------------------ */
    public function addToCart($goods_id, $num, $spec = '')
    {
        $GoodsModel = new GoodsModel();
        $goods = $GoodsModel->info($goods_id);
        if (empty($goods)) return '商品不存在';
        $use_integral = $goods['use_integral'];

        if ($this->is_integral == 1) {
            $gmtime = time();
            $ExchangeGoodsModel = new ExchangeGoodsModel();
            $ex_resddd = $ExchangeGoodsModel->where('goods_id', $goods_id)->find();
            if (empty($ex_resddd)) return '相关商品不存在';
            if ($ex_resddd['is_exchange'] != 1) return "该商品兑换状态已发生变化不能进行积分兑换.";
            if ($gmtime < $ex_resddd['start_time'] || $gmtime > $ex_resddd['end_time']) {
                return "该商品已超过有效兑换时间不能进行积分兑换。";
            }
            $use_integral = $ex_resddd['use_integral'];
        } else {
            if ($this->userInfo['user_id'] > 0) {
                if ($goods['is_promote'] == 1 && $goods['quota_amount'] > 0) {
                    $goods_sn = $spec ? $goods['sub_goods'][$spec] : $goods['goods_sn'];
                    $where[] = ['user_id', '=', $this->userInfo['user_id']];
                    $where[] = ['add_time', 'between', array($goods['promote_start_date'], $goods['promote_end_date'])];
                    $OrderModel = new OrderModel();
                    $orders = $OrderModel->where($where)->field('buy_goods_sn,order_status')->select();
                    foreach ($orders as $o) {
                        if ($o['order_status'] == config('config.OS_CANCELED') || $o['order_status'] == config('config.OS_INVALID')) continue;
                        $gn = explode(',', $o['buy_goods_sn']);
                        if (in_array($goods_sn, $gn)) return '当前商品进行限购每个会员只允许参与一次，你已购买过暂不能购买！';
                    }
                }
            } elseif ($goods['limit_user_buy'] == 1 || $use_integral > 0) return '必须是会员才能购买，请注册会员！';
        }
        unset($where);
        /* 检查该商品是否已经存在在购物车中 */
        if ($this->userInfo['user_id'] > 0) {
            $where['user_id'] = $this->userInfo['user_id'] * 1;
        } else {
            $where['session_id'] = session_id();
            $where['user_id'] = 0;
        }
        $where['is_invalid'] = 0;
        $where['goods_id'] = $goods_id;
        $where['sku_val'] = $spec;
        $where['is_integral'] = $this->is_integral;
        $row = $this->field('goods_number,rec_id,use_integral')->where($where)->find();
        unset($where);
        if ($row) {// 如果购物车已经有此物品，则更新
            if ($use_integral != $row['use_integral']) $update['use_integral'] = $use_integral;			
            // 判断是商品能否购买或修改
            $res = $this->checkGoodsOrder($goods, $num, $spec);
            if ($res !== true) {
                if ($res['code'] == -1) {//如果返回码为-1，更新商品为无效
                    $this->updateCart($row['rec_id'], ['is_invalid' => 1]);
                }
                return $res['msg'];
            }
            $price = $GoodsModel->evalPrice($goods, $num, $spec);//计算需显示的商品价格

            $update['goods_number'] = $num;
            $update['is_select'] = 1;
            $update['goods_price'] = $price['min_price'];
            $update['buy_again_discount'] = 0;
            if ($goods['is_dividend'] == 1) {//分销商品处理，计算分销复购优惠
                $Dividend = json_decode(settings('Dividend'), true);
                if ($Dividend['buy_again_lower'] == 1) {
                    $DividendLevel = $Dividend['LevelRow'];
                    $update['buy_again_discount'] = $DividendLevel[1]['money'];
                }
            }
            $update['is_dividend'] = $goods['is_dividend'];
            $res = $this->where('rec_id', $row['rec_id'])->update($update);
            if ($res < 1) return '未知错误，操作失败，请尝试重新提交';
			$rec_id = $row['rec_id'];
        } else {// 购物车没有此物品，则插入
            $res = $this->checkGoodsOrder($goods, $num, $spec);// 判断是商品能否购买或修改
            if ($res !== true) return $res['msg'];

            $price = $GoodsModel->evalPrice($goods, $num, $spec);//计算需显示的商品价格
            $parent = array(
                'user_id' => $this->userInfo['user_id'] * 1,
                'session_id' => session_id(),
                'brand_id' => $goods['brand_id'],
                'cid' => $goods['cid'],
                'goods_id' => $goods_id,
                'goods_number' => $num,
                'is_dividend' => $goods['is_dividend'],
                'goods_sn' => addslashes($goods['goods_sn']),
                'goods_name' => addslashes($goods['goods_name']),
                'market_price' => $goods['market_price'],
                'goods_weight' => $goods['goods_weight'],
                'shop_price' => $goods['shop_price'],
                'goods_price' => $price['min_price'],
                'discount' => $goods['shop_price'] - $price['min_price'],
                'is_integral' => $this->is_integral,
                'pic' => $goods['goods_thumb'],
                'use_integral' => $use_integral,
                'add_time' => time()
            );

            if ($spec) {
                $sub_goods = $goods['sub_goods'][$spec];
                $SkuCustomModel = new SkuCustomModel();
                $parent['sku_name'] = $SkuCustomModel->getSkuName($sub_goods);
                $parent['sku_val'] = $sub_goods['sku_val'];
                $parent['goods_sn'] = addslashes($sub_goods['goods_sn']);
                $parent['market_price'] = $sub_goods['market_price'];
                $parent['shop_price'] = $sub_goods['shop_price'];
                $parent['goods_price'] = $price['min_price'];
                $parent['goods_weight'] = $sub_goods['goods_weight'];
                if ($sub_goods['market_price'] > 0 && $sub_goods['market_price'] > $sub_goods['shop_price']) {
                    $discont = $sub_goods['shop_price'] - $price['min_price'];
                }
                $parent['discount'] = $discont ? $discont : 0;
            }
            $parent['buy_again_discount'] = 0;
            if ($this->is_integral == 0 && $goods['is_dividend'] == 1 && $this->userInfo['dividend_role_id'] > 0) {
                $Dividend = json_decode(settings('Dividend'), true);
                if ($Dividend['buy_again_lower'] == 1) {
                    $DividendLevel = $Dividend['LevelRow'];
                    $parent['buy_again_discount'] = $DividendLevel[1]['money'];
                }
            }
            $res = $this->save($parent);
            if ($res < 1) return '未知错误，操作失败，请尝试重新提交';
			$rec_id = $this->rec_id;
        }
        $this->cleanMemcache();
        return $rec_id;
    }
    /*------------------------------------------------------ */
    //-- 验证商品能否下单
    /*------------------------------------------------------ */
    public function checkGoodsOrder(&$goods, $num, $spec = '')
    {
        /* 是否正在销售 */
        if ($goods['is_on_sale'] == 0) {
            return ['code' => -1, 'msg' => '商品【' . $goods['goods_name'] . '】已下架，暂不支持购买！'];
        }
        if ($goods['is_alone_sale'] == 0) return ['code' => -1, 'msg' => '商品【' . $goods['goods_name'] . '】为赠品或配件，不能直接进行购买！'];
        //限制等级购买
        if (empty($goods['limit_user_level']) == false){
            $limit_user_level = explode(',',$goods['limit_user_level']);
            if (in_array($this->userInfo['level']['level_id'],$limit_user_level) == false){
                return ['code' => -1, 'msg' => '商品【' . $goods['goods_name'] . '】，您的等级不满足购买条件.'];
            }
        }
        //限制身份购买
        if (empty($goods['limit_user_role']) == false){
            $limit_user_role = explode(',',$goods['limit_user_role']);
            if (in_array($this->userInfo['role_id'],$limit_user_role) == false){
                return ['code' => -1, 'msg' => '商品【' . $goods['goods_name'] . '】，您的身份不满足购买条件.'];
            }
        }
        if ($goods['is_spec'] == 1) {// 多规格商品执行
            if (empty($spec)) return ['code' => 0, 'msg' => '当前商品为多规格商品，请前往详情页选择规格后再操作'];
            $sub_goods = $goods['sub_goods'][$spec];
            $SkuCustomModel = new SkuCustomModel();
            $sku_name = $SkuCustomModel->getSkuName($sub_goods);
            if ($sub_goods['BuyMaxNum'] < 1) return ['code' => -1, 'msg' => '商品【' . $goods['goods_name'] . ' - ' . $sku_name . '】<br>库存不足，暂不能购买！'];
            if ($num > $sub_goods['BuyMaxNum']) return ['code' => 0, 'msg' => '商品【' . $goods['goods_name'] . ' - ' . $sku_name . '】<br>只能购买' . $sub_goods['BuyMaxNum'] . '件'];
            if ($sub_goods['goods_number'] < $num) return ['code' => 0, 'msg' => '商品【' . $goods['goods_name'] . ' - ' . $sku_name . '】<br>库存不够当前定义购买数量，不能直接进行购买！'];
        } else {// 单规格商品执行
            if ($goods['goods_number'] < $num) return ['code' => 0, 'msg' => '商品【' . $goods['goods_name'] . '】<br>库存不够当前定义购买数量，不能直接进行购买！'];
            $BuyMaxNum = $goods['goods_number'];
            if ($goods['quota_amount'] > 0) $BuyMaxNum = $BuyMaxNum > $goods['quota_amount'] ? $goods['quota_amount'] : $BuyMaxNum;
            if ($num > $BuyMaxNum) return ['code' => 0, 'msg' => '商品【' . $goods['goods_name'] . '】只能购买' . $BuyMaxNum . '件'];
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 购物车信息
    /*------------------------------------------------------ */
    public function getCartList($is_sel = 0, $no_cache = false, $recids = '',$is_collect = true)
    {
		$user_id = $this->userInfo['user_id'] * 1;        
        if ($user_id < 1){
			 $where[] = ['session_id','=',session_id()];
		}else{
			$where[] = ['user_id','=',$this->userInfo['user_id']];	
		}
		$where[] = ['is_integral','=',$this->is_integral];
        if ($is_sel == 1) $where[] = ['is_select','=',1];
		
		if (empty($recids) == false){
			$where[] = ['rec_id','in',explode(',',$recids)];			
		}elseif ($no_cache == false){		
			$mkey = 'CartInfo_'.$user_id.session_id().$is_sel.$this->is_integral;
			$data = Cache::get($mkey);
		}
						
        if (empty($data['allGoodsNum'])) {
            $data['isAllSel'] = 1;
            $data['orderTotal'] = 0;
            $data['buyGoodsNum'] = 0;
            $data['buyGoodsWeight'] = 0;
            $data['allGoodsNum'] = 0;
            $data['totalDiscount'] = 0;
            $data['totalGoodsPrice'] = 0;
            $data['buyAgainDiscount'] = 0;
            $rows = $this->where($where)->order('rec_id ASC')->select();
            foreach ($rows as $key => $row) {
                if ($row['is_invalid'] == 1) {//无效记录到无效列表
                    $data['invalidList'][] = $row;
                    continue;
                }
                if ($row['is_select'] == 0) {
                    $data['isAllSel'] = 0;
                }
                $data['buyAgainDiscount'] += $row['buy_again_discount'] * $row['goods_number'];
                $gid_list[$row['goods_id']] = 1;
                $data['allGoodsNum'] += $row['goods_number'];
                if ($row['is_select'] == 1) {
                    //记录购买的商品品牌，分类，单品ID
                    $cat_list[$row['cid']] = 1;
                    $brand_list[$row['brand_id']] = 1;
                    $data['allGoodsSn'][$row['goods_sn']] = 1;
                    $data['buyGoodsNum'] += $row['goods_number'];
                    $data['buyGoodsWeight'] += $row['goods_number'] * $row['goods_weight'];
                }
                if ($this->is_integral == 1) {
                    $row['total'] = $row['goods_number'] * $row['use_integral'];
                    if ($row['is_select'] == 1) {
                        $data['integralTotal'] += $row['total'];
                    }
                } else {
                    $row['total'] = $row['goods_number'] * $row['goods_price'];
                    if ($row['is_select'] == 1) {
                        $data['orderTotal'] += $row['total'];
                        $data['totalGoodsPrice'] += $row['goods_number'] * $row['shop_price'];
                        //当销售价和商城价一致时，计算折扣的总金额
                        if ($row['shop_price'] != $row['goods_price']) {
                            $data['totalDiscount'] += ($row['shop_price'] - $row['goods_price']) * $row['goods_number'];
                        }
                    }
                }
                $row['exp_price'] = explode('.', $row['goods_price']);
                $data['goodsList'][$row['goods_id'] . '_' . $row['sku_val']] = $row;
            }
            unset($rows);
            if (empty($brand_list) == false) $data['brand_list'] = array_keys($brand_list);
            if (empty($cat_list) == false) $data['cat_list'] = array_keys($cat_list);
            if (empty($gid_list) == false) $data['gid_list'] = array_keys($gid_list);

            $data['totalDiscount'] = sprintf("%.2f", $data['totalDiscount']);
            $data['totalGoodsPrice'] = sprintf("%.2f", $data['totalGoodsPrice']);
            $data['orderTotal'] = sprintf("%.2f", $data['orderTotal'] - $data['buyAgainDiscount']);
            $data['exp_total'] = explode('.', $data['orderTotal']);
            Cache::set($mkey, $data, 300);
        }
		//没有指定选和指定商品，执行查询是否收藏
		if ($is_sel == 0 && empty($recids) == true && $is_collect == true){
			$GoodsCollectModel = new GoodsCollectModel();
			foreach ($data['goodsList'] as $key=>$goods){
				$where = [];
				$where[] = ['user_id','=',$user_id];
				$where[] = ['goods_id','=',$goods['goods_id']];
				$where[] = ['status','=',1];
				$data['goodsList'][$key]['is_collect'] = $GoodsCollectModel->where($where)->count();
			}
		}
		
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取购物车商品数量和金额
    /*------------------------------------------------------ */
    public function getCartInfo()
    {		
        $data = $this->getCartList(0,false,'',false);
        $info['num'] = $data['buyGoodsNum'];
        if ($this->is_integral == 1) {
            $info['total'] = $data['integralTotal'];
        } else {
            $info['total'] = $data['orderTotal'];
        }
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 更新购物车数据
    /*------------------------------------------------------ */
    public function updateCart($rec_id, $data)
    {
        if (is_numeric($rec_id)) {
            $where['rec_id'] = $rec_id;
        }
        $where['user_id'] = $this->userInfo['user_id'] * 1;
        if ($where['user_id'] == 0) $where['session_id'] = session_id();
        $res = $this->where($where)->update($data);
        $this->cleanMemcache();
        return $res;
    }
    /*------------------------------------------------------ */
    //-- 更新商品数据
    /*------------------------------------------------------ */
    public function updataGoods($rec_id, $num)
    {
        $cg = $this->where('rec_id', $rec_id)->find();
        $GoodsModel = new GoodsModel();
        $goods = $GoodsModel->info($cg['goods_id']);
        // 判断是商品能否购买或修改
        $res = $this->checkGoodsOrder($goods, $num, $cg['sku_val']);
        if ($res !== true) {
            if ($res['code'] == -1) {//如果返回码为-1，更新商品为无效
                $this->updateCart($rec_id, ['is_invalid' => 1]);
            }
            return $res['msg'];
        }
        //计算需显示的商品价格
        $price = $GoodsModel->evalPrice($goods, $num, $cg['sku_val']);
        $arr['goods_number'] = $num;
        $arr['is_select'] = 1;
        $arr['goods_price'] = $price['min_price'];
        $arr['buy_again_discount'] = 0;
        if ($this->is_integral == 0 && $goods['is_dividend'] == 1 && $this->userInfo['dividend_role_id'] > 0) {
            $Dividend = json_decode(settings('Dividend'), true);
            $DividendLevel = $Dividend['LevelRow'];
            $arr['buy_again_discount'] = $DividendLevel[1]['money'];
        }
        return $this->updateCart($rec_id, $arr, $this->is_integral);
    }
    /*------------------------------------------------------ */
    //-- 删除购物车商品
    /*------------------------------------------------------ */
    public function delGoods($rec_id = 0)
    {
        if ($rec_id > 0) {
            $map['rec_id'] = $rec_id;
        }
        $map['user_id'] = $this->userInfo['user_id'] * 1;
        if ($map['user_id'] == 0) $map['session_id'] = session_id();
        $map['is_integral'] = $this->is_integral;
        $this->where($map)->delete();
        $this->cleanMemcache();
        return true;
    }
    /*------------------------------------------------------ */
    //-- 清除购物车无效的商品
    /*------------------------------------------------------ */
    public function clearInvalid()
    {
        $map['user_id'] = $this->userInfo['user_id'] * 1;
        if ($map['user_id'] == 0) $map['session_id'] = session_id();
        $map['is_integral'] = $this->is_integral;
        $map['is_invalid'] = 1;
        $this->where($map)->delete();
        $this->cleanMemcache();
        return true;
    }
    /*------------------------------------------------------ */
    //-- 计算运费
    /*------------------------------------------------------ */
    public function evalShippingFee(&$userAddress = [], &$cartList = [])
    {
		if ($cartList['buyGoodsNum'] < 1) return 0;
        $GoodsModel = new GoodsModel();
        $CategoryModel = new CategoryModel();
        $Category = $CategoryModel->getRows();
        $sf_id = array();
        foreach ($cartList['goodsList'] as $goods) {
            $goods = $GoodsModel->info($goods['goods_id']);
            if ($goods['freight_template'] > 0) {//判断商品运模板
                $sf_id[$goods['freight_template']] = 1;
            } else {//判断分类运费模板
                $class = $Category[$goods['cid']];
                if ($class['freight_template'] > 0) {
                    $sf_id[$class['freight_template']] = 1;
                }
            }
        }

        $ShippingModel = new \app\shop\model\ShippingModel();
        $shippingList = $ShippingModel->getToSTRows();
        $ShippingTplModel = new \app\shop\model\ShippingTplModel();
        $shippingTpl = $ShippingTplModel->getRows();//获取全部运费模板
        $defShippingTpl = reset($shippingTpl);//获取默认模板
        $sf_id[$defShippingTpl['sf_id']] = 1;//写入默认模板ID

        $sf_info = array();
        //获取最贵的运费模板，根据起步价判断
        foreach ($sf_id as $key => $val) {
            $_sf_info = $shippingTpl[$key]['sf_info'];
            $_consume = $shippingTpl[$key]['consume'];
            $_valuation = $shippingTpl[$key]['valuation'];//计件方式
            if (empty($_sf_info)) continue;
            foreach ($shippingList as $code => $shipping) {
                if ($shipping['status'] == 0 || $shipping['is_zt'] == 1 || $shipping['is_sys'] == 1) continue;

                foreach ($_sf_info[$code] as $rowb) {
                    $region_id = empty($rowb['region_id']) ? array() : explode(',', $rowb['region_id']);
                    if ($rowb['area'] == 'all' || in_array($userAddress['city'], $region_id)) {
                        if (empty($sf_info[$shipping['shipping_code']]) == false) {//如果已存在相关快递的模板
                            if ($sf_info[$shipping['shipping_code']]['postage'] > $rowb['postage']) {
                                continue;
                            }
                        }
                        $rowb['sf_id'] = $key;
                        $rowb['consume'] = $_consume;
                        $sf_info[$code] = $rowb;
                    }
                }
            }
        }
        if ($_valuation == 1) {
            //根据商品数量计算
            foreach ($sf_info as $code => $val) {
                $n_info[$code]['name'] = $shippingList[$code]['shipping_name'];
                $n_info[$code]['code'] = $code;
                $n_info[$code]['sf_id'] = $val['sf_id'];
                $row = $sf_info[$code];
                if ($row['consume'] > 0 && $cartList['totalGoodsPrice'] > $row['consume']) {
                    $n_info[$code]['shipping_fee'] = 0;
                } else {
                    if ($cartList['buyGoodsNum'] > $row['start']) {
                        $d_num = $cartList['buyGoodsNum'] - $row['start'];
                        $d_num = ceil($d_num / $row['plus']);
                        $n_info[$code]['shipping_fee'] = $row['postage'] + ($d_num * $row['postageplus']);
                    } else {
                        $n_info[$code]['shipping_fee'] = $row['postage'];
                    }
                }
            }
        } else {
            //按商品重量计算
            foreach ($sf_info as $code => $val) {
                $n_info[$code]['name'] = $shippingList[$code]['shipping_name'];
                $n_info[$code]['code'] = $code;
                $n_info[$code]['sf_id'] = $val['sf_id'];
                $row = $sf_info[$code];
                if ($row['consume'] > 0 && $cartList['totalGoodsPrice'] > $row['consume']) {
                    $n_info[$code]['shipping_fee'] = 0;
                } else {
                    if ($cartList['buyGoodsWeight'] > $row['start']) {
                        $d_num = $cartList['buyGoodsWeight'] - $row['start'];
                        $d_num = ceil($d_num / $row['plus']);
                        $n_info[$code]['shipping_fee'] = $row['postage'] + ($d_num * $row['postageplus']);
                    } else {
                        $n_info[$code]['shipping_fee'] = $row['postage'];
                    }
                }
            }
        }
        return $n_info;
    }
    /*------------------------------------------------------ */
    //-- 登陆更新购物车的中商品信息
    /*------------------------------------------------------ */
    public function loginUpCart($user_id)
    {
        //更新老的购物车数据
        $where['user_id'] = 0;
        $where['session_id'] = session_id();
        $nRows = $this->where($where)->select();
        unset($where);
        $newGoods = array();
        foreach ($nRows as $row) {
            $newGoods[$row['goods_id'] . '_' . $row['sku_val']] = $row;
        }

        $oldRows = $this->where('user_id',$user_id)->select();
        if (empty($oldRows) == false){
            $GoodsModel = new GoodsModel();
            foreach ($oldRows as $row) {
                $nrow = $newGoods[$row['goods_id'] . '_' . $row['sku_val']];
                if (empty($nrow) == false){//如果存在新的，删除
                    $this->where('rec_id',$row['rec_id'])->delete();
                    continue;
                }
                $goods = $GoodsModel->info($row['goods_id']);
                $checkGoods = $this->checkGoodsOrder($goods, $row['goods_number'], $row['sku_val']);
                $upDate = array();
                if ($checkGoods !== true ) {
                    $upDate['is_invalid'] = 1;
                }
                $price = $GoodsModel->evalPrice($goods,  $row['goods_number'], $row['sku_val']);//计算需显示的商品价格
                $upDate['goods_price'] = $price['min_price'];
                $this->where('rec_id',$row['rec_id'])->update($upDate);
            }
        }
        $where['session_id'] = session_id();
        $where['user_id'] = 0;
        $this->where($where)->update(['user_id'=>$user_id]);
        unset($where);
        return true;
    }
}
