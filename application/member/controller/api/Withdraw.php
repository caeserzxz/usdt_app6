<?php
namespace app\member\controller\api;
use app\ApiController;
use app\member\model\WithdrawAccountModel;

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
		$id = input('id',0,'intval');
		if ($id < 1) return $this->error('传参失败.');
		$res = $this->Model->where('id',$id)->delete();
		if ($res < 1) return $this->error('删除失败.');
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
		return $this->success('添加支付宝成功.');
    }
}
