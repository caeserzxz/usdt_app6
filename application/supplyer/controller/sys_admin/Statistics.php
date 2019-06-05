<?php

namespace app\supplyer\controller\sys_admin;
use think\facade\Session;
use think\facade\Cache;
use app\AdminController;
use app\shop\model\OrderModel;
use app\shop\model\GoodsModel;
use app\supplyer\model\SupplyerModel;

/**
 * 统计相关
 * Class Index
 */
class Statistics extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $supplyer_id = session::get('main_supplyer_id') * 1;
        $this->supplyer_id = input('supplyer_id','0','intval');
        if ($this->supplyer_id == 0) {
            $this->supplyer_id = $supplyer_id;
        }elseif ($this->supplyer_id == -1){
            $this->supplyer_id = 0;
            session::delete('main_supplyer_id');
        }elseif($this->supplyer_id != $supplyer_id){
            session::set('main_supplyer_id',$this->supplyer_id);
        }
        if ($this->supplyer_id > 0){
            $supplyer_name = (new SupplyerModel)->where('supplyer_id',$this->supplyer_id)->value('supplyer_name');
            $this->assign('supplyer_name',$supplyer_name);
        }
        $this->assign('goods_status',config('config.goods_status'));
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {

        $GoodsModel = new GoodsModel();
        if ($this->supplyer_id > 0 ){
            //全部商品
            $goods['all_num'] = $GoodsModel->where('supplyer_id',$this->supplyer_id)->count();
            //销售中商品
            unset($where);
            $where[] = ['supplyer_id','=',$this->supplyer_id];
            $where[] = ['isputaway','=',1];
            $goods['sale_num'] = $GoodsModel->where($where)->count();
            //审核中商品
            unset($where);
            $where[] = ['supplyer_id','=',$this->supplyer_id];
            $where[] = ['isputaway','=',10];
            $goods['sale_num'] = $GoodsModel->where($where)->count();
            //平台下架商品
            unset($where);
            $where[] = ['supplyer_id','=',$this->supplyer_id];
            $where[] = ['isputaway','=',12];
            $goods['obtained_num'] = $GoodsModel->where($where)->count();
        }else{
            $where[] = ['supplyer_id','>',0];
            //全部商品
            $goods['all_num'] = $GoodsModel->where($where)->count();
            //销售中商品
            unset($where);
            $where[] = ['supplyer_id','>',0];
            $where[] = ['isputaway','=',1];
            $goods['sale_num'] = $GoodsModel->where($where)->count();
            //审核中商品
            unset($where);
            $where[] = ['supplyer_id','>',0];
            $where[] = ['isputaway','=',10];
            $goods['check_num'] = $GoodsModel->where($where)->count();
            //平台下架商品
            unset($where);
            $where[] = ['supplyer_id','>',0];
            $where[] = ['isputaway','=',12];
            $goods['obtained_num'] = $GoodsModel->where($where)->count();
        }
        $this->assign("goods", $goods);
        $this->assign("supplyer_id", $this->supplyer_id);
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //--执行统计
    /*------------------------------------------------------ */
    public function evalStat()
    {
        $OrderModel = new OrderModel();
        $stats = [];
        if ($this->supplyer_id > 0) {
            $where[] = ['supplyer_id', '=', $this->supplyer_id];
        } else {
            $where[] = ['supplyer_id', '>', 0];
        }
        $where[] = ['order_status', '=', '1'];
        $where[] = ['shipping_status', '=', '0'];
        $stats['wait_shipping_num'] = $OrderModel->where($where)->count('order_id');

        $today = strtotime(date("Y-m-d"));
        $yesterday = strtotime('-1 day', $today);
        $reportrange = input('reportrange', '', 'trim');
        $reportrange = str_replace('_', '/', $reportrange);
        $dtime = explode('-', $reportrange);
        $riqi = [];
        $i = 0;
        $dt_start = strtotime($dtime[0]);
        $end_day = strtotime($dtime[1]);
        while ($dt_start <= $end_day) {
            $riqi[] = date('Y-m-d', $dt_start);
            $searchtime = $dt_start . ',' . ($dt_start + 86399);
            $data = $this->orderStats($OrderModel, $searchtime);
            if ($dt_start == $today) {
                $stats['today']['all_add_num'] = $data['all_add_num'] * 1;
                $stats['today']['order_pay_num'] = $data['order_pay_num'] * 1;
                $stats['today']['order_shipping_num'] = $data['shipping_num'] * 1;
                $stats['today']['sign_num'] = $data['sign_num'] * 1;
            } elseif ($dt_start == $yesterday) {
                $stats['yesterday']['all_add_num'] = $data['all_add_num'] * 1;
                $stats['yesterday']['order_pay_num'] = $data['order_pay_num'] * 1;
                $stats['yesterday']['order_shipping_num'] = $data['shipping_num'] * 1;
                $stats['yesterday']['sign_num'] = $data['sign_num'] * 1;
            }

            $stats['total_shpping_num'] += $data['shipping_num'] * 1;
            $stats['total_pay_num'] += $data['order_pay_num'] * 1;
            $stats['total_add_num'] += $data['all_add_num'] * 1;
            $stats['total_sign_num'] += $data['sign_num'] * 1;
            $stats['all_add_num'][$i][] = $data['all_add_num'] * 1;
            $stats['order_pay_num'][$i][] = $data['order_pay_num'] * 1;
            $stats['shipping_order_num'][$i][] = $data['shipping_num'] * 1;
            $stats['sign_order_num'][$i][] = $data['sign_num'] * 1;
            unset($data);
            $dt_start = strtotime('+1 day', $dt_start);
            $i++;
        }
        $return['riqi'] = $riqi;
        $return['stats'] = $stats;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取订单信息汇总
    /*------------------------------------------------------ */
    public function orderStats(&$OrderModel, $timeWhere = [])
    {
        $mkey = 'main_order_stat_' . $this->supplyer_id . '_' . md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        if ($this->supplyer_id > 0) {
            $where[] = ['supplyer_id', '=', $this->supplyer_id];
        } else {
            $where[] = ['supplyer_id', '>', 0];
        }
        $where[] = ['add_time', 'between', $timeWhere];
        $rows = $OrderModel->field('order_id,order_status,pay_status,shipping_status,is_pay,order_amount')->where($where)->select();
        foreach ($rows as $row) {
            $info['all_add_num'] += 1;//全部订单
            if ($row['order_status'] == 1) {
                $info['order_pay_num'] += 1;//成交数
                $info['order_amount'] += $row['order_amount'];//成交金额
                if ($row['shipping_status'] > 0){
                    $info['shipping_num'] += 1;
                }
                if ($row['shipping_status'] == 2){
                    $info['sign_num'] += 1;
                }
            }
        }

        Cache::set($mkey, $info, 20);
        return $info;
    }
    /*------------------------------------------------------ */
    //--订单相关统计
    /*------------------------------------------------------ */
    public function order()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->assign("supplyer_id", $this->supplyer_id);
        return $this->fetch('order');
    }
    /*------------------------------------------------------ */
    //--商品相关统计
    /*------------------------------------------------------ */
    public function goods()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->assign("supplyer_id", $this->supplyer_id);
        return $this->fetch('goods');
    }
    /*------------------------------------------------------ */
    //--执行统计
    /*------------------------------------------------------ */
    public function evalOrderStat()
    {
        $OrderModel = new OrderModel();
        $stats = [];
        $reportrange = input('reportrange', '', 'trim');
        $reportrange = str_replace('_', '/', $reportrange);
        $dtime = explode('-', $reportrange);
        $riqi = [];
        $i = 0;
        $dt_start = strtotime($dtime[0]);
        $end_day = strtotime($dtime[1]);
        while ($dt_start <= $end_day) {
            $riqi[] = date('Y-m-d', $dt_start);
            $searchtime = $dt_start . ',' . ($dt_start + 86399);
            $data = $this->_orderStats($OrderModel, $searchtime);
            $stats['total_order_amount'] += $data['order_amount'] * 1;
            $stats['total_shpping_num'] += $data['shipping_num'] * 1;
            $stats['total_pay_num'] += $data['order_pay_num'] * 1;
            $stats['total_add_num'] += $data['all_add_num'] * 1;
            $stats['total_sign_num'] += $data['sign_num'] * 1;
            $stats['total_settle_price'] += $data['settle_price'] * 1;
            $stats['all_add_num'][$i][] = $data['all_add_num'] * 1;
            $stats['order_pay_num'][$i][] = $data['order_pay_num'] * 1;
            $stats['shipping_order_num'][$i][] = $data['shipping_num'] * 1;
            $stats['sign_order_num'][$i][] = $data['sign_num'] * 1;
            $stats['shipping_settle_price'][$i][] = $data['shipping_settle_price'] * 1;
            $stats['wait_settlement'][$i][] = $data['wait_settlement'] * 1;
            $stats['then_settlement'][$i][] = $data['then_settlement'] * 1;
            unset($data);

            $dt_start = strtotime('+1 day', $dt_start);
            $i++;
        }
        $return['riqi'] = $riqi;
        $return['stats'] = $stats;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取订单信息汇总
    /*------------------------------------------------------ */
    public function _orderStats(&$OrderModel, $timeWhere = [])
    {
        $mkey = '_main_order_stat_' . $this->supplyer_id . '_' . md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        if ($this->supplyer_id > 0){
            $where[] = ['supplyer_id', '=', $this->supplyer_id];
        }else{
            $where[] = ['supplyer_id', '>', $this->supplyer_id];
        }
        $where[] = ['add_time', 'between', $timeWhere];
        $rows = $OrderModel->field('order_id,order_status,pay_status,shipping_status,is_pay,order_amount,settle_price,is_settlement')->where($where)->select();
        $info['settle_price'] = 0;

        foreach ($rows as $row) {
            $info['all_add_num'] += 1;//全部订单
            if ($row['order_status'] == 1) {
                $info['order_pay_num'] += 1;//成交数
                $info['settle_price'] += $row['settle_price'];//全部确认结算金额
                if ($row['shipping_status'] > 0) {
                    $info['shipping_num'] += 1;
                    $info['shipping_settle_price'] += $row['settle_price'];
                }
                if ($row['shipping_status'] == 2) {
                    $info['sign_num'] += 1;
                    if ($row['is_settlement'] == 0) {
                        $info['wait_settlement'] += $row['settle_price'];
                    } else {
                        $info['then_settlement'] += $row['settle_price'];
                    }
                }
            }
        }
        Cache::set($mkey, $info, 20);
        return $info;
    }
    /*------------------------------------------------------ */
    //--执行统计
    /*------------------------------------------------------ */
    public function evalGoodsStat()
    {
        $GoodsModel = new GoodsModel();
        if ($this->supplyer_id > 0){
            $where[] = ['supplyer_id', '=', $this->supplyer_id];
        }else{
            $where[] = ['supplyer_id', '>', 0];
        }
        $goodsRows = $GoodsModel->where($where)->order('goods_id DESC')->column('goods_name', 'goods_id');
        $reportrange = input('reportrange', '', 'trim');
        $reportrange = str_replace('_', '/', $reportrange);
        $dtime = explode('-', $reportrange);

        $owhere[] = ['o.add_time', 'between', [strtotime($dtime[0]), strtotime($dtime[1])]];
        if ($this->supplyer_id > 0){
            $owhere[] = ['o.supplyer_id', '=', $this->supplyer_id];
        }else{
            $owhere[] = ['o.supplyer_id', '>', 0];
        }
        $mkey = '_main_order_stat_' . $supplyer_id . '_' . md5(json_encode($owhere));
        $orders = Cache::get($mkey);
        if (empty($orders) == true){
            $orders = (new OrderModel)->alias('o')->join("shop_order_goods og", 'o.order_id=og.order_id', 'left')->where($owhere)->field('o.order_status,o.pay_status,o.shipping_status,og.goods_id,og.goods_number,og.return_goods_munber')->select();
            Cache::set($mkey,$orders,60);
        }
        foreach ($goodsRows as $goods_id => $goods_name) {
            $all_num = 0;
            $shipping_num = 0;
            $sign_num = 0;
            $return_num = 0;
            foreach ($orders as $key => $order) {
                if ($goods_id == $order['goods_id']) {
                    $all_num += $order['goods_number'];//全部订单商品
                    if ($order['order_status'] == 1) {
                        if ($order['shipping_status'] > 0) {
                            $shipping_num += $order['goods_number'];
                        }
                        if ($order['shipping_status'] == 2) {
                            $sign_num += $order['goods_number'];
                        }
                        $return_num += $order['return_goods_munber'];

                    }
                    unset($orders[$key]);
                }
            }
            $goodsStats[$goods_id] = $all_num;
            $stats[$goods_id]['all_num'] = $all_num;
            $stats[$goods_id]['shipping_num'] = $shipping_num;
            $stats[$goods_id]['sign_num'] = $sign_num;
            $stats[$goods_id]['return_num'] = $return_num;
        }
        arsort($goodsStats);
        $top_goods = $top_stats = [];
        $all_goods = $all_stats = [];
        $i = 0;
        foreach ($goodsStats as $goods_id=>$num){
            if ($i < 10){
                $top_goods[] = $goodsRows[$goods_id];
                $top_stats['all_num'][] = $stats[$goods_id]['all_num'];
                $top_stats['shipping_num'][] = $stats[$goods_id]['shipping_num'];
                $top_stats['sign_num'][] = $stats[$goods_id]['sign_num'];
                $top_stats['return_num'][] = $stats[$goods_id]['return_num'];
            }
            $all_goods[] = $goodsRows[$goods_id];
            $all_stats['all_num'][] = $stats[$goods_id]['all_num'];
            $all_stats['shipping_num'][] = $stats[$goods_id]['shipping_num'];
            $all_stats['sign_num'][] = $stats[$goods_id]['sign_num'];
            $all_stats['return_num'][] = $stats[$goods_id]['return_num'];
            $i++;
        }
        $return['top_goods'] = $top_goods;
        $return['top_stats'] = $top_stats;
        $return['all_goods'] = $all_goods;
        $return['all_stats'] = $all_stats;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
}
