<?php
namespace app\supplyer\controller\sys_admin;

use app\AdminController;
use app\supplyer\model\SupplyerModel;
use app\shop\model\GoodsModel;
/**
 * 供应商帐号管理
 */
class Supplyer extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
		$this->Model = new SupplyerModel();
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {		
		$this->getList(true);

        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$where = [];

		$keyword = input("keyword");
        if($keyword){
            $where[] = ['supplyer_name','like','%'.$keyword.'%'];
            $where['or'][] = 'supplyer_id = '.$keyword * 1;
        }
		
        $data = $this->getPageList($this->Model, $where);			
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }

	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
        if (empty($data['supplyer_name'])){
            return $this->error('请输入供应商名称.');
        }
		$count = $this->Model->where('supplyer_name',$data['supplyer_name'])->count('supplyer_id');
		if ($count > 0) return $this->error('操作失败:已存在供应商名称，不允许重复添加！');
		$data['add_time'] = time();	
		$data['update_time'] = time();
		$data['password'] = _hash($data['password']);
		return $data;
	}
    /*------------------------------------------------------ */
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($data)
    {
        $logInfo = '添加供应商帐号，供应商帐号状态：';
        $logInfo .= $data['is_ban'] == 1 ? '封禁':'正常';
        $this->_Log($data['supplyer_id'],$logInfo);
        return $this->success('修改成功.',url('index'));
    }
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
        if (empty($data['supplyer_name'])){
            return $this->error('请输入供应商名称.');
        }
        $data['is_ban'] = $data['is_ban'] * 1;
        $where[] = ['supplyer_name','=',$data['supplyer_name']];
        $where[] = ['supplyer_id','<>',$data['supplyer_id']];
        $count = $this->Model->where($where)->count('supplyer_id');
        if ($count > 0) return $this->error('操作失败:已存在供应商名称，不允许重复添加！');
		$data['update_time'] = time();
		if (empty($data['password']) == false){
			$data['password'] = _hash($data['password']);
		}else{
		    unset($data['password']);
        }
        $logInfo = '修改供应商信息，状态：'.($data['is_ban'] == 1 ? '封禁':'正常');
        $this->_log($data['supplyer_id'], $logInfo ,'supplyer');
		return $data;		
	}
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($data){
        $logInfo = '供应商帐号状态：';
        if ($data['is_ban'] == 1){
            $logInfo .= '封禁';
            //批量执行商品下架
            (new GoodsModel)->where('supplyer_id',$data['supplyer_id'])->update(['isputaway'=>0]);
        }else{
            $logInfo .= '正常';
        }
        if (empty($data['password']) == false){
            $logInfo .= '，修改供应商密码.';
            $this->_Log($data['supplyer_id'],'平台修改供应商密码','supplyer');
        }
        $this->_Log($data['supplyer_id'],$logInfo);
        return $this->success('修改成功.',url('index'));
    }
}
