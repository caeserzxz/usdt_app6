<?php
namespace Kuaidi;
/**
 * Created by PhpStorm.
 * User: 农夫
 * Date: 2019/03/11
 * Time: 16:08
 */
class Kdapieorder
{
    private $kd_id = null; //电商ID
    private $ke_appkey = null; //电商加密私钥，快递鸟提供，注意保管，不要泄漏
    private $ke_requrl = null; //请求url，正式环境地址：http://api.kdniao.com/api/Eorderservice    测试环境地址：http://testapi.kdniao.com:8081/api/EOrderService

    public function __construct($kd_config = [])
    {
        $this->kd_id = isset($kd_config['kd_id']) ? $kd_config['kd_id'] : '';
        $this->ke_appkey = isset($kd_config['ke_appkey']) ? $kd_config['ke_appkey'] : '';
        $this->ke_requrl = isset($kd_config['ke_requrl']) ? $kd_config['ke_requrl'] : 'http://testapi.kdniao.com:8081/api/EOrderService';
    }

    /**
     * Json方式 调用电子面单接口
     * @param $orderdata
     * @return url响应返回的html
     */
    function submitEOrder($orderdata)
    {
        $requestData = json_encode($orderdata, JSON_UNESCAPED_UNICODE);
        $datas = array(
            'EBusinessID' => $this->kd_id,
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->ke_appkey);
        $result = $this->sendPost($this->ke_requrl, $datas);
        //根据公司业务处理返回的信息......
        return $result;
    }


    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    private function sendPost($url, $datas)
    {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if (empty($url_info['port'])) {
            $url_info['port'] = 80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader .= "Host:" . $url_info['host'] . "\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        $httpheader .= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets .= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    private function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
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