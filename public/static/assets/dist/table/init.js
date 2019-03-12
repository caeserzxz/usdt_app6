function diy_Paginator(b,pagesTotal,pageCurrent,totalCount)
{	
	var e = b("ul.pagination");
	e.html('');
	if (pagesTotal > -1)$('.footer .text-muted').html('');
	if (pagesTotal > 0)
	{		
		$('.footer .text-muted').html('总共'+totalCount+'条,共'+pagesTotal+'页');		
	}	
	e.length > 0 && e.Paginator({
			totalPages: pagesTotal==-1?e.data("pagesTotal"):pagesTotal,
			currentPage: pageCurrent?pageCurrent:e.data("pageCurrent"),
			page: function(a) {
				 _search_list($('.talbe-search'),a,b)
				return !0
			}
	})
}
function diy_sortable(e,b){
 var j = b("th.th-sortable"), h = b("#sort_by"),i = b("#order_by");		
        if (typeof(i.val()) != 'undefined' && i.val().length > 0) {
            var k = b('[data-sort-name="' + i.val() + '"]'),
            l = "ASC" == h.val();
            k.adddClass("select", l),
            k.append(l ? '<span class="th-sort"><i class="fa fa-sort-down text-active"></i><i class="fa fa-sort-up text"></i>  <i class="fa fa-sort"></i>  </span>': '<span class="th-sort">  <i class="fa fa-sort-down text"></i><i class="fa fa-sort-up text-active"></i>  <i class="fa fa-sort"></i>  </span>')
        }
        j.on("click",
        function() {
            var a = b(this),
            c = a.data("sortName");
            i.val(c);
            var d = a.hasClass("select");
            h.val(d ? "DESC": "ASC");
            _search_list($('.talbe-search'),0,iq_b_diy);
        })
}
var iq_b_diy;
var iq_totalPages;
window.onload = function (){
	if ($('.talbe-search').length > 0){
		var p = location.hash.replace("#", "");  
		if (p >= 1){
			 _search_list($('.talbe-search'),p,iq_b_diy);
			 return false;
		}
	}
}
function _search_list(obj,p,b){	
   $('#list_box').html('');
	var arr = $(obj).toJson();
	arr.p = (p < 1) ? 1 : p ;
	location.hash = "#" + arr.p;
	arr.page_size = page_size;	
	var action = $(obj).attr('action');
	var res = jq_ajax(action,arr);
	if (res.msg) _alert(res.info);
	if (res.code == 0) return false;
	$('#list_box').html(res.data.content);
	iq_totalPages = res.data.page_count;
	diy_sortable(obj,b);
	//if (p >= 1) return false;	
	diy_Paginator(b,res.data.page_count,p,res.data.total_count);
	return false;
}
define(assets_path + "/assets/dist/table/init", ["$", "dist/application/app", "./paginator", "./talbe-search"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/app"),
    d = c.config;
    a("./paginator"),
    a("./talbe-search");
    diy_Paginator(b,-1);
    b(document).on("click", '[data-toggle^="refresh"]',
    function(a) {
        a && a.preventDefault(),
		// b("form.talbe-search").submit()
        window.location.reload()
    });
    var f = b("div.table-responsive"),
    g = b('table tbody tr td:first-child [type="checkbox"]', f),
    h = b('[data-toggle^="batch"]'),
    i = b('[data-trigger="batch"]');
    if (g.length > 0) {
        var j = function() {
            var a = b('table tbody tr td:first-child [type="checkbox"]:checked', f),
            c = a.map(function() {
                return b(this).val()
            }).get().join(",");
            return {
                ckbox_list: a,
                values: c
            }
        };
        h.on("click",
        function() {
            var a = b(this),
            c = b(this).data("href"),
            e = a.html();
            G.ui.tips.confirm_flag(a.data("msg") || d.lang.confirmAllremove,
            function(g) {
                if (g) {
                    a.attr("disabled", "disabled");
                    var i = b('table tbody tr td:first-child [type="checkbox"]:checked', f),
                    j = i.map(function() {
                        return b(this).val()
                    }).get();
                    a.html('<i class="fa fa-spinner fa-spin"></i>'),
                    b.post(c, {
                        ids: j.join(",")
                    }).done(function(c) {
                        if (d.issucceed(c)) {
                            G.ui.tips.suc(c.message || d.lang.removeSuccess, c.url);
                            var g = b.Deferred(),
                            j = function(a) {
                                var c = 0;
                                return i.parents("tr").fadeOut(function() {
                                    b(this).remove(),
                                    c++,
                                    i.length == c && a.resolve()
                                }),
                                a
                            };
                            b.when(j(g)).done(function() {
                                h.addAttr("disabled", 0 == b('table tbody tr td:first-child [type="checkbox"]:checked', f).length),
                                0 == b("table tbody tr", f).length && window.location.reload()
                            })
                        } else G.ui.tips.info(c.message || d.lang.removeError, c.url);
                        a.html(e)
                    }).fail(function() {
                        a.html(e),
                        G.ui.tips.info(d.lang.exception)
                    })
                }
            })
        }),
        i.on("click",
        function() {
            var a = j();
            b(this).data("set", {
                ids: a.values
            })
        }),
        b(document).on("change", 'div.table-responsive table thead th:first [type="checkbox"]',
        function(a) {
            a && a.preventDefault();
            var c = b(a.target).closest("table"),
            d = b(a.target).is(":checked");
            b('tbody tr td:first-child  [type="checkbox"]', c).prop("checked", d),
            h.add(i).addAttr("disabled", !d)
        }),
        b(document).on("change", 'div.table-responsive table tbody td:first-child [type="checkbox"]',
        function(a) {
            a && a.preventDefault();
            var c = b(a.target).closest("table"),
            d = b(a.target).is(":checked"),
            e = b('tbody tr td:first-child  [type="checkbox"]:checked', c);
            b('thead th:first [type="checkbox"]', c).prop("checked", d && e.length == g.length),
            h.add(i).addAttr("disabled", 0 == e.length)
        })
    }
    b(document).on("click", 'div.table-responsive table tbody [data-toggle="ajaxRemove"]',
    function(a) {
        a.preventDefault();
        var c = b(this),
        e = c.data("remote") || c.attr("href"),
        f = c.data("confirm");
        f = "undefined" == typeof f ? !0 : f,
        c.data("loadingText", '<i class="fa fa-spinner fa-spin"></i>');
        var g = function() {
            c.html();
            c.button("loading"),
            b.post(e).done(function(a) {
                if (d.issucceed(a)) {
                    G.ui.tips.suc(a.message || d.lang.removeSuccess, a.url);
                    var e = c.parents("tr");
                    e.css({
                        "background-color": "#dff0d8"
                    }).find("td").css({
                        "background-color": "#dff0d8"
                    }),
                    e.fadeOut(function() {
                        e.remove(),
                        0 == b("table tbody tr").length && window.location.reload()
                    })
                } else c.button("reset"),
                G.ui.tips.error(a.message || d.lang.removeError, a.url)
            }).fail(function() {
                c.button("reset"),
                G.ui.tips.info(d.lang.exception)
            })
        };
        f && G.ui.tips.confirm_flag(c.data("msg") || d.lang.confirmRemove,
        function(a) {
			$('#fallr-wrapper').remove();
            $.post(e,'',function(res){
					G.ui.tips.suc(res.msg,res.code == 0?'reload':'');	
			 });
        }),
        !f && g()
    })
	 b(document).on("click", 'div [data-toggle="ajaxCurl"]',
    function(a) {
        a.preventDefault();
        var c = b(this),
        e = c.data("remote") || c.attr("href");
        f = 0,
        c.data("loadingText", '<i class="fa fa-spinner fa-spin"></i>');
		//c.button("loading");
        if (c.data("msg")){
			G.ui.tips.confirm_flag(c.data("msg"),
			function(a) {
				c.button("reset");
				$('#fallr-wrapper').remove();
				$.post(e,'',function(res){
						
						G.ui.tips.suc(res.msg,res.code == 1?'reload':'');	
				 });
			});
		}else{
			$.post(e,'',function(res){
						c.button("reset");
						G.ui.tips.suc(res.msg,res.code == 0?'reload':'');	
			});
		}
    })
}),
define(assets_path + "/assets/dist/table/paginator", ["$"],
function(a) {
    "use strict";
    a("$"); +
    function(a) {
        var b = function(a, b) {
            this.isCurrent = function() {
                return b == a.currentPage//$('#pageNumber').val();
            },
            this.isFirst = function() {
                return 1 == b
            },
            this.isLast = function() {
                return b == a.totalPages
            },
            this.isPrev = function() {
                return b == a.currentPage - 1
            },
            this.isNext = function() {
                return b == a.currentPage + 1
            },
            this.isLeftOuter = function() {
                return b <= a.outerWindow
            },
            this.isRightOuter = function() {
                return a.totalPages - b < a.outerWindow
            },
            this.isInsideWindow = function() {
                return a.currentPage < a.innerWindow + 1 ? b <= 2 * a.innerWindow + 1 : a.currentPage > a.totalPages - a.innerWindow ? a.totalPages - b <= 2 * a.innerWindow: Math.abs(a.currentPage - b) <= a.innerWindow
            },
            this.number = function() {
                return b
            }
        },
        c = {
            firstPage: function(b, c, d) {
                var e = a("<li>").append(a('<a href="javascript:;">').html(c.first).bind("click.bs-Paginator",
                function() {
                    return b.firstPage(),
                    !1
                }));
                return d.isFirst() && e.addClass("disabled"),
                e
            },
            prevPage: function(b, c, d) {
                var e = a("<li>").append(a('<a href="javascript:;">').attr("rel", "prev").html(c.prev).bind("click.bs-Paginator",
                function() {
                    return b.prevPage(),
                    !1
                }));
                return d.isFirst() && e.addClass("disabled"),
                e
            },
            nextPage: function(b, c, d) {
                var e = a("<li>").append(a('<a href="javascript:;">').attr("rel", "next").html(c.next).bind("click.bs-Paginator",
                function() {
                    return b.nextPage(),
                    !1
                }));
                return d.isLast() && e.addClass("disabled"),
                e
            },
            lastPage: function(b, c, d) {
                var e = a("<li>").append(a('<a href="javascript:;">').html(c.last).bind("click.bs-Paginator",
                function() {
                    return b.lastPage(),
                    !1
                }));
                return d.isLast() && e.addClass("disabled"),
                e
            },
            gap: function(b, c) {
                return a("<li>").addClass("disabled").append(a('<a href="javascript:;">').html(c.gap))
            },
            page: function(b, c, d) {
                var e = a("<li>").append(function() {
                    var c = a('<a href="javascript:;">');
                    return d.isNext() && c.attr("rel", "next"),
                    d.isPrev() && c.attr("rel", "prev"),
                    c.html(d.number()),
                    c.bind("click.bs-Paginator",
                    function() {
                        return b.page(d.number()),
                        !1
                    }),
                    c
                });
                return d.isCurrent() && e.addClass("active"),
                e
            }
        },
        d = function(b, c) {
            this.$element = a(b),
            this.options = a.extend({},
            d.DEFAULTS, c),
            this.$ul = a(b),
            this.render()
        };
        d.DEFAULTS = {
            currentPage: null,
            totalPages: null,
            innerWindow: 2,
            outerWindow: 0,
            first: '<i class="fa fa-angle-double-left" title="第一页"></i>',
            prev: '<i class="fa fa-angle-left" title="上一页"></i>',
            next: '<i class="fa fa-angle-right" title="下一页"></i>',
            /*last: '<i class="fa fa-angle-double-right" title="最后页"></i>',*/
            gap: "..",
            truncate: !1,
            page: function() {
                return ! 0
            }
        },
        d.prototype.render = function() {
            var a = this.options;
            if (!a.totalPages) return void this.$element.hide();
            this.$element.show();
            var d = new b(a, a.currentPage);
            d.isFirst() && a.truncate || (a.first && this.$ul.append(c.firstPage(this, a, d)), a.prev && this.$ul.append(c.prevPage(this, a, d)));
            for (var e = !1,
            f = 1,
            g = a.totalPages; g >= f; f++) {
                var h = new b(a, f);
                h.isLeftOuter() || h.isRightOuter() || h.isInsideWindow() ? (this.$ul.append(c.page(this, a, h)), e = !1) : !e && a.outerWindow > 0 && (this.$ul.append(c.gap(this, a)), e = !0)
            }
            d.isLast() && a.truncate || (a.next && this.$ul.append(c.nextPage(this, a, d)), a.last && this.$ul.append(c.lastPage(this, a, d)))
        },
        d.prototype.page = function(a, b) {
            var c = this.options;
            return void 0 === b && (b = c.totalPages),
            a > 0 && b >= a && c.page(a) && (this.$ul.empty(), c.currentPage = a, c.totalPages = b, this.render()),
            !1
        },
        d.prototype.firstPage = function() {
            return this.page(1)
        },
        d.prototype.lastPage = function() {
            return this.page(this.options.totalPages)
        },
        d.prototype.nextPage = function() {
            return this.page(this.options.currentPage + 1)
        },
        d.prototype.prevPage = function() {
            return this.page(this.options.currentPage - 1)
        };
        var e = a.fn.Paginator;
        a.fn.Paginator = function(b) {
            var c = arguments;
            return this.each(function() {
                var e = a(this),
                //f = e.data("bs.Paginator"),改ajax分页屏蔽
				f = '',
                g = "object" == typeof b && b;
                f || e.data("bs.Paginator", f = new d(this, g)),
                "string" == typeof b && f[b].apply(f, Array.prototype.slice.call(c, 1))
            })
        },
        a.fn.Paginator.Constructor = d,
        a.fn.Paginator.noConflict = function() {
            return a.fn.Paginator = e,
            this
        }
    } (jQuery)
}),
define(assets_path + "/assets/dist/table/talbe-search", ["$", "dist/application/app"],
function(a) {
    "use strict";
    var b = a("$"),
    c = a("dist/application/app"),
    d = c.config,
    e = b("form.talbe-search");
	iq_b_diy = a("$");
    if (e.length > 0) {
		e.append('<input type="hidden" name="sort_by" id="sort_by" value="'+sort_by+'" /><input type="hidden" name="order_by" id="order_by" value="'+order_by+'" />');
		e.submit(function(x){return _search_list(e,0,b);})
        var f = b("input[type='radio'][name='pageSize']"),
        h = b("#sort_by"),
        i = b("#order_by");
        f.bind("change",
        function() {
            e.submit()
        }),
        i.bind("order:change",
        function() {
            e.submit()
        });
       diy_sortable(e,b);
        b("button[type='submit']", e).bind("click",
        function() {
			return void 0
        })
    }
});