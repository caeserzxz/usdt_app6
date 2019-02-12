<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use think\facade\Env;
use app\mainadmin\model\SettingsModel;
/*------------------------------------------------------ */
//-- 设置
/*------------------------------------------------------ */
class Setting extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new SettingsModel();		
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		
		$this->assign("setting", $this->Model->getRows());
		$this->assign('shippingFunction',  $this->getShippingFunction());
        return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.');
		$res = $this->Model->editSave($set);
		if ($res == false) return $this->error();
		return $this->success('设置成功.');
    }
	/*------------------------------------------------------ */
	//-- 获取所快递有接口程序
	/*------------------------------------------------------ */
    public function getShippingFunction() {
		$rows = readModules(Env::get('extend_path').'/shipping');
		$modules = array();
		foreach ($rows as $row){
			$modules[$row['function']] = $row;
		}
		return $modules;
	}

}
