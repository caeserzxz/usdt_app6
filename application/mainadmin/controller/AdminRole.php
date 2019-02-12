<?php
namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\AdminRoleModel;
use app\mainadmin\model\MenuListModel;

//*------------------------------------------------------ */
//-- 管理员角色管理
/*------------------------------------------------------ */
class AdminRole extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new AdminRoleModel(); 
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
        $data = $this->getPageList($this->Model);			
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
    //-- 调用详细页
    /*------------------------------------------------------ */
    public function asInfo($data) {
		$menuList = model(MenuListModel)->getList();
		//print_r($menuList);
		$this->assign("menuList", $menuList);
		$data['role_action'] = explode(',',$data['role_action']);
		$this->assign("purviewArr", ['manage'=>'管理权限','download'=>'下载权限','export'=>'导出','view'=>'查看权限','edit'=>'添加/修改','del'=>'删除权限']);
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		if (empty($data['role_action']) ) return $this->error('请设置角色权限.');		
		if (empty($data['role_name']) ) return $this->error('角色名称不能为空.');
		$data['role_action'] = join(',',$data['role_action']);				
		$map['role_name'] = $data['role_name'];
		$count = $this->Model->where($map)->count();
		if ($count > 0) return $this->error('已存在相同的角色名称，不允许重复添加.');			
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后处理
	/*------------------------------------------------------ */
    public function afterAdd($data) {
		$this->_Log($data['role_id'],'添加角色:'.$data['role_name']);
	}
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){	
		$map['role_id'] = $data['role_id'];
		if (empty($data['role_action']) ) return $this->_error('请设置角色权限！');		
		if (empty($data['role_name']) ) return $this->error('角色名称不能为空！');
		$data['role_action'] = join(',',$data['role_action']);
		//验证数据是否出现变化
		$dbarr = $this->Model->field(join(',',array_keys($data)))->where($map)->find()->toArray();		
		$this->checkUpData($dbarr,$data);
		$where[]=['role_id','<>',$map['role_id']];
		$where[]=['role_name','=',$data['role_name']];
		$count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的角色名称，不允许重复添加！');
		
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['role_id'],'修改角色:'.$data['role_name']);
	}
}
