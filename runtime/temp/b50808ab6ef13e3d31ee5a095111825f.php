<?php /*a:3:{s:74:"D:\phpStudy\WWW\mainshop\application\shop\view\sys_admin\order\search.html";i:1552460091;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\base.html";i:1552460091;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\page.html";i:1552460091;}*/ ?>
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
        	
<header class="header b-b clearfix">
     <div class="page-breadcrumbs">
          <ul class="breadcrumb" >
              <li>
                  <i class="fa fa-ellipsis-v"></i>
                  <strong>订单查询</strong>
              </li>                                  
          </ul>
    </div>
</header>

<section class="scrollable  wrapper">
    <section class="panel panel-default">
    <form class="form-horizontal " method="post" action="<?php echo url('index'); ?>">
        <div class="panel-body">
           <div class="form-group">
            	<label class=" control-label">订单号：</label>
                <div class="col-sm-2 ">
                  <input type="text" class="input-max"  name="order_sn" value=""  />
                </div>
                  <label class=" control-label">收货人：</label>
                <div class="col-sm-2 ">
                  <input type="text" class="input-max"  name="consignee" value=""  />
                </div>
            </div>          
            <div class="form-group">
                <label class=" control-label">电话：</label>
                <div class="col-sm-2 ">
                  <input type="text" class="input-max"  name="tel" value=""  />
                </div>
                <label class=" control-label">手机：</label>
                <div class="col-sm-2">
                  <input type="text" class="input-max"  name="mobile" value=""  />
                </div>
            </div>
            <div class="form-group">
                <label class=" control-label">地址：</label>
                <div class="col-sm-6">
                  <input type="text" class="input-max"  name="address" value=""  />
                </div>
            </div>
            <div class="form-group">
                <label class=" control-label">所在地区：</label>
                <div class="col-sm-6">
                  <select name="province" id="province" style="width:150px;"  class="region_sel" nextsel="city|district" sel_val="100000|0">
                    <option value="">请选择</option>
                   </select>
                   <select name="city" id="city" style="width:150px;"  class="region_sel" nextsel="district" sel_val="0|0">
                    <option value="">请选择</option>
                   </select>
                   <select name="district" id="district" style="width:150px;"  class="region_sel" sel_val="0|0">
                    <option value="">请选择</option>
                   </select>
                </div>
            </div>
              <div class="form-group">
                <label class=" control-label">会员ID：</label>
                <div class="col-sm-2 ">
                  <input type="text" class="input-max"  name="user_id" value=""  />
                </div>
                <label class=" control-label">会员手机：</label>
                <div class="col-sm-2 ">
                  <input type="text" class="input-max"  name="user_mobile" value=""  />
                </div>
            </div>
            <div class="form-group">
                <label class=" control-label">配送方式：</label>
                <div class="col-sm-2 ">
                  <select name="shipping_id"  style="width:180px;" >
                    <option value="">请选择</option>
                   <?php if(is_array($shippingList) || $shippingList instanceof \think\Collection || $shippingList instanceof \think\Paginator): $i = 0; $__LIST__ = $shippingList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$shipping): $mod = ($i % 2 );++$i;?>
                    <option value="<?php echo htmlentities($shipping['shipping_id']); ?>"><?php echo htmlentities($shipping['shipping_name']); ?></option>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                   </select>
                </div>
                <label class=" control-label">支付方式：</label>
                <div class="col-sm-2 ">
                   <select name="pay_id"  style="width:130px;" >
                    <option value="">请选择</option>
                     <?php if(is_array($paymentList) || $paymentList instanceof \think\Collection || $paymentList instanceof \think\Paginator): $i = 0; $__LIST__ = $paymentList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$payment): $mod = ($i % 2 );++$i;?>
                    <option value="<?php echo htmlentities($payment['pay_id']); ?>"><?php echo htmlentities($payment['pay_name']); ?></option>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                   </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">时间范围：</label>
                <div class="col-sm-10" >
                      <select name="time_type"  style="width:130px; float:left; " >
                        <option value="add_time">下单时间</option>
                        <option value="pay_time">支付时间</option>
                        <option value="shipping_time">发货时间</option>
                        <option value="sign_time">签收时间</option>
                       </select>
                   
                    <label class="control-label" style="width:100px;">开始时间：</label>
                    <div class="col-sm-3">
                        <div class="input-group"> <input type="text" class="input-max" name="start_time" readonly="readonly" value="<?php echo htmlentities($start_date); ?>" data-before="#solddate"   data-toggle="datetimepicker" /><span class="input-group-addon"><i class="fa fa-calendar"></i></span></div> 
                     </div>
                    <label class="control-label" style="width:100px;">结束时间：</label>
                    <div class="col-sm-3"  >
                        <div class="input-group"> <input type="text" class="input-max" name="end_time" value="<?php echo htmlentities($end_date); ?>" data-after="#groundingdate" data-offsetday="0"  readonly="readonly" data-toggle="datetimepicker" /><span class="input-group-addon"><i class="fa fa-calendar"></i></span></div> 
                    </div>
                </div>
            </div>
             <div class="form-group">
                <label class=" control-label">订单状态：</label>
                <div class="col-sm-10 ">
                      <select name="order_status"  style="width:130px; float:left;" >
                        <option value="">请选择</option>
                         <?php if(is_array($orderLang['os']) || $orderLang['os'] instanceof \think\Collection || $orderLang['os'] instanceof \think\Paginator): $i = 0; $__LIST__ = $orderLang['os'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($key); ?>"><?php echo $val; ?></option>
                         <?php endforeach; endif; else: echo "" ;endif; ?>
                       </select>
                    <label class="control-label" style="width:100px;">付款状态：</label>
                    <select name="pay_status"  style="width:130px; float:left;" >
                        <option value="">请选择</option>
                        <?php if(is_array($orderLang['ps']) || $orderLang['ps'] instanceof \think\Collection || $orderLang['ps'] instanceof \think\Paginator): $i = 0; $__LIST__ = $orderLang['ps'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($key); ?>"><?php echo htmlentities($val); ?></option>
                         <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                    <label class="control-label" style="width:100px;">发货状态：</label>
                    <select name="shipping_status"  style="width:130px; float:left;" >
                        <option value="">请选择</option>
                        <?php if(is_array($orderLang['ss']) || $orderLang['ss'] instanceof \think\Collection || $orderLang['ss'] instanceof \think\Paginator): $i = 0; $__LIST__ = $orderLang['ss'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($key); ?>"><?php echo htmlentities($val); ?></option>
                         <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
             </div>
             <div class="form-group">
                <label class=" control-label">导出订单：</label>
                <div class="col-sm-10 ">
                      <label><input name="export" type="checkbox" value="1" ></label>
                </div>
            </div>
            <div class="line line-dashed line-lg pull-in"  style="width:99%;"></div>
                  <div class="form-group" style="width:90%;">
                    <div class="col-sm-4 col-sm-offset-2"> 
                      <button type="submit" class="btn btn-primary " >查询</button>
                      <button type="reset" class="btn btn-default" >重置</button>
                    </div>
            </div>
        </div>
      </form>
    </section>
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