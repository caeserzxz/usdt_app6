<?php
namespace app\member\controller\sys_admin;
use think\Db;

use app\AdminController;
use app\member\model\RechargeLogModel;
use app\mainadmin\model\PaymentModel;
use app\member\model\AccountLogModel;
//*------------------------------------------------------ */
//-- 充值
/*------------------------------------------------------ */
class Recharge extends AdminController
{
    public $pay_id;
	 //*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize(){	
   		parent::initialize();
		$this->Model = new RechargeLogModel(); 
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index(){		
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
        $this->pay_id = 3;//默认选中线下打款
		$this->getList(true);
		$this->assign("userRechargeTypeOpt", arrToSel($this->userRechargeType,0));	
		$this->assign("payList",$this->payList);
		return $this->fetch();
	}
   /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$this->userRechargeType = $this->getDict('UserRechargeType');	
		$this->payList = (new PaymentModel)->getRows(false,'pay_code');
		$search['keyword'] = input('keyword','','trim');
		$search['status'] = input('status',0,'intval');
		$search['pay_id'] = input('pay_id',$this->pay_id,'intval');
		$reportrange = input('reportrange');
		$where = [];
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['add_time','between',[strtotime("-1 months"),time()]];
		}
		if ($search['status'] >= 0){
			$where[] = ['status','=',$search['status']];
		}
		if ($search['pay_id'] > 0){
			$where[] = ['pay_id','=',$search['pay_id']];
		}
		if (empty($search['keyword']) == false){
			 $UsersModel = new UsersModel();
			 $uids = $UsersModel->where(" mobile LIKE '%".$search['keyword']."%' OR user_name LIKE '%".$search['keyword']."%' OR nick_name LIKE '%".$search['keyword']."%' OR mobile LIKE '%".$search['keyword']."%'")->column('user_id');
			 $uids[] = -1;//增加这个为了以上查询为空时，限制本次主查询失效			 
			 $where[] = ['user_id','in',$uids];
		}
        $data = $this->getPageList($this->Model,$where);
		$this->assign("search", $search);	
		$this->assign("userRechargeType", $this->userRechargeType);
		$this->assign("payment", $this->payList);		
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
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
		$userRechargeType = $this->getDict('UserRechargeType');
		$data['status_name'] = $userRechargeType[$data['status']]['name'];
		$this->assign("payList", (new PaymentModel)->getRows(false,'pay_code'));
		if ($data['pay_code'] == 'offline'){
			$data['imgs'] = explode(',',$data['imgs']);
		}	
		return $data;
	}
	
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		$operating = input('operating','','trim');
		if ($operating == 'refuse'){			
			$data['status'] = 1;			
		}elseif ($operating == 'arrival'){			
			$data['status'] = 9;			
		}else{
			return $this->error('非法操作.');
		}
		$data['admin_id'] = AUID;
		$data['check_time'] = time();
		Db::startTrans();//启动事务
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data){
		if ($data['status'] == 9){
			$info = $this->Model->find($data['order_id']);
			$AccountLogModel = new AccountLogModel();
			$changedata['change_desc'] = '充值到帐';
			$changedata['change_type'] = 6;
			$changedata['by_id'] = $info['order_id'];
			$changedata['balance_money'] = $info['order_amount'];
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
