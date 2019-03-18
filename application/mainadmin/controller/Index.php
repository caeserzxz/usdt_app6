<?php
namespace app\mainadmin\controller;
use Think\Db;
use app\AdminController;
use think\facade\Cache;

use app\shop\model\OrderModel;
/**
 * 后台首页
 * Class Index
 * @package app\store\controller
 */
class Index extends AdminController
{
	

    public function index()
    {		
		//判断订单模块是否存在
		if(class_exists('app\shop\model\OrderModel')){
			//执行订单自动签收
			(new \app\shop\model\OrderModel)->autoSign();			
		}
		//执行定期分佣到帐
		if(class_exists('app\distribution\model\EvalArrivalLogModel')){			
			$DividendInfo = settings('DividendInfo');
			if ($DividendInfo['settlement_day'] > 0){
				$EvalArrivalLogModel = new \app\distribution\model\EvalArrivalLogModel();
				$log_time = $EvalArrivalLogModel->order('log_id DESC')->value('log_time');//获取最近操作的时间
				if (time() > $log_time + $DividendInfo['settlement_day'] * 86400){
					$inData['log_time'] = time();					
					Db::startTrans();//事务启用
					$res = $EvalArrivalLogModel->save($inData);
					if ($res >= 1){
						$res = (new \app\distribution\model\DividendModel)->evalArrival(0,$EvalArrivalLogModel->log_id);
						if ($res == true){
							Db::commit();//事务提交						
						}			
					}					
					Db::rollback();//事务回滚		
					
				}
			}
		}
		//统计相关
        $time = time();
		$start_day = date("Y-m-d",strtotime("-1 week"));
		$this->assign('start_day',$start_day);
		$end_day = date('Y-m-d', $time );
        $this->assign('end_day',$end_day);
        $dt_start = strtotime($start_day);
        $today = strtotime(date("Y-m-d"));
        $yesterday = strtotime('-1 day',$today);
        $riqi = [];
        $OrderModel = new OrderModel();
        $stats = [];
        $i = 0;

        $where[] = ['order_status', '=', '1'];
        $where[] = ['shipping_status', '=', '0'];
        $stats['wait_shipping_num'] = $OrderModel->where($where)->count('order_id');

        while ($dt_start <=  strtotime($end_day)){
            $riqi[] = date('Y-m-d',$dt_start);
            $searchtime =  $dt_start.','.($dt_start+86399);
            $data = $this->orderStats($OrderModel,['add_time','between',$searchtime]);
            if ($dt_start == $today){
                $stats['today']['all_add_num'] = $data['all_add_num'];
                $stats['today']['order_pay_num'] = $data['wait_shipping_num'];
                $stats['today']['sign_num'] = $data['sign_num'];
            }elseif ($dt_start == $yesterday){
                $stats['yesterday']['all_add_num'] = $data['all_add_num'];
                $stats['yesterday']['order_pay_num'] = $data['wait_shipping_num'];
                $stats['yesterday']['sign_num'] = $data['sign_num'];
            }

            if ($dt_start <  strtotime($end_day)){
                $stats['all_add_num'][$i][] = $data['all_add_num'];
                $stats['order_pay_num'][$i][] = $data['order_pay_num'];
                $stats['shipping_order_num'][$i][] = $data['shipping_order_num'];
                $stats['sign_order_num'][$i][] = $data['sign_order_num'];
            }
            $dt_start = strtotime('+1 day',$dt_start);
            $i++;
        }
        $this->assign('stats',$stats);
        $this->assign('riqi',json_encode($riqi));
        return $this->fetch('index');
    }

    /*------------------------------------------------------ */
    //-- 获取订单信息汇总
    /*------------------------------------------------------ */
    public function orderStats(&$OrderModel,$timeWhere = [])
    {
        $mkey = 'main_order_stat'.md5(json_encode($timeWhere));
        $info = Cache::get($mkey);
        if (empty($info) == false) return $info;
        $where[] = $timeWhere;
        $rows = $OrderModel->field('order_id,order_status,pay_status,shipping_status,is_pay,order_amount')->where($where)->select();
        foreach ($rows as $row){
            $info['all_add_num'] += 1;//全部订单
            if ($row['order_status'] == 1){
                if ($row['shipping_status'] == 0) {
                    $info['wait_shipping_num'] += 1;//待发货
                }else if ($row['shipping_status'] == 1) {
                    $info['wait_sign_num'] +=1;//待签收
                }
                $info['order_pay_num'] += 1;//成交数
            }elseif ($row['order_status'] == 0) {
                if ($row['is_pay'] == 1 && $row['pay_status'] == 0){
                    $info['wait_pay_num'] += 1;//待支付
                }
            }
        }
        Cache::set($mkey, $info, 30);
        return $info;
    }


}
