<?php
/*------------------------------------------------------ */
//-- 支付相关
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;
use think\Db;
use app\member\model\RechargeLogModel;
use app\shop\model\OrderModel;
use app\mainadmin\model\PaymentModel;

class Payment extends ClientbaseController
{
    public $payment; //  具体的支付类
    public $pay_code; //  具体的支付code

    /**
     * 析构流函数
     */
    public function __construct()
    {
        parent::__construct();

        // 获取支付类型
        $this->pay_code = input('pay_code');
        unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误

        // 获取通知的数据
        if (empty($this->pay_code)) {
            exit('pay_code 不能为空');
        }

        // 导入具体的支付类文件
		$code = str_replace('/','\\',"/payment/{$this->pay_code}/{$this->pay_code}");
       
        $this->payment = new $code();
    }

    /**
     *  提交支付方式
     */
    public function getCode()
    {
        header("Content-type:text/html;charset=utf-8");
        

        // 修改订单的支付方式
        $order_id = input('order_id/d'); // 订单id
		$OrderModel = new OrderModel();
        $order = $OrderModel->where("order_id", $order_id)->find();
        if ($order['pay_status'] == 1) {
            $this->error('此订单，已完成支付!');
        }
		

        $payment = (new PaymentModel)->where('pay_code', $this->pay_code)->find();
        $OrderModel->where("order_id", $order_id)->update(['pay_code'=>$this->pay_code,'pay_id'=>$payment['pay_id'],'pay_name'=>$payment['pay_name']]);

        // 订单支付提交
        $config = parseUrlParam($this->pay_code); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
        $config['body'] = $OrderModel->getPayBody($order_id);
		$wxInfo = session('wxInfo');
        if ($this->pay_code == 'weixin' && $wxInfo['wx_openid'] && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            //微信JS支付
            $code_str = $this->payment->getJSAPI($order);
            exit($code_str);
        } elseif ($this->pay_code == 'weixinH5') {
            //微信H5支付
            $return = $this->payment->get_code($order, $config);
            if ($return['status'] != 1) {
                $this->error($return['msg']);
            }
            $this->assign('deeplink', $return['result']);
        } else {
            //其他支付（支付宝、银联...）
            $code_str = $this->payment->get_code($order, $config);
        }

        $this->assign('code_str', $code_str);
        $this->assign('order_id', $order_id);
        return $this->fetch('payment');  // 分跳转 和不 跳转
    }

    public function getPay()
    {
        //手机端在线充值
        //C('TOKEN_ON',false); // 关闭 TOKEN_ON 
        header("Content-type:text/html;charset=utf-8");
        $log_id = input('log_id/d'); //订单id
      	$RechargeLogModel = new RechargeLogModel();
		if ($order_id < 1){
			$data['amount'] = input('amount') * 1;
			$data['user_id'] = $this->userInfo['user_id'];
			$data['order_sn'] = 'recharge'.date('Ymdhis').rand(100,999);
			$data['add_time'] = time();
			$order_id = $RechargeLogModel->save($data);
		}
        $order = $RechargeLogModel->where("log_id", $log_id)->find();
		if (empty($order)){
			return $this->error('提交失败,参数有误!');	
		}
		
        if ($order['pay_status'] == 0) {
                $order['order_amount'] = $order['amount'];
                $pay_radio = $_REQUEST['pay_radio'];
                $config_value = parseUrlParam($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
                $payment = (new PaymentModel)->where('pay_code', $this->pay_code)->find();
                $RechargeLogModel->where("order_id", $order_id)->save(array('pay_code' => $this->pay_code, 'pay_name' => $payment['pay_name'],'pay_id' => $payment['pay_id']));
				$wxInfo = session('wxInfo');
                //微信JS支付
                if ($this->pay_code == 'weixin' && $wxInfo['wx_openid'] && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
                    $code_str = $this->payment->getJSAPI($order);
                    exit($code_str);
                } else {
                    $code_str = $this->payment->get_code($order, $config_value);
                }
		} else {
			return $this->error('此充值订单，已完成支付!');
		}
       
        $this->assign('code_str', $code_str);
        $this->assign('order_id', $order_id);
        return $this->fetch('recharge'); //分跳转 和不 跳转
    }

    // 服务器点对点 // http://www.tp-shop.cn/index.php/Home/Payment/notifyUrl
    public function notifyUrl()
    {
        $this->payment->response();
        exit();
    }

    // 页面跳转 // http://www.tp-shop.cn/index.php/Home/Payment/returnUrl
    public function returnUrl()
    {
        $result = $this->payment->respond2(); // $result['order_sn'] = '201512241425288593';
        if (stripos($result['order_sn'], 'recharge') !== false) {
			$RechargeLogModel = new RechargeLogModel();
            $order = $RechargeLogModel->where("order_sn", $result['order_sn'])->find();
            $this->assign('order', $order);
            if ($result['status'] == 9)
                return $this->fetch('recharge_success');
            else
                return $this->fetch('recharge_error');
        }
        $order = (new OrderModel)->where("order_sn", $result['order_sn'])->find();
        $this->assign('order', $order);
        if ($result['status'] == 1)
            return $this->fetch('success');
        else
            return $this->fetch('error');
    }
}
