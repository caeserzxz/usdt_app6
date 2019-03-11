<?php /*a:3:{s:76:"D:\phpStudy\WWW\moduleshop\application\member\view\sys_admin\users\info.html";i:1551662306;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\base.html";i:1550818706;s:71:"D:\phpStudy\WWW\moduleshop\application\mainadmin\view\layouts\page.html";i:1549953095;}*/ ?>
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
                    <strong>会员详情</strong>
                </li>                                  
            </ul>
           <a class="text-muted pull-right pointer p-r m-t-md" data-toggle="back" title="返回"><i class="fa fa-reply"></i></a>
        </div>
</header>
<section class="scrollable  wrapper">
           

                <section class="panel panel-default">
                  <header >
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tabinfo" data-toggle="tab">帐户信息</a></li>                            
                            <li><a href="#tabbank" data-toggle="tab">会员资料</a></li>
                            <li><a href="#tabdescribe" data-toggle="tab">地址信息</a></li>
                            <li><a href="#superior" class="superior_tab" data-toggle="tab">上级关系树</a></li>
                            <li><a href="#tabchain" class="tabchain_tab" data-toggle="tab">下级关系树(九级团队人数 - <?php echo htmlentities($teamCount); ?>)</a></li>                           
                        </ul>
                    </header>
                    
                  <form class="form-horizontal form-validate" method="post" action="<?php echo url('info'); ?>" style="padding:0;">
                       <div class="tab-content">
                            <div class="tab-pane active" id="tabinfo">
                                <div class="form-group">
                                    <label class="control-label">注册手机：</label>
                                    <div class="col-sm-3"><label><?php echo htmlentities($row['mobile']); ?></label></div>
                                    <label class="control-label">会员等级：</label>
                                    <div class="col-sm-3"><label><?php echo htmlentities(userLevel($row['total_integral'])); ?></label></div>
                                </div>
                                 <div class="form-group">
                                    <label class="control-label">真实姓名：</label>
                                    <div class="col-sm-3"><label><?php echo htmlentities((isset($row['real_name']) && ($row['real_name'] !== '')?$row['real_name']:'未填写')); ?></label></div>
                                   <label class="control-label">分销身份：</label>
                                    <div class="col-sm-3"><label><?php echo $row['role_id']==0 ? '普通会员' : htmlentities($roleList[$row['role_id']]['role_name']); ?></label>
                                    <a href="javascript:;" title="修改分销身份" data-remote="<?php echo url('editRole',array('user_id'=>$row['user_id'])); ?>" data-toggle="ajaxModal" class="m-xs" >
                <i class="fa fa-edit"></i>
            </a>
                                    </div>
                                </div>
                               
                                 <div class="form-group">
                                    <label class="control-label">注册时间：</label>
                                    <div class="col-sm-3"><label><?php echo htmlentities(dateTpl($row['reg_time'])); ?></label></div>
                                     <label class="control-label">最近购买时间：</label>
                                    <div class="col-sm-3"><label><?php echo htmlentities(dateTpl($row['last_buy_time'])); ?></label></div>
                                    
                                </div>
                                 <div class="form-group">
                                    <label class="control-label">最近登陆时间：</label>
                                    <div class="col-sm-3"><label><?php echo htmlentities(dateTpl($row['login_time'])); ?></label></div>
                                    <label class="control-label">最近登陆IP：</label>
                                    <div class="col-sm-3"><label><?php echo htmlentities((isset($row['login_ip']) && ($row['login_ip'] !== '')?$row['login_ip']:'未记录')); ?></label></div>
                                </div>
                                 <div class="line line-dashed line-lg pull-in"></div>
                                <div class="form-group">
                                    <label class="control-label">帐户余额：</label>
                                    <div class="col-sm-3">
                                        <label>
                                            <?php echo htmlentities(priceFormat($row['account']['balance_money'],true)); ?> 
                                            <a href="<?php echo url('sys_admin.accountLog/index',array('user_id'=>$row['user_id'],'account_type'=>'balance_money')); ?>" class="m-xs" title="查看明细"><i class="fa fa-search text-muted"></i> 查看明细</a>
                                        </label>
                                    </div>
                                     <label class="control-label">待到帐佣金：</label>
                                    <div class="col-sm-3">
                                        <label><?php echo htmlentities(priceFormat($wait_money,true)); ?>                                        
                                        <a href="<?php echo url('sys_admin.accountLog/index',array('user_id'=>$row['user_id'],'account_type'=>'total_dividend')); ?>" class="m-xs" title="查看明细"><i class="fa fa-search text-muted"></i> 查看明细</a>
                                        </label>
                                    </div>
                                </div>   
                                <div class="form-group">
                                    
                                   
                                     <label class="control-label">历史总佣金：</label>
                                    <div class="col-sm-5">
                                        <label ><?php echo htmlentities(priceFormat($row['account']['total_dividend'],true)); ?> <span class="help-inline">此项记录用户历史所有佣金总和</span></label>
                                   </div>
                                </div>  
                                 <div class="form-group">
                                    <label class="control-label">消费积分：</label>
                                    <div class="col-sm-3">
                                        <label>
                                        <?php echo htmlentities(intval($row['account']['use_integral'])); ?> 
                                        </label>
                                    </div>
                                      <label class="control-label">历史总积分：</label>
                                    <div class="col-sm-3">
                                        <label>
                                        <?php echo htmlentities(intval($row['account']['total_integral'])); ?> 
                                        </label>
                                    </div>
                                </div>   
                               <div class="form-group">
                                  <label class="control-label">下级数量：</label>
                                  <div class="col-sm-5"  >
                                  <label> <?php if(is_array($Dividend) || $Dividend instanceof \think\Collection || $Dividend instanceof \think\Paginator): $i = 0; $__LIST__ = $Dividend;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$dval): $mod = ($i % 2 );++$i;?>
                                     <?php echo htmlentities($d_level[$key]); ?>：<strong><?php echo htmlentities(intval($userShareStats[$key])); ?></strong>；　 
                                   <?php endforeach; endif; else: echo "" ;endif; ?></label>
                                  </div>
                              </div>
                            </div>      
                      	
                            <!--提现帐号-->
                            <div class="tab-pane" id="tabbank" style="overflow: hidden">
                               	<div class="form-horizontal">
                                	<header class="panel-heading bg-light">
                                        会员信息
                                    </header>
                                	 <div class="form-group m-t-md">
                                            <label class="control-label">联系电话：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                <?php echo htmlentities((isset($row['tel']) && ($row['tel'] !== '')?$row['tel']:'未填写')); ?>
                                                </label>
                                            </div>
                                            <label class="control-label">qq：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                 <?php echo htmlentities((isset($row['qq']) && ($row['qq'] !== '')?$row['qq']:'未填写')); ?>
                                                </label>
                                            </div>
                        			</div>
                                    <div class="form-group m-t-md">
                                            <label class="control-label">电子邮箱：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                <?php echo htmlentities((isset($row['email']) && ($row['email'] !== '')?$row['email']:'未填写')); ?>
                                                </label>
                                            </div>
                                            <label class="control-label">生日：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                 <?php echo htmlentities($row['birthday']); ?>
                                                </label>
                                            </div>
                        			</div>
                                    <header class="panel-heading bg-light">
                                        微信信息
                                    </header>
                                  
                                	 <div class="form-group m-t-md" style="position:relative;">
                                       <?php if($row['wx']['wx_headimgurl'] <> ''): ?>
                                            <div style="position:absolute; right:20px; width:120px;"><img src="<?php echo htmlentities($row['wx']['wx_headimgurl']); ?>" style="width:100%" /></div>
                                       <?php endif; ?>
                                            <label class="control-label">呢称：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                <?php echo htmlentities((isset($row['wx']['wx_nickname']) && ($row['wx']['wx_nickname'] !== '')?$row['wx']['wx_nickname']:'未获取')); ?>
                                                </label>
                                            </div>
                                             <label class="control-label">性别：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                <?php echo $row['wx']['sex']==1 ? '男' : htmlentities($row['wx']['sex']==2?'女':'未知'); ?>
                                                </label>
                                            </div>
                                           
                        			</div>
                                    <div class="form-group m-t-md">
                                            <label class="control-label">省份：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                 <?php echo htmlentities((isset($row['wx']['wx_province']) && ($row['wx']['wx_province'] !== '')?$row['wx']['wx_province']:'未获取')); ?>
                                                </label>
                                            </div>
                                             <label class="control-label">城市：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                 <?php echo htmlentities((isset($row['wx']['wx_city']) && ($row['wx']['wx_city'] !== '')?$row['wx']['wx_city']:'未获取')); ?>
                                                </label>
                                            </div>
                        			</div>
                                    <div class="form-group m-t-md">
                                            <label class="control-label">是否关注：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                <?php echo $row['wx']['subscribe']==1 ? '已关注' : '未关注'; ?>
                                                </label>
                                            </div>
                                             <label class="control-label">关注时间：</label>
                                            <div class="col-sm-3">
                                                <label>
                                                 <?php echo htmlentities(dateTpl($row['wx']['wx_subscribe_time'])); ?>
                                                </label>
                                            </div>
                        			</div>
                                	<header class="panel-heading bg-light">
                                        银行卡信息
                                    </header>
                                	 <div class="form-group m-t-md">
                                        <label class="control-label">帐号姓名：</label>
                                        <div class="col-sm-8 "  >
                                            <label><?php echo htmlentities((isset($row['bank_user_name']) && ($row['bank_user_name'] !== '')?$row['bank_user_name']:'未填写')); ?></label>
                                        </div>
                        			</div>
                                     <div class="form-group">
                                        <label class="control-label">银行帐号：</label>
                                        <div class="col-sm-8 "  >
                                            <label><?php echo htmlentities((isset($row['service_bank_no']) && ($row['service_bank_no'] !== '')?$row['service_bank_no']:'未填写')); ?></label>
                                        </div>
                        			</div>
                                    <div class="form-group">
                                        <label class="control-label">开户银行：</label>
                                        <div class="col-sm-8 "  >
                                            <label><?php echo htmlentities((isset($row['service_bank_name']) && ($row['service_bank_name'] !== '')?$row['service_bank_name']:'未填写')); ?></label>
                                        </div>
                        			</div>
                                    <div class="form-group">
                                        <label class="control-label">地区：</label>
                                        <div class="col-sm-8">
                                        <label>
                                        	<?php echo htmlentities($row['bank_region_name']); ?>
                                        </label>
                                        </div>
                        			</div>
                                    <div class="form-group">
                                        <label class="control-label">所属分行：</label>
                                        <div class="col-sm-8">
                                        <label><?php echo htmlentities((isset($row['service_bank_address']) && ($row['service_bank_address'] !== '')?$row['service_bank_address']:'未填写')); ?></label>
                                        </div>
                        			</div>
                                 </div>
                                 
                            </div>
                            
                             <!--地址信息-->
                            <div class="tab-pane" id="tabdescribe" style="overflow: hidden">
                               <div class="table-responsive " id="list_box">
                                    <table class="table table-striped  m-b-none">
                                    <thead>
                                    <tr>
                                    	<th width="100">默认</th>
                                        <th width="200">收货人</th>
                                        <th width="200">联系电话</th>
                                        <th>地址</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                    <?php if(is_array($row['user_address']) || $row['user_address'] instanceof \think\Collection || $row['user_address'] instanceof \think\Paginator): $i = 0; $__LIST__ = $row['user_address'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ua): $mod = ($i % 2 );++$i;?>                                   
                                    <tr>
                                    <td ><a href="javascript:;"  class="<?php echo $row['address_id']==$ua['address_id'] ? 'active' : ''; ?>" data-fun="shelves" data-toggle="class" data-ajax="true"><i class="fa fa-check text-success text-active"></i><i class="fa fa-times text-danger text"></i></a></td>
                                    <td ><?php echo htmlentities($ua['consignee']); ?></td>
                                    <td ><?php echo htmlentities($ua['mobile']); ?></td>
                                    <td ><?php echo htmlentities($ua['merger_name']); ?> - <?php echo htmlentities($ua['address']); ?></td>
                                    </tr> 
                                    <?php endforeach; endif; else: echo "" ;endif; if(empty($row['user_address']) || (($row['user_address'] instanceof \think\Collection || $row['user_address'] instanceof \think\Paginator ) && $row['user_address']->isEmpty())): ?>
                                     <tr>
                                      <td colspan="4" align="center" style="height:150px;">暂无收货地址信息！</td>
                                      </tr>
                                      <?php endif; ?>  
                                    </tbody>
                                    </table>
                                </div>                                
                            </div>
                            
                            <!--上级关系树-->
                            <div class="tab-pane" id="superior" style="overflow: hidden">
                               	<div class="table-responsive"></div>
                             </div>
                             
                              <!--下级关系树-->
                            <div class="tab-pane" id="tabchain" style="overflow: hidden">
                               	<div class="table-responsive"></div>
                             </div>
                            
                            
                    </div>
                    
                   </form>  
                </section>

         
        </section>
    </section>
<script type="text/html" id="chainlist">
{{each list as item index}}
<div class="form-group" style=" margin-left:0px;">                                            
	<div class="col-sm-8">	
		<a href="javascript:void(0)" class="btn next_btn" data-id="{{item.user_id}}" ><i class="fa fa-search m-r"></i>{{item.user_id}} - {{item.nick_name?item.nick_name:'未获取'}} - {{item.role_name?item.role_name:'普通会员'}} - 团队数（{{item.teamCount}}）</a>	
	</div>
	<div class="nextList" id="next_box_{{item.user_id}}" style="padding-left:50px; padding-top:10px;clear:both;"></div>
</div>
{{/each}}
</script>
<script type="text/html" id="superiorlist">
{{each list as item index}}
 <div class="form-group" style=" margin-left:0px;">                                            
	  <div class="col-sm-8">
	  {{item.level}}级 - 会员ID:{{item.user_id}} - 昵称：{{item.nick_name}} - 身份：{{item.role_name}} - 注册时间：{{item.reg_time|dateTpl}}	  
	  </div>
  </div>
{{/each}}
</script>

<script type="text/javascript">
	$('.superior_tab').click(function(){
		if ($('#superior').find('.table-responsive').html() == ''){
			getSuperiorList(<?php echo htmlentities($row['user_id']); ?>);
		}
	})
	$('.tabchain_tab').click(function(){
		if ($('#tabchain').find('.table-responsive').html() == ''){
			getChainList(<?php echo htmlentities($row['user_id']); ?>);
		}
	})
	$(document).on('click','.next_btn',function(){
		getChainList($(this).data('id'),true)
	})
	//加载上级
	function getSuperiorList(uid){
		jq_ajax('<?php echo url("sys_admin.users/getSuperiorList"); ?>','user_id='+uid,function(res){
			if (res.list.length < 1){
				$('#superior .table-responsive').html('没有相关信息.');
				return false;
			}
			$('#superior .table-responsive').html(template('superiorlist',res));
			
		});
	}
	//加载下级
	function getChainList(uid,next){
		jq_ajax('<?php echo url("sys_admin.users/getChainList"); ?>','user_id='+uid,function(res){
			if (res.list.length < 1){
				if (next == true){				
				}else{
					$('#tabchain .table-responsive').html('没有了');
				}
				return false;
			}
			if (next == true){
				$('#next_box_'+uid).html(template('chainlist',res));
			}else{
				$('#tabchain .table-responsive').html(template('chainlist',res));
			}
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

</body>
</html>