<?php
namespace app\member\controller\api;
use think\Db;
use app\ApiController;
use app\member\model\WithdrawAccountModel;
use app\member\model\AccountLogModel;
use app\member\model\WithdrawModel;
/*------------------------------------------------------ */
//-- 提现相关API
/*------------------------------------------------------ */

class Withdraw extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
		$this->checkLogin();//验证登陆
        $this->Model = new WithdrawAccountModel();
    }
    /*------------------------------------------------------ */
    //-- 获取帐户列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $return['list'] = $this->Model->getRows();
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
   
    /*------------------------------------------------------ */
    //-- 添加银行卡
    /*------------------------------------------------------ */
    public function addBank()
    {		
		$inArr['bank_name'] = input('bank_name','','trim');
		$inArr['bank_code'] = input('bank_code','','trim');
		$inArr['bank_cardholder'] = input('bank_cardholder','','trim');
		$inArr['bank_card_number'] = input('bank_card_number','','trim');
		$inArr['bank_cardholder_phone'] = input('bank_cardholder_phone','','trim');
		$inArr['bank_location_outlet'] = input('bank_location_outlet','','trim');
		$inArr['bank_branch_name'] = input('bank_branch_name','','trim');
		
		if (empty($inArr['bank_name'])){
			return $this->error('请选择银行.');
		}
		if (empty($inArr['bank_cardholder'])){
			return $this->error('请输入持卡人姓名.');
		}
		if (empty($inArr['bank_card_number'])){
			return $this->error('请输入卡号.');
		}
		if ($this->checkBankCard($inArr['bank_card_number']) == false){
			return $this->error('银行卡号不正确，请核实.');
		}
		if (empty($inArr['bank_cardholder_phone'])){
			return $this->error('请输入持卡人电话.');
		}
		if (checkMobile($inArr['bank_cardholder_phone']) == false){
			return $this->error('输入持卡人电话格式不正确.'.$inArr['bank_cardholder_phone']);
		}
		if (empty($inArr['bank_location_outlet'])){
			return $this->error('请输入网点所在地.');
		}
		if (empty($inArr['bank_branch_name'])){
			return $this->error('请输入支行名称.');
		}
     	$inArr['user_id'] = $this->userInfo['user_id'];
		$inArr['type'] = 'bank';
		$inArr['add_time'] = time();
		$res = $this->Model->save($inArr);
		if ($res < 1){
			return $this->error('添加银行卡失败.');
		}
		$this->Model->cleanMemcache();
		return $this->success('添加银行卡成功.');
    }
   
	/*------------------------------------------------------ */
    //-- 验证银行卡是否有效
    /*------------------------------------------------------ */
	public function checkBankCard($card_number){
        $arr_no = str_split($card_number);
        $last_n = $arr_no[count($arr_no)-1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n){
            if($i%2==0){
                $ix = $n*2;
                if($ix>=10){
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                }else{
                    $total += $ix;
                }
            }else{
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if($x == $last_n){
            return true;
        }else{
            return false;
        }
    }
	/*------------------------------------------------------ */
    //-- 删除用户提现帐户
    /*------------------------------------------------------ */
	public function delAccount(){
		$account_id = input('account_id',0,'intval');
		if ($account_id < 1) return $this->error('传参失败.');
		$res = $this->Model->where('account_id',$account_id)->update(['is_del'=>1]);
		if ($res < 1) return $this->error('删除失败.');
		$this->Model->cleanMemcache($this->userInfo['user_id']);
		return $this->success('删除成功.');
	}
	/*------------------------------------------------------ */
    //-- 添加支付宝
    /*------------------------------------------------------ */
    public function addAlipay(){
       $inArr['alipay_user_name'] = input('alipay_user_name','','trim');
	   $inArr['alipay_account'] = input('alipay_account','','trim');
	   if (empty($inArr['alipay_user_name'])){
			return $this->error('请输入账户姓名.');
		}
		if (empty($inArr['alipay_account'])){
			return $this->error('请输入支付宝账号.');
		}
		$inArr['user_id'] = $this->userInfo['user_id'];
		$inArr['type'] = 'alipay';
		$inArr['add_time'] = time();
		$res = $this->Model->save($inArr);
		if ($res < 1){
			return $this->error('添加支付宝失败.');
		}
		$this->Model->cleanMemcache($this->userInfo['user_id']);
		return $this->success('添加支付宝成功.');
    }
	/*------------------------------------------------------ */
    //-- 验证是否满足提现，并返回手续费
    /*------------------------------------------------------ */
    public function checkWithdraw($amount = 0,$isreturn = false)
    {
		if ($amount <= 0){
			$amount = input('amount') * 1;
		}
		$settings = settings();
		if ($settings['withdraw_status'] == 0){
			return $this->error('提现未开启，暂不能操作.');
		}
		if ($amount < $settings['withdraw_min_money']){
			return $this->error('每次提现不能低于￥'.$settings['withdraw_min_money']);
		}
		if ($amount > $settings['withdraw_max_money']){
			return $this->error('每次提现不能高于￥'.$settings['withdraw_max_money']);
		}
		$withdraw_fee = $amount / 100 * $settings['withdraw_fee'];
		if ($settings['withdraw_fee_min'] > $withdraw_fee){
			$withdraw_fee = $settings['withdraw_fee_min'];
		}elseif ($settings['withdraw_fee_max'] > 0 && $withdraw_fee > $settings['withdraw_fee_max']){
			$withdraw_fee = $settings['withdraw_fee_max'];
		}
		if ($this->userInfo['account']['balance_money'] < $amount + $withdraw_fee){
			return $this->error('帐户余额不足.');
		}
		if ($isreturn == true) return $withdraw_fee;
		$return['withdraw_fee'] = $withdraw_fee;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
	}
	/*------------------------------------------------------ */
    //-- 提交提现
    /*------------------------------------------------------ */
    public function postWithdraw()
    {
		$withdraw_status = settings('withdraw_status');
		if ($withdraw_status < 1){
			return $this->error('暂不开放提现.');
		}
		$inArr['amount'] = input('amount') * 1;
		if ($inArr['amount'] <= 0){
            return $this->error('请输入提现金额.');
        }
        $pay_password = input('pay_password') * 1;
        $pay_password = f_hash($pay_password.$this->userInfo['user_id']);
        if ($pay_password != $this->userInfo['pay_password']){
            return $this->error('支付密码错误，请核实.');
        }
		$inArr['withdraw_fee'] = $this->checkWithdraw($inArr['amount'],true);
		$inArr['account_id'] = input('account_id') * 1;
		if ($inArr['account_id'] < 1){
			return $this->error('选择提现方式.');
		}
		$WithdrawModel = new WithdrawModel();
		$withdraw_num = settings('withdraw_num');
		//判断提现限制
		if ($withdraw_num > 0){
			$where[] = ['user_id','=',$this->userInfo['user_id']];
			$where[] = ['status','<',3];
			$where[] = ['add_time','>',strtotime(date('Y-m-d'))];
			$wnum = $WithdrawModel->where($where)->count('log_id');
			if ($wnum >= $withdraw_num){
				return $this->error('每天最多只能提现'.$withdraw_num.'次.');
			}
		}
        $withdraw_money =  $inArr['amount'] + $inArr['withdraw_fee'];
		if ($this->userInfo['account']['balance_money'] < $withdraw_money){
            return $this->error('余额不足，请核实提现金现.');
        }
		$inArr['user_id'] = $this->userInfo['user_id'];
		$inArr['add_time'] = time();
		Db::startTrans();//启动事务
		
		$res = $WithdrawModel->save($inArr);
		if ($res < 1){
			Db::rollback();// 回滚事务
			return $this->error('提现失败.');
		}
		$AccountLogModel = new AccountLogModel();
	    $changedata['change_desc'] = '提现扣除';
		$changedata['change_type'] = 5;
		$changedata['by_id'] = $WithdrawModel->log_id;
		$changedata['balance_money'] = $withdraw_money * -1;
		$res = $AccountLogModel->change($changedata, $this->userInfo['user_id'], false);
		if ($res !== true) {
			Db::rollback();// 回滚事务
			return $this->error('未知错误，提现扣除用户余额失败.');
		}
		Db::commit();// 提交事务
        $WeiXinMsgTplModel = new \app\weixin\model\WeiXinMsgTplModel();
        $WeiXinUsersModel = new \app\weixin\model\WeiXinUsersModel();
        $inArr['send_scene']  = 'withdraw_apply_msg';//提现申请通知
        $wxInfo = $WeiXinUsersModel->where('user_id', $inArr['user_id'])->field('wx_openid,wx_nickname')->find();
        $inArr['openid'] = $wxInfo['wx_openid'];
        $inArr['send_nick_name'] = $wxInfo['wx_nickname'];
        $WeiXinMsgTplModel->send($inArr);//模板消息通知

		return $this->success('提现成功.');
	}
}
