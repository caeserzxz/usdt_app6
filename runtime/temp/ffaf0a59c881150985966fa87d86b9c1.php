<?php /*a:3:{s:77:"D:\phpStudy\WWW\moduleshop\application\shop\view\sys_admin\setting\index.html";i:1549953096;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\base.html";i:1550818706;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\page.html";i:1549953095;}*/ ?>
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
                        <a href="<?php $vob = reset($vo['list']);echo url($vo['key'].'/'.$vo['controller'].'/'.$vo['action']) ?>"><i class="fa <?php echo htmlentities($vo['icon']); ?>"></i> <?php echo htmlentities($vo['name']); ?></a>
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
        	
<header class="header  b-b clearfix">
     <div class="page-breadcrumbs">
            <ul class="breadcrumb" >
                <li>
                    <i class="fa fa-ellipsis-v"></i>
                    <strong>商城设置</strong>
                </li>                                  
            </ul>
      </div>
</header>
<section class="scrollable  wrapper">
      <section class="panel panel-default">
                <div class="widget-body">
                    <div class="collapse in">
                        <form class="form-horizontal form-validate" method="post" action="<?php echo url('save'); ?>">
                         	<div class="form-group">
                                  <label class="col-sm-2 control-label">首页设定：</label>
                                  <div class="controls">
                                    <label class="radio-inline">
                                      <input name="shop_index_tpl" value="0" <?php echo $setting['shop_index_tpl']==0 ? 'checked' : ''; ?> type="radio">默认首页
                                    </label>
                                    <label class="radio-inline">
                                      <input name="shop_index_tpl" value="1" <?php echo $setting['shop_index_tpl']==1 ? 'checked' : ''; ?> type="radio" >自定义首页
                                    </label>
                                  </div>
                             </div>
                             <div class="form-group">
                                  <label class="col-sm-2 control-label">商城Title：</label>
                                  <div class="controls">
                                   <input type="text" name="shop_title"  class="input-large" value="<?php echo htmlentities($setting['shop_title']); ?>"> <span class="help-line">首页 - xxxxxxxxxx</span>
                                  </div>
                             </div>
                            
                             <div class="form-group">
                                  <label class="col-sm-2 control-label">搜索框文字：</label>
                                  <div class="controls">
                                   <input type="text" name="shop_index_search_text"  class="input-large" data-rule-required="true" value="<?php echo htmlentities($setting['shop_index_search_text']); ?>"> <span class="help-line">搜索框默认显示的搜索关键字</span>
                                  </div>
                             </div>
                              <div class="form-group">
                                  <label class="col-sm-2 control-label">热门搜索：</label>
                                  <div class="controls">
                                   <input type="text" name="hot_search"  class="input-xxlarge" value="<?php echo htmlentities($setting['hot_search']); ?>"> <span class="help-line">每个搜索词中间用空格隔开</span>
                                  </div>
                             </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">开放商品评论：</label>
                                <div class="controls">
                                    <label class="radio-inline">
                                        <input name="shop_goods_comment" value="0" <?php echo $setting['shop_goods_comment']<1 ? 'checked' : ''; ?> type="radio">关闭
                                    </label>
                                    <label class="radio-inline">
                                        <input name="shop_goods_comment" value="1" <?php echo $setting['shop_goods_comment']==1 ? 'checked' : ''; ?> type="radio" >开启
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">开放商品问答：</label>
                                <div class="controls">
                                    <label class="radio-inline">
                                        <input name="shop_goods_answer" value="0" <?php echo $setting['shop_goods_answer']<1 ? 'checked' : ''; ?> type="radio">关闭
                                    </label>
                                    <label class="radio-inline">
                                        <input name="shop_goods_answer" value="1" <?php echo $setting['shop_goods_answer']==1 ? 'checked' : ''; ?> type="radio" >开启
                                    </label>
                                </div>
                            </div>
                             <div class="line line-dashed line-lg pull-in"  style="width:99%;"></div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" >下单未支付超时取消：</label>
                                <div class="controls">
                                  	<select name="shop_order_auto_cancel"  style="width:200px;">
                                    	<option value="0" <?php echo $setting['shop_order_auto_cancel']==0 ? 'selected' : ''; ?>>不执行自动取消</option>
                                    	<option value="15" <?php echo $setting['shop_order_auto_cancel']==15 ? 'selected' : ''; ?>>15 分钟</option>
                                        <option value="30" <?php echo $setting['shop_order_auto_cancel']==30 ? 'selected' : ''; ?>>30 分钟</option>
                                        <?php $__FOR_START_10998__=1;$__FOR_END_10998__=24;for($time=$__FOR_START_10998__;$time < $__FOR_END_10998__;$time+=1){ ?>
                                         <option value="<?php echo htmlentities($time * 60); ?>" <?php echo $setting['shop_order_auto_cancel']==$time * 60 ? 'selected' : ''; ?>><?php echo htmlentities($time); ?> 小时</option>
                                      	<?php } ?>
                                  </select> <span class="help-line">下单成功后超过指定时间未支付自动取消订单</span>
                                 </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" >发货后多少天自动收货：</label>
                                <div class="controls">
                                  	<select name="shop_auto_sign_limit"  style="width:80px;">
                                      <?php $__FOR_START_15901__=1;$__FOR_END_15901__=31;for($day=$__FOR_START_15901__;$day < $__FOR_END_15901__;$day+=1){ ?>
                                         <option value="<?php echo htmlentities($day); ?>" <?php echo $setting['shop_auto_sign_limit']==$day ? 'selected' : ''; ?>><?php echo htmlentities($day); ?> 天</option>
                                      <?php } ?>
                                  </select> <span class="help-line">发货后多少天自动收货</span>
                                 </div>
                            </div>
                            <div class="form-group">
                                   <label class="col-sm-2 control-label" >快递查询接口：</label>
                                  <div class="controls">
                                    <select class="input-max" name="shop_shippping_view_fun">
                                         <option value="">选择快递查询接口</option>
                                         <?php if(is_array($shippingFunction) || $shippingFunction instanceof \think\Collection || $shippingFunction instanceof \think\Paginator): $i = 0; $__LIST__ = $shippingFunction;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>	
                                            <option value="<?php echo htmlentities($val['function']); ?>" <?php echo $setting['shop_shippping_view_fun']==$val['function'] ? 'selected' : ''; ?> ><?php echo htmlentities($val['name']); ?></option>            
                                         <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                             </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" >多少天内可申请售后：</label>
                                <div class="controls">
                                  	<select name="shop_after_sale_limit"  style="width:80px;">
                                      <?php $__FOR_START_1799__=1;$__FOR_END_1799__=31;for($day=$__FOR_START_1799__;$day < $__FOR_END_1799__;$day+=1){ ?>
                                         <option value="<?php echo htmlentities($day); ?>" <?php echo $setting['shop_after_sale_limit']==$day ? 'selected' : ''; ?>><?php echo htmlentities($day); ?> 天</option>
                                      <?php } ?>
                                  </select> <span class="help-line">签收后多少天内可申请售后</span>
                                 </div>
                            </div>
                            
             				<div class="line line-dashed line-lg pull-in"  style="width:99%;"></div>
                            <div class="form-group">
                                  <label class="col-sm-2 control-label">减库存的时机：</label>
                                  <div class="controls">
                                    <label class="radio-inline">
                                      <input name="shop_reduce_stock" value="0" <?php echo $setting['shop_reduce_stock']==0 ? 'checked' : ''; ?> type="radio">下单成功时
                                    </label>
                                    <label class="radio-inline">
                                      <input name="shop_reduce_stock" value="1" <?php echo $setting['shop_reduce_stock']==1 ? 'checked' : ''; ?> type="radio" >支付成功时
                                    </label>
                                  </div>
                             </div>
							
                   			<div class="form-group">
                                <label class="col-sm-2 control-label" >库存预警数：</label>
                                <div class="controls">
                                  	<input type="text" name="goods_stock_warn"  class="input-mini" data-rule-required="true" value="<?php echo htmlentities(intval($setting['goods_stock_warn'])); ?>"> <span class="help-line">库存预警,当商品库存少于库存预警数</span>
                                 </div>
                            </div>
                           <div class="form-group">
                                 <label class="control-label"></label>
                                  <div class="controls"> 
                                      <button type="submit" class="btn btn-primary" data-loading-text="保存中...">保存</button>
                                      <button type="button" class="btn btn-default" data-toggle="back">取消</button>
                                  </div>
                           </div>
                  </form>
                </div>  
             </div>       
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