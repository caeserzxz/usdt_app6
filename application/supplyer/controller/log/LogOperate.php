<?php
namespace app\supplyer\controller\log;
use app\supplyer\Controller;

use app\supplyer\model\LogSysModel;
/**
 * 操作日志
 * Class Index
 * @package app\store\controller
 */
class LogOperate extends Controller
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
		$reportrange = input('reportrange');
        $where[] = ['log.edit_id','=',$this->supplyer_id];

		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['log.log_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['log.log_time','between',[strtotime("-1 months"),time()]];
		}

        $sort_by = input("sort_by", 'DESC', 'trim');
        $order_by = 'log.log_id';
        $viewObj = $this->Model->alias('log')->join("supplyer s", 'log.edit_id=s.supplyer_id', 'left')->where($where)->field('log.*,s.supplyer_name')->order($order_by . ' ' . $sort_by);

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
