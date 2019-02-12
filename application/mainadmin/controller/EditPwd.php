<?php
namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\AdminUserModel;
/**
 * 修改密码
 */
class EditPwd extends AdminController
{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
        return $this->fetch();
    }  
	/*------------------------------------------------------ */
	//-- 修改密码
	/*------------------------------------------------------ */
	public function editPwd()
    {
		$old_pwd = input('old_pwd');
		$data['password'] = input('new_password');
		if (empty($old_pwd)){
			 return $this->error('请输入当前使用的密码.');
		}
		if (empty($data['password'])){
			 return $this->_error('请输入新的密码.');
		}
		$Model = new AdminUserModel();
		$userInfo = $Model->find(AUID);
		if (empty($userInfo)){
			return $this->error('没有找到相关帐号信息，或重新登陆再试.');		
		}
		if ($userInfo['password'] != _hash($old_pwd)){
			return $this->error('当前使用密码错误，请核实.');
		}
		$data['user_id'] = AUID;
		$data['password'] = _hash($data['password']);
		$res = $Model->saveDate($data,'editPwd');
		if ($res < 1){
			return $this->error('未知错误，修改失败.');
		}
	    $Model->logout();
        return $this->success('修改成功，请使用新密码重新登陆.');
    }  
}
