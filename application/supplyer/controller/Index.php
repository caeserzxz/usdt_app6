<?php
namespace app\supplyer\controller;
use think\facade\Cache;

use app\supplyer\Controller;
use app\shop\model\OrderModel;
use app\supplyer\model\SupplyerModel;
use app\shop\model\GoodsModel;
/**
 * 供应商后台首页
 * Class Index
 * @package app\store\controller
 */
class Index extends Controller
{
    public function index()
    {
        $start_day = date("Y-m-d",strtotime("-1 week"));
        $this->assign('start_day',$start_day);
        $end_day = date('Y-m-d', strtotime("+1 day") );
        $this->assign('end_day',$end_day);
        $dt_start = strtotime($start_day);
        $today = strtotime(date("Y-m-d"));
        $this->assign('today',date('Y_m_d',$today));
        $yesterday = strtotime('-1 day',$today);
        $this->assign('yesterday',date('Y_m_d',$yesterday));
        $riqi = [];


		//订单统计相关
        $OrderModel = new OrderModel();
        $stats = [];
        $i = 0;
        $where[] = ['supplyer_id', '=', $this->supplyer_id];
        $where[] = ['order_status', '=', '1'];
        $where[] = ['shipping_status', '=', '0'];
        $stats['wait_shipping_num'] = $OrderModel->where($where)->count('order_id');

        while ($dt_start <=  strtotime($end_day)){
            $riqi[] = date('Y-m-d',$dt_start);
            $searchtime =  $dt_start.','.($dt_start+86399);
            $data = $this->orderStats($OrderModel,$searchtime);
            if ($dt_start == $today){
                $stats['today']['all_add_num'] = $data['all_add_num'] * 1;
                $stats['today']['order_pay_num'] = $data['order_pay_num'] * 1;
                $stats['today']['order_shipping_num'] = $data['shipping_num'] * 1;
                $stats['today']['sign_num'] = $data['sign_num'] * 1;
            }elseif ($dt_start == $yesterday){
                $stats['yesterday']['all_add_num'] = $data['all_add_num'] * 1;
                $stats['yesterday']['order_pay_num'] = $data['order_pay_num'] * 1;
                $stats['yesterday']['order_shipping_num'] = $data['shipping_num'] * 1;
                $stats['yesterday']['sign_num'] = $data['sign_num'] * 1;
            }

            if ($dt_start <  strtotime($end_day)){
                $stats['seven_order_amount_all'] += $data['order_amount'] * 1;
                $stats['seven_real_amount_all'] += $data['order_amount'] - $data['dividend_amount'];
                $stats['seven_dividend_amount_all'] += $data['dividend_amount'] * 1;
                $stats['seven_order_amount'][$i][] = $data['order_amount'] * 1;
                $stats['seven_real_amount'][$i][] = $data['order_amount'] - $data['dividend_amount'];
                $stats['seven_dividend_amount'][$i][] = $data['dividend_amount'] * 1;
                $stats['seven_shpping_num'] += $data['shipping_num'] * 1;
                $stats['seven_pay_num'] += $data['order_pay_num'] * 1;
                $stats['seven_add_num'] += $data['all_add_num'] * 1;
                $stats['seven_sign_num'] += $data['sign_num'] * 1;
                $stats['all_add_num'][$i][] = $data['all_add_num'] * 1;
                $stats['order_pay_num'][$i][] = $data['order_pay_num'] * 1;
                $stats['shipping_order_num'][$i][] = $data['shipping_num'] * 1;
                $stats['sign_order_num'][$i][] = $data['sign_num'] * 1;
            }
            $dt_start = strtotime('+1 day',$dt_start);
            $i++;
        }
        //订单统计相关end
        $SupplyerModel = new SupplyerModel();
        $supplyerInfo = $SupplyerModel->find($this->supplyer_id)->toArray();
        $where[] = ['supplyer_id', '=', $this->supplyer_id];
        $where[] = ['is_settlement', '=', 1];
        $supplyer['wait_settle'] = $OrderModel->where($where)->SUM('settle_price');
        $GoodsModel = new GoodsModel();
        //全部商品
        $goods['all_num'] = $GoodsModel->where('supplyer_id', $this->supplyer_id)->count();
        //销售中商品
        unset($where);
        $where[] = ['supplyer_id', '=', $this->supplyer_id];
        $where[] = ['isputaway', '=', 1];
        $goods['sale_num'] = $GoodsModel->where($where)->count();
        //审核中商品
        unset($where);
        $where[] = ['supplyer_id', '=', $this->supplyer_id];
        $where[] = ['isputaway', '=', 10];
        $goods['check_num'] = $GoodsModel->where($where)->count();
        //平台下架商品
        unset($where);
        $where[] = ['supplyer_id', '=', $this->supplyer_id];
        $where[] = ['isputaway', '=', 12];
        $goods['obtained_num'] = $GoodsModel->where($where)->count();
        $this->assign("goods", $goods);
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->assign("supplyerInfo", $supplyerInfo);
        $this->assign('stats',$stats);
        $this->assign('riqi',json_encode($riqi));
        return $this->fetch('index');
    }

    /*------------------------------------------------------ */
    //-- 获取订单信息汇总
    /*------------------------------------------------------ */
    public function orderStats(&$OrderModel,$timeWhere = [])
    {
        $mkey = 'main_order_stat_'.$this->supplyer_id.'_'.md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        $where[] = ['supplyer_id', '=', $this->supplyer_id];
        $where[] = ['add_time','between',$timeWhere];
        $rows = $OrderModel->field('order_id,order_status,pay_status,shipping_status,is_pay,order_amount,dividend_amount')->where($where)->select();
        foreach ($rows as $row){
            $info['all_add_num'] += 1;//全部订单
            if ($row['order_status'] == 1){
                $info['order_pay_num'] += 1;//成交数
                $info['order_amount'] += $row['order_amount'];//成交金额
                $info['dividend_amount'] += $row['dividend_amount'];//分佣金额
            }
        }
        //发货数量
        unset($where);
        $where[] = ['shipping_time','between',$timeWhere];
        $where[] = ['shipping_status','=',1];
        $info['shipping_num'] = $OrderModel->where($where)->count('order_id');
        //发货数量
        unset($where);
        $where[] = ['sign_time','between',$timeWhere];
        $where[] = ['shipping_status','=',2];
        $info['sign_num'] = $OrderModel->where($where)->count('order_id');
        Cache::set($mkey, $info, 20);
        return $info;
    }


}
