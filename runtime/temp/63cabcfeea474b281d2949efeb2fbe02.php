<?php /*a:3:{s:74:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\backup_db\index.html";i:1549953095;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\base.html";i:1550818706;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\page.html";i:1549953095;}*/ ?>
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
              <ul class="breadcrumb">
                  <li>
                      <i class="fa fa-ellipsis-v"></i>
                      <strong>备份数据</strong>
                  </li>                                  
              </ul>
              <a href="javascript:;" id="export"  class="btn btn-sm btn-default  fr m-r-tm m-t-md"><i class="fa fa-floppy-o m-r-xs"></i>数据备份</a>
          </div>
  </header>

  <section  id="explanation" class="scrollable wrapper ">
      <section class="panel panel-default padding">
          <ul>
         
               <p>数据备份功能根据你的选择备份全部数据或指定数据，导出的数据文件可用"数据恢复"功能或 phpMyAdmin 导入</p>
               <p>建议定期备份数据库</p>
              <p class="ftitle">
              <span>数据库表列表</span>
              <span>(共<?php echo htmlentities($tableNum); ?>张记录，共计<?php echo htmlentities($total); ?>)</span>
              <a href="javascript:;" title="刷新数据" class="pReload"><i class="fa fa-refresh"></i></a>
              
          </p>
          </ul>
    
          <form  method="post" id="export-form" action="<?php echo url('export'); ?>">
      
              <table class="table table-bordered table-striped ">
              <thead>
              <tr>
                  <th class="sign" axis="col0" >
                      <input type="checkbox" onClick="javascript:$('input[name*=tables]').prop('checked',this.checked);">
                  </th>
                  <th align="left" abbr="article_title" axis="col3" class="">
                      数据库表
                  </th>
                  <th align="center" abbr="ac_id" axis="col4" class="">
                      记录条数
                  </th>
                  <th align="center" abbr="article_show" axis="col5" class="">
                      占用空间
                  </th>
                  <th align="center" abbr="article_time" axis="col6" class="">
                      编码
                  </th>
                  <th align="center" abbr="article_time" axis="col6" class="">
                      创建时间
                  </th>
              
                  <th align="center" axis="col1" class="handle">
                      操作
                  </th>
                  
              </tr>
              </thead>
              
              <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $k=>$vo): ?>
                      <tr data-id="<?php echo htmlentities($vo['Name']); ?>">
                          <td class="sign">
                              <input type="checkbox" name="tables[]" value="<?php echo htmlentities($vo['Name']); ?>">
                          </td>
                          <td align="left" class="">
                              <?php echo htmlentities($vo['Name']); ?>
                          </td>
                          <td align="center" class="">
                              <?php echo htmlentities($vo['Rows']); ?>
                          </td>
                          <td align="center" class="">
                              <?php echo htmlentities(formatBytes($vo['Data_length'])); ?>
                          </td>
                          <td align="center" class="">
                              <?php echo htmlentities($vo['Collation']); ?>
                          </td>
                          <td align="center" class="">
                              <?php echo htmlentities($vo['Create_time']); ?>
                          </td>
                          
                          <td align="center" class="handle">
                              
                                  <a href="<?php echo url('BackupDb/optimize',array('tablename'=>$vo['Name'])); ?>" data-toggle="ajaxPost" data-noturl="1" title="优化" class="m-r-xs"><i class="fa fa-magic"></i></a>
                                  
                                  <a  href="<?php echo url('BackupDb/repair',array('tablename'=>$vo['Name'])); ?>" data-toggle="ajaxPost" data-noturl="1" title="修复"><i class="fa fa-wrench"></i></a>
                              
                          </td>
                          
                      </tr>
                  <?php endforeach; endif; else: echo "" ;endif; ?>
          </table>
          </form>
   
</section>

    
    
<script>
	$(document).ready(function(){
		// 表格行点击选中切换
		$('#flexigrid > table>tbody >tr').click(function(){
			$(this).toggleClass('trSelected');
		});

		// 点击刷新数据
		$('.fa-refresh').click(function(){
			location.href = location.href;
		});

	});

	(function($){
		var $form = $("#export-form"), $export = $("#export"), tables
		$export.click(function(){
			if($("input[name^='tables']:checked").length == 0){
				_alert('请选中要备份的数据表');
				return false;
			}
			$export.addClass("disabled");
			$export.html("正在发送备份请求...");
			$.post(
					$form.attr("action"),
					$form.serialize(),
					function(res){
						if(res.code == 1){
							tables = res.data.tables;
							$export.html(res.msg + "开始备份，请不要关闭本页面！");
							backup(res.data.tab);
							window.onbeforeunload = function(){ return "正在备份数据库，请不要关闭！" }
						} else {
							_alert(res.msg);
							$export.removeClass("disabled");
							$export.html("立即备份");
						}
					},
					"json"
			);
			return false;
		});

		function backup(tab, status){
			status && showmsg(tab.id, "开始备份...(0%)");
			$.get($form.attr("action"), tab, function(res){
				if(res.code == 1){
					showmsg(tab.id, res.data.info);
					if(!$.isPlainObject(res.data.tab)){
						$export.removeClass("disabled");
						$export.html("备份完成，点击重新备份");
						window.onbeforeunload = function(){ return null }
						return;
					}
					backup(res.data.tab, tab.id != res.data.tab.id);
				} else {
					$export.removeClass("disabled");
					$export.html("立即备份");
				}
			}, "json");
		}

		function showmsg(id, msg){
			$form.find("input[value=" + tables[id] + "]").closest("tr").find(".info").html(msg);
		}
	})(jQuery);
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