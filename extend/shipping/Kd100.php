<?php
/*------------------------------------------------------ */
//-- 快递100接口
//-- @author iqgmy
/*------------------------------------------------------ */
namespace shipping;
$i = (isset($modules)) ? count($modules) : 0;
$modules[$i]["name"] = "快递100";
class Kd100{
     /*------------------------------------------------------ */
	//--  获取物流信息
	/*------------------------------------------------------ */
	public static function getLog($shipping_code,$invoice_no,$mobile=''){
		$express = array('YT'=>'yuantong','ST'=>'shentong','ZJS'=>'zhaijisong','EMS'=>'ems','ZT'=>'zhongtong','YD'=>'yunda','SF'=>'shunfeng','DBL'=>'debangwuliu','DBLKY'=>'debangwuliu');
		$return['code'] = 0;
		
		if (empty($express[$shipping_code])==true){
			$return['msg'] = '暂不支持当前快递物流查询，请前往官网查询！';
			return $return;
		}
		$url = "http://www.kuaidi100.com/query?type=".$express[$shipping_code]."&postid=".$invoice_no.'&temp=0.'.rand(10000000,99999999).'&phone='.substr($mobile,-4);

		$res = self::vget($url);

		$res = json_decode($res,true);
        print_r($res);
		if ($res['message'] != 'ok'){
			$return['msg'] = $res['message'];
			return $return;
		}
		$return['code'] = 1;
		$return['data'] = $res['data'];
		return $return;
	}
	 /*------------------------------------------------------ */
	//--  数据请求
	/*------------------------------------------------------ */
	public static function vget($url){ // 模拟获取内容函数
		  $header = array(
					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'Accept-Encoding:gzip, deflate',                                
					'Accept-Language:zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3',
					'Connection:keep-alive',
					'Host:www.kuaidi100.com',
			);   
		   $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0';
			$curl = curl_init(); // 启动一个CURL会话
			curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置HTTP头字段的数组
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
			curl_setopt($curl, CURLOPT_USERAGENT, $useragent); // 模拟用户使用的浏览器
			//curl_setopt($ch,CURLOPT_POSTFIELDS,$post); //发送POST数据
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
			curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
			curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的GET请求
			curl_setopt($curl, CURLOPT_COOKIE, 'Hm_lvt_22ea01af58ba2be0fec7c11b25e88e6c=1555330368; WWWID=WWWF5A988734DE61FC84FC0BF04746AE8DD; Hm_lpvt_22ea01af58ba2be0fec7c11b25e88e6c=1555989068; '); // 读取上面所储存的Cookie信息
			curl_setopt($curl, CURLOPT_ENCODING, "gzip"); // 关键在这里
			curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
			curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
			$tmpInfo = curl_exec($curl); // 执行操作
			if (curl_errno($curl)) {
					// echo 'Errno'.curl_error($curl);
			}
			curl_close($curl); // 关闭CURL会话
			return $tmpInfo; // 返回数据
	}

}


?> 