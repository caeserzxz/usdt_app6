<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
use app\member\model\RechargeLogModel;
use app\shop\model\OrderModel;
use app\distribution\model\RoleOrderModel;

use think\facade\Env;
require_once dirname(dirname(__FILE__))."/lib/WxPay.Api.php";
require_once dirname(dirname(__FILE__))."/lib/WxPay.Notify.php";
require_once 'log.php';


$file = Env::get('runtime_path')."/wxpay/";
!is_dir($file) && mkdir($file, 0755, true);
$file .= date('Y-m-d').'.log';
//初始化日志
$logHandler= new CLogFileHandler($file);

$log = Log::Init($logHandler, 15);
class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
        Log::DEBUG("call back:" . json_encode($data));

        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            //Log::DEBUG("call back:" . $msg);
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            //Log::DEBUG("call back:" . $msg);
            return false;
        }

        $appid = $data['appid']; //公众账号ID
        $order_sn = $data['out_trade_no']; //商户系统的订单号，与请求一致。
        $attach = $data['attach']; //商家数据包，原样返回
        //file_put_contents('/web/tpshop2/c.html',print_r($data,true),FILE_APPEND);
        //20160316 JSAPI支付情况 去掉订单号后面的十位时间戳


        //购买身份商品
        if (stripos($order_sn, 'role') !== false) {
            if (strlen($order_sn) > 17) {
                $order_sn = substr($order_sn, 0, 17);
            }
            //Log::DEBUG("充值验证:" . $order_sn);
            $RoleOrderModel = new RoleOrderModel();
            $orderInfo = $RoleOrderModel->where('order_sn',"$order_sn")->field('order_id,order_amount,user_id,pay_status')->find();
            if (empty($orderInfo)) return false;
            $orderInfo = $orderInfo->toArray();
            if ($orderInfo['pay_status'] == 1) return true;
            if ((string)($orderInfo['order_amount'] * 100) != (string)$data['total_fee']) {
                return false; //验证失败
            }
            $orderInfo['transaction_id'] = $data["transaction_id"];
            return $RoleOrderModel->updatePay($orderInfo);// 修改订单支付状态
        } elseif (stripos($order_sn, 'recharge') !== false) {//用户在线充值
            if (strlen($order_sn) > 20) {
                $order_sn = substr($order_sn, 0, 20);
            }
            //Log::DEBUG("充值验证:" . $order_sn);
            $RechargeLogModel = new RechargeLogModel();
            $orderInfo = $RechargeLogModel->where('order_sn',"$order_sn")->field('log_id,order_amount,user_id,status')->find();
            if (empty($orderInfo)) return false;
            $orderInfo = $orderInfo->toArray();
            if ($orderInfo['status'] == 9) return true;
            if ((string)($orderInfo['order_amount'] * 100) != (string)$data['total_fee']) {
                return false; //验证失败
            }
            $orderInfo['transaction_id'] = $data["transaction_id"];
            return $RechargeLogModel->updatePay($orderInfo);// 修改订单支付状态
        } else {
            if (strlen($order_sn) > 13) {
                $order_sn = substr($order_sn, 0, 13);
            }
            //Log::DEBUG("订单验证:" . $order_sn);
            $OrderModel = new OrderModel();
            $orderInfo = $OrderModel->where('order_sn',"$order_sn")->field('order_id,order_amount,pay_status')->find();
            if (empty($orderInfo)){
                Log::DEBUG("call back:本地没有找到订单." );
                return false;
            }
            //Log::DEBUG("订单数据:" . json_encode($orderInfo));
            if ($orderInfo['pay_status'] == 1) return true;
            if ((string)($orderInfo['order_amount'] * 100) != (string)$data['total_fee']) {
                Log::DEBUG("call back:金额验证失败.");
                return false; //验证失败
            }
            //Log::DEBUG("call back:开始更新订单状态.");
            return $OrderModel->updatePay(array('order_id'=>$orderInfo['order_id'],'money_paid'=>$orderInfo['order_amount'],'transaction_id'=>$data["transaction_id"]),'微信支付成功，流水号：'.$data["transaction_id"]);// 修改订单支付状态
        }



       // update_pay_status($order_sn, array('transaction_id' => $data["transaction_id"])); // 修改订单支付状态
		
		return true;
	}
}

//Log::DEBUG("begin notify");
//$notify = new PayNotifyCallBack();
//$notify->Handle(false);
