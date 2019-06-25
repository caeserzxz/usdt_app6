<?php
/*------------------------------------------------------ */
//-- 微信API核心调用
//-- @author iqgmy
/*------------------------------------------------------ */
namespace app\weixin\model;
use app\BaseModel;
use think\facade\Cache;
class MiniModel extends BaseModel {
	public $fromUsername,$toUsername,$keyword,$SetConfig;

    /*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->setting = settings();
    }
	/*------------------------------------------------------ */
	//-- 验证是否微信请求
	/*------------------------------------------------------ */
	public function valid($echoStr = ''){
        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	/*------------------------------------------------------ */
	//-- 验证是否是微信请求
	/*------------------------------------------------------ */
	public function checkSignature(){       
        /*$fp = @fopen("1.txt","a+");
		@fwrite($fp,$this->SetConfig['token'].json_encode($_GET)); 
		fclose($fp);*/
		$tmpArr = array($this->SetConfig['weixin_token'], $_GET["timestamp"], $_GET["nonce"]);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );		
		return  $tmpStr == $_GET["signature"] ? true : false;		
	}

	/**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);
        curl_close($ch);
        return $data;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    public function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->setting['xcx_appid'];
        $urlObj["secret"] = $this->setting['xcx_appsecret'];
        $urlObj["js_code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/jscode2session?".$bizString;
    }


    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    public function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /*------------------------------------------------------ */
	//-- 小程序获取access_token
	/*------------------------------------------------------ */
	function getAccessToken($curlagain = false){
		$access_token = Cache::get('xcx_access_token');
		if (empty($access_token) || $curlagain == true){			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->setting['xcx_appid']."&secret=".$this->setting['xcx_appsecret']);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $par['data']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$tmpInfo = curl_exec($ch);
			if (curl_errno($ch)) {
				return curl_error($ch);
			}
			curl_close($ch);

			$josn = json_decode($tmpInfo,true);
			$access_token = $josn['access_token'];	
			Cache::set('xcx_access_token',$access_token,3600);		
		}
		return $access_token;
	}

	/*------------------------------------------------------ */
	//-- 小程序生成二维码
	//-- $page  	跳转的页面
	//-- $scene 	场景值
	/*------------------------------------------------------ */
	public function get_qrcode($page = '',$scene = '',$method='POST'){

		$ACCESS_TOKEN = $this->getAccessToken(true);
		$url ="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$ACCESS_TOKEN";
		$qrcode = array(
			'width'			=> 400,
			'page'			=> $page,
			'scene' 		=> $scene
		);

		$data = json_encode($qrcode);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			return curl_error($ch);
		}
		curl_close($ch);
	    $base64_image ="data:image/jpeg;base64,".base64_encode($result);

	    return $base64_image;
	}
}

?>