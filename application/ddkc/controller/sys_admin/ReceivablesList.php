<?php
namespace app\ddkc\controller\sys_admin;
use app\AdminController;
use app\ddkc\model\PaymentModel;
/**
 * 矿机订单
 */
class ReceivablesList extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new PaymentModel(); 
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
        $this->search['type'] = input("type");        
		$this->order_by = 'order_id';
		$this->sort_by = 'DESC';
		if (input('reportrange')) {
	        $dtime = explode('-', input('reportrange'));
	        $where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}
        if ($this->search['type']) {
            $where[] = ['type','=',($this->search['type'])];
        }
        if ($this->search['keyword']) {
        	if ($this->search['key_type'] == 1) {
            	$where[] = ['user_id','=',($this->search['keyword'])];
        	}
        }
        $data = $this->getPageList($this->Model,$where);
        foreach ($data['list'] as $key => $value) {
        	$data['list'][$key]['add_date'] = date('m-d H:i',$value['add_time']);
            // 账号
            if ($value['type'] == 1) {
                $data['list'][$key]['account'] = $value['card_number'];
            }elseif ($value['type'] == 2) {
                $data['list'][$key]['account'] = $value['alipay_number'];
            }elseif ($value['type'] == 3) {
                $data['list'][$key]['account'] = $value['wx_number'];
            }
        }
        $type = ['其他','银行卡','支付宝','微信'];

		$this->assign("type", $type);
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
        $type = ['其他','银行卡','支付宝','微信'];

    	$data['type_name'] = $type[$data['type']];
    	$data['add_date'] = date('Y-m-d H:i:s',$data['add_time']);
        return $data;
    }
}
