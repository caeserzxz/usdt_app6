<?php /*a:3:{s:40:"../template/default/shop\flow\index.html";i:1551402899;s:37:"../template/default/layouts\base.html";i:1551315553;s:39:"../template/default/layouts\bottom.html";i:1551092090;}*/ ?>
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
    
<link rel="stylesheet" href="/static/mobile/default/css/shopCart.css" />

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
<div class="top"><span class="fs32 color_3">当前购物车共<em>0</em>件商品</span><p class="fs28 color_r" data-type='0'>编辑</p></div>
    <!-- 空购物车 -->
    <div class="emptyBox">
        <img src="/static/mobile/default/images/emptyCart.png" alt="">
        <div><span class="fs30 color_9">购物车空空如也~</span><a href="/" class="fs30 color_r">去逛逛</a></div>
    </div>
    <!-- 购物车 -->
    <div class="goodslist">
        <ul>

        </ul>
    </div>
    <!-- 失效商品 -->
    <div class="loseGoods" style="display: none;">
        <div class="title"><span class="fs32 fw_b color_3">失效商品</span><p class="fs28 color_r clearInvalid">清空</p></div>

    </div>

</div>
<div class="paying">
    <div class="totalBox ">
        <div class="left">
            <label for="checkall_a">
                <div class="iconBox">
                    <input type="checkbox" class="check checkall" name="checkbox1" id="checkall_a">
                    <i class="icon_checked"></i>
                </div>
                <span class="fs32 fw_b color-3">全选</span>
            </label>
            <p class="fs28 color_9">小计</p>
            <div class="color_3 fs30 num money"><p class="fw_b fm_p">￥</p><em class="fs52 totel_price_1">00</em><p class="totel_price_2">.00</p></div>
        </div>
        <a href="<?php echo url('checkOut'); ?>" class="right BGcolor_r fs30 color_w"><span class="fw_b">结算</span>(<em id="buyNum">0</em>)</a>
    </div>
</div>
<!-- 编辑 -->
<div class="edit">
    <div class="totalBox">
        <div class="left">
            <label for="checkall_b">
                <div class="iconBox">
                    <input type="checkbox"  class="check checkall" name="checkbox1" id="checkall_b">
                    <i class="icon_checked"></i>
                </div>
                <span class="fs32 fw_b color-3">全选</span>
            </label>
        </div>
        <div class="button fs30 color_w fw_b"><span class="BGcolor_3 delSelGoods">删除</span><span class="BGcolor_r collectGoods">收藏</span></div>
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
<script type="text/html" id="goodsLi">
    {{each goodsList as item index}}
    <li>
        <div class="checkbox">
            <label for="s{{item.rec_id}}">
                <div class="iconBox">
                    <input type="checkbox" class="check" id="s{{item.rec_id}}" name="rec_ids[]" value="{{item.rec_id}}" data-rec_id="{{item.rec_id}}" {{item.is_select?'checked':''}}>
                    <i class="icon_checked"></i>
                </div>
            </label>
            <div class="block">
                <a href='<?php echo _url("shop/goods/info",["id"=>"[[item.goods_id]]"]); ?>' >
                    <img src="{{item.pic}}" alt="" class="goodsimg">
                </a>
                <div class="info">
                    <p class="fs28 color_3">{{item.goods_name}}</p>
                    <div class="sign fs28 color_9">{{item.sku_name}}</div>
                    <div class="Money">
                        <div class="left">
                            <div class="color_3 fs24 num"><p class="fw_b fm_p">￥</p><em class="fs36">{{item.exp_price[0]}}</em><i>.{{item.exp_price[1]}}</i></div>
                            <span class="fs24 color_9">￥{{item.market_price}}</span>
                        </div>
                        <div class="number">
                            <img src="/static/mobile/default/images/goodsIcon05.png" onClick="editNum(this,'{{item.rec_id}}','down');" class="minus">
                            <input class="fs30 color_3" type="text" readonly value="{{item.goods_number}}">
                            <img src="/static/mobile/default/images/goodsIcon06.png" onClick="editNum(this,'{{item.rec_id}}','up');" class="add"></div>

                    </div>
                </div>
            </div>
                <div class="swiped BGcolor_3">
                    <div class="delect" data-rec_id="{{item.rec_id}}"><img src="/static/mobile/default/images/delectIcon.png" alt=""></div>
                    <div class="like collectGoods" data-goods_id="{{item.goods_id}}"><img src="/static/mobile/default/images/goodsIcon03{{item.is_collect==1?'_lh':''}}.png" alt="" data-type="{{item.is_collect}}"></div>
                </div>
            </div>
        </a>
    </li>
    {{/each}}
</script>

<script type="text/html" id="loseGoodsLi">
    {{each invalidList as item index}}
    <a href='<?php echo _url("shop/goods/info",["id"=>"[[item.goods_id]]"]); ?>' >
        <div class="box">
            <img src="{{item.pic}}" alt="">
            <div class="info">
                <p class="fs28 color_9">{{item.goods_name}}</p>
                <span class="fs30 color_3">失效</span>
            </div>
        </div>
    </a>
    {{/each}}
</script>

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
    //购物车统一请求
    function evalCart(action,arr){
        jq_ajax('<?php echo url("shop/api.flow/'+action+'"); ?>',arr,function(res){
            if (res.code==0){
                if (res.msg != '') _alert(res.msg);
                return false;
            }
            $('.top').find('em').html(res.cartInfo.allGoodsNum);
            $('.totel_price_1').html(res.cartInfo.exp_total[0]);
            $('.totel_price_2').html('.'+res.cartInfo.exp_total[1]);
            $('#buyNum').html(res.cartInfo.buyGoodsNum);
            if (res.cartInfo.goodsList){
                $('.emptyBox').hide();
                $('.goodslist ul').html(template('goodsLi',res.cartInfo));
            }else{
                $('.goodslist ul').html('');
                $('.emptyBox').show();
            }

            if (res.cartInfo.isAllSel == 1){
                $('.checkall').prop("checked",true);
            }else{
                $('.checkall').prop('checked',false);
            }
            $('.loseGoods').find('.box').remove();
            if (res.cartInfo.invalidList){
                $('.loseGoods').show();
                $('.loseGoods').append(template('loseGoodsLi',res.cartInfo));
            }else{
                $('.loseGoods').hide();
            }
            container('.goodslist li');
            return true;
        })
    }
    //修改购物车订购数量
    function editNum(obj,rec_id,type) {
        var num = $(obj).parent().find('input').val();
        if (type == 'up'){
            num++;
        }else {
            num--;
        }
        if (num < 1) return false;
        return evalCart('editNum','rec_id='+rec_id+'&num='+num);
    }
    //删除购物车商品
    $(document).on('click','.delect',function () {
        var rec_id = $(this).data('rec_id');
        return evalCart('delGoods','rec_id='+rec_id);
    })
    //清空购物车失效商品
    $('.clearInvalid').on('click',function () {
        return evalCart('clearInvalid');
    })
    //选择商品
    $('.goodslist').on('click','.check',function () {
        var is_select = 0;
        if ($(this).is(':checked')){
            is_select = 1;
        }
        return evalCart('setSel','rec_id='+$(this).data('rec_id')+'&is_select='+is_select);
    })
    //全选或全不选商品
    $('.checkall').on('click',function () {
        var is_select = 0;
        if ($(this).is(':checked') == true){
            is_select = 1;
        }
        return evalCart('setSel','rec_id=all&is_select='+is_select);
    })
    //删除选择的商品
    $('.delSelGoods').on('click',function () {
        return evalCart('delSelGoods','');
    })
   

    $(function(){
        evalCart('getList');//加载购物车
        $('.top p').on('click',function(){
            if($(this).attr('data-type')==0){
                $(this).text('完成')
                $('.paying').hide();
                $('.edit').show();
                $(this).attr('data-type','1')
            }else{
                $(this).text('编辑')
                $('.edit').hide();
                $('.paying').show();
                $(this).attr('data-type','0')
            }
        })
        $(document).on('click','.like',function(){
            let imgObj = $(this).find('img');
			var status = imgObj.data('type');
			var goods_id = $(this).data('goods_id');
			jq_ajax('<?php echo url("shop/api.goods/collect"); ?>', 'goods_id='+goods_id, function (res) {
				if (res.code == 0) {
					_alert(res.msg);
					return false;
				}
				if (status == 0) {
					imgObj.attr('src', '/static/mobile/default/images/goodsIcon03_lh.png')
					imgObj.data('type', '1')
				} else {
					imgObj.attr('src', '/static/mobile/default/images/goodsIcon03.png')
					imgObj.data('type', '0')
				}
			});

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