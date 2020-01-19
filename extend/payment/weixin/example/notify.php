<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
use app\shop\model\UpdatePayModel;

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

        //$appid = $data['appid']; //公众账号ID
        //$order_sn = $data['out_trade_no']; //商户系统的订单号，与请求一致。
        //$attach = $data['attach']; //商家数据包，原样返回
        //file_put_contents('/web/tpshop2/c.html',print_r($data,true),FILE_APPEND);
        //20160316 JSAPI支付情况 去掉订单号后面的十位时间戳

        $data['order_sn']= $data['out_trade_no']; //商户系统的订单号，与请求一致。
        return (new UpdatePayModel)->update($data);

        // update_pay_status($order_sn, array('transaction_id' => $data["transaction_id"])); // 修改订单支付状态

        return true;
    }
}

//Log::DEBUG("begin notify");
//$notify = new PayNotifyCallBack();
//$notify->Handle(false);
