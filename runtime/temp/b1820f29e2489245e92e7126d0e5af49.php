<?php /*a:2:{s:46:"../template/default/member\passport\login.html";i:1550818706;s:37:"../template/default/layouts\base.html";i:1551315553;}*/ ?>
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
    
<link rel="stylesheet" href="/static/mobile/default/css/login.css" />

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
        <div class="top">
          <img src="/static/mobile/default/images/APPLOGO.png" alt="">
          <span class="fs34 color_3 fw_b"><?php echo settings('shop_title'); ?></span>
        </div>
        <div class="inputBlock bor_b">
            <div class="tips fs34 color_9">账号</div>
            <input type="text" class="fs34 fw_b textBox" id="mobile">
        </div>
        <div class="inputBlock bor_b">
            <div class="tips fs34 color_9">密码</div>           
              <input type="password" class="fs38 fw_b textBox" id="password">
              <div class="ereBox">
                  <img src="/static/mobile/default/images/login01.png" alt="" data-type="0">
              </div>
        </div>
        <?php if($sms_fun['login']==1): ?>
        <div class="inputBlock bor_b">
            <div class="tips fs34 color_9">验证码</div>           
              <input type="text" class="fs34 fw_b textBox code" id="code">
              <div class="codeBox">
                  <input type="text" value="获取验证码" id="codebtn" class="color_r fs28 fw_b" readonly onclick="codeButton()">
              </div>  
        </div>
        <?php endif; ?>
        <a href="<?php echo url('forgetPwd'); ?>" class="fs30 color_9 remPassword">忘记密码</a>
        <div class="loginbutton fs32 fw_b color_w BGcolor_r login">登录</div>
      <div class="fs30 goregister"><span class="color_9">还没有账号，</span><a href="<?php echo url('register'); ?>" class="fw_b color_3">立即注册</a></div>
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
      $('.textBox').on('click',function(){
        $(this).siblings('.tips').animate({'fontSize':'0.186666rem','top':'0.15rem'})
      })
      $('.inputBlock input').blur(function(){
        if($(this).val()==''){
          $(this).siblings('.tips').animate({'fontSize':'0.226666rem','top':'0.36rem'})
        }
      })
      $('.ereBox').on('click',function(){
        let obj=$(this).find('img')
        if(obj.attr('data-type')=='0'){
          obj.attr('src','/static/mobile/default/images/login02.png')
          obj.attr('data-type','1')
          $(this).siblings('input').attr('type','text')
        }else{
          obj.attr('src','/static/mobile/default/images/login01.png')
          obj.attr('data-type','0')
          $(this).siblings('input').attr('type','password')
        }
      })
    })
    $('.login').on('click',function(){
        var arr = new Object();
        arr.mobile = $('#mobile').val();
        arr.password = $('#password').val();
        if (arr.mobile == ''){
            _alert('请输入手机号码.');
            return false;
        }
        if ($('#code').length > 0){
            arr.code = $('#code').val();

            if (arr.code == ''){
                _alert('请输入验证码..');
                return false;
            }
        }
        if (arr.password == '' ){
            _alert('请输入用户密码.');
            return false;
        }
        if (arr.password.length < 8){
            _alert('用户密码长度不能小于八位.');
            return false;
        }

        jq_ajax('<?php echo url("member/api.passport/login"); ?>',arr,function (res) {
            if (res.code == 0) {
                _alert(res.msg);
                return false;
            }
            window.location.href = '<?php echo url("center/index"); ?>';
        })
    })
    function codeButton(){
        var arr = new Object();
        arr.type = 'login';
        arr.mobile = $('#mobile').val();
        if (arr.mobile == ''){
            _alert('请输入手机号码');
            return false;
        }
        jq_ajax('<?php echo url("publics/api.sms/sendCode"); ?>',arr,function (res) {
            if (res.code == 0){
                _alert(res.msg);
                return false;
            }
            var codebtn = $("#codebtn");
            setTimeout(function(){
                codebtn.css("color","#999");
                codebtn.attr("disabled","disabled");
            },1000)
            var time = 60;
            var set=setInterval(function(){
                codebtn.val(""+--time+"s");
            }, 1000);
            setTimeout(function(){
                codebtn.attr("disabled",false).val("获取验证码");
                codebtn.css("color","#F65236");
                clearInterval(set);
            }, 60000);
        })

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