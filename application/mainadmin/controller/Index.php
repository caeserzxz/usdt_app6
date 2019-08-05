<?php
namespace app\mainadmin\controller;

use app\AdminController;
use think\facade\Cache;

use app\shop\model\OrderModel;
use app\member\model\UsersModel;
use app\member\model\AccountModel;
use app\member\model\RechargeLogModel;
use app\member\model\WithdrawModel;

/**
 * 后台首页
 * Class Index
 * @package app\store\controller
 */
class Index extends AdminController
{

    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->stats();
        return $this->fetch('mainadmin@index/index');
    }

    /*------------------------------------------------------ */
    //-- 获取信息汇总
    /*------------------------------------------------------ */
    public function stats(){
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

        //帐户统计
        $AccountModel = new AccountModel();
        $account['balance_money'] = $AccountModel->sum('balance_money');
        $account['use_integral'] = $AccountModel->sum('use_integral');
        $this->assign('account',$account);
        //end
        //充值&提现审核
        $rchargeLogNum = (new RechargeLogModel)->where('status',0)->count('order_id');
        $this->assign('rchargeLogNum',$rchargeLogNum);
        $withdrawLogNum = (new WithdrawModel)->where('status',0)->count('log_id');
        $this->assign('withdrawLogNum',$withdrawLogNum);
        //end
        $AccountModel = new AccountModel();
        $account['balance_money'] = $AccountModel->sum('balance_money');
        $account['bean_value'] = $AccountModel->sum('bean_value');
        $this->assign('account',$account);
        //end
        //会员统计
        $UsersModel = new UsersModel();
        $userStats['all_num'] = $UsersModel->count('user_id');
        $userStats['ordinary_num'] = $UsersModel->where('role_id',0)->count('user_id');
        $userStats['role_num'] =  $userStats['all_num'] - $userStats['ordinary_num'];
        strtotime(date("Y-m-d"));
        $userWhere[] = ['reg_time','>',$today];
        $userStats['today_reg'] =  $UsersModel->where($userWhere)->count('user_id');
        unset($userWhere);
        $userWhere[] = ['reg_time','between',$yesterday.','.$today-1];
        $userStats['yesterday_reg'] =  $UsersModel->where($userWhere)->count('user_id');
        $this->assign('userStats',$userStats);
        //end


        //判断订单模块是否存在
        if(class_exists('app\shop\model\OrderModel')) {

            $OrderModel = new \app\shop\model\OrderModel();
            //订单统计相关
            $stats = [];
            $i = 0;
            $where[] = ['order_status', '=', '1'];
            $where[] = ['shipping_status', '=', '0'];
            $where[] = ['supplyer_id', '=', '0'];
            $where[] = ['is_success', '=', '1'];
            $stats['wait_shipping_num'] = $OrderModel->where($where)->count('order_id');
            $stats['seven_order_amount_all'] = 0;
            $stats['seven_real_amount_all'] = 0;
            $stats['seven_dividend_amount_all'] = 0;
            $stats['seven_shpping_num'] = 0;
            $stats['seven_pay_num'] = 0;
            $stats['seven_add_num'] = 0;
            $stats['seven_sign_num'] = 0;

            while ($dt_start <= strtotime($end_day)) {
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

                if ($dt_start < strtotime($end_day)) {
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
                $dt_start = strtotime('+1 day', $dt_start);
                $i++;
            }
            //订单统计相关end
            $this->assign('stats', $stats);
            $this->assign('riqi', json_encode($riqi));
            $this->assign('is_order', 1);
        }
    }
    /*------------------------------------------------------ */
    //-- 获取订单信息汇总
    /*------------------------------------------------------ */
    public function orderStats(&$OrderModel,$timeWhere = [])
    {
        $mkey = 'main_order_stat'.md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;

        $where[] = ['add_time','between',$timeWhere];
        $rows = $OrderModel->field('order_id,order_status,pay_status,shipping_status,is_pay,order_amount,dividend_amount')->where($where)->select();
        $info['all_add_num'] = 0;
        $info['order_amount'] = 0;
        $info['order_pay_num'] = 0;
        $info['dividend_amount'] = 0;
        foreach ($rows as $row){
            $info['all_add_num'] += 1;//全部订单
            if ($row['order_status'] == 1){
                $info['order_pay_num'] += 1;//成交数
                $info['order_amount'] += $row['order_amount'];//成交金额
                $info['dividend_amount'] += $row['dividend_amount'];//分佣金额
            }
        }
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
        Cache::set($mkey, $info, 20);
        return $info;
    }
    /*------------------------------------------------------ */
    //-- 清理全部缓存
    /*------------------------------------------------------ */
    public function clearCache()
    {
        $redis = new \think\cache\driver\Redis();
        $keysdata = $redis->keys(config('cache.prefix') . '*');
        foreach ($keysdata as $key => $val) {
            if (stripos($val, config('cache.prefix').'devlogin_') !== 0) {
                $redis->rm($val);
            }
        }
        return $this->success('清理完成.');
    }

}
