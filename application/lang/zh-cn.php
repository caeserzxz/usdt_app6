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
$config = config('config.');
return [

	//订单相关
	'order' => [
		'os'=>[
			$config['OS_UNCONFIRMED']=>'未确认',
			$config['OS_CONFIRMED']=>'已确认',
			$config['OS_CANCELED']=>'<font color="red">取消</font>',
			$config['OS_RETURNED']=>'<font color="red">退货</font>',
			$config['OS_MISSING']=>'<font color="red">快递丢失</font'
		],
		'ps'=>[
			$config['PS_UNPAYED']=>'未付款',
			$config['PS_PAYED']=>'已付款',
			$config['PS_RUNPAYED']=>'已退款',
		],
		'ss'=>[
			$config['SS_UNSHIPPED']=>'未发货',
			$config['SS_SHIPPED']=>'已发货',
			$config['SS_SIGN']=>'已签收',
		],
		'ds'=>[
			$config['DD_UNCONFIRMED']=>'待确定',
            $config['DD_PAYED']=>'已支付',
			$config['DD_SHIPPED']=>'已发货',
			$config['DD_SIGN']=>'已签收,待分成',
            $config['DD_DIVVIDEND']=>'已到帐',
			$config['DD_RETURNED']=>'已退货',
            $config['DD_CANCELED']=>'已取消'

		]
	
	],
	
];
