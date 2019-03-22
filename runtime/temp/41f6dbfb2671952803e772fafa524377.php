<?php /*a:3:{s:40:"../template/default/shop\goods\info.html";i:1552631572;s:37:"../template/default/layouts\base.html";i:1552631572;s:39:"../template/default/shop\goods\sku.html";i:1552460093;}*/ ?>
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
    
<link rel="stylesheet" href="/static/mobile/default/js/Swiper-4.0.7/swiper.min.css"/>
<link rel="stylesheet" href="/static/mobile/default/css/goods.css"/>

      <script type="text/javascript">
          (function (w, d, s, i, v, j, b) {
              w[i] = w[i] || function () {
                      (w[i].v = w[i].v || []).push(arguments)
                  };
              j = d.createElement(s),
                  b = d.getElementsByTagName(s)[0];
              j.async = true;
              j.charset = "UTF-8";
              j.src = "https://www.v5kf.com/162752/v5kf.js";
              b.parentNode.insertBefore(j, b);
          })(window, document, "script", "V5CHAT");
          <?php if(!(empty($userInfo) || (($userInfo instanceof \think\Collection || $userInfo instanceof \think\Paginator ) && $userInfo->isEmpty()))): ?>
          V5CHAT('openId', 'user_<?php echo htmlentities($userInfo['user_id']); ?>');
          V5CHAT('nickname', '<?php echo htmlentities($userInfo['nick_name']); ?>');
          V5CHAT('metadata', [
              {key: '客户等级', val: '<?php echo $userInfo['role_id']>0 ? htmlentities($userInfo['role']['role_name']) : '粉丝'; ?>'},
              {key: '客户ID', val: '<?php echo htmlentities($userInfo['user_id']); ?>'},
              {key: '客户ID', val: '<?php echo htmlentities($userInfo['mobile']); ?>'}
          ]);
          <?php endif; ?>
      </script>
</head>
<body >
  <div class="page Goods">
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
   
<div class="page-hd">
          <div class="header">
              <div class="header-left back">
                  <a href="javascript:history.go(-1)" class="left-arrow"></a>
              </div>
              <div class="header-title">
                <span class="fs32 color_9 active">商品</span>
                <span class="fs32 color_9">评价</span>
                <span class="fs32 color_9">详情</span>  
              </div>
          </div>
      </div>

<div class="page-bd tabBox" id="goods">
    <!-- 页面内容-->
    <!-- 商品轮播 -->
    <div class="swiperBox">
        <div class="swiper-container swiper1" id="swiper01">
            <div class="swiper-wrapper">
                <?php if(is_array($imgsList) || $imgsList instanceof \think\Collection || $imgsList instanceof \think\Paginator): $i = 0; $__LIST__ = $imgsList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$img): $mod = ($i % 2 );++$i;?>
                <a href="javascript:;" class="swiper-slide"><img src="<?php echo htmlentities($img['goods_img']); ?>" alt=""/></a>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
            <div class="swiper-pagination pagination1"></div>

        </div>
    </div>
    <!-- 商品信息 -->
    <div class="moneyBox">
        <div class="top">
            <div class="color_3 fs30 num money"><p class="fw_b fm_p">￥</p>
                <?php if($goods['is_spec'] == '0'): ?>
                <em class="fs52"><?php echo htmlentities($goods['exp_price'][0]); ?></em>
                <p>.<?php echo htmlentities($goods['exp_price'][1]); ?></p>
                <?php else: ?>
                <em class="fs52"><?php echo htmlentities($goods['exp_min_price'][0]); ?></em>
                <p>.<?php echo htmlentities($goods['exp_min_price'][1]); ?></p>
                <em class="fs52">~</em>
                <em class="fs52"><?php echo htmlentities($goods['exp_max_price'][0]); ?></em>
                <p>.<?php echo htmlentities($goods['exp_max_price'][1]); ?></p>
                <?php endif; ?>
            </div>
            <?php if($goods['is_promote'] == '1'): ?><span class="fs22 color_w BGcolor_r">活动价</span><?php endif; ?>
        </div>
        <div class="original fs24 color_9">￥<?php echo htmlentities($goods['market_price']); ?></div>
        <div class="name fw_b fs34 color_3"><?php echo htmlentities($goods['goods_name']); ?></div>
        <div class="tips fs28 color_9"><span>热销<?php echo htmlentities($goods['sale_count']); ?></span>
            <p style="display:none ">运费10元</p></div>
    </div>
    <?php if($goods['is_spec'] == '1'): ?>
    <div class="cell">
        <span class="fs32 fw_b color_3">选择规格</span>
        <div class="size"><p class="fs28 color_6 selSkuName">请选择规格</p><img
                src="/static/mobile/default/images/rightIcon.png" alt="" class="threeRight"></div>
    </div>
    <?php endif; ?>
    <!-- 店铺 -->
    <div class="shopName bor_b" style="display: none">
        <div><img src="/static/mobile/default/images/shoplogo.png" alt=""><span class="fs32 fw_b color_3">xxxx</span>
        </div>
        <img src="/static/mobile/default/images/rightIcon.png" alt="" class="threeRight">
    </div>
   <!-- 评论 -->
        <div class="comment">
           <a class="top" href="<?php echo url('comment',['goods_id'=>$goods['goods_id']]); ?>">
              <div class="fs32 color_3"><span class="fw_b">用户评价</span><p class="color_9 commentNum">(0)</p></div>
              <div><span class="fs26 color_9">更多</span><img src="/static/mobile/default/images/rightIcon.png" alt="" class="threeRight"></div>
           </a>
           <div class="commentBox">
              <div class="user">
                <img src="" alt="">
                <div class="info">
                  <p class="fs28 color_3 uname"></p>
                  <span class="fs24 color_9 time"></span>
                </div>
              </div>
              <div class="comtext fs28 color_3 line_twe content">
                  
              </div>
              <div class="comimg">
              </div>              
           </div>
        </div>
        <!-- 图文 -->
        <div class="imgText">
          <div class="title fw_b fs32 color_3">商品详情</div>
          <?php echo $goods['m_goods_desc']; ?>
        </div>
    <!-- 浮按钮 -->
    <div class="button">
        <a href="<?php echo url('myCode',['goods_id'=>$goods['goods_id']]); ?>" class="share BGcolor_3"><img src="/static/mobile/default/images/goodsIcon01.png" alt=""></a>
        <i></i>
        <div class="attention BGcolor_3"><img
                src="/static/mobile/default/images/goodsIcon03<?php echo $isCollect==1 ? '_lh' : ''; ?>.png" data-no="<?php echo htmlentities($isCollect); ?>"
                alt=""></div>
    </div>
</div>

<!-- 弹出规格选择 -->
<div class="model" >
    <div class="modelContent">
        <div class="closeBox"><img src="/static/mobile/default/images/close_icon.png" alt=""></div>
        <div class="top">
            <div class="left"><img id="skuImg" src="<?php echo htmlentities($goods['goods_thumb']); ?>" alt=""></div>
            <div class="right">
                <div class="money">
                    <div class="color_3 fs30 num"><p class="fw_b fm_p">￥</p>
                        <?php if($goods['is_spec'] == '0'): ?>
                            <em class="fs52"><?php echo htmlentities($goods['exp_price'][0]); ?></em><p>.<?php echo htmlentities($goods['exp_price'][1]); ?></p>
                        <?php else: ?>
                            <div class="skuPrice">
                                <em class="fs52"></em><p></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if($goods['is_promote'] == '1'): ?><span class="fs22 color_w BGcolor_r">活动价</span><?php endif; ?>
                </div>
                <div class="fs24 color_9 moneyPrimary">￥<?php echo htmlentities($goods['market_price']); ?></div>
                <?php if(!(empty($goods['sub_goods']) || (($goods['sub_goods'] instanceof \think\Collection || $goods['sub_goods'] instanceof \think\Paginator ) && $goods['sub_goods']->isEmpty()))): ?><p class="fs28 fw_b color_3 selSkuName">请选择规格</p><?php endif; ?>
            </div>
        </div>

        <div class="cantre sku_list">
            <?php if(is_array($goods['lstSKUArr']) || $goods['lstSKUArr'] instanceof \think\Collection || $goods['lstSKUArr'] instanceof \think\Paginator): $i = 0; $__LIST__ = $goods['lstSKUArr'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sku): $mod = ($i % 2 );++$i;?>
            <div class="block <?php echo $sku['is_show']==0 ? 'hide' : ''; ?>">
                <p class="fs32 fw_b color_3"><?php echo htmlentities($sku['name']); ?></p>
                <div class="list">
                    <?php if(is_array($sku['lstVal']) || $sku['lstVal'] instanceof \think\Collection || $sku['lstVal'] instanceof \think\Paginator): $i = 0; $__LIST__ = $sku['lstVal'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lst): $mod = ($i % 2 );++$i;?>
                        <span class="fs28 color_3 <?php echo $lst['isdef']==1 ? 'tag_active' : ''; ?> <?php echo $lst['issel']==0 ? 'disabled' : ''; ?>" id="sku_<?php echo htmlentities($lst['id']); ?>" data-skukey="<?php echo htmlentities($lst['id']); ?>"><?php echo htmlentities($lst['val']); ?></span>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>

            <div class="block">
                <div class="numberBox"><p class="fs32 fw_b color_3">数量</p>
                    <div class="number">
                        <img src="/static/mobile/default/images/goodsIcon05.png" alt="" class="minus onj">
                        <input class="fs30 color_3 pr_selnum" type="text" value="1" id="buynumber" name="buynumber" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')">
                        <img src="/static/mobile/default/images/goodsIcon06.png" alt="" class="add onz"></div>
                </div>
            </div>
        </div>
        <div class="buttBox">
            <span class="fs28 fw_b color_w BGcolor_3 buyBtn" data-goods_id="<?php echo htmlentities($goods['goods_id']); ?>" data-type="oncart">加入购物车</span>
            <span class="fs28 fw_b color_w BGcolor_r buyBtn" data-goods_id="<?php echo htmlentities($goods['goods_id']); ?>" data-type="onbuy">立即购买</span>
        </div>
    </div>
</div>
<script type="text/javascript">
    var sub_goods = <?php echo json_encode($goods['sub_goods']); ?>;
    var skuLink = <?php echo json_encode($goods['skuLink']); ?>;
    var skuCstom = <?php echo json_encode($goods['skuCstom']); ?>;
    var skuImgs = <?php echo json_encode($skuImgs); ?>;
    //选择商品数量
    $(".onj").click(function(){
        act=parseInt($(".pr_selnum").val());
        if(act<=1){
            $(".pr_selnum").val(1);
        }else{
            $(".pr_selnum").val(act-1);
        }
    })
    $(".onz").click(function(){
        act=parseInt($(".pr_selnum").val());
        $(".pr_selnum").val(act+1);
    })
    $(".pr_selnum").blur(function(){
        if($(this).val()=="" || $(this).val()=="0"){$(this).val(1);}
        act=parseInt($(".pr_selnum").val());
    });
    function selsku(){
		if (sub_goods == null) return false;
        var sku = new Array();
        $('.sku_list .tag_active').each(function(){
            var skukey = $(this).data('skukey');
            sku.push(skukey);
            if (skuLink.indexOf(sku.join(":")) < 0){
                sku.pop();
                $(this).removeClass('selected').addClass('disabled');
                $(this).parent().find('.item').each(function(){
                    skukey = $(this).data('skukey')
                    sku.push(skukey);
                    if (skuLink.indexOf(sku.join(":")) < 0){
                        $(this).removeClass('selected').addClass('disabled');
                        sku.pop();
                        return true;
                    }
                    $(this).removeClass('disabled').addClass('selected');
                    return false;
                })
            }
            $(this).parent().find('.item').each(function(){
                sku.pop();
                sku.push($(this).data('skukey'));
                if (skuLink.indexOf(sku.join(":")) < 0){
                    $(this).addClass('disabled');
                }else{
                    $(this).removeClass('disabled');
                }
            })
            sku.pop();
            sku.push(skukey);
        });
       var _sku = sku.join(":");
        $('.buyBtn').attr('data-sku',_sku);
        var skuName = new Array();
        $.each(sku,function (k,v) {
            skuName.push(skuCstom[v]);
        })
        $('#skuImg').attr('src',skuImgs[_sku]);
        $('.selSkuName').html(skuName.join(" - "));
        $('.skuPrice').find('em').html(sub_goods[_sku].exp_price[0]);
        $('.skuPrice').find('p').html('.'+sub_goods[_sku].exp_price[1]);
        $('.moneyPrimary').html('￥'+sub_goods[_sku].market_price);

        //$('.kcnum').html(sub_goods[sku].goods_number);
    }

    $(function() {
        //开启规格选择弹窗
        $('.size').on('click', function () {
            $('.model').show();
            selsku();
        })
        //规格选择
        $('.sku_list span').on('click', function () {
            $(this).addClass('tag_active')
            $(this).siblings().removeClass('tag_active');
            selsku();
        })
        //关闭规格选择弹窗
        $('.closeBox').on('click', function () {
            $('.model').hide()
        })
    })
</script>

<!-- 底部 -->
<div class="page-ft">
    <div class="left">
        <a href="/"><img src="/static/mobile/default/images/bottom_icon01.png" alt=""><span
                class="fs22 color_6">首页</span></a>
        <div v5_href><img src="/static/mobile/default/images/goodsIcon02.png" alt=""><span class="fs22 color_6" >客服</span>
        </div>
        <a href="<?php echo url('flow/cart'); ?>"><em class="BGcolor_r fs22 color_w cartNum"><?php echo htmlentities(intval($cartInfo['num'])); ?></em><img
                src="/static/mobile/default/images/bottom_icon04.png" alt=""><span
                class="fs22 color_6">购物车</span></a>
    </div>
    <div class="right">
        <div class="fs28 color_w fw_b BGcolor_3 buyBtn" data-type="show">加入购物车</div>
        <div class="fs28 color_w fw_b BGcolor_r buyBtn" data-type="show">立即购买</div>
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
$(function () {
	
	swiper1 = createSwiper(1);
	// 关注
	$('.attention').on('click', function () {
		let imgObj = $(this).find('img');
		var status = imgObj.data('no');
		jq_ajax('<?php echo url("shop/api.goods/collect"); ?>', 'goods_id=<?php echo htmlentities($goods['goods_id']); ?>', function (res) {
			if (res.code == 0) {
				_alert(res.msg);
				return false;
			}
			if (status == 0) {
				imgObj.attr('src', '/static/mobile/default/images/goodsIcon03_lh.png')
				imgObj.data('no', '1')
			} else {
				imgObj.attr('src', '/static/mobile/default/images/goodsIcon03.png')
				imgObj.data('no', '0')
			}
		});
		
	})
	jq_ajax('<?php echo url("shop/api.comment/getListByGoods",["goods_id"=>$goods["goods_id"],"limit"=>1]); ?>','',function(res){
		if (res.code == 0){
			return false;	
		}
		
		$('.commentNum').html('('+res.data.total_count+')');
		if (res.data.total_count > 0){
			$('.commentBox .user img').attr('src',res.data.list[0].headimgurl?res.data.list[0].headimgurl:'/static/mobile/default/images/defheadimg.jpg');
			$('.commentBox .user .uname').html(res.data.list[0].user_name);
			$('.commentBox .user .time').html(res.data.list[0]._time);
			$('.commentBox  .content').html(res.data.list[0].content);
			$.each(res.data.list[0].imgs,function(i,v){
				$('.commentBox .comimg').append('<img src="'+v.thumbnail+'">');
			})
		}
	})

	// 滚动出现tab
	$('#goods').scroll(function(){
	  let scrolltop=$(this).scrollTop()
	  // console.log($('.comment').offset().top,scrolltop)
	  if(scrolltop>100){
		$('.header-title').css('display','flex');
		$('.header-left').removeClass('back')
		$('.page-hd').css('background-color','#fff')
		$('.header').addClass('bgShow')
		if($('.comment').offset().top<$('.page-hd').height()){
		  $('.header-title span').eq(1).addClass('active')
		  $('.header-title span').eq(1).siblings().removeClass('active')
		  if($('.imgText').offset().top<$('.page-hd').height()){
			$('.header-title span').eq(2).addClass('active')
			$('.header-title span').eq(2).siblings().removeClass('active')
		  }else{
			$('.header-title span').eq(1).addClass('active')
			$('.header-title span').eq(1).siblings().removeClass('active')
		  }
		}else{
		  $('.header-title span').eq(0).addClass('active')
		  $('.header-title span').eq(0).siblings().removeClass('active')
		}

	  }else{
		$('.header-title').css('display','none');
		$('.header-left').addClass('back')
		$('.page-hd').css('background-color','transparent')
		$('.header').removeClass('bgShow')
	  }

	})
	  //tab切换
	$('.header-title span').on('click',function(){
	  let index=$(this).index()
	  let topHeight=$('.page-hd').height()
	  let commentTop= $('.comment').offset().top+$('#goods').scrollTop()-topHeight+1
	  let imgTextTop= $('.imgText').offset().top+$('#goods').scrollTop()-topHeight+1
		if(index==0){
		  $("#goods").animate({scrollTop:0}, 300)
		}else if(index==1){
		  $("#goods").animate({scrollTop:commentTop+'px'}, 300)
		}else{
		  $("#goods").animate({scrollTop:imgTextTop+'px'}, 300)
		}
	})
	//点击购买按钮
	$('.buyBtn ').on('click', function () {
		var obj = $(this);
		if (obj.data('type') == 'show') {
			$('.model').show();
			selsku();
		} else {
			addToCart(obj);
		}
	})
})
//获取购物车
jq_ajax("<?php echo url('shop/api.flow/getCartInfo'); ?>", '',function (res) {
	if (res.code == 0)  return false;
	$('.cartNum').html(res.cartInfo.num);
});
//添加到购物车
function addToCart(obj) {
	var arr = new Object;
	arr.goods_id = obj.data('goods_id');
	arr.specifications = obj.data('sku');
	arr.type = obj.data('type');
	arr.number = $('#buynumber').val();
	var res = jq_ajax("<?php echo url('shop/api.flow/addCart'); ?>", arr);
	if (res.code == 1) {
		if (arr.type == 'onbuy') {
			window.location = '<?php echo _url("flow/checkout",array("rec_id"=>"【res.rec_id】")); ?>';
			return false;
		}
		$('.cartNum').html(res.cartInfo.num);
	}
	if (res.msg) _alert(res.msg);
}

function createSwiper(index) {
	var swiper = new Swiper(".swiper" + index, {
		pagination: {
			el: ".pagination" + index
		},
		paginationClickable: true,
		observer: true,
		observeParents: true,
		loop: true,
		autoplay: {
			delay: 1500,
			stopOnLastSlide: false,
			disableOnInteraction: false
		},
		onSlideChangeEnd: function (swiper) {
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