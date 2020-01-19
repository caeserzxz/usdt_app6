<?php
return array(
    'code'=> 'alipayMobile',
    'name' => '手机网站支付宝',
    'version' => '1.0',
    'author' => '宇宙人',
    'desc' => '手机端网站支付宝 ',
    'icon' => 'logo.jpg',
    'scene' =>1,  // 使用场景 0 PC+手机 1 手机 2 PC
    'config' => array(
        array('name' => 'alipay_account','label'=>'支付宝帐户','type'=>'text','value'=>'','is_must'=>1,'tip'=>''),
        array('name' => 'alipay_key','label'=>'交易安全校验码','type'=>'text','value'=>'','is_must'=>1,'tip'=>''),
        array('name' => 'alipay_partner','label'=>'合作者身份ID','type'=>'text','value'=>'','is_must'=>1,'tip'=>''),
        array('name' => 'transfer_alipay_partner','label'=>'退款APPID','type' => 'value','value' => '','is_must'=>0,'tip'=>''),
        array('name' => 'developer_private_key','label'=>'开发者私钥','type' => 'textarea','value' => '','is_must'=>0,'tip'=>''),
        array('name' => 'alipay_public_Key','label'=>'支付宝公钥','type' => 'textarea','value' => '','is_must'=>0,'tip'=>''),
        array('name' => 'alipay_pay_method','label'=>'选择接口类型','type'=>'select','option'=> array(
          '0' =>  '使用担保交易接口',
          '1' => '使用即时到帐交易接口',
        )),
        array('name' => 'is_bank','label'=>'是否开通网银','type' => 'select', 'option' => array(
            '0' => '否',
            '1' =>  '是',
        )),
        array('name'=>'website','label'=>'指定使用的网站域名','type' =>'text','value'=>'','is_must'=>1,
            'tip'=>'只填写域名，格式如：www.xxx.com，多个用|坚线隔开<br><b class="red">注：修改此项必需核实支付宝信息是否正确</b>')
    ),
);