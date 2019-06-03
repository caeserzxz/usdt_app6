<?php

namespace app\supplyer\controller;

use think\facade\Cache;
use app\supplyer\Controller;
use app\shop\model\OrderModel;
use app\shop\model\GoodsModel;
use app\supplyer\model\SupplyerModel;

/**
 * 统计相关
 * Class Index
 */
class Statistics extends Controller
{
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {


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
        $where[] = ['supplyer_id', '=', $this->supplyer_id];
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
            $data = $this->orderStats($OrderModel, $searchtime, $this->supplyer_id);
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
    public function orderStats(&$OrderModel, $timeWhere = [], $supplyer_id = 0)
    {
        $mkey = 'main_order_stat_' . $supplyer_id . '_' . md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        if ($supplyer_id > 0) {
            $where[] = ['supplyer_id', '=', $supplyer_id];
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
                if ($row['shipping_status'] > 0) {
                    $info['shipping_num'] += 1;
                }
                if ($row['shipping_status'] == 2) {
                    $info['sign_num'] += 1;
                }
            }
        }
        Cache::set($mkey, $info, 20);
        return $info;
    }
}
