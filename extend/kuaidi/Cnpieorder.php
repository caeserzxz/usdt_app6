<?php
namespace Kuaidi;
/**
 * Created by PhpStorm.
 * User: linyu
 * Date: 2019/08/1
 */
class Cnpieorder
{
    private $kd_id = null; //电商ID
    private $ke_appkey = null; //电商加密私钥，菜鸟提供，注意保管，不要泄漏
    private $ke_requrl = null; //请求url
    private $ApiName; //对应api名称
    private static $_instance;//容器

    private function __construct($kd_config = [])
    {
        $this->kd_id = isset($kd_config['kd_id']) ? $kd_config['kd_id'] : '';
        $this->ke_appkey = isset($kd_config['ke_appkey']) ? $kd_config['ke_appkey'] : '';
        $this->ke_requrl = isset($kd_config['ke_requrl']) ? $kd_config['ke_requrl'] : 'http://testapi.kdniao.com:8081/api/EOrderService';
    }

    /**
     * 入口方法
     * @array $kd_config 配置
     * @return object
     */
     static public function entrance($kd_config = [])
    {
       if(!(self::$_instance instanceof self)){
            self::$_instance = new self($kd_config);
        }
        return self::$_instance;
    }

    /**
     * 设置api名称
     * @string $name 设置菜鸟api名称
     * @return object
     */
    public function setApiName($name){
        $this->ApiName = trim($name);
        return self::$_instance;
    }

    /**
     * Json方式 发起请求
     * @param $orderdata
     * @return url响应返回的html
     */
    public function submitdata($orderdata)
    {
        //参数
        $msgType = $this->ApiName;//接口名称
        $logistics_interface = urlencode(json_encode($orderdata));//传递参数
        $digest = $this->encrypt($orderdata, $this->ke_appkey);//生成签名
        $cpCode = $this->kd_id; //调用电商CPCODE
        $toCode = ''; //调用的目标TOCODE，有些接口TOCODE可以不用填写

        //拼接数据url
        $datas = 'msg_type='.$msgType .'&to_code='.$toCode .'&logistics_interface='.$logistics_interface .'&data_digest='.urlencode($digest) .'&logistic_provider_id='.urlencode($cpCode);

        //发起请求
        $result = $this->curlSubmit($this->ke_requrl, $datas);

        return $result;
    }


    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    private function curlSubmit($url,$data)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POST, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    private function encrypt($data, $appkey)
    {
        return ubase64_encode(md5(json_encode($data).$appkey, true)); //生成签名;
    }

    /**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     * @param  string &$array 要处理的字符串
     * @param  string $function 要执行的函数
     * @return boolean $apply_to_keys_also     是否也应用到key上
     * @access public
     *
     *************************************************************/
    private function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }


    /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     * @param  array $array 要转换的数组
     * @return string      转换得到的json字符串
     * @access public
     *
     *************************************************************/
    private function JSON($array)
    {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }

}