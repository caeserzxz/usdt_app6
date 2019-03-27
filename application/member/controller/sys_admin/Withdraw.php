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
        $is_export =  input('is_export',0,'intval');
        if ($is_export > 0) {
            return $this->export($where);
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
        //模板消息通知
        $WeiXinMsgTplModel = new \app\weixin\model\WeiXinMsgTplModel();
        $WeiXinUsersModel = new \app\weixin\model\WeiXinUsersModel();
        if ($data['status'] == 1) {
            $data['send_scene'] = 'withdraw_fail_msg';//提现拒绝通知
        }elseif ($data['status'] == 9) {
            $data['send_scene'] = 'withdraw_ok_msg';//提现打款通知
        }
        $wxInfo = $WeiXinUsersModel->where('user_id', $data['user_id'])->field('wx_openid,wx_nickname')->find();
        $data['openid'] = $wxInfo['wx_openid'];
        $data['send_nick_name'] = $wxInfo['wx_nickname'];
        $WeiXinMsgTplModel->send($inArr);//模板消息通知
		return $this->success('操作成功.',url('index'));
	}
    /*------------------------------------------------------ */
    //-- 导出
    /*------------------------------------------------------ */
    public function export(&$where)
    {
        $export_arr['会员ID'] = 'user_id';
        $export_arr['会员名称'] = 'user_name';
        $export_arr['申请日期'] = 'add_time';
        $export_arr['提现金额'] = 'amount';
        $export_arr['处理状态'] = 'status';
        $export_arr['提现方式'] = 'type';
        $export_arr['支付宝账户姓名'] = 'alipay_account';
        $export_arr['支付宝帐号'] = 'alipay_user_name';

        $export_arr['银行'] = 'bank_name';
        $export_arr['持卡人'] = 'bank_cardholder';
        $export_arr['卡号'] = 'bank_card_number';
        $export_arr['持卡人电话'] = 'bank_cardholder_phone';
        $export_arr['网点所在地'] = 'bank_location_outlet';
        $export_arr['支行名称'] = 'bank_branch_name';

        $page = 0;
        $page_size = 500;
        $page_count = 100;
        $title = join("\t", array_keys($export_arr)) . "\t";

        $data = '';
        do {
            $rows = $this->Model->alias('w')->join("users_withdraw_account uwa", 'w.account_id=uwa.account_id','left')->where($where)->limit($page * $page_size, $page_size)->select();

            if (empty($rows)) break;
            foreach ($rows as $row) {
                foreach ($export_arr as $val) {
                    if (strstr($val, '_time')) {
                        $data .= dateTpl($row[$val]) . "\t";
                    } elseif ($val == 'user_name') {
                        $data .= userInfo($row['user_id']). "\t";
                    } elseif ($val == 'type') {
                        $data .= ($row['type']=='alipay'?'支付宝':'银行卡'). "\t";
                    } elseif ($val == 'bank_card_number') {
                        $data .= "'".$row['bank_card_number']. "\t";
                    } else {
                        $data .= str_replace(array("\r\n", "\n", "\r"), '', strip_tags($row[$val])) . "\t";
                    }
                }
                $data .= "\n";
            }
            $page++;
        } while ($page <= $page_count);

        $filename = '提现资料_' . date("YmdHis") . '.xls';
        $filename = iconv('utf-8', 'GBK//IGNORE', $filename);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        echo iconv('utf-8', 'GBK//IGNORE', $title . "\n" . $data) . "\t";
        exit;
    }
}
