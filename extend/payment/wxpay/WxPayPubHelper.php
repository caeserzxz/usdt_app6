<?php
/**
 * 微信支付帮助库
 * ====================================================
 * 接口分三种类型：
 * 【请求型接口】--Wxpay_client_
 * 		统一支付接口类--UnifiedOrder
 * 		订单查询接口--OrderQuery
 * 		退款申请接口--Refund
 * 		退款查询接口--RefundQuery
 * 		对账单接口--DownloadBill
 * 		短链接转换接口--ShortUrl
 * 【响应型接口】--Wxpay_server_
 * 		通用通知接口--Notify
 * 		Native支付——请求商家获取商品信息接口--NativeCall
 * 【其他】
 * 		静态链接二维码--NativeLink
 * 		JSAPI支付--JsApi
 * =====================================================
 * 【CommonUtil】常用工具：
 * 		trimString()，设置参数时需要用到的字符处理函数
 * 		createNoncestr()，产生随机字符串，不长于32位
 * 		formatBizQueryParaMap(),格式化参数，签名过程需要用到
 * 		getSign(),生成签名
 * 		arrayToXml(),array转xml
 * 		xmlToArray(),xml转 array
 * 		postXmlCurl(),以post方式提交xml到对应的接口url
 * 		postXmlSSLCurl(),使用证书，以post方式提交xml到对应的接口url
*/
namespace App\Extend\payment\wxpay;

/**
 * 所有接口的基类
 */
class Common_util_pub
{
	function __construct($cofing) {
		//=======【基本信息设置】=====================================
		//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
		$this->APPID = $cofing['weixin_appid'];							
		//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
		$this->APPSECRET = $cofing['weixin_appsecret'];
		
		//=======【证书路径设置】=====================================
		//证书路径,注意应该填写绝对路径
		
		/*$__FILE__ = str_replace('\\','/',realpath(dirname(__FILE__).'/'));
		$__FILE__ =  strstr($__FILE__, __ROOT__, TRUE).__ROOT__; */
		$__FILE__ = getcwd(); 
		$this->SSLCERT_PATH = $__FILE__.'/Upload/cacert/apiclient_cert.pem';
		$this->SSLKEY_PATH = $__FILE__.'/Upload/cacert/apiclient_key.pem';
		//$this->ROOTCA_PATH = $__FILE__.'/Upload/cacert/rootca.pem';
		//$this->SSLCERT_PATH = $cofing['wx_apiclient_cert'];
		//$this->SSLKEY_PATH = $cofing['wx_apiclient_key'];
		//受理商ID，身份标识
		$this->MCHID = $cofing['pay_config']['MCHID'];
		//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
		$this->KEY = $cofing['pay_config']['KEY'];
		//=======【curl超时设置】===================================
		//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
		 $this->CURL_TIMEOUT = 30;
	}

	function trimString($value)
	{
		$ret = null;
		if (null != $value) 
		{
			$ret = $value;
			if (strlen($ret) == 0) 
			{
				$ret = null;
			}
		}
		return $ret;
	}
	
	/**
	 * 	作用：产生随机字符串，不长于32位
	 */
	public function createNoncestr( $length = 32 ) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		}  
		return $str;
	}
	
	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode)
	{
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
		    if($urlencode)
		    {
			   $v = urlencode($v);
			}
			//$buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen($buff) > 0) 
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
	
	/**
	 * 	作用：生成签名
	 */
	public function getSign($Obj)
	{
		foreach ($Obj as $k => $v)
		{
			$Parameters[$k] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);
		//echo '【string1】'.$String.'</br>';
		//签名步骤二：在string后加入KEY
		$String = $String."&key=".$this->KEY;
		//echo "【string2】".$String."</br>";
		//签名步骤三：MD5加密
		$String = md5($String);
		//echo "【string3】 ".$String."</br>";
		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);
		//echo "【result】 ".$result_."</br>";
		return $result_;
	}
	
	/**
	 * 	作用：array转xml
	 */
	function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
	
	/**
	 * 	作用：将xml转为array
	 */
	public function xmlToArray($xml)
	{		
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $array_data;
	}

	/**
	 * 	作用：以post方式提交xml到对应的接口url
	 */
	public function postXmlCurl($xml,$url,$second=30)
	{		
        //初始化curl        
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOP_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
        $data = curl_exec($ch);
		curl_close($ch);
		//返回结果
		if($data)
		{
			curl_close($ch);
			return $data;
		}
		else 
		{ 
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>"; 
			//echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}

	/**
	 * 	作用：使用证书，以post方式提交xml到对应的接口url
	 */
	function postXmlSSLCurl($xml,$url,$second=30)
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		//这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch,CURLOPT_HEADER,FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		//设置证书
		//使用证书：cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		//curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT, $this->SSLCERT_PATH);
		//默认格式为PEM，可以注释
		//curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY, $this->SSLKEY_PATH);
		
	   //curl_setopt($ch,CURLOPT_CAINFO,$this->ROOTCA_PATH);
		 
		//post提交方式
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		}
		else { 
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>"; 
			//echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}
	
	/**
	 * 	作用：打印数组
	 */
	function printErr($wording='',$err='')
	{
		print_r('<pre>');
		echo $wording."</br>";
		var_dump($err);
		print_r('</pre>');
	}
	
	/**
	 * 	作用：设置jsapi的参数
	 */
	public function getParameters($prepay_id)
	{
		$jsApiObj["appId"] = $this->APPID;
		$timeStamp = time();
	    $jsApiObj["timeStamp"] = "$timeStamp";
	    $jsApiObj["nonceStr"] = $this->createNoncestr();
		$jsApiObj["package"] = "prepay_id=$prepay_id";
	    $jsApiObj["signType"] = "MD5";
	    $jsApiObj["paySign"] = $this->getSign($jsApiObj);
	    $this->parameters = json_encode($jsApiObj);		
		return $this->parameters;
	}
}

/**
 * 请求型接口的基类
 */
class Wxpay_client_pub extends Common_util_pub 
{
	var $parameters;//请求参数，类型为关联数组
	public $response;//微信返回的响应
	public $result;//返回参数，类型为关联数组
	var $url;//接口链接
	var $curl_timeout;//curl超时时间
	
	/**
	 * 	作用：设置请求参数
	 */
	function setParameter($parameter, $parameterValue)
	{
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	
	/**
	 * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
	 */
	function createXml()
	{		
	   	$this->parameters["appid"] = $this->APPID;//公众账号ID
	   	$this->parameters["mch_id"] = $this->MCHID;//商户号
	    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
	    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
	    return  $this->arrayToXml($this->parameters);
	}
	
	/**
	 * 	作用：post请求xml
	 */
	function postXml()
	{
	    $xml = $this->createXml();
		$this->response = $this->postXmlCurl($xml,$this->url,$this->curl_timeout);
		return $this->response;
	}
	
	/**
	 * 	作用：使用证书post请求xml
	 */
	function postXmlSSL()
	{	
	    $xml = $this->createXml();
		$this->response = $this->postXmlSSLCurl($xml,$this->url,$this->curl_timeout);
		return $this->response;
	}

	/**
	 * 	作用：获取结果，默认不使用证书
	 */
	function getResult() 
	{		
		$this->postXml();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
}


/**
 * 统一支付接口类
 */
class UnifiedOrder_pub extends Wxpay_client_pub
{	
	function __construct($order) 
	{
		parent::__construct($order);
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		try
		{
			//检测必填参数
			if($this->parameters["out_trade_no"] == null) 
			{
				throw new SDKRuntimeException("缺少统一支付接口必填参数out_trade_no！"."<br>");
			}elseif($this->parameters["body"] == null){
				throw new SDKRuntimeException("缺少统一支付接口必填参数body！"."<br>");
			}elseif ($this->parameters["total_fee"] == null ) {
				throw new SDKRuntimeException("缺少统一支付接口必填参数total_fee！"."<br>");
			}elseif ($this->parameters["notify_url"] == null) {
				throw new SDKRuntimeException("缺少统一支付接口必填参数notify_url！"."<br>");
			}elseif ($this->parameters["trade_type"] == null) {
				throw new SDKRuntimeException("缺少统一支付接口必填参数trade_type！"."<br>");
			}elseif ($this->parameters["trade_type"] == "JSAPI" &&
				$this->parameters["openid"] == NULL){
				throw new SDKRuntimeException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！"."<br>");
			}
		   	$this->parameters["appid"]  = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		   	$this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];//$_SERVER['REMOTE_ADDR'];//终端ip	    
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	/**
	 * 获取prepay_id
	 */
	function getPrepayId()
	{
		$this->postXml();
		$this->result = $this->xmlToArray($this->response);
		$prepay_id = $this->result["prepay_id"];
		return $prepay_id;
	}
	
}

/**
 * 订单查询接口
 */
class OrderQuery_pub extends Wxpay_client_pub
{
	function __construct($order) 
	{
		parent::__construct($order);
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//设置curl超时时间
		$this->curl_timeout = $this->CURL_TIMEOUT;		
	}

	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		try
		{
			//检测必填参数
			if($this->parameters["out_trade_no"] == null && 
				$this->parameters["transaction_id"] == null) 
			{
				throw new SDKRuntimeException("订单查询接口中，out_trade_no、transaction_id至少填一个！"."<br>");
			}
		   	$this->parameters["appid"] = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}

}

/**
 * 退款申请接口
 */
class Refund_pub extends Wxpay_client_pub
{
	
	function __construct() {
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
		//设置curl超时时间
		$this->curl_timeout = $this->CURL_TIMEOUT;		
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		try
		{
			//检测必填参数
			if($this->parameters["out_trade_no"] == null && $this->parameters["transaction_id"] == null) {
				throw new SDKRuntimeException("退款申请接口中，out_trade_no、transaction_id至少填一个！"."<br>");
			}elseif($this->parameters["out_refund_no"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数out_refund_no！"."<br>");
			}elseif($this->parameters["total_fee"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数total_fee！"."<br>");
			}elseif($this->parameters["refund_fee"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数refund_fee！"."<br>");
			}elseif($this->parameters["op_user_id"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数op_user_id！"."<br>");
			}
		   	$this->parameters["appid"] = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	/**
	 * 	作用：获取结果，使用证书通信
	 */
	function getResult() 
	{		
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
	
}


/**
 * 退款查询接口
 */
class RefundQuery_pub extends Wxpay_client_pub
{
	
	function __construct() {
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/pay/refundquery";
		//设置curl超时时间
		$this->curl_timeout = $this->CURL_TIMEOUT;		
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{		
		try 
		{
			if($this->parameters["out_refund_no"] == null &&
				$this->parameters["out_trade_no"] == null &&
				$this->parameters["transaction_id"] == null &&
				$this->parameters["refund_id "] == null) 
			{
				throw new SDKRuntimeException("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！"."<br>");
			}
		   	$this->parameters["appid"] = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}

	/**
	 * 	作用：获取结果，使用证书通信
	 */
	function getResult() 
	{		
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}

}

/**
 * 对账单接口
 */
class DownloadBill_pub extends Wxpay_client_pub
{

	function __construct() 
	{
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/pay/downloadbill";
		//设置curl超时时间
		$this->curl_timeout = $this->CURL_TIMEOUT;		
	}

	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{		
		try 
		{
			if($this->parameters["bill_date"] == null ) 
			{
				throw new SDKRuntimeException("对账单接口中，缺少必填参数bill_date！"."<br>");
			}
		   	$this->parameters["appid"] = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	/**
	 * 	作用：获取结果，默认不使用证书
	 */
	function getResult() 
	{		
		$this->postXml();
		$this->result = $this->xmlToArray($this->result_xml);
		return $this->result;
	}
	
	

}

/**
 * 短链接转换接口
 */
class ShortUrl_pub extends Wxpay_client_pub
{
	function __construct() 
	{
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/tools/shorturl";
		//设置curl超时时间
		$this->curl_timeout = $this->CURL_TIMEOUT;		
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{		
		try 
		{
			if($this->parameters["long_url"] == null ) 
			{
				throw new SDKRuntimeException("短链接转换接口中，缺少必填参数long_url！"."<br>");
			}
		   	$this->parameters["appid"] = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	/**
	 * 获取prepay_id
	 */
	function getShortUrl()
	{
		$this->postXml();
		$prepay_id = $this->result["short_url"];
		return $prepay_id;
	}
	
}

/**
 * 响应型接口基类
 */
class Wxpay_server_pub extends Common_util_pub 
{
	public $data;//接收到的数据，类型为关联数组
	var $returnParameters;//返回参数，类型为关联数组
	
	function __construct($order) 
	{
		parent::__construct($order);
	}
	/**
	 * 将微信的请求xml转换成关联数组，以方便数据处理
	 */
	function saveData($xml)
	{
		$this->data = $this->xmlToArray($xml);
	}
	
	function checkSign()
	{
		$tmpData = $this->data;
		unset($tmpData['sign']);
		$sign = $this->getSign($tmpData);//本地签名
		if ($this->data['sign'] == $sign) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 获取微信的请求数据
	 */
	function getData()
	{		
		return $this->data;
	}
	
	/**
	 * 设置返回微信的xml数据
	 */
	function setReturnParameter($parameter, $parameterValue)
	{
		$this->returnParameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		return $this->arrayToXml($this->returnParameters);
	}
	
	/**
	 * 将xml数据返回微信
	 */
	function returnXml()
	{
		$returnXml = $this->createXml();
		return $returnXml;
	}
}


/**
 * 通用通知接口
 */
class Notify_pub extends Wxpay_server_pub 
{

}

/**
 * 请求商家获取商品信息接口
 */
class NativeCall_pub extends Wxpay_server_pub
{
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		if($this->returnParameters["return_code"] == "SUCCESS"){
		   	$this->returnParameters["appid"] = $this->APPID;//公众账号ID
		   	$this->returnParameters["mch_id"] = $this->MCHID;//商户号
		    $this->returnParameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->returnParameters["sign"] = $this->getSign($this->returnParameters);//签名
		}
		return $this->arrayToXml($this->returnParameters);
	}
	
	/**
	 * 获取product_id
	 */
	function getProductId()
	{
		$product_id = $this->data["product_id"];
		return $product_id;
	}
	
}

/**
 * 静态链接二维码
 */
class NativeLink_pub  extends Common_util_pub
{
	var $parameters;//静态链接参数
	var $url;//静态链接

	function __construct($order) 
	{
		parent::__construct($order);
	}
	
	/**
	 * 设置参数
	 */
	function setParameter($parameter, $parameterValue) 
	{
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	
	/**
	 * 生成Native支付链接二维码
	 */
	function createLink()
	{
		try 
		{		
			if($this->parameters["product_id"] == null) 
			{
				throw new SDKRuntimeException("缺少Native支付二维码链接必填参数product_id！"."<br>");
			}			
		   	$this->parameters["appid"] = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		   	$time_stamp = time();
		   	$this->parameters["time_stamp"] = "$time_stamp";//时间戳
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名    		
			$bizString = $this->formatBizQueryParaMap($this->parameters, false);
		    $this->url = "weixin://wxpay/bizpayurl?".$bizString;
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	/**
	 * 返回链接
	 */
	function getUrl() 
	{
		$this->createLink();
		return $this->url;
	}
}
/**
 * 现金红包
 */
class sendRedpack extends Wxpay_client_pub
{
	
	function __construct($cofing) {
		parent::__construct($cofing);
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
		//设置curl超时时间
		$this->curl_timeout = $this->CURL_TIMEOUT;		
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		try
		{
		   	$this->parameters["wxappid"] = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	/**
	 * 	作用：获取结果，使用证书通信
	 */
	function getResult() 
	{		
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
	
}
/**
 * 现金红包
 */
class sendGroupRedpack extends Wxpay_client_pub
{
	
	function __construct($cofing) {
		parent::__construct($cofing);
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack";
		//设置curl超时时间
		$this->curl_timeout = $this->CURL_TIMEOUT;		
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		try
		{
		   	$this->parameters["wxappid"] = $this->APPID;//公众账号ID
		   	$this->parameters["mch_id"] = $this->MCHID;//商户号
		    $this->parameters["nonce_str"] = 'rifoqpg6po5iwikibzk2fi2am74fcjx3';//$this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	/**
	 * 	作用：获取结果，使用证书通信
	 */
	function getResult() 
	{		
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
	
}

use Exception;
class  SDKRuntimeException extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}

}


?>
