<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;
use app\distribution\model\DividendRoleModel;
use think\facade\Env;

/**
 * 分销身份管理
 */
class Role extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new DividendRoleModel(); 
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
		$this->order_by = 'level';
		$this->sort_by = 'ASC';
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
	//-- 获取所有接口程序
	/*------------------------------------------------------ */
    public function getFunction($type = '') {
		$rows = readModules(Env::get('extend_path').'/distribution/'.$type);
		$modules = array();
		foreach ($rows as $row){
			$modules[$row['function']] = $row;
		}
		return $modules;
	}
	/*------------------------------------------------------ */
	//-- 详细页调用
	/*------------------------------------------------------ */
    public function asInfo($data) {
		$this->assign('upLevel',  $this->getFunction('upLevel'));
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		$count = $this->Model->where('role_name',$data['role_name'])->whereOr('level',$data['level'])->count('role_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的身份名称或已存在相同级别，不允许重复添加！');		
		$data['add_time'] = $data['update_time'] = time();
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后处理
	/*------------------------------------------------------ */
    public function afterAdd($data) {
		$this->_Log($data['role_id'],'添加分销身份:'.$data['role_name']);
	}
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){	
		$info = $this->Model->find($data['role_id'])->toArray();
		
		$this->checkUpData($info,$data);
		$where[] = ' role_id <> '.$data['role_id'];
		$where[] = " (role_name = '".$data['role_name']."' OR level = '".$data['level']."' )";
		
		$count = $this->Model->where(join(' AND ',$where))->count('role_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的身份名称或已存在相同级别，不允许重复添加！');	
		$data['update_time'] = time();
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['role_id'],'修改分销身份:'.$data['role_name'].'，级别：'.$data['level']);
	}
	
}
