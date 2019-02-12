<?php
namespace app\member\controller\sys_admin;
use app\AdminController;
use app\member\model\WithdrawModel;
use app\member\model\UsersModel;
//*------------------------------------------------------ */
//-- 提现
/*------------------------------------------------------ */
class Withdraw extends AdminController
{
	 //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize(){	
   		parent::initialize();
		$this->Model = new WithdrawModel(); 
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){
		$this->assign("UserWithdrawTypeOpt", arrToSel($this->getDict('UserWithdrawType')));
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		return $this->fetch();
	}
   /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$this->search['keyword'] = input('keyword/d');
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['add_time','between',[strtotime("-1 months"),time()]];
		}
		if (empty($this->search['keyword']) == false){
			 $uwhere[] = "( mobile LIKE '%".$keyword."%' OR user_name LIKE '%".$keyword."%' OR nick_name LIKE '%".$keyword."%' OR mobile LIKE '%".$keyword."%')";
			 $UsersModel = new UsersModel();
			 $uids = $UsersModel->where($uwhere)->column('user_id');
			 $uids[] = -1;//增加这个为了以上查询为空时，限制本次主查询失效			 
			 $where[] = ['user_id','in',$uids];
		}
		
        $data = $this->getPageList($this->Model,$where);
		$this->assign("search", $this->search);		
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }


}
