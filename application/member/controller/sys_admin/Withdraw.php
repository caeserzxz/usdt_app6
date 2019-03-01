<?php
namespace app\member\controller\sys_admin;
use think\Db;

use app\AdminController;
use app\member\model\WithdrawModel;
use app\member\model\WithdrawAccountModel;
use app\member\model\UsersModel;
use app\member\model\AccountLogModel;
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
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
		$this->assign("userWithdrawTypeOpt", arrToSel($this->userWithdrawType,0));
		return $this->fetch();
	}
   /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$this->userWithdrawType = $this->getDict('UserWithdrawType');	
		$search['keyword'] = input('keyword','','trim');
		$search['status'] = input('status',0,'intval');
		$search['type'] = input('type','','trim');
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['w.add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['w.add_time','between',[strtotime("-1 months"),time()]];
		}
		if ($search['status'] >= 0){
			$where[] = ['w.status','=',$search['status']];
		}	
		if (empty($search['keyword']) == false){
			 $UsersModel = new UsersModel();
			 $uids = $UsersModel->where(" mobile LIKE '%".$search['keyword']."%' OR user_name LIKE '%".$search['keyword']."%' OR nick_name LIKE '%".$search['keyword']."%' OR mobile LIKE '%".$search['keyword']."%'")->column('user_id');
			 $uids[] = -1;//增加这个为了以上查询为空时，限制本次主查询失效			 
			 $where[] = ['w.user_id','in',$uids];
		}
		if (empty($search['type']) == false){
			$where[] = ['uwa.type','=',$search['type']];
		}
		$viewObj = $this->Model->alias('w')->join("users_withdraw_account uwa", 'w.account_id=uwa.account_id','left')->where($where);	
        $data = $this->getPageList($this->Model,$viewObj);
		$this->assign("userWithdrawType", $this->userWithdrawType);
		$this->assign("search", $search);		
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }

	/*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){
		$data['account'] = (new WithdrawAccountModel)->find($data['account_id']);
		if (empty($data['account']) == false){
			$data['account'] = $data['account']->toArray();	
		}
		$userWithdrawType = $this->getDict('UserWithdrawType');
		$data['status_name'] = $userWithdrawType[$data['status']]['name'];
		return $data;
	}
	
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		$operating = input('operating','note','trim');
		if ($operating == 'note'){
			if (empty($data['admin_note'])){
				return $this->error('请填写备注.');
			}
		}elseif ($operating == 'refuse'){
			if (empty($data['admin_note'])){
				return $this->error('请填写备注，列明拒绝原因.');
			}
			$data['status'] = 1;
			$data['refuse_time'] = time();
			$data['admin_id'] = AUID;
		}elseif ($operating == 'pay'){
			if (empty($data['pay_info'])){
				return $this->error('请填写打款信息.');
			}
			$data['status'] = 9;
			$data['complete_time'] = time();
			$data['admin_id'] = AUID;
		}else{
			return $this->error('非法操作.');
		}		
		$data['update_time'] = time();
		Db::startTrans();//启动事务
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data){
		if ($data['status'] == 1){//拒绝提现，退回帐户
			$info = $this->Model->find($data['log_id']);
			$AccountLogModel = new AccountLogModel();
			$changedata['change_desc'] = '提现失败退回';
			$changedata['change_type'] = 5;
			$changedata['by_id'] = $info['log_id'];
			$changedata['balance_money'] = ($info['amount'] + $info['withdraw_fee']);
			$res = $AccountLogModel->change($changedata, $info['user_id'], false);
			if ($res !== true) {
				Db::rollback();// 回滚事务
				return $this->error('未知错误，提现退回用户余额失败.');
			}			
		}
		Db::commit();// 提交事务
		return $this->success('操作成功.',url('index'));
	}

}
