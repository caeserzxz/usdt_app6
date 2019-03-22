<?php /*a:3:{s:74:"D:\phpStudy\WWW\mainshop\application\weixin\view\sys_admin\menu\index.html";i:1552460091;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\base.html";i:1552460091;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\page.html";i:1552460091;}*/ ?>
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
            <ul class="breadcrumb">
                <li>
                    <i class="fa fa-ellipsis-v"></i>
                    <strong>微信自定义菜单</strong>
                </li>                                  
            </ul>
           
       </div>
</header>
<section class="scrollable  wrapper">
      <section class="panel panel-default">
          <section class="scrollable wrapper">
      <div class="alert">
          <p>注意：1级菜单最多只能开启3个，2级子菜单最多开启5个!</p>
          <p>只有保存主菜单后才可以添加子菜单</p>
          <p>生成自定义菜单,必须在已经保存的基础上进行,临时勾选启用点击生成是无效的! 第一步必须先修改保存状态！第二步点击生成!</p>
          <p>当您为自定义菜单填写链接地址时请填写以"http://"开头，这样可以保证用户手机浏览的兼容性更好</p>
          <p>撤销自定义菜单：撤销后，您的微信公众帐号上的自定义菜单将不存在；如果您想继续在微信公众帐号上使用自定义菜单，请点击"生成自定义菜单"按钮，将重新启用！</p>
        </div>
        <section class="panel panel-default ">
         <form action="<?php echo url('save'); ?>" method="post" class="form-horizontal form-validate"  style="padding:0;">             
            <table id="listTable" class="table table-bordered table-hover dataTable">
              <thead>
                <tr>
                  <th width="70">顺序</th>
                  <th width="250">主菜单名称</th>
                  <th width="150">响应动作类型</th>
                  <th width="120">绑定操作</th>
                  <th>响应值</th>
                  <th width="70">启用</th>
                  <th width="70">操作</th>
                </tr>
              </thead>                  
              <?php if(is_array($rows) || $rows instanceof \think\Collection || $rows instanceof \think\Paginator): $i = 0; $__LIST__ = $rows;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;if(empty($vo['pid']) || (($vo['pid'] instanceof \think\Collection || $vo['pid'] instanceof \think\Paginator ) && $vo['pid']->isEmpty())): if($i > '1'): ?></thead><?php endif; ?>
                    <thead id="Submenu_<?php echo htmlentities($vo['id']); ?>">
                <?php endif; ?>
                <tr class="<?php echo $vo['icon']=='' ? 'ztr' : 'ptr'; ?>">
                  <td><input type="text" class="input-ssmall" size="3" value="<?php echo htmlentities($vo['sort']); ?>" name="ps[<?php echo htmlentities($vo['id']); ?>][sort]"   /></td>
                  <td>
                 <?php if($vo['pid'] == 0): ?><i class="fa fa-plus cursor_p add" title="添加子菜单" rel="<?php echo htmlentities($vo['id']); ?>" style="float:right; padding-right:10px; padding-top:8px;"></i><?php endif; if(($vo['icon'] != '')): ?><i class='board'></i><?php endif; ?>
                    <input type="text" class="input-medium" size="15" value="<?php echo htmlentities($vo['name']); ?>"  name="ps[<?php echo htmlentities($vo['id']); ?>][name]" data-rule-required="true" data-rule-maxlength="30" style="width:150px;" />
                  </td>
                  <td>
                    <select name="ps[<?php echo htmlentities($vo['id']); ?>][type]" id="ps_type_<?php echo htmlentities($vo['id']); ?>" class="input-small" style="width:150px;" onChange="getKeywordSelect(this,'ps',<?php echo htmlentities($vo['id']); ?>);">
                        <option value="0">--请选择--</option>
                        <?php echo $vo['event_select']; ?>
                    </select>
                  </td>
                  <td>
                    <input type="hidden" value="<?php echo htmlentities($vo['pid']); ?>" name="ps[<?php echo htmlentities($vo['id']); ?>][parent_id]"/>
                    <input name="ps[<?php echo htmlentities($vo['id']); ?>][keyword]" id="ps_keyword_<?php echo htmlentities($vo['id']); ?>" class="input-text <?php echo $vo['keyword_value']=='' ? 'hide' : ''; ?>" style="width:50px;" value="<?php echo htmlentities($vo['keyword']); ?>" type="hidden" >
                    <a href="javascript:;" data-remote="<?php echo url('mainadmin/article/searchBox',array('searchType'=>'ps','_menu_index'=>$vo['id'])); ?>"  data-toggle="ajaxModal" class="ps_bding_btn_<?php echo htmlentities($vo['id']); ?> btn btn-sm btn-default <?php echo $vo['type']=='view' ? '' : 'hide'; ?>" id="ps_Article_btn_<?php echo htmlentities($vo['id']); ?>" >绑定文章</a>
                                          
                    <a href="javascript:;" data-remote="<?php echo url('sys_admin.replyText/searchBox',array('searchType'=>'ps','_menu_index'=>$vo['id'])); ?>" data-toggle="ajaxModal" class="ps_bding_btn_<?php echo htmlentities($vo['id']); ?> btn btn-sm btn-default <?php echo $vo['type']=='click' ? '' : 'hide'; ?>" id="ps_ReplyText_btn_<?php echo htmlentities($vo['id']); ?>">绑定关键字</a>
                  </td>
                  <td><input name="ps[<?php echo htmlentities($vo['id']); ?>][keyword_value]" id="ps_keyword_value_<?php echo htmlentities($vo['id']); ?>"  style="width:100%" value="<?php echo htmlentities($vo['keyword_value']); ?>" type="text" class="input-text <?php echo $vo['keyword_value']=='' ? 'hide' : ''; ?>" ></td>
                  <td>
                  <label class="m-t">
                  <input class="checkbox-slider colored-blue" name="ps[<?php echo htmlentities($vo['id']); ?>][is_show]" type="checkbox" value="1" { <?php echo $vo['is_show']==1 ? 'checked' : ''; ?>>
                  <span class="text"></span>
                  </label>
                  </td>
                  <td><a href="javascript:G.ui.tips.confirm('您确定要删除此菜单吗?', '<?php echo url('delete',array('id'=>$vo['id'])); ?>');" class="fa fa-trash"></a></td>
                </tr>
              <?php endforeach; endif; else: echo "" ;endif; ?>
              
            </table>
            <div class="form-group m-t">
              <a href="javascript:void(0)" id="add_menu" class="btn  btn-default m-r" style=" margin-left:20px;"><i class="fa fa-plus m-r-xs"></i>添加主菜单</a>
              <button id="bsubmit" type="submit" data-loading-text="提交中..." class="btn btn-primary">保存</button>
              <button id="create_menu" type="button" class="btn btn-primary" style="margin:0px 20px;">推送菜单到微信公众号(先保存)</button>
              <button id="remove_menu" type="button" class="btn">撤销自定义菜单</button>
                
            </div>
          </form>
     </section>
</section>

             
<script type="text/javascript">

	   var _add_menu = $("#add_menu");
	   var _add_zmenu = $("i.add");
	   var _menu_index = 0;
	   
	   // 添加主菜单事件
	   _add_menu.click(function (e) {
		   e.preventDefault();
		   _menu_index++;
		   var _menuPtrtmp = '<thead id="Submenu_'+_menu_index+'"><tr>' 
						   + '<td><input name="new[sort][' + _menu_index + ']" size="3" type="text" value="0" class="input-ssmall"  /></td>'
						   + '<td><i class="fa fa-plus cursor_p add" title="添加子菜单" rel="'+_menu_index+'" style="float:right; padding-right:10px; padding-top:8px;"></i><input name="new[name][' + _menu_index + ']" size="15" type="text" class="input-medium" data-rule-required="true" data-rule-maxlength="30" style="width:150px;" /></td>'
						   + '<td>'
						   + '<select name="new[type][' + _menu_index + ']" id="new_type_' + _menu_index + '" class="input-small" style="width:100%" onChange="getKeywordSelect(this,-1,' + _menu_index + ');">'
						   + '    <option value="0">--请选择--</option>'
						   + '	<?php echo $WeixinEventType_opt; ?>'
						   + '</select>'
						   + '</td>'
						   + '<td>'
						   + '<a href="javascript:;" data-remote="<?php echo url("MAdmin/Article/searchBox",array("searchType"=>"new")); ?>&_menu_index='+_menu_index+'"  data-toggle="ajaxModal" class="new_bding_btn_' + _menu_index + ' btn btn-sm btn-default hide" id="new_Article_btn_' + _menu_index + '" >绑定文章</a> <a href="javascript:;" data-remote="<?php echo url("ReplyText/searchBox",array("searchType"=>"new")); ?>&_menu_index='+_menu_index+'" data-toggle="ajaxModal" class="new_bding_btn_' + _menu_index + ' btn btn-sm btn-default hide" id="new_ReplyText_btn_' + _menu_index + '">绑定关键字</a>'						 
						   + '</td><td>'
						   + '<input type="hidden" name="new[parent_id][' + _menu_index + ']" value="{pid}" />'
						   + '<input name="new[keyword][' + _menu_index + ']" id="new_keyword_' + _menu_index + '" type="hidden" >'
						   + '<input name="new[keyword_value][' + _menu_index + ']" id="new_keyword_value_' + _menu_index + '" class="hide input-medium"  type="text"  style="width:100%">'
						   + '</td>'
						   + '<td><label class="m-t"><input class="checkbox-slider colored-blue" name="new[is_show][' + _menu_index + ']" type="checkbox" value="1" checked ><span class="text"></span></label></td>'
						   + '<td><a href="javascript:void(0)" class="del fa fa-trash"></a></td>'
						   + '</tr> </thead>';
		  $("#listTable").append(_menuPtrtmp.replace("{pid}", 0));
	  });
	  
	  // 添加子菜单事件
	  $(document).on('click',"i.add",function() {
		  var $pid = $(this).attr("rel");		
		  _menu_index++;
		  var _menuPtrtmp = '<tr>'
						  + '<td><input name="new[sort][' + _menu_index + ']" size="3" type="text" value="0" class="input-ssmall"   /></td>'
						  + '<td><i class="board"></i><input name="new[name][' + _menu_index + ']" size="15" type="text" class="input-medium" data-rule-required="true" data-rule-maxlength="30" style="width:150px;"/></td>'
						  + '<td>'
						  + '  <select name="new[type][' + _menu_index + ']" id="new_type_' + _menu_index + '" class="input-small" style="width:150px"  onChange="getKeywordSelect(this,-1,' + _menu_index + ');">'
						  + '    <option value="0">--请选择--</option>'
						   + '	<?php echo $WeixinEventType_opt; ?>'
						  + '  </select>'
						  + '</td><td>'
						  + '  <a href="javascript:;" data-remote="<?php echo url("MAdmin/Article/searchBox",array("searchType"=>"new")); ?>&_menu_index='+_menu_index+'"  data-toggle="ajaxModal" class="new_bding_btn_' + _menu_index + ' btn btn-sm btn-default hide" id="new_Article_btn_' + _menu_index + '" >绑定文章</a> <a href="javascript:;" data-remote="<?php echo url("ReplyText/searchBox",array("searchType"=>"new")); ?>&_menu_index='+_menu_index+'" data-toggle="ajaxModal" class="new_bding_btn_' + _menu_index + ' btn btn-sm btn-default hide" id="new_ReplyText_btn_' + _menu_index + '">绑定关键字</a>'
						+ '</td><td>'
						  + '  <input type="hidden" name="new[parent_id][' + _menu_index + ']" value="{pid}" />'
						  + '  <input name="new[keyword][' + _menu_index + ']" id="new_keyword_' + _menu_index + '"   type="hidden"   >'
						  + '  <input name="new[keyword_value][' + _menu_index + ']" id="new_keyword_value_' + _menu_index + '" class="hide input-medium" type="text"   style="width:100%">'
						
						  + '</td>'
						  + '<td><label><input class="checkbox-slider colored-blue" name="new[is_show][' + _menu_index + ']" type="checkbox" value="1" checked ><span class="text"></span></label></td>'
						  + '<td><a href="javascript:void(0)" class="del fa fa-trash"></a></td>'
						  + '</tr>';
		  var tp = _menuPtrtmp.replace("{pid}", $pid);
		  
		   $("#Submenu_"+$pid).append(tp);
		 
	  });
	  
	  $("#listTable .del").live("click", (function () {
		  $(this).parents("tr").remove();
	  }));
	  
	  $("input.type").live("change", function () {
		  var $this = $(this);
		  var $val = $this.val();
		  var $nex = $this.nextAll("input.key_type");
		  var re = /[a-zA-z]+:\/\/[^\s]*/i;
		  if (re.test($val)) { $nex.val(2) } else { $nex.val(1) };
	  });
	  
	  $("#create_menu").click(function () {
		  $.post("<?php echo url('push'); ?>",{ },function(res){
			  _alert(res.msg);
		  },'json');
	  });
	  
	  $("#remove_menu").click(function () {
		  G.ui.tips.confirm_flag('撤销后微信公众号中不再显示自定义菜单，确定要撤销吗？',function(){
			  $.post("<?php echo url('remove'); ?>",{ },function(res){
				  $.fallr('hide');
				  _alert(res.msg);
			  },'json');
		  });
	  });

 function getKeywordSelect(obj,type,id){
	 if(type == 'ps') type = 'ps';
	 else if(type == '-1') type = 'new';
	 $("#"+type+"_keyword_"+id).val('');
	 $("#"+type+"_keyword_value_"+id).val('');
	 $("."+type+"_bding_btn_"+id).addClass('hide');
	 $("#"+type+"_keyword_value_"+id).addClass('hide');
	 if(obj.value == 'view'){
	   $("#"+type+"_Article_btn_"+id).removeClass('hide');
	   $("#"+type+"_keyword_value_"+id).removeClass('hide');
	   $("#"+type+"_keyword_value_"+id).val('http://');
	 }else if(obj.value == 'click'){
	   $("#"+type+"_keyword_value_"+id).removeClass('hide');
	   $("#"+type+"_ReplyText_btn_"+id).removeClass('hide');
	 }
	 $(".modal-dialog .close").trigger("click");
 }
  
  function assigBack(type,type_id,id,title){
	  $("#"+type+"_keyword_value_"+type_id).val(title);
	  $("#"+type+"_keyword_"+type_id).val(id);
	  $(".modal-dialog .close").trigger("click");
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

</body>
</html>