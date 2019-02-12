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
    // 注册页面
    'reg_title' => '注册',
    'reg_small_title'=>'创建一个账户',
    'reg_email'=>'请输入你的邮箱',
    'reg_code'=>'请输入验证码',
    'reg_pwd'=>'请输入密码',
    'reg_submit'=>'创建账户',
    'reg_get_code'=>'获取验证码',
    'reg_opt_too_fast'=>'操作太频繁，请稍后再试',
    'reg_success'=>'注册成功',
    'reg_fail'=>'请求失败，请重试',
    'reg_email_exist'=>'邮箱已被使用',
    'reg_fail_email'=>'邮件发送失败',
    'reg_fail_code'=>'验证码错误',
    'reg_code_send'=>'发送成功，请留意您的邮箱',
    'reg_msg_code'=>'验证码是：',
    'reg_msg_wel'=>'欢迎注册会员，',
    'reg_code_time'=>'验证码半个小时后将失效',
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
			$config['DD_UNCONFIRMED']=>'待处理',
			$config['DD_CANCELED']=>'已取消',
			$config['DD_SHIPPED']=>'已发货',
			$config['DD_SIGN']=>'已签收,待分成',
			$config['DD_RETURNED']=>'已退货',
			$config['DD_DIVVIDEND']=>'已到帐',
		]
	
	],
	
];
