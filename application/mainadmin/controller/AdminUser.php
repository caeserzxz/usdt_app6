<?php
namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\AdminUserModel;
use app\mainadmin\model\AdminRoleModel;
/**
 * 管理员帐号管理
 */
class AdminUser extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
		$this->Model = new AdminUserModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {		
		$this->getList(true);
		$this->assign("roleOpt", arrToSel($this->roleList,$this->roleId));
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$AdminRoleModel = new AdminRoleModel();        
		$where = [];
		$this->roleList = $AdminRoleModel->getRows();  
		$this->roleId = input('role_id',0,'intval');
		if ($this->roleId > 0){
			$where[] = ['role_id','=',$this->roleId]; 
		}
		$keyword = input("keyword");
        if($keyword){
            $where = "user_name LIKE '%".$keyword."%' OR user_id LIKE '%".$keyword."%'";
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
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data = array()){
		$AdminRoleModel = new AdminRoleModel();
		$roleList = $AdminRoleModel->getRows(); 		
		$this->assign("roleOpt", arrToSel($roleList,$data['role_id'] * 1));
		return 	$data;
	}
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
        if (empty($data['user_name'])){
            return $this->error('请输入用户名称.');
        }
		$count = $this->Model->where('user_name',$data['user_name'])->count('user_id');
		if ($count > 0) return $this->error('操作失败:已存在帐号，不允许重复添加！');
		$data['add_time'] = time();	
		$data['update_time'] = time();
		$pwd = input('new_password','','trim');
		$data['password'] = _hash($pwd);
		return $data;
	}

	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		$data['update_time'] = time();
		$pwd = input('new_password','','trim');
		unset($data['password']);
		if (empty($pwd) == false){
			$data['password'] = _hash($pwd);
		}
		return $data;		
	}
}
