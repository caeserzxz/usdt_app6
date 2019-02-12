var release = true,
    dist = "dist";
window.release = release;
seajs.config({
    paths: {
        
        'dist': assets_path +'/assets/'+ dist
      
    },
    alias: {
        '$': assets_path+'/js/jquery/jquery/1.8.3/jquery_min.js',
        'jquery_ui': assets_path+'/js/jquery/jquery_ui/1.10.4/jquery_ui_custom.min.js',
        'bootstrap': assets_path+'/assets/dist/bootstrap/3.1.1/bootstrap.min.js',
        'select2': assets_path+'/assets/dist/select2/3.4.8/select2.min.js',
        'daterangepicker': assets_path+'/assets/dist/daterangepicker/1.3.7/daterangepicker.min.js',
        'datetimepicker': assets_path+'/assets/dist/datetimepicker/2.0/datetimepicker.min.js',
        'swfobject': assets_path+'/assets/dist/swfobject/2.2.0/swfobject.js', 
        'highcharts': assets_path+'/assets/dist/highcharts/2.3.5/highcharts.min.js',
        'underscore': assets_path+'/assets/dist/underscore/1.4.4/underscore.js',
        'backbone': assets_path+'/assets/dist/backbone/1.0.0/backbone.js',
        'moment': assets_path+'/assets/dist/moment/2.5.1/moment.min.js',
        'slimscroll': assets_path+'/js/jquery/slimscroll/1.3.0/slimscroll.min.js',
        'sparkline': assets_path+'/js/jquery/sparkline/2.1.2/jquery.sparkline.min.js',
        'event_drag': assets_path+'/js/jquery/event_drag/2.2.0/jquery.event.drag.min.js',
        'row_sizing': assets_path+'/js/jquery/row_sizing/0.0.3/jquery.grid.rowsizing.min.js',
        'validate': assets_path+'/js/jquery/validate/1.12.0/jquery.validate.min.js',
        'tagsinput': assets_path+'/js/jquery/tagsinput/1.1.0/jquery.tagsinput.min.js',
        'chosen': assets_path+'/js/jquery/chosen/0.9.11/jquery.chosen.min.js',
        'nestable': assets_path+'/js/jquery/nestable/1.1.0/jquery.nestable.min.js',
        'jform': assets_path+'/js/jquery/form/3.50.0/jquery.form.min.js',
        'plupload':assets_path+'/assets/dist/plupload/2.1.2/plupload.full.min.js',
        'kindeditor': assets_path+'/js/kindeditor/kindeditor-all-min.js'
    },
    preload: ['$', 'bootstrap'],
    map: [
        [/^(.*)\.js$/i, '$1\.js?v=' + _version]
    ],
    debug: !release
});


