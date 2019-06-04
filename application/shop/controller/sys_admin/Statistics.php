<?php

namespace app\shop\controller\sys_admin;
use app\AdminController;
use think\facade\Cache;
use app\shop\model\OrderModel;
use app\shop\model\GoodsModel;

/**
 * 统计相关
 * Class Index
 */
class Statistics extends AdminController
{
    /*------------------------------------------------------ */
    //--订单相关统计
    /*------------------------------------------------------ */
    public function order()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        return $this->fetch('order');
    }
    /*------------------------------------------------------ */
    //--商品相关统计
    /*------------------------------------------------------ */
    public function goods()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        return $this->fetch('goods');
    }
    /*------------------------------------------------------ */
    //--执行统计
    /*------------------------------------------------------ */
    public function evalStat()
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
            $data = $this->orderStats($OrderModel, $searchtime);
            $stats['total_order_amount'] += $data['all_order_amount'] * 1;
            $stats['total_shpping_num'] += $data['shipping_num'] * 1;
            $stats['total_pay_num'] += $data['order_pay_num'] * 1;
            $stats['total_add_num'] += $data['all_add_num'] * 1;
            $stats['total_sign_num'] += $data['sign_num'] * 1;
            $stats['all_add_num'][$i][] = $data['all_add_num'] * 1;
            $stats['order_pay_num'][$i][] = $data['order_pay_num'] * 1;
            $stats['shipping_order_num'][$i][] = $data['shipping_num'] * 1;
            $stats['sign_order_num'][$i][] = $data['sign_num'] * 1;
            $stats['shipping_order_amount'][$i][] = $data['shipping_order_amount'] * 1;
            $stats['sign_order_amount'][$i][] = $data['sign_order_amount'] * 1;
            $stats['all_order_amount'][$i][] = $data['all_order_amount'] * 1;
            $stats['cmf_order_amount'][$i][] = $data['cmf_order_amount'] * 1;
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
        $mkey = 'main_order_stat_' .  md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        $where[] = ['supplyer_id', '=', 0];
        $where[] = ['add_time', 'between', $timeWhere];
        $rows = $OrderModel->field('order_id,order_status,pay_status,shipping_status,is_pay,order_amount')->where($where)->select();

        foreach ($rows as $row) {
            $info['all_add_num'] += 1;//全部订单
            $info['all_order_amount'] += $row['order_amount'];//全部订单金额
            if ($row['order_status'] == 1) {
                $info['order_pay_num'] += 1;//成交数
                $info['cmf_order_amount'] += $row['order_amount'];//全部确认订单金额
                if ($row['shipping_status'] > 0) {
                    $info['shipping_num'] += 1;
                    $info['shipping_order_amount'] += $row['order_amount'];
                }
                if ($row['shipping_status'] == 2) {
                    $info['sign_num'] += 1;
                    $info['sign_order_amount'] += $row['order_amount'];
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
        $where[] = ['supplyer_id', '=', 0];
        $goodsRows = $GoodsModel->where('supplyer_id', 0)->order('goods_id DESC')->column('goods_name', 'goods_id');
        $reportrange = input('reportrange', '', 'trim');
        $reportrange = str_replace('_', '/', $reportrange);
        $dtime = explode('-', $reportrange);

        $owhere[] = ['o.add_time', 'between', [strtotime($dtime[0]), strtotime($dtime[1])]];
        $owhere[] = ['o.supplyer_id', '=', 0];
        $mkey = 'main_order_stat_' .  md5(json_encode($owhere));
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
