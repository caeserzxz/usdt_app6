<?php
/**
 *  支付宝插件

 */

namespace payment\alipayMobile;


use think\facade\Env;
use app\member\model\RechargeLogModel;
use app\shop\model\OrderModel;
use app\mainadmin\model\PaymentModel;
use app\distribution\model\RoleOrderModel;
/**
 * 支付 逻辑定义
 * Class AlipayPayment
 * @package Home\Payment
 */

class alipayMobile 
{       
    public $alipay_config = array();// 支付宝支付配置参数
    
    /**
     * 析构流函数
     */
    public function  __construct() {   
       
        unset($_GET['pay_code']);   // 删除掉 以免被进入签名
        unset($_REQUEST['pay_code']);// 删除掉 以免被进入签名
        
        $payment =  (new PaymentModel)->where('pay_code', 'alipayMobile')->find(); // 找到支付插件的配置
        $config_value = json_decode(urldecode($payment['pay_config']),true);    
		
        $this->alipay_config['alipay_pay_method']= $config_value['alipay_pay_method']; // 1 使用担保交易接口  2 使用即时到帐交易接口s
        $this->alipay_config['partner']       = $config_value['alipay_partner'];//合作身份者id，以2088开头的16位纯数字
        $this->alipay_config['seller_email']  = $config_value['alipay_account'];//收款支付宝账号，一般情况下收款账号就是签约账号
        $this->alipay_config['key']	      = $config_value['alipay_key'];//安全检验码，以数字和字母组成的32位字符
        $this->alipay_config['sign_type']     = strtoupper('MD5');//签名方式 不需修改
        $this->alipay_config['input_charset'] = strtolower('utf-8');//字符编码格式 目前支持 gbk 或 utf-8
        $this->alipay_config['cacert']        = getcwd().'\\cacert.pem'; //ca证书路径地址，用于curl中ssl校验 //请保证cacert.pem文件在当前文件夹目录中
        $this->alipay_config['transport']     = 'http';//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $this->alipay_config['transfer_partner']       = $config_value['transfer_alipay_partner'];
        $this->alipay_config['developer_private_key']  = $config_value['developer_private_key'];//秘钥
        $this->alipay_config['alipay_public_Key']  = $config_value['alipay_public_Key'];//查看支付宝公钥
    }    
    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $config_value    支付方式信息
     */
    function get_code($order, $config_value)
    {         
           $shop_info = settings();
           $store_name = $shop_info['shop_title'].'订单';
        
             // 接口类型
            $service = array(             
                 1 => 'create_partner_trade_by_buyer', //使用担保交易接口
                 2 => 'create_direct_pay_by_user', //使用即时到帐交易接口
                 );
            //构造要请求的参数数组，无需改动
            $parameter = array(
                        
                        "partner" => trim($this->alipay_config['partner']), //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
                        'seller_id'=> trim($this->alipay_config['partner']), //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
                        "key" => trim($this->alipay_config['key']), // MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
                        // "seller_email" => trim($this->alipay_config['seller_email']),                                            
                        "notify_url"	=>  _url('shop/payment/notifyUrl',array('pay_code'=>'alipayMobile'),'',true,true) , //服务器异步通知页面路径 //必填，不能修改
                        "return_url"	=>  _url('shop/payment/returnUrl',array('pay_code'=>'alipayMobile'),true,true),  //页面跳转同步通知页面路径
                        "sign_type"     => strtoupper('MD5'), //签名方式
                        "input_charset" =>strtolower('utf-8'), //字符编码格式 目前支持utf-8
                        "cacert"	=>  getcwd().'\\cacert.pem',
                        "transport"	=> 'http', // //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http                        
                        "service" => 'alipay.wap.create.direct.pay.by.user',   // // 产品类型，无需修改                
                        "payment_type"  => "1", // 支付类型 ，无需修改
                        "_input_charset"=> trim(strtolower($this->alipay_config['input_charset'])), //字符编码格式 目前支持 gbk 或 utf-8
                        "out_trade_no"	=> $order['order_sn'], //商户订单号
                        "subject"       => $store_name, //订单名称，必填
                        "total_fee"	=> $order['order_amount'], //付款金额
                        "show_url"	=> config('config.host_path'), //收银台页面上，商品展示的超链接，必填
                
                    );
            //  如果是支付宝网银支付    
            if(!empty($config_value['bank_code']))
            {            
                $parameter["paymethod"] = 'bankPay'; // 若要使用纯网关，取值必须是bankPay（网银支付）。如果不设置，默认为directPay（余额支付）。
                $parameter["defaultbank"] = $config_value['bank_code'];
                $parameter["service"] = 'create_direct_pay_by_user';
            }        
            //建立请求
            require_once("lib/alipay_submit.class.php");            
            $alipaySubmit = new AlipaySubmit($this->alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
            return $html_text;         
    }
    
    /**
     * 服务器点对点响应操作给支付接口方调用
     * 
     */
    function response()
    {
        require_once("lib/alipay_notify.class.php");  // 请求返回


        $fp = fopen(Env::get('runtime_path')."/alipay/".date('Y-m-d').'.txt',"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"记录时间：".strftime("%Y-%m-%d %H:%M:%S",time())."\n".json_encode($_POST)."\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($this->alipay_config); // 使用支付宝原生自带的累 和方法 这里只是引用了一下 而已
        $verify_result = $alipayNotify->verifyNotify();

            if($verify_result) //验证成功
            {
                    $order_sn = $out_trade_no = $_POST['out_trade_no']; //商户订单号
                    $trade_no = $_POST['trade_no']; //支付宝交易号
                    $trade_status = $_POST['trade_status']; //交易状态
                //购买身份商品
                if (stripos($order_sn, 'role') !== false) {
                    $RoleOrderModel = new RoleOrderModel();
                    $orderInfo = $RoleOrderModel->where('order_sn', "$order_sn")->field('order_id,order_amount,user_id,pay_status')->find();
                    if (empty($orderInfo)) exit("fail");
                    if ($orderInfo['pay_status'] == 1) exit("success");
                    if ($orderInfo['order_amount'] != $_POST['price']) exit("fail"); //验证失败
                    $orderInfo = $orderInfo->toArray();
                    $orderInfo['transaction_id'] = $trade_no;
                    // 支付宝解释: 交易成功且结束，即不可再做任何操作。
                    if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                        $RoleOrderModel->updatePay($orderInfo);// 修改订单支付状态
                    } //支付宝解释: 交易成功，且可对该交易做操作，如：多级分润、退款等。
                    elseif ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                        $RoleOrderModel->updatePay($orderInfo); // 修改订单支付状态
                    }

                }elseif (stripos($order_sn, 'recharge') !== false){ //用户在线充值
						$RechargeLogModel = new RechargeLogModel();
						$orderInfo = $RechargeLogModel->where('order_sn',"$order_sn")->field('log_id,order_amount,user_id,status')->find();
                        if (empty($orderInfo)) exit("fail");
                        $orderInfo = $orderInfo->toArray();
						if ($orderInfo['status'] == 9) exit("success");
						if($orderInfo['order_amount']!=$_POST['price'])  exit("fail"); //验证失败
						$orderInfo['transaction_id'] = $trade_no;
						// 支付宝解释: 交易成功且结束，即不可再做任何操作。
						if($_POST['trade_status'] == 'TRADE_FINISHED') 
						{                         
							  $RechargeLogModel->updatePay($orderInfo);// 修改订单支付状态
						}
						//支付宝解释: 交易成功，且可对该交易做操作，如：多级分润、退款等。
						elseif ($_POST['trade_status'] == 'TRADE_SUCCESS') 
						{ 
								$RechargeLogModel->updatePay($orderInfo); // 修改订单支付状态
						}
					}else{
						$OrderModel = new OrderModel();
	                    $orderInfo = $OrderModel->where('order_sn',"$order_sn")->field('order_id,order_amount,pay_status')->find();
	                    if (empty($orderInfo)) exit("fail");
	                    if ($orderInfo['pay_status'] == 1) exit("success");
						if($orderInfo['order_amount']!=$_POST['price'])  exit("fail"); //验证失败

						 // 支付宝解释: 交易成功且结束，即不可再做任何操作。
						if($_POST['trade_status'] == 'TRADE_FINISHED')
						{
							  $OrderModel->updatePay(array('order_id'=>$orderInfo['order_id'],'money_paid'=>$orderInfo['order_amount'],'transaction_id'=>$trade_no),'支付宝支付成功，流水号：'.$trade_no);// 修改订单支付状态
						}
						//支付宝解释: 交易成功，且可对该交易做操作，如：多级分润、退款等。
						elseif ($_POST['trade_status'] == 'TRADE_SUCCESS')
						{ 
								$OrderModel->updatePay(array('order_id'=>$orderInfo['order_id'],'money_paid'=>$orderInfo['order_amount'],'transaction_id'=>$trade_no),'支付宝支付成功，流水号：'.$trade_no);// 修改订单支付状态
						}
					}
                                         

                   
                    echo "success"; // 告诉支付宝处理成功
            }
            else 
            {                
                echo "fail"; //验证失败                                
            }
    }
    
    /**
     * 页面跳转响应操作给支付接口方调用
     */
    function respond2()
    {
        require_once("lib/alipay_notify.class.php");  // 请求返回
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

            if($verify_result) //验证成功
            {
                    $order_sn = $out_trade_no = $_GET['out_trade_no']; //商户订单号
                    $trade_no = $_GET['trade_no']; //支付宝交易号                   
                    $trade_status = $_GET['trade_status']; //交易状态
                    
                    if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') 
                    {                           
                       return array('status'=>1,'order_sn'=>$order_sn);//跳转至成功页面
                    }
                    else {                        
                       return array('status'=>0,'order_sn'=>$order_sn); //跳转至失败页面
                    }                       
            }
            else 
            {                     
                return array('status'=>0,'order_sn'=>$_GET['out_trade_no']);//跳转至失败页面
            }
    }
    /**
     * 退款
     */
    public function refund($orderInfo = []){
        require_once("aop/AopClient.php");
        require_once("aop/request/AlipayTradeRefundRequest.php");
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId =  $this->alipay_config['transfer_partner'];//'your app_id';
        $aop->rsaPrivateKey = $this->alipay_config['developer_private_key'];// '请填写开发者私钥去头去尾去回车，一行字符串';
        $aop->alipayrsaPublicKey= $this->alipay_config['alipay_public_Key'];//'请填写支付宝公钥，一行字符串';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayTradeRefundRequest();
        $array=array(
            'out_trade_no'=>$orderInfo['order_sn'],//订单支付时传入的商户订单号,不能和 trade_no同时为空。
            'trade_no'=>$orderInfo['transaction_id'],//支付宝交易号，和商户订单号不能同时为空
            'refund_amount'=>$orderInfo['refund_amount'],//需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
            'refund_reason'=>'退款',//退款的原因说明
            'out_request_no'=>$orderInfo['order_sn'],//标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
            'operator_id'=>AUID,//商户的操作员编号

        );
        $list=json_encode($array);
        $request->setBizContent($list);
        $result = $aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if(!empty($resultCode)&&$resultCode == 10000){
           return true;
        } else {
            return false;
        }
    }
}