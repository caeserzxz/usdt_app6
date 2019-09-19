
define(assets_path+"/assets/dist/plupload/file", ["./fileupload", "$", "dist/application/app", "jquery_ui"],
function(a) {
    "use strict";
    a("./fileupload")
}),
define(assets_path+"/assets/dist/plupload/fileupload", ["$", "dist/application/app", "jquery_ui"],
function(require, exports, module) {
    "use strict";
    var $ = require("$"),
    app = require("dist/application/app"),
    config = app.config;
    require("jquery_ui");
    $(".js_file_upload").each(function() {
        var $el = $(this),
        $form = $el.closest(".js_upload_container"),
        sname = $el.data("submitname"),
        upload_path = $el.data("uploadpath"),
        datas = eval("(" + $el.data("data") + ")") || null;
        $el.uploader({
            multi: !0,
            url: upload_path,
            data: datas,
            FilesAdded: function(a, b) {
                $.each(b,
                function(a, b) {
                    $el.html('开始上传');
                })
            },
            UploadProgress: function(a, b) {
                $el.html('正在上传：'+Math.ceil(b.size / 1024)+'KB('+b.percent+'%)');
            },
            FileUploaded: function(a, b, c) {
                if (c.code == 1){
                    _alert(c.msg);
                    return false;
                }
                $el.parent().find('input[type="text"]').val(c.filename);
            },
            UploadComplete: function() {
                $el.html('上传成功，点击重新上传');
            }
        },
        "file"),
        $("ul.ipost-list", $form).sortable({
            opacity: .8
        })
    })
})
