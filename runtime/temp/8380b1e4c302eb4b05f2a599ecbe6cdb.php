<?php /*a:3:{s:44:"../template/default/shop\index\all_sort.html";i:1550132957;s:37:"../template/default/layouts\base.html";i:1551315553;s:39:"../template/default/layouts\bottom.html";i:1551092090;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
  <head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
    <meta name="format-detection" content="telephone=no" />
    <link rel="stylesheet" href="/static/mobile/default/css/layout.css?v=2"/>
    <link rel="icon" type="image/png" href="/static/favicon.ico"/>
    <script src="/static/js/jquery/jquery/2.1.4/jquery.min.js"></script>
    <script src="/static/mobile/default/js/page.js?v=1"></script>
    <title><?php echo htmlentities($title); ?>  - <?php echo htmlentities($setting['shop_title']); ?></title>
    
<link rel="stylesheet" href="/static/mobile/default/css/fenlei.css" />

</head>
<body >
  <div class="page fenlei">
  <?php if(empty($not_top_nav) || (($not_top_nav instanceof \think\Collection || $not_top_nav instanceof \think\Paginator ) && $not_top_nav->isEmpty())): ?>
  		<div class="page-hd">
            <div class="header base-header bor-1px-b">
                <div class="header-left">
                    <a href="javascript:history.go(-1)" class="left-arrow"></a>
                </div>
                <div class="header-title"><?php echo htmlentities($title); ?></div>
                <div class="header-right">
                    <a href=""></a>
                </div>
            </div>
        </div>
   <?php endif; ?>     
   

	<div class="page-bd">
        <!-- 页面内容-->
        <div class="top bor_b">
        <a href="<?php echo url('index/search'); ?>">
            <div class="inputBox"><img src="/static/mobile/default/images/selech01.png" alt=""><form action="<?php echo url('goods/index'); ?>"><input class="fs30 color_3" type="text" placeholder="请输入关键词"></form></div></a>
           <span class="fs30 color_3" onclick="javascript:history.go(-1);">取消</span>
        </div>
        <div class="left bor_R">
          <?php if(!(empty($allSort['best']) || (($allSort['best'] instanceof \think\Collection || $allSort['best'] instanceof \think\Paginator ) && $allSort['best']->isEmpty()))): ?>	
          	<div class="box fs28 color_3 boxActive">推荐</div>
          <?php endif; if(is_array($allSort['rows']) || $allSort['rows'] instanceof \think\Collection || $allSort['rows'] instanceof \think\Paginator): $i = 0; $__LIST__ = $allSort['rows'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$class): $mod = ($i % 2 );++$i;?>
           		<div class="box fs28 color_3 <?php if(empty($allSort['best']) && $i == 1): ?>boxActive<?php endif; ?>"><?php echo !empty($class['m_name']) ? htmlentities($class['m_name']) : htmlentities($class['name']); ?></div>
           <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <div class="right">
         <?php if(!(empty($allSort['best']) || (($allSort['best'] instanceof \think\Collection || $allSort['best'] instanceof \think\Paginator ) && $allSort['best']->isEmpty()))): ?>	
          	<div class="rightBox" style="display:block">
            <?php if(is_array($allSort['best']) || $allSort['best'] instanceof \think\Collection || $allSort['best'] instanceof \think\Paginator): $i = 0; $__LIST__ = $allSort['best'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$best): $mod = ($i % 2 );++$i;?>
              <div class="block">
                  <div class="title"><i></i><span class="fs28 fw_b color_3"><?php echo htmlentities($best['m_name']); ?></span><i></i></div>
                  <div class="list">
                  	<?php if(is_array($best['children']) || $best['children'] instanceof \think\Collection || $best['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $best['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cid): $mod = ($i % 2 );++$i;?>
                      <a href='<?php echo url("goods/index",['cid'=>$cid]); ?>'><img src="<?php echo htmlentities($classList[$cid]['pic']); ?>" alt=""><span class="fs26 color_6">
                      <?php echo !empty($classList[$cid]['m_name']) ? htmlentities($classList[$cid]['m_name']) : htmlentities($classList[$cid]['name']); ?>
                      </span></a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                  </div>
              </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
          </div>
          <?php endif; if(is_array($allSort['rows']) || $allSort['rows'] instanceof \think\Collection || $allSort['rows'] instanceof \think\Paginator): $i = 0; $__LIST__ = $allSort['rows'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$class): $mod = ($i % 2 );++$i;?>
              <div class="rightBox" style="<?php if(empty($allSort['best']) && $i == 1): ?>display:block<?php endif; ?>">
              	 <?php if(is_array($class['children']) || $class['children'] instanceof \think\Collection || $class['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $class['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cids): $mod = ($i % 2 );++$i;?>
                  <div class="block">
                      <div class="title"><i></i><span class="fs28 fw_b color_3"><?php echo !empty($classList[$key]['m_name']) ? htmlentities($classList[$key]['m_name']) : htmlentities($classList[$key]['name']); ?></span><i></i></div>
                      <div class="list">
                      	<?php if(is_array($cids) || $cids instanceof \think\Collection || $cids instanceof \think\Paginator): $i = 0; $__LIST__ = $cids;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cid): $mod = ($i % 2 );++$i;?>
                        <a href='<?php echo url("goods/index",['cid'=>$cid]); ?>'><img src="<?php echo htmlentities($classList[$cid]['pic']); ?>" alt=""><span class="fs26 color_6"><?php echo !empty($classList[$cid]['m_name']) ? htmlentities($classList[$cid]['m_name']) : htmlentities($classList[$cid]['name']); ?></span></a>
                       <?php endforeach; endif; else: echo "" ;endif; ?>
                      </div>
                  </div>
                 <?php endforeach; endif; else: echo "" ;endif; ?> 
              </div>
         <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
      </div>
<div class="bottom-tabbar-wrapper">
  <div class="bottom-tabbar">
    <a href="/" class="bottom-tabbar__item <?php echo $routeUri=='index/index' ? 'active' : ''; ?>">
        <span class="icon">
            <img src="/static/mobile/default/images/bottom_icon01.png"/>
            <img class="lhimg" src="/static/mobile/default/images/bottom_icon01_lh.png"/>
        </span>
      <p class="label">首页</p>
    </a>
    <a href="<?php echo url('shop/index/allSort'); ?>" class="bottom-tabbar__item <?php echo $routeUri=='index/allsort' ? 'active' : ''; ?>">
        <span class="icon">
            <img src="/static/mobile/default/images/bottom_icon02.png"/>
            <img class="lhimg" src="/static/mobile/default/images/bottom_icon02_lh.png"/>
        </span>
      <p class="label">分类</p>
    </a>
    <a href="<?php echo url('member/Center/myCode'); ?>" class="bottom-tabbar__item ">
        <span class="icon">
            <img src="/static/mobile/default/images/bottom_icon03.png"/>
            <img class="lhimg" src="/static/mobile/default/images/bottom_icon03_lh.png"/>
        </span>
      <p class="label">二维码</p>
    </a>
    <a href="<?php echo url('shop/flow/cart'); ?>" class="bottom-tabbar__item <?php echo $routeUri=='flow/cart' ? 'active' : ''; ?>">
        <span class="icon">
            <img src="/static/mobile/default/images/bottom_icon04.png"/>
            <img class="lhimg" src="/static/mobile/default/images/bottom_icon04_lh.png"/>
        </span>
      <p class="label">购物车</p>
    </a>
    <a href="<?php echo url('member/Center/index'); ?>" class="bottom-tabbar__item  <?php echo $isUserIndex==1 ? 'active' : ''; ?>">
      <span class="icon">
          <img src="/static/mobile/default/images/bottom_icon05.png"/>
          <img class="lhimg" src="/static/mobile/default/images/bottom_icon05_lh.png"/>
      </span>
    <p class="label">我的</p>
  </a>
  </div>
</div>

  </div>

<script src="/static/js/art-template.js"></script>
<script src="/static/mobile/default/js/lib/fastclick.js"></script>
<script src="/static/js/jquery/lazyload/jquery.lazyload.js"></script>
<script>
    $(function() {
        FastClick.attach(document.body);
		$("img.lazy").lazyload({effect:"fadeIn",event:"scroll",container:$(".page-bd")});		
    });
</script>


<script>
  $(function(){
        $('.top span').on('click',function(){
          $('.inputBox input').val('');
          $('.inputBox input').focus();
        });
        $('.box').on('click',function(){
          let index=$(this).index();
          $(this).addClass('boxActive');
          $(this).siblings().removeClass('boxActive')
          $('.rightBox').eq(index).show();
          $('.rightBox').eq(index).siblings().hide();
        })
 })
</script>

  <div class="alertBox">
      <div class="alertBG"></div>
      <div class="alert">
          <div class="text fs30 color_3 bor_b">

          </div>
          <!-- 单按钮 -->
          <div class="button fs32 fw_b color_r">
              知道了
          </div>
          <!-- 双按钮 -->
          <div class="buttonBox fs32 fw_b">
              <span class="color_9 bor_r cancel">取消</span>
              <span class="color_r confirm">确定</span>
          </div>
      </div>
  </div>
</body>
</html>