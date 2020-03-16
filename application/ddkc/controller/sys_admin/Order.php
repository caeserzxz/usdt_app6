<?php
namespace app\ddkc\controller\sys_admin;
use app\AdminController;
// use app\mining\model\MiningProfitLog;
use app\ddkc\model\MiningGoodsModel;
use app\ddkc\model\MiningOrderModel;

/**
 * 矿机订单
 */
class Order extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new MiningOrderModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
    	$this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
		$this->getList(true);	
		//首页跳转时间
        $start_date = input('start_time', '0', 'trim');
        $end_date = input('end_time', '0', 'trim');
        if( $start_date || $end_date){

            $this->assign("start_date",str_replace('_', '/', $start_date));
            $this->assign("end_date",str_replace('_', '/', $end_date));
        }	
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $this->search['keyword'] = input("keyword");
        $this->search['key_type'] = input("key_type");
        $this->search['pay_type'] = input("pay_type");
        $this->search['status'] = input("status");
        $this->search['order_type'] = input("order_type");

        
		$this->order_by = 'order_id';
		$this->sort_by = 'DESC';
		if (input('reportrange')) {
	        $dtime = explode('-', input('reportrange'));
	        $where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}
        if ($this->search['pay_type']) {
            $where[] = ['pay_type','=',($this->search['pay_type'])];
        }
        if ($this->search['order_type']) {
            $where[] = ['pay_type','=',($this->search['order_type'])];
        }
        
        if ($this->search['status']) {
            $where[] = ['status','=',($this->search['status'])];
        }
        if ($this->search['keyword']) {
        	if ($this->search['key_type'] == 1) {
            	$where[] = ['user_id','=',($this->search['keyword'])];
        	}elseif ($this->search['key_type'] == 2) {
            	$where[] = ['miner_name','=',($this->search['keyword'])];
        	}elseif ($this->search['key_type'] == 3) {
            	$where[] = ['surplus_days','=',($this->search['keyword'])];
        	}
        }
        $data = $this->getPageList($this->Model,$where);
        foreach ($data['list'] as $key => $value) {
        	$data['list'][$key]['add_date'] = date('m-d H:i',$value['add_time']);

        	$pay_name = 'DDB支付';
        	if ($value['pay_type'] == 1) $pay_name = '叮叮支付';
        	
        	$data['list'][$key]['pay_name'] = $pay_name;
        }
        $status = ['待运行','运行中','等待返还','已到期'];
        $order_type = ['其他','矿机','定存包'];

		$this->assign("status", $status);
		$this->assign("order_type", $order_type);

		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单信息
    /*------------------------------------------------------ */
    protected function asInfo($data) {
        $status = ['待运行','运行中','等待返还','已到期'];
        $pay_type = ['其他','叮叮支付','DDB支付'];
        $order_type = ['其他','矿机','定存包'];

    	$data['order_type'] = $order_type[$data['pay_type']];
    	$data['status_name'] = $status[$data['status']];
    	$data['pay_name'] = $pay_type[$data['pay_type']];
    	$data['add_date'] = date('Y-m-d H:i:s',$data['add_time']);
    	$data['update_date'] = date('Y-m-d H:i:s',$data['update_time']);

        return $data;
    }
}
