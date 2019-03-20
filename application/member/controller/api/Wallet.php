<?php

namespace app\member\controller\api;

use app\ApiController;
use think\Db;

use app\member\model\AccountLogModel;
use app\member\model\RechargeLogModel;
use app\mainadmin\model\PaymentModel;
use app\distribution\model\DividendModel;
/*------------------------------------------------------ */
//-- 会员钱包相关
/*------------------------------------------------------ */

class Wallet extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
		$this->checkLogin();//验证登陆
       
    }
   
    /*------------------------------------------------------ */
    //-- 会员充值
    /*------------------------------------------------------ */
    public function recharge()
    {
    	$inArr['pay_code'] = input('pay_code','','trim');
		$inArr['order_amount'] = input('order_amount') * 1;
		if ($inArr['order_amount'] < 1){
			return $this->error('充值金额不能少于1.');
		}
        $payList = (new PaymentModel)->getRows(true,'pay_code');
		if (empty($payList[$inArr['pay_code']])){
            return $this->error('支付方式不存在.');
        }
		if ($inArr['pay_type'] == 'offline'){
			//处理图片
			$imgfile = input('imgfile');
			if (empty($imgfile)){
				return $this->error('线下打款，须上传打款凭证图片.');
			}
			$imgs = array();
			$file_path = config('config._upload_').'recharge/'.date('Ymd').'/';
			makeDir($file_path);
			foreach ($imgfile as $file){
				$file_name = $file_path.random_str(12).'.jpg';
				file_put_contents($file_name,base64_decode(str_replace('data:image/jpeg;base64,','',$file)));				
				$imgs[] = trim($file_name,'.');								
			}
			$inArr['imgs'] = join(',',$imgs);
		}
        $payment = $payList[$inArr['pay_code']];
        $inArr['order_sn'] = 'recharge'.date('Ymdhis').rand(1000,9999);
		$inArr['pay_id'] = $payment['pay_id'];
        $inArr['pay_name'] = $payment['pay_name'];
		$inArr['user_id'] = $this->userInfo['user_id'];
		$inArr['add_time'] = time();
		$RechargeLogModel = new RechargeLogModel();
		$res = $RechargeLogModel->save($inArr);
		if ($res < 1){
			foreach ($imgs as $img){
				@unlink('.'.$img);
			}
			return $this->error('写入数据失败.');	
		}

        return $this->success('提交成功.','',['order_id'=>$RechargeLogModel->order_id]);
    }
    /*------------------------------------------------------ */
    //-- 旅游豆转换
    /*------------------------------------------------------ */
    public function postChange()
    {
        $inArr['amount'] = input('amount') * 1;
        if ($inArr['amount'] <= 0){
            return $this->errot('请输入兑换数量.');
        }
        if ($this->userInfo['account']['bean_value'] < $inArr['amount']){
            return $this->error('旅游豆不足，请核实旅游豆兑换数量.');
        }
        Db::startTrans();//启动事务
        $AccountLogModel = new AccountLogModel();
        $changedata['change_desc'] = '旅游豆兑换';
        $changedata['change_type'] = 8;
        $changedata['by_id'] = 0;
        $changedata['balance_money'] = $inArr['amount'];
        $changedata['bean_value'] = $inArr['amount'] * -1;
        $res = $AccountLogModel->change($changedata, $this->userInfo['user_id'], false);
        if ($res !== true) {
            Db::rollback();// 回滚事务
            return $this->error('未知错误，兑换失败.');
        }
        $where = [];
        $where[] = ['dividend_uid','=',$this->userInfo['user_id']];
        $where[] = ['status','=',9];
        $where[] = ['is_hide','=',0];
        $where[] = ['dividend_bean','>',0];
        $res = (new DividendModel)->where($where)->update(['is_hide'=>1]);

        Db::commit();// 提交事务
        return $this->success('兑换成功.');
    }

	
}
