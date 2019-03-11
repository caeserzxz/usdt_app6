<?php /*a:3:{s:41:"../template/default/shop\index\index.html";i:1552272354;s:37:"../template/default/layouts\base.html";i:1551315553;s:39:"../template/default/layouts\bottom.html";i:1551092090;}*/ ?>
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
    
<link rel="stylesheet" href="/static/mobile/default/js/Swiper-4.0.7/swiper.min.css" />
<link rel="stylesheet" href="/static/mobile/default/css/index.css" />

</head>
<body >
  <div class="page ">
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
        <div class="swiper-container swiper1" id="swiper01" style="background:#CCC;">
          <div class="swiper-wrapper">
          	<?php if(is_array($slideList) || $slideList instanceof \think\Collection || $slideList instanceof \think\Paginator): $i = 0; $__LIST__ = $slideList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$slide): $mod = ($i % 2 );++$i;?>
            	<a href="<?php echo htmlentities($slide['url']); ?>" class="swiper-slide"><img  data-src="<?php echo htmlentities($slide['imgurl']); ?>" class="swiper-lazy"/><div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div></a>
            <?php endforeach; endif; else: echo "" ;endif; ?>
          </div>
          <div class="swiper-pagination pagination1"></div>
          <a href="<?php echo url('index/search'); ?>" class="selech"><img src="/static/mobile/default/images/index_01.png" class="lazy"  alt="" /><span class="fs32 color_w"><?php echo htmlentities($setting['shop_index_search_text']); ?></span></a>
        </div>
        <!-- 功能入口 -->
        <div class="girdBox">
          <div class="gird">
            <div class="row">
             <?php if(is_array($navMenuList) || $navMenuList instanceof \think\Collection || $navMenuList instanceof \think\Paginator): $i = 0; $__LIST__ = $navMenuList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
              <a href="<?php echo htmlentities($nav['url']); ?>" class="box">
                <div><img src="<?php echo htmlentities($nav['imgurl']); ?>" alt="" /></div>
                <span class="fs26 color_3"><?php echo htmlentities($nav['title']); ?></span>
              </a>
              <?php if($i==4): ?>
              	</div><div class="row">
              <?php endif; endforeach; endif; else: echo "" ;endif; ?>
            </div>
          </div>
        </div>
        <!-- 秒杀 -->
        <div class="seckill" style="display:none;">
            <div class="title">
              <div class="left"><p class="fs36 color_3 fw_b">今日秒杀</p><div class="time"><div>00</div><span>:</span><div>30</div><span>:</span><div>10</div></div></div>
              <a href="#" class="right"><span class="fs26 color_9">更多</span><img src="/static/mobile/default/images/rightIcon.png" alt=""></a>    
            </div>
            <div class="seckilllist">
                <a href="商品.html" class="box">
                  <img src="/static/mobile/default/images/seckillimg.png" alt="">
                  <span class="fs28 color_3">补水保湿套装</span>
                  <div class="color_r fs24 num"><p class="fw_b fm_p">￥</p><em class="fs36">199</em><p>.00</p></div>
                </a>
                <div class="box">
                    <img src="/static/mobile/default/images/seckillimg.png" alt="">
                    <span class="fs28 color_3">补水保湿套装补湿套装</span>
                    <div class="color_r fs24 num"><p class="fw_b fm_p">￥</p><em class="fs36">199</em><p>.00</p></div>
                  </div>
                  <div class="box">
                      <img src="/static/mobile/default/images/seckillimg.png" alt="">
                      <span class="fs28 color_3">补水保湿套装</span>
                      <div class="color_r fs24 num"><p class="fw_b fm_p">￥</p><em class="fs36">199</em><p>.00</p></div>
                    </div>
                    <div class="box">
                        <img src="/static/mobile/default/images/seckillimg.png" alt="">
                        <span class="fs28 color_3">补水保湿套装</span>
                        <div class="color_r fs24 num"><p class="fw_b fm_p">￥</p><em class="fs36">199</em><p>.00</p></div>
                      </div>
                      <div class="box">
                          <img src="/static/mobile/default/images/seckillimg.png" alt="">
                          <span class="fs28 color_3">补水保湿套装</span>
                          <div class="color_r fs24 num"><p class="fw_b fm_p">￥</p><em class="fs36">199</em><p>.00</p></div>
                        </div>
            </div>
        </div>
        <!-- 拼团 -->
        <div class="group" style="display:none;">
            <div class="swiper-container swiper2" id="swiper02">
                <div class="swiper-wrapper">
                  <a href="#" class="swiper-slide"><img src="/static/mobile/default/images/group01.png" alt=""/>
                    <div class="info">
                      <div class="fs34 fw_b color_3 name">KENZO/高田贤一春夏新款</div>
                      <div class="tips"><span class="fs26 color_9">韩系时髦搭配指南</span><div class="color_r fs22 num"><p class="fw_b fm_p">￥</p><em>199</em><p>.00</p></div></div>
                    </div>
                  </a>
                  <a href="#" class="swiper-slide"><img src="/static/mobile/default/images/group01.png" alt=""/>
                    <div class="info">
                        <div class="fs34 fw_b color_3 name">KENZO/高田贤三春夏新款</div>
                        <div class="tips"><span class="fs26 color_9">韩系时髦搭配指南</span><div class="color_r fs22 num"><p class="fw_b fm_p">￥</p><em>199</em><p>.00</p></div></div>
                      </div>
                  </a>
                  <a href="#" class="swiper-slide"><img src="/static/mobile/default/images/group01.png" alt=""/>
                    <div class="info">
                        <div class="fs34 fw_b color_3 name">KENZO/高田贤五春夏新款</div>
                        <div class="tips"><span class="fs26 color_9">韩系时髦搭配指南</span><div class="color_r fs22 num"><p class="fw_b fm_p">￥</p><em>199</em><p>.00</p></div></div>
                      </div>
                  </a>
                  <a href="#" class="swiper-slide"><img src="/static/mobile/default/images/group01.png" alt=""/>
                    <div class="info">
                        <div class="fs34 fw_b color_3 name">KENZO/高田贤久春夏新款</div>
                        <div class="tips"><span class="fs26 color_9">韩系时髦搭配指南</span><div class="color_r fs22 num"><p class="fw_b fm_p">￥</p><em>199</em><p>.00</p></div></div>
                      </div>
                  </a>
                </div>
                <div class="swiper-pagination pagination2"></div>                
              </div>
              <a href="#" class="fs26 color_3 groupMore">查看更多拼团</a>
        </div>
        <!-- 分类 -->
        <?php if(is_array($classGoods) || $classGoods instanceof \think\Collection || $classGoods instanceof \think\Paginator): $i = 0; $__LIST__ = $classGoods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cg): $mod = ($i % 2 );++$i;?>
        <div class="trademark">
           <img  src="/static/mobile/default/images/loading.svg" data-original="<?php echo htmlentities($cg['cover']); ?>" alt="" class="lazy trademarkBG">
           <div class="googslist">
           <?php if(is_array($cg['gooodsList']) || $cg['gooodsList'] instanceof \think\Collection || $cg['gooodsList'] instanceof \think\Paginator): $i = 0; $__LIST__ = $cg['gooodsList'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$goods): $mod = ($i % 2 );++$i;?>
             <a href="<?php echo url('goods/info',['id'=>$goods['goods_id']]); ?>">
               <img  src="/static/mobile/default/images/loading.svg" data-original="<?php echo htmlentities($goods['goods_thumb']); ?>" class="lazy" alt="">
               <span class="fs26 color_3"><?php echo htmlentities($goods['goods_name']); ?></span>
               <div class="color_r fs20 num"><p class="fw_b fm_p">￥</p><em class="fs30"><?php echo htmlentities($goods['exp_price'][0]); ?></em><p>.<?php echo htmlentities($goods['exp_price'][1]); ?></p></div>
             </a>
            <?php endforeach; endif; else: echo "" ;endif; ?>
           </div>
           <a href="<?php echo url('goods/index',['cid'=>$cg['id']]); ?>" class="fs26 color_3 trademarkMore">查看更多</a>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        <!-- 猜你喜欢 -->
        <div class="youLike">
            <div class="title">
                <div class="left fs36 color_3 fw_b">猜你喜欢</div>
                <a href="<?php echo url('goods/index',['is_best'=>1]); ?>" class="right"><span class="fs26 color_9">更多</span><img src="/static/mobile/default/images/rightIcon.png" alt=""></a>    
            </div>
            <div class="list">
                <div class="box">
                	<?php if(is_array($bestGoods) || $bestGoods instanceof \think\Collection || $bestGoods instanceof \think\Paginator): $i = 0; $__LIST__ = $bestGoods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$goods): $mod = ($i % 2 );++$i;?>
                    <a href="<?php echo url('goods/info',['id'=>$goods['goods_id']]); ?>">
                        <img  src="/static/mobile/default/images/loading.svg" data-original="<?php echo htmlentities($goods['goods_thumb']); ?>" class="lazy" alt="">
                        <span class="fs30 color_3"><?php echo htmlentities($goods['goods_name']); ?></span>
                        <div class="color_r fs24 num"><p class="fw_b fm_p">￥</p><em class="fs36"><?php echo htmlentities($goods['exp_price'][0]); ?></em><p>.<?php echo htmlentities($goods['exp_price'][1]); ?></p></div>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
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

<script src="/static/mobile/default/js/Swiper-4.0.7/swiper.min.js"></script>
<script>
      $(function() {
        swiper1 = createSwiper(1);
        swiper2 = createSwiper(2);        
      });
      function createSwiper(index) {
        var swiper = new Swiper(".swiper" + index, {
          pagination: {
            el: ".pagination" + index
          },
          paginationClickable: true,
          observer: true,
          observeParents: true,
          loop: true,
		  lazy: {loadPrevNext: true},
          autoplay: {
            delay: 3000,
            stopOnLastSlide: false,
            disableOnInteraction: false
          },
          onSlideChangeEnd: function(swiper) {
            swiper.update(); //swiper更新
          }
        });
        return swiper;
      }
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