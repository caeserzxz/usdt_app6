<?php /*a:1:{s:76:"D:\phpStudy\WWW\mainshop\application\shop\view\sys_admin\edit_page\edit.html";i:1553129665;}*/ ?>

<!DOCTYPE html>
<html class="grey-bg">
<head lang="en">
    <meta charset="UTF-8">
    <title>主页面编辑</title>
    <meta http-equiv="Pragma" contect="no-cache">
    <link rel="icon" type="image/png" href="/static/favicon.ico"/>
    <link media="all" type="text/css" rel="stylesheet" href="/static/editPage/css/bootstrap.min.css">
    <link media="all" type="text/css" rel="stylesheet" href="/static/editPage/css/jquery.fancybox.css">
    <link media="all" type="text/css" rel="stylesheet" href="/static/editPage/css/owl.carousel.css">
    <link media="all" type="text/css" rel="stylesheet" href="/static/editPage/css/layout.css">
    <link media="all" type="text/css" rel="stylesheet" href="/static/editPage/css/baseStyle.css?v=1">

    <script> function writeObj(obj){ 
        var description = ""; 
        for(var i in obj){   
            var property=obj[i];   
            description+=i+" = "+property+"\n";  
        }   
        alert(description); 
    } 
    var _save_url = '<?php echo url("edit"); ?>';//保存链接
	var _upload_url = '<?php echo url("upload"); ?>?';//上传介面
	var _products_select = '<?php echo url("selproducts"); ?>';//选择商品
    var alinks = "<?php echo url('publics/sys_admin.links/index'); ?>";
    var McMore = {
        uploadImageCallback: function (result) {
        },
        selectProductsCallback: function (result) {
            if (result.state == 1) {
                $('.tab-content.active .products-list ul').append($(result.html));
                $('.update-data').click();
            }
            return true;
        },

        page: <?php echo $theme['page']; ?>,

        current_page: { "theme": "<?php echo htmlentities($theme['theme_type']); ?>-<?php echo htmlentities($theme['select_theme']); ?>"},

        componentDefault: <?php echo $componentDefault; ?>,

        components: [{
            'id': 1,
            'title': '商品橱窗',
            'alias': 'products'
        }, {
            'id': 2,
            'title': '搜索栏',
            'alias': 'search'
        }, {
            'id': 4,
            'title': '广告图片',
            'alias': 'ads'
        }, {
            'id': 5,
            'title': '幻灯片',
            'alias': 'slideshow'
        }, {
            'id': 6,
            'title': '主导航',
            'alias': 'mainmenu'
        }, {
            'id': 7,
            'title': '功能入口',
            'alias': 'navigator'
        }, {
            'id': 8,
            'title': '联系我们',
            'alias': 'contact'
        }, {
            'id': 3,
            'title': '扩展排版',
            'alias': 'exttypeset'
        }, {
            'id': 9,
            'title': '自定菜单',
            'alias': 'extmenu'
        }]

    };





</script>    <script data-main="/static/editPage/js/main" src="/static/editPage/js/require.js"></script>

</head>
<body class="<?php echo htmlentities($theme['theme_type']); ?>-<?php echo htmlentities($theme['select_theme']); ?>">
<div id="main-wrapper" class="clearfix">
    <div id="header-wrapper">
        <div id="header">
           
            <div id="select-wrapper">
                <ul class="list-unstyled list-inline" >
                    <li>
                        <span class="theme-label">主题风格</span>
                        <ul class="list-unstyled select-themes" style="margin-left:15px; margin-top:5px;">
                             <li class="clearfix">
                                  <dl class=" list-unstyled list-inline ">
                                   <?php $__FOR_START_32494__=1;$__FOR_END_32494__=11;for($i=$__FOR_START_32494__;$i < $__FOR_END_32494__;$i+=1){ ?>
                                         <dd class="theme-item <?php echo $theme['select_theme']==$i ? 'active' : ''; ?>" data-code="<?php echo htmlentities($theme['theme_type']); ?>-<?php echo $i<10 ? '0' : ''; ?><?php echo htmlentities($i); ?>" data-id="<?php echo htmlentities($i); ?>">
                                                <div class="color-box <?php echo htmlentities($theme['theme_type']); ?>-<?php echo $i<10 ? '0' : ''; ?><?php echo htmlentities($i); ?>"></div>
                                         </dd>
                                    <?php } ?>
                             	</dl>
                         	</li> 
                         </ul>
                    </li>
                     <li><span id="save">保存</span></li>
                 </ul>

            </div>
       
        </div>
    </div>
    <div id="main-content" class="clearfix">
        <div class="left">
            
            <div class="left-bottom">
                <div class="block-title" style="margin-left:15px;">
                    部件库
                </div>
                <div class="components-list clearfix" id="component-region" style="margin-left:15px;">

                </div>
            </div>
        </div>

        <div class="middle">
            <div class="phone-wrapper">
                <div class="phone-wrapper-inner">
                    <div id="preview-region">

                    </div>
                </div>
            </div>
        </div>

        <div class="right">
            <div class="block-title">部件属性</div>
            <div id="config-region">

            </div>
        </div>
    </div>
</div>

<div class="cover">
    <div class="cover-bg"></div>
    <div class="loading-image">
        数据正在加载中...
    </div>
</div>
</body>
</html>