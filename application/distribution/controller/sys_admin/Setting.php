<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;

use app\mainadmin\model\SettingsModel;

/**
 * 分销设置
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
    public function index()
	{
		$Dividend = settings('DividendInfo');
		$Dividend['status'] = settings('DividendSatus');
		$Dividend['share_by_role'] = settings('DividendShareByRole');
		$this->assign('Dividend',$Dividend);
		$this->assign('share_bg',settings('share_bg'));		
		return $this->fetch();
	}
   
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
		$Dividend = input();		
		$arr['DividendSatus'] = $Dividend['status'];
		$arr['DividendShareByRole'] = $Dividend['share_by_role'] * 1;
		unset($Dividend['status'],$DividendSatus['DividendShareByRole'],$Dividend['share_bg']);
		$arr['DividendInfo'] = json_encode($Dividend);
		$arr['share_bg'] = input('share_bg','','trim');
		$res = $this->Model->editSave($arr);
		if ($res == false) return $this->error();
		return $this->success('设置成功.');
    }

}
