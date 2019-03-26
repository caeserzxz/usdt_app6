<?php
/**
 * tpshop 微信支付插件
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

/**
 * 支付 逻辑定义
 * Class
 */
namespace payment\weixinH5;
use think\Db;
use app\mainadmin\model\PaymentModel;
class weixinH5
{
    /**
     * 析构流函数
     */
    public function  __construct()
    {
        require_once(EXTEND_PATH."payment/weixin/lib/WxPay.Api.php"); // 微信扫码支付demo 中的文件
        require_once(EXTEND_PATH."payment/weixin/example/WxPay.NativePay.php");

        $payment =  (new PaymentModel)->where('pay_code', 'weixinH5')->find();
        $config = json_decode(urldecode($payment['pay_config']),true);   
        \WxPayConfig::$appid = $config['appid']; // * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
        \WxPayConfig::$mchid = $config['mchid']; // * MCHID：商户号（必须配置，开户邮件中可查看）
        \WxPayConfig::$key = $config['key']; // KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
        \WxPayConfig::$appsecret = $config['appsecret']; // 公众帐号secert（仅JSAPI支付的时候需要配置)，
    }    
    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $config    支付方式信息
     */
    function get_code($order, $config)
    {
        $notify_url = _url('shop/payment/notifyUrl',['pay_code'=>'weixinH5'],true,true);  // 接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
        $sceneInfo = json_encode([
            'h5_info' => [
                'type' => 'Wap',
                'wap_url' => SITE_URL,
                'wap_name' => '商城网站'
            ]
        ], JSON_UNESCAPED_UNICODE);

        $input = new \WxPayUnifiedOrder();
        $input->SetBody($config['body']); // 商品描述
        $input->SetAttach("weixinH5"); // 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        $input->SetOut_trade_no($order['order_sn'] . time()); // 商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
        $input->SetTotal_fee($order['order_amount'] * 100); // 订单总金额，单位为分，详见支付金额
        $input->SetTrade_type("MWEB"); // 交易类型   取值如下：JSAPI，NATIVE，APP，MWEB 详细说明见参数规定    MWEB--H5wap支付
        $input->SetNotify_url($notify_url); // 接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
        $input->SetSceneInfo($sceneInfo);

        try {
            $return = \WxPayApi::unifiedOrder($input);
        } catch (\Exception $e) {
            return ['status' => -1, 'msg' => $e->getMessage()];
        }

        if ($return['return_code'] == 'SUCCESS' && $return['result_code'] == 'SUCCESS') {
            return ['status' => 1, 'msg' => $return['return_msg'], 'result' => $return['mweb_url']];
        } else {

            return ['status' => -1, 'msg' => $return['return_msg'], 'result' => $return];
        }
    }

    /**
     * 服务器点对点响应操作给支付接口方调用
     * 
     */
    function response()
    {                        
        require_once(EXTEND_PATH."payment/weixin/example/notify.php");
        $notify = new \PayNotifyCallBack();
        $notify->Handle(false);       
    }
    
    /**
     * 页面跳转响应操作给支付接口方调用
     */
    function respond2()
    {

    }

    // 微信提现批量转账
    function transfer($data){
    /*code_8提现在线转账业务逻辑代码*/
    	//CA证书及支付信息
    	$wxchat['appid'] = \WxPayConfig::$appid;
    	$wxchat['mchid'] = \WxPayConfig::$mchid;
    	$wxchat['api_cert'] = './plugins/payment/weixin/cert/apiclient_cert.pem';
    	$wxchat['api_key'] = './plugins/payment/weixin/cert/apiclient_key.pem';
    	$wxchat['api_ca'] = './plugins/payment/weixin/cert/rootca.pem';
    	$webdata = array(
    			'mch_appid' => $wxchat['appid'],
    			'mchid'     => $wxchat['mchid'],
    			'nonce_str' => md5(time()),
    			//'device_info' => '1000',
    			'partner_trade_no'=> $data['pay_code'], //商户订单号，需要唯一
    			'openid' => $data['openid'],//转账用户的openid
    			'check_name'=> 'NO_CHECK', //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：
    			//'re_user_name' => 'jorsh', //收款人用户姓名
    			'amount' => $data['money'] * 100, //付款金额单位为分
    			'desc'   => empty($data['desc'])? '企业付款转账' : $data['desc'],
    			'spbill_create_ip' => request()->ip(),
    	);
    	foreach ($webdata as $k => $v) {
    		$tarr[] =$k.'='.$v;
    	}
    	sort($tarr);
    	$sign = implode($tarr, '&');
    	$sign .= '&key='.\WxPayConfig::$key;
    	$webdata['sign']=strtoupper(md5($sign));
    	$wget = $this->array2xml($webdata);
    	$pay_url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
    	$res = $this->http_post($pay_url, $wget, $wxchat);
    	if(!$res){
    		return array('status'=>1, 'msg'=>"Can't connect the server" );
    	}
    	$content = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
    	if(strval($content->return_code) == 'FAIL'){
    		return array('status'=>1, 'msg'=>strval($content->return_msg));
    	}
    	if(strval($content->result_code) == 'FAIL'){
    		return array('status'=>1, 'msg'=>strval($content->err_code),':'.strval($content->err_code_des));
    	}
    	$rdata = array(
    			'mch_appid'        => strval($content->mch_appid),
    			'mchid'            => strval($content->mchid),
    			'device_info'      => strval($content->device_info),
    			'nonce_str'        => strval($content->nonce_str),
    			'result_code'      => strval($content->result_code),
    			'partner_trade_no' => strval($content->partner_trade_no),
    			'payment_no'       => strval($content->payment_no),
    			'payment_time'     => strval($content->payment_time),
    	);
    	return $rdata;
	/*code_8提现在线转账业务逻辑代码*/
    }
    
    /**
     * 将一个数组转换为 XML 结构的字符串
     * @param array $arr 要转换的数组
     * @param int $level 节点层级, 1 为 Root.
     * @return string XML 结构的字符串
     */
    function array2xml($arr, $level = 1) {
    	$s = $level == 1 ? "<xml>" : '';
    	foreach($arr as $tagname => $value) {
    		if (is_numeric($tagname)) {
    			$tagname = $value['TagName'];
    			unset($value['TagName']);
    		}
    		if(!is_array($value)) {
    			$s .= "<{$tagname}>".(!is_numeric($value) ? '<![CDATA[' : '').$value.(!is_numeric($value) ? ']]>' : '')."</{$tagname}>";
    		} else {
    			$s .= "<{$tagname}>" . $this->array2xml($value, $level + 1)."</{$tagname}>";
    		}
    	}
    	$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    	return $level == 1 ? $s."</xml>" : $s;
    }
    
    function http_post($url, $param, $wxchat) {
    	$oCurl = curl_init();
    	if (stripos($url, "https://") !== FALSE) {
    		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
    	}
    	if (is_string($param)) {
    		$strPOST = $param;
    	} else {
    		$aPOST = array();
    		foreach ($param as $key => $val) {
    			$aPOST[] = $key . "=" . urlencode($val);
    		}
    		$strPOST = join("&", $aPOST);
    	}
    	curl_setopt($oCurl, CURLOPT_URL, $url);
    	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($oCurl, CURLOPT_POST, true);
    	curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    	if($wxchat){
    		curl_setopt($oCurl,CURLOPT_SSLCERT,dirname(THINK_PATH).$wxchat['api_cert']);
    		curl_setopt($oCurl,CURLOPT_SSLKEY,dirname(THINK_PATH).$wxchat['api_key']);
    		curl_setopt($oCurl,CURLOPT_CAINFO,dirname(THINK_PATH).$wxchat['api_ca']);
    	}
    	$sContent = curl_exec($oCurl);
    	$aStatus = curl_getinfo($oCurl);
    	curl_close($oCurl);
    	if (intval($aStatus["http_code"]) == 200) {
    		return $sContent;
    	} else {
    		return false;
    	}
    }
    
     // 微信订单退款原路退回
    public function refund($data){
    /*code_4支付原路退回逻辑*/
    	if(!empty($data["transaction_id"])){
    		$input = new \WxPayRefund();
    		$input->SetTransaction_id($data["transaction_id"]);
    		$input->SetTotal_fee($data["order_amount"]*100);
    		$input->SetRefund_fee($data["money_paid"]*100);
    		$input->SetOut_refund_no(\WxPayConfig::$mchid.date("YmdHis"));
    		$input->SetOp_user_id(\WxPayConfig::$mchid);
            $res = \WxPayApi::refund($input);
            if ($res['return_code'] == 'FAIL'){
                return $res['return_msg'];
            }
            if($res['result_code'] == 'FAIL'){
                return $res['err_code_des'];
            }if ($res['return_code'] == 'FAIL'){
                return $res['return_msg'];
            }
            return true;
    	}else{
    		return false;
    	}
	/*code_4支付原路退回逻辑*/
    }

}