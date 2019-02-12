define(assets_path + "/assets/dist/system/init", ["$", "dist/application/app", "./delivery", "./courier", "event_drag", "row_sizing", "./template/item.html"],
function(a) {
    "use strict"; {
        var b = (a("$"), a("dist/application/app"));
        b.config
    }
    a("./delivery"),
    a("./courier")
}),
define(assets_path + "/assets/dist/system/delivery", ["$", "dist/application/app"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/app"),
    d = c.config,
    e = b('[data-toggle="delivery"]');
    if (e.length > 0) {
        var f = b('[data-toggle="valuation"]:checked'),
        g = b('[data-toggle="delivery_list"]'),
        h = function() {
            this.way = null,
            f.length > 0 && (this.way = f.data("type")),
            this.init()
        };
        h.prototype = {
            init: function() {
                var a = this;
                e.on("click",
                function() {
                    var c = {},
                    d = b(this);
                    c.delivery = d.data("delivery"),
                    c.valuation = a.valuation(),
                    c.custom = null,
                    c.name = d.data("name"),
                    c.normal = {
                        delivery: c.delivery,
                        index: 0
                    };
                    var e = b("#delivery_item_" + c.delivery);
                    if (this.checked) {
                        a.createTable(c)
                    } else e.hide()
                }),
                b(document).on("click", ".js_add_area",
                function() {
                    var c = b(this),
                    d = c.closest("div.panel"),
                    e = b("table:first", d),
                    f = b("tbody tr", d).length,
                    g = d.data("delivery"),
                    h = {
                        area: "<span class='js_no_area'> 未指定地区</span>"
                    };
                    h.normal = {
                        delivery: g,
                        index: f
                    },
                    a.createCustom(e, h)
                }),
                b('[data-toggle="valuation"]').on("click",
                function(c) {
                    var f = b(this),
                    h = function() {
                        f.prop("checked", !0),
                        g.empty(),
                        e.prop("checked", !1),
                        a.way = f.data("type")
                    },
                    i = function() {
                        c && c.preventDefault(),
                        a.way != f.data("type") && G.ui.tips.confirm(d.lang.confirmChangedelivery,
                        function(res) {
                           if (res == 'cancel'){
								f.prop("checked", !1);
								$('[data-type='+valuation+']').prop("checked", !0);
							}else{
								h()
							}

                        })
                    },
                    j = 0 == g.find("table").length;
                    j ? h() : i()
                })
            },
            createTable: function(a) {
                var c = "#delivery_item_" + a.delivery,
                d = b(c);
                return d.length > 0 ? d.show() : g.append(template("delivery_setting_tpl", a)),
                b(c).closest("div.panel")
            },
            createCustom: function(a, b) {
                a.append(template("delivery_area_tpl", b))
            },
            valuation: function() {
                return b('[data-toggle="valuation"]:checked').data()
            }
        },
        new h,
        b('form button[type="submit"]').on("click",
        function() {
            return 0 == b('[data-toggle="delivery"]:checked').length ? (d.msg.info("至少选择一种运送方式！"), !1) : void 0
        })
    }
}),
define(assets_path + "/assets/dist/system/courier", ["$", "dist/application/app", "event_drag", "row_sizing"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/app"),
    d = c.config;
    a("event_drag"),
    a("row_sizing");
    var e = b("select.js_sys_templet");
    if (e.length > 0) {
        b("select.js_sys_templet").on("change",
        function() {
            var a = b(this).val();
            G.ui.tips.confirm(d.lang.confirmChangetemplet,
            function(b) {
                b && d.msg.redirect(a)
            })
        });
        var f = b(".js_container"),
        g = 1,
        h = b("#background"),
        i = b("#width"),
        j = b("#height"),
        k = a(assets_path + "/assets/dist/system/template/item.html");
        b(".js_print_item").on("click",
        function() {
            var a = b(this),
            c = (a.val(), a.attr("id")),
            d = a.prop("checked"),
            e = b("#" + "item_{0}".format(c));
            if (d) {
                if (0 == e.length) {
                    var g = b(k.format(a.data("text"), c));
                    g.attr("id", "item_{0}".format(c)),
                    l(g.appendTo(f))
                }
            } else e.remove()
        });
        var l = function(a) {
            a.drag("start",
            function(a, c) {
                var d = b(this);
                c.width = d.width(),
                c.height = d.height(),
                c.limit = {
                    right: f.innerWidth() - d.outerWidth(),
                    bottom: f.innerHeight() - d.outerHeight()
                },
                c.isResize = b(a.target).hasClass("resize")
            }).drag(function(a, c) {
                var d = b(this);
                c.isResize ? d.css({
                    width: Math.max(20, Math.min(c.width + c.deltaX, f.innerWidth() - d.position().left) - 2),
                    height: Math.max(20, Math.min(c.height + c.deltaY, f.innerHeight() - d.position().top) - 2)
                }).find("textarea").blur() : d.css({
                    top: Math.min(c.limit.bottom, Math.max(0, c.offsetY)),
                    left: Math.min(c.limit.right, Math.max(0, c.offsetX))
                })
            },
            {
                relative: !0
            }).mousedown(function() {
                b(this).css("z-index", g++)
            }).click(function() {
                var a = b(this);
                f.find("div.item").not(a).removeClass("selected"),
                a.toggleClass("selected")
            })
        },
        m = b(".item", f);
        m.length > 0 &&
        function() {
            b.each(m,
            function() {
                l(b(this))
            })
        } (),
        b('[data-toggle="selectimg"]').bind("insert",
        function(a, b) {
            f.css({
                background: "url(" + b + ") 0px 0px no-repeat"
            })
        }),
        h.on("change",
        function() {
            f.css({
                background: "url(" + h.val() + ") 0px 0px no-repeat"
            })
        }),
        i.on("change",
        function() {
            f.width(i.val())
        }),
        j.on("change",
        function() {
            f.height(j.val())
        }),
        b(document).on("click", "div.js_container .close",
        function() {
            var a = b(this).closest(".item");
            a.remove(),
            b("#" + a.data("id")).prop("checked", !1)
        }),
        b('form button[type="submit"]').on("click",
        function() {
            if (0 == b("input.js_print_item:checked").length) return d.msg.info("打印项未勾选或位置设置不正确！"),
            !1;
            var a = b("<div>{0}</div>".format(f.html()));
            b(".close", a).remove();
            var c = b("div.item", a);
            b.each(c,
            function(a, c) {
                var d = b(c);
                b("pre", d).text(b("#" + d.data("id")).data("value"))
            }),
            a.find(".close").remove(),
            b("#content").val(a.html().trim())
        })
    }
}),
define(assets_path + "/assets/dist/system/template/item.html", [], '<div class="item" style="width: 171px; height: 41px;" data-id="{1}">\n<a  href="javascript:;" class="close" >&times;</a><pre>{0}</pre><div class="resize"></div></div>');