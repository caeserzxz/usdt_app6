
var G = {
    ui: {},
    logic: {}
   
   
};
(function (n) {
    var f = {
        buttons: {
            button1: {
                text: "OK",
                danger: !1,
                onclick: function () {
                    n.fallr("hide")
                }
            }
        },
        icon: "check",
        content: "Hello",
        position: "top",
        closeKey: !0,
        closeOverlay: !1,
        useOverlay: !0,
        autoclose: !1,
        easingDuration: 300,
        easingIn: "swing",
        easingOut: "swing",
        height: "auto",
        width: "auto",
        zIndex: 100,
        bound: window,
        afterHide: function () { },
        afterShow: function () { }
    },
		t= new Object, e, i = n(window),
		u = {
		    hide: function (f, o, s) {
		        if (u.isActive()) {
		            n("#fallr-wrapper").stop(!0, !0);
		            var h = n("#fallr-wrapper"),
						a = h.css("position"),
						c = a === "fixed",
						l = 0;
		            switch (t.position) {
		                case "bottom":
		                case "center":
		                    l = (c ? i.height() : h.offset().top + h.outerHeight()) + 10;
		                    break;
		                default:
		                    l = (c ? -1 * h.outerHeight() : h.offset().top - h.outerHeight()) - 10
		            }
		            h.animate({
		                top: l,
		                opacity: c ? 1 : 0
		            }, t.easingDuration || t.duration, t.easingOut, function () {
		                n.support.msie ? n("#fallr-overlay").css("display", "none") : n("#fallr-overlay").fadeOut("fast"), h.remove(), clearTimeout(e), o = typeof o == "function" ? o : t.afterHide, o.call(s)
		            }), n(document).unbind("keydown", r.enterKeyHandler).unbind("keydown", r.closeKeyHandler).unbind("keydown", r.tabKeyHandler)
		        }
		    },
		    resize: function (t, i, f) {
		        var e = n("#fallr-wrapper"),
					o = parseInt(t.width, 10),
					s = parseInt(t.height, 10),
					h = Math.abs(e.outerWidth() - o),
					c = Math.abs(e.outerHeight() - s);
		        u.isActive() && (h > 5 || c > 5) && (e.animate({
		            width: o
		        }, function () {
		            n(this).animate({
		                height: s
		            }, function () {
		                r.fixPos()
		            })
		        }), n("#fallr").animate({
		            width: o - 94
		        }, function () {
		            n(this).animate({
		                height: s - 116
		            }, function () {
		                typeof i == "function" && i.call(f)
		            })
		        }))
		    },
		    show: function (o, s, h) {
		        var a;
		        if (u.isActive()) n("body", "html").animate({
		            scrollTop: n("#fallr").offset().top
		        }, function () {
		            n.fallr("shake")
		        }), n.error("Can't create new message with content: \"" + o.content + '", past message with content "' + t.content + '" is still active');
		        else {
		            t = n.extend({}, f, o), n('<div id="fallr-wrapper"><\/div>').appendTo("body"), t.bound = n(t.bound).length > 0 ? t.bound : window;
		            var c = n("#fallr-wrapper"),
						v = n("#fallr-overlay"),
						l = t.bound === window;
		            c.css({
		                width: t.width,
		                height: t.height,
		                position: "absolute",
		                top: "-9999px",
		                left: "-9999px"
		            }).html('<div id="fallr-icon"><\/div><div id="fallr"><\/div><div id="fallr-buttons"><\/div>').find("#fallr-icon").addClass("icon-msg-" + t.icon).end().find("#fallr").html(t.content).css({
		                height: t.height == "auto" ? "auto" : c.height() - 116,
		                width: c.width() - 94
		            }).end().find("#fallr-buttons").html(function () {
		                var i = "",
							n;
		                for (n in t.buttons) t.buttons.hasOwnProperty(n) && (i = i + '<a href="#" class="fallr-button ' + (t.buttons[n].danger ? "fallr-button-danger" : "") + '" id="fallr-button-' + n + '">' + t.buttons[n].text + "<\/a>");
		                return i
		            }()).find(".fallr-button").bind("click", function () {
		                var i = n(this).attr("id").substring(13),
							r;
		                return typeof t.buttons[i].onclick == "function" && t.buttons[i].onclick != !1 ? (r = n("#fallr"), t.buttons[i].onclick.apply(r)) : u.hide(), !1
		            }), a = function () {
		                c.show();
		                var y = l ? (i.width() - c.outerWidth()) / 2 + i.scrollLeft() : (n(t.bound).width() - c.outerWidth()) / 2 + n(t.bound).offset().left,
							r, f, a = i.height() > c.height() && i.width() > c.width() && l ? "fixed" : "absolute",
							o = a === "fixed";
		                switch (t.position) {
		                    case "bottom":
		                        r = l ? o ? i.height() : i.scrollTop() + i.height() : n(t.bound).offset().top + n(t.bound).outerHeight(), f = r - c.outerHeight();
		                        break;
		                    case "center":
		                        r = l ? o ? -1 * c.outerHeight() : v.offset().top - c.outerHeight() : n(t.bound).offset().top + n(t.bound).height() / 2 - c.outerHeight(), f = r + c.outerHeight() + ((l ? i.height() : c.outerHeight() / 2) - c.outerHeight()) / 2;
		                        break;
		                    default:
		                        f = l ? o ? 0 : i.scrollTop() : n(t.bound).offset().top, r = f - c.outerHeight()
		                }
		                c.css({
		                    left: y,
		                    position: a,
		                    top: r,
		                    "z-index": 999999
		                }).animate({
		                    top: f
		                }, t.easingDuration, t.easingIn, function () {
		                    s = typeof s == "function" ? s : t.afterShow, s.call(h), t.autoclose && (e = setTimeout(u.hide, t.autoclose))
		                })
		            }, t.useOverlay ? n.support.msie && n.support.version < 9 ? (v.css({
		                display: "block",
		                "z-index": t.zIndex
		            }), a()) : v.css({
		                "z-index": t.zIndex
		            }).fadeIn(a) : a(), n(document).bind("keydown", r.enterKeyHandler).bind("keydown", r.closeKeyHandler).bind("keydown", r.tabKeyHandler), n("#fallr-buttons").children().eq(-1).bind("focus", function () {
		                n(this).bind("keydown", r.tabKeyHandler)
		            }), c.find(":input").bind("keydown", function (t) {
		                r.unbindKeyHandler(), t.keyCode === 13 && n(".fallr-button").eq(0).trigger("click")
		            })
		        }
		    },
		    set: function (n, i, r) {
		        for (var u in n) f.hasOwnProperty(u) && (f[u] = n[u], t && t[u] && (t[u] = n[u]));
		        typeof i == "function" && i.call(r)
		    },
		    isActive: function () {
		        return !!(n("#fallr-wrapper").length > 0)
		    },
		    blink: function () {
		        n("#fallr-wrapper").fadeOut(100, function () {
		            n(this).fadeIn(100)
		        })
		    },
		    shake: function () {
		        n("#fallr-wrapper").stop(!0, !0).animate({
		            left: "+=20px"
		        }, 50, function () {
		            n(this).animate({
		                left: "-=40px"
		            }, 50, function () {
		                n(this).animate({
		                    left: "+=30px"
		                }, 50, function () {
		                    n(this).animate({
		                        left: "-=20px"
		                    }, 50, function () {
		                        n(this).animate({
		                            left: "+=10px"
		                        }, 50)
		                    })
		                })
		            })
		        })
		    }
		},
		r = {
		    fixPos: function () {
		        var r = n("#fallr-wrapper"),
					e = r.css("position"),
					f, u;
		        if (i.width() > r.outerWidth() && i.height() > r.outerHeight()) {
		            f = (i.width() - r.outerWidth()) / 2, u = i.height() - r.outerHeight();
		            switch (t.position) {
		                case "center":
		                    u = u / 2;
		                    break;
		                case "bottom":
		                    break;
		                default:
		                    u = 0
		            }
		            e == "fixed" ? r.animate({
		                left: f
		            }, function () {
		                n(this).animate({
		                    top: u
		                })
		            }) : r.css({
		                position: "fixed",
		                left: f,
		                top: u
		            })
		        } else f = (i.width() - r.outerWidth()) / 2 + i.scrollLeft(), u = i.scrollTop(), e != "fixed" ? r.animate({
		            left: f
		        }, function () {
		            n(this).animate({
		                top: u
		            })
		        }) : r.css({
		            position: "absolute",
		            top: u,
		            left: f > 0 ? f : 0
		        })
		    },
		    enterKeyHandler: function (t) {
		        t.keyCode === 13 && (n("#fallr-buttons").children().eq(0).focus(), r.unbindKeyHandler())
		    },
		    tabKeyHandler: function (t) {
		        t.keyCode === 9 && (n("#fallr-wrapper").find(":input, .fallr-button").eq(0).focus(), r.unbindKeyHandler(), t.preventDefault())
		    },
		    closeKeyHandler: function (n) {
		        n.keyCode === 27 && t.closeKey && u.hide()
		    },
		    unbindKeyHandler: function () {
		        n(document).unbind("keydown", r.enterKeyHandler).unbind("keydown", r.tabKeyHandler)
		    }
		};
    n(document).ready(function () {
        n("body").append('<div id="fallr-overlay"><\/div>'), n("#fallr-overlay").bind("click", function () {
            t.closeOverlay ? u.hide() : u.blink()
        })
    }), n(window).resize(function () {
        u.isActive() && t.bound === window && r.fixPos()
    }), n.fallr = function (t, i, r) {
        var f = window;
        typeof t == "object" && (i = t, t = "show"), u[t] ? (typeof i == "function" && (r = i, i = null), u[t](i, r, f)) : n.error('Method "' + t + '" does not exist in $.fallr')
    }
})(jQuery);
jQuery.fn.extend({
    autoscroll: function () {
        var _self = $(this);
        $('html,body').animate({
            scrollTop: _self.offset().top
        }, 800)
    }
});

G.string = {
    Empty: ""
};

G.ui.tips = {
	    err: function (t, u) {
        this._set(t, u, {
            useOverlay: true,
            position: "center",
            icon: "error",
            autoclose: null,
            closeOverlay: true,
            buttons: {
                button1: {
                    text: '确定',
                    danger: false,
                    onclick: function () {
                        $.fallr('hide')
                    }
                }
            }
        }), afterHide = function () {
            if (u) {
                window.location = u
            }
        }
    },
    info: function (t, u) {
        this._set(t, u, {
            useOverlay: true,
            position: "center",
            icon: "info",
            autoclose: null,
            closeOverlay: true,
            buttons: u ? {
                button1: {
                    text: '确定',
                    onclick: function () {
                        window.location = u
                    }
                }
            } : {
                button1: {
                    text: '确定',
                    danger: false,
                    onclick: function () {
                        $.fallr('hide')
                    }
                }
            }
        })
    },
    suc: function (t, u) {
        this._set(t, u, {
            useOverlay: true,
            position: null,
            autoclose: 1000,
            icon: "check",
            closeOverlay: true,
            buttons: null
        })
    },
    upload: function (t, u) {
        this._set(t, u, {
            useOverlay: true,
            position: null,
            autoclose: false,
            icon: "check",
            closeOverlay: true,
            buttons: null
        })
    },
    _set: function (str, url, opt) {
        opt = $.extend({
            useOverlay: true,
            position: null,
            icon: null,
            autoclose: null,
            closeOverlay: true,
            buttons: {},
            afterHide: function () { }
        }, opt || {});
        if (url) {
            opt.afterHide = function () {
				if (url == 'reload')
				{
					location.reload();
				}else{
                	window.location = url
				}
            }
        } else {
            opt.afterHide = function () { }
        }
        $.fallr('show', {
            content: str,
            autoclose: opt.autoclose,
            useOverlay: opt.useOverlay,
            position: opt.position,
            icon: opt.icon,
            closeOverlay: opt.closeOverlay,
            buttons: opt.buttons,
            afterHide: opt.afterHide
        })
    },
    confirm: function (t, u) {
        $.fallr('show', {
            buttons: {
                button1: {
                    text: '确定',
                    danger: true,
                    onclick: function () {
						if (typeof(u) == 'function'){
							u();
							return $.fallr('hide');
						}
                        window.location = u
                    }
                },
                button2: {
                    text: '取消'
                }
            },
            content: '<p>' + t + '</p>',
            icon: 'trash'
        })
    },
    confirm_flag: function (t, f,cfm_id,icon) {
		var input = '';
		if (cfm_id > 0)
		{
			input = '<input id="cfm_id" name="cfm_id" type="hidden" value="'+cfm_id+'">';
		}
        $.fallr('show', {
            buttons: {
                button1: {
                    text: '确定',
                    danger: true,
                    onclick: function(){
						$.fallr('hide');
						f();						
					}
                },
                button2: {
                    text: '取消'
                }
            },
            content: '<p>' + t + '</p>'+input,
            icon: icon?icon:'trash'
        })
    },
    confirm_tips: function (u, t) {
        $.fallr('show', {
            position: 'center',
            buttons: {
                button1: {
                    text: '验证',
                    danger: true,
                    onclick: function () {
                        window.location = u
                    }
                },
                button2: {
                    text: '继续使用'
                }
            },
            content: t,
            icon: 'info'
        })
    },
    iframe: function (t, u, w, h) {
        if (!w) w = 500;
        if (!h) h = 300;
        console.log(    w);
        $.fallr('show', {
            content: '<h2>' + t + '</h2>' + '<iframe width="' + w + '" height="' + h + '" src="' + u + '" frameborder="0" allowfullscreen></iframe>',
            width: w + 130,
            icon: null,
            closeOverlay: true,
            position: 'center',
            buttons: {
                button1: {
                    text: '关闭'
                }
            }
        })
    },
    iframe2: function (t, u, w, h) {
        if (w) w = 500;
        if (h) h = 300;
        $.fallr('show', {
            content: '<h2>' + t + '</h2>' + '<iframe width="' + w + '" height="' + h + '" src="' + u + '" frameborder="0" allowfullscreen></iframe>',
            width: w + 130,
            icon: null,
            closeOverlay: true,
            position: 'center',
            buttons: {
                button1: {
                    text: '关闭'
                }
            }
        })
    },
    html: function (t, u) {
        var b = {};
        if (u) {
            b = {
                button1: {
                    text: '确定',
                    onclick: function () {
                        $.fallr('hide')
                    }
                }
            }
        }
        $.fallr('show', {
            content: '' + t + '',
            position: 'center',
            buttons: b,
            width: 500,
            afterHide: function () {
                if (u) {
                    window.location = u
                }
            }
        })
    },
    up: function (t, v, u) {
        var c = $.cookie("up_tips");
        if (!c || c != v) {
            var b = {
                button1: {
                    text: '关闭',
                    onclick: function () {
                        $.cookie("up_tips", v, {
                            expires: 365,
                            path: "/"
                        });
                        $.fallr('hide')
                    }
                },
                button2: {
                    text: '查看详情',
                    onclick: function () {
                        $.cookie("up_tips", v, {
                            expires: 365,
                            path: "/"
                        });
                        window.location = u;
                        $.fallr('hide')
                    }
                }
            };
            $.fallr('show', {
                content: '' + t + '',
                position: 'center',
                buttons: b,
                icon: 'up',
                width: '400px',
                height: '230px'
            })
        }
    },
    confirm_t: function (t, u) {
        $.fallr('show', {
            buttons: {
                button1: {
                    text: '确定',
                    danger: true,
                    onclick: function () {
                        window.location = u
                    }
                },
                button2: {
                    text: '取消'
                }
            },
            content: '<p>' + t + '</p>'
        })
    },
    confirm_c: function (t, u) {
        $.fallr('show', {
            position: 'center',
            buttons: {
                button1: {
                    text: '确定',
                    danger: true,
                    onclick: function () {
                        window.location = u;
                    }
                },
                button2: {
                    text: '取消'
                }
            },
            content: t,
            icon: 'info'
        });
    }
};

