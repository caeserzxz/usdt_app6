<?php
namespace app\member\controller\sys_admin;
use app\AdminController;

use app\member\model\AccountLogModel;
use app\member\model\UsersModel;

/**
 * 会员帐户变动明细
 */
class AccountLog extends AdminController
{
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new AccountLogModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->getList(true);
		$this->assign("account",(new UsersModel)->getAccount($this->search['user_id']));
        return $this->fetch();
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {		
		$this->search['user_id'] = input('user_id/d');	
		$this->search['account_type'] = input('account_type/s');
		$this->search['change_type'] = input('change_type/d');
		$this->assign("search", $this->search);
		$reportrange = input('reportrange');
		$where = [];
		if ($this->search['change_type'] > 0 ){
			$where[] = ['change_type','=',$this->search['change_type']];	
		}
		switch ($this->search['account_type']) {
			case 'balance_money':
				$where[] = ['balance_money','<>',0];	
				break;
			case 'use_integral':
				$where[] = ['use_integral','<>',0];	
				break;
			case 'total_dividend':
				$where[] = ['total_dividend','<>',0];	
				break;
			case 'total_integral':
				$where[] = ['total_integral','<>',0];	
				break;
		}		
		
		if (empty($reportrange) == false){
			$dtime = explode('-',$reportrange);
			$where[] = ['change_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}else{
			$where[] = ['change_time','between',[strtotime(date('Y/m/01',strtotime("-1 months"))),time()]];
		}
		if (0 < $this->search['user_id'] ){
			$where[] = ['user_id','=',$this->search['user_id'] ];
		}
		$this->sqlOrder = 'change_time DESC';
        $data = $this->getPageList($this->Model,$where);			
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
	//-- 调节会员帐号
	/*------------------------------------------------------ */ 
	public function manage(){
		$user_id = input('user_id',0,'intval');

		if ($this->request->isPost()){
			
			$total_dividend_type = input('total_dividend_type','add','trim');
			$total_dividend = input('total_dividend',0,'float');
			$name = '';
			$number = '';
			if ($total_dividend > 0){
                $name = '历史总佣金';
				 $data['total_dividend'] = $number = $total_dividend_type == 'add' ? $total_dividend : $total_dividend * -1;
			}
			$balance_money_type = input('balance_money_type','add','trim');
			$balance_money = input('balance_money',0,'float');
			if ($balance_money > 0){
                $name = '余额';
				$data['balance_money'] = $number = $balance_money_type == 'add' ? $balance_money : $balance_money * -1;
			}
            $bean_value_type = input('bean_value_type','add','trim');
            $bean_value = input('bean_value',0,'float');
            if ($bean_value > 0){
                $name = '旅游币';
                $data['bean_value'] = $number = $bean_value_type == 'add' ? $bean_value : $bean_value * -1;
            }
			$total_integral_type = input('total_integral_type','add','trim');
			$total_integral = input('total_integral',0,'intval');
			if ($total_integral > 0){
                $name = '历史总积分';
				 $data['total_integral'] = $number = $total_integral_type == 'add' ? $total_integral : $total_integral * -1;
			}
			$use_integral_type = input('use_integral_type','add','trim');
			$use_integral = input('use_integral',0,'intval');
			if ($use_integral > 0){
			    $name = '可用积分';
				 $data['use_integral'] = $number = $use_integral_type == 'add' ? $use_integral : $use_integral * -1;
			}
			if (empty($data)) return $this->error('请核实是否有输入正确的更改值？');
			$data['user_id'] = $user_id;
			$data['change_desc'] = input('change_desc','','trim');
			$data['change_type'] = 1;
			$data['by_id'] = AUID;
			$res = $this->Model->change($data,$user_id);
			if ($res < 1) return $this->error();
            $this->_log($user_id, '调节会员账户：' . $name. '-(' . $number.')','member');
			return $this->success('操作成功','reload');
		}
        $account = model(UsersModel)->getAccount($user_id,false);
		$this->assign("account", $account);
		return $this->fetch();
	}
}
