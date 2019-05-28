<?php
namespace app\member\controller\sys_admin;
use app\AdminController;

use app\member\model\LogSysModel;
/**
 * 操作日志
 * Class Index
 * @package app\store\controller
 */
class LogOperate extends AdminController
{
	

   //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new LogSysModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);		
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		
		$this->search['user_id'] = input('user_id/d');
		$this->search['edit_id'] = input('edit_id/d');
		
		$this->assign("search", $this->search);
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['log_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['log_time','between',[strtotime("-1 months"),time()]];
		}
		if ($this->search['user_id'] > 0){
			$where[] = ['user_id','=',$this->search['user_id'] ];
		}
		if ($this->search['edit_id'] > 0){
			$where[] = ['edit_id','=',$this->search['edit_id']];
		}
        $data = $this->getPageList($this->Model,$where);
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
  


}
