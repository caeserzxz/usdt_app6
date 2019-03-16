<?php
/*------------------------------------------------------ */
//-- 支付相关
/*------------------------------------------------------ */

namespace app\shop\controller;

use app\ClientbaseController;
use function Couchbase\defaultEncoder;
use think\Db;
use app\member\model\RechargeLogModel;
use app\shop\model\OrderModel;
use app\mainadmin\model\PaymentModel;
use app\member\model\AccountLogModel;

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
        if ($this->pay_code != 'balance') {
            define('SITE_URL',config('config.host_path'));
            // 导入具体的支付类文件
            $code = str_replace('/', '\\', "/payment/{$this->pay_code}/{$this->pay_code}");

            $this->payment = new $code();
        }

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
            $this->error('此订单，已完成支付!', url('shop/flow/done', ['order_id' => $order_id]));
        }
        if ($order['order_status'] == 2) {
            $this->error('此订单，已取消不能执行支付!', url('shop/flow/done', ['order_id' => $order_id]));
        }
        $payment = (new PaymentModel)->where('pay_code', $this->pay_code)->find();

        if ($this->pay_code == 'balance') {//如果使用余额，判断用户余额是否足够
            if ($order['order_amount'] > $this->userInfo['account']['balance_money']) {
                return $this->error('余额不足，请使用其它支付方式.', url('shop/flow/done', ['order_id' => $order_id]));
            }
            //余额完成支付
            $upArr['order_id'] = $order_id;
            $upArr['pay_code'] = $this->pay_code;
            $upArr['pay_id'] = $payment['pay_id'];
            $upArr['pay_name'] = $payment['pay_name'];
            $res = $OrderModel->updatePay($upArr, '余额支付成功.');
            if ($res !== true) {
                return $this->error($res);
            }
            return $this->redirect('shop/flow/done', ['order_id' => $order_id]);

        }


        $OrderModel->where("order_id", $order_id)->update(['is_pay' => $payment['is_pay'], 'pay_code' => $this->pay_code, 'pay_id' => $payment['pay_id'], 'pay_name' => $payment['pay_name']]);

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
                $this->error($return['msg'], url('shop/flow/done', ['order_id' => $order_id]));
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
        $order_id = input('order_id/d'); //订单id
        $RechargeLogModel = new RechargeLogModel();

        $order = $RechargeLogModel->where("order_id", $order_id)->find();
        if (empty($order)) {
            return $this->error('提交失败,参数有误!');
        }
        if ($order['status'] != 0) {
            return $this->error('此充值订单，状态非待支付，不能完成操作.');
        }

        $pay_radio = $_REQUEST['pay_radio'];
        $config_value = parseUrlParam($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
        $wxInfo = session('wxInfo');
        //微信JS支付
        if (($this->pay_code == 'weixin' || $this->pay_code == 'weixinH5') && $wxInfo['wx_openid'] && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $code_str = $this->payment->getJSAPI($order);
            exit($code_str);
        } else {
            $code_str = $this->payment->get_code($order, $config_value);
        }


        $this->assign('code_str', $code_str);
        $this->assign('order_id', $log_id);
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
