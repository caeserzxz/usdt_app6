<?php /*a:4:{s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\region\index.html";i:1552460091;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\base.html";i:1552460091;s:75:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\web_upload.html";i:1552460091;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\page.html";i:1552460091;}*/ ?>
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
        	
<header class="header  b-b clearfix">
     <div class="page-breadcrumbs">
            <ul class="breadcrumb" >
                <li>
                    <i class="fa fa-ellipsis-v"></i>
                    <strong>区域管理</strong>
                </li>                                  
            </ul>
            <div class="upload-file btn btn-sm btn-primary fr  m-t-md m-r" data-uploadpath="<?php echo url('upload'); ?>" data-type='file' data-text='上传execl导入区域信息'> 上传execl导入区域信息</div> 
             <?php if($region['pid'] > '0'): ?><a href="<?php echo url('index',['pid'=>$region['pid']]); ?>" class="btn btn-sm btn-default fr  m-t-md m-r" ><i class="fa fa-reply"></i> 返回上一级</a><?php endif; ?>
      </div>
</header>
    <section class="scrollable wrapper w-f ">
        <section class="panel panel-default ">
           <table class="table table-bordered table-striped table-hover">
                <tr>
                    <th  colspan="3">当前选择的区域：<?php echo htmlentities($region['name']); ?> </th>
                </tr>
                <tr>
                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 3 );++$i;?>	
                    <td><?php echo htmlentities($vo['name']); if($vo['level_type'] < 3): ?> | <a href="<?php echo url('index',['pid'=>$vo['id']]); ?>" class="btn btn-sm btn-default"><i class="fa fa-edit m-r-xs"></i>管理</a><?php endif; ?></td>
                    <?php if($mod == '2'): ?></tr><tr><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                </tr>	
             </table>
        </section>
</section>

    <script src="/static/js/webuploader/webuploader.js"></script>
<script type="text/javascript">//upload
var ratio = window.devicePixelRatio || 1,
thumbnailWidth = 100 * ratio,// 优化retina, 在retina下这个值是2
thumbnailHeight = 100 * ratio,
uploader = null, pick = null,uploadering = 0;
function WebUploaderDiy(_pick){
	    uploader = WebUploader.create({
        // swf文件路径
        swf: '/static/js/webuploader/Uploader.swf',
        // 文件接收服务端。
        server: '<?php echo url("mainAdmin/Attachment/upload"); ?>',
        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: _pick,
        resize: false,
        auto: false,
		duplicate :true
    });
 
    uploader.on('fileQueued', function (file) {   	
        var _this = pick.parent();
        pick.find('div.error').remove();  
        uploadering = 1;        
        if (_this.find('.upload-file').data('type') == 'file'){
      		 _this.find('.upload-file').data('text',_this.find('.webuploader-pick').text());    
             _this.find('.webuploader-pick').text('上传处理中...');           
             uploader.upload();//执行上传  
			  return;      
        }else if (_this.find('.file-item').length > 0){
            var $li = $('<div><img><div class="info">' + file.name + '</div></div>'),
            $img = $li.find('img');
            pick.parent().find('.file-item').html( $li );
        }else if (_this.find('.upload-file').length > 0){
        	 _this.find('.info').data('text',_this.find('.info').text()); 
        	 _this.find('.info').text('上传0%...');  
              uploader.makeThumb( file, function( error, src ) {
                   if ( error ) {
                        $('<div class="error">不能预览</div>').appendTo(pick);               
                        return;
                    }
                   _this.find('.upload-file').css("background-image","url("+src+")");        
                }, 1, 1 );     
             uploader.upload();//执行上传
             return false;         
        }else{            
        	_alert('无法识别上传类型',true);      
            return false;
        }
        // 创建缩略图
        // 如果为非图片文件，可以不用调用此方法。
        // thumbnailWidth x thumbnailHeight 为 100 x 100
        uploader.makeThumb( file, function( error, src ) {
            if ( error ) {
                $img.replaceWith('<span>不能预览</span>');
                return;
            }
            $img.attr( 'src', src );
        }, 1, 1 );     
         uploader.upload();//执行上传    	
    });
    uploader.on( 'uploadProgress', function( file, percentage ) {
        var _this = pick.parent();
        if (_this.find('.upload-file').length > 0){
        	_this.find('.info').text('上传中'+percentage * 100 + '%'); 
            return false;
        }        
        
    	var $li = $( '.uploadify-queue'),
        $percent = $li.find('.uploadify-queue-item');
		if ($li.length < 1 ) return false;
        // 避免重复创建
        if ( !$percent.length ) {
            $percent = $('<div  class="uploadify-queue-item"><span class="fileName">'+file.name+' </span>	<span class="data"></span><div class="uploadify-progress"><div class="uploadify-progress-bar" style="width: 1%;"><!--Progress Bar--></div></div></div>').appendTo( $li ).find('.progress-bar');
        }
       
    	$li.find('.data').html('上传中'+percentage * 100 + '%');
        $li.find('.uploadify-progress-bar').css( 'width', percentage * 100 + '%' );
        if (percentage == 1){        
         	$percent.remove();
        }
	});
    uploader.on('uploadSuccess', function (file, data) {
   		uploadering = 0;
    	if (data.code == 1){
        	_alert(data.msg);
            return false;
        }
     	var _this =  pick.parent();
        if (_this.find('.upload-file').data('type') == 'file'){
        	_this.find('.webuploader-pick').text($(_this).find('.upload-file').data('text'));
      		 return false;
    	}else if (_this.find('.file-item').length > 0){
        	_this.find('.file-item').html("<img src='" + data.src + "' /><div class='info'>" + data.savename + "</div>");
        }else if (_this.find('.upload-file').length > 0){
        	_this.find('.upload-file').css("background-image","url("+data.src+")");
        	_this.find('.info').text($(_this).find('.info').data('text'));
            _this.find('.file_path').val(data.src);
			_this.find('.item_close').data("id",data.image.id);
            _this.find('.info').html('上传成功！');
			if (_this.find('.upload-file').data('sku')){
				 var c = _this.find('.file_path').data("id"),
				d = _this.find('.file_path').data("name"),
				ic = _this.find('.item_close').data("name"),
				f = {};
				f[d] = data.src;
				f[ic] = data.image.id;
				window.goods_data.products[c] = f;
			}
            return false;         
        }else if(_this.find('.info').length > 0){
        	_this.find('.info').html('上传成功！');
        }
        pick.prev().val(data.savename);
    });

	// 文件上传失败，显示上传出错。
    uploader.on('uploadError', function (file) {
      uploadering = 0;
       var $error = pick.find('div.error');
        // 避免重复创建
        if ( !$error.length ) {
            $error = $('<div class="error"></div>').appendTo(pick);
        }

        $error.text('上传失败');
    });
}  
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

<script type="text/javascript">
WebUploaderDiy('.upload-file');
$(document).on('click',".upload-file input", function () {

    pick = $(this).parents('.upload-file');
    var extdata = pick.data('extdata');
    if (uploadering == 1) return false;
    
    if (typeof(pick.data('uploadpath')) != 'undefined'){
    	uploader.options.server = pick.data('uploadpath');
    }
    if (typeof(extdata) == 'object'){
    	uploader.options.formData.extdata = JSON.stringify(extdata);    
    }
});
</script>

</body>
</html>