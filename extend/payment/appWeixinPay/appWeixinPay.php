<?php
/**
 * 支付 逻辑定义
 * Class
 * @package Home\Payment
 */
namespace payment\appWeixinPay;

use app\mainadmin\model\PaymentModel;
class appWeixinPay
{
    public $tableName = 'main_payment'; // 插件表
    public $alipay_config = array();// 支付宝支付配置参数
    /**
     * 析构流函数
     */
    public function  __construct($code=""){
        require_once(EXTEND_PATH."payment/weixin/lib/WxPay.Api.php"); // 微信扫码支付demo 中的文件
        require_once(EXTEND_PATH."payment/weixin/example/WxPay.NativePay.php");
        require_once(EXTEND_PATH."payment/weixin/example/WxPay.JsApiPay.php");
        if(!$code){
            $code = 'appWeixinPay';
        }
        $payment =  (new PaymentModel)->where('pay_code', 'appWeixinPay')->find();
        $config = json_decode(urldecode($payment['pay_config']),true);
        \WxPayConfig::$appid = $config['appid']; // * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
        \WxPayConfig::$mchid = $config['mchid']; // * MCHID：商户号（必须配置，开户邮件中可查看）
        \WxPayConfig::$key = $config['key']; // KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
        \WxPayConfig::$appsecret = $config['appsecret']; // 公众帐号secert（仅JSAPI支付的时候需要配置)，
        \WxPayConfig::$app_type = $code;
    }

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $config_value    支付方式信息
     */
    function get_code($order, $config_value)
    {
        $notify_url = _url('shop/Payment/notifyUrl',['pay_code'=>'appWeixinPay'],true,true);  // 接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
        $return_url = _url('shop/flow/done',['order_id'=>$order['order_id']],true,true);  //页面跳转同步通知页面路径

        $order_amount = $order['order_amount']*100;
        // $order_amount = 1;
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("支付订单：".$order['order_sn']);
        $input->SetOut_trade_no($order['order_sn']);
        $input->SetTotal_fee($order_amount);
        $input->SetNotify_url($notify_url);
        $input->SetTrade_type("APP");
  
        $inputObj = \WxPayApi::unifiedOrderApp($input);  //还需要签名
        $appJson = $this->appJson($inputObj,$order['order_id']);
        $html = <<<EOF
    <script src="/static/js/ios.js"></script>
	<script type="text/javascript">
	//调用微信JS api 支付
	var u = navigator.userAgent;
	var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1;
	if(isAndroid){
	    window.auc.wxPay('$appJson',"$return_url"); //安卓调用方式
	}else{
	    window.app.wxPay('$appJson',"$return_url");//IOS调用方式
	}
	</script>
EOF;
        return $html;
    }
    /**
     * 服务器点对点响应操作给支付接口方调用
     *
     */
    function response()
    {
        $post = file_get_contents('php://input');
        trace('xml:'.$post,'debug');
        require_once(EXTEND_PATH."payment/weixin/example/notify.php"); // 微信扫码支付demo 中的文件
        $notify = new \PayNotifyCallBack();
        $notify->Handle(false);       
    }

    // 微信订单退款原路退回
    public function refund($data){
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
            }
            return true;
        }else{
            return false;
        }

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

    public function appJson($data,$order_id){
        $a = ['status'=>200,"message"=> "成功",
            'data'=>[
                'prepay'=>$data,
                'orderId'=>null,
            ],
        ];
        return json_encode($a);

    }
}