<?php
namespace app\distribution\controller\sys_admin;
use think\Db;

use app\AdminController;
use app\mainadmin\model\SettingsModel;
use app\distribution\model\DividendModel;
use app\distribution\model\EvalArrivalLogModel;
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
        $settings = settings();
		$Dividend['status'] = $settings['DividendSatus'];
		$Dividend['share_by_role'] = $settings['DividendShareByRole'];
		$this->assign('Dividend',$settings['DividendInfo']);
		$this->assign('share_bg',$settings['share_bg']);
		$this->assign('shop_after_sale_limit',$settings['shop_after_sale_limit']);
        $this->assign('setting',$settings);
		return $this->fetch();
	}
   
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
		$Dividend = input();
        $arr = input('post.setting');
		$arr['DividendSatus'] = $Dividend['status'];
		$arr['DividendShareByRole'] = $Dividend['share_by_role'] * 1;
		unset($Dividend['setting'],$Dividend['status'],$Dividend['DividendShareByRole'],$Dividend['share_bg']);
		$arr['DividendInfo'] = json_encode($Dividend);
		$arr['share_bg'] = input('share_bg','','trim');
		$res = $this->Model->editSave($arr);
		if ($res == false) return $this->error();
		return $this->success('设置成功.');
    }
	/*------------------------------------------------------ */
	//-- 手动结算
	/*------------------------------------------------------ */
    public function evalArrival(){
		$inData['log_time'] = time();
		$inData['admin_id'] = AUID;
		$EvalArrivalLogModel = new EvalArrivalLogModel();
		Db::startTrans();//事务启用
		$res = $EvalArrivalLogModel->save($inData);
		if ($res < 1){
			Db::rollback();//事务回滚
			return $this->error('执行失败-1，请重试.');
		}
		$res = (new DividendModel)->evalArrival(0,$EvalArrivalLogModel->log_id);
		if ($res == false){
			Db::rollback();//事务回滚
			return $this->error('执行失败-2，请重试.');
		}
		Db::commit();//事务提交
		return $this->success('操作成功.');
	}

}
