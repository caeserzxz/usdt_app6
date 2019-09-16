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
error_reporting(E_ERROR | E_PARSE );
return [
    // 是否开启多语言
    'lang_switch_on'         => true,
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 上传目录
	'_upload_'=> './upload/',
	'apikey'=>'eb5c6b3e4505c1fd7878bde2ed8544cf',
	'host_path'=> isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://'.$_SERVER['SERVER_NAME'] : 'http://'.$_SERVER['SERVER_NAME'],//指定域名
    'bind_max_level' => 50,//绑定指定层数关系链
	'dividend_level' => array(1=>'一级',2=>'二级',3=>'三级',4=>'四级',5=>'五级',6=>'六级',7=>'七级',8=>'八级',9=>'九级'),//提成等级

    //使用的分销模式
    'dividend_type' => 'sdtydw',
    //使用的分销模式
    'order_type'=>['0'=>'普通订单','1'=>'积分订单','2'=>'拼团订单','3'=>'秒杀订单','9'=>'售后订单'],
    /* 订单状态 */
    'OS_UNCONFIRMED' => 0,// 未确认
    'OS_CONFIRMED' => 1,// 已确认
    'OS_CANCELED' => 2,// 已取消
    'OS_RETURNED' => 3,// 退货
    'OS_MISSING' => 9,// 丢失
    /* 配送状态 */
    'SS_UNSHIPPED' => 0,// 未发货
    'SS_SHIPPED' =>   1,// 已发货
    'SS_SIGN' =>  2,// 已收货
    /* 支付状态 */
    'PS_UNPAYED' =>   0,// 未付款
    'PS_PAYED' => 1, // 已付款
    'PS_RUNPAYED' => 2, // 已退款

    //提成状态
    'DD_UNCONFIRMED' => 0, // 待处理
    'DD_PAYED' => 1, // 已支付
    'DD_SHIPPED' => 2, // 已发货
    'DD_SIGN' => 3, // 已签收,待分成
    'DD_DIVVIDEND' => 9, // 已分成
    'DD_RETURNED' => 11, // 退货
    'DD_CANCELED' => 10, // 已取消
     //商品状态
     'goods_status'=>['0'=>'未上架','1'=>'上架中','2'=>'自动上架','10'=>'审核中','11'=>'审核失败','12'=>'平台下架商品','13'=>'供应商下架'],
     //拼团状态
     'FG_WAITPAY' => 0,// 待支付
     'FG_DOING' => 1,// 拼团中
     'FG_FULL' => 2,// 拼团满员
     'FG_SEUCCESS' => 3,// 拼团成功
     'FG_FAIL' => 9,// 拼团失败

     //提现手续费扣除方式
     'fee_types'=>['0'=>'外扣','1'=>'内扣'],
];
