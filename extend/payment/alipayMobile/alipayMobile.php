<?php
/**
 *  支付宝插件

 */

namespace payment\alipayMobile;


use think\Request;
use think\Db;
use app\member\model\RechargeLogModel;
use app\shop\model\OrderModel;
use app\mainadmin\model\PaymentModel;
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
                        "notify_url"	=>  url('shop/payment/notifyUrl',array('pay_code'=>'alipayMobile'),'',true,true) , //服务器异步通知页面路径 //必填，不能修改
                        "return_url"	=>  url('shop/payment/returnUrl',array('pay_code'=>'alipayMobile')),  //页面跳转同步通知页面路径
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
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->alipay_config); // 使用支付宝原生自带的累 和方法 这里只是引用了一下 而已
        $verify_result = $alipayNotify->verifyNotify();
        
            if($verify_result) //验证成功
            {
                    $order_sn = $out_trade_no = $_POST['out_trade_no']; //商户订单号                    
                    $trade_no = $_POST['trade_no']; //支付宝交易号                   
                    $trade_status = $_POST['trade_status']; //交易状态

					//用户在线充值
					if (stripos($order_sn, 'recharge') !== false){	
						$RechargeLogModel = new RechargeLogModel();				
						$orderInfo = $RechargeLogModel->where(['order_sn' => $order_sn, 'status' => 0])->field('log_id,amount,user_id')->find();
						if($orderInfo['amount']!=$_POST['price'])  exit("fail"); //验证失败
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
	                    $orderInfo = $OrderModel->where(['order_sn'=>"$order_sn",'pay_status'=>0])->field('order_id,order_amount')->find();
						if($orderInfo['order_amount']!=$_POST['price'])  exit("fail"); //验证失败 
						
						 // 支付宝解释: 交易成功且结束，即不可再做任何操作。
						if($_POST['trade_status'] == 'TRADE_FINISHED') 
						{                         
							  $OrderModel->updatePay(array('order_id'=>$orderInfo['order_id'],'transaction_id'=>$trade_no));// 修改订单支付状态
						}
						//支付宝解释: 交易成功，且可对该交易做操作，如：多级分润、退款等。
						elseif ($_POST['trade_status'] == 'TRADE_SUCCESS') 
						{ 
								$OrderModel->updatePay(array('order_id'=>$orderInfo['order_id'],'transaction_id'=>$trade_no)); // 修改订单支付状态
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
        $alipayNotify = new AlipayNotify($this->alipay_config);
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
    
}