<?php
namespace app\supplyer\controller\sys_admin;
use app\AdminController;

use app\supplyer\model\LogLoginModel;
/**
 * 登陆日志
 * Class Index
 * @package app\store\controller
 */
class LogLogin extends AdminController
{
   //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new LogLoginModel(); 
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
	
		$this->assign("search", $this->search);
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['log_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['log_time','between',[strtotime("-1 months"),time()]];
		}
		if (0 < $this->search['user_id'] ){
			$where[] = ['user_id','=',$this->search['user_id'] ];
		}

        $sort_by = input("sort_by", 'DESC', 'trim');
        $order_by = 'log.log_id';
        $viewObj = $this->Model->alias('log')->join("supplyer s", 'log.supplyer_id=s.supplyer_id', 'left')->where($where)->field('log.*,s.supplyer_name')->order($order_by . ' ' . $sort_by);

        $data = $this->getPageList($this->Model,$viewObj);
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
  


}
