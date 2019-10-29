<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;
use app\distribution\model\DividendRoleModel;
use app\distribution\model\RoleGoodsModel;
use think\facade\Env;

use app\member\model\UsersModel;
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
		
		$this->sqlOrder = 'level ASC';
        $data = $this->getPageList($this->Model);			
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
	//-- 获取所有接口程序
	/*------------------------------------------------------ */
    public function getFunction() {
		$rows = readModules(Env::get('extend_path').'/distribution/'.config('config.dividend_type'),'Level');

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
		$this->assign('upLevel',  $this->getFunction());
		$roleList = $this->Model->getRows();
		if ($data['role_id'] > 0){
			foreach ($roleList as $key=>$role){
				if ($role['level'] > $data['level']){
					unset($roleList[$key]);	
				}
			}
			unset($roleList[$data['role_id']]);	
		}
		$this->assign('roleList', $roleList);

		$data['function'] = [];
		if ($data['role_id'] > 0){
			$upleve_value = json_decode($data['upleve_value'],true);
			$data['function'][$data['upleve_function']] = $upleve_value;
			if (empty($upleve_value['buy_goods']) == false){
				$where[] = ['goods_id','in',$upleve_value['buy_goods']];
				$list = (new \app\shop\model\GoodsModel)->where($where)->field('goods_id,goods_sn,goods_name')->select();
				if (empty($list) == false) $data['function'][$data['upleve_function']]['buy_goods'] = $list->toArray(); 
			}
		}
		
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		$count = $this->Model->where('role_name',$data['role_name'])->whereOr('level',$data['level'])->count('role_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的身份名称或已存在相同级别，不允许重复添加！');		
		$data['add_time'] = $data['update_time'] = time();
		$upleve_value = input('function_val');
        $buy_goods_id = input('buy_goods_id');
		if (empty($buy_goods_id) == false){
            $upleve_value['buy_goods'] = $buy_goods_id;
        }
		$data['upleve_value'] = json_encode($upleve_value);
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
		$upleve_value = input('function_val');
		$buy_goods_id = input('buy_goods_id');
        if (empty($buy_goods_id) == false){
            $upleve_value['buy_goods'] = $buy_goods_id;
        }
		$data['upleve_value'] = json_encode($upleve_value);
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['role_id'],'修改分销身份:'.$data['role_name'].'，级别：'.$data['level']);
	}
	/*------------------------------------------------------ */
    //-- 删除
    /*------------------------------------------------------ */
    public function delete()
    {
        $role_id = input('role_id/d');
		if ($role_id < 1){
			return $this->error('传参错误.');	
		}
		$data = $this->Model->where('role_id',$role_id)->find();
		if (empty($data) == true){
			return $this->error('没有找到相关身份.');	
		}
		$count = (new UsersModel)->where('role_id',$role_id)->count();
		if ($count > 0){
			return $this->error('有会员是此分销身份，请修改相关会员身份后再操作.');
		}
        $res = $this->Model->where('role_id',$role_id)->delete();
        if ($res < 1) return $this->error('删除失败，请重试.');
        $this->Model->cleanMemcache();
		$this->_Log($data['role_id'],'删除分销身份:'.$data['role_name']);
        return $this->success('删除成功.');
    }
}
