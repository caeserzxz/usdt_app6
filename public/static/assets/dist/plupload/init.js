
define(assets_path+"/assets/dist/plupload/init", ["./imagefile", "$", "dist/application/app", "jquery_ui", "./template/imageTemp.html", "./template/progress.html"],
function(a) {
    "use strict";
    a("./imagefile")
}),
define(assets_path+"/assets/dist/plupload/imagefile", ["$", "dist/application/app", "jquery_ui"],
function(require, exports, module) {
    "use strict";
    var $ = require("$"),
    app = require("dist/application/app"),
    config = app.config,
    method = app.method;
    require("jquery_ui");
    var imageTemp = require(assets_path+"/assets/dist/plupload/template/imageTemp.html"),
    progressTemp = require(assets_path+"/assets/dist/plupload/template/progress.html"),
    render_item = template.compile(imageTemp),
    render_progress = template.compile(progressTemp),
    uploadimte = null;
    $(".js_new_upload").each(function() {
        var $el = $(this),
        $form = $el.closest(".js_upload_container"),
        sname = $el.data("submitname"),
        upload_path = $el.data("uploadpath"),
        deletepath = $el.data("delpath"),
        count = $el.data("count"),
        datas = eval("(" + $el.data("data") + ")") || null;
        $el.uploader({
            multi: !0,
            url: upload_path,
            delete_path: deletepath,
            max_count: count,
            data: datas,
            FilesAdded: function(a, b) {
                $.each(b,
                function(a, b) {
                    $(".js_file_upload_queue", $form).append(render_progress({
                        id: b.id,
                        filename: b.name,
                        filesize: Math.ceil(b.size / 1024)
                    }))
                })
            },
            UploadProgress: function(a, b) {
                var c = "upload{0}".format(b.id);
                if (uploadimte = $("#{0}".format(c)), uploadimte.length) {
                    var d = b.percent + "%";
                    $(".data", uploadimte).text(d),
                    $(".uploadify-progress-bar", uploadimte).width(d)
                }
            },
            FileUploaded: function(a, b, c) {
                $.isPlainObject(c) ? (c.image.progressid = b.id, c.image.sname = sname, c.image.deletepath = deletepath, c.image.index = $form.find("li.imgbox").length, $(".js_fileList", $form).append(render_item(c.image)), uploadimte.remove()) : (uploadimte.addClass("uploadify-error"), uploadimte.remove(), a.files.removeFile(b), config.msg.info(c || config.lang.uplodError))
            },
            UploadComplete: function() {
                $(".uploadify-progress-bar", uploadimte).remove()
            }
        },
        "picture"),
        $("ul.ipost-list", $form).sortable({
            opacity: .8
        })
    }),
    $(document).on("click", "a.item_new_close",
    function(a) {
        var b = $(this),
        c = b.data("progressid"),
        d = b.data("delpath");
       var res = $.post(d, {
            id: b.data("post-id"),
            url: b.data("path")
        });
		if (res.code==0){
			_alert(res.msg);
			return falsel	
		}
        var e = $(a.target).closest("li.imgbox");
        e.fadeOut(function() {
            e.remove(),
            $("#upload" + c).remove()
        })
    })
}),
define(assets_path+"/assets/dist/plupload/template/imageTemp.html", [], '<li class="imgbox" data-post-id="{{id}}" data-path="{{thumbnail}}">\n	<a class="item_new_close item_close" href="javascript:void(0)" data-delpath="{{deletepath}}" data-progressid="{{progressid}}" title="删除"  data-path="{{thumbnail}}" data-post-id="{{id}}"></a>  \n	<input type="hidden" value="{{id}}" name="{{sname}}[id][]"> \n	<input type="hidden" value="{{thumbnail}}" name="{{sname}}[path][]"> \n	<span class="item_box"><img src="{{path}}"></span>\n</li>'),
define(assets_path+"/assets/dist/plupload/template/progress.html", [], '<div id="upload{{id}}" class="uploadify-queue-item">  \n	<span class="fileName">{{filename}} ({{filesize}}KB)</span>\n	<span class="data"> -上传完成 正在加载.....</span>   \n	<div class="uploadify-progress">                        \n		<div class="uploadify-progress-bar" style="width: 1%;"><!--Progress Bar-->\n		</div>                    \n	</div>                \n</div>');