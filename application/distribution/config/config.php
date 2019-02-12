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
   'DIVIDEND_LEVEL' => array(1=>'一级',2=>'二级',3=>'三级',4=>'四级',5=>'五级',6=>'六级',7=>'七级',8=>'八级',9=>'九级'),//提成等级
   //提成状态
	'DD_UNCONFIRMED' => 0, // 待处理
	'DD_CANCELED' => 1, // 已取消
	'DD_SHIPPED' => 2, // 已发货
	'DD_SIGN' => 3, // 已签收,待分成
	'DD_RETURNED' => 4, // 退货
	'DD_DIVVIDEND' => 9, // 已分成
];
