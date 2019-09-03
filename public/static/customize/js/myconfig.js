var version = +new Date();
var myconfig = {
    path: '/static/customize/js/',
    alias: {
        'jquery.gcjs': 'jquery/jquery.gcjs',
        'jquery.confirm': 'jquery/confirm/jquery-confirm',
        'tpl': 'dist/tmodjs'
    },
    map: {
        'js': '.js?v=' + version,
        'css': '.css?v=' + version
    },
    css: {
        'jquery.confirm': 'jquery/confirm/jquery-confirm',
    }
    , preload: ['jquery']

};

var myrequire = function (arr, callback) {
    var newarr = [];
    $.each(arr, function () {
        var js = this;

        if (myconfig.css[js]) {
            var css = myconfig.css[js].split(',');
            $.each(css, function () {
                if(typeof myrequire.systemVersion !== 'undefined'){
                    if (myrequire.systemVersion === '1.0.0' || myrequire.systemVersion <= '0.8')
                    {
                        newarr.push("css!" + myconfig.path + this + myconfig.map['css']);
                    }
                    else
                    {
                        newarr.push("loadcss!" + myconfig.path + this + myconfig.map['css']);
                    }
                }else{
                    newarr.push("css!" + myconfig.path + this + myconfig.map['css']);
                }
            });


        }

        var jsitem = this;
        if (myconfig.alias[js]) {
            jsitem = myconfig.alias[js];

        }
        newarr.push(myconfig.path + jsitem + myconfig.map['js']);
    });
    require(newarr, callback);
}
