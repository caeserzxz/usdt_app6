<?php

namespace app\member\controller\api;

use app\ApiController;
use think\Db;

use app\member\model\AccountLogModel;
use app\member\model\RechargeLogModel;
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
    	$inArr['pay_type'] = input('pay_type','','trim');
		$inArr['amount'] = input('amount') * 1;
		if ($inArr['amount'] < 1){
			return $this->error('充值金额不能少于1.');
		}
		if ($inArr['pay_type'] == 'offline'){
			//处理图片
			$imgfile = input('imgfile');
			if (empty($imgfile)){
				return $this->error('线下打款，须上传打款凭证图片.');
				return false;
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
		$inArr['user_id'] = $this->userInfo['user_id'];
		$inArr['add_time'] = time();
		$res = (new RechargeLogModel)->save($inArr);
		if ($res < 1){
			foreach ($imgs as $img){
				@unlink('.'.$img);
			}
			return $this->error('写入数据失败.');	
		}		
        return $this->success('提交成功.');
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
        Db::commit();// 提交事务
        return $this->success('兑换成功.');
    }

	
}
