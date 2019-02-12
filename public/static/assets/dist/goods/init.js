define("dist/goods/init", ["./classify", "$", "dist/application/app",  "./goods", "chosen"],
function(a) {
    "use strict";
    a("./classify"),
    a("./goods")
}),
define("dist/goods/classify", ["$", "dist/application/app"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/app"),
    e = c.config,
    f = b("#dragClassify"),
    g = (b("#noClassify"), b("#save_class")),
    h = b("#nestableMenu");
    f.length > 0 && a.async("nestable",
    function() {
        f.nestable({
            maxDepth: 3
        }),
        b(".dd-handle a").on("mousedown",
        function(a) {
            a.stopPropagation()
        })
    }),
    g.on("click",
    function() {
        var a = b(this),
        c = a.data("remote") || a.attr("href");
        a.button("loading");
        var g = window.JSON.stringify(f.nestable("serialize"));
        b.post(c, {
            data: g
        }).done(function(b) {
            a.button("reset"),
            e.issucceed(b) ? G.ui.tips.suc(b.message || e.lang.saveSuccess, b.url) : G.ui.tips.suc(b.message || e.lang.saveError, b.url)
        }).fail(function() {
            a.button("reset"),
            G.ui.tips.suc(e.lang.exception, d.url)
        })
    }),
    h.on("click",
    function() {
        var a = b(this).hasClass("active");
        f.nestable(a ? "expandAll": "collapseAll")
    })
	var v = b(".js_quota");
    v.on("click",
    function() {
        var a = b(this).hasClass("js_quota_show"),
        c = b("."+b(this).attr('name')+"_container");
        c.toggleClass("hd", !a).toggleClass("inline", a)
    })
}),

define("dist/goods/goods", ["$", "dist/application/app", "chosen"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/app"),
    d = c.config,
    e = c.method;
    a("chosen");
    var f = b('[data-toggle="attribute"]');
    window.goods_data ? (template.helper("specifications_checked",
    function(a, c) {
        return b.inArray(c, window.goods_data.specifications[a]) > -1 ? 'checked="checked"': d.empty
    })) : (template.helper("specifications_checked",
    function() {
        return d.empty
    }));
	var fn_a;
	var cf = b('[data-toggle="changeSkuModelId"]');
    var h = b('[data-toggle="specifications"]'),
    i = b("div.specificationstable"),
    j = b("div.nospecifications"),
	fn = function(){
		var skuModelId = cf.val();
		if (skuModelId < 1) return false;
		e.post(window.goods_setting.specifications_path,
        function(d) {
            fn_a.SpecificationsList = d.data,
            b.each(d.data,
            function(b, c) {
                fn_a.TableData.ths.push(c)
            }),
            h.html(template("specifications_template", d)),
            fn_a.init(),
            c && fn_a.acquire()
        },
        d.lang.specificationsError,0,'skuModelId='+skuModelId)
	};
	b(document).on("change", '[data-toggle="changeSkuModelId"]',function(c) {fn(j)})
   var k = function() {
        this.specVals = null,
        this.SpecificationsList = null,
        this.TableData = {
            ths: []
        },
        this.specificationstable = b('[data-toggle="specificationstable"]'); {
            var a = this;
            b('[data-toggle="specvals"]')
        }
        b('[data-toggle="specification-enable"]').on("click",
        function() {
            var a = b(this).data("enable");
            h.toggle(a),
            a ? (i.show(), j.hide()) : (i.hide(),j.show())
        });
        var c = b('[data-toggle="specification-enable"]:checked').data("enable");		
        c ? (h.show(), i.show(), j.hide()) :  i.hide(),fn();
        fn_a = a;
    },
    l = function() {};
    l.prototype.getAttrExist = function(a, c) {
        var d, e, f = [],
        g = "";
        return b("." + c).find("dt").each(function() {
            b(this).data("title") && f.push(b(this).data("title"))
        }),
        e = b.inArray(a, f),
        d = 0 > e ? !1 : !0,
        g = b("." + c).find("dt").eq(e).data("id"),
        {
            flag: d,
            id: g
        }
    },
    l.prototype.getAttrValExist = function(a, c) {
        var d, e, f = [];
        return b("#" + c).find("input[type='radio']").each(function() {
            b(this).val() && f.push(b(this).data("title"))
        }),
        e = b.inArray(a, f),
        d = 0 > e ? !1 : !0
    },
    l.prototype.attrValSearch = function(a, c) {
        var d = 0;
        return b("#" + c).find("input[type='radio']").each(function() {
            var c = b(this).data("title") || "选择",
            e = b(this).parent().parent();
            c && (c += "", c.indexOf(a) > -1 ? (d++, e.show()) : e.hide())
        }),
        d
    },
    l.prototype.getSpevalExist = function(a, c) {
        var d, e, f = [];
        return b("#specvals_show_" + c).find(".js_specvals_show").each(function() {
            f.push(b(this).data("name"))
        }),
        e = b.inArray(a, f),
        d = 0 > e ? !1 : !0
    },
    l.prototype.spevalExist = function(a, c) {
        var d, e = [],
        f = this.getSpelist(a).arrval,
        g = "";
        return b("#specvals_show_" + c).find(".js_specvals_show").each(function() {
            e.push(b(this).data("name"))
        }),
        b(f).each(function(a, c) {
            d = b.inArray(c, e),
            d > -1 && (g += c + ",")
        }),
        g
    },
    l.prototype.getnSpevalExist = function(a) {
        var c, d, e = [];
        return b(".js_spe_spev_show").find(".js_specvals_show").each(function() {
            e.push(b(this).data("name"))
        }),
        d = b.inArray(a, e),
        c = 0 > d ? !1 : !0
    },
    l.prototype.closeAttrForm = function() {
        b(".js_add_attr").removeClass("hide"),
        b(".js_add_attr_form").addClass("hide")
    },
    l.prototype.attrresult = function(a, c, e) {
        var f = b("." + e).find("dl:last");
        f.length ? b(template("customattr_tem", a)).insertAfter(f) : b(template("customattr_tem", a)).insertBefore(b("." + e).find(".line-dashed:first")),
        c.val(""),
        c.next(".js_limit").find("em").html(0),
        b(".js_add_attr_input").val(""),
        b(".js_add_attr_input").next(".js_limit").find("em").html(0),
        this.closeAttrForm(),
        G.ui.tips.suc(d.lang.saveSuccess)
    },
    l.prototype.result = function(a, c, e) {
        var f = b("#" + a.attrid).find(".js_custom_list:first");
        b("#" + a.attrid).find("li").removeClass("active"),
        b("#" + a.attrid).find("li input[type='radio']").removeAttr("checked"),
        b(template("customattrval_tem", a)).insertBefore(f),
        b("#label_" + a.attrid).html(a.title),
        b("#label_" + a.attrid).removeClass("error"),
        b(document).trigger("click"),
        c.val(""),
        c.next(".js_limit").find("em").html(0),
        e ? (b(".js_add_attr_input").val(""), b(".js_add_attr_input").next(".js_limit").find("em").html(0), this.closeAttrForm()) : (f.addClass("hide"), f.next(".js_custom_list").removeClass("hide")),
        b("#" + a.attrid).find(".js_search_result").addClass("hide"),
        G.ui.tips.suc(d.lang.saveSuccess)
    },
    l.prototype.customAttrValvali = function(a) {
        var b = !0,
        c = a.data("limit") - 0,
        d = this.getInputLen(a);
        return b = d > 2 * c ? !1 : !0
    },
    l.prototype.customInput = function() {
        var a = this;
        b(document).on("keyup", ".js_custom_input",
        function() {
            var c = a.getInputLen(b(this)),
            d = b(this).data("limit") - 0,
            e = b(this).next(".js_limit");
            e.find("em").html(Math.ceil(c / 2)),
            c > 2 * d ? e.addClass("error") : e.removeClass("error")
        })
    },
    l.prototype.getInputLen = function(a) {
        return a.val().replace(/[^\x00-\xff]/g, "xx").length
    },
    l.prototype.customdata = function(a) {
        return {
            attrid: a.data("id"),
            attrval: a.val()
        }
    },
    l.prototype.getSpelist = function(a) {
        var c = [],
        d = [];
        return a.find(".js_specvals_temp").each(function() {
            c.push(b(this).data("name"))
        }),
        a.find(".js_specvals_del .js_del_specvals_val_list").each(function() {
            d.push(b(this).data("id"))
        }),
        {
            arrval: c,
            idarr: d
        }
    },
    l.prototype.saveSpeval = function(a, c) {
		a.skuModelId = b('[data-toggle="changeSkuModelId"]').val();
        var e = this,posttxt = '<i class="fa fa-spinner fa-spin"></i>提交中';
		if (c.html()==posttxt)return false;
        c.data("loadingText", posttxt),
        c.button("loading"),
        b.ajax(window.goods_setting.custom_spevalue_path, {
            type: "post",
            data: a,
            dataType: "json"
        }).done(function(a) {
            c.button("reset"),
            d.issucceed(a) ? (e.resultSpeval(a.data), G.ui.tips.suc(a.msg || d.lang.removeSuccess)) : G.ui.tips.suc(a.msg)
        }).fail(function() {
            c.button("reset"),
            G.ui.tips.suc(d.lang.attributeError)
        })
    },
    l.prototype.saveSpe = function(a, c) {
		a.skuModelId = b('[data-toggle="changeSkuModelId"]').val();
        var e = this,posttxt = '<i class="fa fa-spinner fa-spin"></i>提交中';
		if (c.html()==posttxt)return false;
        c.data("loadingText", posttxt),
        c.button("loading"),
        b.ajax(window.goods_setting.custom_spe_path, {
            type: "post",
            data: a,
            dataType: "json"
        }).done(function(a) {
            c.button("reset"),
            d.issucceed(a) ? (e.resultSpe(a.data), b(".js_nospe_tips").addClass("hide"), G.ui.tips.suc(a.msg || d.lang.removeSuccess)) : G.ui.tips.suc(a.msg)
        }).fail(function() {
            c.button("reset"),
            G.ui.tips.suc(d.lang.attributeError)
        })
    },
    l.prototype.delSpeval = function(a) {
		a.skuModelId = b('[data-toggle="changeSkuModelId"]').val();
        var c = this;
        b.ajax(window.goods_setting.custom_delspevalue_path, {
            type: "post",
            data: a,
            dataType: "json"
        }).done(function(a) {
            d.issucceed(a) ? (c.resultdelSpeval(a.data), m.SpecificationsListskey().delspvals(a.data.speid, a.data.spevalidList), G.ui.tips.suc(a.message || d.lang.removeSuccess)) : G.ui.tips.suc(a.message)
        }).fail(function() {
            G.ui.tips.suc(d.lang.attributeError)
        })
    },
    l.prototype.resultSpeval = function(a) {
        var c = a.speid,
        d = b("#specvals_" + c),
        e = b("#specvals_show_" + c).find("div");
        d.append(template("specv_tem", a)),
        this.closeopen(c, !0),
        e.find(".js_specvals_temp").remove(),
        e.append(template("specv_result_tem", a))
    },
    l.prototype.resultSpe = function(a) {
        b(template("spe_result_tem", a)).insertBefore(b(".js_add_spe_div"));
        var c = {
            id: a.id,
            name: a.name,
            all_val: a.spevalList
        };
        m.SpecificationsList.push(c),
        m.TableData.ths.push(c),
        m.acquire(),
        b(".js_add_spe_input").val(""),
        b(".js_add_spe_input").next(".js_limit").find("em").html(0),
        b(".js_spe_spev_show").find(".js_specvals_temp").remove(),
        this.specloseopen(!0)
    },
    l.prototype.resultdelSpeval = function(a) {
        var c = a.speid,
        d = b("#specvals_" + c),
        e = b("#specvals_show_" + c).find("div");
        this.closeopen(c, !0),
        b(a.spevalidList).each(function(a, b) {
            d.find("#spe_" + b).remove()
        }),
        e.find(".js_specvals_del").remove()
    },
    l.prototype.closeopen = function(a, c) {
        b("#specvals_ed_" + a).toggleClass("hide", c),
        b("#js_specifications_" + a).toggleClass("hide", !c),
        b(".js_add_spe_div,.js_specifica_edit,.js_specifica_del").toggleClass("hide", !c)
    },
    l.prototype.specloseopen = function(a) {
        b(".js_add_spe_form").toggleClass("hide", a),
        b(".js_add_spe_div,.js_specifica_edit,.js_specifica_del").toggleClass("hide", !a)
    },
    l.prototype.getSpeExist = function(a) {
        var c, d, e = [],
        f = "";
        return b("[data-toggle='specifications']").find(".js_specifica").each(function() {
            b(this).data("name") && e.push(b(this).data("name"))
        }),
        d = b.inArray(a, e),
        c = 0 > d ? !1 : !0,
        f = b("[data-toggle='specifications']").find(".js_specifica").eq(d).data("id"),
        {
            flag: c,
            id: f
        }
    },
    k.prototype.acquire = function() {
        this.selectSpecifications_vals = new Array;
        var a = this;
        a.TableData.ths = [],
        b.each(this.SpecificationsList,
        function(c, d) {
            var e = d.id,
            f = b("#specvals_" + e).find('[type="checkbox"]:checked'),
            g = new Array;
            b.each(f,
            function(a, b) {
                g.push({
                    val: b.title,
                    key: b.value
                })
            });
            var h = g.length > 0;
            h && a.TableData.ths.push(d),
            h && a.selectSpecifications_vals.push(g)
        }),
        this.showtable = this.selectSpecifications_vals.length,
        this.showtable && this.generate(),
        this.showtable || this.specificationstable.html('<span class="help-inline p-t">请选择规格</span>')
    },
    k.prototype.generate = function() {
        this.res = this.combine(this.selectSpecifications_vals.reverse()),
        this.rowspan(),
        this.TableData.trs = [];
        var a = this;
        b.each(this.res,
        function(c, d) {
            var e = [],
            f = [];
            b.each(d,
            function(b, d) {
                var g = [];
                g.rowspan = a.row[b],
                g.key = d.key,
                g.val = d.val,
                g.index = c,
                e.push(g),
                f.push(d.key)
            });
            var g = f.join(":"),
            h = {
                tds: e,
                index: c,
                key: g
            };
            if (a.changeval(g), window.goods_data || (window.goods_data = {
                products: {}
            }), window.goods_data && window.goods_data.products) {
                var i = window.goods_data.products[g];
                i && (h = b.extend({},
                h, i))
            }
            a.TableData.trs.push(h)
        }),
        this.specificationstable.html(template("specifications_table_template", this.TableData)),
        i.show(),
		WebUploaderDiy('.upload-file');
    },
    k.prototype.changeval = function(a) { ! window.goods_data.products[a] && (window.goods_data.products[a] = {}),
        b(document).on("change", '[data-id="' + a + '"]',
        function() {
            var a = b(this),
            c = a.data("id"),
            d = a.data("name"),
            e = b('[data-id="' + c + '"]'),
            f = {};
			
            b.each(e,
            function() {
                var c = {},
                e = a.val();
                c[d] = e,
                f = b.extend({},
                f, c)
            });
            var g = a.closest("tr").siblings().find('[data-name="' + d + '"]');
            a.valid() && b.each(g,
            function() {
                var c = b(this);
                if (c.data('nochangeval') != 1) {
                    c.val().length < 1 && (c.val(a.val()), c.valid())
                }
            }),
            window.goods_data.products[c] = f
			
        })
    },
    k.prototype.rowspan = function() {
        for (var a = [], b = this.res.length, c = this.selectSpecifications_vals.length - 1; c > -1; c--) a[c] = parseInt(b / this.selectSpecifications_vals[c].length),
        b = a[c];
        this.row = a.reverse()
    },
    k.prototype.refresh = function() {
        var a = this;
        a.TableData.ths = [],
        b.each(a.SpecificationsList,
        function(b, c) {
            a.TableData.ths.push(c)
        }),
        this.acquire()
    },
    k.prototype.SpecificationsListskey = function() {
        var a = this;
        return {
            delsp: function(b) {
                for (var c = [], d = 0, e = a.SpecificationsList.length; e > d; d++) a.SpecificationsList[d].id != b && c.push(a.SpecificationsList[d]);
                a.SpecificationsList = c,
                a.refresh()
            },
            delspvals: function(b, c) {
                for (var d = 0,
                e = a.SpecificationsList.length; e > d; d++) if (a.SpecificationsList[d].id == b) {
                    for (var f = [], g = 0, h = a.SpecificationsList[d].all_val.length; h > g; g++) for (var i = 0,
                    j = c.length; j > i; i++) a.SpecificationsList[d].all_val[g].key != c[i] && f.push(a.SpecificationsList[d].all_val[g]);
                    a.SpecificationsList[d].all_val = f
                }
                a.refresh()
            }
        }
    },
    k.prototype.combine = function(a) {
        var b = [];
        return function c(a, d, e) {
            if (0 == e) return b.push(a);
            for (var f = 0; f < d[e - 1].length; f++) c(a.concat(d[e - 1][f]), d, e - 1)
        } ([], a, a.length),
        b
    },
    k.prototype.init = function() {
        var a = this;
        b(document).on("click", '[data-toggle="specvals"] input[type="checkbox"]',
        function() {
            a.acquire()
        });
        var c = new l;
		c.customInput();//add by iqgmy
        b(document).on("click", ".js_add_speval",
        function() {
            var a = b(this).closest(".form-group").find("input"),
            d = b(this).data("id"),
            e = a.val();
            c.customAttrValvali(a) && e ? c.getSpevalExist(e, d) ? b("#specvals_error_" + d).html("输入的规格值已存在") : (b("#specvals_error_" + d).html(""), b("#specvals_show_" + d).find("div").append(template("specv_show_tem", {
                val: e,
                speid: d
            })), a.val(""), a.next(".js_limit").find("em").html(0)) : b("#specvals_error_" + d).html("请输入规格值，长度不要超过15个字")
        }),
        b(document).on("click", ".js_spe_speval",
        function() {
            var a = b(".js_add_spev_input"),
            d = a.val();
            c.customAttrValvali(a) && d ? c.getnSpevalExist(d) ? b(".js_js_spe_spev_error").html("输入的规格值已存在") : (b(".js_js_spe_spev_error").html(""), b(".js_spe_spev_show").append(template("specv_show_tem", {
                val: d,
                speid: ""
            })), a.val(""), a.next(".js_limit").find("em").html(0)) : b(".js_js_spe_spev_error").html("请输入规格值，长度不要超过15个字")
        }),
        b(document).on("click", ".js_specifica_edit",
        function() {
            var a = b(this).data("id");
            c.closeopen(a, !1),
            b(".js_add_spe_div,.js_specifica_edit,.js_specifica_del").addClass("hide")
        }),
        b(document).on("click", ".js_specvals_val_save",
        function() {
            var a = b(this).data("id"),
            e = b(this).closest(".form-group").prev(".form-group"),
            f = c.getSpelist(e),
            g = f.arrval,
            h = f.idarr,
            i = {
                speid: a,
                speval: g
            };
            g.length > 0 && c.saveSpeval(i, b(this)),
            h.length > 0 && c.delSpeval({
                speid: a,
                idarr: h
            },
            b(this)),
            g.length < 1 && h.length < 1 && G.ui.tips.suc("请添加规格值或者删除规格值")
        }),
        b(document).on("click", ".js_specvals_val_cancel",
        function() {
            var a = b(this).data("id");
            c.closeopen(a, !0)
        }),
        b(document).on("click", ".js_del_specvals_val_list",
        function() {
            var a = b(this).data("id");
            a ? b(this).parent().removeClass("js_specvals_result").addClass("hide js_specvals_del") : b(this).parent().remove()
        }),
        b(document).on("click", ".js_add_spe_btn",
        function() {
            c.specloseopen(!1)
        }),
        b(document).on("click", ".js_add_spe_save",
        function() {
            var a = b(".js_add_spe_input").val(),
            e = (b(".js_add_spev_input").val(), b(".js_spe_spev_show").find(".js_specvals_temp").length),
            f = c.getSpeExist(a);
            if (b(".js_js_spe_spev_error").html(""), a) if (c.customAttrValvali(b(".js_add_spe_input"))) if (f.flag) if (e > 0) {
                var g = c.spevalExist(b(".js_spe_spev_show"), f.id);
                if (g.length > 0) b(".js_js_spe_spev_error").html("输入的规格值：" + g + "已存在");
                else {
                    var h = f.id,
                    i = b(this).closest(".form-group").prev(".form-group"),
                    j = c.getSpelist(i),
                    k = j.arrval,
                    l = (j.idarr, {
                        speid: h,
                        speval: k
                    });
                    k.length > 0 && (c.saveSpeval(l, b(this)), b(".js_add_spe_input").val(""), b(".js_add_spe_input").next(".js_limit").find("em").html(0), b(".js_spe_spev_show").find(".js_specvals_temp").remove(), c.specloseopen(!0))
                }
            } else G.ui.tips.suc("请添加规格值");
            else {
                var m = c.getSpelist(b(".js_spe_spev_show")).arrval,
                l = {
                    spe: a,
                    speval: m
                };
                m.length > 0 ? c.saveSpe(l, b(this)) : G.ui.tips.suc("请添加规格值")
            } else G.ui.tips.suc("规格只能输入5个字请修改规则");
            else G.ui.tips.suc("请添加规格")
        }),
        b(document).on("click", ".js_add_spe_cancel",
        function() {
            c.specloseopen(!0)
        }),
        b(document).on("click", ".js_specifica_del",
        function() {
            var c = b(this).data("id"),
            f = b(this).data("name"),
			skuModelId = b('[data-toggle="changeSkuModelId"]').val();;
            G.ui.tips.confirm_flag("将会删除所有相关规格的商品，请核实！<br>确定删除规格：" + f + "，及其所属的规格吗？",
            function(f) {$('#fallr-wrapper').remove();
                e.post(window.goods_setting.custom_spe_del_path,
                function(c) {
                    d.issucceed(c) ? (G.ui.tips.suc(c.msg || d.lang.removeSuccess), b("#js_specifications_" + c.data.speid).remove(), b("#specvals_ed_" + c.data.speid).next(".line-dashed").remove(), b("#specvals_ed_" + c.data.speid).remove(), a.SpecificationsListskey().delsp(c.data.speid), b(".js_specifica").length < 1 && b(".js_nospe_tips").removeClass("hide")) : G.ui.tips.suc(c.msg)
                },
                d.lang.attributeError, !1, {
                    speid: c,
					skuModelId: skuModelId
                })
            })
        })
    },
    f.length > 0 && g(new l);
    var m;
    h.length > 0 && (m = new k);
    var n = b(".js_undertake");
    if (n.length > 0) {
        var o = b(".js_freight_container"),
        p = b(".js_freight_type"),
        q = b(".js_unify_container"),
        r = b(".js_template_container"),
        s = (b(".js_freight_template_loading"), b(".js_freight_template")),
        t = b(".js_freight_template_refresh"),
        u = b(".js_freight_item");
        n.on("click",
        function() {
            o.toggle(b(this).hasClass("js_freight_container_show"))
        }),
        p.on("click",
        function() {
            var a = b(this);
            q.toggle(a.hasClass("js_unify_container_show")),
            r.toggle(a.hasClass("js_template_container_show"))
        }),
        u.on("click",
        function() {
            var a = b(this),
            c = a.closest("dl");
            b('input[type="text"]', c).addAttr("disabled", !a.prop("checked"))
        }),
        t.on("click",
        function(a) {
            a && a.preventDefault();
            var c = b(this),
            e = c.data("remote") || c.attr("href"),
            f = c.find("i");
            f.addClass("fa-spin");
            var g = ' <option value="{0}">{1}</option>';
            s.empty(),
            b.post(e).done(function(a) {
                a.data && (s.append('<option value="">请选择</option>'), b.each(a.data,
                function(a, b) {
                    s.append(g.format(b.id, b.name))
                })),
                f.removeClass("fa-spin"),
                s.focus()
            }).fail(function() {
                f.removeClass("fa-spin"),
                G.ui.tips.suc(d.lang.exception)
            })
        })
    }
    b(document).on("click", ".js_save_submit",
    function() {
        return b(".js_fileList li.imgbox").length < 1 ? (G.ui.tips.suc("至少选择一个商品图片"), !1) : void 0
	   return void 0;
    }),
    b(document).on("click", ".js_save_submit",
    function() {
		var is_package = $('#is_package').val();
		if (is_package == 1) return void 0;
        var a = b('[data-toggle="specifications"]'),
        c = a.find("input[type='checkbox']:checked").length;
        return ! a.is(":hidden") && 1 > c ? (G.ui.tips.suc("至少选择一个商品规格"), !1) : void 0
    });
    var v = b(".js_quota");
    v.on("click",
    function() {
        var a = b(this).hasClass("js_quota_show"),
        c = b(".js_quota_container");
        c.toggleClass("hd", !a).toggleClass("inline", a)
    }),
    b(document).on("click", ".js_submit",
    function() {
        b(this).closest("form").submit()
    }),
    b(document).on("keypress",
    function(a) {
		if (b(".js_spe_speval").length < 1) return true;
        var a = a || event,
        c = a.keyCode || a.which || a.charCode;
        return 13 == c ? (b(".js_spe_speval").closest(".js_enter_div").hasClass("hide") || b(".js_spe_speval").trigger("click"), b(".js_add_speval").each(function() {
            b(this).closest(".js_enter_div").hasClass("hide") || b(this).trigger("click")
        }), !1) : void 0
    }),
    b(document).on("click", ".js_save_submit",
    function() {
        var a = b(this),
        c = b(this).data("confirm"),
        e = a.data("confirmMsg");
        if (c) {
            var f = b("#js_store_way"),
            g = b(".js_way_select:checked").val();
            if (0 == g && f.val() != g) return  G.ui.tips.confirm_flag(a.data("confirmText") || "确定修改",
             	function(b) {
                   a.submit()
                }
            ),
            !1
        }
    }),
    b(document).on("click", ".js_search_add_submit",
    function() {
        var a = b(this).closest("form"),
        c = a.attr("action"),
        d = b(".talbe-search").serialize();
        a.attr("action", c.format(d))
    }),
    b(document).on("click", ".js_edit_search",
    function() {
        var a = b(this),
        c = a.attr("href"),
        d = b(".talbe-search").serialize();
        a.attr("href", c.format(d))
    })
});