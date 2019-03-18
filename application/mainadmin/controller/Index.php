<?php
namespace app\mainadmin\controller;
use Think\Db;
use app\AdminController;
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
		$end_day = date('Y-m-d', strtotime('-1 day',$time) );
        $this->assign('end_day',$end_day);
        $dt_start = strtotime($start_day);
        $riqi = [];
        while ($dt_start <=  strtotime($end_day)){
            $riqi[] = date('Y-m-d',$dt_start);
            $dt_start = strtotime('+1 day',$dt_start);
        }

        $this->assign('riqi',json_encode($riqi));
        return $this->fetch('index');
    }

  


}
