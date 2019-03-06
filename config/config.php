<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 上传目录
	'_upload_'=> str_replace(Request::baseFile(),'',Request::root()).'upload/',
	'apikey'=>'eb5c6b3e4505c1fd7878bde2ed8544cf',
	'host_path'=> 'http://'.$_SERVER['SERVER_NAME'],//指定域名
	'dividend_level' => array(1=>'一级',2=>'二级',3=>'三级',4=>'四级',5=>'五级',6=>'六级',7=>'七级',8=>'八级',9=>'九级'),//提成等级
];
