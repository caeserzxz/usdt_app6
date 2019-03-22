<?php /*a:3:{s:68:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\index\index.html";i:1553129665;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\base.html";i:1553129665;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\page.html";i:1552460091;}*/ ?>
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
		page_size = '';
		
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
        	
<script type="text/javascript" src="/static/js/highcharts/highcharts.js"></script>
<script type="text/javascript" src="/static/js/highcharts/highcharts_more.js"></script>
<section class="scrollable wrapper  ">
    <section class="panel panel-default ">
        <div class="row m-l-none m-r-none bg-light lter">
            <div class="col-sm-6 col-md-3 padder-v b-r b-light ">

                <span class="fa-stack fa-2x pull-left m-r-sm  m-l">
                            <i class="fa fa-circle fa-stack-2x text-danger"></i>
                            <i class="fa fa-clipboard  fa-stack-1x text-white"></i>
                            <span class="easypiechart pos-abt" data-percent="100" data-line-width="4"
                                  data-track-color="#f5f5f5" data-scale-color="false" data-size="50"
                                  data-line-cap="butt" data-animate="3000" data-target="#firers"
                                  data-update="5000"></span>
                        </span>
                <a class="fl" href="<?php echo url('shop/sys_admin.order/index',['reportrange'=>$today.'-'.$today]); ?>">
                    <span class="h3 block m-t-xs"><strong id="t_day_order_num"><?php echo htmlentities($stats['today']['all_add_num']); ?></strong></span>
                    <small class="text-muted text-uc">今天下单数</small>
                </a>
                <a class="fr" href="<?php echo url('shop/sys_admin.order/index',['reportrange'=>$yesterday.'-'.$yesterday]); ?>">
                    <span class="h3 block m-t-xs"><strong id="y_day_order_num"><?php echo htmlentities($stats['yesterday']['all_add_num']); ?></strong></span>
                    <small class="text-muted text-uc">昨天下单数</small>
                </a>
            </div>
            <div class="col-sm-6 col-md-3 padder-v b-r b-light lt">
                        <span class="fa-stack fa-2x pull-left m-r-sm  m-l">
                            <i class="fa fa-circle fa-stack-2x text-warning"></i>
                            <i class="fa fa-yen fa-stack-1x text-white"></i>
                            <span class="easypiechart pos-abt" data-percent="100" data-line-width="4"
                                  data-track-color="#fff" data-scale-color="false" data-size="50" data-line-cap="butt"
                                  data-animate="2000" data-target="#bugs" data-update="3000"></span>
                        </span>
                <a class="fl" >
                    <span class="h3 block m-t-xs"><strong id="t_day_clinch_money"><?php echo htmlentities($stats['today']['order_pay_num']); ?></strong></span>
                    <small class="text-muted text-uc">今天成交金额</small>
                </a>
                <a class="fr">
                    <span class="h3 block m-t-xs"><strong id="y_day_clinch_money"><?php echo htmlentities($stats['yesterday']['order_pay_num']); ?></strong></span>
                    <small class="text-muted text-uc" >昨天成交金额</small>
                </a>
            </div>
            <div class="col-sm-6 col-md-3 padder-v b-r b-light">
                       <span class="fa-stack fa-2x pull-left m-r-sm  m-l">
                            <i class="fa fa-circle fa-stack-2x text-info"></i>
                            <i class="fa fa-truck fa-stack-1x text-white"></i>
                        </span>
                <a class="fl"  href="<?php echo url('shop/sys_admin.order/index',['state'=>3]); ?>">
                    <span class="h3 block m-t-xs"><strong id="t_day_wait_shipping_num"><?php echo htmlentities($stats['wait_shipping_num']); ?></strong></span>
                    <small class="text-muted text-uc">待发货订单</small>
                </a>
                <a class="fr" href="<?php echo url('shop/sys_admin.order/index',['time_type'=>'shipping_time','start_time'=>$yesterday,'end_time'=>$yesterday]); ?>">
                    <span class="h3 block m-t-xs"><strong id="y_day_shipping_num"><?php echo htmlentities($stats['yesterday']['order_shipping_num']); ?></strong></span>
                    <small class="text-muted text-uc">昨天发货订单</small>
                </a>
            </div>
            <div class="col-sm-6 col-md-3 padder-v b-r b-light lt">
                        <span class="fa-stack fa-2x pull-left m-r-sm  m-l">
                            <i class="fa fa-circle fa-stack-2x icon-muted"></i>
                            <i class="fa fa-legal fa-stack-1x text-white"></i>
                        </span>
                <a class="fl" href="<?php echo url('shop/sys_admin.order/index',['time_type'=>'sign_time','start_time'=>$today,'end_time'=>$today]); ?>">
                    <span class="h3 block m-t-xs"><strong id="t_day_sign_num"><?php echo htmlentities($stats['today']['sign_num']); ?></strong></span>
                    <small class="text-muted text-uc">今天签收订单</small>
                </a>
                <a class="fr" href="<?php echo url('shop/sys_admin.order/index',['time_type'=>'sign_time','start_time'=>$yesterday,'end_time'=>$yesterday]); ?>">
                    <span class="h3 block m-t-xs"><strong id="y_day_sign_num"><?php echo htmlentities($stats['yesterday']['sign_num']); ?></strong></span>
                    <small class="text-muted text-uc">昨天签收订单</small>
                </a>
            </div>
        </div>
    </section>

    <section class="panel panel-default ">
        <div class="row m-l-none m-r-none bg-light lter">
            <div class="col-sm-6 col-md-3 padder-v b-r b-light ">
                <span class="fa-stack fa-2x pull-left m-r-sm  m-l">
                            <i class="fa fa-circle fa-stack-2x icon-muted"></i>
                            <i class="fa fa-yen fa-stack-1x text-white"></i>
                        </span>
                <a class="fl" href="<?php echo url('member/sys_admin.recharge/index'); ?>">
                    <span class="h3 block m-t-xs"><strong id="wait_pay"><?php echo htmlentities($rchargeLogNum); ?></strong></span>
                    <small class="text-muted text-uc">充值待审核</small>
                </a>
                <a class="fr" href="<?php echo url('member/sys_admin.withdraw/index'); ?>">
                    <span class="h3 block m-t-xs"><strong id="wait_withdraw"><?php echo htmlentities($withdrawLogNum); ?></strong></span>
                    <small class="text-muted text-uc">提现待审核</small>
                </a>
            </div>
            <div class="col-sm-6 col-md-3 padder-v b-r b-light lt">
                        <span class="fa-stack fa-2x pull-left m-r-sm  m-l">
                            <i class="fa fa-circle fa-stack-2x text-warning"></i>
                            <i class="fa fa-user fa-stack-1x text-white"></i>
                            <span class="easypiechart pos-abt" data-percent="100" data-line-width="4"
                                  data-track-color="#fff" data-scale-color="false" data-size="50" data-line-cap="butt"
                                  data-animate="2000" data-target="#bugs" data-update="3000"></span>
                        </span>
                <a class="fl">
                    <span class="h3 block m-t-xs"><strong id="t_day_new_user"><?php echo htmlentities($userStats['today_reg']); ?></strong></span>
                    <small class="text-muted text-uc">今天新增会员</small>
                </a>
                <a class="fr">
                    <span class="h3 block m-t-xs"><strong id="y_day_new_user"><?php echo htmlentities($userStats['yesterday_reg']); ?></strong></span>
                    <small class="text-muted text-uc">昨天新增会员</small>
                </a>
            </div>
            <div class="col-sm-6 col-md-3 padder-v b-r b-light">
                       <span class="fa-stack fa-2x pull-left m-r-sm  m-l">
                            <i class="fa fa-circle fa-stack-2x text-info"></i>
                            <i class="fa fa-users fa-stack-1x text-white"></i>
                        </span>
                <a class="fl" href="#">
                    <span class="h3 block m-t-xs"><strong id="user_num"><?php echo htmlentities($userStats['all_num']); ?></strong></span>
                    <small class="text-muted text-uc">会员总数</small>
                </a>
                <a class="fr" href="#">
                    <span class="h3 block m-t-xs"><strong id="user_role_num"><?php echo htmlentities($userStats['role_num']); ?></strong></span>
                    <small class="text-muted text-uc">身份会员总数</small>
                </a>
            </div>
            <div class="col-sm-6 col-md-3 padder-v b-r b-light lt">
                  <span class="fa-stack fa-2x pull-left m-r-sm  m-l">
                            <i class="fa fa-circle fa-stack-2x text-danger"></i>
                            <i class="fa fa-yen  fa-stack-1x text-white"></i>
                            <span class="easypiechart pos-abt" data-percent="100" data-line-width="4"
                                  data-track-color="#f5f5f5" data-scale-color="false" data-size="50"
                                  data-line-cap="butt" data-animate="3000" data-target="#firers"
                                  data-update="5000"></span>
                        </span>
                <a class="fl" href="#">
                    <span class="h3 block m-t-xs"><strong id="balance_total"><?php echo htmlentities($account['balance_money']); ?></strong></span>
                    <small class="text-muted text-uc">金币余额总数</small>
                </a>
                <a class="fr" href="#">
                    <span class="h3 block m-t-xs"><strong id="bean_total"><?php echo htmlentities($account['bean_value']); ?></strong></span>
                    <small class="text-muted text-uc">旅游豆总数</small>
                </a>
            </div>
        </div>
    </section>

    <div class="col-md-8">
        <section class="panel panel-default ">
            <header class="panel-heading font-bold">
                7天订单数据
                <small>（<?php echo htmlentities($start_day); ?>至<?php echo htmlentities($end_day); ?>）</small>
                <a href="javascript:;" data-toggle="popover" data-html="true" data-placement="bottom"
                   data-trigger="hover"
                   data-content="<p style='color:#999'><strong style='color:#333'>下单数：</strong>每日提交的订单数；</p><p style='color:#999'><strong style='color:#333'>待签收数：</strong>每日待签收订单数；</p><p style='color:#999'><strong style='color:#333'>发货数：</strong>每天发货的订单数。</p>"
                   data-original-title="" title=""><i class="fa fa-question-circle fa-lg m-l-xs"></i></a>
            </header>
            <div class="panel-body">
                <!--   style="height: 210px"  -->
                <div id="container"></div>
            </div>
            <footer class="panel-footer bg-white no-padder">
                <div class="row text-center no-gutter">

                    <div class="col-xs-3 b-r b-light">
                        <span class="h4 font-bold m-t block"><?php echo htmlentities(intval($stats['seven_add_num'])); ?></span>
                        <small class="text-muted m-b block">下单总数</small>
                    </div>
                    <div class="col-xs-3 b-r b-light">
                        <span class="h4 font-bold m-t block"><?php echo htmlentities(intval($stats['seven_pay_num'])); ?></span>
                        <small class="text-muted m-b block">成交总数</small>
                    </div>
                    <div class="col-xs-3 b-r b-light">
                        <span class="h4 font-bold m-t block"><?php echo htmlentities(intval($stats['seven_shpping_num'])); ?></span>
                        <small class="text-muted m-b block">发货总数</small>
                    </div>
                    <div class="col-xs-3">
                        <span class="h4 font-bold m-t block"><?php echo htmlentities(intval($stats['seven_sign_num'])); ?></span>
                        <small class="text-muted m-b block">签收总数</small>
                    </div>
                </div>
            </footer>
        </section>
    </div>
    <div class="col-md-4">
        <section class="panel panel-default">
            <header class="panel-heading font-bold">
                7天交易数据
                <small>（<?php echo htmlentities($start_day); ?>至<?php echo htmlentities($end_day); ?>）</small>
                <a href="javascript:;" data-toggle="popover" data-html="true" data-placement="bottom"
                   data-trigger="hover"
                   data-content="<p style='color:#999'><strong style='color:#333'>成交金额：</strong>7天内总的成交金额（线上支付的统计7天内已支付的实收款，线下支付的统计7天内确认收货的实收款）；</p><p style='color:#999'><strong style='color:#333'>退款金额：</strong>7天内退货订单的金额总数；</p><p style='color:#999'><strong style='color:#333'>实际收入：</strong>7天内的实际收入=成交金额—退款金额。</p>"
                   data-original-title="" title=""><i class="fa fa-question-circle fa-lg m-l-xs"></i></a>
            </header>
            <div class="bg-light dk wrapper">
                <!--  style="height: 150px" -->
                <div class="text-center m-b-n m-t-sm">
                    <div id="total_money"></div>
                </div>
            </div>

            <div class="panel-body">

                <div class="row m-t-sm">
                    <div class="col-xs-3 m-l fl">
                        <small class="text-muted block">成交</small>
                        <span class="money"><?php echo htmlentities($stats['seven_order_amount_all']); ?></span>
                    </div>
                    <div class="col-xs-3 m-l fl">
                        <small class="text-muted block">实收</small>
                        <span class="money"><?php echo htmlentities($stats['seven_real_amount_all']); ?></span>
                    </div>
                    <div class="col-xs-3 m-l fl">
                        <small class="text-muted block">佣金</small>
                        <span class="money"><?php echo htmlentities($stats['seven_dividend_amount_all']); ?></span>
                    </div>
                </div>
            </div>
        </section>
    </div>

</section>
<script type="text/javascript">
    $(function () {
        $('#container').highcharts({
            chart: {type: 'spline'},
            title: {text: ''},
            subtitle: {text: ''},
            xAxis: { categories: <?php echo $riqi; ?>},
            yAxis: {
                title: {text: ''},
                min: 0
            },
            tooltip: {
                formatter: function () {
                    return '<b>时间:' + this.x + '</b><br>' + this.series.name + ': ' + this.y + '';
                }
            },
            series: [{
                name: '下单数 <span style="color:gray" >(点击隐藏)</span>',
                data: <?php echo json_encode($stats['all_add_num']); ?>
            },{
                name: '成交数 <span style="color:gray" >(点击隐藏)</span>',
                data: <?php echo json_encode($stats['order_pay_num']); ?>
            },{
                name: '发货数 <span style="color:gray" >(点击隐藏)</span>',
                data: <?php echo json_encode($stats['shipping_order_num']); ?>
            }, {
                name: '签收数 <span style="color:gray" >(点击隐藏)</span>',
                data:  <?php echo json_encode($stats['sign_order_num']); ?>
            }]
        });


        $('#total_money').highcharts({
            chart: {type: 'spline'},
            title: {text: ''},
            subtitle: {text: ''},
            xAxis: { categories: <?php echo $riqi; ?>},
            yAxis: {
                title: {text: ''},
                min: 0
            },
            tooltip: {
                formatter: function () {
                    return '<b>时间:' + this.x + '</b><br>' + this.series.name + ': ' + this.y + '';
                }
            },
            series: [{
                name: '成交',
                data: <?php echo json_encode($stats['seven_order_amount']); ?>
            },{
                name: '实收',
                data: <?php echo json_encode($stats['seven_real_amount']); ?>
            },{
                name: '佣金',
                data: <?php echo json_encode($stats['seven_dividend_amount']); ?>
            }
            ]
        });
    });
</script>

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