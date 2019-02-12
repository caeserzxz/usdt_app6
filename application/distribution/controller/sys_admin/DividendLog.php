<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;

use app\distribution\model\DividendModel;

//*------------------------------------------------------ */
//-- 佣金明细
/*------------------------------------------------------ */
class DividendLog extends AdminController{
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
    public function initialize(){	
   		parent::initialize();
		$this->Model = new DividendModel();
    }
	
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		$this->assign("divdend_satus", lang('divdend_satus'));
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
        return $this->fetch('index');
    }
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false,$is_cancel = false){
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['add_time','between',[strtotime("-1 months"),time()]];
		}
        $data = $this->getPageList($this->Model, $where);	
		$this->assign("data", $data);
		$this->assign("search", $search);
		if ($runData == false){
			$data['content'] = $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }

}
