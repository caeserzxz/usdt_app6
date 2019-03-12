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
		$this->checkLogin();//验证登陆
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
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPwd()
    {
        $res = $this->Model->editPwd(input(),$this);
        if ($res !== true) return $this->error($res);		
        return $this->success('密码已重置，请用新密码登陆.');
    }
    /*------------------------------------------------------ */
    //-- 获取会员中心首页所需数据
    /*------------------------------------------------------ */
    public function getCenterInfo()
    {        
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
    //-- 获取分享二维码
    /*------------------------------------------------------ */
    public function myCode(){
		$file_path = config('config._upload_').'qrcode/'. substr($this->userInfo['user_id'], -1) . '/';
		$file = $file_path.$this->userInfo['token'].'.png';
		if (file_exists($file) == false){
			include EXTEND_PATH . 'phpqrcode/phpqrcode.php';//引入PHP QR库文件			
        	$QRcode = new \phpqrcode\QRcode();
			$value = config('config.host_path').'/?share_token='.$this->userInfo['token'];
			makeDir($file_path);
			$png = $QRcode::png($value, $file, "L", 10,1,2,true);		
		}
		$return['file'] = config('config.host_path').'/'.trim($file,'.');
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
	/*------------------------------------------------------ */
    //-- 获取分享商品二维码
    /*------------------------------------------------------ */
    public function goodsCode(){
		$goods_id = input('goods_id',0,'intval');
		$file_path = config('config._upload_').'goods_qrcode/'. $goods_id . '/';
		$file = $file_path.$this->userInfo['token'].'.png';
		if (file_exists($file) == false){
			include EXTEND_PATH . 'phpqrcode/phpqrcode.php';//引入PHP QR库文件			
        	$QRcode = new \phpqrcode\QRcode();
			$value = config('config.host_path').url('shop/goods/info',['id'=>$goods_id,'share_token'=>$this->userInfo['token']]);
			makeDir($file_path);
			$png = $QRcode::png($value, $file, "L", 10,1,2,true);		
		}
		$return['file'] = config('config.host_path').'/'.trim($file,'.');
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
	/*------------------------------------------------------ */
    //-- 获取会员帐号数据
    /*------------------------------------------------------ */
    public function getAccount()
    {
        $return['account'] = $this->userInfo['account'];
		//计算提现中金额，即为冻结金额
		$WithdrawModel = new WithdrawModel();
		$where[] = ['user_id','=',$this->userInfo['user_id']];
		$where[] = ['status','=',0];
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
	
	/*------------------------------------------------------ */
    //-- 修改会员信息
    /*------------------------------------------------------ */
    public function editInfo(){
		$imgfile = input('imgfile');
		if (empty($imgfile) == false){
			$file_path = config('config._upload_').'headimg/'.substr($this->userInfo['user_id'], -1) .'/';
			makeDir($file_path);
			$file_name = $file_path.random_str(12).'.jpg';
			file_put_contents($file_name,base64_decode(str_replace('data:image/jpeg;base64,','',$imgfile)));
			$upArr['headimgurl'] = trim($file_name,'.');	
		}
		$upArr['nick_name'] = input('nick_name','','trim');
		if (empty($upArr['nick_name']) == true){
			return $this->error('请填写用户昵称.');	
		}
		$where[] = ['nick_name','=',$upArr['nick_name']];
		$where[] = ['user_id','<>',$this->userInfo['user_id']];	
		$count = $this->Model->where($where)->count('user_id');
		if ($count > 0) return '昵称：'.$upArr['nick_name'].'，已存在.';
		$upArr['signature'] = input('signature','','trim');
		$upArr['sex'] = input('sex','男','trim');
		$upArr['sex'] = $upArr['sex'] == '男' ? 1 : 0;
		$upArr['birthday'] = input('birthday','','trim');
		$upArr['show_mobile'] = input('show_mobile',0,'intval');
		$res = $this->Model->upInfo($this->userInfo['user_id'],$upArr);
		if ($res < 1){
			@unlink($file_name);
			return $this->error('修改用户信息失败，请重试.');	
		}
		 return $this->success('修改成功.');
	}
}
