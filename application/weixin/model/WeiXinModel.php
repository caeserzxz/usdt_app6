<?php
/*------------------------------------------------------ */
//-- 微信API核心调用
//-- @author iqgmy
/*------------------------------------------------------ */
namespace app\weixin\model;
use app\BaseModel;
use think\facade\Cache;
class WeiXinModel extends BaseModel {
	public $fromUsername,$toUsername,$keyword,$SetConfig;

    /*------------------------------------------------------ */
    //-- 优先自动执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->SetConfig = settings();
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
	// 打印log
	function  log_result($file,$word) {
	    $fp = fopen($file,"a");
	    flock($fp, LOCK_EX) ;
	    fwrite($fp,"执行日期：".strftime("%Y-%m-%d %H:%M:%S",time())."\n".$word."\n\n");
	    flock($fp, LOCK_UN);
	    fclose($fp);
	}
	/*------------------------------------------------------ */
	//-- 获取微信请求类型
	/*------------------------------------------------------ */
	public function getMsg(){	
		if ($this->checkSignature() == false) die('无效请求！');
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if(!$postStr){  
            $postStr = file_get_contents("php://input");  
        }  
		if (!empty($postStr)){                
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			
			$this->fromUsername = trim($postObj->FromUserName);
			$this->toUsername = trim($postObj->ToUserName);
			
			if ($postObj->MsgType == 'text'){
				$this->keyword =  trim($postObj->Content);
			}elseif ($postObj->MsgType == 'event'){
				if(trim($postObj->Event) == 'SCAN'){
					$this->keyword = trim($postObj->Event);
					$this->eventkey = trim($postObj->EventKey);
				}elseif (trim($postObj->Event) == 'subscribe'){ 
					$this->keyword = trim($postObj->Event);
					if($postObj->EventKey) $this->eventkey = trim($postObj->EventKey);
				}else{ $this->keyword = empty($postObj->EventKey) ? trim($postObj->Event) : trim($postObj->EventKey);}
			}
		}
	}
	/*------------------------------------------------------ */
	//-- 封装微信回复格式
	/*------------------------------------------------------ */
    public function responseMsg($arr='')
    {
		if (empty($arr)) exit;
      	//extract post data
		$time = time();
			
		$resultStr = "<xml>
				<ToUserName><![CDATA[".$this->fromUsername."]]></ToUserName>
				<FromUserName><![CDATA[".$this->toUsername."]]></FromUserName>
				<CreateTime>".$time."</CreateTime>";
		foreach ($arr as $key=>$val)
		{
			if ($key == 'Articles')
			{				
				$resultStr .= "\n<ArticleCount>".count($val)."</ArticleCount>";	
				$resultStr .= "\n<Articles>";			
				foreach($val as $keyc=>$valb)
				{
					$resultStr .= "\n<item>";
					foreach($valb as $keyc=>$valc)
					{
						$resultStr .= "\n<".$keyc."><![CDATA[".$valc."]]></".$keyc.">";
					}
					$resultStr .= "\n</item>";
				}
				$resultStr .= "\n</Articles>";			
			}else{
				$resultStr .= "\n<".$key."><![CDATA[".$val."]]></".$key.">";
			}
		}
		$resultStr .= "<FuncFlag>0</FuncFlag>
				</xml>";				
		//$resultStr = sprintf($textTpl, $this->fromUsername, $this->toUsername, $time, $msgType, $contentStr,$url);
		$resultStr = str_replace('[openid]',$this->fromUsername, $resultStr);
		echo $resultStr;
		exit;
		
		
      
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
	/*------------------------------------------------------ */
	//-- 获取access_token
	/*------------------------------------------------------ */
	function getAccessToken($curlagain = false){
		$access_token = Cache::get('weixin_access_token');
		if (empty($access_token) || $curlagain == true){			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->SetConfig['weixin_appid']."&secret=".$this->SetConfig['weixin_appsecret']);
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
			Cache::set('weixin_access_token',$access_token,3600);		
		}
		return $access_token;
	}
	// ------------------------------------------
	// -- 生成分享所需参数（调用时需修改此方法）
	// ---------------------------------------------------
	public function getSignPackage($shareUrl = '') {
		if (empty($shareUrl)) return false;		
		$jsapiTicket = $this->getJsApiTicket();
		$timestamp = time();
		$nonceStr = $this->createNonceStr();
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=".$shareUrl;
		$signature = sha1($string);
		$signPackage = array(
			"appId"     => $this->SetConfig['weixin_appid'],
			"timestamp" => $timestamp,
			"nonceStr"  => $nonceStr,
			"signature" => $signature,
			"rawString" => $string,
			"shareUrl"  => $shareUrl
		);
		return $signPackage; 
	}
	// ------------------------------------------
	// -- 根据access_token获取ticket（调用时需修改此方法，需要先获取到plc_id）
	// ---------------------------------------------------
	public function getJsApiTicket() {
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) return false;
		$ticket = Cache::get('weixin_ticket');
		if (!$ticket){
			$accessToken = $this->getAccessToken();
			// 如果是企业号用以下 URL 获取 ticket
			// $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
			$josn = file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken");
			$josn = json_decode($josn,true);
			$ticket = $josn['ticket'];
			Cache::set('weixin_ticket',$ticket,600);
			return $ticket;
		}
		return $ticket;
	}
	/*------------------------------------------------------ */
	//-- 生成随机码
	/*------------------------------------------------------ */
	private function createNonceStr($length = 16){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++){
		  $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	/*------------------------------------------------------ */
	//-- 获取微信用户信息，非关注也可以调用
	/*------------------------------------------------------ */
	function getWxUserInfo($access_token,$curlagain = false){
		if (empty($access_token)) return array();
		$userinfo = file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token['access_token']."&openid=".$access_token['openid']."&lang=zh_CN");
		$userinfo = json_decode($userinfo,true);
		
		if ($userinfo['errcode'] == '40001'){		
			$ref = file_get_contents("https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$this->SetConfig['weixin_appid']."&grant_type=refresh_token&refresh_token=".$access_token['refresh_token']);
			$ref = json_decode($ref,true);
			if (empty($ref['access_token']) == false && $curlagain == false){
				return $this->getWxUserInfo($ref,true);
			}			
		}elseif ($userinfo['errcode'] == '41001' && $curlagain == false){				
			 return $this->getWxUserInfo($access_token,true);
		}
		
		return $userinfo;
	}
    /*------------------------------------------------------ */
    //-- 获取微信用户信息关注才能调用
    /*------------------------------------------------------ */
    function getWxUserInfoSubscribe($openid){
        $access_token = $this->getAccessToken();
        $userinfo = file_get_contents("https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN");
        $userinfo = json_decode($userinfo,true);
        return $userinfo;
    }
	/*------------------------------------------------------ */
	//-- 获取微信openid
	/*------------------------------------------------------ */
	function getWxOpenId(){
		$code = input('code','','trim');
		if (empty($code)){
			//获取code
			$redirect_uri = urlencode(getUrl());
			//$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->SetConfig['weixin_appid'].'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state=oauth&connect_redirect=1#wechat_redirect';
			$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->SetConfig['weixin_appid'].'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
			return header("location:".$url);
		}
		//获取access_token
	     $access_token = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->SetConfig['weixin_appid']."&secret=".$this->SetConfig['weixin_appsecret']."&code=".$code."&grant_type=authorization_code");		
	   
		return json_decode($access_token,true);
	}
	
	
	/*------------------------------------------------------ */
	//-- 发送post请求
	//-- yxb add by 20150409
	//--
	//-- $par
	//--	url		请求地址
	//--	data	post参数（json格式）
	//--
	//-- @return array
	/*------------------------------------------------------ */
	private function _curl($par,$curlagain = false){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $par['url']);
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
		$tmpInfo = json_decode($tmpInfo,true);
		return $tmpInfo;
	}
	/*------------------------------------------------------ */
	//-- 提交处理
	/*------------------------------------------------------ */
	function weiXinCurl($url,$data,$curlagain = false,$retunarr = false){
		 if (is_array($data) && $retunarr == false) $data = urldecode(jsonEncode($data));
		 $access_token = $this->getAccessToken($curlagain);
		 $ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, $url."access_token=".$access_token);
		 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		 curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 $tmpInfo = curl_exec($ch);
		 if (curl_errno($ch)) {
		  	return curl_error($ch);
		 }
		 curl_close($ch);
		 $tmpInfo = json_decode($tmpInfo,true);
		 //如果access_token expired无效，清除access_token记录再试一次提交
		 if (in_array($tmpInfo['errcode'],array('42001','41001','40001')) && $curlagain == false){			
			 return $this->weiXinCurl($url,$data,true);
		 }
		 $err_arr['-1'] = '系统繁忙，此时请开发者稍候再试 ';
		 if ($err_arr[$tmpInfo['errcode']]) $tmpInfo['errmsg'] = $err_arr[$tmpInfo['errcode']];
		 return $tmpInfo;	
	}
}

?>