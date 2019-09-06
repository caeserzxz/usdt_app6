var version = +new Date();
require.config({
    urlArgs: 'v=' + version, 
    baseUrl: '/static/customize/mobile/js/',
    paths: {
        'jquery': '../../js/jquery/jquery-1.11.1.min',
        'jquery.gcjs': '../../js/jquery/jquery.gcjs',
        'tpl':'../../js/dist/tmodjs',
        'foxui':'../../js/dist/foxui/js/foxui.min',
        'foxui.picker':'../../js/dist/foxui/js/foxui.picker.min',
        'foxui.citydata':'../../js/dist/foxui/js/foxui.citydata.min',
        'foxui.citydatanew':'../../js/dist/foxui/js/foxui.citydatanew.min',
        'foxui.street':'../../js/dist/foxui/js/foxui.street.min',
        'jquery.qrcode':'../../../js/jquery/jquery.qrcode.min',
        'ydb':'../../js/dist/Ydb/YdbOnline',
        'swiper':'../../js/dist/swiper/swiper.min',
        'jquery.fly': '../../js/jquery/jquery.fly',

    },
    shim: {
        'foxui':{
            deps:['jquery']
        },
        'foxui.picker': {
            exports: "foxui",
            deps: ['foxui','foxui.citydata']
        },
		'jquery.gcjs': {
	                 deps:['jquery']
		},
		'jquery.fly': {
	                 deps:['jquery']
		}
    },
    waitSeconds: 0
});
