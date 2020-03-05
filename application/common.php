<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 语言包定义
 * @param $cn_msg string 中文提示
 * @param $keys array 提示对应key值
 * @param $param array 替换参数
 * @return string
 */
function langMsg($cn_msg,$keys = [],$param = []){
    if (empty($keys) == true){//没有设置提示对应key值，直接返回中文
        return $cn_msg;
    }
    if (defined('LANG') == false || LANG == 'cn'){//未设置语言常量
        return $cn_msg.'-'.LANG;
    }

    $keys = explode('.',$keys);
    $langFile = dirname(__DIR__) . '/application/'.$keys[0].'/lang/'.LANG.'.php';
    if (is_file($langFile) == false){//语言包不存在
        return $cn_msg;
    }
    $lang = include($langFile);
    unset($keys[0]);
    foreach ($keys as $key){
        if (empty($lang[$key])){
            return $cn_msg;
        }
        $lang = $lang[$key];
    }
    if (empty($param)){
        return $lang;
    }
    $replace = [];
    foreach ($param as $key=>$val){
        $replace[] = '$'.($key+1);
    }
    return str_replace($replace,$param,$lang);
}
/**
 * 驼峰命名转下划线命名
 * @param $str
 * @return string
 */
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

/**
 * @param $url 链接直址
 * @param array $arr 参数
 * @param bool $isNotHtml 是否带html结尾
 * @param bool $domain 是否补全域名
 * @param bool $replaceAdmin 是否过滤后台zpadmin.php路径
 * @return mixed
 */
function _url($url,$arr=[],$isNotHtml=true,$domain = false,$replaceAdminPath=false){

    if ($domain === '/'){
        $url = url($url,$arr,$isNotHtml,false);
    }else{
         $url = url($url,$arr,$isNotHtml,$domain);
    }
    if (empty($domain) == false || $replaceAdminPath == true){
        $url = str_replace($_SERVER['SCRIPT_NAME'],'',$url);
    }

	return str_replace(array('%E3%80%90','%E3%80%91','%5B%5B','%5D%5D','%5B','%5D'),array("'+","+'",'{{','}}','[',']'),$url);
}
/**
 * 获取当前页面完整URL地址，前台调用
 *
 */
function getUrl($val='',$type='',$var=array()) {
    if (strstr($val,'http:')) return $val;
    if($type == 'img'){
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        return $sys_protocal.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '').str_replace('./','',$val);
    }
    $var['share_token'] = $GLOBALS['userInfo']['token'];
    return url($val,$var,false,true);
}
/**
 * 后台生成密码hash值
 * @param $password
 * @return string
 */
function _hash($password)
{
    return md5(md5($password) . 'main_salt_zpTRx');
}

/**
 * 前台生成密码hash值
 * @param $password
 * @return string
 */
function f_hash($password)
{
    return md5('@by_'.md5(md5($password).'pwd@2019'));
}
//验证手机号码
function checkMobile($phone = ''){
    $preg_phone='/^1\d{10}$/ims';
    if(preg_match($preg_phone,$phone)){
        return true;
    }
    return false;

}
/*------------------------------------------------------ */
//-- 联系电话隐藏,156****312
/*------------------------------------------------------ */
function mobileSubstr($phone = '', $strlen = false)
{
    if (empty($phone) == true) return '无记录';
    if (is_numeric($phone) == false) return $phone;
    if (strlen($phone) <= 6) return $phone;
    $phone_back =  substr_replace($phone, "*****", strlen($phone)-8, 5);

    if ($strlen)   $phone_back .= ' ［长度：'.strlen($phone).'］';

    return $phone_back;
}
/*------------------------------------------------------ */
//-- 过滤掉emoji表情
/*------------------------------------------------------ */ 
function repEmoji($str){
    $str = preg_replace_callback( '/./u',function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },$str);
     return $str;
}
/*------------------------------------------------------ */
//-- 获取会员信息
/*------------------------------------------------------ */ 
function userInfo($user_id,$return=true){
	static $userList;
	static $userModel;
	if ($user_id < 1) return $return == true ? '--' : [];
	if (!isset($userModel)){
		 $userModel = model('app\member\model\UsersModel');	
	}
	if (!isset($userList[$user_id])){
		$userList[$user_id] = $userModel->info($user_id);
	}
	if (empty($userList[$user_id])) return $return == true ? '--' : [];
	$info = $userList[$user_id];
	if ($return == true) return $info['nick_name'];
	return $info;
}
/*------------------------------------------------------ */
//-- 获取会员等级
/*------------------------------------------------------ */ 
function userLevel($integral,$returnName=true){
	static $userLevelList;	
	if (!isset($userLevelList)){
		 $Model = model('app\member\model\UsersLevelModel');	
		 $userLevelList = $Model->getRows();
	}
	$level = array();
	foreach ($userLevelList as $row){
		if ($integral >= $row['min'] && $integral <= $row['max']){
			$level = $row;		
			break;
		}elseif ($row['max'] == 0){
			$level = $row;
			break;
		}
	}
	if ($returnName == true) return $level['level_name'];
	return $level;
}
/*------------------------------------------------------ */
//-- 格式化价格
//-- @access  public
//-- @param   float   $price  价格
//-- @return  string
/*------------------------------------------------------ */
function priceFormat($price,$show_yuan = false,$type=0){    
	switch ($type){
		case 0:
			$price = number_format($price, 2, '.', '');
			break;
		case 1: // 保留不为 0 的尾数
			$price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));
			if (substr($price, -1) == '.') $price = substr($price, 0, -1);
			break;
		case 2: // 不四舍五入，保留1位
			$price = substr(number_format($price, 2, '.', ''), 0, -1);
			break;
		case 3: // 直接取整
			$price = intval($price);
			break;
		case 4: // 四舍五入，保留 1 位
			$price = number_format($price, 1, '.', '');
			break;
		case 5: // 先四舍五入，不保留小数
			$price = round($price);
			break;
	}   

    if($show_yuan == false) return sprintf("%s", $price);
	else return sprintf("￥%s元", $price);
}
//价格拆分显示
function priceShow($price){
    $price = explode('.',$price);
    return '<span>￥</span><small>'.$price[0].'</small>.<span>'.$price[1].'</span>';
}

/**
 * curl请求指定url
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
/*------------------------------------------------------ */
//-- 模板自定义判断
//-- val 传入值
//-- dval  判断值
//-- rval 处理返回
//-- default 是否默认
/*------------------------------------------------------ */
function tplckval($val='',$dval='',$rval='',$default = false){	
	if ($val !== 0){
		if (empty($val) == true && $default === true) return $rval;
		if (empty($dval) == true) return '';
	}
	
	if (is_array($dval)) return (in_array($val,$dval))?$rval:'';
	
	if ($val === $dval) return $rval;
	
	if (strstr($dval,'=')){
		$dval = explode('=',$dval);	
		return ($val == $dval[1]) ? $rval : $default;
	}
	if (strstr($dval,'<>')){
		$dval = explode('<>',$dval);
		return ($val <> $dval[1]) ? $rval : $default;
	}
	if (strstr($dval,'>=')){
		$dval = explode('>=',$dval);
		return ($val >= $dval[1]) ? $rval : $default;
	}
	if (strstr($dval,'>')){
		$dval = explode('>',$dval);
		return ($val > $dval[1]) ? $rval : $default;
	}
	if (strstr($dval,'<=')){
		$dval = explode('<=',$dval);
		return ($val <= $dval[1]) ? $rval : $default;
	}
	if (strstr($dval,'<')){
		$dval = explode('<',$dval);
		return ($val < $dval[1]) ? $rval : $default;
	}
	
}
/*------------------------------------------------------ */
//-- 模板中调用，将GMT时间戳格式化为用户自定义时区日期
/*------------------------------------------------------ */
function dateTpl($time = '',$format = '',$return_false = false){    
	if ($format === true){ 
		$time = time();
		$format = 'Y-m-d H:i';
	}elseif (empty($time)){
		 return ($return_false == false) ? '没有记录' : '';
	}
	if (empty($format)){
		$format = 'Y-m-d H:i';
	}
    if ($return_false == true){
        return date($format, $time);
    }
	if (date('Y') == date('Y', $time)){
        $format = str_replace('Y-','',$format);
    }else{
        $format = 'Y-m-d';
    }
    return date($format, $time);
}
/*------------------------------------------------------ */
//-- 返回一个带子级别的数组
//--@Param $rows 数组源; 设置：必要;
//--@Param $parent_id 顶级pid; 设置：不需要;
//--@Param $leve 默认层级;设置; 不需要;
//--@Param $newrows 递归子类的id; 设置：不需要;
/*------------------------------------------------------ */
function returnRows($rows,$pid = 0,$level = 1){	
	static $newrows = array();
	if ($level == 1) $newrows = array();  
	$icon = array('&nbsp;&nbsp;│ ','&nbsp;&nbsp;├─ ','&nbsp;&nbsp;└─ ');
	$now_id = 0;
	foreach ($rows as $key=>$row){
		$_pid = isset($row['pid'])?$row['pid']:0;
		if ($pid != $_pid) continue;	
		if (isset($newrows[$row['id']])) continue;						
		$children = returnChildren($rows,$row['id']);
		$row['children'] = ($children) ? $row['id'].','.join(',',$children) : $row['id'];
		$row['level'] = $level;
		if ($level > 1){
			$now_icon = '';
			for($i=1;$i<$level;$i++){
				$now_icon .= ($i == $level-1) ? $icon[1] : $icon[0];
			}
			$row['icon'] = $now_icon;
		}else{
			$row['icon'] = '';	
		}
		
		$now_id = $row['id'];
		$newrows[$now_id] = $row;
		unset($rows[$key]);		
		$nc = count($newrows);
		if ($rows){
			 $newrows = returnRows($rows,$now_id,($level+1));
		}
		if (count($newrows) > $nc){
			$end_arr = end($newrows);
			if ($end_arr['icon']) $newrows[$end_arr['id']]['icon'] = str_replace($icon[1],$icon[2],$end_arr['icon']);
		}
	}
	if ($now_id > 0) $newrows[$now_id]['icon'] = str_replace($icon[1],$icon[2],$newrows[$now_id]['icon']);
	unset($rows);
	return $newrows;
}
function returnChildren(&$rows,$pid = 0){
	$newrows = array();
	foreach ($rows as $key=>$row){
        if(isset($row['pid']) == false) continue;
        if ($pid != $row['pid']) continue;
		$children = returnChildren($rows,$row['id']);
		if ($children) $row['id'] .= ','.join(',',$children);
		$newrows[] = $row['id'];
	}
	return $newrows;
}
/*------------------------------------------------------ */
//-- 返回一个带有缩进级别的数组
/*------------------------------------------------------ */
function returnRecArr(&$rows){
	$newrows = array();
	foreach ($rows as $key=>$row){
		$newrows[$row['pid']][$row['id']] = $row;
	}
	return $newrows;
}
/*------------------------------------------------------ */
//-- 将数组转换组下拉选项
//-- @param   array   $arr             所有的数组
//-- @param   int     $selected        选中项
//-- @param   boolean     $islimit     是否判断限制不可选
//-- @param   int     $level           返回等级
//-- @param   int     $kd_type           快递类型：0 快递100； 1 快递鸟
//-- @return  string
/*------------------------------------------------------ */
function arrToSel(&$rows = array(), $selected = 0, $islimit = false, $level = 0 ){
	$select = '';
	$selected = explode(',',$selected);
	foreach ($rows AS $key=>$val){
		if (is_array($val) == false){
			$selectedArr = (in_array($key,$selected)) ? "selected='selected'" : '';
			$select .= '<option value="'.$key.'" '.$selectedArr.'>'.$val.'</option>';
			 continue;	
		}
		if ($level > 0 && $val['level'] > $level) continue;		
		$select .= '<option ';
		if ($islimit === true && $val['children'] != $val['id'] ){
			$val['id'] = '';
			$select .=  ' style="background:#999;" ';
		}
		
		if (isset($val['status']) && $val['status'] == 0){
			$select .=  ' style="color:#CCC;" ';
		}elseif (isset($val['is_sys']) && $val['is_sys'] == 1){
			$select .=  ' style="color:#ff0000;"  ';
		}
	    $text = htmlspecialchars(strip_tags($val['name']));
		if (empty($val['dict_val']) == false){
			$select .= ' value="'.$val['ext_val'].'"  ';
			$selval = $val['ext_val'];
		}else{
			$select .= ' value="'.$val['id'].'" ';
			$selval = $val['id'];
			
		}
		$select .= (in_array($selval,$selected)) ? "selected='selected'" : '';
		$select .= ' data-text="'.$text.'"   data-children="' . $val['children'] . '"   label="'.$text.'" >';
		if (isset($val['icon'])) $select .= $val['icon'];
		$select .= $text;
		$select .= '</option>';
	}
	return $select;
}
/*------------------------------------------------------ */
//-- 判断是否json，是返回数组
/*------------------------------------------------------ */
function isJson($string) {
 $arr = json_decode($string,true);
 return (json_last_error() == JSON_ERROR_NONE) ? $arr : $string;
}
/*------------------------------------------------------ */
//-- 创建目录
/*------------------------------------------------------ */   
function makeDir($folder){
    $reval = false;
    if (!file_exists($folder)){
        /* 如果目录不存在则尝试创建该目录 */
        @umask(0);
        /* 将目录路径拆分成数组 */
        preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);
        /* 如果第一个字符为/则当作物理路径处理 */
        $base = ($atmp[0][0] == '/') ? '/' : '';
        /* 遍历包含路径信息的数组 */
        foreach ($atmp[1] as $val){
            if ('' != $val){
                $base .= $val;
                if ('..' == $val || '.' == $val){
                    /* 如果目录为.或者..则直接补/继续下一个循环 */
                    $base .= '/';
                    continue;
                }
            }else{continue;}
            $base .= '/';
            if (!file_exists($base)){
                /* 尝试创建目录，如果创建失败则继续循环 */
                if (@mkdir(rtrim($base, '/'), 0777)){
                    @chmod($base, 0777);
                    $reval = true;
                }
            }
        }
    }else{
        /* 路径已经存在。返回该路径是不是一个目录 */
        $reval = is_dir($folder);
    }
    clearstatcache();
    return $reval;
}
/*------------------------------------------------------ 
 * 校验日期格式是否正确
 * @param string $date 日期
 * @param string $formats 需要检验的格式数组
 * @return boolean
------------------------------------------------------ */
function checkDateIsValid($date, $formats = array("Y-m-d H:i:s","Y-m-d H:i")){
    $unixTime = strtotime($date);
    if (!$unixTime) return false;  //strtotime转换不对，日期格式显然不对
	 //校验日期的有效性，只要满足其中一个格式就OK
	if (!is_array($formats)) $formats = explode(',',$formats);
    foreach ($formats as $format) {
    	if (date($format, $unixTime) == $date)  return true;
	}
    return false;
}
/*------------------------------------------------------ */
//-- 系统配置读取
/*------------------------------------------------------ */
function settings($key = ''){
	static $settings;
	if (!isset($settings)){
		 $settings = model('app\mainadmin\model\SettingsModel')->getRows();
	}
	if (empty($key) == false){
		return isJson($settings[$key]);		
	}
	return $settings;
}
/*------------------------------------------------------ */
//-- 生成指定长度的随机字符串(包含大写英文字母, 小写英文字母, 数字)
//-- @param int $length 需要生成的字符串的长度
//-- @return string 包含 大小写英文字母 和 数字 的随机字符串
/*------------------------------------------------------ */
function random_str($length,$isupper = false){
    //生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
    $arr = $isupper ? array_merge(range('A','H'),range('J','M'),range('P','Z'),range(0,9)) : array_merge(range('A', 'Z'),range(0, 9), range('a', 'z'));
    $str = '';
    $arr_len = count($arr);
    for ($i = 0; $i < $length; $i++){
        $rand = mt_rand(0, $arr_len-1);
        $str.=$arr[$rand];
    }
    return $str;
}
/*------------------------------------------------------ */
//-- 时间转换计算
/*------------------------------------------------------ */
function timeTran($show_time) {  
    $dur = time() - $show_time;  
    if ($dur < 0) {  
        return '刚刚';  
    } 
	if ($dur < 60) {  
		return $dur . '秒前';  
	}
	if ($dur < 3600) {  
		return floor($dur / 60) . '分钟前';  
	} 
	if ($dur < 86400) {  
		return floor($dur / 3600) . '小时前';  
	} 
	if ($dur < 259200) {//3天内  
		return floor($dur / 86400) . '天前';  
	}
	return date("Y-m-d", $show_time); 
}
 /*------------------------------------------------------ */
// * 对银行卡号进行掩码处理
// * @param  string $bankCardNo 银行卡号
//* @return string             掩码后的银行卡号
/*------------------------------------------------------ */
function formatBankCardNo($bankCardNo){
	//截取银行卡号前4位
	$prefix = substr($bankCardNo,0,4);
	//截取银行卡号后4位
	$suffix = substr($bankCardNo,-4,4);
	$maskBankCardNo = $prefix." **** **** **** ".$suffix;
	return $maskBankCardNo;
}

/**
 * 获取url 中的各个参数  类似于 pay_code=alipay&bank_code=ICBC-DEBIT
 * @param type $str
 * @return type
 */
function parseUrlParam($str){
    $data = array();
    $str = explode('?',$str);
    $str = end($str);
    $parameter = explode('&',$str);
    foreach($parameter as $val){
        $tmp = explode('=',$val);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}
/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
      if(mb_strlen($string,'utf-8')>$length){
          $str = mb_substr($string, $start, $length,'utf-8');
          return $str.'...';
      }else{
          return $string;
      }
}

/*------------------------------------------------------ */
// * 获取数组中的某一列
// * @param array $arr 数组
//* *@param string $key_name  列名
// @return array  返回那一列的数组
/*------------------------------------------------------ */
function getArrColumn($arr, $key_name)
{
    $arr2 = array();
    foreach ($arr as $key => $val) {
        $arr2[] = $val[$key_name];
    }
    return $arr2;
}

/*------------------------------------------------------ */
//-- 保存网络图片到本地
//-- @param string $url 网络图片地址
//-- @return string $path 保存的路径及文件名 ./public/upload/headimg/s3f21sdf3s1ads.jpg
/*------------------------------------------------------ */
function downloadImage($url,$path){
    $ch=curl_init();
    $timeout=5;
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    $img=curl_exec($ch);
    curl_close($ch);

    $fp2=@fopen($path,'a');
    fwrite($fp2,$img);
    fclose($fp2);
    unset($img,$url);

    return true;
}

/**
* 获取用户真实IP
*/
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    $ip         =   'unknown';
    if ($ip !== 'unknown') return $ip[$type];
    if($_SERVER['HTTP_X_REAL_IP']){//nginx 代理模式下，获取客户端真实 IP
        $ip=$_SERVER['HTTP_X_REAL_IP'];
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的 ip
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的 ip 地址
    }else{
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    // IP 地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
* 过滤微信名里面的表情特殊符号
*/
function filterEmoji($str){
	$str = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $str);
	$str = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $str);
	$str = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $str);
	$str = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $str);
	$str = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $str);
	$str = str_replace(array('"','\''), '', $str);
	$str = preg_replace_callback( '/./u',function (array $match) {
      		return strlen($match[0]) >= 4 ? '' : $match[0];
    	},$str);
	$str  = addslashes(trim($str));
 	return $str;
}

/**
 * 获取当前页面完整URL地址，前台调用
 *
 */
function getWxBackUrl() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}

/**
 ** @desc 封装 curl 的调用接口，post的请求方式
 **/
function http_curl($url,$type='get',$res='json',$arr=''){
    //初始化curl
    $ch = curl_init();
    //设置curl的参数
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // post数据
    if($type=='post'){
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
    }
    //采集
    $output = curl_exec($ch);
    curl_close($ch);

    if($res=='json'){
        return json_decode($output,true);
    }
}

/**
 **获取买家订单状态
 **/
function get_buy_status($status){
    if($status==0){
        $str = '等待下单';
    }elseif($status==1){
        $str = '已拍下,等待您上传凭证';
    }elseif($status==2){
        $str = '已上传凭证,等待卖家确认';
    }elseif($status==3){
        $str = '申诉中,等待平台处理';
    }elseif($status==4){
        $str = '交易成功';
    }elseif($status==5){
        $str = '交易失败,您未及时上传凭证';
    }elseif($status==6){
        $str = '订单取消';
    }
    return  $str;
}

/**
 **获取卖家订单状态
 **/
function get_sell_status($status){
    if($status==0){
        $str = '等待下单';
    }elseif($status==1){
        $str = '买家已拍下,等待买家上传凭证';
    }elseif($status==2){
        $str = '买家已上传凭证,等待您确认';
    }elseif($status==3){
        $str = '申诉中,等待平台处理';
    }elseif($status==4){
        $str = '交易成功';
    }elseif($status==5){
        $str = '交易失败,挂卖取消';
    }elseif($status==6){
        $str = '订单取消';
    }

    return  $str;
}

/**
 **上传付款凭证
 **/
function upload_img($img_name){
    $file = request()->file($img_name);
    if($file){
        $info = $file->move("./".UPLOAD_PATH."/$img_name");
        if($info){
            $tmp_path = $info->getSaveName();
            $path = UPLOAD_PATH."/$img_name/".$tmp_path;
//            $path = substr($path, 9);
            return  $path;
        }else{
            return $info->getError();die;
        }
    }
}

//发送聚合断信
function send_sms($mobile,$tplCode){
    $sms_fun = settings('sms_fun');
    $sms = new \sms\Juhe($sms_fun['function_val']);
    $code = rand(1000,9999);
    $sms->send($mobile,$tplCode,['code'=>$code]);
}

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays ($day1, $day2)
{
    $second1 = strtotime($day1);
    $second2 = strtotime($day2);

    if ($second1 < $second2) {
        $tmp = $second2;
        $second2 = $second1;
        $second1 = $tmp;
    }
    return ($second1 - $second2) / 86400;
}