<?php /*a:2:{s:41:"../template/default/shop\goods\index.html";i:1550132957;s:37:"../template/default/layouts\base.html";i:1551315553;}*/ ?>
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
    
<link rel="stylesheet" href="/static/mobile/default/css/goodslist.css" />

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
        <div class="scrollBox">
        <div class="top">
        	<a href="<?php echo url('index/search'); ?>">
            <div class="inputBox"><img src="/static/mobile/default/images/selech01.png" alt=""><form action="<?php echo url('goods/index'); ?>"><input class="fs30 color_3" type="text" name="keyword" id="keyword"  value="<?php echo htmlentities($keyword); ?>" placeholder="<?php echo htmlentities($setting['shop_index_search_text']); ?>"></form></div>
           </a>
           <span class="fs30 color_3" onclick="javascript:history.go(-1);">取消</span>
        </div>
        <div class="selectBox">
               <div class="color_3 fs30 rank rank_active" data-type="complex">综合</div>
               <div class="color_3 fs30 rank " data-type="sales"><span>销量</span><img src="/static/mobile/default/images/goodslist01.png" alt="" data-type="1"></div>
               <div class="color_3 fs30 bor_R rank" data-type="price"><span>价格</span><img src="/static/mobile/default/images/goodslist01.png" alt="" data-type="1"></div>
               <div class="color_3 fs30 select"><span>筛选</span><img src="/static/mobile/default/images/goodslist04.png" alt=""></div>
        </div>
        <div class="goodslist">

        </div>
        </div>
      </div>
      <!-- 弹出规格选择 -->
      <div class="model">
          <div class="modelcover"></div>
          <div class="modelContent">
            <div class="cantre">
                <div class="block">
                    <div class="title"><p class="fs32 fw_b color_3">价格区间</p><img src="/static/mobile/default/images/goodslist05.png" alt="" data-type="0"></div> 
                    <div class="list moneyBox">
                      <div><input type="text" class="fs30 color_3" id="min_price" placeholder="最低价"></div>
                      <em class="fs28 color_3"></em>
                      <div><input type="text" class="fs30 color_3"  id="max_price" placeholder="最高价"></div>
                    </div>
                  </div>
              <div class="block">
                  <div class="title"><p class="fs32 fw_b color_3">分类</p><img src="/static/mobile/default/images/goodslist05.png" alt="" data-type="0"></div> 
                <div class="list class_list">
                    <?php if(is_array($classList) || $classList instanceof \think\Collection || $classList instanceof \think\Paginator): $i = 0; $__LIST__ = $classList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$class): $mod = ($i % 2 );++$i;?>
                        <span class="fs28 color_3 " data-type="class" data-cid="<?php echo htmlentities($class['id']); ?>"><?php echo htmlentities($class['name']); ?></span>
                    <?php endforeach; endif; else: echo "" ;endif; ?>

                </div>
              </div>
              <div class="brandlist">

              </div>

            </div>
            <div class="buttBox">
              <span class="fs28 fw_b color_w BGcolor_3" data-type="clear">清除</span>
              <span class="fs28 fw_b color_w BGcolor_r" data-type="confirm">确定</span>
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

<script type="text/html" id="brand">
<div class="block">
    <div class="title"><p class="fs32 fw_b color_3">品牌</p><img src="/static/mobile/default/images/goodslist05.png" alt="" data-type="0"></div>
    <div class="list">
        {{each list as item index}}
        <span class="fs28 color_3 " data-type="brand" data-brand_id="{{item.id}}">{{item.name}}</span>
        {{/each}}
    </div>
</div>
</script>
<script type="text/html" id="goods">
    {{each list as item index}}
    <div class="Box">
        <a href='<?php echo _url("goods/info",["id"=>"[[item.goods_id]]"]); ?>'>
        <img src="{{item.goods_thumb}}" alt="">
        <div class="info">
            <span class="fs30 color_3" title="{{item.goods_name}}">{{item.short_name?item.short_name:item.goods_name}}</span>
            <div class="color_r fs24 num money"><p class="fw_b fm_p">￥</p><em class="fs36">{{item.exp_price[0]}}</em><p>.{{item.exp_price[1]}}</p></div>
            <div class="fs24 color_9 tips"><em>￥{{item.market_price}}</em><div>已售 {{item.sale_count}}</div></div>
        </div>
        </a>
    </div>
    {{/each}}
</script>
<script>
    var nowPage = 1,getAgain = false,isLoadend = false;
    var postData = <?php echo json_encode($input); ?>;
    $(function(){
        $('.page-bd').scroll(function(){
            let box_h=$(this).height()
            let content_h=$('.scrollBox').height()
            let scroll_h=$(this).scrollTop();
            if(content_h-box_h-scroll_h<20){
                getList(nowPage);
            }
        })
        //请求列表数据
        function getList(page,isagain) {
            if (page == 1){
                $('.goodslist').html('');
            }
            if (isLoadend == true ) return false;
            if (isagain == true){
                if (getAgain == false) return false;
                getAgain = false;
            }else{
                $('.goodslist').append('<div class="get_list_tip">加载数据中...</div>');
            }
            isLoadend = true;
            $.ajax({
                url:'<?php echo _url("shop/api.goods/getlist",["p"=>"【page】"]); ?>',// 跳转到 action
                data:postData,
                type:'post',
                cache:false,
                dataType:'json',
                success:function(res) {
                    isLoadend = false;
                    if(res.code  == 0 ){
                        _alert(res.msg);
                        getAgain = true;
                        $('.get_list_tip').html('加载失败，点击重新加载.');
                        return false;
                    }
                    nowPage = page;
                    nowPage++;
                    $('.get_list_tip').remove();
                    if (res.list){
                        $('.goodslist').append(template('goods',res));
                    }else{
                        $('.goodslist').append('<div class="get_list_tip">---没有找到相关商品---</div>');
                    }

                    if (res.page_count == page) {
                        $('.goodslist').append('<div class="get_list_tip">---我也有底线的---</div>');
                        isLoadend = true;
                    }
                },error : function() {
                    isLoadend = false;
                    getAgain = true;
                    $('.get_list_tip').html('加载失败，点击重新加载.');
                }
            });
        }
        //重新请求数据
        $(document).on('click','.get_list_tip',function () {
                getList(nowPage,true);
        })
        getList(nowPage);//执行商品加载
        $('.rank').on('click',function(){
            if(!$(this).hasClass('rank_active')){
                let sibImg=$('.rank').find('img')
                sibImg.attr('src','/static/mobile/default/images/goodslist01.png')
                sibImg.attr('data-type','1')
            }
            $(this).addClass('rank_active')
            $(this).siblings().removeClass('rank_active')
            let imgObj=$(this).find('img')
            if(imgObj.attr('data-type')==0){
                imgObj.attr('src','/static/mobile/default/images/goodslist03.png')
                imgObj.attr('data-type','1')
                postData.sort = 'ASC';
            }else{
                imgObj.attr('src','/static/mobile/default/images/goodslist02.png')
                imgObj.attr('data-type','0')
                postData.sort = 'DESC';
            }
            postData.order = $(this).data('type');
            isLoadend = false;
            getList(1);
        })
        //开启规格选择弹窗
        $('.select').on('click',function(){
            $('.model').show()
        })
        //关闭规格选择弹窗
        $('.buttBox span').on('click',function(){
			isLoadend = false;
            var type = $(this).data('type');
            if (type == 'clear'){
                $('.modelContent').find('span').removeClass('tag_active');
                $('.modelContent').find('input').val('');
                $('.brandlist').html('');
                return false;
            }else{				
                postData.min_price = $('#min_price').val();
                postData.max_price = $('#max_price').val();
                postData.cid = $('.class_list').find('.tag_active').data('cid');
                postData.brand_id = $('.brandlist').find('.tag_active').data('brand_id');
            }
            getList(1);
            $('.model').hide()
        })
        $('.modelcover').on('click',function(){
            $('.model').hide()
        })
        //筛选选择
        $(document).on('click','.list span',function(){
            var type = $(this).data('type');
            if (type == 'class'){
                $('.brandlist').html('');
            }
            if ($(this).hasClass('tag_active')){
                $(this).removeClass('tag_active');
            }else{
                $(this).addClass('tag_active');
                $(this).siblings().removeClass('tag_active');
                if (type == 'class'){
                    var cid = $(this).data('cid');
                    //获取分类关联品牌
                    $.ajax({
                        url: '<?php echo _url("shop/api.goods/getBrandList",["cid"=>"【cid】"]); ?>',// 跳转到 action
                        cache: false,
                        dataType: 'json',
                        success: function (res) {
                            if (res.code == 0) {
                                _alert(res.msg);
                                return false;
                            }
                            if ($.isEmptyObject(res.list) == false){
                                $('.brandlist').html(template('brand', res));
                            }
                        }, error: function () {
                            $('.brandlist').html('加载失败.');
                        }
                    })
                }
            }
        })
        //筛选块的收放
        $('.title').on('click',function(){
            $(this).siblings().slideToggle()
            let imgObj=$(this).find('img');
            if(imgObj.attr('data-type')==0){
                imgObj.css('transform','rotate(180deg)')
                imgObj.attr('data-type','1')
            }else{
                imgObj.css('transform','rotate(3600deg)')
                imgObj.attr('data-type','0')
            }
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