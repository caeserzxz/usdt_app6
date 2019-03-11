<?php
namespace app\member\controller\sys_admin;

use app\AdminController;
use app\member\model\UsersModel;
use app\member\model\AccountModel;
use app\member\model\UsersLevelModel;
use app\member\model\UsersBindModel;
use app\distribution\model\DividendRoleModel;
use app\distribution\model\DividendModel;
use think\Db;
/**
 * 会员管理
 * Class Index
 * @package app\store\controller
 */
class Users extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
		$this->Model = new UsersModel(); 
		$this->is_ban = 0;
    }
	/*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
		$this->assign("roleOpt", arrToSel($this->roleList,$search['roleId']));
		$this->assign("levelOpt", arrToSel($this->levelList,$search['levelId']));
        return $this->fetch('sys_admin/users/index');
    }  

    /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false,$is_ban = -1) {
		
		$search['roleId'] = input('rode_id',0,'intval');
		$search['levelId'] = input('level_id',0,'intval');
		$search['keyword'] = input("keyword");		
		$search['time_type'] = input("time_type");
		
		$this->assign("is_ban", $this->is_ban);
		$this->assign("search", $search);
		$DividendRoleModel = new DividendRoleModel(); 
		$this->roleList = $DividendRoleModel->getRows();  
		$this->assign("roleList", $this->roleList);
		$UsersLevelModel = new UsersLevelModel(); 
		$this->levelList = $UsersLevelModel->getRows();
		$this->assign("levelList", $this->levelList);		
		$where[] = ' is_ban = '.$this->is_ban;
		
		$reportrange = input('reportrange');
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
		}
		switch ($search['time_type']) {
			case 'reg_time':
				$where[] = ' u.reg_time between '.strtotime($dtime[0]).' AND '.(strtotime($dtime[1])+86399);
				break;
			case 'login_time':
				$where[] = ' u.login_time between '.strtotime($dtime[0]).' AND '.(strtotime($dtime[1])+86399);
				break;
			case 'buy_time':
				$where[] = ' u.last_buy_time between '.strtotime($dtime[0]).' AND '.(strtotime($dtime[1])+86399);
				break;
			 default:
			 break;
		}
		
		if ($search['roleId'] > 0){
			$where[] = ' u.role_id = '.$search['roleId']; 
		}
		if ($search['levelId'] > 0){
			$level = $this->levelList[$search['levelId']];
			$where[] = ' uc.total_integral between '.$level['min'].' AND '.$level['max']; 
		}
	
        if(empty($search['keyword']) == false){			
			if (is_numeric($search['keyword'])){
				$where[] = "  u.user_id = '".($search['keyword'])."' or mobile like '".$search['keyword']."%'";  
			}else{
				$where[] = " ( u.user_name like '".$search['keyword']."%' or u.nick_name like '".$search['keyword']."%' )";  
			}			
		}
		$sort_by = input("sort_by",'DESC','trim');
		$order_by = 'u.user_id';
		$viewObj = $this->Model->alias('u')->join("users_account uc", 'u.user_id=uc.user_id','left')->where(join(' AND ',$where))->field('u.*,uc.balance_money,uc.use_integral')->order($order_by.' '.$sort_by);		
		
        $data = $this->getPageList($this->Model, $viewObj);	
		$data['order_by'] = $order_by;
		$data['sort_by'] = $sort_by;
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('sys_admin/users/list');
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
    //-- 重置密码
    /*------------------------------------------------------ */
    public function restPassword() {
		$user_id = input("user_id/d");
		if ($user_id < 0){
			return $this->error('获取用户ID传值失败！');
		}
		$data['password'] = f_hash('Abc123456');
		$oldpassword = $this->Model->where('user_id',$user_id)->value('password');
		if ($data['password'] == $oldpassword){
			return $this->error('当前用户密码为系统默认：Abc123456，无须修改.');
		}
		$res = $this->Model->where('user_id',$user_id)->update($data);
		if ($res < 1){
			return $this->error('未知错误，处理失败.');
		}
		$this->_log($user_id,'重置用户密码.','member');
		return $this->success('操作成功.');
	}
	/*------------------------------------------------------ */
	//-- 会员管理
	/*------------------------------------------------------ */
    public function info(){
		$user_id =  input('user_id/d');
		if ($user_id < 1) return $this->error('获取用户ID传值失败！');
		$row = $this->Model->info($user_id);		
		if (empty($row)) return $this->error('用户不存在！');
		$row['wx'] = Db::table('weixin_users')->where('user_id',$user_id)->find();		
		$this->assign("userShareStats", $this->Model->userShareStats($user_id));
		$row['user_address'] = Db::table('users_address')->where('user_id',$user_id)->select();	
		$this->assign('row',$row);
		$this->assign('Dividend',DividendModel::level());
		$this->assign('d_level',config('config.dividend_level'));
		$DividendRoleModel = new DividendRoleModel(); 
		$this->assign("roleList", $DividendRoleModel->getRows());
		$this->assign("teamCount", (new UsersBindModel)->where('pid',$user_id)->count());	
		return $this->fetch('sys_admin/users/info');
	}
	/*------------------------------------------------------ */
	//-- 修改分销身份
	/*------------------------------------------------------ */
    public function editRole(){
		$user_id = input('user_id',0,'intval');
		$row = $this->Model->info($user_id);
		$DividendRoleModel = new DividendRoleModel(); 	
		$roleList = $DividendRoleModel->getRows();
		if ($this->request->isPost()){
			$data['role_id'] = input('role_id',0,'intval');	
			$this->checkUpData($row,$data);
			$res = $this->Model->upInfo($user_id,$data);
			if ($res < 1) return $this->error('操作失败,请重试.');
			$info = '后台手工操作由【'.($row['role_id']==0?'粉丝':$roleList[$row['role_id']]['role_name']).'】升级为【'.$roleList[$data['role_id']]['role_name'].'】';
			$this->_log($user_id,$info,'member');
			return $this->success('修改分佣身份成功！','reload');
		}		
		$this->assign("roleList", $roleList);
		$this->assign("row", $row);
		
		return response($this->fetch('sys_admin/users/edit_role'));
	}
	/*------------------------------------------------------ */
	//-- 封禁会员
	/*------------------------------------------------------ */
    public function evelBan(){
		$user_id = input('user_id',0,'intval');
		$row = $this->Model->info($user_id);
		if ($row['is_ban'] == 1)return $this->error('会员已被封禁，无须重复操作.');
		$data['is_ban'] = 1;
		$res = $this->Model->upInfo($user_id,$data);
		if ($res < 1) return $this->error();
		$this->_log($user_id,'后台封禁会员.','member');
		return $this->success('禁封成功.','reload');
	}
	/*------------------------------------------------------ */
	//-- 解禁会员
	/*------------------------------------------------------ */
    public function reBan(){
		$user_id = input('user_id',0,'intval');
		$row = $this->Model->info($user_id);
		if ($row['is_ban'] == 0)return $this->error('会员已被解禁，无须重复操作.');
		$data['is_ban'] = 0;
		$res = $this->Model->upInfo($user_id,$data);
		if ($res < 1) return $this->error('操作失败,请重试.');
		$this->_log($user_id,'后台解禁会员.','member');
		return $this->success('解禁成功.','reload');
	}
	/*------------------------------------------------------ */
	//-- 根据关键字查询
	/*------------------------------------------------------ */
	public function pubSearch() {
		$keyword =  input('keyword','','trim');	
		
		if (!empty($keyword)){
			 $where = "( mobile LIKE '%".$keyword."%' OR user_name LIKE '%".$keyword."%' OR nick_name LIKE '%".$keyword."%' OR mobile LIKE '%".$keyword."%')";
		}
		$_list = $this->Model->where($where)->field("user_id,mobile,nick_name,user_name")->limit(20)->select();
		foreach ($_list as $key=>$row){
			$_list[$key] = $row;
		}
		$result['list'] = $_list;
		$result['code'] = 1;
		return $this->ajaxReturn($result);
	}
	/*------------------------------------------------------ */
	//-- 下级名单
	/*------------------------------------------------------ */
    public function getChainList(){
		$user_id = input('user_id',0,'intval');
		$DividendRole = (new DividendRoleModel)->getRows();
		$UsersBindModel = new UsersBindModel();
		$rows = $this->Model->field('user_id,nick_name,role_id')->where('pid',$user_id)->select();
		foreach ($rows as $key=>$row){
			$row['role_name'] = $DividendRole[$row['role_id']]['role_name'];
			$row['teamCount'] = $UsersBindModel->where('pid',$user_id)->count();
			$rows[$key] = $row;
		}
		$result['list'] = $rows;
		return $this->ajaxReturn($result);		
	}
	/*------------------------------------------------------ */
	//-- 上级名单
	/*------------------------------------------------------ */
    public function getSuperiorList(){
		$pid = input('pid',0,'intval');
		$result['code'] = 1;
		$result['list'] = $this->Model->getSuperior($pid);
		return $this->ajaxReturn($result);		
	}
}
