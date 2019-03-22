<?php /*a:4:{s:73:"D:\phpStudy\WWW\mainshop\application\shop\view\sys_admin\goods\index.html";i:1552460091;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\base.html";i:1553129665;s:72:"D:\phpStudy\WWW\mainshop\application\shop\view\sys_admin\goods\list.html";i:1552460091;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\page.html";i:1552460091;}*/ ?>
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
        	

<header class="header  b-b clearfix">
    <form id="forms" class="talbe-search form-inline "  method="post" action="<?php echo url($is_delete==0?'getList':'trashList'); ?>" >
     <div class="page-breadcrumbs">
            <ul class="breadcrumb" >
                <li>
                    <i class="fa fa-ellipsis-v"></i>
                    <strong><?php echo $is_delete==0 ? '商品列表' : '商品回收站'; ?></strong>
                </li>                                  
            </ul>
            		<select name="brand_id"  style="width: 150px;" data-toggle="select2" data-chang="submit">
                          <option value="">所有品牌</option>
                          <?php echo $brandListOpt; ?>
                     </select>
                  	<select name="cat_id"  style="width: 150px;" data-toggle="select2"  data-chang="submit">
                           <option value="">所有分类</option>
                           <?php echo $classListOpt; ?>
                     </select>
                    <select name="is_promote" style="width: 150px;" data-toggle="select2" data-chang="submit">
                        <option value="-1">是否限时促销</option>
                        <option value="1">是</option>
                        <option value="0">否</option>
                    </select>
                    <select name="status" style="width: 100px;" data-toggle="select2"  data-chang="submit">
                        <option value="">状态</option>
                        <option value="1">上架中</option>
                        <option value="2">未上架</option>
                    </select>
           			 <input type="text" class="form-control input-large" value="<?php echo htmlentities($search['keyword']); ?>" name="keyword" placeholder="商品名称/SN" data-rule-required="true" />
              <button class="btn btn-sm btn-default-iq" type="submit" title="搜索"><i class="fa fa-search"></i></button>
              <?php if($is_delete == '0'): ?>
              	<a href="<?php echo url('info'); ?>"  title="添加商品" class="btn btn-sm btn-default fr m-t-md m-r"><i class="fa fa-plus m-r-xs"></i>添加商品</a>
              <?php endif; ?>
        </div>
    </form>
</header>

<section class="scrollable wrapper w-f ">
    <section class="panel panel-default ">
        <div class="table-responsive " id="list_box">
            <table class="table table-bordered table-striped ">
<thead class="flip-content bordered-palegreen">
<tr>
    <th width="120">商品编号</th>
    <th>商品名</th>
    <th width="80">价格</th>
    <th width="60">上架</th>
    <th width="60">分销</th>
    <th width="60">促销</th>
    <th width="60">推荐</th>
    <th width="60">新品</th>
    <th width="60">热销</th>
    <th width="80">库存</th>
    <th width="60">排序</th>
    <th width="80">操作</th>
</tr>
</thead>
<tbody>
<?php if(is_array($data['list']) || $data['list'] instanceof \think\Collection || $data['list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
      	<td><?php echo $vo['is_spec']==0 ? htmlentities($vo['goods_sn']) : '多规格'; ?></td>
        <td><?php echo htmlentities($vo['goods_name']); ?></td>
        <td><?php echo $vo['is_spec']==0 ? htmlentities($vo['shop_price']) : htmlentities($vo['show_price']); ?></td>
        <td align="center"><a href="javascript:;" class="<?php echo htmlentities(tplckval($vo['isputaway'],'=1','active')); ?>" ><i class="fa fa-check text-success text-active"></i><i class="fa fa-times text-danger text"></i></a></td>   
        <td align="center"><a href="javascript:;" class="<?php echo htmlentities(tplckval($vo['is_dividend'],'=1','active')); ?>"><i class="fa fa-check text-success text-active"></i><i class="fa fa-times text-danger text"></i></a></td>    
		<td align="center"><a href="javascript:;" class="<?php echo htmlentities(tplckval($vo['is_promote'],'=1','active')); ?>"><i class="fa fa-check text-success text-active"></i><i class="fa fa-times text-danger text"></i></a></td>
        
        <td align="center"><a href="#" data-remote="<?php echo url('ajaxEdit',array('goods_id'=>$vo['goods_id'],'field'=>'is_best')); ?>" class="<?php echo htmlentities(tplckval($vo['is_best'],'=1','active')); ?>"  data-toggle="class" data-ajax="true"><i class="fa fa-check text-success text-active"></i><i class="fa fa-times text-danger text"></i></a></td>
        <td align="center"><a href="#" data-remote="<?php echo url('ajaxEdit',array('goods_id'=>$vo['goods_id'],'field'=>'is_new')); ?>" class="<?php echo htmlentities(tplckval($vo['is_new'],'=1','active')); ?>"  data-toggle="class" data-ajax="true"><i class="fa fa-check text-success text-active"></i><i class="fa fa-times text-danger text"></i></a></td>
        <td align="center"><a href="#" data-remote="<?php echo url('ajaxEdit',array('goods_id'=>$vo['goods_id'],'field'=>'is_hot')); ?>" class="<?php echo htmlentities(tplckval($vo['is_hot'],'=1','active')); ?>"  data-toggle="class" data-ajax="true"><i class="fa fa-check text-success text-active"></i><i class="fa fa-times text-danger text"></i></a></td>
        <td align="center">
       <?php echo htmlentities($vo['goods_number']); ?>
        </td>
        <td align="center"><span data-url="<?php echo url('ajaxEdit',array('goods_id'=>$vo['goods_id'])); ?>" data-field="sort_order" data-toggle="ajaxEditInput"><?php echo htmlentities($vo['sort_order']); ?></span></td>
        <td align="center">
        <?php if($is_delete == 0): ?>
        	<a href="<?php echo url('info',array('goods_id'=>$vo['goods_id'])); ?>"  title="编辑" class="fa fa-edit m-xs" ></a>
   			<a href="<?php echo url('del',array('goods_id'=>$vo['goods_id'])); ?>" data-toggle="ajaxRemove" data-msg="确定将 <?php echo htmlentities($vo['goods_name']); ?> 放入回收站？"  class="fa fa-trash"  title="回收"></a>
         <?php else: ?>
         	<a href="<?php echo url('revert',array('goods_id'=>$vo['goods_id'])); ?>" data-toggle="ajaxRemove" data-msg="确定还原 <?php echo htmlentities($vo['goods_name']); ?> ？" title="还原"  class="fa fa-repeat m-r"></a>
         <?php endif; ?>
        </td>
    </tr>
<?php endforeach; endif; else: echo "" ;endif; ?>
</tbody>
</table>
<?php if(empty($data['list']) || (($data['list'] instanceof \think\Collection || $data['list'] instanceof \think\Paginator ) && $data['list']->isEmpty())): ?>
<table width="100%" >
 	<tr><td height="300" colspan="8" align="center" valign="middle" >没有相关数据！</td></tr>
</table>
<?php endif; ?>  
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