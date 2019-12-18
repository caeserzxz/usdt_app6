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
        $favour_time_cycle= settings('favour_time_cycle');
        $favour_start_time= settings('favour_start_time');
        $res = $this->Model->editSave($set);
        if ($res == false) return $this->error();
        //档期档期间隔 或 活动开始时间 变动，清空档期记录
        if($set['favour_time_cycle']!=$favour_time_cycle||$set['favour_start_time']!=$favour_start_time){
            (new \app\favour\model\FavourGoodsModel)->clearTimeSlot();

        }
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
