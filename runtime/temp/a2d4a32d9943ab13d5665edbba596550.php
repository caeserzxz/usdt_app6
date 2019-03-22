<?php /*a:3:{s:83:"D:\phpStudy\WWW\mainshop\application\distribution\view\sys_admin\setting\index.html";i:1553217360;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\base.html";i:1553129665;s:69:"D:\phpStudy\WWW\mainshop\application\mainadmin\view\layouts\page.html";i:1552460091;}*/ ?>
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
        	
<header>
    <div class="page-breadcrumbs">
        <ul class="breadcrumb">
            <li>
                <i class="fa fa-ellipsis-v"></i>
                <strong>提成设置</strong>
            </li>
        </ul>
        <div style="float:right; padding-right:10px;">
            <a class="refresh" id="refresh-toggler" href=""><i class="fa fa-refresh"></i></a>
        </div>
    </div>
</header>
<section class="scrollable  wrapper">
    <form class="form-horizontal form-validate" method="post" action="<?php echo url('save'); ?>">
        <section class="panel panel-default">
            <header>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#basic" data-toggle="tab">基本配置</a></li>
                    <li><a href="#bydividend" data-toggle="tab">分佣说明</a></li>
                    <li><a href="#byrole" data-toggle="tab">身份升级说明</a></li>
                </ul>
            </header>

            <div class="tab-content">
                <div class="tab-pane active" id="basic">
                    <div class="form-group m-t">
                        <label class=" control-label">是否开启推荐：</label>
                        <div class="controls">
                            <label class="radio-inline">
                                <input name="status" value="0" <?php echo $Dividend['status']==0 ? 'checked' : ''; ?> type="radio" >停用
                            </label>
                            <label class="radio-inline">
                                <input name="status" value="1" <?php echo $Dividend['status']==1 ? 'checked' : ''; ?> type="radio">启用
                            </label>
                            <span class="help-inline">（停用后，将不执行推荐关系绑定）</span>
                        </div>
                    </div>
                    <div class="form-group m-t">
                        <label class=" control-label">绑定关系时间：</label>
                        <div class="controls">
                            <label class="radio-inline">
                                <input name="bind_type" value="0" <?php echo $Dividend['bind_type']==0 ? 'checked' : ''; ?> type="radio"
                                > 注册
                            </label>
                            <label class="radio-inline">
                                <input name="bind_type" value="1" <?php echo $Dividend['bind_type']==1 ? 'checked' : ''; ?> type="radio">
                                订单支付后
                            </label>
                        </div>
                    </div>
                    <div class="form-group m-t">
                        <label class=" control-label">分享权限：</label>
                        <div class="controls">
                            <label class="radio-inline">
                                <input name="share_by_role" value="0" <?php echo $Dividend['share_by_role']==0 ? 'checked' : ''; ?>
                                type="radio"
                                > 无需身份
                            </label>
                            <label class="radio-inline">
                                <input name="share_by_role" value="1" <?php echo $Dividend['share_by_role']==1 ? 'checked' : ''; ?>
                                type="radio">
                                需分佣身份
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" control-label">佣金到帐时间：</label>
                        <div class="col-sm-8 controls">
                            <strong><?php echo $shop_after_sale_limit==0 ? '订单签收到帐' : htmlentities($shop_after_sale_limit.'天'); ?></strong>
                            <span class="help-inline">（与订单申请售后时间一致，即签收后过了此时间即到帐，如需修改请前往【商城-商城设置】中修改）</span>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" control-label">结算间隔：</label>
                        <div class="col-sm-8 controls">
                            <select name="settlement_day" style="width:100px;">
                                <option value="0" <?php echo $Dividend[
                                'settlement_day']==0 ? 'selected' : ''; ?>>不限制</option>
                                <?php $__FOR_START_7386__=1;$__FOR_END_7386__=121;for($day=$__FOR_START_7386__;$day < $__FOR_END_7386__;$day+=1){ ?>
                                <option value="<?php echo htmlentities($day); ?>" <?php echo $Dividend['settlement_day']==$day ? 'selected' : ''; ?>><?php echo htmlentities($day); ?> 天</option>
                                <?php } ?>
                            </select>
                            <span class="help-inline">天（订单过售后期超过指定天数后执行旅游豆到帐）</span>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" control-label">手动结算：</label>
                        <div class="col-sm-8 controls">
                            <input type="text" id="arrival_code" class="input-mini" placeholder="校验码" value="">
                            <a href="javascript:;" class="btn btn-sm btn-danger" id="evalArrival"><i
                                    class="fa fa-hand-pointer-o m-r-xs"></i>执行结算</a>
                            <span class="help-inline">操作此项将针对过了售后期的订单立即执行旅游豆到帐，操作请输入校检码：<em id="sarrival_code_em"></em></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">复购限制：</label>
                        <div class="col-sm-9 controls">
                            <select name="repeat_buy_day" style="width:100px;">
                                <option value="0" <?php echo $Dividend[
                                'repeat_buy_day']==0 ? 'selected' : ''; ?>>不限制</option>
                                <?php $__FOR_START_19402__=1;$__FOR_END_19402__=121;for($day=$__FOR_START_19402__;$day < $__FOR_END_19402__;$day+=1){ ?>
                                <option value="<?php echo htmlentities($day); ?>" <?php echo $Dividend[
                                'repeat_buy_day']==$day ? 'selected' : ''; ?>><?php echo htmlentities($day); ?> 天</option>
                                <?php } ?>
                            </select>
                            <span class="help-inline">天（成为合伙人及以上会员，从成会合伙人的那天开始计算，达指定天数需要复购一次，否则不享受管理奖和平推奖资格）</span>
                        </div>
                    </div>

                    <div class="line line-dashed line-lg pull-in" style="width:99%;"></div>
                    <div class="form-group">
                        <label class=" control-label">分享海报：</label>
                        <div class="controls col-sm-6">
                            <img class="thumb_img" src="<?php echo htmlentities($share_bg); ?>" style="max-height: 100px;"/><br>
                            <input class="hide" type="text" name="share_bg" value="<?php echo htmlentities($share_bg); ?>"/>
                            <button class="btn btn-default" type="button" data-toggle="selectimg">选择分享背景图</button>
                            <span class="help-inline">建议图片尺寸：320*320像素</span><br>
                        </div>
                    </div>
                </div>
                <div class="tab-pane " id="bydividend">
                    <div class="form-group">
                        <label class="control-label">是否显示：</label>
                        <div class="col-sm-9">
                            <label class="radio-inline">
                                <input name="setting[dividend_directions_status]" value="0" class="js_undertake"
                                       type="radio" <?php echo htmlentities(tplckval($setting['dividend_directions_status'],'=0','checked',true)); ?>>隐藏
                            </label>
                            <label class="radio-inline">
                                <input name="setting[dividend_directions_status]" value="1" class="js_undertake "
                                       type="radio" <?php echo htmlentities(tplckval($setting['dividend_directions_status'],'=1','checked')); ?>>
                                显示
                            </label>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group publicnote_status">
                        <label class="control-label">分佣说明：</label>
                        <div class="col-sm-9 " style="padding-left:0px;">
                            <textarea rows="8" class="input-max hd" data-toggle="kindeditor" data-config="simple"
                                      data-kdheight="150" data-tongji="remain"
                                      data-tongji-target=".js_kindeditor_tongji" data-rule-rangelength="[0,50000]" d
                                      name="setting[dividend_directions]" style="visibility:hidden;"><?php echo htmlentities($setting['dividend_directions']); ?></textarea>
                            <p class="pull-right js_kindeditor_tongji">还可输入{0}字</p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane " id="byrole">
                    <div class="form-group">
                        <label class=" control-label">是否显示：</label>
                        <div class="col-sm-9">
                            <label class="radio-inline">
                                <input name="setting[role_directions_status]" value="0" class="js_undertake"
                                       type="radio" <?php echo htmlentities(tplckval($setting['role_directions_status'],'=0','checked',true)); ?>>隐藏
                            </label>
                            <label class="radio-inline">
                                <input name="setting[role_directions_status]" value="1" class="js_undertake "
                                       type="radio" <?php echo htmlentities(tplckval($setting['role_directions_status'],'=1','checked')); ?>>
                                显示
                            </label>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group publicnote_status">
                        <label class="control-label">身份升级说明：</label>
                        <div class="col-sm-9 " style="padding-left:0px;">
                            <textarea rows="8" class="input-max hd" data-toggle="kindeditor" data-config="simple"
                                      data-kdheight="150" data-tongji="remain"
                                      data-tongji-target=".js_kindeditor_tongji" data-rule-rangelength="[0,50000]" d
                                      name="setting[role_directions]" style="visibility:hidden;"><?php echo htmlentities($setting['role_directions']); ?></textarea>
                            <p class="pull-right js_kindeditor_tongji">还可输入{0}字</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="line line-dashed line-lg pull-in"  style="width:99%;"></div>
            <div class="form-group">
                <label class=" control-label"></label>
                <button type="submit" class="btn btn-blue" data-loading-text="保存中...">保存</button>

            </div>

        </section>
    </form>
</section>

</section>

<script type="text/javascript">
    var arrival_code = Math.ceil(Math.random() * 1000) + 1000;
    $('#sarrival_code_em').html(arrival_code);
    $('#evalArrival').click(function () {
        var _arrival_code = $('#arrival_code').val();
        if (_arrival_code == '') {
            _alert('请输入校验码，后再操作.', true);
            return false;
        }
        if (_arrival_code != arrival_code) {
            _alert('校验码为' + arrival_code + '，请核实输入是否正确.', true);
            return false;
        }
        _confirm('确定执行 - 手动执行结算？', function () {
            jq_ajax('<?php echo url("evalArrival"); ?>', '', function (res) {
                _alert(res.msg, true);
            });
        });
    })
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