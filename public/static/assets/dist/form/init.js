var not_kindeditor = false;
define(assets_path+"/assets/dist/form/init", ["$", "./controls", "dist/application/app", "./validate", "./tip"],
function(a, b) {
    "use strict";
    var c = (a("$"), a("./controls")),
    d = a("./validate");
    b.init = function(a) {
        c.init(a),
        d.init(a)
    }
}),
define(assets_path+"/assets/dist/form/controls", ["$", "dist/application/app"],
function(a, b) {
    "use strict";
    var c = a("$"),
    d = a("dist/application/app"),
    e = d.config,
    f = d.method;
    b.init = function(b) {
        
		function d() {
            var a = KindEditor.editor({themeType: "simple",allowFileManager: true});
            h.on("click",
            function(b) {
                var d = c(b.target),
                e = d.prevAll("input[type=text]"),
                f = d.closest("div.input-group").find("input[type=text]"),
                g = d.data("insert"),
                h = d.data("input"),
                i = e.val();
                h && (i = f.val()),
                a.loadPlugin("smimage",
                function() {
                    a.plugin.imageDialog({
                        imageUrl: i,
                        clickFn: function(b) {
                            if (g || void 0 == g) {
                                e.val(b).hide();
                                var c = d.prevAll("img");
                                if (c.length > 0) c.attr("src", b).removeClass("hide");
                                else {
                                    var i = '<img class="thumb_img" src="{0}" style="max-height: 100px;">';
                                    e.before(i.format(b))
                                }
                            } else d.trigger("insert", b);
                            h && f.val(b),
                            a.hideDialog()
                        }
                    })
                })
            })
        }
        var g = c('[data-toggle="kindeditor"]', b),
        h = c('[data-toggle="selectimg"]', b);
        g.length > 0 ? a.async("kindeditor",
        function() {
            g.each(function() {
                var a = c(this),
                b = a.data("config"),
                d = a.data("tongji"),
                f = b ? e.kindeditor[b] : e.kindeditor["default"];
                if (d) {
                    var g = a.data("tongjiTarget"),
                    h = a.data("ruleRangelength")[1];
                    if (g) {
                        var i = c(g),
                        j = i.html();
                        f = c.extend({},
                        f, {
                            afterChange: function() {
                                var a = "remain" == d ? h - this.count("text") : this.count("text");
                                0 > a || a > h ? i.addClass("error") : i.removeClass("error"),
                                i.html(j.format(a))
                            }
                        })
                    }
                }
                KindEditor.create(a, f)
            }),
            d()
        }) : h.length > 0 && a.async(["kindeditor", assets_path+"/js/kindeditor/themes/default/default.css"],
        function() {
            d()
        });
        var i = c('[data-toggle="method"]');
        if (i.length > 0) {
            var j = c("#method"),
            k = i.data("confirm");
            i.on("click",
            function() {
                var a = c(this),
                b = a.data("method"),
                d = function() {
                    j.val(b),
                    a.button("loading"),
                    i.closest("form").submit()
                };
                k && G.ui.tips.confirm(i.data("msg") || e.lang.confirmCloseOrder,
                function(a) {
                    a && d()
                }),
                !k && d()
            })
        }
        var l = c('[data-toggle="location"]', b);
        l.length > 0 && a.async("dist/location/init.js",
        function(a) {
            c.each(l,
            function() {
                var b = c(this),
                d = c('[data-location="provinces"]', b),
                e = c('[data-location="city"]', b),
                f = c('[data-location="district"]', b),
                g = new a.select({
                    data: a.data
                });
                g.bind(d),
                g.bind(e),
                g.bind(f)
            })
        });
        var m = c('[data-toggle="select_level2"]', b);
        if (m.length > 0) {
            var n = c('[data-location="select_level_1"]', b),
            o = c('[data-location="select_level_2"]', b),
            p = new f.select({
                data: window.select_level2_data
            });
            p.bind(n),
            p.bind(o)
        }
        var q = c(".js_sortable");
        q.length &&
        function() {
            q.sortable()
        } ()
    }
}),
define(assets_path+"/assets/dist/form/validate", ["$", "dist/application/app", assets_path+"/assets/dist/form/tip"],
function(a, b) {
    "use strict";
    var c = a("$"),
    d = a("dist/application/app"),
    e = d.config;
    b.init = function(b) {
        var d = b.hasClass("form-modal"),
        g = null;
        d && (g = b.parents(".modal"), g.on("hidden",
        function() {
            b.resetForm()
        }));
        var h = {
            errorElement: "span",
            errorClass: "help-block error",
            errorPlacement: function(a, b) {
                var c = b.parents(".input-group");
                c.length > 0 ? c.after(a) : b.after(a)
            },
            highlight: function(a) {
                c(a).removeClass("error has-success").addClass("error")
            },
            success: function(a) {
                a.addClass("valid")
            },
            onkeyup: function(a) {
                c(a).valid()
            },
            onfocusout: function(a) {
                c(a).valid()
            },
            submitHandler: function(a) {
                var b = !0;
                if (c('[data-toggle="kindeditor"]', a).length > 0 && "undefined" != typeof KindEditor && KindEditor.instances && c.each(KindEditor.instances,
                function() {
                    this.sync();
                    var a = c(this.srcElement[0]),
                    d = a.data("ruleRequired"),
                    f = a.data("msgRequired"),
                    g = a.data("ruleRangelength"),
                    h = a.data("msgRangelength"),
                    i = c.trim(a.val()).replace(/(&nbsp;)|\s|\u00a0/g, "");
					if (typeof(not_kindeditor) != "undefined" && not_kindeditor == true) return true;
                    if (d && 0 == i.length) {
                        var j = f;
                        return G.ui.tips.info(j || "内容不能为空"),
                        b = !1,
                        !1
                    }
                    if (g) {
                        var k = g[0],
                        l = g[1],
                        m = a.val();
                        if (m.length < k || m.length > l) return G.ui.tips.info(h || "内容不能小于{0}且大于{1}".format(k, l)),
                        b = !1,
                        !1
                    }
                }), b) {
                    var d = c("button[type='submit']", a);
                    d.button("loading"),
                    c(a).ajaxSubmit({
                        dataType: "json",
                        success: function(a) {
                            d.button("reset"),
                            //g && g.modal("hide"),
                            f.run(a)
                        },
                        error: function(a) {
                            d.button("reset"),
                            g && g.modal("hide"),
                             G.ui.tips.info(a.responseText)
                        }
                    })
                }
            }
        },
        i = b.data("ignore");
        i && (h.ignore = i);
        var j = c("div.errorContainer"),
        k = {
            errorContainer: c("div.errorContainer"),
            errorLabelContainer: c("div.errorLabelContainer"),
            errorElement: "label"
        };
        j.length > 0 && c.extend(h, k),
        a.async(["validate", "jform"],
        function() {
            b.each(function() {
                c(this).validate(h)
            })
        })
    };
    var f = a(assets_path+"/assets/dist/form/tip")
}),
define(assets_path+"/assets/dist/form/tip", ["$", "dist/application/app"],
function(require, exports, module) {
    "use strict";
    var $ = require("$"),
    app = require("dist/application/app"),
    config = app.config;
    return exports.run = function(data) {
		if (data.url == 'reload')  return window.location.reload();
        switch (data.code) {
        case 1:
			if (data._fun) eval(data._fun+'(data)');
			$(".modal-dialog .close").trigger("click");
            G.ui.tips.suc(data.msg || config.lang.saveSuccess, data.url);
            break;
        default:
            if (data.msg != 'no') G.ui.tips.suc(data.msg || config.lang.exception, data.url)
        }
        data.callback && eval(data.callback)
    },
    exports
});