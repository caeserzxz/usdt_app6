<?php
namespace app\mainadmin\controller;
use app\AdminController;
use think\facade\Env;
use app\mainadmin\model\SettingsModel;
/**
 * 短信接口
 * Class Index
 * @package app\store\controller
 */
class SmsFun extends AdminController
{
	

    public function index()
    {	
		
		$this->assign('row',  settings('sms_fun'));
		$this->assign('function',  $this->getSmsFunction());
        return $this->fetch();
    }

  	/*------------------------------------------------------ */
	//-- 获取所有接口程序
	/*------------------------------------------------------ */
    public function getSmsFunction() {
		$rows = readModules(Env::get('extend_path').'/sms');
		$modules = array();
		foreach ($rows as $row){
			$modules[$row['function']] = $row;
		}
		return $modules;
	}
	/*------------------------------------------------------ */
	//-- 保存短信设置
	/*------------------------------------------------------ */
    public function save() {
		$data['register'] = input('register',0,'intval');
		$data['forget_password'] = input('forget_password',0,'intval');
        $data['bind_mobile'] = input('bind_mobile',0,'intval');
		$data['shipping'] = input('shipping',0,'intval');
		$data['login'] = input('login',0,'intval');
		$data['admin_login'] = input('admin_login',0,'intval');
		
		if ($data['admin_login'] == 1){
			$adminInfo = adminInfo(AUID,false);
			if (empty($adminInfo['moblie'])){
				return $this->error('你未设置管理员手机号码，不能开通短信验证.');
			}
		}
		
		$supplier = input('supplier','','trim');
		if (empty($supplier)){
			return $this->error('请选择短信接口.');
		}
		$modules = $this->getSmsFunction();
		$data['supplier'] = $modules[$supplier]['name'];
		$data['function'] = $modules[$supplier]['function'];
		$function_val = input('function_val');
		$data['function_val'] = $function_val;
		$SettingsModel = new SettingsModel();
		$res = $SettingsModel->editSave(['sms_fun'=>json_encode($data)]);
		if ($res != true) return $this->error('未知错误');
		$this->_log(0,'修改短信设置.');
		return $this->success("操作成功.");
	}

}
