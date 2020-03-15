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
        $status = ['待运行','运行中','已到期'];
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
        $status = ['待运行','运行中','已到期'];
        $pay_type = ['其他','叮叮支付','DDB支付'];
        $order_type = ['其他','矿机','定存包'];

    	$data['order_type'] = $order_type[$data['pay_type']];
    	$data['status_name'] = $status[$data['status']];
    	$data['pay_name'] = $pay_type[$data['pay_type']];
    	$data['add_date'] = date('Y-m-d H:i:s',$data['add_time']);
    	$data['update_date'] = date('Y-m-d H:i:s',$data['update_time']);

        return $data;
    }
    /*------------------------------------------------------ */
    //-- 摧毁矿机 订单退款
    /*------------------------------------------------------ */
    public function destroy_mining() {
        $profitModel = new MiningProfitLog();
		$order_id = input('order_id',0,'intval');
		$time = time();
		$order = $this->Model->where(['order_id' => $order_id,'status' => 1])->find();
		if (!$order) {
			return $this->error('操作失败:订单不存在或状态错误！');
		}
		# 是否是积分支付
		if ($order['pay_type'] != 2) {
			return $this->error('操作失败:非积分支付订单无法摧毁！');
		}
		# 是否在X天内
		$destroy_time = $order['add_time']+($order['returnable_day']*60*60*24);
		if ($time > $destroy_time) {
			return $this->error('操作失败:已超出可回收天数无法摧毁！');
		}
		$update = ['surplus_days' => 0,'status' => 3,'update_time' => $time];
		# 更改订单状态
		$res1 = $this->Model->where(['order_id' => $order_id])->update($update);
		if (!$res1) {
			return $this->error('操作失败:订单更新失败！');
		}
		# 收益自动失效
		$res2 = $profitModel->where(['order_id' => $order_id,'status' => 0])->update($update);
		if (!$res2) {
			return $this->error('操作失败:收益追回失败！');
		}
		return $this->success('操作成功！');
    }
	











	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		if (empty($data['miner_name'])) return $this->error('操作失败:矿机名称不能为空！');
		$imgs = input('imgs');
        $data['imgs'] = '';
        if(is_array($imgs['path'])) $data['imgs'] = serialize($imgs['path']);
		
		$count = $this->Model->where('miner_name',$data['miner_name'])->count('miner_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的矿机名称，不允许重复添加！');
		
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后处理
	/*------------------------------------------------------ */
    public function afterAdd($data) {
		$this->_Log($data['miner_id'],'矿机:'.$data['level_name']);
	}
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
     	
		if (empty($data['miner_name'])) return $this->error('操作失败:矿机名称不能为空！');
		$where[] = ['miner_name','=',$data['miner_name']];
		$where[] = ['miner_id','<>',$data['miner_id']];
		$count = $this->Model->where($where)->count('miner_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的矿机名称，不允许重复添加！');
		unset($where);
		$imgs = input('imgs');
        $data['imgs'] = '';
        if(is_array($imgs['path'])) $data['imgs'] = serialize($imgs['path']);
	
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['miner_id'],'修改矿机:'.$data['miner_name']);
	}
	/*------------------------------------------------------ */
	//-- 删除等级
	/*------------------------------------------------------ */
	public function delete(){
		$miner_id = input('miner_id',0,'intval');
		if ($miner_id < 1)  return $this->error('传参失败！');
		$res = $this->Model->where('miner_id',$miner_id)->delete();
		if ($res < 1) return $this->error('未知错误，删除失败！');
		$this->Model->cleanMemcache();	
		return $this->success('删除成功！',url('index'));
	}    
}
