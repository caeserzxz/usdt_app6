<?php
namespace app\mainadmin\controller;
use app\AdminController;
use app\mainadmin\model\PaymentModel;
/**
 * 支付列表
 * Class Index
 * @package app\store\controller
 */
class Payment extends AdminController
{
	
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
		$this->Model = new PaymentModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
		$this->assign("_list", $this->Model->getRows());
        return $this->fetch('index');
    }
 	/*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){	
		if (empty($data['def_config']) == false){
			$data['def_config'] = json_decode($data['def_config'],true);		
		}
		$data['pay_config'] = json_decode(urldecode($data['pay_config']),true);
		return $data;
	}
   /*------------------------------------------------------ */
	//-- 修改前调用
	/*------------------------------------------------------ */
	public function beforeEdit($row){		
		$row['pay_config'] = urlencode(json_encode($row['pay_config']));
		return $row;
	}
	/*------------------------------------------------------ */
	//-- 修改后调用
	/*------------------------------------------------------ */
	public function afterEdit($row){		
		$this->_Log($row['pay_id'],'修改支付配置');
		$this->Model->cleanMemcache();//清除缓存
		return $this->success('修改成功.',url('index'));
	}
	/*------------------------------------------------------ */
	//-- 快速修改后处理
	/*------------------------------------------------------ */
    public function afterAjax($id,$data){		
		$log = '快速修改支付：'.$data['pay_name'];
		if (isset($data['status'])){
			$log .= '状态为';
			$log .= ($data['status'] == 1)?'开启':'关闭';	
		}
		$this->Model->cleanMemcache();//清除缓存
		$this->_Log($id,$log);
		return true;		
	}

}
