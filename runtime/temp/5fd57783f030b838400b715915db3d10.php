<?php /*a:4:{s:81:"D:\phpStudy\WWW\moduleshop\application\weixin\view\sys_admin\reply_news\info.html";i:1549953096;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\base.html";i:1550818706;s:85:"D:\phpStudy\WWW\moduleshop\application\weixin\view\sys_admin\reply_news\news_box.html";i:1549953096;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\page.html";i:1549953095;}*/ ?>
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
    
<script type="text/javascript" src="/static/js/kindeditor/kindeditor-min.js"></script>
<link rel="stylesheet" type="text/css" href="/static/js/kindeditor/themes/default/default.css">
<script type="text/javascript" src="/static/js/kindeditor/lang/zh_CN.js"></script>

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
        	
<header>
	<div class="page-breadcrumbs">
    	<ul class="breadcrumb">
                <li>
                    <i class="fa fa-edit"></i>
                    <strong>图文管理</strong>
                </li>                                  
         </ul>
    	<a class="text-muted pull-right m-t-md m-r pointer" data-toggle="back" title="返回"><i class="fa fa-reply"></i></a>
    </div>
</header>
<section class="scrollable  wrapper">
   <section class="panel panel-default" >
          <div class="widget-body">
          <form class="form-horizontal form-validate" method="post" action="<?php echo url('save'); ?>">
        <div class="form-group">
            <label for="keyword" class="control-label">关键词：</label>
            <div class="controls">
                <input type="text" name="keyword" id="keyword" value="<?php echo htmlentities($row['keyword']); ?>" data-rule-required="true" data-rule-maxlength="30" class="input-xlarge" >
                <span class="maroon">*</span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">是否启用：</label>
            <div class="controls">
                            <label>
                                  <input class="checkbox-slider colored-blue" name="status" type="checkbox" value="1" <?php echo htmlentities(tplckval($row['status'],1,'checked')); ?>>
                                  <span class="text"></span>
                           </label>
                        </div>
        </div>
        <style>
.up_image{width:420px;position:relative;overflow:hidden;margin-bottom:20px;border:1px solid #d3d3d3;background-color:#fff;box-shadow:0 1px 0 rgba(0,0,0,0.1);-moz-box-shadow:0 1px 0 rgba(0,0,0,0.1);-webkit-box-shadow:0 1px 0 rgba(0,0,0,0.1);border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px
}
.up_image span div{padding:2px;}
#top_title {padding:10px;position:relative;}
#top_title span{ width:90%; padding:0px;}
#top_title p{cursor:pointer;display:block;color:#c0c0c0;text-align:center;line-height:160px;font-weight:400;font-style:normal;background-color:#ececec;font-size:22px}

.appmsg_item{*zoom:1;position:relative;padding:10px;border-top:1px solid #d3d3d3; height:100px; clear:both;}
.appmsg_item span{float:left;width:318px;height:100%;}
.appmsg_item p{cursor:pointer;position:relative;float:right;width:78px;height:78px;background-color:#ececec;}
.appmsg_item p i{position:absolute;line-height:78px;font-weight:200;color:#c0c0c0; text-align:center;font-style:normal;width:78px;}
.appmsg_item .p_class{width:78px;height:20px; background-color:#1e74c5; clear:both}
.appmsg_item .p_class i{line-height:20px;width:78px; color:#ffffff; }
</style>
 <div class="form-group">
    <label class="control-label">图文内容：</label>
    <div class="controls">
        
        <div class="up_image">
            <div id="top_title">
                <p onclick="up_image_p_click(this)" style="background-image:url('<?php echo htmlentities($row['imgurl']); ?>'); background-size:100%;">
                    点击上传图片，360*200
                    <input name="imgurl" value="<?php echo htmlentities($row['imgurl']); ?>" type="hidden">
                </p>
                <span>
                    <div>
                        <strong style="margin-left:2em;">标题：</strong>
                        <input name="title" title="请录入图文标题" class="input-text" style="width:75%;" value="<?php echo htmlentities($row['title']); ?>" type="text">
                    </div>
                    <div>
                        <strong style="margin-left:2em;">简介：</strong>
                        <input name="description" title="请录入图文简介" class="input-text" style="width:75%;" value="<?php echo htmlentities($row['description']); ?>" type="text">
                    </div>
                    <div>
                        <strong>回复类型：</strong>
                        <select id="bind_type" name="bind_type" onChange="show_url_type(this,0);" class="input-medium" style="width:285px;">
                            <option>请选择</option>
                            <?php echo $row['option']; ?>
                         </select>
                    </div>
                    <div  >
                        <strong>绑定关联：</strong>
                        <input name="type_val" id="type_val_0" class="input-text" style="width:50%;" type="text" value="<?php echo htmlentities($row['data']); ?>">
                       <input name="type_val_key" id="type_val_key_0" type="hidden" value="<?php echo htmlentities($row['ext_id']); ?>">
                         <a href="javascript:;" data-remote="<?php echo url('mainadmin/article/searchBox',array('_menu_index'=>0)); ?>"  data-toggle="ajaxModal" class="type_bding_btn_0 btn btn-sm btn-default <?php echo $row['bind_type']=='article' ? '' : 'hide'; ?>" id="article_btn_0" >绑定文章</a> 
                         <a href="javascript:;" data-remote="<?php echo url('shop/sys_admin.goods/searchBox',array('_menu_index'=>0)); ?>"  data-toggle="ajaxModal" class="type_bding_btn_0 btn btn-sm btn-default <?php echo $row['bind_type']=='goods' ? '' : 'hide'; ?>" id="goods_btn_0" >绑定商品</a> 
                        
                    </div>
                   
                </span>
            </div>
            
            <?php if(is_array($row['row_sun']) || $row['row_sun'] instanceof \think\Collection || $row['row_sun'] instanceof \think\Paginator): $i = 0; $__LIST__ = $row['row_sun'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$t): $mod = ($i % 2 );++$i;?>
                <div id="uds_appmsg_item_<?php echo htmlentities($t['id']); ?>" class="appmsg_item">
                    <span>
                        <div>
                            <strong style="margin-left:2em;">标题：&nbsp;</strong>
                            <input type="text" value="<?php echo htmlentities($t['title']); ?>" style="width:65%;" class="input-text" name="ndata[title][<?php echo htmlentities($t['id']); ?>]">
                        </div>
                        <div>
                            <strong>回复类型：</strong>
                            <select style="width:190px;" class="input-medium" onchange="show_url_type(this,<?php echo htmlentities($t['id']); ?>);" name="ndata[bind_type][<?php echo htmlentities($t['id']); ?>]" id="type">
                                 <option>请选择</option>
                                 <?php echo $t['option']; ?>
                            </select>
                        </div>
                        <div>
                           <strong>绑定关联：</strong>
                        <input name="ndata[type_val][<?php echo htmlentities($t['id']); ?>]" id="type_val_<?php echo htmlentities($t['id']); ?>" class="input-text" style="width:50%;" type="text" value="<?php echo htmlentities($t['data']); ?>">
                       <input name="ndata[type_val_key][<?php echo htmlentities($t['id']); ?>]" id="type_val_key_<?php echo htmlentities($t['id']); ?>" type="hidden" value="<?php echo htmlentities($t['ext_id']); ?>">
                       <input name="ndata[ext_activity][<?php echo htmlentities($t['id']); ?>]" id="ext_activity_<?php echo htmlentities($t['id']); ?>" type="hidden" value="<?php echo htmlentities($t['ext_activity']); ?>">
                         <a href="javascript:;" data-remote="<?php echo url('mainadmin/article/searchBox',array('_menu_index'=>$t['id'])); ?>"  data-toggle="ajaxModal" class="type_bding_btn_<?php echo htmlentities($t['id']); ?> btn btn-sm btn-default <?php echo $t['bind_type']=='article' ? '' : 'hide'; ?>" id="article_btn_<?php echo htmlentities($t['id']); ?>" >绑定文章</a> 
                        <a href="javascript:;" data-remote="<?php echo url('shop/sys_admin.goods/searchBox',['_menu_index'=>$t['id']]); ?>"  data-toggle="ajaxModal" class="type_bding_btn_<?php echo htmlentities($t['id']); ?> btn btn-sm btn-default <?php echo $t['bind_type']=='goods' ? '' : 'hide'; ?>" id="goods_btn_<?php echo htmlentities($t['id']); ?>" >绑定商品</a> 
                        
                        </div>
                     </span>
                     
                     <p onclick="up_image_p_click(this)" style="background-image:url('<?php echo htmlentities($t['imgurl']); ?>'); background-size:100%;"><i>点击上传</i><input type="hidden" value="<?php echo htmlentities($t['imgurl']); ?>" name="ndata[imgurl][<?php echo htmlentities($t['id']); ?>]"></p>
                     <p onclick="del_appmsg_item(<?php echo htmlentities($t['id']); ?>,'deldb')" class="p_class"><i class="p_class">删除</i></p>
                </div>
                <input name="ndata[id][<?php echo htmlentities($t['id']); ?>]" type="hidden" value="<?php echo htmlentities($t['id']); ?>">
            <?php endforeach; endif; else: echo "" ;endif; ?>
            
        </div>
      
    </div>
   
 <script>
	// 图片选择与上传
	var editor = KindEditor.editor({
		uploadJson : uploadJ ,
		fileManagerJson : fileManagerJ ,
		allowFileManager : true
	});
	function up_image_p_click(sel) {
	  editor.loadPlugin("smimage", function() {
		  editor.plugin.imageDialog({
			  imageUrl : $(sel).find('input').val(),
			  clickFn : function(url) {
				  $(sel).find('input').val(url);
				  editor.hideDialog();
				  $(sel).css("background-image","url("+url+")");
			  }
		  });
	  });
	}
	// 显示选中的链接类型
	function show_url_type(sel,input_id){
		$(".type_bding_btn_"+input_id).addClass('hide');
		$("#type_val_"+input_id).val('');
		$("#ext_activity_"+input_id).val(0);
		$("#type_val_key_"+input_id).val(0);
		if (sel.value == 'article') $("#article_btn_"+input_id).removeClass('hide');
		else if (sel.value == 'link') $("#type_val_"+input_id).val('http://');
		else if (sel.value == 'goods') $("#goods_btn_"+input_id).removeClass('hide');
	}
	function assigBack(type,type_id,id,title,tid){
	  $("#type_val_key_"+type_id).val(id);
	  $("#ext_activity_"+type_id).val(tid);
	  $("#type_val_"+type_id).val(title);
	  $(".modal-dialog .close").trigger("click");
  }
	
	var new_id = 0;var _new_id_;
	function add_appmsg_item() {
		if(isshowimgwin() >= 9){
			G.ui.tips.info("小图标图文内容不能超过9条！");
			return false;
		}
		new_id ++;
		_new_id_ = new_id+'n';
		var html = [];
		html.push('<div id="uds_appmsg_item_'+_new_id_+'" class="appmsg_item">');
		html.push(' <span>');
		html.push('	 <div><strong style="margin-left:2em;">标题：&nbsp;</strong><input type="text" value="" style="width:65%;" class="input-text" name="new[title]['+_new_id_+']"></div>');
		html.push('	 <div>');
		html.push('	  <strong>回复类型：</strong>');
		html.push('	  <select id="type" name="new[bind_type]['+_new_id_+']" onChange="show_url_type(this,\''+_new_id_+'\');" class="input-medium" style="width:190px;"><option value="">请选择</option><?php echo $option; ?></select>');
		html.push('	 </div>');
		html.push('	 <div >');
		html.push('	 <strong>绑定关联：</strong>');
		html.push('	 <input name="new[type_val]['+_new_id_+']" id="type_val_'+_new_id_+'" class="input-text" style="width:50%;" type="text" value="">');
		html.push('	 <input name="new[type_val_key]['+_new_id_+']" id="type_val_key_'+_new_id_+'" type="hidden" value="">');
		html.push('	 <input name="new[ext_activity]['+_new_id_+']" id="ext_activity_'+_new_id_+'" type="hidden" value="">');
		html.push('	 <a href="javascript:;" data-remote="<?php echo _url("mainadmin/article/searchBox",array("_menu_index"=>"【_new_id_】")); ?>"  data-toggle="ajaxModal" class="type_bding_btn_'+_new_id_+' btn btn-sm btn-default hide" id="article_btn_'+_new_id_+'" >绑定文章</a> ');
		html.push('	 <a href="javascript:;" data-remote="<?php echo _url("shop/sys_admin.goods/searchBox",array("_menu_index"=>"【_new_id_】")); ?>"  data-toggle="ajaxModal" class="type_bding_btn_'+_new_id_+' btn btn-sm btn-default hide" id="goods_btn_'+_new_id_+'" >绑定商品</a>');
		
		html.push('	 </div>');
		html.push('	</span>');
		html.push('	<p onclick="up_image_p_click(this)" style="background-size:100%;"><i>点击上传</i><input name="new[imgurl]['+_new_id_+']" type="hidden" value="" /></p>');
		html.push('	<p class="p_class" onclick="del_appmsg_item(\''+_new_id_+'\')"><i class="p_class">删除</i></p>');
		html.push('</div>');
		$(".up_image").append(html.join(''));
	}
	function del_appmsg_item(input_id,type){
		if (type == 'deldb')
		{
			var res = jq_ajax('<?php echo url("delete"); ?>','id='+input_id);
			if (res.info) alert(res.info);
			if (res.status != 0) return false;
		}
		$("#uds_appmsg_item_"+input_id).remove();
	}
	

	function isshowimgwin(){
		var i = 0;
		$("div.appmsg_item").each(function(){
			i ++;
		});
		return i;
	}
	</script>
         <div class="clearfix"></div>           
        <input name="id" type="hidden" value="<?php echo htmlentities($row['id']); ?>">
             <div class="form-group">
            <div class="controls">
                <label class="control-label"></label><button type="submit" class="btn btn-primary" data-loading-text="保存中...">保存</button>
                <input class="btn" name="button" id="button" onclick="add_appmsg_item();" value="增加小图标图文内容" type="button">
            </div>
        </div>
        </form>
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