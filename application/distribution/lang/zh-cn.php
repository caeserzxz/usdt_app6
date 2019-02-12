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
	//佣金相关
	'divdend_satus' => [
		config('config.DD_UNCONFIRMED') => '待处理',
		config('config.DD_CANCELED')    => '已取消',
		config('config.DD_SHIPPED') => '已发货',
		config('config.DD_SIGN') => '已签收,待分成',
		config('config.DD_RETURNED') => '已退货',
		config('config.DD_DIVVIDEND') => '已分成',
	]
	
];
