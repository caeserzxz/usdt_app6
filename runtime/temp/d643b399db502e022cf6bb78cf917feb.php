<?php /*a:3:{s:74:"D:\phpStudy\WWW\moduleshop\application\shop\view\sys_admin\order\info.html";i:1552272354;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\base.html";i:1552272354;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\page.html";i:1549953095;}*/ ?>
<?PHP header("Cache-Control:private"); ?>
<!DOCTYPE html>
<html lang="cn" class="app fadeInUp animated">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title><?php echo empty($title)?'后台管理系统':$title;?></title>
  
    <link rel="icon" type="image/png" href="/static/favicon.ico"/>
    
    <link rel="stylesheet" href="/static/main/css/app.css"/>
    <!--Basic Styles-->
    <link href="/static/main/css/stylesheets/bootstrap.min.css" rel="stylesheet" />
    <link href="/static/main/css/inside.css" rel="stylesheet">
    <link href="/static/awesome/css/font-awesome.min.css" rel="stylesheet" />
    <!--Beyond styles-->
    <link id="beyond-link" href="/static/main/css/stylesheets/beyond.min.css" rel="stylesheet" type="text/css" />
    <link href="/static/main/css/stylesheets/style.min.css" rel="stylesheet" />
    
    
    <script type="text/javascript" src="/static/js/jquery/jquery/1.8.3/jquery.min.js"></script>
    <script src="/static/js/inside.js"></script>
    
    <link rel="stylesheet" href="/static/main/css/public.css"/>
    <link href="/static/main/css/stylesheets/daterangepicker/daterangepicker-bs3.min.css" rel="stylesheet" />
	<script type="text/javascript">
    	var assets_path="/static",
		_version = "1.0.0",
		uploadJ = "<?php echo url('mainAdmin/attachment/editer_upload',array('ckv'=>editerUploadCkv())); ?>",
		fileManagerJ = "<?php echo url('mainAdmin/attachment/editer_manager'); ?>",
		searchUserUrl = "<?php echo url('member/sys_admin.users/pubSearch'); ?>",
		searchGoodsUrl = "<?php echo url('shop/sys_admin.goods/pubSearch'); ?>",
		regionUrl  = "<?php echo url('publics/api.region/getList'); ?>",
		order_by = '<?=empty($data["order_by"])?'':$data["order_by"];?>',
		sort_by = '<?=empty($data["sort_by"])?'':$data["sort_by"];?>',
		page_size = '<?=empty($data["page_size"])?'':$data["page_size"];?>';
		
		/**
 * app.js
 */
$(function () {
    /**
     * 点击侧边开关 (一级)
     */
    $('.switch-button').on('click', function () {
        var header = $('.tpl-header'), wrapper = $('.tpl-content-wrapper'), leftSidebar = $('.left-sidebar');
        if (leftSidebar.css('left') !== "0px") {
            header.removeClass('active') && wrapper.removeClass('active') && leftSidebar.css('left', 0);
        } else {
            header.addClass('active') && wrapper.addClass('active') && leftSidebar.css('left', -280);
        }
    });
    /**
     * 侧边栏开关 (二级)
     */
    $('.sidebar-nav-sub-title').click(function () {
        $(this).toggleClass('active');
    });

});
    </script>    
    
</head>
  

<body  >
<div class="am-g tpl-g">
    <!-- 头部 -->
    <header class="tpl-header">
        <!-- 右侧内容 -->
        <div class="tpl-header-fluid">
            <!-- 侧边切换 -->
            <div class="am-fl tpl-header-button switch-button">
                <i class="fa fa-bars"></i>
            </div>
           
           <?php if(is_array($top_menus) || $top_menus instanceof \think\Collection || $top_menus instanceof \think\Paginator): $i = 0; $__LIST__ = $top_menus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                
                    <div class="am-fl tpl-header-fun-button <?php echo $_module==$vo['key'] ? 'top_select' : 'top_no_select'; ?>">
                        <a href="<?php echo url($vo['key'].'/'.$vo['controller'].'/'.$vo['action']) ?>"><i class="fa <?php echo htmlentities($vo['icon']); ?>"></i> <?php echo htmlentities($vo['name']); ?></a>
                    </div>
              
           <?php endforeach; endif; else: echo "" ;endif; ?>
            
            
            <!-- 其它功能-->
            <div class="fr tpl-header-navbar">
                <ul>
                    <!-- 欢迎语 -->
                    <li class="am-text-sm tpl-header-navbar-welcome">
                        <a href="javascript:;">欢迎你，<span><?= $admin['info']['user_name'] ?></span>
                        </a>
                    </li>
                
                    <!-- 退出 -->
                    <li class="am-text-sm">
                        <a href="<?= url('mainAdmin/passport/logout') ?>">
                            <i class="fa fa-power-off"></i> 退出
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <!-- 侧边导航栏 -->
    <div class="left-sidebar">
        <?php $menus = $menus ?: []; ?>
        <!-- 一级菜单 -->
        <ul class="sidebar-nav">
            <li class="sidebar-nav-heading">后台管理</li>
            <?php foreach ($menus as $key => $item): 
                if (isset($item['submenu'])){
                    $vob = reset($item['submenu']);
                    $_url = url($vob['controller'].'/'.$vob['action']);
                }else{
                    $_url = isset($item['controller']) ? url($item['controller'].'/'.$item['action']) : 'javascript:void(0);';
                } 
            ?>
                <li class="sidebar-nav-link">
                    <a href="<?=$_url?>"
                       class="<?= $item['active'] ? 'active' : '' ?>">
                            <i class="fa sidebar-nav-link-logo <?php echo htmlentities($item['icon']); ?>" style=""></i> <?php echo htmlentities($item['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <!-- 子级菜单-->
        <?php 
        $second = isset($menus[$menus_group]['submenu']) ? $menus[$menus_group]['submenu'] : [];
        if (!empty($second)) : ?>
            <ul class="left-sidebar-second">
                <li class="sidebar-second-title hide"><?= $menus[$menus_group]['name'] ?></li>
                <li class="sidebar-second-item">
                    <?php foreach ($second as $item) :  if (!isset($item['submenu'])): ?>
                            <!-- 二级菜单-->
                            <a href="<?= url($item['controller'].'/'.$item['action']) ?>" class="<?= $item['active'] ? 'active' : '' ?>">
                                <?= $item['name']; ?>
                            </a>
                        <?php else: ?>
                            <!-- 三级菜单-->
                            <div class="sidebar-third-item">
                                <a href="javascript:void(0);"
                                   class="sidebar-nav-sub-title <?= $item['active'] ? 'active' : '' ?>">
                                    <i class="fa fa-sort"></i>
                                    <?= $item['name']; ?>
                                </a>
                                <ul class="sidebar-third-nav-sub">
                                    <?php foreach ($item['submenu'] as $third) : ?>
                                        <li>
                                            <a class="<?= $third['active'] ? 'active' : '' ?>"
                                               href="<?= url($third['controller'].'/'.$third['action']) ?>">
                                                &nbsp;&nbsp;├─<?= $third['name']; ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; endforeach; ?>
                </li>
            </ul>
        <?php endif; ?>
    </div>

    <!-- 内容区域 start -->
    <div class="tpl-content-wrapper <?= empty($second) ? 'no-sidebar-second' : '' ?>" >
    	<section class="vbox">
        	
<header>
    <div class="page-breadcrumbs">
        <ul class="breadcrumb">
            <li>
                <i class="fa fa-ellipsis-v"></i>
                <strong>订单详情</strong>
            </li>
        </ul>

        <a class="pull-right pointer p-r" data-toggle="back" title="返回"><i class="fa fa-reply"></i></a>
    </div>
</header>

<section class="scrollable  wrapper ">
    <form class="form-horizontal form-validate" method="post" action="" style="padding:0;">
        <div class="alert alert-warning ">

            <div class="table-responsive">
                <table>
                    <tr>
                        <td>
                            当前订单状态：【<?php echo htmlentities($orderInfo['ostatus']); ?>】<?php echo $orderLang['os'][$orderInfo['order_status']]; ?>,<?php echo $orderLang['ps'][$orderInfo['pay_status']]; ?>,<?php echo $orderLang['ss'][$orderInfo['shipping_status']]; ?>
                        </td>

                        <?php if(!(empty($operating['confirmed']) || (($operating['confirmed'] instanceof \think\Collection || $operating['confirmed'] instanceof \think\Paginator ) && $operating['confirmed']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('confirmed',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-toggle="ajaxCurl" class="btn btn-default btn-sm" data-msg="确定把订单设为确认？">确认订单</a>
                            </td>
                        <?php endif; if($operating['isCancel'] == '1'): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('cancel',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-toggle="ajaxRemove" data-msg="确定取消订单？" class="btn btn-default btn-sm"
                                   data-loading-text="取消中...">取消</a></td>
                        <?php endif; if(!(empty($operating['setUnPay']) || (($operating['setUnPay'] instanceof \think\Collection || $operating['setUnPay'] instanceof \think\Paginator ) && $operating['setUnPay']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('setUnPay',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-toggle="ajaxRemove" data-msg="确定设为未付款？" class="btn btn-default btn-sm"
                                   title="设为未付款">设为未付款</a></td>
                        <?php endif; if(!(empty($operating['returnPay']) || (($operating['returnPay'] instanceof \think\Collection || $operating['returnPay'] instanceof \think\Paginator ) && $operating['returnPay']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('returnPay',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-toggle="ajaxRemove" data-msg="确定设为退款，支付金额退回帐户余额？" class="btn btn-default btn-sm"
                                   title="设为退款">设为退款</a></td>
                        <?php endif; if(!(empty($operating['changePrice']) || (($operating['changePrice'] instanceof \think\Collection || $operating['changePrice'] instanceof \think\Paginator ) && $operating['changePrice']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('changePrice',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-toggle="ajaxModal" class="btn btn-default btn-sm" title="修改价格">修改价格</a></td>
                        <?php endif; if(!(empty($operating['cfmCodPay']) || (($operating['cfmCodPay'] instanceof \think\Collection || $operating['cfmCodPay'] instanceof \think\Paginator ) && $operating['cfmCodPay']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('cfmCodPay',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-toggle="ajaxModal" class="btn btn-default btn-sm" title="线下支付收款确认">线下支付收款确认</a>
                            </td>
                        <?php endif; if(!(empty($operating['shipping']) || (($operating['shipping'] instanceof \think\Collection || $operating['shipping'] instanceof \think\Paginator ) && $operating['shipping']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('shipping',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-toggle="ajaxModal" class="btn btn-default btn-sm" title="发货">发货</a></td>
                        <?php endif; if(!(empty($operating['unshipping']) || (($operating['unshipping'] instanceof \think\Collection || $operating['unshipping'] instanceof \think\Paginator ) && $operating['unshipping']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('unshipping',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-toggle="ajaxCurl" data-msg="确定把订单设为未发货？" class="btn btn-default btn-sm"
                                   title="未发货">设为未发货</a></td>
                        <?php endif; if(!(empty($operating['sign']) || (($operating['sign'] instanceof \think\Collection || $operating['sign'] instanceof \think\Paginator ) && $operating['sign']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;" data-remote="<?php echo url('sign',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-msg="确定把订单设为签收？" data-toggle="ajaxCurl" class="btn btn-default btn-sm"
                                   title="设为签收">设为签收</a></td>
                        <?php endif; if(!(empty($operating['unsign']) || (($operating['unsign'] instanceof \think\Collection || $operating['unsign'] instanceof \think\Paginator ) && $operating['unsign']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('unsign',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-msg="确定把订单设为未签收？" data-toggle="ajaxCurl" class="btn btn-default btn-sm"
                                   title="设为未签收">设为未签收</a></td>
                        <?php endif; if(!(empty($operating['returned']) || (($operating['returned'] instanceof \think\Collection || $operating['returned'] instanceof \think\Paginator ) && $operating['returned']->isEmpty()))): ?>
                            <td>&nbsp;</td>
                            <td><a href="javascript:;"
                                   data-remote="<?php echo url('returned',array('id'=>$orderInfo['order_id'])); ?>"
                                   data-msg="确定把订单设为退货状态？" data-toggle="ajaxCurl" class="btn btn-default btn-sm"
                                   title="设为退货">设为退货</a></td>
                        <?php endif; ?>

                    </tr>
                </table>
            </div>
        </div>
        <section class="panel panel-default">
            <header>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tabinfo" data-toggle="tab">订单信息</a></li>
                    <li><a href="#payinfo" data-toggle="tab">支付信息</a></li>
                    <li><a href="#tabdescribe" data-toggle="tab">物流信息</a></li>
                    <li><a href="#tabdividend" data-toggle="tab">分佣信息</a></li>
                    <li><a href="#log" data-toggle="tab">订单日志</a></li>

                </ul>
            </header>
            <div class="panel-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="tabinfo">
                        <div class="col-sm-12">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">订单编号：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities($orderInfo['order_sn']); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">下单时间：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities(dateTpl($orderInfo['add_time'])); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">支付时间：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities(dateTpl($orderInfo['pay_time'])); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">确定时间：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities(dateTpl($orderInfo['confirm_time'])); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">购买会员：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities($orderInfo['user_id']); ?> - <?php echo htmlentities(userInfo($orderInfo['user_id'])); ?>
                                    </div>
                                </div>


                            </div>
                            <div class="col-sm-6">


                                <div class="form-group">
                                    <label class="col-sm-3 control-label">发货时间：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities(dateTpl($orderInfo['shipping_time'])); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">签收/自提时间：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities(dateTpl($orderInfo['sign_time'])); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">退货时间：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities(dateTpl($orderInfo['returned_time'])); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">取消时间：</label>
                                    <div class="col-sm-5 form-control-static">
                                        <?php echo htmlentities(dateTpl($orderInfo['cancel_time'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label">买家留言：</label>
                                <div class="col-sm-8 form-control-static">
                                    <?php echo htmlentities($orderInfo['buyer_message']); ?>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-12 table-responsive">

                            <table class="table table-bordered ">
                                <thead>
                                <tr>
                                    <th>商品信息</th>
                                    <th>单价</th>
                                    <th>数量</th>
                                    <th>小计</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(is_array($orderInfo['goodsList']) || $orderInfo['goodsList'] instanceof \think\Collection || $orderInfo['goodsList'] instanceof \think\Paginator): $i = 0; $__LIST__ = $orderInfo['goodsList'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$grow): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td>

                                            <div class="media">
                                                        <span class="pull-left thumb-sm">
                                                            <img src="<?php echo htmlentities($grow['pic']); ?>" alt="John said"></span>
                                                <div class="media-body">
                                                    <div><?php echo htmlentities($grow['goods_name']); ?></div>
                                                    <div style="color:#999;"><?php echo htmlentities($grow['sku_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlentities(priceFormat($grow['goods_price'])); ?></td>
                                        <td><?php echo htmlentities($grow['goods_number']); ?></td>
                                        <td><?php echo htmlentities(priceFormat($grow['goods_price']*$grow['goods_number'])); ?></td>
                                    </tr>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                <tr>
                                    <td colspan="4" align="right">
                                        <p>商品价格：<?php echo htmlentities(priceFormat($orderInfo['goods_amount'])); ?></p>
                                        <p class="red">折扣：- <?php echo htmlentities(priceFormat($orderInfo['discount'])); ?></p>
                                        <p class="red">调整折扣：- <?php echo htmlentities(priceFormat($orderInfo['diy_discount'])); ?></p>
                                        <p class="red">优惠券：- <?php echo htmlentities(priceFormat($orderInfo['use_bonus'])); ?></p>
                                        <p>+ 运费：<?php echo htmlentities(priceFormat($orderInfo['shipping_fee'])); ?></p>
                                        <p>= 实收款：<?php echo htmlentities(priceFormat($orderInfo['order_amount'])); ?></p>
                                    </td>
                                </tr>

                                </tbody>
                            </table>

                        </div>
                    </div>

                    <div class="tab-pane" id="payinfo">
                        <div class="col-sm-12">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="col-sm-10 col-sm-offset-1">
                                        应付款:￥<?php echo htmlentities(priceFormat($orderInfo['order_amount'])); ?> <span class="m-l-lg">已收款:￥<?php echo htmlentities(priceFormat($orderInfo['money_paid'])); ?></span>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="col-sm-12 table-responsive">
                            <?php if($row['is_pay'] == '0'): ?>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th width="120">操作者</th>
                                        <th width="180">确认收款时间</th>
                                        <th width="120">支付金额</th>
                                        <th>备注</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><?php echo htmlentities($orderInfo['cfmpay_user']); ?></td>
                                        <td><?php echo htmlentities(dateTpl($orderInfo['pay_time'])); ?></td>
                                        <td><?php echo htmlentities(priceFormat($orderInfo['money_paid'])); ?></td>
                                        <td><?php echo htmlentities($orderInfo['pay_no']); ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th>支付方式</th>
                                        <th>支付金额</th>
                                        <th>支付时间</th>

                                        <th>支付流水号|商户订单号</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><?php echo htmlentities($orderInfo['pay_name']); ?></td>
                                        <td><?php echo htmlentities(priceFormat($orderInfo['money_paid'])); ?></td>
                                        <td><?php echo htmlentities(dateTpl($orderInfo['pay_time'])); ?></td>

                                        <td><?php echo htmlentities($orderInfo['pay_no']); ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="tab-pane" id="tabdescribe" style="overflow: hidden">
						<div class="form-group">
                            <label class="col-sm-1 control-label">收货人：</label>
                            <div class="col-sm-8 form-control-static">
                                <?php echo htmlentities($orderInfo['consignee']); ?>
                               
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">联系手机：</label>
                            <div class="col-sm-8 form-control-static">
                                <?php echo htmlentities($orderInfo['mobile']); ?>
                               
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">收货地址：</label>
                            <div class="col-sm-8 form-control-static">
                                【<?php echo htmlentities($orderInfo['merger_name']); ?>】 
                                <?php echo htmlentities($orderInfo['address']); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">要求配送：</label>
                            <div class="col-sm-8 form-control-static">

                                <?php if($row['shipping_fee'] == '0'): ?>
                                免运费
                                <?php else: ?>
                                <?php echo htmlentities(priceFormat($orderInfo['shipping_fee'])); endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">买家留言：</label>
                            <div class="col-sm-8 form-control-static">
                                <?php echo htmlentities($orderInfo['buyer_message']); ?>
                            </div>
                        </div>
                        <?php if($orderInfo['shipping_status'] > '0'): ?>
                            <div class="form-group">
                                <label class="col-sm-5 control-label">快递公司：</label>
                                <div class="col-sm-8 ">
                                    <label><?php echo htmlentities($orderInfo['shipping_name']); ?> 快递单号：<?php echo htmlentities($orderInfo['invoice_no']); ?></label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-5 control-label"></label>
                                <div class="col-sm-8 shipping_log">
                                 <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <td colspan="4">数据加载中...</td>
                                      </tr>
                                    </thead>
                                    <tbody>                                    
                                    </tbody>
                                </table></div>
                            </div>
                            <script type="text/javascript">
                            	$(function(){
									var _obj = $('.shipping_log');
									jq_ajax('<?php echo url("shop/api.shipping/getLog"); ?>','order_id=<?php echo htmlentities($orderInfo['order_id']); ?>',function(res){
										if (res.code == 0){
											_obj.find('thead td').html(res.msg);
											return false;
										}
										_obj.find('thead').remove();
										$.each(res.data,function(i,v){
											_obj.find('tbody').append('<tr><td>'+v.time+'</td><td >'+v.context+'</td></tr>');
										})
									})	
								})
                            </script>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane" id="tabdividend">
                        <div class="col-sm-12">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="col-sm-9 col-sm-offset-1">
                                        订单金额：￥<?php echo htmlentities(priceFormat($orderInfo['order_amount'])); ?> <span class="m-l-lg">总提成金额：￥<?php echo htmlentities(priceFormat($orderInfo['dividend_amount'])); ?></span>
                                        <span class="m-l-lg hide">实收：<?php echo htmlentities(priceFormat($orderInfo['real_amount'])); ?></span>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="col-sm-12 table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>用户ID</th>
                                    <th>分佣身份</th>
                                    <th>奖项相关</th>
                                    <th>金币</th>
                                    <th>旅游豆</th>
                                    <th>分佣状态</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(is_array($dividend_log) || $dividend_log instanceof \think\Collection || $dividend_log instanceof \think\Paginator): $i = 0; $__LIST__ = $dividend_log;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$dlog): $mod = ($i % 2 );++$i;?>
                                    <tr>
                                        <td><?php echo htmlentities($dlog['dividend_uid']); ?>-<?php echo htmlentities(userInfo($dlog['dividend_uid'])); ?></td>
                                        <td><?php echo htmlentities($dlog['role_name']); ?></td>
                                        <td><?php echo htmlentities($dlog['award_name']); ?> - <?php echo htmlentities($dlog['level_award_name']); ?></td>
                                        <td><?php echo htmlentities(priceFormat($dlog['dividend_amount'])); ?></td>
                                        <td><?php echo htmlentities(priceFormat($dlog['dividend_bean'])); ?></td>
                                        <td><?php echo htmlentities($orderLang['ds'][$dlog['status']]); ?></td>
                                    </tr>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                    <div class="tab-pane" id="log">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th width="150">记录时间</th>
                                <th width="120">操作者</th>
                                <th width="100">订单状态</th>
                                <th width="100">支付状态</th>
                                <th width="100">发货状态</th>
                                <th>备注</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($orderLog) || $orderLog instanceof \think\Collection || $orderLog instanceof \think\Paginator): $i = 0; $__LIST__ = $orderLog;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$log): $mod = ($i % 2 );++$i;?>
                                <tr>
                                    <td><?php echo htmlentities(dateTpl($log['log_time'])); ?></td>
                                    <td><?php echo htmlentities(adminInfo($log['admin_id'])); ?></td>
                                    <td><?php echo htmlentities($orderLang['os'][$log['order_status']]); ?></td>
                                    <td><?php echo htmlentities($orderLang['ps'][$log['pay_status']]); ?></td>
                                    <td><?php echo htmlentities($orderLang['ss'][$log['shipping_status']]); ?></td>
                                    <td><?php echo htmlentities($log['log_info']); ?></td>
                                </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>


        </section>

    </form>
</section>



            <?php if(!(empty($data['page_size']) || (($data['page_size'] instanceof \think\Collection || $data['page_size'] instanceof \think\Paginator ) && $data['page_size']->isEmpty()))): ?>
<footer class="footer bg-white b-t">
    <div class="row text-center-xs ">
        <div class="dropdown-box fl m-l m-t">
                 <a data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle">
                      <span class="dropdown-label"><?php echo htmlentities($data['page_size']); ?></span>
                      <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-select dropdown-select-50">
                      <li class="<?php echo htmlentities(tplckval($data['page_size'],10,'active')); ?>">
                          <a href="javascript:;">
                              <input type="radio" name="pageSize" value="10" <?php echo htmlentities(tplckval($data['page_size'],10,'checked')); ?>  />10</a>
                      </li>
                      <li class="<?php echo htmlentities(tplckval($data['page_size'],20,'active')); ?>">
                          <a href="javascript:;">
                              <input type="radio" name="pageSize" value="20" <?php echo htmlentities(tplckval($data['page_size'],20,'checked')); ?>/>20</a>
                      </li>
                      <li class="<?php echo htmlentities(tplckval($data['page_size'],50,'active')); ?>">
                          <a href="javascript:;">
                              <input type="radio" name="pageSize" value="50"  <?php echo htmlentities(tplckval($data['page_size'],50,'checked')); ?>/>50</a>
                      </li>
                      <li class="<?php echo htmlentities(tplckval($data['page_size'],100,'active')); ?>">
                          <a href="javascript:;">
                              <input type="radio" name="pageSize" value="100" <?php echo htmlentities(tplckval($data['page_size'],100,'checked')); ?>/>100</a>
                      </li>
                  </ul>
        </div>
        <div class="col-md-3 hidden-sm fl m-t">
        
            <p class="text-muted  fl" >总共<?php echo htmlentities(intval($data['total_count'])); ?>条,共<?php echo htmlentities(intval($data['page_count'])); ?>页</p>
        </div> 
        <div class="col-md-6 col-sm-12 text-right text-center-xs fr ">
            <ul class="pagination pagination-sm m-t-sm m-b-none" data-pages-total="<?php echo htmlentities($data['page_count']); ?>" data-page-current="<?php echo htmlentities($data['page']); ?>"></ul>
        </div>
    </div>
</footer>
<?php endif; ?>
    	</section>
    </div>
    <!-- 内容区域 end -->

</div>
<script src="/static/js/layer/layer.js"></script>
<script src="/static/js/art-template.js"></script>
<script src="/static/js/app.js"></script>
<script src="/static/assets/sea.js"></script>
<script src="/static/assets/seajs_config.js"></script>
<script type="text/javascript">
	seajs.use("dist/application/app.js");	
</script>

</body>
</html>