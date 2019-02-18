<?php

/**
 * 支付宝插件
 */

/**
 * 生成支付代码
 * @param   array   $order      订单信息
 * @param   array   $payment    支付方式信息
 */
function get_alipay_code($order)
{
	$pay_config = json_decode(urldecode($order['pay_config']),true);
	$service = 'create_direct_pay_by_user';
	$extend_param = 'isv^sh22';
	$notify_url = $order['notify_url'];
	$parameter = array(
		'extend_param'      => $extend_param,
		'service'           => $service,
		'partner'           => $pay_config['partner'],
		//'partner'           => ALIPAY_ID,
		'_input_charset'    =>  'utf-8',
		'notify_url'        => $notify_url,
		'return_url'        => $notify_url,
		/* 业务参数 */
		'subject'           => $order['order_sn'],
		'out_trade_no'      => $order['pay_out_trade_no'],
		'price'             => $order['order_amount'],
		'quantity'          => 1,
		'payment_type'      => 1,
		/* 物流参数 */
		'logistics_type'    => 'EXPRESS',
		'logistics_fee'     => 0,
		'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
		/* 买卖双方信息 */
		'seller_email'      => $pay_config['seller_email'],
		'call_back_url'		=> $notify_url
	);

	ksort($parameter);
	reset($parameter);

	$param = '';
	$sign  = '';

	foreach ($parameter AS $key => $val)
	{
		$param .= "$key=" .urlencode($val). "&";
		$sign  .= "$key=$val&";
	}

	$param = substr($param, 0, -1);
	$sign  = substr($sign, 0, -1). $pay_config['key'];
	//$sign  = substr($sign, 0, -1). ALIPAY_AUTH;
	$url = "https://www.alipay.com/cooperate/gateway.do?$param&sign=".md5($sign)."&sign_type=MD5";
	return '<input id="alipay_button" type="button" value="点击前往支付宝支付" onclick="window.location.href=\''.$url.'\'">';
}

 /**
 * 生成支付代码
 * @param   array   $order      订单信息
 * @param   array   $payment    支付方式信息
 */
function get_wap_alipay_code($order)
{
	$pay_config = json_decode(urldecode($order['pay_config']),true);
	require_once("alipay_submit.class.php");

		/**************************调用授权接口alipay.wap.trade.create.direct获取授权码token**************************/
		$alipay_config['partner']		= $pay_config['partner'];
		
		//安全检验码，以数字和字母组成的32位字符
		//如果签名方式设置为"MD5"时，请设置该参数
		$alipay_config['key']			= $pay_config['key'];
		//签名方式 不需修改
		$alipay_config['sign_type']    = 'MD5';
		
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= 'utf-8';
		
		//返回格式
		$format = "xml";
		//必填，不需要修改
		
		//返回格式
		$v = "2.0";
		//必填，不需要修改
		
		//请求号
		$req_id = date('Ymdhis');
		//必填，须保证每次请求都是唯一
		
		//**req_data详细信息**
		
		//服务器异步通知页面路径
		$notify_url = $order['notify_url'];
		//需http://格式的完整路径，不允许加?id=123这类自定义参数
		
		//页面跳转同步通知页面路径
		$call_back_url = $notify_url;
		//需http://格式的完整路径，不允许加?id=123这类自定义参数
		
		//操作中断返回地址
		$merchant_url = $order['notify_url'];
		//用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数
		
		//卖家支付宝帐户
		$seller_email = $pay_config['seller_email'];
		//必填
		
		//商户订单号
		$out_trade_no = $order['pay_out_trade_no'];
		//商户网站订单系统中唯一订单号，必填
		
		//订单名称
		$subject = $order['order_sn'];
		//必填
		
		//付款金额
		$total_fee = $order['order_amount'];
		//必填
		
		//请求业务参数详细
		$req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';
		//必填
		
		/************************************************************/

		//构造要请求的参数数组，无需改动
		$para_token = array(
				"service" => "alipay.wap.trade.create.direct",
				"partner" => trim($alipay_config['partner']),
				"sec_id" => trim($alipay_config['sign_type']),
				"format"	=> $format,
				"v"	=> $v,
				"req_id"	=> $req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> 'utf-8'
		);
		
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($para_token);
		
		//URLDECODE返回的信息
		$html_text = urldecode($html_text);
		
		//解析远程模拟提交后返回的信息
		$para_html_text = $alipaySubmit->parseResponse($html_text);
		
		//获取request_token
		$request_token = $para_html_text['request_token'];
		
		
		/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
		
		//业务详细
		$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
		//必填
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "alipay.wap.auth.authAndExecute",
				"partner" => trim($alipay_config['partner']),
				"sec_id" => trim($alipay_config['sign_type']),
				"format"	=> $format,
				"v"	=> $v,
				"req_id"	=> $req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> 'utf-8'
		);
		
		//建立请求		
		//$parameter = $alipaySubmit->buildRequestParaToString($parameter);	
		
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "前往支付宝支付");
        return $html_text;
		
}



/**
 * 响应操作
 */
function alipay_respond($orderInfo){
	$payment = json_decode(urldecode($orderInfo['pay_config']),true);
	
	if (!empty($_POST)){
		if ($_POST['notify_data']){
			$notify_data = @simplexml_load_string(stripslashes($_POST['notify_data']),NULL,LIBXML_NOCDATA);
			$notify_data = json_decode(json_encode($notify_data),true);
			$out_trade_no = explode('__',$notify_data['out_trade_no']);
			if ($out_trade_no[0] != $orderInfo['order_sn']) return false;
			$sign = 'service=';
			$sign .= $_POST['service'];
			$sign .= '&v='.$_POST['v'];
			$sign .= '&sec_id='.$_POST['sec_id'];
			$sign .= '&notify_data='.$_POST['notify_data'];
			$sign .= $payment['key'];  
			$_GET['trade_status'] = $notify_data['trade_status'];
			$_GET['sign'] = $_POST['sign'];
			$_GET['out_trade_no'] = $notify_data['out_trade_no'];
			$_GET['trade_no'] = $notify_data['trade_no'];
		}else{
			ksort($_POST);
			reset($_POST);
			$sign = '';
			foreach ($_POST as $key=>$val){
				if ($key != 'sign' && $key != 'sign_type' && $key != 'code' ){
					$sign .= "$key=$val&";
				}
			}
			$sign = substr($sign, 0, -1) . $payment['key'];
			$_GET['sign'] = $_POST['sign'];
			$_GET['trade_status'] = $_POST['trade_status'];
			$_GET['out_trade_no'] = $_POST['out_trade_no'];
			$_GET['trade_no'] = $_POST['trade_no'];
		}
		
	}else{
		
		$seller_email = rawurldecode($_GET['seller_email']);
		if (empty($_GET['subject'])){
			$out_trade_no = explode('__',$_GET['out_trade_no']);
			$order_sn = $out_trade_no[0];
			if ($order_sn != $orderInfo['order_sn']) return false;
		}else{
			$order_sn = $_GET['subject'];
			if ($order_sn != $orderInfo['order_sn']) return false;
		}
	
		$order_sn = trim($order_sn);
		

		unset($_GET['order_id']);
		/* 检查数字签名是否正确 */
		ksort($_GET);
		reset($_GET);
		$sign = '';
		foreach ($_GET as $key=>$val){
			if ($key != 'sign' && $key != 'sign_type' && $key != 'code' ){
				$sign .= "$key=$val&";
			}
		}
		$sign = substr($sign, 0, -1) . $payment['key'];
	}	
	if (md5($sign) != $_GET['sign']) return false;
	

	if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS')
	{	
		return true;
	}
	elseif ($_GET['trade_status'] == 'TRADE_FINISHED')
	{
		return true;
	}
	elseif ($_GET['trade_status'] == 'TRADE_SUCCESS')
	{
		return true;
	}
	elseif ($_GET['result'] == 'success')
	{
		return true;
	}
	
	return false;
	
}

?>