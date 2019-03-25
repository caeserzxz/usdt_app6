
define("dist/application/app", ["$", "./setting",  "./method", "./extend", "./element", "./plugins", "./template"],
function(a) {	
    var b = a("$"),
    c = a("./setting"),
    d = a("./method");
    return a("./extend"),
    a("./element"),
    a("./plugins"),
    window.template = a("./template"),
    b.ajaxSetup({
        cache: !1,
        dataType: "json"
    }),
    b("form.form-validate").length > 0 && a.async("dist/form/init",
    function(a) {
        var c = b("form.form-validate");
        c.length && a.init(c)
    }),
    b("table").length > 0 && a.async("dist/table/init"),
    {
        config: c,
        method: d,
        v: "1.0.1"
    }
}),
define("dist/application/setting", ["$"],
function(a, b) {
    "use strict";
    var c = (a("$")),
    b = {
        kindeditor: {
            "default": {
                items: ["source", "|", "undo", "redo", "|", "preview", "print", "template", "cut", "copy", "paste", "plainpaste", "wordpaste", "|", "justifyleft", "justifycenter", "justifyright", "justifyfull", "insertorderedlist", "insertunorderedlist", "indent", "outdent", "subscript", "superscript", "clearhtml", "quickformat", "selectall", "|", "fullscreen", "/", "formatblock", "fontname", "fontsize", "|", "forecolor", "hilitecolor", "bold", "italic", "underline", "strikethrough", "lineheight", "removeformat", "|", "image", "multiimage", "insertfile", "table", "hr", "emoticons", "baidumap", "code", "pagebreak", "link", "unlink"],
                height: "400px",
                width: "100%",
                afterCreate: function() {
                    this.sync()
                },
                afterBlur: function() {
                    this.sync()
                },
				allowFileManager: true,
                extraFileUploadParams: window.extraFileUploadParams
            },
            simple: {
                items: ["source", "undo", "redo", "plainpaste", "plainpaste", "wordpaste", "clearhtml", "quickformat", "selectall", "fullscreen", "fontname", "fontsize", "|", "forecolor", "hilitecolor", "bold", "italic", "underline", "hr", "removeformat", "|", "justifyleft", "justifycenter", "justifyright", "insertorderedlist", "insertunorderedlist", "|", "emoticons", "image", "link", "unlink", "preview"],
                height: "300px",
                width: "100%",
                afterCreate: function() {
                    this.sync()
                },
                afterBlur: function() {
                    this.sync()
                }
            },
            mini: {
                items: ["fontsize", "forecolor", "hilitecolor", "bold", "italic", "removeformat", "justifyleft", "justifycenter", "justifyright", "insertorderedlist", "insertunorderedlist", "link", "unlink"],
                height: "300px",
                width: "100%",
                afterCreate: function() {
                    this.sync()
                },
                afterBlur: function() {
                    this.sync()
                }
            }
        },
        loading: '<div  class="load"></div>',
        issucceed: function(a) {
            return 1 == a.code
        },
        empty: "",
        lang: {
            saveSuccess: "保存成功",
            modifySuccess: "修改成功",
            saveError: "保存失败",
            modifyError: "修改失败",
            exception: "网络异常 请重试",
            confirmRemove: "确定删除 ?",
            confirmAllremove: "确定删除选定 ?",
            removeSuccess: "删除成功",
            removeError: "删除失败",
            attributeError: "获取商品属性出错,请重试",
            specificationsError: "获取商品规格出错,请重试",
            confirmPost: "确定提交",
            confirmChangedelivery: "切换计价方式后,所设置当前模版的运费信息将被清空,确认继续",
            confirmChangetemplet: "你要载入新模版吗？当前数据将被清空",
            shelvesSuccess: "修改成功",
            shelvesError: "修改失败 请重试",
            confirmRefund: "<p><b>确认退款</b></p><p>确认后钱将直接通过原支付方式返回，无法撤销，请再次确认。</p>",
            confirmRefundGoods: "<p><b>同意退款退货</b></p><p>退货地址：加载中...</p>",
            confirmCloseOrder: "确定关闭订单,关闭订单后将无法改价或发货",
            widgetError: "部件添加失败,请重试",
            pageInfoError: "部件添加失败,请重试",
            templateConfigLoadError: "模版配置加载失败,请重试",
            maxNavLength: "最多添加5个导航",
            maxFnLength: "最多添加4个导航",
            minOneItem: "至少保留一个",
            maxItem10: "最多添加10个"
        },
        statistics: {},
        domain: {
            www: window.location.protocol + "//" + window.location.host,
            "static": window.location.protocol + "//" + window.location.host
        }
    };
    return window.wm = b,
    b.msg = window.top != window.self ? window.parent.msg: c,
    b
}),
define("dist/application/method", ["$", "dist/application/setting"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("/assets/javascripts/dist/application/setting"),
    d = function(a, d, e, f, g) {
        b.ajax(a, {
            type: f || "post",
            data: g,
            dataType: "json"
        }).done(function(a) {
            d && d(a)
        }).fail(function() {
            G.ui.tips.info(e || c.lang.exception)
        })
    };
    return {
        get: function(a, b, c) {
            d(a, b, c, "get")
        },
        post: function(a, b, c, e, f) {
            d(a, b, c, e, f)
        },
        postd: function(a, b, c) {
            d(a, b, !1, !1, c)
        },
        OpenWindowWithPost: function(a, b, c, d) {
            var e = document.createElement("form");
            e.setAttribute("method", d),
            e.setAttribute("action", a),
            e.setAttribute("target", b);
            for (var f in c) {
                var g = document.createElement("input");
                g.type = "hidden",
                g.name = c[f].name,
                g.value = c[f].value,
                e.appendChild(g)
            }
            document.body.appendChild(e),
            e.submit(),
            document.body.removeChild(e)
        },
        select: function(a) {
            function d(a, c) {
                var g = b(a).data("selected");
                g && (c = g);
                var k = h.length ? h[h.length - 1].key + "," + h[h.length - 1].value: j.root;
                h.push({
                    element: a,
                    key: k,
                    value: c
                });
                var l = 0;
                for (var m in i) l++;
                for (var n in h) if (h[n].element == a) var o = parseInt(n);
                for (var n in h) o > n && h[n].element.change(function() {
                    e(a)
                });
                o > 0 && h[o - 1].element.change(function() {
                    e(a, h[o].key)
                }),
                a.change(function() {
                    var c = h[o - 1] ? h[o].key + "," + b(this).val() : "0," + b(this).val();
                    "undefined" != typeof h[o + 1] && (h[o + 1].key = c),
                    j.field_name && b(j.field_name).val(b(this).val()),
                    j.auto && "undefined" == typeof h[o + 1] && f(c,
                    function(c, f) {
                        if (f) {
                            var g = b("<select></select>");
                            a.after(g),
                            d(g, ""),
                            e(h[o + 1].element, c, f)
                        }
                    })
                }),
                e(a, k, c)
            }
            function e(a, c, d) {
                var g, h, i, k, l, m, n;
                if (a.empty(), a.append('<option value="">{0}</option>'.format(j.default_text)), g = f(c,
                function() {
                    e(a, c, d)
                }), !g) return j.auto && a.hide(),
                !1;
                a.show();
                var o = new Array;
                for (var p in g) o.push(p);
                0 == o.length && a.hide(),
                h = 1,
                i = 0;
                for (k in g) g.hasOwnProperty(g[k]) || (l = g[k], m = "", k == d && (i = h, m = 'selected="selected"'), n = b('<option value="' + k + '" ' + m + ">" + l + "</option>"), a.append(n), h++);
                a[0] && (setTimeout(function() {
                    a[0].options[i].selected = !0
                },
                0), a[0].selectedIndex = 0, a.attr("selectedIndex", i))
            }
            function f(a, c) {
                var d, e;
                if ("undefined" == typeof a || "," == a[a.length - 1]) return null;
                if ("undefined" == typeof i[a]) {
                    d = 0;
                    for (e in i) {
                        d++;
                        break
                    }
                    j.ajax ? b.getJSON(j.ajax, {
                        key: a
                    },
                    function(b) {
                        i[a] = b,
                        c(a, b)
                    }) : j.file && 0 == d && b.getJSON(j.file,
                    function(b) {
                        i = b,
                        c(a, b)
                    })
                }
                return i[a]
            }
            function g(a) {
                return "string" == typeof a ? b(a) : a
            }
            var h = [],
            i = {},
            j = {
                data: {},
                file: null,
                root: "0",
                ajax: null,
                timeout: 30,
                method: "post",
                field_name: null,
                auto: !1,
                default_text: "请选择"
            };
            return a && jQuery.extend(j, a),
            i = j.data,
            {
                bind: function(a, b) {
                    "object" != typeof a && (a = g(a)),
                    b = b ? b: "",
                    d(a, b)
                },
                find: function(a) {
                    if (a.length > 0) var b = i[0][a[0]],
                    d = i["0,{0}".format(a[0])][a[1]],
                    e = i["0,{0},{1}".format(a[0], a[1])][a[2]];
                    return {
                        p: b,
                        c: d,
                        d: e,
                        toString: function() {
                            return e = "undefined" == typeof e ? c.empty: e,
                            b + d + e
                        }
                    }
                }
            }
        }
    }
}),
define("dist/application/extend", [],
function() {
    "use strict";
    String.prototype.format || Object.defineProperty(String.prototype, "format", {
        value: function() {
            var a = arguments;
            return this.replace(/{(\d+)}/g,
            function(b, c) {
                return "undefined" != typeof a[c] ? a[c] : b
            })
        },
        enumerable: !1
    }),
    String.prototype.trim || Object.defineProperty(String.prototype, "trim", {
        value: function() {
            return this.replace(/^\s*/, "").replace(/\s*$/, "")
        },
        enumerable: !1
    }),
    String.prototype.parameters || Object.defineProperty(String.prototype, "parameters", {
        value: function() {
            for (var a = {},
            b = new RegExp("([\\?|&])(.+?)=([^&?]*)", "ig"), c = b.exec(this); c;) a[c[2]] = c[3],
            c = b.exec(this);
            return a
        },
        enumerable: !1
    }),
    String.prototype.stripTags || Object.defineProperty(String.prototype, "stripTags", {
        value: function() {
            return this.replace(/<\/?[^>]+>/gi, "")
        },
        enumerable: !1
    }),
    String.prototype.getNum || Object.defineProperty(String.prototype, "getNum", {
        value: function() {
            return this.replace(/[^d]/g, "")
        },
        enumerable: !1
    }),
    String.prototype.getEn || Object.defineProperty(String.prototype, "getEn", {
        value: function() {
            return this.replace(/[^A-Za-z]/g, "")
        },
        enumerable: !1
    }),
    String.prototype.getCn || Object.defineProperty(String.prototype, "getCn", {
        value: function() {
            return this.replace(/[^u4e00-u9fa5uf900-ufa2d]/g, "")
        },
        enumerable: !1
    }),
    Date.prototype.format || Object.defineProperty(Date.prototype, "getCn", {
        value: function() {
            var a = {
                "M+": this.getMonth() + 1,
                "d+": this.getDate(),
                "h+": this.getHours(),
                "m+": this.getMinutes(),
                "s+": this.getSeconds(),
                "q+": Math.floor((this.getMonth() + 3) / 3),
                S: this.getMilliseconds()
            };
            /(y+)/.test(format) && (format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length)));
            for (var b in a) new RegExp("(" + b + ")").test(format) && (format = format.replace(RegExp.$1, 1 == RegExp.$1.length ? a[b] : ("00" + a[b]).substr(("" + a[b]).length)));
            return format
        },
        enumerable: !1
    }),
    window.StringBuilder = function() {
        this.tmp = new Array
    },
    StringBuilder.prototype.Append = function(a) {
        return this.tmp.push(a),
        this
    },
    StringBuilder.prototype.Clear = function() {
        tmp.length = 1
    },
    StringBuilder.prototype.toString = function() {
        return this.tmp.join("")
    }
}),
define("dist/application/element", ["$", "dist/application/setting",  "dist/application/method"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/setting"),
    e = a("dist/application/method");
    b(document).on("click", '[data-toggle="trigger"]',
    function(a) {
        a && a.preventDefault();
        var c = b(this).data("target").split(":");
        c && b(c[0]).trigger(c[1])
    }),
    b(document).on("click", '[data-toggle="back"]',
    function(a) {
        a && a.preventDefault();
        var c = b(this).data("url");
        c ? window.location = c: window.history.go( - 1)
    }),
    b.fn.extend({
        popoverClosable: function(a) {
            var c = {
                template: '<div class="popover"><div class="arrow"></div><div class="popover-header"><button type="button" class="close m-r-xs m-t-xs" data-dismiss="popover" aria-hidden="true">&times;</button><h3 class="popover-title"></h3></div><div class="popover-content"></div></div>'
            };
            a = b.extend({},
            c, a);
            var d = this;
            d.popover(a),
            d.on("click",
            function(a) {
                a.preventDefault(),
                d.not(this).popover("hide")
            }),
            b(document).on("click", '[data-dismiss="popover"]',
            function() {
                d.popover("hide")
            })
        }
    }),
    b('[data-toggle="popover"]').popoverClosable(),
    b(document).on("click", '[data-toggle^="class"]',
    function(a) {
        "checkbox" != a.target.type && "radio" != a.target.type && a && a.preventDefault();
        var d, f, g, h, i, j = b(a.target); ! j.data("toggle") && (j = j.closest('[data-toggle^="class"]')),
        d = j.data().toggle,
        f = j.data("target") || j.attr("href"),
        d && (g = d.split(":")[1]) && (h = g.split(",")),
        f && (i = f.split(",")),
        i && i.length && b.each(i,
        function(a) {
            "#" != i[a] && b(i[a]).toggleClass(h[a])
        }),
        j.toggleClass("active");
        var k = b(this),
        l = k.data("ajax"),
        m = k.data("fun"),
        n = k.data("remote") || k.attr("href"),
        o = null;
        if (m) switch (m) {
        case "shelves":
            o = function(a) {
                c.issucceed(a) ? G.ui.tips.suc(a.message || c.lang.shelvesSuccess, a.url) : G.ui.tips.info(a.message || c.lang.shelvesError, a.url)
            }
        }
        l && e.post(n, o, null, null, {
            toggle: k.hasClass("active")
        })
    }),
    b(document).on("click", '[data-toggle="ajaxModal"]',
    function(c) {
		
        var d = b(this);
        b("#ajaxModal").remove(),
        c.preventDefault();
        var d = b(this),
        e = d.data("remote") || d.attr("href"),
        f = d.data("set"),
		sf = d.data("setfid"),
		fn = d.data("fun"),
        g = b('<div class="modal fade" id="ajaxModal"><div class="modal-body "></div></div>');
		if (sf){
			f = typeof(f) == 'undefined' ? 'id='+$('#'+sf).val() : f+'&id='+$('#'+sf).val();
		}
        b(document).append(g),
        g.modal(),
        b.ajax(e, {
            type: "get",
            dataType: "html",
            data: typeof(f) == 'undefined' ? 'ishtml=1' : f+'&ishtml=0'
        }).done(function(res) {
			if(res.indexOf('{"code"')==-1) {
				var c = res;
			}else{
				$('#ajaxModal').trigger('click');
				var obj=JSON.parse(res);
				_alert(obj.msg);
				return false;
			}
            g.append2(c,
            function() {
                var c = b("form.form-validate", g);
                c.length > 0 && a.async("dist/form/init",
                function(a) {
                    b("button[type='submit']", g).length && b("button[type='submit']", g).removeAttr("disabled"),
                    a.init(c)
                }),
                d.trigger("init", g);
                region_sel();
				if (fn) eval(fn+'()');
            })
        })
    }),
	 b(document).on("click", '[data-toggle="divModal"]',
    function(c) {
		
        var d = b(this);
        b("#ajaxModal").remove(),
        c.preventDefault();
        var d = b(this),
        e = d.data("remote") || d.attr("href"),
        f = d.data("set"),
		fn = d.data("fun"),
		data = d.data(),
        g = b('<div class="modal fade" id="ajaxModal"><div class="modal-body "></div></div>');
        b(document).append(g),
        g.modal(),
        g.append2(template(e,data),
            function() {
                var c = b("form.form-validate", g);
                c.length > 0 && a.async("dist/form/init",
                function(a) {
                    b("button[type='submit']", g).length && b("button[type='submit']", g).removeAttr("disabled"),
                    a.init(c)
                }),
                d.trigger("init", g);				
				if (fn) eval(fn+'(data)');
         })
    }),
    b(document).on("click", '[data-toggle="iframeModal"]',
            function(c) {
                var d = b(this);
                b("#iframeModal").remove(),
                    c.preventDefault();
                var d = b(this),
                    url = d.data("url"),
                    g = b('<div class="modal fade" id="ajaxModal"><div class="modal-body "></div></div>');
                b(document).append(g),
                g.modal(),
                g.append2('<div class="modal-dialog"><div class="modal-content" style="min-width: 700px;">' +
                    '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                    '<h4 class="modal-title"><i class="icon-table"></i>选择链接</h4></div> ' +
                    '<div class="modal-body"><iframe src="'+url+'" style="width:100%; height:100%;min-height: 300px; border:0px;"></iframe></div>' +
                    '</div></div>',function(){});


     }),
	b(document).on("click", '[data-toggle="ajaxEditInput"]',
    function(c) {
        var d = b(this);
         /* 保存原始的内容 */
		 var val = d.html(),
		     u = d.data("url"),
			 f = d.data("field"),
			tp = d.data("type"),
		  _fmt = d.data("format") || 'Y-m-d H:i';
		
		 if(val.indexOf("input ") > 0) return false;
		 var txt = document.createElement("input");
		 txt.value = (val == 'N/A') ? '' : val;
		 txt.style = "width:100%;text-align:center;" ;
		 /* 隐藏对象中的内容，并将输入框加入到对象中 */
		 d.html('');
		 d.append(txt);
		 if (tp == 'datetime'){
			var a = d.find('input');
			a.datetimepicker({
				lang:'ch',
				format:_fmt
            }).on("show",
            function() {
                if (i && b(i).val()) {
                    var c = new Date(b(i).val());
                    c.setDate(c.getDate() + k),
                    a.datetimepicker("setStartDate", c)
                }
                if (j && b(j).val()) {
                    var c = new Date(b(j).val());
                    c.setDate(c.getDate() + k),
                    a.datetimepicker("setEndDate", c)
                }
            }); 
		 }
		 
		  txt.focus();
		 
		 /* 编辑区失去焦点的处理函数 */
		 txt.onblur = function(e){
			var _val = d.find('input').val();
			d.html(_val);
			if (_val == val) return false;
			$.post(u,'field='+f+'&value='+_val,function(res){				
				if (res.code != 0){					
					d.html(val);
					return false;
				}else{
					G.ui.tips.suc(res.msg);	
				}
			 });
		 }
    }),
    b(document).on("click.dropdown-menu", ".dropdown-select > .js_custom_list",
    function(a) {
        return a.preventDefault(),
        !1
    }),
    b(document).on("click.dropdown-menu", ".dropdown-select > li > a",
    function(a) {
        a.preventDefault();
        var c, d, e, f, g, h, i = b(a.target),
        j = !1;
		page_size = i.find("input").val();
        if (!i.is("a") && (i = i.closest("a")), d = i.closest(".dropdown-menu"), e = d.parent().find(".dropdown-label"), f = e.text(), c = i.find("input"), j = c.is(":checked"), !(c.is(":disabled") || "radio" == c.attr("type") && j)) {
            "radio" == c.attr("type") && d.find("li").removeClass("active"),
            i.parent().removeClass("active"),
            !j && i.parent().addClass("active"),
            c.prop("checked", !c.prop("checked")),
            g = d.find("li > a > input:checked"),
            g.length ? (h = [], g.each(function() {
                var a = b(this).parent().text();
                a && h.push(b.trim(a))
            }), h = h.length < 6 ? h.join(", ") : " 选中" + h.length + "项", e.html(h)) : e.html(e.data("placeholder")),
            c.trigger("change", [c.val()]);
			
            var k = d.data("change");
            if (k) switch (k) {
            case "submit":
                d.closest("form ").submit()
            }
        }
    }),
    b(document).on("click", 'table [data-toggle="removeRow"]',
    function() {
        var a = b(this).closest("tr");
        a.fadeOut(function() {
            a.remove()
        })
    }),
    b(document).on("click", '[data-toggle="ajaxPost"]',
    function(a) {
        a.preventDefault();
        var d = b(this),
        e = d.data("remote") || d.attr("href"),
        f = d.data("confirm"),
        g = d.data("msgtype"),
        h = d.data("set"),
		nu = d.data("noturl"),
		i = d.data("remove");
        f = "undefined" == typeof f ? false : f,
        d.data("loadingText", '<i class="fa fa-spinner fa-spin"></i>');
		
        var j = function() {
            d.html();
            d.button("loading"),
            b.post(e, h).done(function(a) {
                if (d.button("reset"), c.issucceed(a)) {
                    if (i) {
                        var b = d.closest(i);
                        b.fadeOut(function() {
                            b.remove()
                        })
                    }
                    G.ui.tips.suc(a.msg, nu==1?'':a.url)
                } else G.ui.tips.info(a.msg,nu==1?'':a.url)
            }).fail(function() {
                d.button("reset"),
                G.ui.tips.info(c.lang.exception)
            })
        },
        k = function(a) {
            switch (a) {
            case "refund":
                return c.lang.confirmRefund;
            case "refundGoods":
                return c.lang.confirmRefundGoods
            }
        },
        l = g ? k(g) : d.data("msg");
        f && G.ui.tips.confirm_flag({
            message: l || c.lang.confirmPost,
            buttons: {
                confirm: {
                    label: d.data("confirmText") || "确定"
                }
            },
            callback: function(a) {
                a && j()
            }
        }),
        !f && j()
    }),
    b(document).on("click", ".js_remove_dd_item",
    function() {
        var a = b(this),
        c = a.closest(".dd-item");
        c.fadeOut(function() {
            c.remove(),
            0 == b(".dd-item").length && b("#noitems").removeClass("hide")
        })
    }),
    b(document).on("click", '[data-toggle="ajaxRemove"]',
    function(a) {
        a.preventDefault();
        var e = b(this),
        f = e.data("remote") || e.attr("href"),
        g = e.data("confirm");
        g = "undefined" == typeof g ? !0 : g;
        var h = function() {
            var a = e.html();
            e.html('<i class="fa fa-spinner fa-spin"></i>'),
            b.post(f).done(function(d) {
                if (c.issucceed(d)) {
                    G.ui.tips.suc(d.msg || c.lang.removeSuccess, d.url);
                    var f = e.parents("li").first();
                    f.find(".dd-handle").css({
                        "background-color": "#dff0d8"
                    }),
                    f.fadeOut(function() {
                        f.remove(),
                        0 == b(".dd-item").length && $noClassify.removeClass("hide")
                    })
                } else e.html(a),
                G.ui.tips.info(d.msg || c.lang.removeError, d.url)
            }).fail(function() {
                e.html(a),
                G.ui.tips.info(c.lang.exception, d.url)
            })
        };
        g && G.ui.tips.confirm_flag(e.data("msg") || c.lang.confirmRemove,
        function(a) {
			$('#fallr-wrapper').remove();
			$.post(f,'',function(res){
				G.ui.tips.suc(res.msg,res.code == 1?'reload':'');	
			 });
        }),
        !g && h()
    }),
	b(document).on("click", '[data-toggle="cfmAjax"]',
    function(a) {
        a.preventDefault();
        var e = b(this),
        f = e.attr("href"),
        g = e.data("confirm"),
		fn = e.data("fun"),
		icon = e.data("icon"),
		title = e.attr("title");
        g = "undefined" == typeof g ? !0 : g;
       
        g && G.ui.tips.confirm_flag(g?g:title,
        function(a) {
			$('#fallr-wrapper').remove();
			$.post(f,'',function(res){
			    if (res.data.alert == 1){
                    G.ui.tips.info(res.msg,res.url);
                }else{
                    G.ui.tips.suc(res.msg,res.url);
                }
				if (fn) eval(fn+'(res)');
			 });
        },0,icon)
    }),
	 b(document).on("click", '[data-toggle="dropdown"]',
    function() {
	   if (!$(this).parent().hasClass("light-blue")) return false;
	   if ($(this).parent().hasClass("open")){
		   return $(this).parent().removeClass("open");
	   }
       $(this).parent().addClass("open");
    })
}),
define("dist/application/plugins", ["$", "dist/application/setting"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/setting");
    b("[data-toggle=tooltip]").tooltip(),
    b(function() {
        var c = b(".no-touch .slim-scroll");
        c.length > 0 && a.async(["slimscroll"],
        function() {
            c.each(function() {
                var a, c = b(this),
                d = c.data();
                c.slimScroll(d),
                b(window).resize(function() {
                    clearTimeout(a),
                    a = setTimeout(function() {
                        c.slimScroll(d)
                    },
                    500)
                })
            })
        })
    });
    var d = b('[data-toggle="chosen"]');
    d.length > 0 && a.async(["chosen"],
    function() {
        d.each(function() {
            var a = b(this),
            d = a.data("nosearch"),
            e = {},
            f = a.data("maxlength"),
            g = a.data("ruleRequired"),
            h = a.data("msgRequired") || a.data("placeholder"),
            i = a.data("remote") || a.attr("href");
            if (f && (e.max_selected_options = f), d && (e.disable_search_threshold = 9999999), e.hide = !1, e.no_results_text = "找不到", console.log(g), g) {
                var j = b(this).closest("form");
                b(j).on("click", 'button[type="submit"]',
                function() {
                    var b = a.val();
                    return b ? void 0 : (G.ui.tips.info(h || "请输入必填项"), !1)
                })
            }
            i ? (console.log(i), b.ajax(i, {
                type: "post",
                dataType: "json"
            }).done(function(c) {
                b.each(c,
                function(b, c) {
                    var d = '<option value="{1}">{0}</option>';
                    a.append(d.format(c.text, c.value))
                }),
                a.chosen(e)
            }).fail(function() {
                G.ui.tips.info("网络异常")
            })) : a.chosen(e)
        })
    });
    var e = b('[data-toggle="select2"]');
    e.length > 0 && a.async(["select2"],
    function() {
		
        e.each(function() {
            var a = b(this),
            d = {},
            e = a.data("maxcount"),
            f = a.data("chang"),
            g = (a.data("remote") || a.attr("href"), a.data("ruleRequired")),
            h = a.data("msgRequired") || a.data("placeholder"),
			fd = a.data("notsearch");
			if (fd == true)  d = {minimumResultsForSearch: -1};
            if (e && (d.maximumSelectionSize = e), g) {
                var i = b(this).closest("form");
                b(i).on("click", 'button[type="submit"]',
                function() {
                    var b = a.val();
                    return b ? void 0 : (G.ui.tips.info(h || "请输入必填项"), !1)
                })
            }
            if (f) switch (f) {
            case "submit":
                a.on("change",
                function() {
                    a.closest("form ").submit()
                })
            }
            a.select2(d)
        })
    });
    var f = b('[data-toggle="daterangepicker"]');
    f.length > 0 && a.async(["moment", "daterangepicker"],
    function() {
        f.each(function() {
            var a = b(this);
            a.daterangepicker({
                timePicker: !0,
                format: "YYYY/MM/DD HH:mm"
            });
            var c = a.next("span.add-on"),
            d = a.prev("span.add-on");
            c.add(d).on("click",
            function() {
                a.trigger("click")
            })
        })
    });
    var g = b('[data-toggle="datetimepicker"]');
    g.length > 0 && a.async(["moment", "datetimepicker", assets_path+"/main/css/stylesheets/datetimepicker/datetimepicker.min.css"],
    function() {
        g.each(function() {
            var a = b(this),
            c = a.data("minutestep") || 5,
            d = a.data("position") || "bottom-left",
            e = "",
            f = a.data("startdate"),
            g = "",
            h = a.data("enddate"),
            i = a.data("after"),
            j = a.data("before"),
            k = a.data("offsetday") - 0 || 0;
            "now" == f ? (e = new Date, e.setDate(e.getDate() + k)) : (e = new Date(f), e.setDate(e.getDate() + k)),
            "now" == h ? (g = new Date, g.setDate(g.getDate())) : (g = new Date(h), g.setDate(g.getDate())),
            a.datetimepicker({
                language: "zh-CN",
                startDate: e,
                endDate: g,
                autoclose: !0,
                minuteStep: c,
                pickerPosition: d
            }).on("show",
            function() {
                if (i && b(i).val()) {
                    var c = new Date(b(i).val());
                    c.setDate(c.getDate() + k),
                    a.datetimepicker("setStartDate", c)
                }
                if (j && b(j).val()) {
                    var c = new Date(b(j).val());
                    c.setDate(c.getDate() + k),
                    a.datetimepicker("setEndDate", c)
                }
            });
            var l = a.next("span.input-group-addon"),
            m = a.prev("span.input-group-addon");
            l.add(m).on("click",
            function() {
                a.datetimepicker("show")
            })
        })
    });
    var h = b('[data-toggle="reportrange"]');
    h.length > 0 && a.async(["moment", "daterangepicker"],
    function() {
        h.each(function() {
            var a = b(this);
			a.find('span').html($('#reportrange').val());//add by iqgmy
            var c = (a.data("selected"), a.find("span")),
            d = c.text().split("-"),
            e = moment(d[0]),
            f = moment(d[1]),
            g = f.diff(e, "days");
			
            a.daterangepicker({
                format: "YYYY/MM/DD HH:mm",
                ranges: {
                    "今天": [moment(), moment()],
                    "昨天": [moment().subtract("days", 1), moment().subtract("days", 1)],
                    "最近7天": [moment().subtract("days", 6), moment()],
                    "最近30天": [moment().subtract("days", 29), moment()],
                    "这个月": [moment().startOf("month"), moment().endOf("month")],
                    "上个月": [moment().subtract("month", 1).startOf("month"), moment().subtract("month", 1).endOf("month")]
                },
                startDate: e || moment().subtract("days", g),
                endDate: f || moment()
            },
            function(c, d) {
                var e = c.format("YYYY/MM/DD") + " - " + d.format("YYYY/MM/DD");
                b("span", a).html(e),
                b('input[type="hidden"]', a).val(e);
                var f = a.data("change");
                if (f) switch (f) {
                case "submit":
                    a.closest("form ").submit()
                }
            })
        })
    }),
    b.fn.addAttr = function(a, c) {
        return this.each(function() {
            var d = b(this);
            c ? d.attr(a, a) : d.removeAttr(a)
        })
    },
    b.fn.adddClass = function(a, c) {
        return this.each(function() {
            var d = b(this);
            c && d.addClass(a)
        })
    },
    !
    function(b) {
        var d = function(a, b, c) {
            this.element = c.get(0),
            this.defaults_file_type = b,
            this.init(a)
        };
        d.defaults = {
            url: c.empty,
            multi: !1,
            prevent_duplicates: !1,
            mime_types: [],
            max_file_size: 0,
            data: {},
            max_count: 0,
            FilesAdded: function() {},
            UploadProgress: function() {},
            FileUploaded: function() {},
            UploadComplete: function() {}
        },
        d.prototype = {
            init: function(a) {
                var e = this;
                if (this.defaults_file_type) {
                    switch (this.defaults_file_type) {
                    case "picture":
                        d.defaults.max_file_size = "2mb",
                        d.defaults.mime_types = [{
                            title: "图片文件",
                            extensions: "bmp,png,jpeg,jpg,gif"
                        }],
                        plupload.addI18n({
                            "File extension error.": "图片格式必须为以下格式：bmp, jpeg, jpg, gif",
                            "File size error.": "文件大小错误。"
                        });
                        break;
                    case "voice":
                        d.defaults.max_file_size = "5mb",
                        d.defaults.mime_types = [{
                            title: "音频文件",
                            extensions: "mp3,wma,wav,amr"
                        }],
                        plupload.addI18n({
                            "File extension error.": "语音格式必须为以下格式：mp3, wma, wav, amr"
                        });
                        break;
                    case "video":
                        d.defaults.max_file_size = "20mb",
                        d.defaults.mime_types = [{
                            title: "视频文件",
                            extensions: "rm,rmvb,wmv,avi,mpg,mpeg,mp4"
                        }],
                        plupload.addI18n({
                            "File extension error.": "视频格式必须为以下格式：rm, rmvb, wmv, avi, mpg, mpeg, mp4"
                        });
                        break;
                    case "file":
                        d.defaults.max_file_size = "10mb",
                        d.defaults.mime_types = [{
                            title: "自定义文件",
                            extensions: "txt,xml,pdf,zip,doc,ppt,xls,docx,pptx,xlsx"
                        }],
                        plupload.addI18n({
                            "File extension error.": "文件格式必须为以下格式：txt, xml, pdf, zip, doc, ppt, xls, docx, pptx, xlsx"
                        })
                    }
                    plupload.addI18n({
                        "File size error.": "最大只能上传{0}的文件".format(d.defaults.max_file_size)
                    })
                }
                this.options = b.extend(!0, {},
                d.defaults, a),
                this.plupload = new plupload.Uploader({
                    runtimes: "html5,flash,silverlight,html4",
                    browse_button: e.element,
                    url: e.options.url,
                    flash_swf_url: "/assets/swf/Moxie.swf",
                    silverlight_xap_url: "/assets/swf/Moxie.xap",
                    multi_selection: e.options.multi,
                    filters: {
                        max_file_size: e.options.max_file_size,
                        prevent_duplicates: e.options.prevent_duplicates,
                        mime_types: e.options.mime_types
                    },
                    multipart_params: e.options.data,
                    init: {
                        FilesAdded: function(a, b) {
                            return e.options.max_count && a.files.length > e.options.max_count ? (G.ui.tips.info("最多上传{0}个文件".format(e.options.max_count)), plupload.each(b,
                            function(b) {
                                a.removeFile(b)
                            }), !1) : (e.options.FilesAdded(a, b), void a.start())
                        },
                        UploadProgress: function(a, b) {
                            e.options.UploadProgress(a, b)
                        },
                        UploadComplete: function(a, b) {
                            e.options.UploadComplete(a, b)
                        },
                        FileUploaded: function(a, c, d) {
                            e.options.FileUploaded(a, c, b.parseJSON(d.response))
                        },
                        Error: function(a, b) {
                            return G.ui.tips.info(b.message),
                            !1
                        }
                    }
                }),
                this.plupload.init()
            },
            update: function(a) {
                b.isPlainObject(a) && (this.options = b.extend(!0, this.options, a))
            },
            destroy: function() {
                this.plupload.destroy(),
                this.element.removeData("uploader")
            }
        },
        b.fn.uploader = function(c, e) {
            var f = this;
            a.async(["plupload"],
            function() {
                var a = typeof c;
                if ("string" === a) {
                    var g = Array.prototype.slice.call(arguments, 1);
                    f.each(function() {
                        var a = b.data(f, "uploader");
                        return a && b.isFunction(a[c]) && "_" !== c.charAt(0) ? void a[c].apply(a, g) : !1
                    })
                } else f.each(function() {
                    var a = b.data(f, "uploader");
                    a ? a.update(c) : (a = new d(c, e, f), b.data(f, "uploader", a))
                })
            })
        }
    } (jQuery),
    b.fn.append2 = function(a, c) {
        var d = b("body").html().length;
        this.append(a);
        var e = 1,
        f = setInterval(function() {
            e++;
            var a = function() {
                clearInterval(f),
                c()
            },
            g = d != b("body").html().length || e > 1e3;
            g && a()
        },
        1)
    }
}),
!
function() {
    function a(a) {
        return a.replace(t, "").replace(u, ",").replace(v, "").replace(w, "").replace(x, "").split(/^$|,+/)
    }
    function b(a) {
        return "'" + a.replace(/('|\\)/g, "\\$1").replace(/\r/g, "\\r").replace(/\n/g, "\\n") + "'"
    }
    function c(c, d) {
        function e(a) {
            return m += a.split(/\n/).length - 1,
            k && (a = a.replace(/[\n\r\t\s]+/g, " ").replace(/<!--.*?-->/g, "")),
            a && (a = s[1] + b(a) + s[2] + "\n"),
            a
        }
        function f(b) {
            var c = m;
            if (j ? b = j(b, d) : g && (b = b.replace(/\n/g,
            function() {
                return m++,
                "$line=" + m + ";"
            })), 0 === b.indexOf("=")) {
                var e = l && !/^=[=#]/.test(b);
                if (b = b.replace(/^=[=#]?|[\s;]*$/g, ""), e) {
                    var f = b.replace(/\s*\([^\)]+\)/, "");
                    n[f] || /^(include|print)$/.test(f) || (b = "$escape(" + b + ")")
                } else b = "$string(" + b + ")";
                b = s[1] + b + s[2]
            }
            return g && (b = "$line=" + c + ";" + b),
            r(a(b),
            function(a) {
                if (a && !p[a]) {
                    var b;
                    b = "print" === a ? u: "include" === a ? v: n[a] ? "$utils." + a: o[a] ? "$helpers." + a: "$data." + a,
                    w += a + "=" + b + ",",
                    p[a] = !0
                }
            }),
            b + "\n"
        }
        var g = d.debug,
        h = d.openTag,
        i = d.closeTag,
        j = d.parser,
        k = d.compress,
        l = d.escape,
        m = 1,
        p = {
            $data: 1,
            $filename: 1,
            $utils: 1,
            $helpers: 1,
            $out: 1,
            $line: 1
        },
        q = "".trim,
        s = q ? ["$out='';", "$out+=", ";", "$out"] : ["$out=[];", "$out.push(", ");", "$out.join('')"],
        t = q ? "$out+=text;return $out;": "$out.push(text);",
        u = "function(){var text=''.concat.apply('',arguments);" + t + "}",
        v = "function(filename,data){data=data||$data;var text=$utils.$include(filename,data,$filename);" + t + "}",
        w = "'use strict';var $utils=this,$helpers=$utils.$helpers," + (g ? "$line=0,": ""),
        x = s[0],
        y = "return new String(" + s[3] + ");";
        r(c.split(h),
        function(a) {
            a = a.split(i);
            var b = a[0],
            c = a[1];
            1 === a.length ? x += e(b) : (x += f(b), c && (x += e(c)))
        });
        var z = w + x + y;
        g && (z = "try{" + z + "}catch(e){throw {filename:$filename,name:'Render Error',message:e.message,line:$line,source:" + b(c) + ".split(/\\n/)[$line-1].replace(/^[\\s\\t]+/,'')};}");
        try {
            var A = new Function("$data", "$filename", z);
            return A.prototype = n,
            A
        } catch(B) {
            throw B.temp = "function anonymous($data,$filename) {" + z + "}",
            B
        }
    }
    var d = function(a, b) {
        return "string" == typeof b ? q(b, {
            filename: a
        }) : g(a, b)
    };
    d.version = "3.0.0",
    d.config = function(a, b) {
        e[a] = b
    };
    var e = d.defaults = {
        openTag: "<%",
        closeTag: "%>",
        escape: !0,
        cache: !0,
        compress: !1,
        parser: null
    },
    f = d.cache = {};
    d.render = function(a, b) {
        return q(a, b)
    };
    var g = d.renderFile = function(a, b) {
        var c = d.get(a) || p({
            filename: a,
            name: "Render Error",
            message: "Template not found"
        });
        return b ? c(b) : c
    };
    d.get = function(a) {
        var b;
        if (f[a]) b = f[a];
        else if ("object" == typeof document) {
            var c = document.getElementById(a);
            if (c) {
                var d = (c.value || c.innerHTML).replace(/^\s*|\s*$/g, "");
                b = q(d, {
                    filename: a
                })
            }
        }
        return b
    };
    var h = function(a, b) {
        return "string" != typeof a && (b = typeof a, "number" === b ? a += "": a = "function" === b ? h(a.call(a)) : ""),
        a
    },
    i = {
        "<": "&#60;",
        ">": "&#62;",
        '"': "&#34;",
        "'": "&#39;",
        "&": "&#38;"
    },
    j = function(a) {
        return i[a]
    },
    k = function(a) {
        return h(a).replace(/&(?![\w#]+;)|[<>"']/g, j)
    },
    l = Array.isArray ||
    function(a) {
        return "[object Array]" === {}.toString.call(a)
    },
    m = function(a, b) {
        var c, d;
        if (l(a)) for (c = 0, d = a.length; d > c; c++) b.call(a, a[c], c, a);
        else for (c in a) b.call(a, a[c], c)
    },
    n = d.utils = {
        $helpers: {},
        $include: g,
        $string: h,
        $escape: k,
        $each: m
    };
    d.helper = function(a, b) {
        o[a] = b
    };
    var o = d.helpers = n.$helpers;
    d.onerror = function(a) {
        var b = "Template Error\n\n";
        for (var c in a) b += "<" + c + ">\n" + a[c] + "\n\n";
        "object" == typeof console && console.error(b)
    };
    var p = function(a) {
        return d.onerror(a),
        function() {
            return "{Template Error}"
        }
    },
    q = d.compile = function(a, b) {
        function d(c) {
            try {
                return new i(c, h) + ""
            } catch(d) {
                return b.debug ? p(d)() : (b.debug = !0, q(a, b)(c))
            }
        }
        b = b || {};
        for (var g in e) void 0 === b[g] && (b[g] = e[g]);
        var h = b.filename;
        try {
            var i = c(a, b)
        } catch(j) {
            return j.filename = h || "anonymous",
            j.name = "Syntax Error",
            p(j)
        }
        return d.prototype = i.prototype,
        d.toString = function() {
            return i.toString()
        },
        h && b.cache && (f[h] = d),
        d
    },
    r = n.$each,
    s = "break,case,catch,continue,debugger,default,delete,do,else,false,finally,for,function,if,in,instanceof,new,null,return,switch,this,throw,true,try,typeof,var,void,while,with,abstract,boolean,byte,char,class,const,double,enum,export,extends,final,float,goto,implements,import,int,interface,long,native,package,private,protected,public,short,static,super,synchronized,throws,transient,volatile,arguments,let,yield,undefined",
    t = /\/\*[\w\W]*?\*\/|\/\/[^\n]*\n|\/\/[^\n]*$|"(?:[^"\\]|\\[\w\W])*"|'(?:[^'\\]|\\[\w\W])*'|[\s\t\n]*\.[\s\t\n]*[$\w\.]+/g,
    u = /[^\w$]+/g,
    v = new RegExp(["\\b" + s.replace(/,/g, "\\b|\\b") + "\\b"].join("|"), "g"),
    w = /^\d[^,]*|,\d[^,]*/g,
    x = /^,+|,+$/g;
    e.openTag = "{{",
    e.closeTag = "}}";
    var y = function(a, b) {
        var c = b.split(":"),
        d = c.shift(),
        e = c.join(":") || "";
        return e && (e = ", " + e),
        "$helpers." + d + "(" + a + e + ")"
    };
    e.parser = function(a, b) {
        a = a.replace(/^\s/, "");
        var c = a.split(" "),
        e = c.shift(),
        f = c.join(" ");
        switch (e) {
        case "if":
            a = "if(" + f + "){";
            break;
        case "else":
            c = "if" === c.shift() ? " if(" + c.join(" ") + ")": "",
            a = "}else" + c + "{";
            break;
        case "/if":
            a = "}";
            break;
        case "each":
            var g = c[0] || "$data",
            h = c[1] || "as",
            i = c[2] || "$value",
            j = c[3] || "$index",
            k = i + "," + j;
            "as" !== h && (g = "[]"),
            a = "$each(" + g + ",function(" + k + "){";
            break;
        case "/each":
            a = "});";
            break;
        case "echo":
            a = "print(" + f + ");";
            break;
        case "print":
        case "include":
            a = e + "(" + c.join(",") + ");";
            break;
        default:
            if ( - 1 !== f.indexOf("|")) {
                var l = b.escape;
                0 === a.indexOf("#") && (a = a.substr(1), l = !1);
                for (var m = 0,
                n = a.split("|"), o = n.length, p = l ? "$escape": "$string", q = p + "(" + n[m++] + ")"; o > m; m++) q = y(q, n[m]);
                a = "=#" + q
            } else a = d.helpers[e] ? "=#" + e + "(" + c.join(",") + ");": "=" + a
        }
        return a
    },
    "function" == typeof define ? define("dist/application/template", [],
    function() {
        return d
    }) : "undefined" != typeof exports ? module.exports = d: this.template = d
} ();