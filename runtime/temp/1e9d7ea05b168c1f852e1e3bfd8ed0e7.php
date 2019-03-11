<?php /*a:3:{s:78:"D:\phpStudy\WWW\moduleshop\application\weixin\view\sys_admin\msg_tpl\info.html";i:1549953096;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\base.html";i:1550818706;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\page.html";i:1549953095;}*/ ?>
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
    
<link href="/static/js/colorpicker/bootstrap-colorpicker.css" rel="stylesheet" />
<script type="text/javascript" src="/static/js/colorpicker/bootstrap-colorpicker.js"></script>
<script type="text/javascript" src="/static/js/clipboard.min.js"></script>

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
                    <strong>编缉短信模板</strong>
                </li>                                  
            </ul>
           
      </div>
   
</header>
<section class="scrollable wrapper w-f ">
    <section class="panel panel-default ">
<form class="form-horizontal form-validate form-modal" method="post" action="<?php echo url('info'); ?>">
<div class="widget-body">
                    <div class="collapse in">
                    <div class="form-group">
                        <label class="control-label">模板名称</label>
                        <div class="col-sm-6 m-t-md">
                            <?php echo htmlentities($row['tpl_name']); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">模板code</label>
                        <div class="col-sm-6 ">
                            <input type="text" class="input-xlarge"  name="tpl_code" value="<?php echo htmlentities($row['tpl_code']); ?>" data-rule-required="true" >
                        </div>
                    </div>
                 <div class="form-group">
                                <label class="control-label">是否开启</label>
                                <div class="col-sm-7">
                                    <label class="m-t-md">
                                          <input class="checkbox-slider colored-blue" name="status" type="checkbox" value="1" <?php echo htmlentities(tplckval($row['status'],1,'checked')); ?>>
                                          <span class="text"></span>
                                   </label>
                                </div>
                   </div>
                    <div class="form-group">
                        <label class="control-label">标题颜色</label>
                        <div class="col-sm-6">
                           <input id="sel_color" name="topcolor"  class="sel-color" type="text" style="background-color:<?php echo htmlentities((isset($row['topcolor']) && ($row['topcolor'] !== '')?$row['topcolor']:'#FF0000')); ?>;" value="<?php echo htmlentities((isset($row['topcolor']) && ($row['topcolor'] !== '')?$row['topcolor']:'#FF0000')); ?>" readonly/>
                        </div>
                    </div> 
                    
                    <div class="form-group">
                        <label class="control-label">替换值</label>
                        <div class="col-sm-8">
                        <p><span class="m-r">分佣相关:</span>
                          	<a href="javascript:;" class="copy_str m-r">[分佣等级]</a>
                            <a href="javascript:;" class="copy_str m-r">[佣金金额]</a>
                            <a href="javascript:;" class="copy_str m-r">[到帐天数]</a>
                         </p>
                         <p><span class="m-r">提现相关:</span>
                            <a href="javascript:;" class="copy_str m-r">[提现金额]</a>
                            <a href="javascript:;" class="copy_str m-r">[提现时间]</a>
                         </p>
                         <p><span class="m-r">会员相关:</span>
                          	<a href="javascript:;" class="copy_str m-r">[会员ID]</a>
                            <a href="javascript:;" class="copy_str m-r">[会员昵称]</a>
                            <a href="javascript:;" class="copy_str m-r">[会员姓别]</a>
                            <a href="javascript:;" class="copy_str m-r">[会员所在地区]</a>
                         </p>
                         <p><span class="m-r">订单相关:</span>
                          	<a href="javascript:;" class="copy_str m-r">[订单ID]</a>
                            <a href="javascript:;" class="copy_str m-r">[订单编号]</a>
                            <a href="javascript:;" class="copy_str m-r">[收货人]</a>
                            <a href="javascript:;" class="copy_str m-r">[订单金额]</a>
                            <a href="javascript:;" class="copy_str m-r">[下单时间]</a>
                            <a href="javascript:;" class="copy_str m-r">[快递公司]</a>
                            <a href="javascript:;" class="copy_str m-r">[快递编号]</a>
                         </p>
                          <p><span class="m-r">其它内容:</span>
                          	<a href="javascript:;" class="copy_str m-r">[当前时间]</a>
                         </p>
                         <span class="help-inline">点击以上内容即可复制</span>
                        </div>
                    </div> 
                     <div class="form-group">
                        <label class="control-label">标题</label>
                        <div class="col-sm-8">
                           <input type="text" class="input-max"  name="first" value="<?php echo htmlentities($row['first']); ?>" data-rule-required="true">
                        </div>
                    </div> 
                    
                    <?php if(is_array($row['tpl_keys']) || $row['tpl_keys'] instanceof \think\Collection || $row['tpl_keys'] instanceof \think\Paginator): $i = 0; $__LIST__ = $row['tpl_keys'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <div class="form-group tpl_keys">
                        <label class="control-label">第<?php echo htmlentities($key); ?>行内容</label>
                        <div class="col-sm-8">
                           <input type="text" class="input-xxlarge"  name="tpl_keys[<?php echo htmlentities($key); ?>]" value="<?php echo htmlentities($vo); ?>" data-rule-required="true">
                           <?php if($key == '1'): ?>
                           		<a href="javascript:;" id="add_key" title="添加" class="btn btn-default "><i class="fa fa-plus m-r-xs"></i></a>
                            <?php else: ?>
                            	<a href="javascript:;" title="删除" class="btn btn-default "><i class="fa fa-times m-r-xs"></i></a>
                            <?php endif; ?>
                        </div>
                    </div> 
                     <?php endforeach; endif; else: echo "" ;endif; if(empty($row['tpl_keys']) || (($row['tpl_keys'] instanceof \think\Collection || $row['tpl_keys'] instanceof \think\Paginator ) && $row['tpl_keys']->isEmpty())): ?>
                     <div class="form-group tpl_keys">
                        <label class="control-label">第1行内容</label>
                        <div class="col-sm-8">
                           <input type="text" class="input-xxlarge"  name="tpl_keys[1]" value="" data-rule-required="true">
                           <a href="javascript:;" id="add_key" title="添加" class="btn btn-default m-b-md" ><i class="fa fa-plus m-r-xs"></i></a>
                        </div>
                    </div> 
                     <?php endif; ?>
                     <div id="new_key_list"></div>
                     <div class="form-group">
                        <label class="control-label">备注</label>
                        <div class="col-sm-8">
                           <input type="text" class="input-max"  name="remark" value="<?php echo htmlentities($row['remark']); ?>" data-rule-required="true">
                        </div>
                    </div> 
                    <div class="form-group">
                        <label class="control-label">短信样板</label>
                        <div class="col-sm-8 ">
                        <textarea name="tpl_content" data-rule-required="true" style="width:100%; height:120px;"><?php echo htmlentities($row['tpl_content']); ?></textarea>
                           
                        </div>
                    </div> 
            </div>
            <input  type="hidden" name="tpl_id" value="<?php echo htmlentities(intval($row['tpl_id'])); ?>"/>
             <div class="form-group">
                   <label class="control-label"></label>
                    <div class="controls"> 
                        <button type="submit" class="btn btn-primary" data-loading-text="保存中...">保存</button>
                        <button type="button" class="btn btn-default" data-toggle="back">取消</button>
                    </div>
             </div>
    </div>
</div> 
</form>
    </section>
 </section>
<script>
    $(function () {
        // 基本实例化:
        $('#sel_color').colorpicker();

        // 添加change事件 改变背景色
        $('#sel_color').on('change', function (event) {
            $(this).css('background-color', event.color.toString()).val('');
            $(this).text(event.color.toString());
        });
		$('.copy_str').click(function(){
			var s = $(this).html();
			var clipboard = new Clipboard('.copy_str', {
                //.btn为点击复制的按钮
                    text: function() {						
                        return s;
                    }
                });
                clipboard.on('success', function(e) {
                    //_alert("复制成功",true);
                });

                clipboard.on('error', function(e) {
                   _alert("复制失败")
                });
		})
		//添加key
		$('#add_key').click(function(){
			var i = $('.tpl_keys').length + 1;
			$('#new_key_list').append('<div class="form-group tpl_keys"><label class="control-label">第'+i+'行内容</label><div class="col-sm-8"><input type="text" class="input-xxlarge"  name="tpl_keys['+i+']" value="" data-rule-required="true"> <a href="javascript:;" title="删除" class="btn btn-default m-b-md del_key" ><i class="fa fa-times m-r-xs"></i></a></div></div>');
		})
		//删除
		$(document).on('click','.del_key',function(){
			$(this).parents('.tpl_keys').remove();
		})
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