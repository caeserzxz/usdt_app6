<?php

namespace app\member\controller\api;

use app\ApiController;

use app\member\model\UsersModel;
use app\member\model\WithdrawModel;
use app\member\model\AccountLogModel;
use app\distribution\model\DividendModel;
use app\shop\model\OrderModel;
use app\shop\model\BonusModel;



/*------------------------------------------------------ */
//-- 会员相关API
/*------------------------------------------------------ */

class Users extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new UsersModel();
    }
    /*------------------------------------------------------ */
    //-- 获取登陆会员信息
    /*------------------------------------------------------ */
    public function getInfo()
    {
        $return['info'] = $this->userInfo;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 用户登陆
    /*------------------------------------------------------ */
    public function login()
    {
        $this->checkPostLimit('login');//验证请求次数
        $this->checkCode('login',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->login(input());
        if ($res < 1) return $this->error($res);
        return $this->success('登陆成功.');
    }

    /*------------------------------------------------------ */
    //-- 注册用户
    /*------------------------------------------------------ */
    public function register()
    {
        $this->checkCode('register',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->register(input());
        if ($res !== true) return $this->error($res);
        return $this->success('注册成功.');
    }
	/*------------------------------------------------------ */
    //-- 找回用户密码
    /*------------------------------------------------------ */
    public function forgetPwd()
    {
        $this->checkCode('forget_password',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->forgetPwd(input(),$this);
        if ($res !== true) return $this->error($res);		
        return $this->success('密码已重置，请用新密码登陆.');
    }
	/*------------------------------------------------------ */
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPwd()
    {
		$this->checkLogin();//验证登陆
        $res = $this->Model->editPwd(input(),$this);
        if ($res !== true) return $this->error($res);		
        return $this->success('密码已重置，请用新密码登陆.');
    }
    /*------------------------------------------------------ */
    //-- 获取会员中心首页所需数据
    /*------------------------------------------------------ */
    public function getCenterInfo()
    {
        $this->checkLogin();//验证登陆
        $OrderModel = new OrderModel();
        $return['orderStats'] =  $OrderModel->userOrderStats($this->userInfo['user_id']);
        $return['userInfo'] = $this->userInfo;
        $BonusModel = new BonusModel();
        $bonus = $BonusModel->getListByUser();
        $return['unusedNum'] = $bonus['unusedNum'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
	/*------------------------------------------------------ */
    //-- 获取会员帐号数据
    /*------------------------------------------------------ */
    public function getAccount()
    {
        $this->checkLogin();//验证登陆
        $return['account'] = $this->userInfo['account'];
		//计算提现中金额，即为冻结金额
		$WithdrawModel = new WithdrawModel();
		$where[] = ['user_id','=',$this->userInfo['user_id']];
		$where[] = ['status','<',2];
		$return['frozen_amount'] = $WithdrawModel->where($where)->sum('amount');  
		//end
		$DividendModel = new DividendModel();
		//今天收益		
		unset($where);
		$where[] = ['dividend_uid','=',$this->userInfo['user_id']];
		$where[] = ['status','<=',9];
		$where[] = ['add_time','>=',strtotime("today")];
		$return['today_income'] = $DividendModel->where($where)->sum('dividend_amount'); 
		//end
		//本月收益
		unset($where);
		$where[] = ['dividend_uid','=',$this->userInfo['user_id']];
		$where[] = ['status','<=',9];
		$where[] = ['add_time','>',strtotime(date('Y-m-01', strtotime('-1 month')))];
		$return['month_income'] = $DividendModel->where($where)->sum('dividend_amount'); 
		//end
		$return['withdraw_status'] = settings('withdraw_status');//获取是否开启提现
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
	/*------------------------------------------------------ */
    //-- 获取会员帐户变动日志
    /*------------------------------------------------------ */
    public function getAccountLog()
    {
		$type = input('type','order','trim');
		$time = input('time','','trim');
		if (empty($time)){
			$time = date('Y年m月');			
		}
		$return['time'] = $time;
		$_time = strtotime(str_replace(array('年','月'),array('-',''),$time));
		$return['code'] = 1;
		$AccountLogModel = new AccountLogModel();
		$where[] = ['user_id','=',$this->userInfo['user_id']];
		switch($type){
			case 'order'://订单相关
				$where[] = ['change_type','=',3];
			break;
			case 'brokerage'://佣金相关
				$where[] = ['change_type','=',4];
			break;
			case 'withdraw'://提现相关
				$where[] = ['change_type','=',5];
			break;
			case 'integral'://积分相关
				$where[] = ['use_integral','<>',0];
			break;
			default:
				return $this->error('类型错误.');
			break;			
		}
		$where[] = ['change_time','between',array($_time,strtotime(date('Y-m-t',$_time))+86399)];
		$rows = $AccountLogModel->where($where)->order('change_time DESC')->select();
		foreach ($rows as $key=>$row){			
			if ($row['balance_money'] != 0){
				if ( $row['balance_money'] > 0){
					$return['expend'] += $row['balance_money'];
					$row['value'] = '+'.$row['balance_money'];
				}else{
					$return['income'] += $row['balance_money'];
					$row['value'] = $row['balance_money'];
				}
			}elseif ($row['use_integral'] != 0){
				if ( $row['use_integral'] > 0){
					$return['expend'] += $row['use_integral'];
					$row['value'] = '+'.$row['use_integral'];
				}else{
					$return['income'] += $row['use_integral'];
					$row['value'] = $row['use_integral'];
				}
			}else{
				continue;
			}
			$row['_time'] = timeTran($row['change_time']);
			$return['list'][] = $row;		
		}
        return $this->ajaxReturn($return);
	}
}
