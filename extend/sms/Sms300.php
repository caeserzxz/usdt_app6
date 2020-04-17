<?php
/*------------------------------------------------------ */
//-- SMS300短信接口
//-- @author zc
/*------------------------------------------------------ */
namespace sms;
use app\mainadmin\model\SmsTplModel;
$i = (isset($modules)) ? count($modules) : 0;
$modules[$i]["name"] = "Sms300";
$modules[$i]["val"] = array('Sms_id'=>'机构代码','Sms_name'=>'帐户名','Sms_psw'=>'密码','Sms_sign'=>'【签名】带括');

class Sms300{
    protected $Sms_id;//机构代码
    protected $Sms_name;//帐户名
    protected $Sms_psw;//密码，支持使用明文或MD5加密大写
    protected $Sms_sign;//【签名】,带上括号

    /**
     * 构造方法
     * 
     * @param string $appKey    [description]
     * @param string $appSecret [description]
     */
    public function __construct($val){		
        $this->Sms_id    = $val['Sms_id'];
        $this->Sms_name    = $val['Sms_name'];
        $this->Sms_psw    = $val['Sms_psw'];
        $this->Sms_sign    = $val['Sms_sign'];
    }
	
	/*------------------------------------------------------ */
	//-- 发送短信
	/*------------------------------------------------------ */
	public function send($mobile,$tplCode,$smsParams = array()){
        $url = "http://124.172.234.157:8180/Service.asmx/SendMessage";
        $tpl_content = SmsTplModel::where('sms_tpl_code',$tplCode)->value('tpl_content');
        if (empty($tpl_content)) return $this->error('未找到短信模板.');
        $Message = $this->Sms_sign.str_replace('${code}',$smsParams['code'],$tpl_content);
        $params = array(
            'Id'        => $this->Sms_id,   //机构代码
            'Name'      => $this->Sms_name, //帐户名
            'Psw'       => $this->Sms_psw,  //密码，支持使用明文或MD5加密大写
            'Phone'     => $mobile,         //接受短信的用户手机号码
            'Message'   => $Message,        //短信内容
            'Timestamp' => 0,               //时间戳，可为0
        );
        $paramstring = http_build_query($params);
        $resualt = $this->smsCurl($url, $paramstring);
        if($resualt){
            return true;
        }else{
            return "发送失败";
        }
	}

    /**
     * SMS300发送短信
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    function smsCurl($url, $params = false, $ispost = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        $rearr = $this->xml_to_arr($response);
        if ($rearr['State'] == 1) {
            return true;
        }else{
            return false;
        }
    }

    /*------------------------------------------------------ */
    //-- XMS转数组
    /*------------------------------------------------------ */
    function xml_to_arr($xml){//xml字符串转数组
        $objectxml = simplexml_load_string($xml);//将文件转换成 对象
        $xmljson = json_encode($objectxml);//将对象转换个JSON
        $xmlarray = json_decode($xmljson, true);//将json转换成数组
        return $xmlarray;
    }

}


?> 