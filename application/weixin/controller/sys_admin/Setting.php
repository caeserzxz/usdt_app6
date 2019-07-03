<?php
namespace app\weixin\controller\sys_admin;

use app\AdminController;
use think\facade\Cache;


use app\mainadmin\model\SettingsModel;
/**
 * 微信设置
 * Class Index
 * @package app\store\controller
 */
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
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
		
		$this->assign("domain", $this->request->domain());	
		$this->assign("setting", $this->Model->getRows());
		return $this->fetch();
	}
   
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.setting');
		$res = $this->Model->editSave($set);
		if ($res == false) return $this->error();
        Cache::rm('weixin_access_token');
		return $this->success('设置成功.');
    }
    /*
	// 小程序配置
    */
    public function xcxconfig(){
        $this->assign("domain", $this->request->domain());
        $this->assign("setting", $this->Model->getRows());
        return $this->fetch();
    }
}
