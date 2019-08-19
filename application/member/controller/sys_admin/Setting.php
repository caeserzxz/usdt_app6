<?php
namespace app\member\controller\sys_admin;
use app\AdminController;
use app\mainadmin\model\SettingsModel;
/**
 * 设置
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
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
        $setting = $this->Model->getRows();
        if (empty($setting['sign_constant']) == false){
            $setting['sign_constant'] = json_decode($setting['sign_constant'],true);
        }else{
            $setting['sign_constant'] = [];
        }
		$this->assign("setting", $setting);
        return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 保存配置
	/*------------------------------------------------------ */
    public function save(){
        $set = input('post.');
        if (empty($set['sign_constant']) == false){
            $day = 0;
            $constants = $set['sign_constant'];
            unset($set['sign_constant']);
            foreach ($constants as $constant){
                if ($day >= $constant['day']){
                    return $this->error('连续签到天数设置错误，天数必须从上到下递增.'.$day.' - '.$constant['day']);
                }
                $day = $constant['day'];
                $set['sign_constant'][$day] = $constant;
            }
            $set['sign_constant'] = json_encode($set['sign_constant']);
        }
		$res = $this->Model->editSave($set);
		if ($res == false) return $this->error();
		return $this->success('设置成功.');
    }

}
