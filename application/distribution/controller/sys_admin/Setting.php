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
		$this->assign('Dividend',$Dividend);
		$this->assign('d_level',config('config.DIVIDEND_LEVEL'));
		return $this->fetch();
	}
   
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
		$Dividend = input();
		if ($this->Dividend['account_limit_money']) $Dividend['account_limit_money']=$this->Dividend['account_limit_money'];
		$d_value = 51;
		$max_point = 0;
		$einfo = '系统限制了最高提成不能高于70%！';
		foreach ($Dividend['LevelRow'] as $key=>$row){
			if ($Dividend['dividend_model'] == 1 ){
				if ($row['money'] >= $d_value) return $this->error($einfo);
				$max_point += $row['money'] * 1;
			}
			
		}
		if ($Dividend['dividend_model'] == 1 ){
			if ($max_money > 70 || $max_point > 70) return $this->error('系统限制了总提成不能高于70%！');	
		}
		$arr['DividendSatus'] = $Dividend['status'];
		unset($Dividend['status']);
		$arr['DividendInfo'] = json_encode($Dividend);
		$res = $this->Model->editSave($arr);
		if ($res == false) return $this->error();
		return $this->success('设置成功.');
    }

}
