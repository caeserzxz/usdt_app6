!
function(t, e) {
    if ("object" == typeof exports && exports) e(exports);
    else {
        var i = {};
        e(i),
        "function" == typeof define && define.amd ? define("mustache", i) : t.Mustache = i
    }
} (this,
function(t) {
    function e(t, e) {
        return p.call(t, e)
    }
    function i(t) {
        return ! e(f, t)
    }
    function n(t) {
        return "function" == typeof t
    }
    function s(t) {
        return t.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&")
    }
    function o(t) {
        return String(t).replace(/[&<>"'\/]/g,
        function(t) {
            return v[t]
        })
    }
    function r(t) {
        if (!g(t) || 2 !== t.length) throw new Error("Invalid tags: " + t);
        return [new RegExp(s(t[0]) + "\\s*"), new RegExp("\\s*" + s(t[1]))]
    }
    function a(e, n) {
        function o() {
            if (D && !E) for (; T.length;) delete k[T.pop()];
            else T = [];
            D = !1,
            E = !1
        }
        n = n || t.tags,
        e = e || "",
        "string" == typeof n && (n = n.split(b));
        for (var a, u, d, p, f, m, g = r(n), v = new h(e), C = [], k = [], T = [], D = !1, E = !1; ! v.eos();) {
            if (a = v.pos, d = v.scanUntil(g[0])) for (var M = 0,
            I = d.length; I > M; ++M) p = d.charAt(M),
            i(p) ? T.push(k.length) : E = !0,
            k.push(["text", p, a, a + 1]),
            a += 1,
            "\n" === p && o();
            if (!v.scan(g[0])) break;
            if (D = !0, u = v.scan(x) || "name", v.scan(y), "=" === u ? (d = v.scanUntil(_), v.scan(_), v.scanUntil(g[1])) : "{" === u ? (d = v.scanUntil(new RegExp("\\s*" + s("}" + n[1]))), v.scan(w), v.scanUntil(g[1]), u = "&") : d = v.scanUntil(g[1]), !v.scan(g[1])) throw new Error("Unclosed tag at " + v.pos);
            if (f = [u, d, a, v.pos], k.push(f), "#" === u || "^" === u) C.push(f);
            else if ("/" === u) {
                if (m = C.pop(), !m) throw new Error('Unopened section "' + d + '" at ' + a);
                if (m[1] !== d) throw new Error('Unclosed section "' + m[1] + '" at ' + a)
            } else "name" === u || "{" === u || "&" === u ? E = !0 : "=" === u && (g = r(n = d.split(b)))
        }
        if (m = C.pop()) throw new Error('Unclosed section "' + m[1] + '" at ' + v.pos);
        return c(l(k))
    }
    function l(t) {
        for (var e, i, n = [], s = 0, o = t.length; o > s; ++s) e = t[s],
        e && ("text" === e[0] && i && "text" === i[0] ? (i[1] += e[1], i[3] = e[3]) : (n.push(e), i = e));
        return n
    }
    function c(t) {
        for (var e, i, n = [], s = n, o = [], r = 0, a = t.length; a > r; ++r) switch (e = t[r], e[0]) {
        case "#":
        case "^":
            s.push(e),
            o.push(e),
            s = e[4] = [];
            break;
        case "/":
            i = o.pop(),
            i[5] = e[2],
            s = o.length > 0 ? o[o.length - 1][4] : n;
            break;
        default:
            s.push(e)
        }
        return n
    }
    function h(t) {
        this.string = t,
        this.tail = t,
        this.pos = 0
    }
    function u(t, e) {
        this.view = null == t ? {}: t,
        this.cache = {
            ".": this.view
        },
        this.parent = e
    }
    function d() {
        this.cache = {}
    }
    var p = RegExp.prototype.test,
    f = /\S/,
    m = Object.prototype.toString,
    g = Array.isArray ||
    function(t) {
        return "[object Array]" === m.call(t)
    },
    v = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
        "/": "&#x2F;"
    },
    y = /\s*/,
    b = /\s+/,
    _ = /\s*=/,
    w = /\s*\}/,
    x = /#|\^|\/|>|\{|&|=|!/;
    h.prototype.eos = function() {
        return "" === this.tail
    },
    h.prototype.scan = function(t) {
        var e = this.tail.match(t);
        if (e && 0 === e.index) {
            var i = e[0];
            return this.tail = this.tail.substring(i.length),
            this.pos += i.length,
            i
        }
        return ""
    },
    h.prototype.scanUntil = function(t) {
        var e, i = this.tail.search(t);
        switch (i) {
        case - 1 : e = this.tail,
            this.tail = "";
            break;
        case 0:
            e = "";
            break;
        default:
            e = this.tail.substring(0, i),
            this.tail = this.tail.substring(i)
        }
        return this.pos += e.length,
        e
    },
    u.prototype.push = function(t) {
        return new u(t, this)
    },
    u.prototype.lookup = function(t) {
        var e;
        if (t in this.cache) e = this.cache[t];
        else {
            for (var i = this; i;) {
                if (t.indexOf(".") > 0) {
                    e = i.view;
                    for (var s = t.split("."), o = 0; null != e && o < s.length;) e = e[s[o++]]
                } else e = i.view[t];
                if (null != e) break;
                i = i.parent
            }
            this.cache[t] = e
        }
        return n(e) && (e = e.call(this.view)),
        e
    },
    d.prototype.clearCache = function() {
        this.cache = {}
    },
    d.prototype.parse = function(t, e) {
        var i = this.cache,
        n = i[t];
        return null == n && (n = i[t] = a(t, e)),
        n
    },
    d.prototype.render = function(t, e, i) {
        var n = this.parse(t),
        s = e instanceof u ? e: new u(e);
        return this.renderTokens(n, s, i, t)
    },
    d.prototype.renderTokens = function(e, i, s, o) {
        function r(t) {
            return h.render(t, i, s)
        }
        for (var a, l, c = "",
        h = this,
        u = 0,
        d = e.length; d > u; ++u) switch (a = e[u], a[0]) {
        case "#":
            if (l = i.lookup(a[1]), !l) continue;
            if (g(l)) for (var p = 0,
            f = l.length; f > p; ++p) c += this.renderTokens(a[4], i.push(l[p]), s, o);
            else if ("object" == typeof l || "string" == typeof l) c += this.renderTokens(a[4], i.push(l), s, o);
            else if (n(l)) {
                if ("string" != typeof o) throw new Error("Cannot use higher-order sections without the original template");
                l = l.call(i.view, o.slice(a[3], a[5]), r),
                null != l && (c += l)
            } else c += this.renderTokens(a[4], i, s, o);
            break;
        case "^":
            l = i.lookup(a[1]),
            (!l || g(l) && 0 === l.length) && (c += this.renderTokens(a[4], i, s, o));
            break;
        case ">":
            if (!s) continue;
            l = n(s) ? s(a[1]) : s[a[1]],
            null != l && (c += this.renderTokens(this.parse(l), i, s, l));
            break;
        case "&":
            l = i.lookup(a[1]),
            null != l && (c += l);
            break;
        case "name":
            l = i.lookup(a[1]),
            null != l && (c += t.escape(l));
            break;
        case "text":
            c += a[1]
        }
        return c
    },
    t.name = "mustache.js",
    t.version = "0.8.1",
    t.tags = ["{{", "}}"];
    var C = new d;
    t.clearCache = function() {
        return C.clearCache()
    },
    t.parse = function(t, e) {
        return C.parse(t, e)
    },
    t.render = function(t, e, i) {
        return C.render(t, e, i)
    },
    t.to_html = function(e, i, s, o) {
        var r = t.render(e, i, s);
        return n(o) ? void o(r) : r
    },
    t.escape = o,
    t.Scanner = h,
    t.Context = u,
    t.Writer = d
}),
function() {
    var t = this,
    e = t._,
    i = {},
    n = Array.prototype,
    s = Object.prototype,
    o = Function.prototype,
    r = n.push,
    a = n.slice,
    l = n.concat,
    c = s.toString,
    h = s.hasOwnProperty,
    u = n.forEach,
    d = n.map,
    p = n.reduce,
    f = n.reduceRight,
    m = n.filter,
    g = n.every,
    v = n.some,
    y = n.indexOf,
    b = n.lastIndexOf,
    _ = Array.isArray,
    w = Object.keys,
    x = o.bind,
    C = function(t) {
        return t instanceof C ? t: this instanceof C ? void(this._wrapped = t) : new C(t)
    };
    "undefined" != typeof exports ? ("undefined" != typeof module && module.exports && (exports = module.exports = C), exports._ = C) : t._ = C,
    C.VERSION = "1.6.0";
    var k = C.each = C.forEach = function(t, e, n) {
        if (null == t) return t;
        if (u && t.forEach === u) t.forEach(e, n);
        else if (t.length === +t.length) {
            for (var s = 0,
            o = t.length; o > s; s++) if (e.call(n, t[s], s, t) === i) return
        } else for (var r = C.keys(t), s = 0, o = r.length; o > s; s++) if (e.call(n, t[r[s]], r[s], t) === i) return;
        return t
    };
    C.map = C.collect = function(t, e, i) {
        var n = [];
        return null == t ? n: d && t.map === d ? t.map(e, i) : (k(t,
        function(t, s, o) {
            n.push(e.call(i, t, s, o))
        }), n)
    };
    var T = "Reduce of empty array with no initial value";
    C.reduce = C.foldl = C.inject = function(t, e, i, n) {
        var s = arguments.length > 2;
        if (null == t && (t = []), p && t.reduce === p) return n && (e = C.bind(e, n)),
        s ? t.reduce(e, i) : t.reduce(e);
        if (k(t,
        function(t, o, r) {
            s ? i = e.call(n, i, t, o, r) : (i = t, s = !0)
        }), !s) throw new TypeError(T);
        return i
    },
    C.reduceRight = C.foldr = function(t, e, i, n) {
        var s = arguments.length > 2;
        if (null == t && (t = []), f && t.reduceRight === f) return n && (e = C.bind(e, n)),
        s ? t.reduceRight(e, i) : t.reduceRight(e);
        var o = t.length;
        if (o !== +o) {
            var r = C.keys(t);
            o = r.length
        }
        if (k(t,
        function(a, l, c) {
            l = r ? r[--o] : --o,
            s ? i = e.call(n, i, t[l], l, c) : (i = t[l], s = !0)
        }), !s) throw new TypeError(T);
        return i
    },
    C.find = C.detect = function(t, e, i) {
        var n;
        return D(t,
        function(t, s, o) {
            return e.call(i, t, s, o) ? (n = t, !0) : void 0
        }),
        n
    },
    C.filter = C.select = function(t, e, i) {
        var n = [];
        return null == t ? n: m && t.filter === m ? t.filter(e, i) : (k(t,
        function(t, s, o) {
            e.call(i, t, s, o) && n.push(t)
        }), n)
    },
    C.reject = function(t, e, i) {
        return C.filter(t,
        function(t, n, s) {
            return ! e.call(i, t, n, s)
        },
        i)
    },
    C.every = C.all = function(t, e, n) {
        e || (e = C.identity);
        var s = !0;
        return null == t ? s: g && t.every === g ? t.every(e, n) : (k(t,
        function(t, o, r) {
            return (s = s && e.call(n, t, o, r)) ? void 0 : i
        }), !!s)
    };
    var D = C.some = C.any = function(t, e, n) {
        e || (e = C.identity);
        var s = !1;
        return null == t ? s: v && t.some === v ? t.some(e, n) : (k(t,
        function(t, o, r) {
            return s || (s = e.call(n, t, o, r)) ? i: void 0
        }), !!s)
    };
    C.contains = C.include = function(t, e) {
        return null == t ? !1 : y && t.indexOf === y ? -1 != t.indexOf(e) : D(t,
        function(t) {
            return t === e
        })
    },
    C.invoke = function(t, e) {
        var i = a.call(arguments, 2),
        n = C.isFunction(e);
        return C.map(t,
        function(t) {
            return (n ? e: t[e]).apply(t, i)
        })
    },
    C.pluck = function(t, e) {
        return C.map(t, C.property(e))
    },
    C.where = function(t, e) {
        return C.filter(t, C.matches(e))
    },
    C.findWhere = function(t, e) {
        return C.find(t, C.matches(e))
    },
    C.max = function(t, e, i) {
        if (!e && C.isArray(t) && t[0] === +t[0] && t.length < 65535) return Math.max.apply(Math, t);
        var n = -1 / 0,
        s = -1 / 0;
        return k(t,
        function(t, o, r) {
            var a = e ? e.call(i, t, o, r) : t;
            a > s && (n = t, s = a)
        }),
        n
    },
    C.min = function(t, e, i) {
        if (!e && C.isArray(t) && t[0] === +t[0] && t.length < 65535) return Math.min.apply(Math, t);
        var n = 1 / 0,
        s = 1 / 0;
        return k(t,
        function(t, o, r) {
            var a = e ? e.call(i, t, o, r) : t;
            s > a && (n = t, s = a)
        }),
        n
    },
    C.shuffle = function(t) {
        var e, i = 0,
        n = [];
        return k(t,
        function(t) {
            e = C.random(i++),
            n[i - 1] = n[e],
            n[e] = t
        }),
        n
    },
    C.sample = function(t, e, i) {
        return null == e || i ? (t.length !== +t.length && (t = C.values(t)), t[C.random(t.length - 1)]) : C.shuffle(t).slice(0, Math.max(0, e))
    };
    var E = function(t) {
        return null == t ? C.identity: C.isFunction(t) ? t: C.property(t)
    };
    C.sortBy = function(t, e, i) {
        return e = E(e),
        C.pluck(C.map(t,
        function(t, n, s) {
            return {
                value: t,
                index: n,
                criteria: e.call(i, t, n, s)
            }
        }).sort(function(t, e) {
            var i = t.criteria,
            n = e.criteria;
            if (i !== n) {
                if (i > n || void 0 === i) return 1;
                if (n > i || void 0 === n) return - 1
            }
            return t.index - e.index
        }), "value")
    };
    var M = function(t) {
        return function(e, i, n) {
            var s = {};
            return i = E(i),
            k(e,
            function(o, r) {
                var a = i.call(n, o, r, e);
                t(s, a, o)
            }),
            s
        }
    };
    C.groupBy = M(function(t, e, i) {
        C.has(t, e) ? t[e].push(i) : t[e] = [i]
    }),
    C.indexBy = M(function(t, e, i) {
        t[e] = i
    }),
    C.countBy = M(function(t, e) {
        C.has(t, e) ? t[e]++:t[e] = 1
    }),
    C.sortedIndex = function(t, e, i, n) {
        i = E(i);
        for (var s = i.call(n, e), o = 0, r = t.length; r > o;) {
            var a = o + r >>> 1;
            i.call(n, t[a]) < s ? o = a + 1 : r = a
        }
        return o
    },
    C.toArray = function(t) {
        return t ? C.isArray(t) ? a.call(t) : t.length === +t.length ? C.map(t, C.identity) : C.values(t) : []
    },
    C.size = function(t) {
        return null == t ? 0 : t.length === +t.length ? t.length: C.keys(t).length
    },
    C.first = C.head = C.take = function(t, e, i) {
        return null == t ? void 0 : null == e || i ? t[0] : 0 > e ? [] : a.call(t, 0, e)
    },
    C.initial = function(t, e, i) {
        return a.call(t, 0, t.length - (null == e || i ? 1 : e))
    },
    C.last = function(t, e, i) {
        return null == t ? void 0 : null == e || i ? t[t.length - 1] : a.call(t, Math.max(t.length - e, 0))
    },
    C.rest = C.tail = C.drop = function(t, e, i) {
        return a.call(t, null == e || i ? 1 : e)
    },
    C.compact = function(t) {
        return C.filter(t, C.identity)
    };
    var I = function(t, e, i) {
        return e && C.every(t, C.isArray) ? l.apply(i, t) : (k(t,
        function(t) {
            C.isArray(t) || C.isArguments(t) ? e ? r.apply(i, t) : I(t, e, i) : i.push(t)
        }), i)
    };
    C.flatten = function(t, e) {
        return I(t, e, [])
    },
    C.without = function(t) {
        return C.difference(t, a.call(arguments, 1))
    },
    C.partition = function(t, e) {
        var i = [],
        n = [];
        return k(t,
        function(t) { (e(t) ? i: n).push(t)
        }),
        [i, n]
    },
    C.uniq = C.unique = function(t, e, i, n) {
        C.isFunction(e) && (n = i, i = e, e = !1);
        var s = i ? C.map(t, i, n) : t,
        o = [],
        r = [];
        return k(s,
        function(i, n) { (e ? n && r[r.length - 1] === i: C.contains(r, i)) || (r.push(i), o.push(t[n]))
        }),
        o
    },
    C.union = function() {
        return C.uniq(C.flatten(arguments, !0))
    },
    C.intersection = function(t) {
        var e = a.call(arguments, 1);
        return C.filter(C.uniq(t),
        function(t) {
            return C.every(e,
            function(e) {
                return C.contains(e, t)
            })
        })
    },
    C.difference = function(t) {
        var e = l.apply(n, a.call(arguments, 1));
        return C.filter(t,
        function(t) {
            return ! C.contains(e, t)
        })
    },
    C.zip = function() {
        for (var t = C.max(C.pluck(arguments, "length").concat(0)), e = new Array(t), i = 0; t > i; i++) e[i] = C.pluck(arguments, "" + i);
        return e
    },
    C.object = function(t, e) {
        if (null == t) return {};
        for (var i = {},
        n = 0,
        s = t.length; s > n; n++) e ? i[t[n]] = e[n] : i[t[n][0]] = t[n][1];
        return i
    },
    C.indexOf = function(t, e, i) {
        if (null == t) return - 1;
        var n = 0,
        s = t.length;
        if (i) {
            if ("number" != typeof i) return n = C.sortedIndex(t, e),
            t[n] === e ? n: -1;
            n = 0 > i ? Math.max(0, s + i) : i
        }
        if (y && t.indexOf === y) return t.indexOf(e, i);
        for (; s > n; n++) if (t[n] === e) return n;
        return - 1
    },
    C.lastIndexOf = function(t, e, i) {
        if (null == t) return - 1;
        var n = null != i;
        if (b && t.lastIndexOf === b) return n ? t.lastIndexOf(e, i) : t.lastIndexOf(e);
        for (var s = n ? i: t.length; s--;) if (t[s] === e) return s;
        return - 1
    },
    C.range = function(t, e, i) {
        arguments.length <= 1 && (e = t || 0, t = 0),
        i = arguments[2] || 1;
        for (var n = Math.max(Math.ceil((e - t) / i), 0), s = 0, o = new Array(n); n > s;) o[s++] = t,
        t += i;
        return o
    };
    var S = function() {};
    C.bind = function(t, e) {
        var i, n;
        if (x && t.bind === x) return x.apply(t, a.call(arguments, 1));
        if (!C.isFunction(t)) throw new TypeError;
        return i = a.call(arguments, 2),
        n = function() {
            if (! (this instanceof n)) return t.apply(e, i.concat(a.call(arguments)));
            S.prototype = t.prototype;
            var s = new S;
            S.prototype = null;
            var o = t.apply(s, i.concat(a.call(arguments)));
            return Object(o) === o ? o: s
        }
    },
    C.partial = function(t) {
        var e = a.call(arguments, 1);
        return function() {
            for (var i = 0,
            n = e.slice(), s = 0, o = n.length; o > s; s++) n[s] === C && (n[s] = arguments[i++]);
            for (; i < arguments.length;) n.push(arguments[i++]);
            return t.apply(this, n)
        }
    },
    C.bindAll = function(t) {
        var e = a.call(arguments, 1);
        if (0 === e.length) throw new Error("bindAll must be passed function names");
        return k(e,
        function(e) {
            t[e] = C.bind(t[e], t)
        }),
        t
    },
    C.memoize = function(t, e) {
        var i = {};
        return e || (e = C.identity),
        function() {
            var n = e.apply(this, arguments);
            return C.has(i, n) ? i[n] : i[n] = t.apply(this, arguments)
        }
    },
    C.delay = function(t, e) {
        var i = a.call(arguments, 2);
        return setTimeout(function() {
            return t.apply(null, i)
        },
        e)
    },
    C.defer = function(t) {
        return C.delay.apply(C, [t, 1].concat(a.call(arguments, 1)))
    },
    C.throttle = function(t, e, i) {
        var n, s, o, r = null,
        a = 0;
        i || (i = {});
        var l = function() {
            a = i.leading === !1 ? 0 : C.now(),
            r = null,
            o = t.apply(n, s),
            n = s = null
        };
        return function() {
            var c = C.now();
            a || i.leading !== !1 || (a = c);
            var h = e - (c - a);
            return n = this,
            s = arguments,
            0 >= h ? (clearTimeout(r), r = null, a = c, o = t.apply(n, s), n = s = null) : r || i.trailing === !1 || (r = setTimeout(l, h)),
            o
        }
    },
    C.debounce = function(t, e, i) {
        var n, s, o, r, a, l = function() {
            var c = C.now() - r;
            e > c ? n = setTimeout(l, e - c) : (n = null, i || (a = t.apply(o, s), o = s = null))
        };
        return function() {
            o = this,
            s = arguments,
            r = C.now();
            var c = i && !n;
            return n || (n = setTimeout(l, e)),
            c && (a = t.apply(o, s), o = s = null),
            a
        }
    },
    C.once = function(t) {
        var e, i = !1;
        return function() {
            return i ? e: (i = !0, e = t.apply(this, arguments), t = null, e)
        }
    },
    C.wrap = function(t, e) {
        return C.partial(e, t)
    },
    C.compose = function() {
        var t = arguments;
        return function() {
            for (var e = arguments,
            i = t.length - 1; i >= 0; i--) e = [t[i].apply(this, e)];
            return e[0]
        }
    },
    C.after = function(t, e) {
        return function() {
            return--t < 1 ? e.apply(this, arguments) : void 0
        }
    },
    C.keys = function(t) {
        if (!C.isObject(t)) return [];
        if (w) return w(t);
        var e = [];
        for (var i in t) C.has(t, i) && e.push(i);
        return e
    },
    C.values = function(t) {
        for (var e = C.keys(t), i = e.length, n = new Array(i), s = 0; i > s; s++) n[s] = t[e[s]];
        return n
    },
    C.pairs = function(t) {
        for (var e = C.keys(t), i = e.length, n = new Array(i), s = 0; i > s; s++) n[s] = [e[s], t[e[s]]];
        return n
    },
    C.invert = function(t) {
        for (var e = {},
        i = C.keys(t), n = 0, s = i.length; s > n; n++) e[t[i[n]]] = i[n];
        return e
    },
    C.functions = C.methods = function(t) {
        var e = [];
        for (var i in t) C.isFunction(t[i]) && e.push(i);
        return e.sort()
    },
    C.extend = function(t) {
        return k(a.call(arguments, 1),
        function(e) {
            if (e) for (var i in e) t[i] = e[i]
        }),
        t
    },
    C.pick = function(t) {
        var e = {},
        i = l.apply(n, a.call(arguments, 1));
        return k(i,
        function(i) {
            i in t && (e[i] = t[i])
        }),
        e
    },
    C.omit = function(t) {
        var e = {},
        i = l.apply(n, a.call(arguments, 1));
        for (var s in t) C.contains(i, s) || (e[s] = t[s]);
        return e
    },
    C.defaults = function(t) {
        return k(a.call(arguments, 1),
        function(e) {
            if (e) for (var i in e) void 0 === t[i] && (t[i] = e[i])
        }),
        t
    },
    C.clone = function(t) {
        return C.isObject(t) ? C.isArray(t) ? t.slice() : C.extend({},
        t) : t
    },
    C.tap = function(t, e) {
        return e(t),
        t
    };
    var P = function(t, e, i, n) {
        if (t === e) return 0 !== t || 1 / t == 1 / e;
        if (null == t || null == e) return t === e;
        t instanceof C && (t = t._wrapped),
        e instanceof C && (e = e._wrapped);
        var s = c.call(t);
        if (s != c.call(e)) return ! 1;
        switch (s) {
        case "[object String]":
            return t == String(e);
        case "[object Number]":
            return t != +t ? e != +e: 0 == t ? 1 / t == 1 / e: t == +e;
        case "[object Date]":
        case "[object Boolean]":
            return + t == +e;
        case "[object RegExp]":
            return t.source == e.source && t.global == e.global && t.multiline == e.multiline && t.ignoreCase == e.ignoreCase
        }
        if ("object" != typeof t || "object" != typeof e) return ! 1;
        for (var o = i.length; o--;) if (i[o] == t) return n[o] == e;
        var r = t.constructor,
        a = e.constructor;
        if (r !== a && !(C.isFunction(r) && r instanceof r && C.isFunction(a) && a instanceof a) && "constructor" in t && "constructor" in e) return ! 1;
        i.push(t),
        n.push(e);
        var l = 0,
        h = !0;
        if ("[object Array]" == s) {
            if (l = t.length, h = l == e.length) for (; l--&&(h = P(t[l], e[l], i, n)););
        } else {
            for (var u in t) if (C.has(t, u) && (l++, !(h = C.has(e, u) && P(t[u], e[u], i, n)))) break;
            if (h) {
                for (u in e) if (C.has(e, u) && !l--) break;
                h = !l
            }
        }
        return i.pop(),
        n.pop(),
        h
    };
    C.isEqual = function(t, e) {
        return P(t, e, [], [])
    },
    C.isEmpty = function(t) {
        if (null == t) return ! 0;
        if (C.isArray(t) || C.isString(t)) return 0 === t.length;
        for (var e in t) if (C.has(t, e)) return ! 1;
        return ! 0
    },
    C.isElement = function(t) {
        return ! (!t || 1 !== t.nodeType)
    },
    C.isArray = _ ||
    function(t) {
        return "[object Array]" == c.call(t)
    },
    C.isObject = function(t) {
        return t === Object(t)
    },
    k(["Arguments", "Function", "String", "Number", "Date", "RegExp"],
    function(t) {
        C["is" + t] = function(e) {
            return c.call(e) == "[object " + t + "]"
        }
    }),
    C.isArguments(arguments) || (C.isArguments = function(t) {
        return ! (!t || !C.has(t, "callee"))
    }),
    "function" != typeof / . / &&(C.isFunction = function(t) {
        return "function" == typeof t
    }),
    C.isFinite = function(t) {
        return isFinite(t) && !isNaN(parseFloat(t))
    },
    C.isNaN = function(t) {
        return C.isNumber(t) && t != +t
    },
    C.isBoolean = function(t) {
        return t === !0 || t === !1 || "[object Boolean]" == c.call(t)
    },
    C.isNull = function(t) {
        return null === t
    },
    C.isUndefined = function(t) {
        return void 0 === t
    },
    C.has = function(t, e) {
        return h.call(t, e)
    },
    C.noConflict = function() {
        return t._ = e,
        this
    },
    C.identity = function(t) {
        return t
    },
    C.constant = function(t) {
        return function() {
            return t
        }
    },
    C.property = function(t) {
        return function(e) {
            return e[t]
        }
    },
    C.matches = function(t) {
        return function(e) {
            if (e === t) return ! 0;
            for (var i in t) if (t[i] !== e[i]) return ! 1;
            return ! 0
        }
    },
    C.times = function(t, e, i) {
        for (var n = Array(Math.max(0, t)), s = 0; t > s; s++) n[s] = e.call(i, s);
        return n
    },
    C.random = function(t, e) {
        return null == e && (e = t, t = 0),
        t + Math.floor(Math.random() * (e - t + 1))
    },
    C.now = Date.now ||
    function() {
        return (new Date).getTime()
    };
    var A = {
        escape: {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#x27;"
        }
    };
    A.unescape = C.invert(A.escape);
    var N = {
        escape: new RegExp("[" + C.keys(A.escape).join("") + "]", "g"),
        unescape: new RegExp("(" + C.keys(A.unescape).join("|") + ")", "g")
    };
    C.each(["escape", "unescape"],
    function(t) {
        C[t] = function(e) {
            return null == e ? "": ("" + e).replace(N[t],
            function(e) {
                return A[t][e]
            })
        }
    }),
    C.result = function(t, e) {
        if (null == t) return void 0;
        var i = t[e];
        return C.isFunction(i) ? i.call(t) : i
    },
    C.mixin = function(t) {
        k(C.functions(t),
        function(e) {
            var i = C[e] = t[e];
            C.prototype[e] = function() {
                var t = [this._wrapped];
                return r.apply(t, arguments),
                R.call(this, i.apply(C, t))
            }
        })
    };
    var O = 0;
    C.uniqueId = function(t) {
        var e = ++O + "";
        return t ? t + e: e
    },
    C.templateSettings = {
        evaluate: /<%([\s\S]+?)%>/g,
        interpolate: /<%=([\s\S]+?)%>/g,
        escape: /<%-([\s\S]+?)%>/g
    };
    var z = /(.)^/,
    H = {
        "'": "'",
        "\\": "\\",
        "\r": "r",
        "\n": "n",
        "	": "t",
        "\u2028": "u2028",
        "\u2029": "u2029"
    },
    j = /\\|'|\r|\n|\t|\u2028|\u2029/g;
    C.template = function(t, e, i) {
        var n;
        i = C.defaults({},
        i, C.templateSettings);
        var s = new RegExp([(i.escape || z).source, (i.interpolate || z).source, (i.evaluate || z).source].join("|") + "|$", "g"),
        o = 0,
        r = "__p+='";
        t.replace(s,
        function(e, i, n, s, a) {
            return r += t.slice(o, a).replace(j,
            function(t) {
                return "\\" + H[t]
            }),
            i && (r += "'+\n((__t=(" + i + "))==null?'':_.escape(__t))+\n'"),
            n && (r += "'+\n((__t=(" + n + "))==null?'':__t)+\n'"),
            s && (r += "';\n" + s + "\n__p+='"),
            o = a + e.length,
            e
        }),
        r += "';\n",
        i.variable || (r = "with(obj||{}){\n" + r + "}\n"),
        r = "var __t,__p='',__j=Array.prototype.join,print=function(){__p+=__j.call(arguments,'');};\n" + r + "return __p;\n";
        try {
            n = new Function(i.variable || "obj", "_", r)
        } catch(a) {
            throw a.source = r,
            a
        }
        if (e) return n(e, C);
        var l = function(t) {
            return n.call(this, t, C)
        };
        return l.source = "function(" + (i.variable || "obj") + "){\n" + r + "}",
        l
    },
    C.chain = function(t) {
        return C(t).chain()
    };
    var R = function(t) {
        return this._chain ? C(t).chain() : t
    };
    C.mixin(C),
    k(["pop", "push", "reverse", "shift", "sort", "splice", "unshift"],
    function(t) {
        var e = n[t];
        C.prototype[t] = function() {
            var i = this._wrapped;
            return e.apply(i, arguments),
            "shift" != t && "splice" != t || 0 !== i.length || delete i[0],
            R.call(this, i)
        }
    }),
    k(["concat", "join", "slice"],
    function(t) {
        var e = n[t];
        C.prototype[t] = function() {
            return R.call(this, e.apply(this._wrapped, arguments))
        }
    }),
    C.extend(C.prototype, {
        chain: function() {
            return this._chain = !0,
            this
        },
        value: function() {
            return this._wrapped
        }
    }),
    "function" == typeof define && define.amd && define("underscore", [],
    function() {
        return C
    })
}.call(this),
function(t, e) {
    "object" == typeof module && "object" == typeof module.exports ? module.exports = t.document ? e(t, !0) : function(t) {
        if (!t.document) throw new Error("jQuery requires a window with a document");
        return e(t)
    }: e(t)
} ("undefined" != typeof window ? window: this,
function(t, e) {
    function i(t) {
        var e = "length" in t && t.length,
        i = se.type(t);
        return "function" === i || se.isWindow(t) ? !1 : 1 === t.nodeType && e ? !0 : "array" === i || 0 === e || "number" == typeof e && e > 0 && e - 1 in t
    }
    function n(t, e, i) {
        if (se.isFunction(e)) return se.grep(t,
        function(t, n) {
            return !! e.call(t, n, t) !== i
        });
        if (e.nodeType) return se.grep(t,
        function(t) {
            return t === e !== i
        });
        if ("string" == typeof e) {
            if (de.test(e)) return se.filter(e, t, i);
            e = se.filter(e, t)
        }
        return se.grep(t,
        function(t) {
            return se.inArray(t, e) >= 0 !== i
        })
    }
    function s(t, e) {
        do t = t[e];
        while (t && 1 !== t.nodeType);
        return t
    }
    function o(t) {
        var e = _e[t] = {};
        return se.each(t.match(be) || [],
        function(t, i) {
            e[i] = !0
        }),
        e
    }
    function r() {
        fe.addEventListener ? (fe.removeEventListener("DOMContentLoaded", a, !1), t.removeEventListener("load", a, !1)) : (fe.detachEvent("onreadystatechange", a), t.detachEvent("onload", a))
    }
    function a() { (fe.addEventListener || "load" === event.type || "complete" === fe.readyState) && (r(), se.ready())
    }
    function l(t, e, i) {
        if (void 0 === i && 1 === t.nodeType) {
            var n = "data-" + e.replace(Te, "-$1").toLowerCase();
            if (i = t.getAttribute(n), "string" == typeof i) {
                try {
                    i = "true" === i ? !0 : "false" === i ? !1 : "null" === i ? null: +i + "" === i ? +i: ke.test(i) ? se.parseJSON(i) : i
                } catch(s) {}
                se.data(t, e, i)
            } else i = void 0
        }
        return i
    }
    function c(t) {
        var e;
        for (e in t) if (("data" !== e || !se.isEmptyObject(t[e])) && "toJSON" !== e) return ! 1;
        return ! 0
    }
    function h(t, e, i, n) {
        if (se.acceptData(t)) {
            var s, o, r = se.expando,
            a = t.nodeType,
            l = a ? se.cache: t,
            c = a ? t[r] : t[r] && r;
            if (c && l[c] && (n || l[c].data) || void 0 !== i || "string" != typeof e) return c || (c = a ? t[r] = X.pop() || se.guid++:r),
            l[c] || (l[c] = a ? {}: {
                toJSON: se.noop
            }),
            ("object" == typeof e || "function" == typeof e) && (n ? l[c] = se.extend(l[c], e) : l[c].data = se.extend(l[c].data, e)),
            o = l[c],
            n || (o.data || (o.data = {}), o = o.data),
            void 0 !== i && (o[se.camelCase(e)] = i),
            "string" == typeof e ? (s = o[e], null == s && (s = o[se.camelCase(e)])) : s = o,
            s
        }
    }
    function u(t, e, i) {
        if (se.acceptData(t)) {
            var n, s, o = t.nodeType,
            r = o ? se.cache: t,
            a = o ? t[se.expando] : se.expando;
            if (r[a]) {
                if (e && (n = i ? r[a] : r[a].data)) {
                    se.isArray(e) ? e = e.concat(se.map(e, se.camelCase)) : e in n ? e = [e] : (e = se.camelCase(e), e = e in n ? [e] : e.split(" ")),
                    s = e.length;
                    for (; s--;) delete n[e[s]];
                    if (i ? !c(n) : !se.isEmptyObject(n)) return
                } (i || (delete r[a].data, c(r[a]))) && (o ? se.cleanData([t], !0) : ie.deleteExpando || r != r.window ? delete r[a] : r[a] = null)
            }
        }
    }
    function d() {
        return ! 0
    }
    function p() {
        return ! 1
    }
    function f() {
        try {
            return fe.activeElement
        } catch(t) {}
    }
    function m(t) {
        var e = He.split("|"),
        i = t.createDocumentFragment();
        if (i.createElement) for (; e.length;) i.createElement(e.pop());
        return i
    }
    function g(t, e) {
        var i, n, s = 0,
        o = typeof t.getElementsByTagName !== Ce ? t.getElementsByTagName(e || "*") : typeof t.querySelectorAll !== Ce ? t.querySelectorAll(e || "*") : void 0;
        if (!o) for (o = [], i = t.childNodes || t; null != (n = i[s]); s++) ! e || se.nodeName(n, e) ? o.push(n) : se.merge(o, g(n, e));
        return void 0 === e || e && se.nodeName(t, e) ? se.merge([t], o) : o
    }
    function v(t) {
        Se.test(t.type) && (t.defaultChecked = t.checked)
    }
    function y(t, e) {
        return se.nodeName(t, "table") && se.nodeName(11 !== e.nodeType ? e: e.firstChild, "tr") ? t.getElementsByTagName("tbody")[0] || t.appendChild(t.ownerDocument.createElement("tbody")) : t
    }
    function b(t) {
        return t.type = (null !== se.find.attr(t, "type")) + "/" + t.type,
        t
    }
    function _(t) {
        var e = Ye.exec(t.type);
        return e ? t.type = e[1] : t.removeAttribute("type"),
        t
    }
    function w(t, e) {
        for (var i, n = 0; null != (i = t[n]); n++) se._data(i, "globalEval", !e || se._data(e[n], "globalEval"))
    }
    function x(t, e) {
        if (1 === e.nodeType && se.hasData(t)) {
            var i, n, s, o = se._data(t),
            r = se._data(e, o),
            a = o.events;
            if (a) {
                delete r.handle,
                r.events = {};
                for (i in a) for (n = 0, s = a[i].length; s > n; n++) se.event.add(e, i, a[i][n])
            }
            r.data && (r.data = se.extend({},
            r.data))
        }
    }
    function C(t, e) {
        var i, n, s;
        if (1 === e.nodeType) {
            if (i = e.nodeName.toLowerCase(), !ie.noCloneEvent && e[se.expando]) {
                s = se._data(e);
                for (n in s.events) se.removeEvent(e, n, s.handle);
                e.removeAttribute(se.expando)
            }
            "script" === i && e.text !== t.text ? (b(e).text = t.text, _(e)) : "object" === i ? (e.parentNode && (e.outerHTML = t.outerHTML), ie.html5Clone && t.innerHTML && !se.trim(e.innerHTML) && (e.innerHTML = t.innerHTML)) : "input" === i && Se.test(t.type) ? (e.defaultChecked = e.checked = t.checked, e.value !== t.value && (e.value = t.value)) : "option" === i ? e.defaultSelected = e.selected = t.defaultSelected: ("input" === i || "textarea" === i) && (e.defaultValue = t.defaultValue)
        }
    }
    function k(e, i) {
        var n, s = se(i.createElement(e)).appendTo(i.body),
        o = t.getDefaultComputedStyle && (n = t.getDefaultComputedStyle(s[0])) ? n.display: se.css(s[0], "display");
        return s.detach(),
        o
    }
    function T(t) {
        var e = fe,
        i = Ze[t];
        return i || (i = k(t, e), "none" !== i && i || (Qe = (Qe || se("<iframe frameborder='0' width='0' height='0'/>")).appendTo(e.documentElement), e = (Qe[0].contentWindow || Qe[0].contentDocument).document, e.write(), e.close(), i = k(t, e), Qe.detach()), Ze[t] = i),
        i
    }
    function D(t, e) {
        return {
            get: function() {
                var i = t();
                if (null != i) return i ? void delete this.get: (this.get = e).apply(this, arguments)
            }
        }
    }
    function E(t, e) {
        if (e in t) return e;
        for (var i = e.charAt(0).toUpperCase() + e.slice(1), n = e, s = di.length; s--;) if (e = di[s] + i, e in t) return e;
        return n
    }
    function M(t, e) {
        for (var i, n, s, o = [], r = 0, a = t.length; a > r; r++) n = t[r],
        n.style && (o[r] = se._data(n, "olddisplay"), i = n.style.display, e ? (o[r] || "none" !== i || (n.style.display = ""), "" === n.style.display && Me(n) && (o[r] = se._data(n, "olddisplay", T(n.nodeName)))) : (s = Me(n), (i && "none" !== i || !s) && se._data(n, "olddisplay", s ? i: se.css(n, "display"))));
        for (r = 0; a > r; r++) n = t[r],
        n.style && (e && "none" !== n.style.display && "" !== n.style.display || (n.style.display = e ? o[r] || "": "none"));
        return t
    }
    function I(t, e, i) {
        var n = li.exec(e);
        return n ? Math.max(0, n[1] - (i || 0)) + (n[2] || "px") : e
    }
    function S(t, e, i, n, s) {
        for (var o = i === (n ? "border": "content") ? 4 : "width" === e ? 1 : 0, r = 0; 4 > o; o += 2)"margin" === i && (r += se.css(t, i + Ee[o], !0, s)),
        n ? ("content" === i && (r -= se.css(t, "padding" + Ee[o], !0, s)), "margin" !== i && (r -= se.css(t, "border" + Ee[o] + "Width", !0, s))) : (r += se.css(t, "padding" + Ee[o], !0, s), "padding" !== i && (r += se.css(t, "border" + Ee[o] + "Width", !0, s)));
        return r
    }
    function P(t, e, i) {
        var n = !0,
        s = "width" === e ? t.offsetWidth: t.offsetHeight,
        o = ti(t),
        r = ie.boxSizing && "border-box" === se.css(t, "boxSizing", !1, o);
        if (0 >= s || null == s) {
            if (s = ei(t, e, o), (0 > s || null == s) && (s = t.style[e]), ni.test(s)) return s;
            n = r && (ie.boxSizingReliable() || s === t.style[e]),
            s = parseFloat(s) || 0
        }
        return s + S(t, e, i || (r ? "border": "content"), n, o) + "px"
    }
    function A(t, e, i, n, s) {
        return new A.prototype.init(t, e, i, n, s)
    }
    function N() {
        return setTimeout(function() {
            pi = void 0
        }),
        pi = se.now()
    }
    function O(t, e) {
        var i, n = {
            height: t
        },
        s = 0;
        for (e = e ? 1 : 0; 4 > s; s += 2 - e) i = Ee[s],
        n["margin" + i] = n["padding" + i] = t;
        return e && (n.opacity = n.width = t),
        n
    }
    function z(t, e, i) {
        for (var n, s = (bi[e] || []).concat(bi["*"]), o = 0, r = s.length; r > o; o++) if (n = s[o].call(i, e, t)) return n
    }
    function H(t, e, i) {
        var n, s, o, r, a, l, c, h, u = this,
        d = {},
        p = t.style,
        f = t.nodeType && Me(t),
        m = se._data(t, "fxshow");
        i.queue || (a = se._queueHooks(t, "fx"), null == a.unqueued && (a.unqueued = 0, l = a.empty.fire, a.empty.fire = function() {
            a.unqueued || l()
        }), a.unqueued++, u.always(function() {
            u.always(function() {
                a.unqueued--,
                se.queue(t, "fx").length || a.empty.fire()
            })
        })),
        1 === t.nodeType && ("height" in e || "width" in e) && (i.overflow = [p.overflow, p.overflowX, p.overflowY], c = se.css(t, "display"), h = "none" === c ? se._data(t, "olddisplay") || T(t.nodeName) : c, "inline" === h && "none" === se.css(t, "float") && (ie.inlineBlockNeedsLayout && "inline" !== T(t.nodeName) ? p.zoom = 1 : p.display = "inline-block")),
        i.overflow && (p.overflow = "hidden", ie.shrinkWrapBlocks() || u.always(function() {
            p.overflow = i.overflow[0],
            p.overflowX = i.overflow[1],
            p.overflowY = i.overflow[2]
        }));
        for (n in e) if (s = e[n], mi.exec(s)) {
            if (delete e[n], o = o || "toggle" === s, s === (f ? "hide": "show")) {
                if ("show" !== s || !m || void 0 === m[n]) continue;
                f = !0
            }
            d[n] = m && m[n] || se.style(t, n)
        } else c = void 0;
        if (se.isEmptyObject(d))"inline" === ("none" === c ? T(t.nodeName) : c) && (p.display = c);
        else {
            m ? "hidden" in m && (f = m.hidden) : m = se._data(t, "fxshow", {}),
            o && (m.hidden = !f),
            f ? se(t).show() : u.done(function() {
                se(t).hide()
            }),
            u.done(function() {
                var e;
                se._removeData(t, "fxshow");
                for (e in d) se.style(t, e, d[e])
            });
            for (n in d) r = z(f ? m[n] : 0, n, u),
            n in m || (m[n] = r.start, f && (r.end = r.start, r.start = "width" === n || "height" === n ? 1 : 0))
        }
    }
    function j(t, e) {
        var i, n, s, o, r;
        for (i in t) if (n = se.camelCase(i), s = e[n], o = t[i], se.isArray(o) && (s = o[1], o = t[i] = o[0]), i !== n && (t[n] = o, delete t[i]), r = se.cssHooks[n], r && "expand" in r) {
            o = r.expand(o),
            delete t[n];
            for (i in o) i in t || (t[i] = o[i], e[i] = s)
        } else e[n] = s
    }
    function R(t, e, i) {
        var n, s, o = 0,
        r = yi.length,
        a = se.Deferred().always(function() {
            delete l.elem
        }),
        l = function() {
            if (s) return ! 1;
            for (var e = pi || N(), i = Math.max(0, c.startTime + c.duration - e), n = i / c.duration || 0, o = 1 - n, r = 0, l = c.tweens.length; l > r; r++) c.tweens[r].run(o);
            return a.notifyWith(t, [c, o, i]),
            1 > o && l ? i: (a.resolveWith(t, [c]), !1)
        },
        c = a.promise({
            elem: t,
            props: se.extend({},
            e),
            opts: se.extend(!0, {
                specialEasing: {}
            },
            i),
            originalProperties: e,
            originalOptions: i,
            startTime: pi || N(),
            duration: i.duration,
            tweens: [],
            createTween: function(e, i) {
                var n = se.Tween(t, c.opts, e, i, c.opts.specialEasing[e] || c.opts.easing);
                return c.tweens.push(n),
                n
            },
            stop: function(e) {
                var i = 0,
                n = e ? c.tweens.length: 0;
                if (s) return this;
                for (s = !0; n > i; i++) c.tweens[i].run(1);
                return e ? a.resolveWith(t, [c, e]) : a.rejectWith(t, [c, e]),
                this
            }
        }),
        h = c.props;
        for (j(h, c.opts.specialEasing); r > o; o++) if (n = yi[o].call(c, t, h, c.opts)) return n;
        return se.map(h, z, c),
        se.isFunction(c.opts.start) && c.opts.start.call(t, c),
        se.fx.timer(se.extend(l, {
            elem: t,
            anim: c,
            queue: c.opts.queue
        })),
        c.progress(c.opts.progress).done(c.opts.done, c.opts.complete).fail(c.opts.fail).always(c.opts.always)
    }
    function $(t) {
        return function(e, i) {
            "string" != typeof e && (i = e, e = "*");
            var n, s = 0,
            o = e.toLowerCase().match(be) || [];
            if (se.isFunction(i)) for (; n = o[s++];)"+" === n.charAt(0) ? (n = n.slice(1) || "*", (t[n] = t[n] || []).unshift(i)) : (t[n] = t[n] || []).push(i)
        }
    }
    function L(t, e, i, n) {
        function s(a) {
            var l;
            return o[a] = !0,
            se.each(t[a] || [],
            function(t, a) {
                var c = a(e, i, n);
                return "string" != typeof c || r || o[c] ? r ? !(l = c) : void 0 : (e.dataTypes.unshift(c), s(c), !1)
            }),
            l
        }
        var o = {},
        r = t === Vi;
        return s(e.dataTypes[0]) || !o["*"] && s("*")
    }
    function W(t, e) {
        var i, n, s = se.ajaxSettings.flatOptions || {};
        for (n in e) void 0 !== e[n] && ((s[n] ? t: i || (i = {}))[n] = e[n]);
        return i && se.extend(!0, t, i),
        t
    }
    function F(t, e, i) {
        for (var n, s, o, r, a = t.contents,
        l = t.dataTypes;
        "*" === l[0];) l.shift(),
        void 0 === s && (s = t.mimeType || e.getResponseHeader("Content-Type"));
        if (s) for (r in a) if (a[r] && a[r].test(s)) {
            l.unshift(r);
            break
        }
        if (l[0] in i) o = l[0];
        else {
            for (r in i) {
                if (!l[0] || t.converters[r + " " + l[0]]) {
                    o = r;
                    break
                }
                n || (n = r)
            }
            o = o || n
        }
        return o ? (o !== l[0] && l.unshift(o), i[o]) : void 0
    }
    function V(t, e, i, n) {
        var s, o, r, a, l, c = {},
        h = t.dataTypes.slice();
        if (h[1]) for (r in t.converters) c[r.toLowerCase()] = t.converters[r];
        for (o = h.shift(); o;) if (t.responseFields[o] && (i[t.responseFields[o]] = e), !l && n && t.dataFilter && (e = t.dataFilter(e, t.dataType)), l = o, o = h.shift()) if ("*" === o) o = l;
        else if ("*" !== l && l !== o) {
            if (r = c[l + " " + o] || c["* " + o], !r) for (s in c) if (a = s.split(" "), a[1] === o && (r = c[l + " " + a[0]] || c["* " + a[0]])) {
                r === !0 ? r = c[s] : c[s] !== !0 && (o = a[0], h.unshift(a[1]));
                break
            }
            if (r !== !0) if (r && t["throws"]) e = r(e);
            else try {
                e = r(e)
            } catch(u) {
                return {
                    state: "parsererror",
                    error: r ? u: "No conversion from " + l + " to " + o
                }
            }
        }
        return {
            state: "success",
            data: e
        }
    }
    function B(t, e, i, n) {
        var s;
        if (se.isArray(e)) se.each(e,
        function(e, s) {
            i || Yi.test(t) ? n(t, s) : B(t + "[" + ("object" == typeof s ? e: "") + "]", s, i, n)
        });
        else if (i || "object" !== se.type(e)) n(t, e);
        else for (s in e) B(t + "[" + s + "]", e[s], i, n)
    }
    function q() {
        try {
            return new t.XMLHttpRequest
        } catch(e) {}
    }
    function U() {
        try {
            return new t.ActiveXObject("Microsoft.XMLHTTP")
        } catch(e) {}
    }
    function Y(t) {
        return se.isWindow(t) ? t: 9 === t.nodeType ? t.defaultView || t.parentWindow: !1
    }
    var X = [],
    K = X.slice,
    G = X.concat,
    J = X.push,
    Q = X.indexOf,
    Z = {},
    te = Z.toString,
    ee = Z.hasOwnProperty,
    ie = {},
    ne = "1.11.3",
    se = function(t, e) {
        return new se.fn.init(t, e)
    },
    oe = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,
    re = /^-ms-/,
    ae = /-([\da-z])/gi,
    le = function(t, e) {
        return e.toUpperCase()
    };
    se.fn = se.prototype = {
        jquery: ne,
        constructor: se,
        selector: "",
        length: 0,
        toArray: function() {
            return K.call(this)
        },
        get: function(t) {
            return null != t ? 0 > t ? this[t + this.length] : this[t] : K.call(this)
        },
        pushStack: function(t) {
            var e = se.merge(this.constructor(), t);
            return e.prevObject = this,
            e.context = this.context,
            e
        },
        each: function(t, e) {
            return se.each(this, t, e)
        },
        map: function(t) {
            return this.pushStack(se.map(this,
            function(e, i) {
                return t.call(e, i, e)
            }))
        },
        slice: function() {
            return this.pushStack(K.apply(this, arguments))
        },
        first: function() {
            return this.eq(0)
        },
        last: function() {
            return this.eq( - 1)
        },
        eq: function(t) {
            var e = this.length,
            i = +t + (0 > t ? e: 0);
            return this.pushStack(i >= 0 && e > i ? [this[i]] : [])
        },
        end: function() {
            return this.prevObject || this.constructor(null)
        },
        push: J,
        sort: X.sort,
        splice: X.splice
    },
    se.extend = se.fn.extend = function() {
        var t, e, i, n, s, o, r = arguments[0] || {},
        a = 1,
        l = arguments.length,
        c = !1;
        for ("boolean" == typeof r && (c = r, r = arguments[a] || {},
        a++), "object" == typeof r || se.isFunction(r) || (r = {}), a === l && (r = this, a--); l > a; a++) if (null != (s = arguments[a])) for (n in s) t = r[n],
        i = s[n],
        r !== i && (c && i && (se.isPlainObject(i) || (e = se.isArray(i))) ? (e ? (e = !1, o = t && se.isArray(t) ? t: []) : o = t && se.isPlainObject(t) ? t: {},
        r[n] = se.extend(c, o, i)) : void 0 !== i && (r[n] = i));
        return r
    },
    se.extend({
        expando: "jQuery" + (ne + Math.random()).replace(/\D/g, ""),
        isReady: !0,
        error: function(t) {
            throw new Error(t)
        },
        noop: function() {},
        isFunction: function(t) {
            return "function" === se.type(t)
        },
        isArray: Array.isArray ||
        function(t) {
            return "array" === se.type(t)
        },
        isWindow: function(t) {
            return null != t && t == t.window
        },
        isNumeric: function(t) {
            return ! se.isArray(t) && t - parseFloat(t) + 1 >= 0
        },
        isEmptyObject: function(t) {
            var e;
            for (e in t) return ! 1;
            return ! 0
        },
        isPlainObject: function(t) {
            var e;
            if (!t || "object" !== se.type(t) || t.nodeType || se.isWindow(t)) return ! 1;
            try {
                if (t.constructor && !ee.call(t, "constructor") && !ee.call(t.constructor.prototype, "isPrototypeOf")) return ! 1
            } catch(i) {
                return ! 1
            }
            if (ie.ownLast) for (e in t) return ee.call(t, e);
            for (e in t);
            return void 0 === e || ee.call(t, e)
        },
        type: function(t) {
            return null == t ? t + "": "object" == typeof t || "function" == typeof t ? Z[te.call(t)] || "object": typeof t
        },
        globalEval: function(e) {
            e && se.trim(e) && (t.execScript ||
            function(e) {
                t.eval.call(t, e)
            })(e)
        },
        camelCase: function(t) {
            return t.replace(re, "ms-").replace(ae, le)
        },
        nodeName: function(t, e) {
            return t.nodeName && t.nodeName.toLowerCase() === e.toLowerCase()
        },
        each: function(t, e, n) {
            var s, o = 0,
            r = t.length,
            a = i(t);
            if (n) {
                if (a) for (; r > o && (s = e.apply(t[o], n), s !== !1); o++);
                else for (o in t) if (s = e.apply(t[o], n), s === !1) break
            } else if (a) for (; r > o && (s = e.call(t[o], o, t[o]), s !== !1); o++);
            else for (o in t) if (s = e.call(t[o], o, t[o]), s === !1) break;
            return t
        },
        trim: function(t) {
            return null == t ? "": (t + "").replace(oe, "")
        },
        makeArray: function(t, e) {
            var n = e || [];
            return null != t && (i(Object(t)) ? se.merge(n, "string" == typeof t ? [t] : t) : J.call(n, t)),
            n
        },
        inArray: function(t, e, i) {
            var n;
            if (e) {
                if (Q) return Q.call(e, t, i);
                for (n = e.length, i = i ? 0 > i ? Math.max(0, n + i) : i: 0; n > i; i++) if (i in e && e[i] === t) return i
            }
            return - 1
        },
        merge: function(t, e) {
            for (var i = +e.length,
            n = 0,
            s = t.length; i > n;) t[s++] = e[n++];
            if (i !== i) for (; void 0 !== e[n];) t[s++] = e[n++];
            return t.length = s,
            t
        },
        grep: function(t, e, i) {
            for (var n, s = [], o = 0, r = t.length, a = !i; r > o; o++) n = !e(t[o], o),
            n !== a && s.push(t[o]);
            return s
        },
        map: function(t, e, n) {
            var s, o = 0,
            r = t.length,
            a = i(t),
            l = [];
            if (a) for (; r > o; o++) s = e(t[o], o, n),
            null != s && l.push(s);
            else for (o in t) s = e(t[o], o, n),
            null != s && l.push(s);
            return G.apply([], l)
        },
        guid: 1,
        proxy: function(t, e) {
            var i, n, s;
            return "string" == typeof e && (s = t[e], e = t, t = s),
            se.isFunction(t) ? (i = K.call(arguments, 2), n = function() {
                return t.apply(e || this, i.concat(K.call(arguments)))
            },
            n.guid = t.guid = t.guid || se.guid++, n) : void 0
        },
        now: function() {
            return + new Date
        },
        support: ie
    }),
    se.each("Boolean Number String Function Array Date RegExp Object Error".split(" "),
    function(t, e) {
        Z["[object " + e + "]"] = e.toLowerCase()
    });
    var ce = function(t) {
        function e(t, e, i, n) {
            var s, o, r, a, l, c, u, p, f, m;
            if ((e ? e.ownerDocument || e: L) !== A && P(e), e = e || A, i = i || [], a = e.nodeType, "string" != typeof t || !t || 1 !== a && 9 !== a && 11 !== a) return i;
            if (!n && O) {
                if (11 !== a && (s = ye.exec(t))) if (r = s[1]) {
                    if (9 === a) {
                        if (o = e.getElementById(r), !o || !o.parentNode) return i;
                        if (o.id === r) return i.push(o),
                        i
                    } else if (e.ownerDocument && (o = e.ownerDocument.getElementById(r)) && R(e, o) && o.id === r) return i.push(o),
                    i
                } else {
                    if (s[2]) return Q.apply(i, e.getElementsByTagName(t)),
                    i;
                    if ((r = s[3]) && w.getElementsByClassName) return Q.apply(i, e.getElementsByClassName(r)),
                    i
                }
                if (w.qsa && (!z || !z.test(t))) {
                    if (p = u = $, f = e, m = 1 !== a && t, 1 === a && "object" !== e.nodeName.toLowerCase()) {
                        for (c = T(t), (u = e.getAttribute("id")) ? p = u.replace(_e, "\\$&") : e.setAttribute("id", p), p = "[id='" + p + "'] ", l = c.length; l--;) c[l] = p + d(c[l]);
                        f = be.test(t) && h(e.parentNode) || e,
                        m = c.join(",")
                    }
                    if (m) try {
                        return Q.apply(i, f.querySelectorAll(m)),
                        i
                    } catch(g) {} finally {
                        u || e.removeAttribute("id")
                    }
                }
            }
            return E(t.replace(le, "$1"), e, i, n)
        }
        function i() {
            function t(i, n) {
                return e.push(i + " ") > x.cacheLength && delete t[e.shift()],
                t[i + " "] = n
            }
            var e = [];
            return t
        }
        function n(t) {
            return t[$] = !0,
            t
        }
        function s(t) {
            var e = A.createElement("div");
            try {
                return !! t(e)
            } catch(i) {
                return ! 1
            } finally {
                e.parentNode && e.parentNode.removeChild(e),
                e = null
            }
        }
        function o(t, e) {
            for (var i = t.split("|"), n = t.length; n--;) x.attrHandle[i[n]] = e
        }
        function r(t, e) {
            var i = e && t,
            n = i && 1 === t.nodeType && 1 === e.nodeType && (~e.sourceIndex || Y) - (~t.sourceIndex || Y);
            if (n) return n;
            if (i) for (; i = i.nextSibling;) if (i === e) return - 1;
            return t ? 1 : -1
        }
        function a(t) {
            return function(e) {
                var i = e.nodeName.toLowerCase();
                return "input" === i && e.type === t
            }
        }
        function l(t) {
            return function(e) {
                var i = e.nodeName.toLowerCase();
                return ("input" === i || "button" === i) && e.type === t
            }
        }
        function c(t) {
            return n(function(e) {
                return e = +e,
                n(function(i, n) {
                    for (var s, o = t([], i.length, e), r = o.length; r--;) i[s = o[r]] && (i[s] = !(n[s] = i[s]))
                })
            })
        }
        function h(t) {
            return t && "undefined" != typeof t.getElementsByTagName && t
        }
        function u() {}
        function d(t) {
            for (var e = 0,
            i = t.length,
            n = ""; i > e; e++) n += t[e].value;
            return n
        }
        function p(t, e, i) {
            var n = e.dir,
            s = i && "parentNode" === n,
            o = F++;
            return e.first ?
            function(e, i, o) {
                for (; e = e[n];) if (1 === e.nodeType || s) return t(e, i, o)
            }: function(e, i, r) {
                var a, l, c = [W, o];
                if (r) {
                    for (; e = e[n];) if ((1 === e.nodeType || s) && t(e, i, r)) return ! 0
                } else for (; e = e[n];) if (1 === e.nodeType || s) {
                    if (l = e[$] || (e[$] = {}), (a = l[n]) && a[0] === W && a[1] === o) return c[2] = a[2];
                    if (l[n] = c, c[2] = t(e, i, r)) return ! 0
                }
            }
        }
        function f(t) {
            return t.length > 1 ?
            function(e, i, n) {
                for (var s = t.length; s--;) if (!t[s](e, i, n)) return ! 1;
                return ! 0
            }: t[0]
        }
        function m(t, i, n) {
            for (var s = 0,
            o = i.length; o > s; s++) e(t, i[s], n);
            return n
        }
        function g(t, e, i, n, s) {
            for (var o, r = [], a = 0, l = t.length, c = null != e; l > a; a++)(o = t[a]) && (!i || i(o, n, s)) && (r.push(o), c && e.push(a));
            return r
        }
        function v(t, e, i, s, o, r) {
            return s && !s[$] && (s = v(s)),
            o && !o[$] && (o = v(o, r)),
            n(function(n, r, a, l) {
                var c, h, u, d = [],
                p = [],
                f = r.length,
                v = n || m(e || "*", a.nodeType ? [a] : a, []),
                y = !t || !n && e ? v: g(v, d, t, a, l),
                b = i ? o || (n ? t: f || s) ? [] : r: y;
                if (i && i(y, b, a, l), s) for (c = g(b, p), s(c, [], a, l), h = c.length; h--;)(u = c[h]) && (b[p[h]] = !(y[p[h]] = u));
                if (n) {
                    if (o || t) {
                        if (o) {
                            for (c = [], h = b.length; h--;)(u = b[h]) && c.push(y[h] = u);
                            o(null, b = [], c, l)
                        }
                        for (h = b.length; h--;)(u = b[h]) && (c = o ? te(n, u) : d[h]) > -1 && (n[c] = !(r[c] = u))
                    }
                } else b = g(b === r ? b.splice(f, b.length) : b),
                o ? o(null, r, b, l) : Q.apply(r, b)
            })
        }
        function y(t) {
            for (var e, i, n, s = t.length,
            o = x.relative[t[0].type], r = o || x.relative[" "], a = o ? 1 : 0, l = p(function(t) {
                return t === e
            },
            r, !0), c = p(function(t) {
                return te(e, t) > -1
            },
            r, !0), h = [function(t, i, n) {
                var s = !o && (n || i !== M) || ((e = i).nodeType ? l(t, i, n) : c(t, i, n));
                return e = null,
                s
            }]; s > a; a++) if (i = x.relative[t[a].type]) h = [p(f(h), i)];
            else {
                if (i = x.filter[t[a].type].apply(null, t[a].matches), i[$]) {
                    for (n = ++a; s > n && !x.relative[t[n].type]; n++);
                    return v(a > 1 && f(h), a > 1 && d(t.slice(0, a - 1).concat({
                        value: " " === t[a - 2].type ? "*": ""
                    })).replace(le, "$1"), i, n > a && y(t.slice(a, n)), s > n && y(t = t.slice(n)), s > n && d(t))
                }
                h.push(i)
            }
            return f(h)
        }
        function b(t, i) {
            var s = i.length > 0,
            o = t.length > 0,
            r = function(n, r, a, l, c) {
                var h, u, d, p = 0,
                f = "0",
                m = n && [],
                v = [],
                y = M,
                b = n || o && x.find.TAG("*", c),
                _ = W += null == y ? 1 : Math.random() || .1,
                w = b.length;
                for (c && (M = r !== A && r); f !== w && null != (h = b[f]); f++) {
                    if (o && h) {
                        for (u = 0; d = t[u++];) if (d(h, r, a)) {
                            l.push(h);
                            break
                        }
                        c && (W = _)
                    }
                    s && ((h = !d && h) && p--, n && m.push(h))
                }
                if (p += f, s && f !== p) {
                    for (u = 0; d = i[u++];) d(m, v, r, a);
                    if (n) {
                        if (p > 0) for (; f--;) m[f] || v[f] || (v[f] = G.call(l));
                        v = g(v)
                    }
                    Q.apply(l, v),
                    c && !n && v.length > 0 && p + i.length > 1 && e.uniqueSort(l)
                }
                return c && (W = _, M = y),
                m
            };
            return s ? n(r) : r
        }
        var _, w, x, C, k, T, D, E, M, I, S, P, A, N, O, z, H, j, R, $ = "sizzle" + 1 * new Date,
        L = t.document,
        W = 0,
        F = 0,
        V = i(),
        B = i(),
        q = i(),
        U = function(t, e) {
            return t === e && (S = !0),
            0
        },
        Y = 1 << 31,
        X = {}.hasOwnProperty,
        K = [],
        G = K.pop,
        J = K.push,
        Q = K.push,
        Z = K.slice,
        te = function(t, e) {
            for (var i = 0,
            n = t.length; n > i; i++) if (t[i] === e) return i;
            return - 1
        },
        ee = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",
        ie = "[\\x20\\t\\r\\n\\f]",
        ne = "(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",
        se = ne.replace("w", "w#"),
        oe = "\\[" + ie + "*(" + ne + ")(?:" + ie + "*([*^$|!~]?=)" + ie + "*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + se + "))|)" + ie + "*\\]",
        re = ":(" + ne + ")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|" + oe + ")*)|.*)\\)|)",
        ae = new RegExp(ie + "+", "g"),
        le = new RegExp("^" + ie + "+|((?:^|[^\\\\])(?:\\\\.)*)" + ie + "+$", "g"),
        ce = new RegExp("^" + ie + "*," + ie + "*"),
        he = new RegExp("^" + ie + "*([>+~]|" + ie + ")" + ie + "*"),
        ue = new RegExp("=" + ie + "*([^\\]'\"]*?)" + ie + "*\\]", "g"),
        de = new RegExp(re),
        pe = new RegExp("^" + se + "$"),
        fe = {
            ID: new RegExp("^#(" + ne + ")"),
            CLASS: new RegExp("^\\.(" + ne + ")"),
            TAG: new RegExp("^(" + ne.replace("w", "w*") + ")"),
            ATTR: new RegExp("^" + oe),
            PSEUDO: new RegExp("^" + re),
            CHILD: new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + ie + "*(even|odd|(([+-]|)(\\d*)n|)" + ie + "*(?:([+-]|)" + ie + "*(\\d+)|))" + ie + "*\\)|)", "i"),
            bool: new RegExp("^(?:" + ee + ")$", "i"),
            needsContext: new RegExp("^" + ie + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + ie + "*((?:-\\d)?\\d*)" + ie + "*\\)|)(?=[^-]|$)", "i")
        },
        me = /^(?:input|select|textarea|button)$/i,
        ge = /^h\d$/i,
        ve = /^[^{]+\{\s*\[native \w/,
        ye = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,
        be = /[+~]/,
        _e = /'|\\/g,
        we = new RegExp("\\\\([\\da-f]{1,6}" + ie + "?|(" + ie + ")|.)", "ig"),
        xe = function(t, e, i) {
            var n = "0x" + e - 65536;
            return n !== n || i ? e: 0 > n ? String.fromCharCode(n + 65536) : String.fromCharCode(n >> 10 | 55296, 1023 & n | 56320)
        },
        Ce = function() {
            P()
        };
        try {
            Q.apply(K = Z.call(L.childNodes), L.childNodes),
            K[L.childNodes.length].nodeType
        } catch(ke) {
            Q = {
                apply: K.length ?
                function(t, e) {
                    J.apply(t, Z.call(e))
                }: function(t, e) {
                    for (var i = t.length,
                    n = 0; t[i++] = e[n++];);
                    t.length = i - 1
                }
            }
        }
        w = e.support = {},
        k = e.isXML = function(t) {
            var e = t && (t.ownerDocument || t).documentElement;
            return e ? "HTML" !== e.nodeName: !1
        },
        P = e.setDocument = function(t) {
            var e, i, n = t ? t.ownerDocument || t: L;
            return n !== A && 9 === n.nodeType && n.documentElement ? (A = n, N = n.documentElement, i = n.defaultView, i && i !== i.top && (i.addEventListener ? i.addEventListener("unload", Ce, !1) : i.attachEvent && i.attachEvent("onunload", Ce)), O = !k(n), w.attributes = s(function(t) {
                return t.className = "i",
                !t.getAttribute("className")
            }), w.getElementsByTagName = s(function(t) {
                return t.appendChild(n.createComment("")),
                !t.getElementsByTagName("*").length
            }), w.getElementsByClassName = ve.test(n.getElementsByClassName), w.getById = s(function(t) {
                return N.appendChild(t).id = $,
                !n.getElementsByName || !n.getElementsByName($).length
            }), w.getById ? (x.find.ID = function(t, e) {
                if ("undefined" != typeof e.getElementById && O) {
                    var i = e.getElementById(t);
                    return i && i.parentNode ? [i] : []
                }
            },
            x.filter.ID = function(t) {
                var e = t.replace(we, xe);
                return function(t) {
                    return t.getAttribute("id") === e
                }
            }) : (delete x.find.ID, x.filter.ID = function(t) {
                var e = t.replace(we, xe);
                return function(t) {
                    var i = "undefined" != typeof t.getAttributeNode && t.getAttributeNode("id");
                    return i && i.value === e
                }
            }), x.find.TAG = w.getElementsByTagName ?
            function(t, e) {
                return "undefined" != typeof e.getElementsByTagName ? e.getElementsByTagName(t) : w.qsa ? e.querySelectorAll(t) : void 0
            }: function(t, e) {
                var i, n = [],
                s = 0,
                o = e.getElementsByTagName(t);
                if ("*" === t) {
                    for (; i = o[s++];) 1 === i.nodeType && n.push(i);
                    return n
                }
                return o
            },
            x.find.CLASS = w.getElementsByClassName &&
            function(t, e) {
                return O ? e.getElementsByClassName(t) : void 0
            },
            H = [], z = [], (w.qsa = ve.test(n.querySelectorAll)) && (s(function(t) {
                N.appendChild(t).innerHTML = "<a id='" + $ + "'></a><select id='" + $ + "-\f]' msallowcapture=''><option selected=''></option></select>",
                t.querySelectorAll("[msallowcapture^='']").length && z.push("[*^$]=" + ie + "*(?:''|\"\")"),
                t.querySelectorAll("[selected]").length || z.push("\\[" + ie + "*(?:value|" + ee + ")"),
                t.querySelectorAll("[id~=" + $ + "-]").length || z.push("~="),
                t.querySelectorAll(":checked").length || z.push(":checked"),
                t.querySelectorAll("a#" + $ + "+*").length || z.push(".#.+[+~]")
            }), s(function(t) {
                var e = n.createElement("input");
                e.setAttribute("type", "hidden"),
                t.appendChild(e).setAttribute("name", "D"),
                t.querySelectorAll("[name=d]").length && z.push("name" + ie + "*[*^$|!~]?="),
                t.querySelectorAll(":enabled").length || z.push(":enabled", ":disabled"),
                t.querySelectorAll("*,:x"),
                z.push(",.*:")
            })), (w.matchesSelector = ve.test(j = N.matches || N.webkitMatchesSelector || N.mozMatchesSelector || N.oMatchesSelector || N.msMatchesSelector)) && s(function(t) {
                w.disconnectedMatch = j.call(t, "div"),
                j.call(t, "[s!='']:x"),
                H.push("!=", re)
            }), z = z.length && new RegExp(z.join("|")), H = H.length && new RegExp(H.join("|")), e = ve.test(N.compareDocumentPosition), R = e || ve.test(N.contains) ?
            function(t, e) {
                var i = 9 === t.nodeType ? t.documentElement: t,
                n = e && e.parentNode;
                return t === n || !(!n || 1 !== n.nodeType || !(i.contains ? i.contains(n) : t.compareDocumentPosition && 16 & t.compareDocumentPosition(n)))
            }: function(t, e) {
                if (e) for (; e = e.parentNode;) if (e === t) return ! 0;
                return ! 1
            },
            U = e ?
            function(t, e) {
                if (t === e) return S = !0,
                0;
                var i = !t.compareDocumentPosition - !e.compareDocumentPosition;
                return i ? i: (i = (t.ownerDocument || t) === (e.ownerDocument || e) ? t.compareDocumentPosition(e) : 1, 1 & i || !w.sortDetached && e.compareDocumentPosition(t) === i ? t === n || t.ownerDocument === L && R(L, t) ? -1 : e === n || e.ownerDocument === L && R(L, e) ? 1 : I ? te(I, t) - te(I, e) : 0 : 4 & i ? -1 : 1)
            }: function(t, e) {
                if (t === e) return S = !0,
                0;
                var i, s = 0,
                o = t.parentNode,
                a = e.parentNode,
                l = [t],
                c = [e];
                if (!o || !a) return t === n ? -1 : e === n ? 1 : o ? -1 : a ? 1 : I ? te(I, t) - te(I, e) : 0;
                if (o === a) return r(t, e);
                for (i = t; i = i.parentNode;) l.unshift(i);
                for (i = e; i = i.parentNode;) c.unshift(i);
                for (; l[s] === c[s];) s++;
                return s ? r(l[s], c[s]) : l[s] === L ? -1 : c[s] === L ? 1 : 0
            },
            n) : A
        },
        e.matches = function(t, i) {
            return e(t, null, null, i)
        },
        e.matchesSelector = function(t, i) {
            if ((t.ownerDocument || t) !== A && P(t), i = i.replace(ue, "='$1']"), !(!w.matchesSelector || !O || H && H.test(i) || z && z.test(i))) try {
                var n = j.call(t, i);
                if (n || w.disconnectedMatch || t.document && 11 !== t.document.nodeType) return n
            } catch(s) {}
            return e(i, A, null, [t]).length > 0
        },
        e.contains = function(t, e) {
            return (t.ownerDocument || t) !== A && P(t),
            R(t, e)
        },
        e.attr = function(t, e) { (t.ownerDocument || t) !== A && P(t);
            var i = x.attrHandle[e.toLowerCase()],
            n = i && X.call(x.attrHandle, e.toLowerCase()) ? i(t, e, !O) : void 0;
            return void 0 !== n ? n: w.attributes || !O ? t.getAttribute(e) : (n = t.getAttributeNode(e)) && n.specified ? n.value: null
        },
        e.error = function(t) {
            throw new Error("Syntax error, unrecognized expression: " + t)
        },
        e.uniqueSort = function(t) {
            var e, i = [],
            n = 0,
            s = 0;
            if (S = !w.detectDuplicates, I = !w.sortStable && t.slice(0), t.sort(U), S) {
                for (; e = t[s++];) e === t[s] && (n = i.push(s));
                for (; n--;) t.splice(i[n], 1)
            }
            return I = null,
            t
        },
        C = e.getText = function(t) {
            var e, i = "",
            n = 0,
            s = t.nodeType;
            if (s) {
                if (1 === s || 9 === s || 11 === s) {
                    if ("string" == typeof t.textContent) return t.textContent;
                    for (t = t.firstChild; t; t = t.nextSibling) i += C(t)
                } else if (3 === s || 4 === s) return t.nodeValue
            } else for (; e = t[n++];) i += C(e);
            return i
        },
        x = e.selectors = {
            cacheLength: 50,
            createPseudo: n,
            match: fe,
            attrHandle: {},
            find: {},
            relative: {
                ">": {
                    dir: "parentNode",
                    first: !0
                },
                " ": {
                    dir: "parentNode"
                },
                "+": {
                    dir: "previousSibling",
                    first: !0
                },
                "~": {
                    dir: "previousSibling"
                }
            },
            preFilter: {
                ATTR: function(t) {
                    return t[1] = t[1].replace(we, xe),
                    t[3] = (t[3] || t[4] || t[5] || "").replace(we, xe),
                    "~=" === t[2] && (t[3] = " " + t[3] + " "),
                    t.slice(0, 4)
                },
                CHILD: function(t) {
                    return t[1] = t[1].toLowerCase(),
                    "nth" === t[1].slice(0, 3) ? (t[3] || e.error(t[0]), t[4] = +(t[4] ? t[5] + (t[6] || 1) : 2 * ("even" === t[3] || "odd" === t[3])), t[5] = +(t[7] + t[8] || "odd" === t[3])) : t[3] && e.error(t[0]),
                    t
                },
                PSEUDO: function(t) {
                    var e, i = !t[6] && t[2];
                    return fe.CHILD.test(t[0]) ? null: (t[3] ? t[2] = t[4] || t[5] || "": i && de.test(i) && (e = T(i, !0)) && (e = i.indexOf(")", i.length - e) - i.length) && (t[0] = t[0].slice(0, e), t[2] = i.slice(0, e)), t.slice(0, 3))
                }
            },
            filter: {
                TAG: function(t) {
                    var e = t.replace(we, xe).toLowerCase();
                    return "*" === t ?
                    function() {
                        return ! 0
                    }: function(t) {
                        return t.nodeName && t.nodeName.toLowerCase() === e
                    }
                },
                CLASS: function(t) {
                    var e = V[t + " "];
                    return e || (e = new RegExp("(^|" + ie + ")" + t + "(" + ie + "|$)")) && V(t,
                    function(t) {
                        return e.test("string" == typeof t.className && t.className || "undefined" != typeof t.getAttribute && t.getAttribute("class") || "")
                    })
                },
                ATTR: function(t, i, n) {
                    return function(s) {
                        var o = e.attr(s, t);
                        return null == o ? "!=" === i: i ? (o += "", "=" === i ? o === n: "!=" === i ? o !== n: "^=" === i ? n && 0 === o.indexOf(n) : "*=" === i ? n && o.indexOf(n) > -1 : "$=" === i ? n && o.slice( - n.length) === n: "~=" === i ? (" " + o.replace(ae, " ") + " ").indexOf(n) > -1 : "|=" === i ? o === n || o.slice(0, n.length + 1) === n + "-": !1) : !0
                    }
                },
                CHILD: function(t, e, i, n, s) {
                    var o = "nth" !== t.slice(0, 3),
                    r = "last" !== t.slice( - 4),
                    a = "of-type" === e;
                    return 1 === n && 0 === s ?
                    function(t) {
                        return !! t.parentNode
                    }: function(e, i, l) {
                        var c, h, u, d, p, f, m = o !== r ? "nextSibling": "previousSibling",
                        g = e.parentNode,
                        v = a && e.nodeName.toLowerCase(),
                        y = !l && !a;
                        if (g) {
                            if (o) {
                                for (; m;) {
                                    for (u = e; u = u[m];) if (a ? u.nodeName.toLowerCase() === v: 1 === u.nodeType) return ! 1;
                                    f = m = "only" === t && !f && "nextSibling"
                                }
                                return ! 0
                            }
                            if (f = [r ? g.firstChild: g.lastChild], r && y) {
                                for (h = g[$] || (g[$] = {}), c = h[t] || [], p = c[0] === W && c[1], d = c[0] === W && c[2], u = p && g.childNodes[p]; u = ++p && u && u[m] || (d = p = 0) || f.pop();) if (1 === u.nodeType && ++d && u === e) {
                                    h[t] = [W, p, d];
                                    break
                                }
                            } else if (y && (c = (e[$] || (e[$] = {}))[t]) && c[0] === W) d = c[1];
                            else for (; (u = ++p && u && u[m] || (d = p = 0) || f.pop()) && ((a ? u.nodeName.toLowerCase() !== v: 1 !== u.nodeType) || !++d || (y && ((u[$] || (u[$] = {}))[t] = [W, d]), u !== e)););
                            return d -= s,
                            d === n || d % n === 0 && d / n >= 0
                        }
                    }
                },
                PSEUDO: function(t, i) {
                    var s, o = x.pseudos[t] || x.setFilters[t.toLowerCase()] || e.error("unsupported pseudo: " + t);
                    return o[$] ? o(i) : o.length > 1 ? (s = [t, t, "", i], x.setFilters.hasOwnProperty(t.toLowerCase()) ? n(function(t, e) {
                        for (var n, s = o(t, i), r = s.length; r--;) n = te(t, s[r]),
                        t[n] = !(e[n] = s[r])
                    }) : function(t) {
                        return o(t, 0, s)
                    }) : o
                }
            },
            pseudos: {
                not: n(function(t) {
                    var e = [],
                    i = [],
                    s = D(t.replace(le, "$1"));
                    return s[$] ? n(function(t, e, i, n) {
                        for (var o, r = s(t, null, n, []), a = t.length; a--;)(o = r[a]) && (t[a] = !(e[a] = o))
                    }) : function(t, n, o) {
                        return e[0] = t,
                        s(e, null, o, i),
                        e[0] = null,
                        !i.pop()
                    }
                }),
                has: n(function(t) {
                    return function(i) {
                        return e(t, i).length > 0
                    }
                }),
                contains: n(function(t) {
                    return t = t.replace(we, xe),
                    function(e) {
                        return (e.textContent || e.innerText || C(e)).indexOf(t) > -1
                    }
                }),
                lang: n(function(t) {
                    return pe.test(t || "") || e.error("unsupported lang: " + t),
                    t = t.replace(we, xe).toLowerCase(),
                    function(e) {
                        var i;
                        do
                        if (i = O ? e.lang: e.getAttribute("xml:lang") || e.getAttribute("lang")) return i = i.toLowerCase(),
                        i === t || 0 === i.indexOf(t + "-");
                        while ((e = e.parentNode) && 1 === e.nodeType);
                        return ! 1
                    }
                }),
                target: function(e) {
                    var i = t.location && t.location.hash;
                    return i && i.slice(1) === e.id
                },
                root: function(t) {
                    return t === N
                },
                focus: function(t) {
                    return t === A.activeElement && (!A.hasFocus || A.hasFocus()) && !!(t.type || t.href || ~t.tabIndex)
                },
                enabled: function(t) {
                    return t.disabled === !1
                },
                disabled: function(t) {
                    return t.disabled === !0
                },
                checked: function(t) {
                    var e = t.nodeName.toLowerCase();
                    return "input" === e && !!t.checked || "option" === e && !!t.selected
                },
                selected: function(t) {
                    return t.parentNode && t.parentNode.selectedIndex,
                    t.selected === !0
                },
                empty: function(t) {
                    for (t = t.firstChild; t; t = t.nextSibling) if (t.nodeType < 6) return ! 1;
                    return ! 0
                },
                parent: function(t) {
                    return ! x.pseudos.empty(t)
                },
                header: function(t) {
                    return ge.test(t.nodeName)
                },
                input: function(t) {
                    return me.test(t.nodeName)
                },
                button: function(t) {
                    var e = t.nodeName.toLowerCase();
                    return "input" === e && "button" === t.type || "button" === e
                },
                text: function(t) {
                    var e;
                    return "input" === t.nodeName.toLowerCase() && "text" === t.type && (null == (e = t.getAttribute("type")) || "text" === e.toLowerCase())
                },
                first: c(function() {
                    return [0]
                }),
                last: c(function(t, e) {
                    return [e - 1]
                }),
                eq: c(function(t, e, i) {
                    return [0 > i ? i + e: i]
                }),
                even: c(function(t, e) {
                    for (var i = 0; e > i; i += 2) t.push(i);
                    return t
                }),
                odd: c(function(t, e) {
                    for (var i = 1; e > i; i += 2) t.push(i);
                    return t
                }),
                lt: c(function(t, e, i) {
                    for (var n = 0 > i ? i + e: i; --n >= 0;) t.push(n);
                    return t
                }),
                gt: c(function(t, e, i) {
                    for (var n = 0 > i ? i + e: i; ++n < e;) t.push(n);
                    return t
                })
            }
        },
        x.pseudos.nth = x.pseudos.eq;
        for (_ in {
            radio: !0,
            checkbox: !0,
            file: !0,
            password: !0,
            image: !0
        }) x.pseudos[_] = a(_);
        for (_ in {
            submit: !0,
            reset: !0
        }) x.pseudos[_] = l(_);
        return u.prototype = x.filters = x.pseudos,
        x.setFilters = new u,
        T = e.tokenize = function(t, i) {
            var n, s, o, r, a, l, c, h = B[t + " "];
            if (h) return i ? 0 : h.slice(0);
            for (a = t, l = [], c = x.preFilter; a;) { (!n || (s = ce.exec(a))) && (s && (a = a.slice(s[0].length) || a), l.push(o = [])),
                n = !1,
                (s = he.exec(a)) && (n = s.shift(), o.push({
                    value: n,
                    type: s[0].replace(le, " ")
                }), a = a.slice(n.length));
                for (r in x.filter) ! (s = fe[r].exec(a)) || c[r] && !(s = c[r](s)) || (n = s.shift(), o.push({
                    value: n,
                    type: r,
                    matches: s
                }), a = a.slice(n.length));
                if (!n) break
            }
            return i ? a.length: a ? e.error(t) : B(t, l).slice(0)
        },
        D = e.compile = function(t, e) {
            var i, n = [],
            s = [],
            o = q[t + " "];
            if (!o) {
                for (e || (e = T(t)), i = e.length; i--;) o = y(e[i]),
                o[$] ? n.push(o) : s.push(o);
                o = q(t, b(s, n)),
                o.selector = t
            }
            return o
        },
        E = e.select = function(t, e, i, n) {
            var s, o, r, a, l, c = "function" == typeof t && t,
            u = !n && T(t = c.selector || t);
            if (i = i || [], 1 === u.length) {
                if (o = u[0] = u[0].slice(0), o.length > 2 && "ID" === (r = o[0]).type && w.getById && 9 === e.nodeType && O && x.relative[o[1].type]) {
                    if (e = (x.find.ID(r.matches[0].replace(we, xe), e) || [])[0], !e) return i;
                    c && (e = e.parentNode),
                    t = t.slice(o.shift().value.length)
                }
                for (s = fe.needsContext.test(t) ? 0 : o.length; s--&&(r = o[s], !x.relative[a = r.type]);) if ((l = x.find[a]) && (n = l(r.matches[0].replace(we, xe), be.test(o[0].type) && h(e.parentNode) || e))) {
                    if (o.splice(s, 1), t = n.length && d(o), !t) return Q.apply(i, n),
                    i;
                    break
                }
            }
            return (c || D(t, u))(n, e, !O, i, be.test(t) && h(e.parentNode) || e),
            i
        },
        w.sortStable = $.split("").sort(U).join("") === $,
        w.detectDuplicates = !!S,
        P(),
        w.sortDetached = s(function(t) {
            return 1 & t.compareDocumentPosition(A.createElement("div"))
        }),
        s(function(t) {
            return t.innerHTML = "<a href='#'></a>",
            "#" === t.firstChild.getAttribute("href")
        }) || o("type|href|height|width",
        function(t, e, i) {
            return i ? void 0 : t.getAttribute(e, "type" === e.toLowerCase() ? 1 : 2)
        }),
        w.attributes && s(function(t) {
            return t.innerHTML = "<input/>",
            t.firstChild.setAttribute("value", ""),
            "" === t.firstChild.getAttribute("value")
        }) || o("value",
        function(t, e, i) {
            return i || "input" !== t.nodeName.toLowerCase() ? void 0 : t.defaultValue
        }),
        s(function(t) {
            return null == t.getAttribute("disabled")
        }) || o(ee,
        function(t, e, i) {
            var n;
            return i ? void 0 : t[e] === !0 ? e.toLowerCase() : (n = t.getAttributeNode(e)) && n.specified ? n.value: null
        }),
        e
    } (t);
    se.find = ce,
    se.expr = ce.selectors,
    se.expr[":"] = se.expr.pseudos,
    se.unique = ce.uniqueSort,
    se.text = ce.getText,
    se.isXMLDoc = ce.isXML,
    se.contains = ce.contains;
    var he = se.expr.match.needsContext,
    ue = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
    de = /^.[^:#\[\.,]*$/;
    se.filter = function(t, e, i) {
        var n = e[0];
        return i && (t = ":not(" + t + ")"),
        1 === e.length && 1 === n.nodeType ? se.find.matchesSelector(n, t) ? [n] : [] : se.find.matches(t, se.grep(e,
        function(t) {
            return 1 === t.nodeType
        }))
    },
    se.fn.extend({
        find: function(t) {
            var e, i = [],
            n = this,
            s = n.length;
            if ("string" != typeof t) return this.pushStack(se(t).filter(function() {
                for (e = 0; s > e; e++) if (se.contains(n[e], this)) return ! 0
            }));
            for (e = 0; s > e; e++) se.find(t, n[e], i);
            return i = this.pushStack(s > 1 ? se.unique(i) : i),
            i.selector = this.selector ? this.selector + " " + t: t,
            i
        },
        filter: function(t) {
            return this.pushStack(n(this, t || [], !1))
        },
        not: function(t) {
            return this.pushStack(n(this, t || [], !0))
        },
        is: function(t) {
            return !! n(this, "string" == typeof t && he.test(t) ? se(t) : t || [], !1).length
        }
    });
    var pe, fe = t.document,
    me = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/,
    ge = se.fn.init = function(t, e) {
        var i, n;
        if (!t) return this;
        if ("string" == typeof t) {
            if (i = "<" === t.charAt(0) && ">" === t.charAt(t.length - 1) && t.length >= 3 ? [null, t, null] : me.exec(t), !i || !i[1] && e) return ! e || e.jquery ? (e || pe).find(t) : this.constructor(e).find(t);
            if (i[1]) {
                if (e = e instanceof se ? e[0] : e, se.merge(this, se.parseHTML(i[1], e && e.nodeType ? e.ownerDocument || e: fe, !0)), ue.test(i[1]) && se.isPlainObject(e)) for (i in e) se.isFunction(this[i]) ? this[i](e[i]) : this.attr(i, e[i]);
                return this
            }
            if (n = fe.getElementById(i[2]), n && n.parentNode) {
                if (n.id !== i[2]) return pe.find(t);
                this.length = 1,
                this[0] = n
            }
            return this.context = fe,
            this.selector = t,
            this
        }
        return t.nodeType ? (this.context = this[0] = t, this.length = 1, this) : se.isFunction(t) ? "undefined" != typeof pe.ready ? pe.ready(t) : t(se) : (void 0 !== t.selector && (this.selector = t.selector, this.context = t.context), se.makeArray(t, this))
    };
    ge.prototype = se.fn,
    pe = se(fe);
    var ve = /^(?:parents|prev(?:Until|All))/,
    ye = {
        children: !0,
        contents: !0,
        next: !0,
        prev: !0
    };
    se.extend({
        dir: function(t, e, i) {
            for (var n = [], s = t[e]; s && 9 !== s.nodeType && (void 0 === i || 1 !== s.nodeType || !se(s).is(i));) 1 === s.nodeType && n.push(s),
            s = s[e];
            return n
        },
        sibling: function(t, e) {
            for (var i = []; t; t = t.nextSibling) 1 === t.nodeType && t !== e && i.push(t);
            return i
        }
    }),
    se.fn.extend({
        has: function(t) {
            var e, i = se(t, this),
            n = i.length;
            return this.filter(function() {
                for (e = 0; n > e; e++) if (se.contains(this, i[e])) return ! 0
            })
        },
        closest: function(t, e) {
            for (var i, n = 0,
            s = this.length,
            o = [], r = he.test(t) || "string" != typeof t ? se(t, e || this.context) : 0; s > n; n++) for (i = this[n]; i && i !== e; i = i.parentNode) if (i.nodeType < 11 && (r ? r.index(i) > -1 : 1 === i.nodeType && se.find.matchesSelector(i, t))) {
                o.push(i);
                break
            }
            return this.pushStack(o.length > 1 ? se.unique(o) : o)
        },
        index: function(t) {
            return t ? "string" == typeof t ? se.inArray(this[0], se(t)) : se.inArray(t.jquery ? t[0] : t, this) : this[0] && this[0].parentNode ? this.first().prevAll().length: -1
        },
        add: function(t, e) {
            return this.pushStack(se.unique(se.merge(this.get(), se(t, e))))
        },
        addBack: function(t) {
            return this.add(null == t ? this.prevObject: this.prevObject.filter(t))
        }
    }),
    se.each({
        parent: function(t) {
            var e = t.parentNode;
            return e && 11 !== e.nodeType ? e: null
        },
        parents: function(t) {
            return se.dir(t, "parentNode")
        },
        parentsUntil: function(t, e, i) {
            return se.dir(t, "parentNode", i)
        },
        next: function(t) {
            return s(t, "nextSibling")
        },
        prev: function(t) {
            return s(t, "previousSibling")
        },
        nextAll: function(t) {
            return se.dir(t, "nextSibling")
        },
        prevAll: function(t) {
            return se.dir(t, "previousSibling")
        },
        nextUntil: function(t, e, i) {
            return se.dir(t, "nextSibling", i)
        },
        prevUntil: function(t, e, i) {
            return se.dir(t, "previousSibling", i)
        },
        siblings: function(t) {
            return se.sibling((t.parentNode || {}).firstChild, t)
        },
        children: function(t) {
            return se.sibling(t.firstChild)
        },
        contents: function(t) {
            return se.nodeName(t, "iframe") ? t.contentDocument || t.contentWindow.document: se.merge([], t.childNodes)
        }
    },
    function(t, e) {
        se.fn[t] = function(i, n) {
            var s = se.map(this, e, i);
            return "Until" !== t.slice( - 5) && (n = i),
            n && "string" == typeof n && (s = se.filter(n, s)),
            this.length > 1 && (ye[t] || (s = se.unique(s)), ve.test(t) && (s = s.reverse())),
            this.pushStack(s)
        }
    });
    var be = /\S+/g,
    _e = {};
    se.Callbacks = function(t) {
        t = "string" == typeof t ? _e[t] || o(t) : se.extend({},
        t);
        var e, i, n, s, r, a, l = [],
        c = !t.once && [],
        h = function(o) {
            for (i = t.memory && o, n = !0, r = a || 0, a = 0, s = l.length, e = !0; l && s > r; r++) if (l[r].apply(o[0], o[1]) === !1 && t.stopOnFalse) {
                i = !1;
                break
            }
            e = !1,
            l && (c ? c.length && h(c.shift()) : i ? l = [] : u.disable())
        },
        u = {
            add: function() {
                if (l) {
                    var n = l.length; !
                    function o(e) {
                        se.each(e,
                        function(e, i) {
                            var n = se.type(i);
                            "function" === n ? t.unique && u.has(i) || l.push(i) : i && i.length && "string" !== n && o(i)
                        })
                    } (arguments),
                    e ? s = l.length: i && (a = n, h(i))
                }
                return this
            },
            remove: function() {
                return l && se.each(arguments,
                function(t, i) {
                    for (var n; (n = se.inArray(i, l, n)) > -1;) l.splice(n, 1),
                    e && (s >= n && s--, r >= n && r--)
                }),
                this
            },
            has: function(t) {
                return t ? se.inArray(t, l) > -1 : !(!l || !l.length)
            },
            empty: function() {
                return l = [],
                s = 0,
                this
            },
            disable: function() {
                return l = c = i = void 0,
                this
            },
            disabled: function() {
                return ! l
            },
            lock: function() {
                return c = void 0,
                i || u.disable(),
                this
            },
            locked: function() {
                return ! c
            },
            fireWith: function(t, i) {
                return ! l || n && !c || (i = i || [], i = [t, i.slice ? i.slice() : i], e ? c.push(i) : h(i)),
                this
            },
            fire: function() {
                return u.fireWith(this, arguments),
                this
            },
            fired: function() {
                return !! n
            }
        };
        return u
    },
    se.extend({
        Deferred: function(t) {
            var e = [["resolve", "done", se.Callbacks("once memory"), "resolved"], ["reject", "fail", se.Callbacks("once memory"), "rejected"], ["notify", "progress", se.Callbacks("memory")]],
            i = "pending",
            n = {
                state: function() {
                    return i
                },
                always: function() {
                    return s.done(arguments).fail(arguments),
                    this
                },
                then: function() {
                    var t = arguments;
                    return se.Deferred(function(i) {
                        se.each(e,
                        function(e, o) {
                            var r = se.isFunction(t[e]) && t[e];
                            s[o[1]](function() {
                                var t = r && r.apply(this, arguments);
                                t && se.isFunction(t.promise) ? t.promise().done(i.resolve).fail(i.reject).progress(i.notify) : i[o[0] + "With"](this === n ? i.promise() : this, r ? [t] : arguments)
                            })
                        }),
                        t = null
                    }).promise()
                },
                promise: function(t) {
                    return null != t ? se.extend(t, n) : n
                }
            },
            s = {};
            return n.pipe = n.then,
            se.each(e,
            function(t, o) {
                var r = o[2],
                a = o[3];
                n[o[1]] = r.add,
                a && r.add(function() {
                    i = a
                },
                e[1 ^ t][2].disable, e[2][2].lock),
                s[o[0]] = function() {
                    return s[o[0] + "With"](this === s ? n: this, arguments),
                    this
                },
                s[o[0] + "With"] = r.fireWith
            }),
            n.promise(s),
            t && t.call(s, s),
            s
        },
        when: function(t) {
            var e, i, n, s = 0,
            o = K.call(arguments),
            r = o.length,
            a = 1 !== r || t && se.isFunction(t.promise) ? r: 0,
            l = 1 === a ? t: se.Deferred(),
            c = function(t, i, n) {
                return function(s) {
                    i[t] = this,
                    n[t] = arguments.length > 1 ? K.call(arguments) : s,
                    n === e ? l.notifyWith(i, n) : --a || l.resolveWith(i, n)
                }
            };
            if (r > 1) for (e = new Array(r), i = new Array(r), n = new Array(r); r > s; s++) o[s] && se.isFunction(o[s].promise) ? o[s].promise().done(c(s, n, o)).fail(l.reject).progress(c(s, i, e)) : --a;
            return a || l.resolveWith(n, o),
            l.promise()
        }
    });
    var we;
    se.fn.ready = function(t) {
        return se.ready.promise().done(t),
        this
    },
    se.extend({
        isReady: !1,
        readyWait: 1,
        holdReady: function(t) {
            t ? se.readyWait++:se.ready(!0)
        },
        ready: function(t) {
            if (t === !0 ? !--se.readyWait: !se.isReady) {
                if (!fe.body) return setTimeout(se.ready);
                se.isReady = !0,
                t !== !0 && --se.readyWait > 0 || (we.resolveWith(fe, [se]), se.fn.triggerHandler && (se(fe).triggerHandler("ready"), se(fe).off("ready")))
            }
        }
    }),
    se.ready.promise = function(e) {
        if (!we) if (we = se.Deferred(), "complete" === fe.readyState) setTimeout(se.ready);
        else if (fe.addEventListener) fe.addEventListener("DOMContentLoaded", a, !1),
        t.addEventListener("load", a, !1);
        else {
            fe.attachEvent("onreadystatechange", a),
            t.attachEvent("onload", a);
            var i = !1;
            try {
                i = null == t.frameElement && fe.documentElement
            } catch(n) {}
            i && i.doScroll && !
            function s() {
                if (!se.isReady) {
                    try {
                        i.doScroll("left")
                    } catch(t) {
                        return setTimeout(s, 50)
                    }
                    r(),
                    se.ready()
                }
            } ()
        }
        return we.promise(e)
    };
    var xe, Ce = "undefined";
    for (xe in se(ie)) break;
    ie.ownLast = "0" !== xe,
    ie.inlineBlockNeedsLayout = !1,
    se(function() {
        var t, e, i, n;
        i = fe.getElementsByTagName("body")[0],
        i && i.style && (e = fe.createElement("div"), n = fe.createElement("div"), n.style.cssText = "position:absolute;border:0;width:0;height:0;top:0;left:-9999px", i.appendChild(n).appendChild(e), typeof e.style.zoom !== Ce && (e.style.cssText = "display:inline;margin:0;border:0;padding:1px;width:1px;zoom:1", ie.inlineBlockNeedsLayout = t = 3 === e.offsetWidth, t && (i.style.zoom = 1)), i.removeChild(n))
    }),
    function() {
        var t = fe.createElement("div");
        if (null == ie.deleteExpando) {
            ie.deleteExpando = !0;
            try {
                delete t.test
            } catch(e) {
                ie.deleteExpando = !1
            }
        }
        t = null
    } (),
    se.acceptData = function(t) {
        var e = se.noData[(t.nodeName + " ").toLowerCase()],
        i = +t.nodeType || 1;
        return 1 !== i && 9 !== i ? !1 : !e || e !== !0 && t.getAttribute("classid") === e
    };
    var ke = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,
    Te = /([A-Z])/g;
    se.extend({
        cache: {},
        noData: {
            "applet ": !0,
            "embed ": !0,
            "object ": "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
        },
        hasData: function(t) {
            return t = t.nodeType ? se.cache[t[se.expando]] : t[se.expando],
            !!t && !c(t)
        },
        data: function(t, e, i) {
            return h(t, e, i)
        },
        removeData: function(t, e) {
            return u(t, e)
        },
        _data: function(t, e, i) {
            return h(t, e, i, !0)
        },
        _removeData: function(t, e) {
            return u(t, e, !0)
        }
    }),
    se.fn.extend({
        data: function(t, e) {
            var i, n, s, o = this[0],
            r = o && o.attributes;
            if (void 0 === t) {
                if (this.length && (s = se.data(o), 1 === o.nodeType && !se._data(o, "parsedAttrs"))) {
                    for (i = r.length; i--;) r[i] && (n = r[i].name, 0 === n.indexOf("data-") && (n = se.camelCase(n.slice(5)), l(o, n, s[n])));
                    se._data(o, "parsedAttrs", !0)
                }
                return s
            }
            return "object" == typeof t ? this.each(function() {
                se.data(this, t)
            }) : arguments.length > 1 ? this.each(function() {
                se.data(this, t, e)
            }) : o ? l(o, t, se.data(o, t)) : void 0
        },
        removeData: function(t) {
            return this.each(function() {
                se.removeData(this, t)
            })
        }
    }),
    se.extend({
        queue: function(t, e, i) {
            var n;
            return t ? (e = (e || "fx") + "queue", n = se._data(t, e), i && (!n || se.isArray(i) ? n = se._data(t, e, se.makeArray(i)) : n.push(i)), n || []) : void 0
        },
        dequeue: function(t, e) {
            e = e || "fx";
            var i = se.queue(t, e),
            n = i.length,
            s = i.shift(),
            o = se._queueHooks(t, e),
            r = function() {
                se.dequeue(t, e)
            };
            "inprogress" === s && (s = i.shift(), n--),
            s && ("fx" === e && i.unshift("inprogress"), delete o.stop, s.call(t, r, o)),
            !n && o && o.empty.fire()
        },
        _queueHooks: function(t, e) {
            var i = e + "queueHooks";
            return se._data(t, i) || se._data(t, i, {
                empty: se.Callbacks("once memory").add(function() {
                    se._removeData(t, e + "queue"),
                    se._removeData(t, i)
                })
            })
        }
    }),
    se.fn.extend({
        queue: function(t, e) {
            var i = 2;
            return "string" != typeof t && (e = t, t = "fx", i--),
            arguments.length < i ? se.queue(this[0], t) : void 0 === e ? this: this.each(function() {
                var i = se.queue(this, t, e);
                se._queueHooks(this, t),
                "fx" === t && "inprogress" !== i[0] && se.dequeue(this, t)
            })
        },
        dequeue: function(t) {
            return this.each(function() {
                se.dequeue(this, t)
            })
        },
        clearQueue: function(t) {
            return this.queue(t || "fx", [])
        },
        promise: function(t, e) {
            var i, n = 1,
            s = se.Deferred(),
            o = this,
            r = this.length,
            a = function() {--n || s.resolveWith(o, [o])
            };
            for ("string" != typeof t && (e = t, t = void 0), t = t || "fx"; r--;) i = se._data(o[r], t + "queueHooks"),
            i && i.empty && (n++, i.empty.add(a));
            return a(),
            s.promise(e)
        }
    });
    var De = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,
    Ee = ["Top", "Right", "Bottom", "Left"],
    Me = function(t, e) {
        return t = e || t,
        "none" === se.css(t, "display") || !se.contains(t.ownerDocument, t)
    },
    Ie = se.access = function(t, e, i, n, s, o, r) {
        var a = 0,
        l = t.length,
        c = null == i;
        if ("object" === se.type(i)) {
            s = !0;
            for (a in i) se.access(t, e, a, i[a], !0, o, r)
        } else if (void 0 !== n && (s = !0, se.isFunction(n) || (r = !0), c && (r ? (e.call(t, n), e = null) : (c = e, e = function(t, e, i) {
            return c.call(se(t), i)
        })), e)) for (; l > a; a++) e(t[a], i, r ? n: n.call(t[a], a, e(t[a], i)));
        return s ? t: c ? e.call(t) : l ? e(t[0], i) : o
    },
    Se = /^(?:checkbox|radio)$/i; !
    function() {
        var t = fe.createElement("input"),
        e = fe.createElement("div"),
        i = fe.createDocumentFragment();
        if (e.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>", ie.leadingWhitespace = 3 === e.firstChild.nodeType, ie.tbody = !e.getElementsByTagName("tbody").length, ie.htmlSerialize = !!e.getElementsByTagName("link").length, ie.html5Clone = "<:nav></:nav>" !== fe.createElement("nav").cloneNode(!0).outerHTML, t.type = "checkbox", t.checked = !0, i.appendChild(t), ie.appendChecked = t.checked, e.innerHTML = "<textarea>x</textarea>", ie.noCloneChecked = !!e.cloneNode(!0).lastChild.defaultValue, i.appendChild(e), e.innerHTML = "<input type='radio' checked='checked' name='t'/>", ie.checkClone = e.cloneNode(!0).cloneNode(!0).lastChild.checked, ie.noCloneEvent = !0, e.attachEvent && (e.attachEvent("onclick",
        function() {
            ie.noCloneEvent = !1
        }), e.cloneNode(!0).click()), null == ie.deleteExpando) {
            ie.deleteExpando = !0;
            try {
                delete e.test
            } catch(n) {
                ie.deleteExpando = !1
            }
        }
    } (),
    function() {
        var e, i, n = fe.createElement("div");
        for (e in {
            submit: !0,
            change: !0,
            focusin: !0
        }) i = "on" + e,
        (ie[e + "Bubbles"] = i in t) || (n.setAttribute(i, "t"), ie[e + "Bubbles"] = n.attributes[i].expando === !1);
        n = null
    } ();
    var Pe = /^(?:input|select|textarea)$/i,
    Ae = /^key/,
    Ne = /^(?:mouse|pointer|contextmenu)|click/,
    Oe = /^(?:focusinfocus|focusoutblur)$/,
    ze = /^([^.]*)(?:\.(.+)|)$/;
    se.event = {
        global: {},
        add: function(t, e, i, n, s) {
            var o, r, a, l, c, h, u, d, p, f, m, g = se._data(t);
            if (g) {
                for (i.handler && (l = i, i = l.handler, s = l.selector), i.guid || (i.guid = se.guid++), (r = g.events) || (r = g.events = {}), (h = g.handle) || (h = g.handle = function(t) {
                    return typeof se === Ce || t && se.event.triggered === t.type ? void 0 : se.event.dispatch.apply(h.elem, arguments)
                },
                h.elem = t), e = (e || "").match(be) || [""], a = e.length; a--;) o = ze.exec(e[a]) || [],
                p = m = o[1],
                f = (o[2] || "").split(".").sort(),
                p && (c = se.event.special[p] || {},
                p = (s ? c.delegateType: c.bindType) || p, c = se.event.special[p] || {},
                u = se.extend({
                    type: p,
                    origType: m,
                    data: n,
                    handler: i,
                    guid: i.guid,
                    selector: s,
                    needsContext: s && se.expr.match.needsContext.test(s),
                    namespace: f.join(".")
                },
                l), (d = r[p]) || (d = r[p] = [], d.delegateCount = 0, c.setup && c.setup.call(t, n, f, h) !== !1 || (t.addEventListener ? t.addEventListener(p, h, !1) : t.attachEvent && t.attachEvent("on" + p, h))), c.add && (c.add.call(t, u), u.handler.guid || (u.handler.guid = i.guid)), s ? d.splice(d.delegateCount++, 0, u) : d.push(u), se.event.global[p] = !0);
                t = null
            }
        },
        remove: function(t, e, i, n, s) {
            var o, r, a, l, c, h, u, d, p, f, m, g = se.hasData(t) && se._data(t);
            if (g && (h = g.events)) {
                for (e = (e || "").match(be) || [""], c = e.length; c--;) if (a = ze.exec(e[c]) || [], p = m = a[1], f = (a[2] || "").split(".").sort(), p) {
                    for (u = se.event.special[p] || {},
                    p = (n ? u.delegateType: u.bindType) || p, d = h[p] || [], a = a[2] && new RegExp("(^|\\.)" + f.join("\\.(?:.*\\.|)") + "(\\.|$)"), l = o = d.length; o--;) r = d[o],
                    !s && m !== r.origType || i && i.guid !== r.guid || a && !a.test(r.namespace) || n && n !== r.selector && ("**" !== n || !r.selector) || (d.splice(o, 1), r.selector && d.delegateCount--, u.remove && u.remove.call(t, r));
                    l && !d.length && (u.teardown && u.teardown.call(t, f, g.handle) !== !1 || se.removeEvent(t, p, g.handle), delete h[p])
                } else for (p in h) se.event.remove(t, p + e[c], i, n, !0);
                se.isEmptyObject(h) && (delete g.handle, se._removeData(t, "events"))
            }
        },
        trigger: function(e, i, n, s) {
            var o, r, a, l, c, h, u, d = [n || fe],
            p = ee.call(e, "type") ? e.type: e,
            f = ee.call(e, "namespace") ? e.namespace.split(".") : [];
            if (a = h = n = n || fe, 3 !== n.nodeType && 8 !== n.nodeType && !Oe.test(p + se.event.triggered) && (p.indexOf(".") >= 0 && (f = p.split("."), p = f.shift(), f.sort()), r = p.indexOf(":") < 0 && "on" + p, e = e[se.expando] ? e: new se.Event(p, "object" == typeof e && e), e.isTrigger = s ? 2 : 3, e.namespace = f.join("."), e.namespace_re = e.namespace ? new RegExp("(^|\\.)" + f.join("\\.(?:.*\\.|)") + "(\\.|$)") : null, e.result = void 0, e.target || (e.target = n), i = null == i ? [e] : se.makeArray(i, [e]), c = se.event.special[p] || {},
            s || !c.trigger || c.trigger.apply(n, i) !== !1)) {
                if (!s && !c.noBubble && !se.isWindow(n)) {
                    for (l = c.delegateType || p, Oe.test(l + p) || (a = a.parentNode); a; a = a.parentNode) d.push(a),
                    h = a;
                    h === (n.ownerDocument || fe) && d.push(h.defaultView || h.parentWindow || t)
                }
                for (u = 0; (a = d[u++]) && !e.isPropagationStopped();) e.type = u > 1 ? l: c.bindType || p,
                o = (se._data(a, "events") || {})[e.type] && se._data(a, "handle"),
                o && o.apply(a, i),
                o = r && a[r],
                o && o.apply && se.acceptData(a) && (e.result = o.apply(a, i), e.result === !1 && e.preventDefault());
                if (e.type = p, !s && !e.isDefaultPrevented() && (!c._default || c._default.apply(d.pop(), i) === !1) && se.acceptData(n) && r && n[p] && !se.isWindow(n)) {
                    h = n[r],
                    h && (n[r] = null),
                    se.event.triggered = p;
                    try {
                        n[p]()
                    } catch(m) {}
                    se.event.triggered = void 0,
                    h && (n[r] = h)
                }
                return e.result
            }
        },
        dispatch: function(t) {
            t = se.event.fix(t);
            var e, i, n, s, o, r = [],
            a = K.call(arguments),
            l = (se._data(this, "events") || {})[t.type] || [],
            c = se.event.special[t.type] || {};
            if (a[0] = t, t.delegateTarget = this, !c.preDispatch || c.preDispatch.call(this, t) !== !1) {
                for (r = se.event.handlers.call(this, t, l), e = 0; (s = r[e++]) && !t.isPropagationStopped();) for (t.currentTarget = s.elem, o = 0; (n = s.handlers[o++]) && !t.isImmediatePropagationStopped();)(!t.namespace_re || t.namespace_re.test(n.namespace)) && (t.handleObj = n, t.data = n.data, i = ((se.event.special[n.origType] || {}).handle || n.handler).apply(s.elem, a), void 0 !== i && (t.result = i) === !1 && (t.preventDefault(), t.stopPropagation()));
                return c.postDispatch && c.postDispatch.call(this, t),
                t.result
            }
        },
        handlers: function(t, e) {
            var i, n, s, o, r = [],
            a = e.delegateCount,
            l = t.target;
            if (a && l.nodeType && (!t.button || "click" !== t.type)) for (; l != this; l = l.parentNode || this) if (1 === l.nodeType && (l.disabled !== !0 || "click" !== t.type)) {
                for (s = [], o = 0; a > o; o++) n = e[o],
                i = n.selector + " ",
                void 0 === s[i] && (s[i] = n.needsContext ? se(i, this).index(l) >= 0 : se.find(i, this, null, [l]).length),
                s[i] && s.push(n);
                s.length && r.push({
                    elem: l,
                    handlers: s
                })
            }
            return a < e.length && r.push({
                elem: this,
                handlers: e.slice(a)
            }),
            r
        },
        fix: function(t) {
            if (t[se.expando]) return t;
            var e, i, n, s = t.type,
            o = t,
            r = this.fixHooks[s];
            for (r || (this.fixHooks[s] = r = Ne.test(s) ? this.mouseHooks: Ae.test(s) ? this.keyHooks: {}), n = r.props ? this.props.concat(r.props) : this.props, t = new se.Event(o), e = n.length; e--;) i = n[e],
            t[i] = o[i];
            return t.target || (t.target = o.srcElement || fe),
            3 === t.target.nodeType && (t.target = t.target.parentNode),
            t.metaKey = !!t.metaKey,
            r.filter ? r.filter(t, o) : t
        },
        props: "altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),
        fixHooks: {},
        keyHooks: {
            props: "char charCode key keyCode".split(" "),
            filter: function(t, e) {
                return null == t.which && (t.which = null != e.charCode ? e.charCode: e.keyCode),
                t
            }
        },
        mouseHooks: {
            props: "button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
            filter: function(t, e) {
                var i, n, s, o = e.button,
                r = e.fromElement;
                return null == t.pageX && null != e.clientX && (n = t.target.ownerDocument || fe, s = n.documentElement, i = n.body, t.pageX = e.clientX + (s && s.scrollLeft || i && i.scrollLeft || 0) - (s && s.clientLeft || i && i.clientLeft || 0), t.pageY = e.clientY + (s && s.scrollTop || i && i.scrollTop || 0) - (s && s.clientTop || i && i.clientTop || 0)),
                !t.relatedTarget && r && (t.relatedTarget = r === t.target ? e.toElement: r),
                t.which || void 0 === o || (t.which = 1 & o ? 1 : 2 & o ? 3 : 4 & o ? 2 : 0),
                t
            }
        },
        special: {
            load: {
                noBubble: !0
            },
            focus: {
                trigger: function() {
                    if (this !== f() && this.focus) try {
                        return this.focus(),
                        !1
                    } catch(t) {}
                },
                delegateType: "focusin"
            },
            blur: {
                trigger: function() {
                    return this === f() && this.blur ? (this.blur(), !1) : void 0
                },
                delegateType: "focusout"
            },
            click: {
                trigger: function() {
                    return se.nodeName(this, "input") && "checkbox" === this.type && this.click ? (this.click(), !1) : void 0
                },
                _default: function(t) {
                    return se.nodeName(t.target, "a")
                }
            },
            beforeunload: {
                postDispatch: function(t) {
                    void 0 !== t.result && t.originalEvent && (t.originalEvent.returnValue = t.result)
                }
            }
        },
        simulate: function(t, e, i, n) {
            var s = se.extend(new se.Event, i, {
                type: t,
                isSimulated: !0,
                originalEvent: {}
            });
            n ? se.event.trigger(s, null, e) : se.event.dispatch.call(e, s),
            s.isDefaultPrevented() && i.preventDefault()
        }
    },
    se.removeEvent = fe.removeEventListener ?
    function(t, e, i) {
        t.removeEventListener && t.removeEventListener(e, i, !1)
    }: function(t, e, i) {
        var n = "on" + e;
        t.detachEvent && (typeof t[n] === Ce && (t[n] = null), t.detachEvent(n, i))
    },
    se.Event = function(t, e) {
        return this instanceof se.Event ? (t && t.type ? (this.originalEvent = t, this.type = t.type, this.isDefaultPrevented = t.defaultPrevented || void 0 === t.defaultPrevented && t.returnValue === !1 ? d: p) : this.type = t, e && se.extend(this, e), this.timeStamp = t && t.timeStamp || se.now(), void(this[se.expando] = !0)) : new se.Event(t, e)
    },
    se.Event.prototype = {
        isDefaultPrevented: p,
        isPropagationStopped: p,
        isImmediatePropagationStopped: p,
        preventDefault: function() {
            var t = this.originalEvent;
            this.isDefaultPrevented = d,
            t && (t.preventDefault ? t.preventDefault() : t.returnValue = !1)
        },
        stopPropagation: function() {
            var t = this.originalEvent;
            this.isPropagationStopped = d,
            t && (t.stopPropagation && t.stopPropagation(), t.cancelBubble = !0)
        },
        stopImmediatePropagation: function() {
            var t = this.originalEvent;
            this.isImmediatePropagationStopped = d,
            t && t.stopImmediatePropagation && t.stopImmediatePropagation(),
            this.stopPropagation()
        }
    },
    se.each({
        mouseenter: "mouseover",
        mouseleave: "mouseout",
        pointerenter: "pointerover",
        pointerleave: "pointerout"
    },
    function(t, e) {
        se.event.special[t] = {
            delegateType: e,
            bindType: e,
            handle: function(t) {
                var i, n = this,
                s = t.relatedTarget,
                o = t.handleObj;
                return (!s || s !== n && !se.contains(n, s)) && (t.type = o.origType, i = o.handler.apply(this, arguments), t.type = e),
                i
            }
        }
    }),
    ie.submitBubbles || (se.event.special.submit = {
        setup: function() {
            return se.nodeName(this, "form") ? !1 : void se.event.add(this, "click._submit keypress._submit",
            function(t) {
                var e = t.target,
                i = se.nodeName(e, "input") || se.nodeName(e, "button") ? e.form: void 0;
                i && !se._data(i, "submitBubbles") && (se.event.add(i, "submit._submit",
                function(t) {
                    t._submit_bubble = !0
                }), se._data(i, "submitBubbles", !0))
            })
        },
        postDispatch: function(t) {
            t._submit_bubble && (delete t._submit_bubble, this.parentNode && !t.isTrigger && se.event.simulate("submit", this.parentNode, t, !0))
        },
        teardown: function() {
            return se.nodeName(this, "form") ? !1 : void se.event.remove(this, "._submit")
        }
    }),
    ie.changeBubbles || (se.event.special.change = {
        setup: function() {
            return Pe.test(this.nodeName) ? (("checkbox" === this.type || "radio" === this.type) && (se.event.add(this, "propertychange._change",
            function(t) {
                "checked" === t.originalEvent.propertyName && (this._just_changed = !0)
            }), se.event.add(this, "click._change",
            function(t) {
                this._just_changed && !t.isTrigger && (this._just_changed = !1),
                se.event.simulate("change", this, t, !0)
            })), !1) : void se.event.add(this, "beforeactivate._change",
            function(t) {
                var e = t.target;
                Pe.test(e.nodeName) && !se._data(e, "changeBubbles") && (se.event.add(e, "change._change",
                function(t) { ! this.parentNode || t.isSimulated || t.isTrigger || se.event.simulate("change", this.parentNode, t, !0)
                }), se._data(e, "changeBubbles", !0))
            })
        },
        handle: function(t) {
            var e = t.target;
            return this !== e || t.isSimulated || t.isTrigger || "radio" !== e.type && "checkbox" !== e.type ? t.handleObj.handler.apply(this, arguments) : void 0
        },
        teardown: function() {
            return se.event.remove(this, "._change"),
            !Pe.test(this.nodeName)
        }
    }),
    ie.focusinBubbles || se.each({
        focus: "focusin",
        blur: "focusout"
    },
    function(t, e) {
        var i = function(t) {
            se.event.simulate(e, t.target, se.event.fix(t), !0)
        };
        se.event.special[e] = {
            setup: function() {
                var n = this.ownerDocument || this,
                s = se._data(n, e);
                s || n.addEventListener(t, i, !0),
                se._data(n, e, (s || 0) + 1)
            },
            teardown: function() {
                var n = this.ownerDocument || this,
                s = se._data(n, e) - 1;
                s ? se._data(n, e, s) : (n.removeEventListener(t, i, !0), se._removeData(n, e))
            }
        }
    }),
    se.fn.extend({
        on: function(t, e, i, n, s) {
            var o, r;
            if ("object" == typeof t) {
                "string" != typeof e && (i = i || e, e = void 0);
                for (o in t) this.on(o, e, i, t[o], s);
                return this
            }
            if (null == i && null == n ? (n = e, i = e = void 0) : null == n && ("string" == typeof e ? (n = i, i = void 0) : (n = i, i = e, e = void 0)), n === !1) n = p;
            else if (!n) return this;
            return 1 === s && (r = n, n = function(t) {
                return se().off(t),
                r.apply(this, arguments)
            },
            n.guid = r.guid || (r.guid = se.guid++)),
            this.each(function() {
                se.event.add(this, t, n, i, e)
            })
        },
        one: function(t, e, i, n) {
            return this.on(t, e, i, n, 1)
        },
        off: function(t, e, i) {
            var n, s;
            if (t && t.preventDefault && t.handleObj) return n = t.handleObj,
            se(t.delegateTarget).off(n.namespace ? n.origType + "." + n.namespace: n.origType, n.selector, n.handler),
            this;
            if ("object" == typeof t) {
                for (s in t) this.off(s, e, t[s]);
                return this
            }
            return (e === !1 || "function" == typeof e) && (i = e, e = void 0),
            i === !1 && (i = p),
            this.each(function() {
                se.event.remove(this, t, i, e)
            })
        },
        trigger: function(t, e) {
            return this.each(function() {
                se.event.trigger(t, e, this)
            })
        },
        triggerHandler: function(t, e) {
            var i = this[0];
            return i ? se.event.trigger(t, e, i, !0) : void 0
        }
    });
    var He = "abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",
    je = / jQuery\d+="(?:null|\d+)"/g,
    Re = new RegExp("<(?:" + He + ")[\\s/>]", "i"),
    $e = /^\s+/,
    Le = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
    We = /<([\w:]+)/,
    Fe = /<tbody/i,
    Ve = /<|&#?\w+;/,
    Be = /<(?:script|style|link)/i,
    qe = /checked\s*(?:[^=]|=\s*.checked.)/i,
    Ue = /^$|\/(?:java|ecma)script/i,
    Ye = /^true\/(.*)/,
    Xe = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,
    Ke = {
        option: [1, "<select multiple='multiple'>", "</select>"],
        legend: [1, "<fieldset>", "</fieldset>"],
        area: [1, "<map>", "</map>"],
        param: [1, "<object>", "</object>"],
        thead: [1, "<table>", "</table>"],
        tr: [2, "<table><tbody>", "</tbody></table>"],
        col: [2, "<table><tbody></tbody><colgroup>", "</colgroup></table>"],
        td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
        _default: ie.htmlSerialize ? [0, "", ""] : [1, "X<div>", "</div>"]
    },
    Ge = m(fe),
    Je = Ge.appendChild(fe.createElement("div"));
    Ke.optgroup = Ke.option,
    Ke.tbody = Ke.tfoot = Ke.colgroup = Ke.caption = Ke.thead,
    Ke.th = Ke.td,
    se.extend({
        clone: function(t, e, i) {
            var n, s, o, r, a, l = se.contains(t.ownerDocument, t);
            if (ie.html5Clone || se.isXMLDoc(t) || !Re.test("<" + t.nodeName + ">") ? o = t.cloneNode(!0) : (Je.innerHTML = t.outerHTML, Je.removeChild(o = Je.firstChild)), !(ie.noCloneEvent && ie.noCloneChecked || 1 !== t.nodeType && 11 !== t.nodeType || se.isXMLDoc(t))) for (n = g(o), a = g(t), r = 0; null != (s = a[r]); ++r) n[r] && C(s, n[r]);
            if (e) if (i) for (a = a || g(t), n = n || g(o), r = 0; null != (s = a[r]); r++) x(s, n[r]);
            else x(t, o);
            return n = g(o, "script"),
            n.length > 0 && w(n, !l && g(t, "script")),
            n = a = s = null,
            o
        },
        buildFragment: function(t, e, i, n) {
            for (var s, o, r, a, l, c, h, u = t.length,
            d = m(e), p = [], f = 0; u > f; f++) if (o = t[f], o || 0 === o) if ("object" === se.type(o)) se.merge(p, o.nodeType ? [o] : o);
            else if (Ve.test(o)) {
                for (a = a || d.appendChild(e.createElement("div")), l = (We.exec(o) || ["", ""])[1].toLowerCase(), h = Ke[l] || Ke._default, a.innerHTML = h[1] + o.replace(Le, "<$1></$2>") + h[2], s = h[0]; s--;) a = a.lastChild;
                if (!ie.leadingWhitespace && $e.test(o) && p.push(e.createTextNode($e.exec(o)[0])), !ie.tbody) for (o = "table" !== l || Fe.test(o) ? "<table>" !== h[1] || Fe.test(o) ? 0 : a: a.firstChild, s = o && o.childNodes.length; s--;) se.nodeName(c = o.childNodes[s], "tbody") && !c.childNodes.length && o.removeChild(c);
                for (se.merge(p, a.childNodes), a.textContent = ""; a.firstChild;) a.removeChild(a.firstChild);
                a = d.lastChild
            } else p.push(e.createTextNode(o));
            for (a && d.removeChild(a), ie.appendChecked || se.grep(g(p, "input"), v), f = 0; o = p[f++];) if ((!n || -1 === se.inArray(o, n)) && (r = se.contains(o.ownerDocument, o), a = g(d.appendChild(o), "script"), r && w(a), i)) for (s = 0; o = a[s++];) Ue.test(o.type || "") && i.push(o);
            return a = null,
            d
        },
        cleanData: function(t, e) {
            for (var i, n, s, o, r = 0,
            a = se.expando,
            l = se.cache,
            c = ie.deleteExpando,
            h = se.event.special; null != (i = t[r]); r++) if ((e || se.acceptData(i)) && (s = i[a], o = s && l[s])) {
                if (o.events) for (n in o.events) h[n] ? se.event.remove(i, n) : se.removeEvent(i, n, o.handle);
                l[s] && (delete l[s], c ? delete i[a] : typeof i.removeAttribute !== Ce ? i.removeAttribute(a) : i[a] = null, X.push(s))
            }
        }
    }),
    se.fn.extend({
        text: function(t) {
            return Ie(this,
            function(t) {
                return void 0 === t ? se.text(this) : this.empty().append((this[0] && this[0].ownerDocument || fe).createTextNode(t))
            },
            null, t, arguments.length)
        },
        append: function() {
            return this.domManip(arguments,
            function(t) {
                if (1 === this.nodeType || 11 === this.nodeType || 9 === this.nodeType) {
                    var e = y(this, t);
                    e.appendChild(t)
                }
            })
        },
        prepend: function() {
            return this.domManip(arguments,
            function(t) {
                if (1 === this.nodeType || 11 === this.nodeType || 9 === this.nodeType) {
                    var e = y(this, t);
                    e.insertBefore(t, e.firstChild)
                }
            })
        },
        before: function() {
            return this.domManip(arguments,
            function(t) {
                this.parentNode && this.parentNode.insertBefore(t, this)
            })
        },
        after: function() {
            return this.domManip(arguments,
            function(t) {
                this.parentNode && this.parentNode.insertBefore(t, this.nextSibling)
            })
        },
        remove: function(t, e) {
            for (var i, n = t ? se.filter(t, this) : this, s = 0; null != (i = n[s]); s++) e || 1 !== i.nodeType || se.cleanData(g(i)),
            i.parentNode && (e && se.contains(i.ownerDocument, i) && w(g(i, "script")), i.parentNode.removeChild(i));
            return this
        },
        empty: function() {
            for (var t, e = 0; null != (t = this[e]); e++) {
                for (1 === t.nodeType && se.cleanData(g(t, !1)); t.firstChild;) t.removeChild(t.firstChild);
                t.options && se.nodeName(t, "select") && (t.options.length = 0)
            }
            return this
        },
        clone: function(t, e) {
            return t = null == t ? !1 : t,
            e = null == e ? t: e,
            this.map(function() {
                return se.clone(this, t, e)
            })
        },
        html: function(t) {
            return Ie(this,
            function(t) {
                var e = this[0] || {},
                i = 0,
                n = this.length;
                if (void 0 === t) return 1 === e.nodeType ? e.innerHTML.replace(je, "") : void 0;
                if (! ("string" != typeof t || Be.test(t) || !ie.htmlSerialize && Re.test(t) || !ie.leadingWhitespace && $e.test(t) || Ke[(We.exec(t) || ["", ""])[1].toLowerCase()])) {
                    t = t.replace(Le, "<$1></$2>");
                    try {
                        for (; n > i; i++) e = this[i] || {},
                        1 === e.nodeType && (se.cleanData(g(e, !1)), e.innerHTML = t);
                        e = 0
                    } catch(s) {}
                }
                e && this.empty().append(t)
            },
            null, t, arguments.length)
        },
        replaceWith: function() {
            var t = arguments[0];
            return this.domManip(arguments,
            function(e) {
                t = this.parentNode,
                se.cleanData(g(this)),
                t && t.replaceChild(e, this)
            }),
            t && (t.length || t.nodeType) ? this: this.remove()
        },
        detach: function(t) {
            return this.remove(t, !0)
        },
        domManip: function(t, e) {
            t = G.apply([], t);
            var i, n, s, o, r, a, l = 0,
            c = this.length,
            h = this,
            u = c - 1,
            d = t[0],
            p = se.isFunction(d);
            if (p || c > 1 && "string" == typeof d && !ie.checkClone && qe.test(d)) return this.each(function(i) {
                var n = h.eq(i);
                p && (t[0] = d.call(this, i, n.html())),
                n.domManip(t, e)
            });
            if (c && (a = se.buildFragment(t, this[0].ownerDocument, !1, this), i = a.firstChild, 1 === a.childNodes.length && (a = i), i)) {
                for (o = se.map(g(a, "script"), b), s = o.length; c > l; l++) n = a,
                l !== u && (n = se.clone(n, !0, !0), s && se.merge(o, g(n, "script"))),
                e.call(this[l], n, l);
                if (s) for (r = o[o.length - 1].ownerDocument, se.map(o, _), l = 0; s > l; l++) n = o[l],
                Ue.test(n.type || "") && !se._data(n, "globalEval") && se.contains(r, n) && (n.src ? se._evalUrl && se._evalUrl(n.src) : se.globalEval((n.text || n.textContent || n.innerHTML || "").replace(Xe, "")));
                a = i = null
            }
            return this
        }
    }),
    se.each({
        appendTo: "append",
        prependTo: "prepend",
        insertBefore: "before",
        insertAfter: "after",
        replaceAll: "replaceWith"
    },
    function(t, e) {
        se.fn[t] = function(t) {
            for (var i, n = 0,
            s = [], o = se(t), r = o.length - 1; r >= n; n++) i = n === r ? this: this.clone(!0),
            se(o[n])[e](i),
            J.apply(s, i.get());
            return this.pushStack(s)
        }
    });
    var Qe, Ze = {}; !
    function() {
        var t;
        ie.shrinkWrapBlocks = function() {
            if (null != t) return t;
            t = !1;
            var e, i, n;
            return i = fe.getElementsByTagName("body")[0],
            i && i.style ? (e = fe.createElement("div"), n = fe.createElement("div"), n.style.cssText = "position:absolute;border:0;width:0;height:0;top:0;left:-9999px", i.appendChild(n).appendChild(e), typeof e.style.zoom !== Ce && (e.style.cssText = "-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;display:block;margin:0;border:0;padding:1px;width:1px;zoom:1", e.appendChild(fe.createElement("div")).style.width = "5px", t = 3 !== e.offsetWidth), i.removeChild(n), t) : void 0
        }
    } ();
    var ti, ei, ii = /^margin/,
    ni = new RegExp("^(" + De + ")(?!px)[a-z%]+$", "i"),
    si = /^(top|right|bottom|left)$/;
    t.getComputedStyle ? (ti = function(e) {
        return e.ownerDocument.defaultView.opener ? e.ownerDocument.defaultView.getComputedStyle(e, null) : t.getComputedStyle(e, null)
    },
    ei = function(t, e, i) {
        var n, s, o, r, a = t.style;
        return i = i || ti(t),
        r = i ? i.getPropertyValue(e) || i[e] : void 0,
        i && ("" !== r || se.contains(t.ownerDocument, t) || (r = se.style(t, e)), ni.test(r) && ii.test(e) && (n = a.width, s = a.minWidth, o = a.maxWidth, a.minWidth = a.maxWidth = a.width = r, r = i.width, a.width = n, a.minWidth = s, a.maxWidth = o)),
        void 0 === r ? r: r + ""
    }) : fe.documentElement.currentStyle && (ti = function(t) {
        return t.currentStyle
    },
    ei = function(t, e, i) {
        var n, s, o, r, a = t.style;
        return i = i || ti(t),
        r = i ? i[e] : void 0,
        null == r && a && a[e] && (r = a[e]),
        ni.test(r) && !si.test(e) && (n = a.left, s = t.runtimeStyle, o = s && s.left, o && (s.left = t.currentStyle.left), a.left = "fontSize" === e ? "1em": r, r = a.pixelLeft + "px", a.left = n, o && (s.left = o)),
        void 0 === r ? r: r + "" || "auto"
    }),
    function() {
        function e() {
            var e, i, n, s;
            i = fe.getElementsByTagName("body")[0],
            i && i.style && (e = fe.createElement("div"), n = fe.createElement("div"), n.style.cssText = "position:absolute;border:0;width:0;height:0;top:0;left:-9999px", i.appendChild(n).appendChild(e), e.style.cssText = "-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;display:block;margin-top:1%;top:1%;border:1px;padding:1px;width:4px;position:absolute", o = r = !1, l = !0, t.getComputedStyle && (o = "1%" !== (t.getComputedStyle(e, null) || {}).top, r = "4px" === (t.getComputedStyle(e, null) || {
                width: "4px"
            }).width, s = e.appendChild(fe.createElement("div")), s.style.cssText = e.style.cssText = "-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;display:block;margin:0;border:0;padding:0", s.style.marginRight = s.style.width = "0", e.style.width = "1px", l = !parseFloat((t.getComputedStyle(s, null) || {}).marginRight), e.removeChild(s)), e.innerHTML = "<table><tr><td></td><td>t</td></tr></table>", s = e.getElementsByTagName("td"), s[0].style.cssText = "margin:0;border:0;padding:0;display:none", a = 0 === s[0].offsetHeight, a && (s[0].style.display = "", s[1].style.display = "none", a = 0 === s[0].offsetHeight), i.removeChild(n))
        }
        var i, n, s, o, r, a, l;
        i = fe.createElement("div"),
        i.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>",
        s = i.getElementsByTagName("a")[0],
        n = s && s.style,
        n && (n.cssText = "float:left;opacity:.5", ie.opacity = "0.5" === n.opacity, ie.cssFloat = !!n.cssFloat, i.style.backgroundClip = "content-box", i.cloneNode(!0).style.backgroundClip = "", ie.clearCloneStyle = "content-box" === i.style.backgroundClip, ie.boxSizing = "" === n.boxSizing || "" === n.MozBoxSizing || "" === n.WebkitBoxSizing, se.extend(ie, {
            reliableHiddenOffsets: function() {
                return null == a && e(),
                a
            },
            boxSizingReliable: function() {
                return null == r && e(),
                r
            },
            pixelPosition: function() {
                return null == o && e(),
                o
            },
            reliableMarginRight: function() {
                return null == l && e(),
                l
            }
        }))
    } (),
    se.swap = function(t, e, i, n) {
        var s, o, r = {};
        for (o in e) r[o] = t.style[o],
        t.style[o] = e[o];
        s = i.apply(t, n || []);
        for (o in e) t.style[o] = r[o];
        return s
    };
    var oi = /alpha\([^)]*\)/i,
    ri = /opacity\s*=\s*([^)]*)/,
    ai = /^(none|table(?!-c[ea]).+)/,
    li = new RegExp("^(" + De + ")(.*)$", "i"),
    ci = new RegExp("^([+-])=(" + De + ")", "i"),
    hi = {
        position: "absolute",
        visibility: "hidden",
        display: "block"
    },
    ui = {
        letterSpacing: "0",
        fontWeight: "400"
    },
    di = ["Webkit", "O", "Moz", "ms"];
    se.extend({
        cssHooks: {
            opacity: {
                get: function(t, e) {
                    if (e) {
                        var i = ei(t, "opacity");
                        return "" === i ? "1": i
                    }
                }
            }
        },
        cssNumber: {
            columnCount: !0,
            fillOpacity: !0,
            flexGrow: !0,
            flexShrink: !0,
            fontWeight: !0,
            lineHeight: !0,
            opacity: !0,
            order: !0,
            orphans: !0,
            widows: !0,
            zIndex: !0,
            zoom: !0
        },
        cssProps: {
            "float": ie.cssFloat ? "cssFloat": "styleFloat"
        },
        style: function(t, e, i, n) {
            if (t && 3 !== t.nodeType && 8 !== t.nodeType && t.style) {
                var s, o, r, a = se.camelCase(e),
                l = t.style;
                if (e = se.cssProps[a] || (se.cssProps[a] = E(l, a)), r = se.cssHooks[e] || se.cssHooks[a], void 0 === i) return r && "get" in r && void 0 !== (s = r.get(t, !1, n)) ? s: l[e];
                if (o = typeof i, "string" === o && (s = ci.exec(i)) && (i = (s[1] + 1) * s[2] + parseFloat(se.css(t, e)), o = "number"), null != i && i === i && ("number" !== o || se.cssNumber[a] || (i += "px"), ie.clearCloneStyle || "" !== i || 0 !== e.indexOf("background") || (l[e] = "inherit"), !(r && "set" in r && void 0 === (i = r.set(t, i, n))))) try {
                    l[e] = i
                } catch(c) {}
            }
        },
        css: function(t, e, i, n) {
            var s, o, r, a = se.camelCase(e);
            return e = se.cssProps[a] || (se.cssProps[a] = E(t.style, a)),
            r = se.cssHooks[e] || se.cssHooks[a],
            r && "get" in r && (o = r.get(t, !0, i)),
            void 0 === o && (o = ei(t, e, n)),
            "normal" === o && e in ui && (o = ui[e]),
            "" === i || i ? (s = parseFloat(o), i === !0 || se.isNumeric(s) ? s || 0 : o) : o
        }
    }),
    se.each(["height", "width"],
    function(t, e) {
        se.cssHooks[e] = {
            get: function(t, i, n) {
                return i ? ai.test(se.css(t, "display")) && 0 === t.offsetWidth ? se.swap(t, hi,
                function() {
                    return P(t, e, n)
                }) : P(t, e, n) : void 0
            },
            set: function(t, i, n) {
                var s = n && ti(t);
                return I(t, i, n ? S(t, e, n, ie.boxSizing && "border-box" === se.css(t, "boxSizing", !1, s), s) : 0)
            }
        }
    }),
    ie.opacity || (se.cssHooks.opacity = {
        get: function(t, e) {
            return ri.test((e && t.currentStyle ? t.currentStyle.filter: t.style.filter) || "") ? .01 * parseFloat(RegExp.$1) + "": e ? "1": ""
        },
        set: function(t, e) {
            var i = t.style,
            n = t.currentStyle,
            s = se.isNumeric(e) ? "alpha(opacity=" + 100 * e + ")": "",
            o = n && n.filter || i.filter || "";
            i.zoom = 1,
            (e >= 1 || "" === e) && "" === se.trim(o.replace(oi, "")) && i.removeAttribute && (i.removeAttribute("filter"), "" === e || n && !n.filter) || (i.filter = oi.test(o) ? o.replace(oi, s) : o + " " + s)
        }
    }),
    se.cssHooks.marginRight = D(ie.reliableMarginRight,
    function(t, e) {
        return e ? se.swap(t, {
            display: "inline-block"
        },
        ei, [t, "marginRight"]) : void 0
    }),
    se.each({
        margin: "",
        padding: "",
        border: "Width"
    },
    function(t, e) {
        se.cssHooks[t + e] = {
            expand: function(i) {
                for (var n = 0,
                s = {},
                o = "string" == typeof i ? i.split(" ") : [i]; 4 > n; n++) s[t + Ee[n] + e] = o[n] || o[n - 2] || o[0];
                return s
            }
        },
        ii.test(t) || (se.cssHooks[t + e].set = I)
    }),
    se.fn.extend({
        css: function(t, e) {
            return Ie(this,
            function(t, e, i) {
                var n, s, o = {},
                r = 0;
                if (se.isArray(e)) {
                    for (n = ti(t), s = e.length; s > r; r++) o[e[r]] = se.css(t, e[r], !1, n);
                    return o
                }
                return void 0 !== i ? se.style(t, e, i) : se.css(t, e)
            },
            t, e, arguments.length > 1)
        },
        show: function() {
            return M(this, !0)
        },
        hide: function() {
            return M(this)
        },
        toggle: function(t) {
            return "boolean" == typeof t ? t ? this.show() : this.hide() : this.each(function() {
                Me(this) ? se(this).show() : se(this).hide()
            })
        }
    }),
    se.Tween = A,
    A.prototype = {
        constructor: A,
        init: function(t, e, i, n, s, o) {
            this.elem = t,
            this.prop = i,
            this.easing = s || "swing",
            this.options = e,
            this.start = this.now = this.cur(),
            this.end = n,
            this.unit = o || (se.cssNumber[i] ? "": "px")
        },
        cur: function() {
            var t = A.propHooks[this.prop];
            return t && t.get ? t.get(this) : A.propHooks._default.get(this)
        },
        run: function(t) {
            var e, i = A.propHooks[this.prop];
            return this.pos = e = this.options.duration ? se.easing[this.easing](t, this.options.duration * t, 0, 1, this.options.duration) : t,
            this.now = (this.end - this.start) * e + this.start,
            this.options.step && this.options.step.call(this.elem, this.now, this),
            i && i.set ? i.set(this) : A.propHooks._default.set(this),
            this
        }
    },
    A.prototype.init.prototype = A.prototype,
    A.propHooks = {
        _default: {
            get: function(t) {
                var e;
                return null == t.elem[t.prop] || t.elem.style && null != t.elem.style[t.prop] ? (e = se.css(t.elem, t.prop, ""), e && "auto" !== e ? e: 0) : t.elem[t.prop]
            },
            set: function(t) {
                se.fx.step[t.prop] ? se.fx.step[t.prop](t) : t.elem.style && (null != t.elem.style[se.cssProps[t.prop]] || se.cssHooks[t.prop]) ? se.style(t.elem, t.prop, t.now + t.unit) : t.elem[t.prop] = t.now
            }
        }
    },
    A.propHooks.scrollTop = A.propHooks.scrollLeft = {
        set: function(t) {
            t.elem.nodeType && t.elem.parentNode && (t.elem[t.prop] = t.now)
        }
    },
    se.easing = {
        linear: function(t) {
            return t
        },
        swing: function(t) {
            return.5 - Math.cos(t * Math.PI) / 2
        }
    },
    se.fx = A.prototype.init,
    se.fx.step = {};
    var pi, fi, mi = /^(?:toggle|show|hide)$/,
    gi = new RegExp("^(?:([+-])=|)(" + De + ")([a-z%]*)$", "i"),
    vi = /queueHooks$/,
    yi = [H],
    bi = {
        "*": [function(t, e) {
            var i = this.createTween(t, e),
            n = i.cur(),
            s = gi.exec(e),
            o = s && s[3] || (se.cssNumber[t] ? "": "px"),
            r = (se.cssNumber[t] || "px" !== o && +n) && gi.exec(se.css(i.elem, t)),
            a = 1,
            l = 20;
            if (r && r[3] !== o) {
                o = o || r[3],
                s = s || [],
                r = +n || 1;
                do a = a || ".5",
                r /= a,
                se.style(i.elem, t, r + o);
                while (a !== (a = i.cur() / n) && 1 !== a && --l)
            }
            return s && (r = i.start = +r || +n || 0, i.unit = o, i.end = s[1] ? r + (s[1] + 1) * s[2] : +s[2]),
            i
        }]
    };
    se.Animation = se.extend(R, {
        tweener: function(t, e) {
            se.isFunction(t) ? (e = t, t = ["*"]) : t = t.split(" ");
            for (var i, n = 0,
            s = t.length; s > n; n++) i = t[n],
            bi[i] = bi[i] || [],
            bi[i].unshift(e)
        },
        prefilter: function(t, e) {
            e ? yi.unshift(t) : yi.push(t)
        }
    }),
    se.speed = function(t, e, i) {
        var n = t && "object" == typeof t ? se.extend({},
        t) : {
            complete: i || !i && e || se.isFunction(t) && t,
            duration: t,
            easing: i && e || e && !se.isFunction(e) && e
        };
        return n.duration = se.fx.off ? 0 : "number" == typeof n.duration ? n.duration: n.duration in se.fx.speeds ? se.fx.speeds[n.duration] : se.fx.speeds._default,
        (null == n.queue || n.queue === !0) && (n.queue = "fx"),
        n.old = n.complete,
        n.complete = function() {
            se.isFunction(n.old) && n.old.call(this),
            n.queue && se.dequeue(this, n.queue)
        },
        n
    },
    se.fn.extend({
        fadeTo: function(t, e, i, n) {
            return this.filter(Me).css("opacity", 0).show().end().animate({
                opacity: e
            },
            t, i, n)
        },
        animate: function(t, e, i, n) {
            var s = se.isEmptyObject(t),
            o = se.speed(e, i, n),
            r = function() {
                var e = R(this, se.extend({},
                t), o); (s || se._data(this, "finish")) && e.stop(!0)
            };
            return r.finish = r,
            s || o.queue === !1 ? this.each(r) : this.queue(o.queue, r)
        },
        stop: function(t, e, i) {
            var n = function(t) {
                var e = t.stop;
                delete t.stop,
                e(i)
            };
            return "string" != typeof t && (i = e, e = t, t = void 0),
            e && t !== !1 && this.queue(t || "fx", []),
            this.each(function() {
                var e = !0,
                s = null != t && t + "queueHooks",
                o = se.timers,
                r = se._data(this);
                if (s) r[s] && r[s].stop && n(r[s]);
                else for (s in r) r[s] && r[s].stop && vi.test(s) && n(r[s]);
                for (s = o.length; s--;) o[s].elem !== this || null != t && o[s].queue !== t || (o[s].anim.stop(i), e = !1, o.splice(s, 1)); (e || !i) && se.dequeue(this, t)
            })
        },
        finish: function(t) {
            return t !== !1 && (t = t || "fx"),
            this.each(function() {
                var e, i = se._data(this),
                n = i[t + "queue"],
                s = i[t + "queueHooks"],
                o = se.timers,
                r = n ? n.length: 0;
                for (i.finish = !0, se.queue(this, t, []), s && s.stop && s.stop.call(this, !0), e = o.length; e--;) o[e].elem === this && o[e].queue === t && (o[e].anim.stop(!0), o.splice(e, 1));
                for (e = 0; r > e; e++) n[e] && n[e].finish && n[e].finish.call(this);
                delete i.finish
            })
        }
    }),
    se.each(["toggle", "show", "hide"],
    function(t, e) {
        var i = se.fn[e];
        se.fn[e] = function(t, n, s) {
            return null == t || "boolean" == typeof t ? i.apply(this, arguments) : this.animate(O(e, !0), t, n, s)
        }
    }),
    se.each({
        slideDown: O("show"),
        slideUp: O("hide"),
        slideToggle: O("toggle"),
        fadeIn: {
            opacity: "show"
        },
        fadeOut: {
            opacity: "hide"
        },
        fadeToggle: {
            opacity: "toggle"
        }
    },
    function(t, e) {
        se.fn[t] = function(t, i, n) {
            return this.animate(e, t, i, n)
        }
    }),
    se.timers = [],
    se.fx.tick = function() {
        var t, e = se.timers,
        i = 0;
        for (pi = se.now(); i < e.length; i++) t = e[i],
        t() || e[i] !== t || e.splice(i--, 1);
        e.length || se.fx.stop(),
        pi = void 0
    },
    se.fx.timer = function(t) {
        se.timers.push(t),
        t() ? se.fx.start() : se.timers.pop()
    },
    se.fx.interval = 13,
    se.fx.start = function() {
        fi || (fi = setInterval(se.fx.tick, se.fx.interval))
    },
    se.fx.stop = function() {
        clearInterval(fi),
        fi = null
    },
    se.fx.speeds = {
        slow: 600,
        fast: 200,
        _default: 400
    },
    se.fn.delay = function(t, e) {
        return t = se.fx ? se.fx.speeds[t] || t: t,
        e = e || "fx",
        this.queue(e,
        function(e, i) {
            var n = setTimeout(e, t);
            i.stop = function() {
                clearTimeout(n)
            }
        })
    },
    function() {
        var t, e, i, n, s;
        e = fe.createElement("div"),
        e.setAttribute("className", "t"),
        e.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>",
        n = e.getElementsByTagName("a")[0],
        i = fe.createElement("select"),
        s = i.appendChild(fe.createElement("option")),
        t = e.getElementsByTagName("input")[0],
        n.style.cssText = "top:1px",
        ie.getSetAttribute = "t" !== e.className,
        ie.style = /top/.test(n.getAttribute("style")),
        ie.hrefNormalized = "/a" === n.getAttribute("href"),
        ie.checkOn = !!t.value,
        ie.optSelected = s.selected,
        ie.enctype = !!fe.createElement("form").enctype,
        i.disabled = !0,
        ie.optDisabled = !s.disabled,
        t = fe.createElement("input"),
        t.setAttribute("value", ""),
        ie.input = "" === t.getAttribute("value"),
        t.value = "t",
        t.setAttribute("type", "radio"),
        ie.radioValue = "t" === t.value
    } ();
    var _i = /\r/g;
    se.fn.extend({
        val: function(t) {
            var e, i, n, s = this[0]; {
                if (arguments.length) return n = se.isFunction(t),
                this.each(function(i) {
                    var s;
                    1 === this.nodeType && (s = n ? t.call(this, i, se(this).val()) : t, null == s ? s = "": "number" == typeof s ? s += "": se.isArray(s) && (s = se.map(s,
                    function(t) {
                        return null == t ? "": t + ""
                    })), e = se.valHooks[this.type] || se.valHooks[this.nodeName.toLowerCase()], e && "set" in e && void 0 !== e.set(this, s, "value") || (this.value = s))
                });
                if (s) return e = se.valHooks[s.type] || se.valHooks[s.nodeName.toLowerCase()],
                e && "get" in e && void 0 !== (i = e.get(s, "value")) ? i: (i = s.value, "string" == typeof i ? i.replace(_i, "") : null == i ? "": i)
            }
        }
    }),
    se.extend({
        valHooks: {
            option: {
                get: function(t) {
                    var e = se.find.attr(t, "value");
                    return null != e ? e: se.trim(se.text(t))
                }
            },
            select: {
                get: function(t) {
                    for (var e, i, n = t.options,
                    s = t.selectedIndex,
                    o = "select-one" === t.type || 0 > s,
                    r = o ? null: [], a = o ? s + 1 : n.length, l = 0 > s ? a: o ? s: 0; a > l; l++) if (i = n[l], !(!i.selected && l !== s || (ie.optDisabled ? i.disabled: null !== i.getAttribute("disabled")) || i.parentNode.disabled && se.nodeName(i.parentNode, "optgroup"))) {
                        if (e = se(i).val(), o) return e;
                        r.push(e)
                    }
                    return r
                },
                set: function(t, e) {
                    for (var i, n, s = t.options,
                    o = se.makeArray(e), r = s.length; r--;) if (n = s[r], se.inArray(se.valHooks.option.get(n), o) >= 0) try {
                        n.selected = i = !0
                    } catch(a) {
                        n.scrollHeight
                    } else n.selected = !1;
                    return i || (t.selectedIndex = -1),
                    s
                }
            }
        }
    }),
    se.each(["radio", "checkbox"],
    function() {
        se.valHooks[this] = {
            set: function(t, e) {
                return se.isArray(e) ? t.checked = se.inArray(se(t).val(), e) >= 0 : void 0
            }
        },
        ie.checkOn || (se.valHooks[this].get = function(t) {
            return null === t.getAttribute("value") ? "on": t.value
        })
    });
    var wi, xi, Ci = se.expr.attrHandle,
    ki = /^(?:checked|selected)$/i,
    Ti = ie.getSetAttribute,
    Di = ie.input;
    se.fn.extend({
        attr: function(t, e) {
            return Ie(this, se.attr, t, e, arguments.length > 1)
        },
        removeAttr: function(t) {
            return this.each(function() {
                se.removeAttr(this, t)
            })
        }
    }),
    se.extend({
        attr: function(t, e, i) {
            var n, s, o = t.nodeType;
            if (t && 3 !== o && 8 !== o && 2 !== o) return typeof t.getAttribute === Ce ? se.prop(t, e, i) : (1 === o && se.isXMLDoc(t) || (e = e.toLowerCase(), n = se.attrHooks[e] || (se.expr.match.bool.test(e) ? xi: wi)), void 0 === i ? n && "get" in n && null !== (s = n.get(t, e)) ? s: (s = se.find.attr(t, e), null == s ? void 0 : s) : null !== i ? n && "set" in n && void 0 !== (s = n.set(t, i, e)) ? s: (t.setAttribute(e, i + ""), i) : void se.removeAttr(t, e))
        },
        removeAttr: function(t, e) {
            var i, n, s = 0,
            o = e && e.match(be);
            if (o && 1 === t.nodeType) for (; i = o[s++];) n = se.propFix[i] || i,
            se.expr.match.bool.test(i) ? Di && Ti || !ki.test(i) ? t[n] = !1 : t[se.camelCase("default-" + i)] = t[n] = !1 : se.attr(t, i, ""),
            t.removeAttribute(Ti ? i: n)
        },
        attrHooks: {
            type: {
                set: function(t, e) {
                    if (!ie.radioValue && "radio" === e && se.nodeName(t, "input")) {
                        var i = t.value;
                        return t.setAttribute("type", e),
                        i && (t.value = i),
                        e
                    }
                }
            }
        }
    }),
    xi = {
        set: function(t, e, i) {
            return e === !1 ? se.removeAttr(t, i) : Di && Ti || !ki.test(i) ? t.setAttribute(!Ti && se.propFix[i] || i, i) : t[se.camelCase("default-" + i)] = t[i] = !0,
            i
        }
    },
    se.each(se.expr.match.bool.source.match(/\w+/g),
    function(t, e) {
        var i = Ci[e] || se.find.attr;
        Ci[e] = Di && Ti || !ki.test(e) ?
        function(t, e, n) {
            var s, o;
            return n || (o = Ci[e], Ci[e] = s, s = null != i(t, e, n) ? e.toLowerCase() : null, Ci[e] = o),
            s
        }: function(t, e, i) {
            return i ? void 0 : t[se.camelCase("default-" + e)] ? e.toLowerCase() : null
        }
    }),
    Di && Ti || (se.attrHooks.value = {
        set: function(t, e, i) {
            return se.nodeName(t, "input") ? void(t.defaultValue = e) : wi && wi.set(t, e, i)
        }
    }),
    Ti || (wi = {
        set: function(t, e, i) {
            var n = t.getAttributeNode(i);
            return n || t.setAttributeNode(n = t.ownerDocument.createAttribute(i)),
            n.value = e += "",
            "value" === i || e === t.getAttribute(i) ? e: void 0
        }
    },
    Ci.id = Ci.name = Ci.coords = function(t, e, i) {
        var n;
        return i ? void 0 : (n = t.getAttributeNode(e)) && "" !== n.value ? n.value: null
    },
    se.valHooks.button = {
        get: function(t, e) {
            var i = t.getAttributeNode(e);
            return i && i.specified ? i.value: void 0
        },
        set: wi.set
    },
    se.attrHooks.contenteditable = {
        set: function(t, e, i) {
            wi.set(t, "" === e ? !1 : e, i)
        }
    },
    se.each(["width", "height"],
    function(t, e) {
        se.attrHooks[e] = {
            set: function(t, i) {
                return "" === i ? (t.setAttribute(e, "auto"), i) : void 0
            }
        }
    })),
    ie.style || (se.attrHooks.style = {
        get: function(t) {
            return t.style.cssText || void 0
        },
        set: function(t, e) {
            return t.style.cssText = e + ""
        }
    });
    var Ei = /^(?:input|select|textarea|button|object)$/i,
    Mi = /^(?:a|area)$/i;
    se.fn.extend({
        prop: function(t, e) {
            return Ie(this, se.prop, t, e, arguments.length > 1)
        },
        removeProp: function(t) {
            return t = se.propFix[t] || t,
            this.each(function() {
                try {
                    this[t] = void 0,
                    delete this[t]
                } catch(e) {}
            })
        }
    }),
    se.extend({
        propFix: {
            "for": "htmlFor",
            "class": "className"
        },
        prop: function(t, e, i) {
            var n, s, o, r = t.nodeType;
            if (t && 3 !== r && 8 !== r && 2 !== r) return o = 1 !== r || !se.isXMLDoc(t),
            o && (e = se.propFix[e] || e, s = se.propHooks[e]),
            void 0 !== i ? s && "set" in s && void 0 !== (n = s.set(t, i, e)) ? n: t[e] = i: s && "get" in s && null !== (n = s.get(t, e)) ? n: t[e]
        },
        propHooks: {
            tabIndex: {
                get: function(t) {
                    var e = se.find.attr(t, "tabindex");
                    return e ? parseInt(e, 10) : Ei.test(t.nodeName) || Mi.test(t.nodeName) && t.href ? 0 : -1
                }
            }
        }
    }),
    ie.hrefNormalized || se.each(["href", "src"],
    function(t, e) {
        se.propHooks[e] = {
            get: function(t) {
                return t.getAttribute(e, 4)
            }
        }
    }),
    ie.optSelected || (se.propHooks.selected = {
        get: function(t) {
            var e = t.parentNode;
            return e && (e.selectedIndex, e.parentNode && e.parentNode.selectedIndex),
            null
        }
    }),
    se.each(["tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable"],
    function() {
        se.propFix[this.toLowerCase()] = this
    }),
    ie.enctype || (se.propFix.enctype = "encoding");
    var Ii = /[\t\r\n\f]/g;
    se.fn.extend({
        addClass: function(t) {
            var e, i, n, s, o, r, a = 0,
            l = this.length,
            c = "string" == typeof t && t;
            if (se.isFunction(t)) return this.each(function(e) {
                se(this).addClass(t.call(this, e, this.className))
            });
            if (c) for (e = (t || "").match(be) || []; l > a; a++) if (i = this[a], n = 1 === i.nodeType && (i.className ? (" " + i.className + " ").replace(Ii, " ") : " ")) {
                for (o = 0; s = e[o++];) n.indexOf(" " + s + " ") < 0 && (n += s + " ");
                r = se.trim(n),
                i.className !== r && (i.className = r)
            }
            return this
        },
        removeClass: function(t) {
            var e, i, n, s, o, r, a = 0,
            l = this.length,
            c = 0 === arguments.length || "string" == typeof t && t;
            if (se.isFunction(t)) return this.each(function(e) {
                se(this).removeClass(t.call(this, e, this.className))
            });
            if (c) for (e = (t || "").match(be) || []; l > a; a++) if (i = this[a], n = 1 === i.nodeType && (i.className ? (" " + i.className + " ").replace(Ii, " ") : "")) {
                for (o = 0; s = e[o++];) for (; n.indexOf(" " + s + " ") >= 0;) n = n.replace(" " + s + " ", " ");
                r = t ? se.trim(n) : "",
                i.className !== r && (i.className = r)
            }
            return this
        },
        toggleClass: function(t, e) {
            var i = typeof t;
            return "boolean" == typeof e && "string" === i ? e ? this.addClass(t) : this.removeClass(t) : this.each(se.isFunction(t) ?
            function(i) {
                se(this).toggleClass(t.call(this, i, this.className, e), e)
            }: function() {
                if ("string" === i) for (var e, n = 0,
                s = se(this), o = t.match(be) || []; e = o[n++];) s.hasClass(e) ? s.removeClass(e) : s.addClass(e);
                else(i === Ce || "boolean" === i) && (this.className && se._data(this, "__className__", this.className), this.className = this.className || t === !1 ? "": se._data(this, "__className__") || "")
            })
        },
        hasClass: function(t) {
            for (var e = " " + t + " ",
            i = 0,
            n = this.length; n > i; i++) if (1 === this[i].nodeType && (" " + this[i].className + " ").replace(Ii, " ").indexOf(e) >= 0) return ! 0;
            return ! 1
        }
    }),
    se.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "),
    function(t, e) {
        se.fn[e] = function(t, i) {
            return arguments.length > 0 ? this.on(e, null, t, i) : this.trigger(e)
        }
    }),
    se.fn.extend({
        hover: function(t, e) {
            return this.mouseenter(t).mouseleave(e || t)
        },
        bind: function(t, e, i) {
            return this.on(t, null, e, i)
        },
        unbind: function(t, e) {
            return this.off(t, null, e)
        },
        delegate: function(t, e, i, n) {
            return this.on(e, t, i, n)
        },
        undelegate: function(t, e, i) {
            return 1 === arguments.length ? this.off(t, "**") : this.off(e, t || "**", i)
        }
    });
    var Si = se.now(),
    Pi = /\?/,
    Ai = /(,)|(\[|{)|(}|])|"(?:[^"\\\r\n]|\\["\\\/bfnrt]|\\u[\da-fA-F]{4})*"\s*:?|true|false|null|-?(?!0\d)\d+(?:\.\d+|)(?:[eE][+-]?\d+|)/g;
    se.parseJSON = function(e) {
        if (t.JSON && t.JSON.parse) return t.JSON.parse(e + "");
        var i, n = null,
        s = se.trim(e + "");
        return s && !se.trim(s.replace(Ai,
        function(t, e, s, o) {
            return i && e && (n = 0),
            0 === n ? t: (i = s || e, n += !o - !s, "")
        })) ? Function("return " + s)() : se.error("Invalid JSON: " + e)
    },
    se.parseXML = function(e) {
        var i, n;
        if (!e || "string" != typeof e) return null;
        try {
            t.DOMParser ? (n = new DOMParser, i = n.parseFromString(e, "text/xml")) : (i = new ActiveXObject("Microsoft.XMLDOM"), i.async = "false", i.loadXML(e))
        } catch(s) {
            i = void 0
        }
        return i && i.documentElement && !i.getElementsByTagName("parsererror").length || se.error("Invalid XML: " + e),
        i
    };
    var Ni, Oi, zi = /#.*$/,
    Hi = /([?&])_=[^&]*/,
    ji = /^(.*?):[ \t]*([^\r\n]*)\r?$/gm,
    Ri = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/,
    $i = /^(?:GET|HEAD)$/,
    Li = /^\/\//,
    Wi = /^([\w.+-]+:)(?:\/\/(?:[^\/?#]*@|)([^\/?#:]*)(?::(\d+)|)|)/,
    Fi = {},
    Vi = {},
    Bi = "*/".concat("*");
    try {
        Oi = location.href
    } catch(qi) {
        Oi = fe.createElement("a"),
        Oi.href = "",
        Oi = Oi.href
    }
    Ni = Wi.exec(Oi.toLowerCase()) || [],
    se.extend({
        active: 0,
        lastModified: {},
        etag: {},
        ajaxSettings: {
            url: Oi,
            type: "GET",
            isLocal: Ri.test(Ni[1]),
            global: !0,
            processData: !0,
            async: !0,
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            accepts: {
                "*": Bi,
                text: "text/plain",
                html: "text/html",
                xml: "application/xml, text/xml",
                json: "application/json, text/javascript"
            },
            contents: {
                xml: /xml/,
                html: /html/,
                json: /json/
            },
            responseFields: {
                xml: "responseXML",
                text: "responseText",
                json: "responseJSON"
            },
            converters: {
                "* text": String,
                "text html": !0,
                "text json": se.parseJSON,
                "text xml": se.parseXML
            },
            flatOptions: {
                url: !0,
                context: !0
            }
        },
        ajaxSetup: function(t, e) {
            return e ? W(W(t, se.ajaxSettings), e) : W(se.ajaxSettings, t)
        },
        ajaxPrefilter: $(Fi),
        ajaxTransport: $(Vi),
        ajax: function(t, e) {
            function i(t, e, i, n) {
                var s, h, v, y, _, x = e;
                2 !== b && (b = 2, a && clearTimeout(a), c = void 0, r = n || "", w.readyState = t > 0 ? 4 : 0, s = t >= 200 && 300 > t || 304 === t, i && (y = F(u, w, i)), y = V(u, y, w, s), s ? (u.ifModified && (_ = w.getResponseHeader("Last-Modified"), _ && (se.lastModified[o] = _), _ = w.getResponseHeader("etag"), _ && (se.etag[o] = _)), 204 === t || "HEAD" === u.type ? x = "nocontent": 304 === t ? x = "notmodified": (x = y.state, h = y.data, v = y.error, s = !v)) : (v = x, (t || !x) && (x = "error", 0 > t && (t = 0))), w.status = t, w.statusText = (e || x) + "", s ? f.resolveWith(d, [h, x, w]) : f.rejectWith(d, [w, x, v]), w.statusCode(g), g = void 0, l && p.trigger(s ? "ajaxSuccess": "ajaxError", [w, u, s ? h: v]), m.fireWith(d, [w, x]), l && (p.trigger("ajaxComplete", [w, u]), --se.active || se.event.trigger("ajaxStop")))
            }
            "object" == typeof t && (e = t, t = void 0),
            e = e || {};
            var n, s, o, r, a, l, c, h, u = se.ajaxSetup({},
            e),
            d = u.context || u,
            p = u.context && (d.nodeType || d.jquery) ? se(d) : se.event,
            f = se.Deferred(),
            m = se.Callbacks("once memory"),
            g = u.statusCode || {},
            v = {},
            y = {},
            b = 0,
            _ = "canceled",
            w = {
                readyState: 0,
                getResponseHeader: function(t) {
                    var e;
                    if (2 === b) {
                        if (!h) for (h = {}; e = ji.exec(r);) h[e[1].toLowerCase()] = e[2];
                        e = h[t.toLowerCase()]
                    }
                    return null == e ? null: e
                },
                getAllResponseHeaders: function() {
                    return 2 === b ? r: null
                },
                setRequestHeader: function(t, e) {
                    var i = t.toLowerCase();
                    return b || (t = y[i] = y[i] || t, v[t] = e),
                    this
                },
                overrideMimeType: function(t) {
                    return b || (u.mimeType = t),
                    this
                },
                statusCode: function(t) {
                    var e;
                    if (t) if (2 > b) for (e in t) g[e] = [g[e], t[e]];
                    else w.always(t[w.status]);
                    return this
                },
                abort: function(t) {
                    var e = t || _;
                    return c && c.abort(e),
                    i(0, e),
                    this
                }
            };
            if (f.promise(w).complete = m.add, w.success = w.done, w.error = w.fail, u.url = ((t || u.url || Oi) + "").replace(zi, "").replace(Li, Ni[1] + "//"), u.type = e.method || e.type || u.method || u.type, u.dataTypes = se.trim(u.dataType || "*").toLowerCase().match(be) || [""], null == u.crossDomain && (n = Wi.exec(u.url.toLowerCase()), u.crossDomain = !(!n || n[1] === Ni[1] && n[2] === Ni[2] && (n[3] || ("http:" === n[1] ? "80": "443")) === (Ni[3] || ("http:" === Ni[1] ? "80": "443")))), u.data && u.processData && "string" != typeof u.data && (u.data = se.param(u.data, u.traditional)), L(Fi, u, e, w), 2 === b) return w;
            l = se.event && u.global,
            l && 0 === se.active++&&se.event.trigger("ajaxStart"),
            u.type = u.type.toUpperCase(),
            u.hasContent = !$i.test(u.type),
            o = u.url,
            u.hasContent || (u.data && (o = u.url += (Pi.test(o) ? "&": "?") + u.data, delete u.data), u.cache === !1 && (u.url = Hi.test(o) ? o.replace(Hi, "$1_=" + Si++) : o + (Pi.test(o) ? "&": "?") + "_=" + Si++)),
            u.ifModified && (se.lastModified[o] && w.setRequestHeader("If-Modified-Since", se.lastModified[o]), se.etag[o] && w.setRequestHeader("If-None-Match", se.etag[o])),
            (u.data && u.hasContent && u.contentType !== !1 || e.contentType) && w.setRequestHeader("Content-Type", u.contentType),
            w.setRequestHeader("Accept", u.dataTypes[0] && u.accepts[u.dataTypes[0]] ? u.accepts[u.dataTypes[0]] + ("*" !== u.dataTypes[0] ? ", " + Bi + "; q=0.01": "") : u.accepts["*"]);
            for (s in u.headers) w.setRequestHeader(s, u.headers[s]);
            if (u.beforeSend && (u.beforeSend.call(d, w, u) === !1 || 2 === b)) return w.abort();
            _ = "abort";
            for (s in {
                success: 1,
                error: 1,
                complete: 1
            }) w[s](u[s]);
            if (c = L(Vi, u, e, w)) {
                w.readyState = 1,
                l && p.trigger("ajaxSend", [w, u]),
                u.async && u.timeout > 0 && (a = setTimeout(function() {
                    w.abort("timeout")
                },
                u.timeout));
                try {
                    b = 1,
                    c.send(v, i)
                } catch(x) {
                    if (! (2 > b)) throw x;
                    i( - 1, x)
                }
            } else i( - 1, "No Transport");
            return w
        },
        getJSON: function(t, e, i) {
            return se.get(t, e, i, "json")
        },
        getScript: function(t, e) {
            return se.get(t, void 0, e, "script")
        }
    }),
    se.each(["get", "post"],
    function(t, e) {
        se[e] = function(t, i, n, s) {
            return se.isFunction(i) && (s = s || n, n = i, i = void 0),
            se.ajax({
                url: t,
                type: e,
                dataType: s,
                data: i,
                success: n
            })
        }
    }),
    se._evalUrl = function(t) {
        return se.ajax({
            url: t,
            type: "GET",
            dataType: "script",
            async: !1,
            global: !1,
            "throws": !0
        })
    },
    se.fn.extend({
        wrapAll: function(t) {
            if (se.isFunction(t)) return this.each(function(e) {
                se(this).wrapAll(t.call(this, e))
            });
            if (this[0]) {
                var e = se(t, this[0].ownerDocument).eq(0).clone(!0);
                this[0].parentNode && e.insertBefore(this[0]),
                e.map(function() {
                    for (var t = this; t.firstChild && 1 === t.firstChild.nodeType;) t = t.firstChild;
                    return t
                }).append(this)
            }
            return this
        },
        wrapInner: function(t) {
            return this.each(se.isFunction(t) ?
            function(e) {
                se(this).wrapInner(t.call(this, e))
            }: function() {
                var e = se(this),
                i = e.contents();
                i.length ? i.wrapAll(t) : e.append(t)
            })
        },
        wrap: function(t) {
            var e = se.isFunction(t);
            return this.each(function(i) {
                se(this).wrapAll(e ? t.call(this, i) : t)
            })
        },
        unwrap: function() {
            return this.parent().each(function() {
                se.nodeName(this, "body") || se(this).replaceWith(this.childNodes)
            }).end()
        }
    }),
    se.expr.filters.hidden = function(t) {
        return t.offsetWidth <= 0 && t.offsetHeight <= 0 || !ie.reliableHiddenOffsets() && "none" === (t.style && t.style.display || se.css(t, "display"))
    },
    se.expr.filters.visible = function(t) {
        return ! se.expr.filters.hidden(t)
    };
    var Ui = /%20/g,
    Yi = /\[\]$/,
    Xi = /\r?\n/g,
    Ki = /^(?:submit|button|image|reset|file)$/i,
    Gi = /^(?:input|select|textarea|keygen)/i;
    se.param = function(t, e) {
        var i, n = [],
        s = function(t, e) {
            e = se.isFunction(e) ? e() : null == e ? "": e,
            n[n.length] = encodeURIComponent(t) + "=" + encodeURIComponent(e)
        };
        if (void 0 === e && (e = se.ajaxSettings && se.ajaxSettings.traditional), se.isArray(t) || t.jquery && !se.isPlainObject(t)) se.each(t,
        function() {
            s(this.name, this.value)
        });
        else for (i in t) B(i, t[i], e, s);
        return n.join("&").replace(Ui, "+")
    },
    se.fn.extend({
        serialize: function() {
            return se.param(this.serializeArray())
        },
        serializeArray: function() {
            return this.map(function() {
                var t = se.prop(this, "elements");
                return t ? se.makeArray(t) : this
            }).filter(function() {
                var t = this.type;
                return this.name && !se(this).is(":disabled") && Gi.test(this.nodeName) && !Ki.test(t) && (this.checked || !Se.test(t))
            }).map(function(t, e) {
                var i = se(this).val();
                return null == i ? null: se.isArray(i) ? se.map(i,
                function(t) {
                    return {
                        name: e.name,
                        value: t.replace(Xi, "\r\n")
                    }
                }) : {
                    name: e.name,
                    value: i.replace(Xi, "\r\n")
                }
            }).get()
        }
    }),
    se.ajaxSettings.xhr = void 0 !== t.ActiveXObject ?
    function() {
        return ! this.isLocal && /^(get|post|head|put|delete|options)$/i.test(this.type) && q() || U()
    }: q;
    var Ji = 0,
    Qi = {},
    Zi = se.ajaxSettings.xhr();
    t.attachEvent && t.attachEvent("onunload",
    function() {
        for (var t in Qi) Qi[t](void 0, !0)
    }),
    ie.cors = !!Zi && "withCredentials" in Zi,
    Zi = ie.ajax = !!Zi,
    Zi && se.ajaxTransport(function(t) {
        if (!t.crossDomain || ie.cors) {
            var e;
            return {
                send: function(i, n) {
                    var s, o = t.xhr(),
                    r = ++Ji;
                    if (o.open(t.type, t.url, t.async, t.username, t.password), t.xhrFields) for (s in t.xhrFields) o[s] = t.xhrFields[s];
                    t.mimeType && o.overrideMimeType && o.overrideMimeType(t.mimeType),
                    t.crossDomain || i["X-Requested-With"] || (i["X-Requested-With"] = "XMLHttpRequest");
                    for (s in i) void 0 !== i[s] && o.setRequestHeader(s, i[s] + "");
                    o.send(t.hasContent && t.data || null),
                    e = function(i, s) {
                        var a, l, c;
                        if (e && (s || 4 === o.readyState)) if (delete Qi[r], e = void 0, o.onreadystatechange = se.noop, s) 4 !== o.readyState && o.abort();
                        else {
                            c = {},
                            a = o.status,
                            "string" == typeof o.responseText && (c.text = o.responseText);
                            try {
                                l = o.statusText
                            } catch(h) {
                                l = ""
                            }
                            a || !t.isLocal || t.crossDomain ? 1223 === a && (a = 204) : a = c.text ? 200 : 404
                        }
                        c && n(a, l, c, o.getAllResponseHeaders())
                    },
                    t.async ? 4 === o.readyState ? setTimeout(e) : o.onreadystatechange = Qi[r] = e: e()
                },
                abort: function() {
                    e && e(void 0, !0)
                }
            }
        }
    }),
    se.ajaxSetup({
        accepts: {
            script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
        },
        contents: {
            script: /(?:java|ecma)script/
        },
        converters: {
            "text script": function(t) {
                return se.globalEval(t),
                t
            }
        }
    }),
    se.ajaxPrefilter("script",
    function(t) {
        void 0 === t.cache && (t.cache = !1),
        t.crossDomain && (t.type = "GET", t.global = !1)
    }),
    se.ajaxTransport("script",
    function(t) {
        if (t.crossDomain) {
            var e, i = fe.head || se("head")[0] || fe.documentElement;
            return {
                send: function(n, s) {
                    e = fe.createElement("script"),
                    e.async = !0,
                    t.scriptCharset && (e.charset = t.scriptCharset),
                    e.src = t.url,
                    e.onload = e.onreadystatechange = function(t, i) { (i || !e.readyState || /loaded|complete/.test(e.readyState)) && (e.onload = e.onreadystatechange = null, e.parentNode && e.parentNode.removeChild(e), e = null, i || s(200, "success"))
                    },
                    i.insertBefore(e, i.firstChild)
                },
                abort: function() {
                    e && e.onload(void 0, !0)
                }
            }
        }
    });
    var tn = [],
    en = /(=)\?(?=&|$)|\?\?/;
    se.ajaxSetup({
        jsonp: "callback",
        jsonpCallback: function() {
            var t = tn.pop() || se.expando + "_" + Si++;
            return this[t] = !0,
            t
        }
    }),
    se.ajaxPrefilter("json jsonp",
    function(e, i, n) {
        var s, o, r, a = e.jsonp !== !1 && (en.test(e.url) ? "url": "string" == typeof e.data && !(e.contentType || "").indexOf("application/x-www-form-urlencoded") && en.test(e.data) && "data");
        return a || "jsonp" === e.dataTypes[0] ? (s = e.jsonpCallback = se.isFunction(e.jsonpCallback) ? e.jsonpCallback() : e.jsonpCallback, a ? e[a] = e[a].replace(en, "$1" + s) : e.jsonp !== !1 && (e.url += (Pi.test(e.url) ? "&": "?") + e.jsonp + "=" + s), e.converters["script json"] = function() {
            return r || se.error(s + " was not called"),
            r[0]
        },
        e.dataTypes[0] = "json", o = t[s], t[s] = function() {
            r = arguments
        },
        n.always(function() {
            t[s] = o,
            e[s] && (e.jsonpCallback = i.jsonpCallback, tn.push(s)),
            r && se.isFunction(o) && o(r[0]),
            r = o = void 0
        }), "script") : void 0
    }),
    se.parseHTML = function(t, e, i) {
        if (!t || "string" != typeof t) return null;
        "boolean" == typeof e && (i = e, e = !1),
        e = e || fe;
        var n = ue.exec(t),
        s = !i && [];
        return n ? [e.createElement(n[1])] : (n = se.buildFragment([t], e, s), s && s.length && se(s).remove(), se.merge([], n.childNodes))
    };
    var nn = se.fn.load;
    se.fn.load = function(t, e, i) {
        if ("string" != typeof t && nn) return nn.apply(this, arguments);
        var n, s, o, r = this,
        a = t.indexOf(" ");
        return a >= 0 && (n = se.trim(t.slice(a, t.length)), t = t.slice(0, a)),
        se.isFunction(e) ? (i = e, e = void 0) : e && "object" == typeof e && (o = "POST"),
        r.length > 0 && se.ajax({
            url: t,
            type: o,
            dataType: "html",
            data: e
        }).done(function(t) {
            s = arguments,
            r.html(n ? se("<div>").append(se.parseHTML(t)).find(n) : t)
        }).complete(i &&
        function(t, e) {
            r.each(i, s || [t.responseText, e, t])
        }),
        this
    },
    se.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"],
    function(t, e) {
        se.fn[e] = function(t) {
            return this.on(e, t)
        }
    }),
    se.expr.filters.animated = function(t) {
        return se.grep(se.timers,
        function(e) {
            return t === e.elem
        }).length
    };
    var sn = t.document.documentElement;
    se.offset = {
        setOffset: function(t, e, i) {
            var n, s, o, r, a, l, c, h = se.css(t, "position"),
            u = se(t),
            d = {};
            "static" === h && (t.style.position = "relative"),
            a = u.offset(),
            o = se.css(t, "top"),
            l = se.css(t, "left"),
            c = ("absolute" === h || "fixed" === h) && se.inArray("auto", [o, l]) > -1,
            c ? (n = u.position(), r = n.top, s = n.left) : (r = parseFloat(o) || 0, s = parseFloat(l) || 0),
            se.isFunction(e) && (e = e.call(t, i, a)),
            null != e.top && (d.top = e.top - a.top + r),
            null != e.left && (d.left = e.left - a.left + s),
            "using" in e ? e.using.call(t, d) : u.css(d)
        }
    },
    se.fn.extend({
        offset: function(t) {
            if (arguments.length) return void 0 === t ? this: this.each(function(e) {
                se.offset.setOffset(this, t, e)
            });
            var e, i, n = {
                top: 0,
                left: 0
            },
            s = this[0],
            o = s && s.ownerDocument;
            if (o) return e = o.documentElement,
            se.contains(e, s) ? (typeof s.getBoundingClientRect !== Ce && (n = s.getBoundingClientRect()), i = Y(o), {
                top: n.top + (i.pageYOffset || e.scrollTop) - (e.clientTop || 0),
                left: n.left + (i.pageXOffset || e.scrollLeft) - (e.clientLeft || 0)
            }) : n
        },
        position: function() {
            if (this[0]) {
                var t, e, i = {
                    top: 0,
                    left: 0
                },
                n = this[0];
                return "fixed" === se.css(n, "position") ? e = n.getBoundingClientRect() : (t = this.offsetParent(), e = this.offset(), se.nodeName(t[0], "html") || (i = t.offset()), i.top += se.css(t[0], "borderTopWidth", !0), i.left += se.css(t[0], "borderLeftWidth", !0)),
                {
                    top: e.top - i.top - se.css(n, "marginTop", !0),
                    left: e.left - i.left - se.css(n, "marginLeft", !0)
                }
            }
        },
        offsetParent: function() {
            return this.map(function() {
                for (var t = this.offsetParent || sn; t && !se.nodeName(t, "html") && "static" === se.css(t, "position");) t = t.offsetParent;
                return t || sn
            })
        }
    }),
    se.each({
        scrollLeft: "pageXOffset",
        scrollTop: "pageYOffset"
    },
    function(t, e) {
        var i = /Y/.test(e);
        se.fn[t] = function(n) {
            return Ie(this,
            function(t, n, s) {
                var o = Y(t);
                return void 0 === s ? o ? e in o ? o[e] : o.document.documentElement[n] : t[n] : void(o ? o.scrollTo(i ? se(o).scrollLeft() : s, i ? s: se(o).scrollTop()) : t[n] = s)
            },
            t, n, arguments.length, null)
        }
    }),
    se.each(["top", "left"],
    function(t, e) {
        se.cssHooks[e] = D(ie.pixelPosition,
        function(t, i) {
            return i ? (i = ei(t, e), ni.test(i) ? se(t).position()[e] + "px": i) : void 0
        })
    }),
    se.each({
        Height: "height",
        Width: "width"
    },
    function(t, e) {
        se.each({
            padding: "inner" + t,
            content: e,
            "": "outer" + t
        },
        function(i, n) {
            se.fn[n] = function(n, s) {
                var o = arguments.length && (i || "boolean" != typeof n),
                r = i || (n === !0 || s === !0 ? "margin": "border");
                return Ie(this,
                function(e, i, n) {
                    var s;
                    return se.isWindow(e) ? e.document.documentElement["client" + t] : 9 === e.nodeType ? (s = e.documentElement, Math.max(e.body["scroll" + t], s["scroll" + t], e.body["offset" + t], s["offset" + t], s["client" + t])) : void 0 === n ? se.css(e, i, r) : se.style(e, i, n, r)
                },
                e, o ? n: void 0, o, null)
            }
        })
    }),
    se.fn.size = function() {
        return this.length
    },
    se.fn.andSelf = se.fn.addBack,
    "function" == typeof define && define.amd && define("jquery", [],
    function() {
        return se
    });
    var on = t.jQuery,
    rn = t.$;
    return se.noConflict = function(e) {
        return t.$ === se && (t.$ = rn),
        e && t.jQuery === se && (t.jQuery = on),
        se
    },
    typeof e === Ce && (t.jQuery = t.$ = se),
    se
}),
function(t, e) {
    if ("function" == typeof define && define.amd) define("backbone", ["underscore", "jquery", "exports"],
    function(i, n, s) {
        t.Backbone = e(t, s, i, n)
    });
    else if ("undefined" != typeof exports) {
        var i = require("underscore");
        e(t, exports, i)
    } else t.Backbone = e(t, {},
    t._, t.jQuery || t.Zepto || t.ender || t.$)
} (this,
function(t, e, i, n) {
    {
        var s = t.Backbone,
        o = [],
        r = (o.push, o.slice);
        o.splice
    }
    e.VERSION = "1.1.2",
    e.$ = n,
    e.noConflict = function() {
        return t.Backbone = s,
        this
    },
    e.emulateHTTP = !1,
    e.emulateJSON = !1;
    var a = e.Events = {
        on: function(t, e, i) {
            if (!c(this, "on", t, [e, i]) || !e) return this;
            this._events || (this._events = {});
            var n = this._events[t] || (this._events[t] = []);
            return n.push({
                callback: e,
                context: i,
                ctx: i || this
            }),
            this
        },
        once: function(t, e, n) {
            if (!c(this, "once", t, [e, n]) || !e) return this;
            var s = this,
            o = i.once(function() {
                s.off(t, o),
                e.apply(this, arguments)
            });
            return o._callback = e,
            this.on(t, o, n)
        },
        off: function(t, e, n) {
            var s, o, r, a, l, h, u, d;
            if (!this._events || !c(this, "off", t, [e, n])) return this;
            if (!t && !e && !n) return this._events = void 0,
            this;
            for (a = t ? [t] : i.keys(this._events), l = 0, h = a.length; h > l; l++) if (t = a[l], r = this._events[t]) {
                if (this._events[t] = s = [], e || n) for (u = 0, d = r.length; d > u; u++) o = r[u],
                (e && e !== o.callback && e !== o.callback._callback || n && n !== o.context) && s.push(o);
                s.length || delete this._events[t]
            }
            return this
        },
        trigger: function(t) {
            if (!this._events) return this;
            var e = r.call(arguments, 1);
            if (!c(this, "trigger", t, e)) return this;
            var i = this._events[t],
            n = this._events.all;
            return i && h(i, e),
            n && h(n, arguments),
            this
        },
        stopListening: function(t, e, n) {
            var s = this._listeningTo;
            if (!s) return this;
            var o = !e && !n;
            n || "object" != typeof e || (n = this),
            t && ((s = {})[t._listenId] = t);
            for (var r in s) t = s[r],
            t.off(e, n, this),
            (o || i.isEmpty(t._events)) && delete this._listeningTo[r];
            return this
        }
    },
    l = /\s+/,
    c = function(t, e, i, n) {
        if (!i) return ! 0;
        if ("object" == typeof i) {
            for (var s in i) t[e].apply(t, [s, i[s]].concat(n));
            return ! 1
        }
        if (l.test(i)) {
            for (var o = i.split(l), r = 0, a = o.length; a > r; r++) t[e].apply(t, [o[r]].concat(n));
            return ! 1
        }
        return ! 0
    },
    h = function(t, e) {
        var i, n = -1,
        s = t.length,
        o = e[0],
        r = e[1],
        a = e[2];
        switch (e.length) {
        case 0:
            for (; ++n < s;)(i = t[n]).callback.call(i.ctx);
            return;
        case 1:
            for (; ++n < s;)(i = t[n]).callback.call(i.ctx, o);
            return;
        case 2:
            for (; ++n < s;)(i = t[n]).callback.call(i.ctx, o, r);
            return;
        case 3:
            for (; ++n < s;)(i = t[n]).callback.call(i.ctx, o, r, a);
            return;
        default:
            for (; ++n < s;)(i = t[n]).callback.apply(i.ctx, e);
            return
        }
    },
    u = {
        listenTo: "on",
        listenToOnce: "once"
    };
    i.each(u,
    function(t, e) {
        a[e] = function(e, n, s) {
            var o = this._listeningTo || (this._listeningTo = {}),
            r = e._listenId || (e._listenId = i.uniqueId("l"));
            return o[r] = e,
            s || "object" != typeof n || (s = this),
            e[t](n, s, this),
            this
        }
    }),
    a.bind = a.on,
    a.unbind = a.off,
    i.extend(e, a);
    var d = e.Model = function(t, e) {
        var n = t || {};
        e || (e = {}),
        this.cid = i.uniqueId("c"),
        this.attributes = {},
        e.collection && (this.collection = e.collection),
        e.parse && (n = this.parse(n, e) || {}),
        n = i.defaults({},
        n, i.result(this, "defaults")),
        this.set(n, e),
        this.changed = {},
        this.initialize.apply(this, arguments)
    };
    i.extend(d.prototype, a, {
        changed: null,
        validationError: null,
        idAttribute: "id",
        initialize: function() {},
        toJSON: function() {
            return i.clone(this.attributes)
        },
        sync: function() {
            return e.sync.apply(this, arguments)
        },
        get: function(t) {
            return this.attributes[t]
        },
        escape: function(t) {
            return i.escape(this.get(t))
        },
        has: function(t) {
            return null != this.get(t)
        },
        set: function(t, e, n) {
            var s, o, r, a, l, c, h, u;
            if (null == t) return this;
            if ("object" == typeof t ? (o = t, n = e) : (o = {})[t] = e, n || (n = {}), !this._validate(o, n)) return ! 1;
            r = n.unset,
            l = n.silent,
            a = [],
            c = this._changing,
            this._changing = !0,
            c || (this._previousAttributes = i.clone(this.attributes), this.changed = {}),
            u = this.attributes,
            h = this._previousAttributes,
            this.idAttribute in o && (this.id = o[this.idAttribute]);
            for (s in o) e = o[s],
            i.isEqual(u[s], e) || a.push(s),
            i.isEqual(h[s], e) ? delete this.changed[s] : this.changed[s] = e,
            r ? delete u[s] : u[s] = e;
            if (!l) {
                a.length && (this._pending = n);
                for (var d = 0,
                p = a.length; p > d; d++) this.trigger("change:" + a[d], this, u[a[d]], n)
            }
            if (c) return this;
            if (!l) for (; this._pending;) n = this._pending,
            this._pending = !1,
            this.trigger("change", this, n);
            return this._pending = !1,
            this._changing = !1,
            this
        },
        unset: function(t, e) {
            return this.set(t, void 0, i.extend({},
            e, {
                unset: !0
            }))
        },
        clear: function(t) {
            var e = {};
            for (var n in this.attributes) e[n] = void 0;
            return this.set(e, i.extend({},
            t, {
                unset: !0
            }))
        },
        hasChanged: function(t) {
            return null == t ? !i.isEmpty(this.changed) : i.has(this.changed, t)
        },
        changedAttributes: function(t) {
            if (!t) return this.hasChanged() ? i.clone(this.changed) : !1;
            var e, n = !1,
            s = this._changing ? this._previousAttributes: this.attributes;
            for (var o in t) i.isEqual(s[o], e = t[o]) || ((n || (n = {}))[o] = e);
            return n
        },
        previous: function(t) {
            return null != t && this._previousAttributes ? this._previousAttributes[t] : null
        },
        previousAttributes: function() {
            return i.clone(this._previousAttributes)
        },
        fetch: function(t) {
            t = t ? i.clone(t) : {},
            void 0 === t.parse && (t.parse = !0);
            var e = this,
            n = t.success;
            return t.success = function(i) {
                return e.set(e.parse(i, t), t) ? (n && n(e, i, t), void e.trigger("sync", e, i, t)) : !1
            },
            j(this, t),
            this.sync("read", this, t)
        },
        save: function(t, e, n) {
            var s, o, r, a = this.attributes;
            if (null == t || "object" == typeof t ? (s = t, n = e) : (s = {})[t] = e, n = i.extend({
                validate: !0
            },
            n), s && !n.wait) {
                if (!this.set(s, n)) return ! 1
            } else if (!this._validate(s, n)) return ! 1;
            s && n.wait && (this.attributes = i.extend({},
            a, s)),
            void 0 === n.parse && (n.parse = !0);
            var l = this,
            c = n.success;
            return n.success = function(t) {
                l.attributes = a;
                var e = l.parse(t, n);
                return n.wait && (e = i.extend(s || {},
                e)),
                i.isObject(e) && !l.set(e, n) ? !1 : (c && c(l, t, n), void l.trigger("sync", l, t, n))
            },
            j(this, n),
            o = this.isNew() ? "create": n.patch ? "patch": "update",
            "patch" === o && (n.attrs = s),
            r = this.sync(o, this, n),
            s && n.wait && (this.attributes = a),
            r
        },
        destroy: function(t) {
            t = t ? i.clone(t) : {};
            var e = this,
            n = t.success,
            s = function() {
                e.trigger("destroy", e, e.collection, t)
            };
            if (t.success = function(i) { (t.wait || e.isNew()) && s(),
                n && n(e, i, t),
                e.isNew() || e.trigger("sync", e, i, t)
            },
            this.isNew()) return t.success(),
            !1;
            j(this, t);
            var o = this.sync("delete", this, t);
            return t.wait || s(),
            o
        },
        url: function() {
            var t = i.result(this, "urlRoot") || i.result(this.collection, "url") || H();
            return this.isNew() ? t: t.replace(/([^\/])$/, "$1/") + encodeURIComponent(this.id)
        },
        parse: function(t) {
            return t
        },
        clone: function() {
            return new this.constructor(this.attributes)
        },
        isNew: function() {
            return ! this.has(this.idAttribute)
        },
        isValid: function(t) {
            return this._validate({},
            i.extend(t || {},
            {
                validate: !0
            }))
        },
        _validate: function(t, e) {
            if (!e.validate || !this.validate) return ! 0;
            t = i.extend({},
            this.attributes, t);
            var n = this.validationError = this.validate(t, e) || null;
            return n ? (this.trigger("invalid", this, n, i.extend(e, {
                validationError: n
            })), !1) : !0
        }
    });
    var p = ["keys", "values", "pairs", "invert", "pick", "omit"];
    i.each(p,
    function(t) {
        d.prototype[t] = function() {
            var e = r.call(arguments);
            return e.unshift(this.attributes),
            i[t].apply(i, e)
        }
    });
    var f = e.Collection = function(t, e) {
        e || (e = {}),
        e.model && (this.model = e.model),
        void 0 !== e.comparator && (this.comparator = e.comparator),
        this._reset(),
        this.initialize.apply(this, arguments),
        t && this.reset(t, i.extend({
            silent: !0
        },
        e))
    },
    m = {
        add: !0,
        remove: !0,
        merge: !0
    },
    g = {
        add: !0,
        remove: !1
    };
    i.extend(f.prototype, a, {
        model: d,
        initialize: function() {},
        toJSON: function(t) {
            return this.map(function(e) {
                return e.toJSON(t)
            })
        },
        sync: function() {
            return e.sync.apply(this, arguments)
        },
        add: function(t, e) {
            return this.set(t, i.extend({
                merge: !1
            },
            e, g))
        },
        remove: function(t, e) {
            var n = !i.isArray(t);
            t = n ? [t] : i.clone(t),
            e || (e = {});
            var s, o, r, a;
            for (s = 0, o = t.length; o > s; s++) a = t[s] = this.get(t[s]),
            a && (delete this._byId[a.id], delete this._byId[a.cid], r = this.indexOf(a), this.models.splice(r, 1), this.length--, e.silent || (e.index = r, a.trigger("remove", a, this, e)), this._removeReference(a, e));
            return n ? t[0] : t
        },
        set: function(t, e) {
            e = i.defaults({},
            e, m),
            e.parse && (t = this.parse(t, e));
            var n = !i.isArray(t);
            t = n ? t ? [t] : [] : i.clone(t);
            var s, o, r, a, l, c, h, u = e.at,
            p = this.model,
            f = this.comparator && null == u && e.sort !== !1,
            g = i.isString(this.comparator) ? this.comparator: null,
            v = [],
            y = [],
            b = {},
            _ = e.add,
            w = e.merge,
            x = e.remove,
            C = !f && _ && x ? [] : !1;
            for (s = 0, o = t.length; o > s; s++) {
                if (l = t[s] || {},
                r = l instanceof d ? a = l: l[p.prototype.idAttribute || "id"], c = this.get(r)) x && (b[c.cid] = !0),
                w && (l = l === a ? a.attributes: l, e.parse && (l = c.parse(l, e)), c.set(l, e), f && !h && c.hasChanged(g) && (h = !0)),
                t[s] = c;
                else if (_) {
                    if (a = t[s] = this._prepareModel(l, e), !a) continue;
                    v.push(a),
                    this._addReference(a, e)
                }
                a = c || a,
                !C || !a.isNew() && b[a.id] || C.push(a),
                b[a.id] = !0
            }
            if (x) {
                for (s = 0, o = this.length; o > s; ++s) b[(a = this.models[s]).cid] || y.push(a);
                y.length && this.remove(y, e)
            }
            if (v.length || C && C.length) if (f && (h = !0), this.length += v.length, null != u) for (s = 0, o = v.length; o > s; s++) this.models.splice(u + s, 0, v[s]);
            else {
                C && (this.models.length = 0);
                var k = C || v;
                for (s = 0, o = k.length; o > s; s++) this.models.push(k[s])
            }
            if (h && this.sort({
                silent: !0
            }), !e.silent) {
                for (s = 0, o = v.length; o > s; s++)(a = v[s]).trigger("add", a, this, e); (h || C && C.length) && this.trigger("sort", this, e)
            }
            return n ? t[0] : t
        },
        reset: function(t, e) {
            e || (e = {});
            for (var n = 0,
            s = this.models.length; s > n; n++) this._removeReference(this.models[n], e);
            return e.previousModels = this.models,
            this._reset(),
            t = this.add(t, i.extend({
                silent: !0
            },
            e)),
            e.silent || this.trigger("reset", this, e),
            t
        },
        push: function(t, e) {
            return this.add(t, i.extend({
                at: this.length
            },
            e))
        },
        pop: function(t) {
            var e = this.at(this.length - 1);
            return this.remove(e, t),
            e
        },
        unshift: function(t, e) {
            return this.add(t, i.extend({
                at: 0
            },
            e))
        },
        shift: function(t) {
            var e = this.at(0);
            return this.remove(e, t),
            e
        },
        slice: function() {
            return r.apply(this.models, arguments)
        },
        get: function(t) {
            return null == t ? void 0 : this._byId[t] || this._byId[t.id] || this._byId[t.cid]
        },
        at: function(t) {
            return this.models[t]
        },
        where: function(t, e) {
            return i.isEmpty(t) ? e ? void 0 : [] : this[e ? "find": "filter"](function(e) {
                for (var i in t) if (t[i] !== e.get(i)) return ! 1;
                return ! 0
            })
        },
        findWhere: function(t) {
            return this.where(t, !0)
        },
        sort: function(t) {
            if (!this.comparator) throw new Error("Cannot sort a set without a comparator");
            return t || (t = {}),
            i.isString(this.comparator) || 1 === this.comparator.length ? this.models = this.sortBy(this.comparator, this) : this.models.sort(i.bind(this.comparator, this)),
            t.silent || this.trigger("sort", this, t),
            this
        },
        pluck: function(t) {
            return i.invoke(this.models, "get", t)
        },
        fetch: function(t) {
            t = t ? i.clone(t) : {},
            void 0 === t.parse && (t.parse = !0);
            var e = t.success,
            n = this;
            return t.success = function(i) {
                var s = t.reset ? "reset": "set";
                n[s](i, t),
                e && e(n, i, t),
                n.trigger("sync", n, i, t)
            },
            j(this, t),
            this.sync("read", this, t)
        },
        create: function(t, e) {
            if (e = e ? i.clone(e) : {},
            !(t = this._prepareModel(t, e))) return ! 1;
            e.wait || this.add(t, e);
            var n = this,
            s = e.success;
            return e.success = function(t, i) {
                e.wait && n.add(t, e),
                s && s(t, i, e)
            },
            t.save(null, e),
            t
        },
        parse: function(t) {
            return t
        },
        clone: function() {
            return new this.constructor(this.models)
        },
        _reset: function() {
            this.length = 0,
            this.models = [],
            this._byId = {}
        },
        _prepareModel: function(t, e) {
            if (t instanceof d) return t;
            e = e ? i.clone(e) : {},
            e.collection = this;
            var n = new this.model(t, e);
            return n.validationError ? (this.trigger("invalid", this, n.validationError, e), !1) : n
        },
        _addReference: function(t) {
            this._byId[t.cid] = t,
            null != t.id && (this._byId[t.id] = t),
            t.collection || (t.collection = this),
            t.on("all", this._onModelEvent, this)
        },
        _removeReference: function(t) {
            this === t.collection && delete t.collection,
            t.off("all", this._onModelEvent, this)
        },
        _onModelEvent: function(t, e, i, n) { ("add" !== t && "remove" !== t || i === this) && ("destroy" === t && this.remove(e, n), e && t === "change:" + e.idAttribute && (delete this._byId[e.previous(e.idAttribute)], null != e.id && (this._byId[e.id] = e)), this.trigger.apply(this, arguments))
        }
    });
    var v = ["forEach", "each", "map", "collect", "reduce", "foldl", "inject", "reduceRight", "foldr", "find", "detect", "filter", "select", "reject", "every", "all", "some", "any", "include", "contains", "invoke", "max", "min", "toArray", "size", "first", "head", "take", "initial", "rest", "tail", "drop", "last", "without", "difference", "indexOf", "shuffle", "lastIndexOf", "isEmpty", "chain", "sample"];
    i.each(v,
    function(t) {
        f.prototype[t] = function() {
            var e = r.call(arguments);
            return e.unshift(this.models),
            i[t].apply(i, e)
        }
    });
    var y = ["groupBy", "countBy", "sortBy", "indexBy"];
    i.each(y,
    function(t) {
        f.prototype[t] = function(e, n) {
            var s = i.isFunction(e) ? e: function(t) {
                return t.get(e)
            };
            return i[t](this.models, s, n)
        }
    });
    var b = e.View = function(t) {
        this.cid = i.uniqueId("view"),
        t || (t = {}),
        i.extend(this, i.pick(t, w)),
        this._ensureElement(),
        this.initialize.apply(this, arguments),
        this.delegateEvents()
    },
    _ = /^(\S+)\s*(.*)$/,
    w = ["model", "collection", "el", "id", "attributes", "className", "tagName", "events"];
    i.extend(b.prototype, a, {
        tagName: "div",
        $: function(t) {
            return this.$el.find(t)
        },
        initialize: function() {},
        render: function() {
            return this
        },
        remove: function() {
            return this.$el.remove(),
            this.stopListening(),
            this
        },
        setElement: function(t, i) {
            return this.$el && this.undelegateEvents(),
            this.$el = t instanceof e.$ ? t: e.$(t),
            this.el = this.$el[0],
            i !== !1 && this.delegateEvents(),
            this
        },
        delegateEvents: function(t) {
            if (!t && !(t = i.result(this, "events"))) return this;
            this.undelegateEvents();
            for (var e in t) {
                var n = t[e];
                if (i.isFunction(n) || (n = this[t[e]]), n) {
                    var s = e.match(_),
                    o = s[1],
                    r = s[2];
                    n = i.bind(n, this),
                    o += ".delegateEvents" + this.cid,
                    "" === r ? this.$el.on(o, n) : this.$el.on(o, r, n)
                }
            }
            return this
        },
        undelegateEvents: function() {
            return this.$el.off(".delegateEvents" + this.cid),
            this
        },
        _ensureElement: function() {
            if (this.el) this.setElement(i.result(this, "el"), !1);
            else {
                var t = i.extend({},
                i.result(this, "attributes"));
                this.id && (t.id = i.result(this, "id")),
                this.className && (t["class"] = i.result(this, "className"));
                var n = e.$("<" + i.result(this, "tagName") + ">").attr(t);
                this.setElement(n, !1)
            }
        }
    }),
    e.sync = function(t, n, s) {
        var o = C[t];
        i.defaults(s || (s = {}), {
            emulateHTTP: e.emulateHTTP,
            emulateJSON: e.emulateJSON
        });
        var r = {
            type: o,
            dataType: "json"
        };
        if (s.url || (r.url = i.result(n, "url") || H()), null != s.data || !n || "create" !== t && "update" !== t && "patch" !== t || (r.contentType = "application/json", r.data = JSON.stringify(s.attrs || n.toJSON(s))), s.emulateJSON && (r.contentType = "application/x-www-form-urlencoded", r.data = r.data ? {
            model: r.data
        }: {}), s.emulateHTTP && ("PUT" === o || "DELETE" === o || "PATCH" === o)) {
            r.type = "POST",
            s.emulateJSON && (r.data._method = o);
            var a = s.beforeSend;
            s.beforeSend = function(t) {
                return t.setRequestHeader("X-HTTP-Method-Override", o),
                a ? a.apply(this, arguments) : void 0
            }
        }
        "GET" === r.type || s.emulateJSON || (r.processData = !1),
        "PATCH" === r.type && x && (r.xhr = function() {
            return new ActiveXObject("Microsoft.XMLHTTP")
        });
        var l = s.xhr = e.ajax(i.extend(r, s));
        return n.trigger("request", n, l, s),
        l
    };
    var x = !("undefined" == typeof window || !window.ActiveXObject || window.XMLHttpRequest && (new XMLHttpRequest).dispatchEvent),
    C = {
        create: "POST",
        update: "PUT",
        patch: "PATCH",
        "delete": "DELETE",
        read: "GET"
    };
    e.ajax = function() {
        return e.$.ajax.apply(e.$, arguments)
    };
    var k = e.Router = function(t) {
        t || (t = {}),
        t.routes && (this.routes = t.routes),
        this._bindRoutes(),
        this.initialize.apply(this, arguments)
    },
    T = /\((.*?)\)/g,
    D = /(\(\?)?:\w+/g,
    E = /\*\w+/g,
    M = /[\-{}\[\]+?.,\\\^$|#\s]/g;
    i.extend(k.prototype, a, {
        initialize: function() {},
        route: function(t, n, s) {
            i.isRegExp(t) || (t = this._routeToRegExp(t)),
            i.isFunction(n) && (s = n, n = ""),
            s || (s = this[n]);
            var o = this;
            return e.history.route(t,
            function(i) {
                var r = o._extractParameters(t, i);
                o.execute(s, r),
                o.trigger.apply(o, ["route:" + n].concat(r)),
                o.trigger("route", n, r),
                e.history.trigger("route", o, n, r)
            }),
            this
        },
        execute: function(t, e) {
            t && t.apply(this, e)
        },
        navigate: function(t, i) {
            return e.history.navigate(t, i),
            this
        },
        _bindRoutes: function() {
            if (this.routes) {
                this.routes = i.result(this, "routes");
                for (var t, e = i.keys(this.routes); null != (t = e.pop());) this.route(t, this.routes[t])
            }
        },
        _routeToRegExp: function(t) {
            return t = t.replace(M, "\\$&").replace(T, "(?:$1)?").replace(D,
            function(t, e) {
                return e ? t: "([^/?]+)"
            }).replace(E, "([^?]*?)"),
            new RegExp("^" + t + "(?:\\?([\\s\\S]*))?$")
        },
        _extractParameters: function(t, e) {
            var n = t.exec(e).slice(1);
            return i.map(n,
            function(t, e) {
                return e === n.length - 1 ? t || null: t ? decodeURIComponent(t) : null
            })
        }
    });
    var I = e.History = function() {
        this.handlers = [],
        i.bindAll(this, "checkUrl"),
        "undefined" != typeof window && (this.location = window.location, this.history = window.history)
    },
    S = /^[#\/]|\s+$/g,
    P = /^\/+|\/+$/g,
    A = /msie [\w.]+/,
    N = /\/$/,
    O = /#.*$/;
    I.started = !1,
    i.extend(I.prototype, a, {
        interval: 50,
        atRoot: function() {
            return this.location.pathname.replace(/[^\/]$/, "$&/") === this.root
        },
        getHash: function(t) {
            var e = (t || this).location.href.match(/#(.*)$/);
            return e ? e[1] : ""
        },
        getFragment: function(t, e) {
            if (null == t) if (this._hasPushState || !this._wantsHashChange || e) {
                t = decodeURI(this.location.pathname + this.location.search);
                var i = this.root.replace(N, "");
                t.indexOf(i) || (t = t.slice(i.length))
            } else t = this.getHash();
            return t.replace(S, "")
        },
        start: function(t) {
            if (I.started) throw new Error("Backbone.history has already been started");
            I.started = !0,
            this.options = i.extend({
                root: "/"
            },
            this.options, t),
            this.root = this.options.root,
            this._wantsHashChange = this.options.hashChange !== !1,
            this._wantsPushState = !!this.options.pushState,
            this._hasPushState = !!(this.options.pushState && this.history && this.history.pushState);
            var n = this.getFragment(),
            s = document.documentMode,
            o = A.exec(navigator.userAgent.toLowerCase()) && (!s || 7 >= s);
            if (this.root = ("/" + this.root + "/").replace(P, "/"), o && this._wantsHashChange) {
                var r = e.$('<iframe src="javascript:0" tabindex="-1">');
                this.iframe = r.hide().appendTo("body")[0].contentWindow,
                this.navigate(n)
            }
            this._hasPushState ? e.$(window).on("popstate", this.checkUrl) : this._wantsHashChange && "onhashchange" in window && !o ? e.$(window).on("hashchange", this.checkUrl) : this._wantsHashChange && (this._checkUrlInterval = setInterval(this.checkUrl, this.interval)),
            this.fragment = n;
            var a = this.location;
            if (this._wantsHashChange && this._wantsPushState) {
                if (!this._hasPushState && !this.atRoot()) return this.fragment = this.getFragment(null, !0),
                this.location.replace(this.root + "#" + this.fragment),
                !0;
                this._hasPushState && this.atRoot() && a.hash && (this.fragment = this.getHash().replace(S, ""), this.history.replaceState({},
                document.title, this.root + this.fragment))
            }
            return this.options.silent ? void 0 : this.loadUrl()
        },
        stop: function() {
            e.$(window).off("popstate", this.checkUrl).off("hashchange", this.checkUrl),
            this._checkUrlInterval && clearInterval(this._checkUrlInterval),
            I.started = !1
        },
        route: function(t, e) {
            this.handlers.unshift({
                route: t,
                callback: e
            })
        },
        checkUrl: function() {
            var t = this.getFragment();
            return t === this.fragment && this.iframe && (t = this.getFragment(this.getHash(this.iframe))),
            t === this.fragment ? !1 : (this.iframe && this.navigate(t), void this.loadUrl())
        },
        loadUrl: function(t) {
            return t = this.fragment = this.getFragment(t),
            i.any(this.handlers,
            function(e) {
                return e.route.test(t) ? (e.callback(t), !0) : void 0
            })
        },
        navigate: function(t, e) {
            if (!I.started) return ! 1;
            e && e !== !0 || (e = {
                trigger: !!e
            });
            var i = this.root + (t = this.getFragment(t || ""));
            if (t = t.replace(O, ""), this.fragment !== t) {
                if (this.fragment = t, "" === t && "/" !== i && (i = i.slice(0, -1)), this._hasPushState) this.history[e.replace ? "replaceState": "pushState"]({},
                document.title, i);
                else {
                    if (!this._wantsHashChange) return this.location.assign(i);
                    this._updateHash(this.location, t, e.replace),
                    this.iframe && t !== this.getFragment(this.getHash(this.iframe)) && (e.replace || this.iframe.document.open().close(), this._updateHash(this.iframe.location, t, e.replace))
                }
                return e.trigger ? this.loadUrl(t) : void 0
            }
        },
        _updateHash: function(t, e, i) {
            if (i) {
                var n = t.href.replace(/(javascript:|#).*$/, "");
                t.replace(n + "#" + e)
            } else t.hash = "#" + e
        }
    }),
    e.history = new I;
    var z = function(t, e) {
        var n, s = this;
        n = t && i.has(t, "constructor") ? t.constructor: function() {
            return s.apply(this, arguments)
        },
        i.extend(n, s, e);
        var o = function() {
            this.constructor = n
        };
        return o.prototype = s.prototype,
        n.prototype = new o,
        t && i.extend(n.prototype, t),
        n.__super__ = s.prototype,
        n
    };
    d.extend = f.extend = k.extend = b.extend = I.extend = z;
    var H = function() {
        throw new Error('A "url" property or function must be specified')
    },
    j = function(t, e) {
        var i = e.error;
        e.error = function(n) {
            i && i(t, n, e),
            t.trigger("error", t, n, e)
        }
    };
    return e
}),
function(t, e) {
    if ("function" == typeof define && define.amd) define("backbone.wreqr", ["backbone", "underscore"],
    function(t, i) {
        return e(t, i)
    });
    else if ("undefined" != typeof exports) {
        var i = require("backbone"),
        n = require("underscore");
        module.exports = e(i, n)
    } else e(t.Backbone, t._)
} (this,
function(t, e) {
    "use strict";
    var i = t.Wreqr,
    n = t.Wreqr = {};
    return t.Wreqr.VERSION = "1.3.2",
    t.Wreqr.noConflict = function() {
        return t.Wreqr = i,
        this
    },
    n.Handlers = function(t, e) {
        var i = function(t) {
            this.options = t,
            this._wreqrHandlers = {},
            e.isFunction(this.initialize) && this.initialize(t)
        };
        return i.extend = t.Model.extend,
        e.extend(i.prototype, t.Events, {
            setHandlers: function(t) {
                e.each(t,
                function(t, i) {
                    var n = null;
                    e.isObject(t) && !e.isFunction(t) && (n = t.context, t = t.callback),
                    this.setHandler(i, t, n)
                },
                this)
            },
            setHandler: function(t, e, i) {
                var n = {
                    callback: e,
                    context: i
                };
                this._wreqrHandlers[t] = n,
                this.trigger("handler:add", t, e, i)
            },
            hasHandler: function(t) {
                return !! this._wreqrHandlers[t]
            },
            getHandler: function(t) {
                var e = this._wreqrHandlers[t];
                if (e) return function() {
                    return e.callback.apply(e.context, arguments)
                }
            },
            removeHandler: function(t) {
                delete this._wreqrHandlers[t]
            },
            removeAllHandlers: function() {
                this._wreqrHandlers = {}
            }
        }),
        i
    } (t, e),
    n.CommandStorage = function() {
        var i = function(t) {
            this.options = t,
            this._commands = {},
            e.isFunction(this.initialize) && this.initialize(t)
        };
        return e.extend(i.prototype, t.Events, {
            getCommands: function(t) {
                var e = this._commands[t];
                return e || (e = {
                    command: t,
                    instances: []
                },
                this._commands[t] = e),
                e
            },
            addCommand: function(t, e) {
                var i = this.getCommands(t);
                i.instances.push(e)
            },
            clearCommands: function(t) {
                var e = this.getCommands(t);
                e.instances = []
            }
        }),
        i
    } (),
    n.Commands = function(t, e) {
        return t.Handlers.extend({
            storageType: t.CommandStorage,
            constructor: function(e) {
                this.options = e || {},
                this._initializeStorage(this.options),
                this.on("handler:add", this._executeCommands, this),
                t.Handlers.prototype.constructor.apply(this, arguments)
            },
            execute: function(t) {
                t = arguments[0];
                var i = e.rest(arguments);
                this.hasHandler(t) ? this.getHandler(t).apply(this, i) : this.storage.addCommand(t, i)
            },
            _executeCommands: function(t, i, n) {
                var s = this.storage.getCommands(t);
                e.each(s.instances,
                function(t) {
                    i.apply(n, t)
                }),
                this.storage.clearCommands(t)
            },
            _initializeStorage: function(t) {
                var i, n = t.storageType || this.storageType;
                i = e.isFunction(n) ? new n: n,
                this.storage = i
            }
        })
    } (n, e),
    n.RequestResponse = function(t, e) {
        return t.Handlers.extend({
            request: function(t) {
                return this.hasHandler(t) ? this.getHandler(t).apply(this, e.rest(arguments)) : void 0
            }
        })
    } (n, e),
    n.EventAggregator = function(t, e) {
        var i = function() {};
        return i.extend = t.Model.extend,
        e.extend(i.prototype, t.Events),
        i
    } (t, e),
    n.Channel = function() {
        var i = function(e) {
            this.vent = new t.Wreqr.EventAggregator,
            this.reqres = new t.Wreqr.RequestResponse,
            this.commands = new t.Wreqr.Commands,
            this.channelName = e
        };
        return e.extend(i.prototype, {
            reset: function() {
                return this.vent.off(),
                this.vent.stopListening(),
                this.reqres.removeAllHandlers(),
                this.commands.removeAllHandlers(),
                this
            },
            connectEvents: function(t, e) {
                return this._connect("vent", t, e),
                this
            },
            connectCommands: function(t, e) {
                return this._connect("commands", t, e),
                this
            },
            connectRequests: function(t, e) {
                return this._connect("reqres", t, e),
                this
            },
            _connect: function(t, i, n) {
                if (i) {
                    n = n || this;
                    var s = "vent" === t ? "on": "setHandler";
                    e.each(i,
                    function(i, o) {
                        this[t][s](o, e.bind(i, n))
                    },
                    this)
                }
            }
        }),
        i
    } (n),
    n.radio = function(t, e) {
        var i = function() {
            this._channels = {},
            this.vent = {},
            this.commands = {},
            this.reqres = {},
            this._proxyMethods()
        };
        e.extend(i.prototype, {
            channel: function(t) {
                if (!t) throw new Error("Channel must receive a name");
                return this._getChannel(t)
            },
            _getChannel: function(e) {
                var i = this._channels[e];
                return i || (i = new t.Channel(e), this._channels[e] = i),
                i
            },
            _proxyMethods: function() {
                e.each(["vent", "commands", "reqres"],
                function(t) {
                    e.each(n[t],
                    function(e) {
                        this[t][e] = s(this, t, e)
                    },
                    this)
                },
                this)
            }
        });
        var n = {
            vent: ["on", "off", "trigger", "once", "stopListening", "listenTo", "listenToOnce"],
            commands: ["execute", "setHandler", "setHandlers", "removeHandler", "removeAllHandlers"],
            reqres: ["request", "setHandler", "setHandlers", "removeHandler", "removeAllHandlers"]
        },
        s = function(t, i, n) {
            return function(s) {
                var o = t._getChannel(s)[i];
                return o[n].apply(o, e.rest(arguments))
            }
        };
        return new i
    } (n, e),
    t.Wreqr
}),
function(t, e) {
    if ("function" == typeof define && define.amd) define("backbone.babysitter", ["backbone", "underscore"],
    function(t, i) {
        return e(t, i)
    });
    else if ("undefined" != typeof exports) {
        var i = require("backbone"),
        n = require("underscore");
        module.exports = e(i, n)
    } else e(t.Backbone, t._)
} (this,
function(t, e) {
    "use strict";
    var i = t.ChildViewContainer;
    return t.ChildViewContainer = function(t, e) {
        var i = function(t) {
            this._views = {},
            this._indexByModel = {},
            this._indexByCustom = {},
            this._updateLength(),
            e.each(t, this.add, this)
        };
        e.extend(i.prototype, {
            add: function(t, e) {
                var i = t.cid;
                return this._views[i] = t,
                t.model && (this._indexByModel[t.model.cid] = i),
                e && (this._indexByCustom[e] = i),
                this._updateLength(),
                this
            },
            findByModel: function(t) {
                return this.findByModelCid(t.cid)
            },
            findByModelCid: function(t) {
                var e = this._indexByModel[t];
                return this.findByCid(e)
            },
            findByCustom: function(t) {
                var e = this._indexByCustom[t];
                return this.findByCid(e)
            },
            findByIndex: function(t) {
                return e.values(this._views)[t]
            },
            findByCid: function(t) {
                return this._views[t]
            },
            remove: function(t) {
                var i = t.cid;
                return t.model && delete this._indexByModel[t.model.cid],
                e.any(this._indexByCustom,
                function(t, e) {
                    return t === i ? (delete this._indexByCustom[e], !0) : void 0
                },
                this),
                delete this._views[i],
                this._updateLength(),
                this
            },
            call: function(t) {
                this.apply(t, e.tail(arguments))
            },
            apply: function(t, i) {
                e.each(this._views,
                function(n) {
                    e.isFunction(n[t]) && n[t].apply(n, i || [])
                })
            },
            _updateLength: function() {
                this.length = e.size(this._views)
            }
        });
        var n = ["forEach", "each", "map", "find", "detect", "filter", "select", "reject", "every", "all", "some", "any", "include", "contains", "invoke", "toArray", "first", "initial", "rest", "last", "without", "isEmpty", "pluck", "reduce"];
        return e.each(n,
        function(t) {
            i.prototype[t] = function() {
                var i = e.values(this._views),
                n = [i].concat(e.toArray(arguments));
                return e[t].apply(e, n)
            }
        }),
        i
    } (t, e),
    t.ChildViewContainer.VERSION = "0.1.6",
    t.ChildViewContainer.noConflict = function() {
        return t.ChildViewContainer = i,
        this
    },
    t.ChildViewContainer
}),
function(t, e) {
    if ("function" == typeof define && define.amd) define("backbone.marionette", ["backbone", "underscore", "backbone.wreqr", "backbone.babysitter"],
    function(i, n) {
        return t.Marionette = e(t, i, n)
    });
    else if ("undefined" != typeof exports) {
        {
            var i = require("backbone"),
            n = require("underscore");
            require("backbone.wreqr"),
            require("backbone.babysitter")
        }
        module.exports = e(t, i, n)
    } else t.Marionette = e(t, t.Backbone, t._)
} (this,
function(t, e, i) {
    "use strict";
    var n = t.Marionette,
    s = e.Marionette = {};
    s.VERSION = "2.2.2",
    s.noConflict = function() {
        return t.Marionette = n,
        this
    },
    s.Deferred = e.$.Deferred;
    var o = Array.prototype.slice;
    s.extend = e.Model.extend,
    s.getOption = function(t, e) {
        if (t && e) {
            var i;
            return i = t.options && void 0 !== t.options[e] ? t.options[e] : t[e]
        }
    },
    s.proxyGetOption = function(t) {
        return s.getOption(this, t)
    },
    s.normalizeMethods = function(t) {
        var e = {};
        return i.each(t,
        function(t, n) {
            i.isFunction(t) || (t = this[t]),
            t && (e[n] = t)
        },
        this),
        e
    },
    s.normalizeUIString = function(t, e) {
        return t.replace(/@ui\.[a-zA-Z_$0-9]*/g,
        function(t) {
            return e[t.slice(4)]
        })
    },
    s.normalizeUIKeys = function(t, e) {
        return "undefined" != typeof t ? (t = i.clone(t), i.each(i.keys(t),
        function(i) {
            var n = s.normalizeUIString(i, e);
            n !== i && (t[n] = t[i], delete t[i])
        }), t) : void 0
    },
    s.normalizeUIValues = function(t, e) {
        return "undefined" != typeof t ? (i.each(t,
        function(n, o) {
            i.isString(n) && (t[o] = s.normalizeUIString(n, e))
        }), t) : void 0
    },
    s.actAsCollection = function(t, e) {
        var n = ["forEach", "each", "map", "find", "detect", "filter", "select", "reject", "every", "all", "some", "any", "include", "contains", "invoke", "toArray", "first", "initial", "rest", "last", "without", "isEmpty", "pluck"];
        i.each(n,
        function(n) {
            t[n] = function() {
                var t = i.values(i.result(this, e)),
                s = [t].concat(i.toArray(arguments));
                return i[n].apply(i, s)
            }
        })
    },
    s.triggerMethod = function(t) {
        function e(t, e, i) {
            return i.toUpperCase()
        }
        var n, s = /(^|:)(\w)/gi,
        o = "on" + t.replace(s, e),
        r = this[o];
        return i.isFunction(r) && (n = r.apply(this, i.tail(arguments))),
        i.isFunction(this.trigger) && this.trigger.apply(this, arguments),
        n
    },
    s.triggerMethodOn = function(t, e) {
        var n, o = i.tail(arguments, 2);
        return n = i.isFunction(t.triggerMethod) ? t.triggerMethod: s.triggerMethod,
        n.apply(t, [e].concat(o))
    },
    s.MonitorDOMRefresh = function(t) {
        function n(t) {
            t._isShown = !0,
            o(t)
        }
        function s(t) {
            t._isRendered = !0,
            o(t)
        }
        function o(t) {
            t._isShown && t._isRendered && r(t) && i.isFunction(t.triggerMethod) && t.triggerMethod("dom:refresh")
        }
        function r(i) {
            return e.$.contains(t, i.el)
        }
        return function(t) {
            t.listenTo(t, "show",
            function() {
                n(t)
            }),
            t.listenTo(t, "render",
            function() {
                s(t)
            })
        }
    } (document.documentElement),
    function(t) {
        function e(e, n, s, o) {
            var r = o.split(/\s+/);
            i.each(r,
            function(i) {
                var o = e[i];
                if (!o) throw new t.Error('Method "' + i + '" was configured as an event handler, but does not exist.');
                e.listenTo(n, s, o)
            })
        }
        function n(t, e, i, n) {
            t.listenTo(e, i, n)
        }
        function s(t, e, n, s) {
            var o = s.split(/\s+/);
            i.each(o,
            function(i) {
                var s = t[i];
                t.stopListening(e, n, s)
            })
        }
        function o(t, e, i, n) {
            t.stopListening(e, i, n)
        }
        function r(e, n, s, o, r) {
            if (n && s) {
                if (!i.isFunction(s) && !i.isObject(s)) throw new t.Error({
                    message: "Bindings must be an object or function.",
                    url: "marionette.functions.html#marionettebindentityevents"
                });
                i.isFunction(s) && (s = s.call(e)),
                i.each(s,
                function(t, s) {
                    i.isFunction(t) ? o(e, n, s, t) : r(e, n, s, t)
                })
            }
        }
        t.bindEntityEvents = function(t, i, s) {
            r(t, i, s, n, e)
        },
        t.unbindEntityEvents = function(t, e, i) {
            r(t, e, i, o, s)
        },
        t.proxyBindEntityEvents = function(e, i) {
            return t.bindEntityEvents(this, e, i)
        },
        t.proxyUnbindEntityEvents = function(e, i) {
            return t.unbindEntityEvents(this, e, i)
        }
    } (s);
    var r = ["description", "fileName", "lineNumber", "name", "message", "number"];
    return s.Error = s.extend.call(Error, {
        urlRoot: "http://marionettejs.com/docs/v" + s.VERSION + "/",
        constructor: function(t, e) {
            i.isObject(t) ? (e = t, t = e.message) : e || (e = {});
            var n = Error.call(this, t);
            i.extend(this, i.pick(n, r), i.pick(e, r)),
            this.captureStackTrace(),
            e.url && (this.url = this.urlRoot + e.url)
        },
        captureStackTrace: function() {
            Error.captureStackTrace && Error.captureStackTrace(this, s.Error)
        },
        toString: function() {
            return this.name + ": " + this.message + (this.url ? " See: " + this.url: "")
        }
    }),
    s.Error.extend = s.extend,
    s.Callbacks = function() {
        this._deferred = s.Deferred(),
        this._callbacks = []
    },
    i.extend(s.Callbacks.prototype, {
        add: function(t, e) {
            var n = i.result(this._deferred, "promise");
            this._callbacks.push({
                cb: t,
                ctx: e
            }),
            n.then(function(i) {
                e && (i.context = e),
                t.call(i.context, i.options)
            })
        },
        run: function(t, e) {
            this._deferred.resolve({
                options: t,
                context: e
            })
        },
        reset: function() {
            var t = this._callbacks;
            this._deferred = s.Deferred(),
            this._callbacks = [],
            i.each(t,
            function(t) {
                this.add(t.cb, t.ctx)
            },
            this)
        }
    }),
    s.Controller = function(t) {
        this.options = t || {},
        i.isFunction(this.initialize) && this.initialize(this.options)
    },
    s.Controller.extend = s.extend,
    i.extend(s.Controller.prototype, e.Events, {
        destroy: function() {
            var t = o.call(arguments);
            return this.triggerMethod.apply(this, ["before:destroy"].concat(t)),
            this.triggerMethod.apply(this, ["destroy"].concat(t)),
            this.stopListening(),
            this.off(),
            this
        },
        triggerMethod: s.triggerMethod,
        getOption: s.proxyGetOption
    }),
    s.Object = function(t) {
        this.options = i.extend({},
        i.result(this, "options"), t),
        this.initialize.apply(this, arguments)
    },
    s.Object.extend = s.extend,
    i.extend(s.Object.prototype, {
        initialize: function() {},
        destroy: function() {
            this.triggerMethod("before:destroy"),
            this.triggerMethod("destroy"),
            this.stopListening()
        },
        triggerMethod: s.triggerMethod,
        getOption: s.proxyGetOption,
        bindEntityEvents: s.proxyBindEntityEvents,
        unbindEntityEvents: s.proxyUnbindEntityEvents
    }),
    i.extend(s.Object.prototype, e.Events),
    s.Region = function(t) {
        if (this.options = t || {},
        this.el = this.getOption("el"), this.el = this.el instanceof e.$ ? this.el[0] : this.el, !this.el) throw new s.Error({
            name: "NoElError",
            message: 'An "el" must be specified for a region.'
        });
        if (this.$el = this.getEl(this.el), this.initialize) {
            var i = o.apply(arguments);
            this.initialize.apply(this, i)
        }
    },
    i.extend(s.Region, {
        buildRegion: function(t, e) {
            if (i.isString(t)) return this._buildRegionFromSelector(t, e);
            if (t.selector || t.el || t.regionClass) return this._buildRegionFromObject(t, e);
            if (i.isFunction(t)) return this._buildRegionFromRegionClass(t);
            throw new s.Error({
                message: "Improper region configuration type.",
                url: "marionette.region.html#region-configuration-types"
            })
        },
        _buildRegionFromSelector: function(t, e) {
            return new e({
                el: t
            })
        },
        _buildRegionFromObject: function(t, n) {
            var s = t.regionClass || n,
            o = i.omit(t, "selector", "regionClass");
            t.selector && !o.el && (o.el = t.selector);
            var r = new s(o);
            return t.parentEl && (r.getEl = function(n) {
                if (i.isObject(n)) return e.$(n);
                var s = t.parentEl;
                return i.isFunction(s) && (s = s()),
                s.find(n)
            }),
            r
        },
        _buildRegionFromRegionClass: function(t) {
            return new t
        }
    }),
    i.extend(s.Region.prototype, e.Events, {
        show: function(t, e) {
            this._ensureElement();
            var i = e || {},
            n = t !== this.currentView,
            o = !!i.preventDestroy,
            r = !!i.forceShow,
            a = !!this.currentView,
            l = n && !o,
            c = n || r;
            return a && this.triggerMethod("before:swapOut", this.currentView),
            l && this.empty(),
            c ? (t.once("destroy", this.empty, this), t.render(), a && this.triggerMethod("before:swap", t), this.triggerMethod("before:show", t), s.triggerMethodOn(t, "before:show"), this.attachHtml(t), a && this.triggerMethod("swapOut", this.currentView), this.currentView = t, a && this.triggerMethod("swap", t), this.triggerMethod("show", t), s.triggerMethodOn(t, "show"), this) : this
        },
        _ensureElement: function() {
            if (i.isObject(this.el) || (this.$el = this.getEl(this.el), this.el = this.$el[0]), !this.$el || 0 === this.$el.length) throw new s.Error('An "el" ' + this.$el.selector + " must exist in DOM")
        },
        getEl: function(t) {
            return e.$(t)
        },
        attachHtml: function(t) {
            this.el.innerHTML = "",
            this.el.appendChild(t.el)
        },
        empty: function() {
            var t = this.currentView;
            if (t) return t.off("destroy", this.empty, this),
            this.triggerMethod("before:empty", t),
            this._destroyView(),
            this.triggerMethod("empty", t),
            delete this.currentView,
            this
        },
        _destroyView: function() {
            var t = this.currentView;
            t.destroy && !t.isDestroyed ? t.destroy() : t.remove && t.remove()
        },
        attachView: function(t) {
            return this.currentView = t,
            this
        },
        hasView: function() {
            return !! this.currentView
        },
        reset: function() {
            return this.empty(),
            this.$el && (this.el = this.$el.selector),
            delete this.$el,
            this
        },
        getOption: s.proxyGetOption,
        triggerMethod: s.triggerMethod
    }),
    s.Region.extend = s.extend,
    s.RegionManager = function(t) {
        var e = t.Controller.extend({
            constructor: function(e) {
                this._regions = {},
                t.Controller.call(this, e)
            },
            addRegions: function(t, e) {
                i.isFunction(t) && (t = t.apply(this, arguments));
                var n = {};
                return i.each(t,
                function(t, s) {
                    i.isString(t) && (t = {
                        selector: t
                    }),
                    t.selector && (t = i.defaults({},
                    t, e));
                    var o = this.addRegion(s, t);
                    n[s] = o
                },
                this),
                n
            },
            addRegion: function(e, i) {
                var n;
                return n = i instanceof t.Region ? i: t.Region.buildRegion(i, t.Region),
                this.triggerMethod("before:add:region", e, n),
                this._store(e, n),
                this.triggerMethod("add:region", e, n),
                n
            },
            get: function(t) {
                return this._regions[t]
            },
            getRegions: function() {
                return i.clone(this._regions)
            },
            removeRegion: function(t) {
                var e = this._regions[t];
                return this._remove(t, e),
                e
            },
            removeRegions: function() {
                var t = this.getRegions();
                return i.each(this._regions,
                function(t, e) {
                    this._remove(e, t)
                },
                this),
                t
            },
            emptyRegions: function() {
                var t = this.getRegions();
                return i.each(t,
                function(t) {
                    t.empty()
                },
                this),
                t
            },
            destroy: function() {
                return this.removeRegions(),
                t.Controller.prototype.destroy.apply(this, arguments)
            },
            _store: function(t, e) {
                this._regions[t] = e,
                this._setLength()
            },
            _remove: function(t, e) {
                this.triggerMethod("before:remove:region", t, e),
                e.empty(),
                e.stopListening(),
                delete this._regions[t],
                this._setLength(),
                this.triggerMethod("remove:region", t, e)
            },
            _setLength: function() {
                this.length = i.size(this._regions)
            }
        });
        return t.actAsCollection(e.prototype, "_regions"),
        e
    } (s),
    s.TemplateCache = function(t) {
        this.templateId = t
    },
    i.extend(s.TemplateCache, {
        templateCaches: {},
        get: function(t) {
            var e = this.templateCaches[t];
            return e || (e = new s.TemplateCache(t), this.templateCaches[t] = e),
            e.load()
        },
        clear: function() {
            var t, e = o.call(arguments),
            i = e.length;
            if (i > 0) for (t = 0; i > t; t++) delete this.templateCaches[e[t]];
            else this.templateCaches = {}
        }
    }),
    i.extend(s.TemplateCache.prototype, {
        load: function() {
            if (this.compiledTemplate) return this.compiledTemplate;
            var t = this.loadTemplate(this.templateId);
            return this.compiledTemplate = this.compileTemplate(t),
            this.compiledTemplate
        },
        loadTemplate: function(t) {
            var i = e.$(t).html();
            if (!i || 0 === i.length) throw new s.Error({
                name: "NoTemplateError",
                message: 'Could not find template: "' + t + '"'
            });
            return i
        },
        compileTemplate: function(t) {
            return i.template(t)
        }
    }),
    s.Renderer = {
        render: function(t, e) {
            if (!t) throw new s.Error({
                name: "TemplateNotFoundError",
                message: "Cannot render the template since its false, null or undefined."
            });
            var i;
            return (i = "function" == typeof t ? t: s.TemplateCache.get(t))(e)
        }
    },
    s.View = e.View.extend({
        constructor: function(t) {
            i.bindAll(this, "render"),
            this.options = i.extend({},
            i.result(this, "options"), i.isFunction(t) ? t.call(this) : t),
            this._behaviors = s.Behaviors(this),
            e.View.apply(this, arguments),
            s.MonitorDOMRefresh(this),
            this.listenTo(this, "show", this.onShowCalled)
        },
        getTemplate: function() {
            return this.getOption("template")
        },
        serializeModel: function(t) {
            return t.toJSON.apply(t, o.call(arguments, 1))
        },
        mixinTemplateHelpers: function(t) {
            t = t || {};
            var e = this.getOption("templateHelpers");
            return i.isFunction(e) && (e = e.call(this)),
            i.extend(t, e)
        },
        normalizeUIKeys: function(t) {
            var e = i.result(this, "ui"),
            n = i.result(this, "_uiBindings");
            return s.normalizeUIKeys(t, n || e)
        },
        normalizeUIValues: function(t) {
            var e = i.result(this, "ui"),
            n = i.result(this, "_uiBindings");
            return s.normalizeUIValues(t, n || e)
        },
        configureTriggers: function() {
            if (this.triggers) {
                var t = {},
                e = this.normalizeUIKeys(i.result(this, "triggers"));
                return i.each(e,
                function(e, i) {
                    t[i] = this._buildViewTrigger(e)
                },
                this),
                t
            }
        },
        delegateEvents: function(t) {
            return this._delegateDOMEvents(t),
            this.bindEntityEvents(this.model, this.getOption("modelEvents")),
            this.bindEntityEvents(this.collection, this.getOption("collectionEvents")),
            i.each(this._behaviors,
            function(t) {
                t.bindEntityEvents(this.model, t.getOption("modelEvents")),
                t.bindEntityEvents(this.collection, t.getOption("collectionEvents"))
            },
            this),
            this
        },
        _delegateDOMEvents: function(t) {
            var n = t || this.events;
            i.isFunction(n) && (n = n.call(this)),
            n = this.normalizeUIKeys(n),
            i.isUndefined(t) && (this.events = n);
            var s = {},
            o = i.result(this, "behaviorEvents") || {},
            r = this.configureTriggers(),
            a = i.result(this, "behaviorTriggers") || {};
            i.extend(s, o, n, r, a),
            e.View.prototype.delegateEvents.call(this, s)
        },
        undelegateEvents: function() {
            var t = o.call(arguments);
            return e.View.prototype.undelegateEvents.apply(this, t),
            this.unbindEntityEvents(this.model, this.getOption("modelEvents")),
            this.unbindEntityEvents(this.collection, this.getOption("collectionEvents")),
            i.each(this._behaviors,
            function(t) {
                t.unbindEntityEvents(this.model, t.getOption("modelEvents")),
                t.unbindEntityEvents(this.collection, t.getOption("collectionEvents"))
            },
            this),
            this
        },
        onShowCalled: function() {},
        _ensureViewIsIntact: function() {
            if (this.isDestroyed) throw new s.Error({
                name: "ViewDestroyedError",
                message: 'View (cid: "' + this.cid + '") has already been destroyed and cannot be used.'
            })
        },
        destroy: function() {
            if (!this.isDestroyed) {
                var t = o.call(arguments);
                return this.triggerMethod.apply(this, ["before:destroy"].concat(t)),
                this.isDestroyed = !0,
                this.triggerMethod.apply(this, ["destroy"].concat(t)),
                this.unbindUIElements(),
                this.remove(),
                i.invoke(this._behaviors, "destroy", t),
                this
            }
        },
        bindUIElements: function() {
            this._bindUIElements(),
            i.invoke(this._behaviors, this._bindUIElements)
        },
        _bindUIElements: function() {
            if (this.ui) {
                this._uiBindings || (this._uiBindings = this.ui);
                var t = i.result(this, "_uiBindings");
                this.ui = {},
                i.each(i.keys(t),
                function(e) {
                    var i = t[e];
                    this.ui[e] = this.$(i)
                },
                this)
            }
        },
        unbindUIElements: function() {
            this._unbindUIElements(),
            i.invoke(this._behaviors, this._unbindUIElements)
        },
        _unbindUIElements: function() {
            this.ui && this._uiBindings && (i.each(this.ui,
            function(t, e) {
                delete this.ui[e]
            },
            this), this.ui = this._uiBindings, delete this._uiBindings)
        },
        _buildViewTrigger: function(t) {
            var e = i.isObject(t),
            n = i.defaults({},
            e ? t: {},
            {
                preventDefault: !0,
                stopPropagation: !0
            }),
            s = e ? n.event: t;
            return function(t) {
                t && (t.preventDefault && n.preventDefault && t.preventDefault(), t.stopPropagation && n.stopPropagation && t.stopPropagation());
                var e = {
                    view: this,
                    model: this.model,
                    collection: this.collection
                };
                this.triggerMethod(s, e)
            }
        },
        setElement: function() {
            var t = e.View.prototype.setElement.apply(this, arguments);
            return i.invoke(this._behaviors, "proxyViewProperties", this),
            t
        },
        triggerMethod: function() {
            var t = arguments,
            e = s.triggerMethod,
            n = e.apply(this, t);
            return i.each(this._behaviors,
            function(i) {
                e.apply(i, t)
            }),
            n
        },
        normalizeMethods: s.normalizeMethods,
        getOption: s.proxyGetOption,
        bindEntityEvents: s.proxyBindEntityEvents,
        unbindEntityEvents: s.proxyUnbindEntityEvents
    }),
    s.ItemView = s.View.extend({
        constructor: function() {
            s.View.apply(this, arguments)
        },
        serializeData: function() {
            var t = {};
            return this.model ? t = i.partial(this.serializeModel, this.model).apply(this, arguments) : this.collection && (t = {
                items: i.partial(this.serializeCollection, this.collection).apply(this, arguments)
            }),
            t
        },
        serializeCollection: function(t) {
            return t.toJSON.apply(t, o.call(arguments, 1))
        },
        render: function() {
            return this._ensureViewIsIntact(),
            this.triggerMethod("before:render", this),
            this._renderTemplate(),
            this.bindUIElements(),
            this.triggerMethod("render", this),
            this
        },
        _renderTemplate: function() {
            var t = this.getTemplate();
            if (t !== !1) {
                if (!t) throw new s.Error({
                    name: "UndefinedTemplateError",
                    message: "Cannot render the template since it is null or undefined."
                });
                var e = this.serializeData();
                e = this.mixinTemplateHelpers(e);
                var i = s.Renderer.render(t, e, this);
                return this.attachElContent(i),
                this
            }
        },
        attachElContent: function(t) {
            return this.$el.html(t),
            this
        },
        destroy: function() {
            return this.isDestroyed ? void 0 : s.View.prototype.destroy.apply(this, arguments)
        }
    }),
    s.CollectionView = s.View.extend({
        childViewEventPrefix: "childview",
        constructor: function(t) {
            var e = t || {};
            this.sort = i.isUndefined(e.sort) ? !0 : e.sort,
            this.once("render", this._initialEvents),
            this._initChildViewStorage(),
            s.View.apply(this, arguments),
            this.initRenderBuffer()
        },
        initRenderBuffer: function() {
            this.elBuffer = document.createDocumentFragment(),
            this._bufferedChildren = []
        },
        startBuffering: function() {
            this.initRenderBuffer(),
            this.isBuffering = !0
        },
        endBuffering: function() {
            this.isBuffering = !1,
            this._triggerBeforeShowBufferedChildren(),
            this.attachBuffer(this, this.elBuffer),
            this._triggerShowBufferedChildren(),
            this.initRenderBuffer()
        },
        _triggerBeforeShowBufferedChildren: function() {
            this._isShown && i.each(this._bufferedChildren, i.partial(this._triggerMethodOnChild, "before:show"))
        },
        _triggerShowBufferedChildren: function() {
            this._isShown && (i.each(this._bufferedChildren, i.partial(this._triggerMethodOnChild, "show")), this._bufferedChildren = [])
        },
        _triggerMethodOnChild: function(t, e) {
            s.triggerMethodOn(e, t)
        },
        _initialEvents: function() {
            this.collection && (this.listenTo(this.collection, "add", this._onCollectionAdd), this.listenTo(this.collection, "remove", this._onCollectionRemove), this.listenTo(this.collection, "reset", this.render), this.sort && this.listenTo(this.collection, "sort", this._sortViews))
        },
        _onCollectionAdd: function(t) {
            this.destroyEmptyView();
            var e = this.getChildView(t),
            i = this.collection.indexOf(t);
            this.addChild(t, e, i)
        },
        _onCollectionRemove: function(t) {
            var e = this.children.findByModel(t);
            this.removeChildView(e),
            this.checkEmpty()
        },
        onShowCalled: function() {
            this.children.each(i.partial(this._triggerMethodOnChild, "show"))
        },
        render: function() {
            return this._ensureViewIsIntact(),
            this.triggerMethod("before:render", this),
            this._renderChildren(),
            this.triggerMethod("render", this),
            this
        },
        resortView: function() {
            this.render()
        },
        _sortViews: function() {
            var t = this.collection.find(function(t, e) {
                var i = this.children.findByModel(t);
                return ! i || i._index !== e
            },
            this);
            t && this.resortView()
        },
        _renderChildren: function() {
            this.destroyEmptyView(),
            this.destroyChildren(),
            this.isEmpty(this.collection) ? this.showEmptyView() : (this.triggerMethod("before:render:collection", this), this.startBuffering(), this.showCollection(), this.endBuffering(), this.triggerMethod("render:collection", this))
        },
        showCollection: function() {
            var t;
            this.collection.each(function(e, i) {
                t = this.getChildView(e),
                this.addChild(e, t, i)
            },
            this)
        },
        showEmptyView: function() {
            var t = this.getEmptyView();
            if (t && !this._showingEmptyView) {
                this.triggerMethod("before:render:empty"),
                this._showingEmptyView = !0;
                var i = new e.Model;
                this.addEmptyView(i, t),
                this.triggerMethod("render:empty")
            }
        },
        destroyEmptyView: function() {
            this._showingEmptyView && (this.triggerMethod("before:remove:empty"), this.destroyChildren(), delete this._showingEmptyView, this.triggerMethod("remove:empty"))
        },
        getEmptyView: function() {
            return this.getOption("emptyView")
        },
        addEmptyView: function(t, e) {
            var n = this.getOption("emptyViewOptions") || this.getOption("childViewOptions");
            i.isFunction(n) && (n = n.call(this));
            var o = this.buildChildView(t, e, n);
            this.proxyChildEvents(o),
            this._isShown && s.triggerMethodOn(o, "before:show"),
            this.children.add(o),
            this.renderChildView(o, -1),
            this._isShown && s.triggerMethodOn(o, "show")
        },
        getChildView: function() {
            var t = this.getOption("childView");
            if (!t) throw new s.Error({
                name: "NoChildViewError",
                message: 'A "childView" must be specified'
            });
            return t
        },
        addChild: function(t, e, n) {
            var s = this.getOption("childViewOptions");
            i.isFunction(s) && (s = s.call(this, t, n));
            var o = this.buildChildView(t, e, s);
            return this._updateIndices(o, !0, n),
            this._addChildView(o, n),
            o
        },
        _updateIndices: function(t, e, i) {
            this.sort && (e ? (t._index = i, this.children.each(function(e) {
                e._index >= t._index && e._index++
            })) : this.children.each(function(e) {
                e._index >= t._index && e._index--
            }))
        },
        _addChildView: function(t, e) {
            this.proxyChildEvents(t),
            this.triggerMethod("before:add:child", t),
            this.children.add(t),
            this.renderChildView(t, e),
            this._isShown && !this.isBuffering && s.triggerMethodOn(t, "show"),
            this.triggerMethod("add:child", t)
        },
        renderChildView: function(t, e) {
            return t.render(),
            this.attachHtml(this, t, e),
            t
        },
        buildChildView: function(t, e, n) {
            var s = i.extend({
                model: t
            },
            n);
            return new e(s)
        },
        removeChildView: function(t) {
            return t && (this.triggerMethod("before:remove:child", t), t.destroy ? t.destroy() : t.remove && t.remove(), this.stopListening(t), this.children.remove(t), this.triggerMethod("remove:child", t), this._updateIndices(t, !1)),
            t
        },
        isEmpty: function() {
            return ! this.collection || 0 === this.collection.length
        },
        checkEmpty: function() {
            this.isEmpty(this.collection) && this.showEmptyView()
        },
        attachBuffer: function(t, e) {
            t.$el.append(e)
        },
        attachHtml: function(t, e, i) {
            t.isBuffering ? (t.elBuffer.appendChild(e.el), t._bufferedChildren.push(e)) : t._insertBefore(e, i) || t._insertAfter(e)
        },
        _insertBefore: function(t, e) {
            var i, n = this.sort && e < this.children.length - 1;
            return n && (i = this.children.find(function(t) {
                return t._index === e + 1
            })),
            i ? (i.$el.before(t.el), !0) : !1
        },
        _insertAfter: function(t) {
            this.$el.append(t.el)
        },
        _initChildViewStorage: function() {
            this.children = new e.ChildViewContainer
        },
        destroy: function() {
            return this.isDestroyed ? void 0 : (this.triggerMethod("before:destroy:collection"), this.destroyChildren(), this.triggerMethod("destroy:collection"), s.View.prototype.destroy.apply(this, arguments))
        },
        destroyChildren: function() {
            var t = this.children.map(i.identity);
            return this.children.each(this.removeChildView, this),
            this.checkEmpty(),
            t
        },
        proxyChildEvents: function(t) {
            var e = this.getOption("childViewEventPrefix");
            this.listenTo(t, "all",
            function() {
                var n = o.call(arguments),
                s = n[0],
                r = this.normalizeMethods(i.result(this, "childEvents"));
                n[0] = e + ":" + s,
                n.splice(1, 0, t),
                "undefined" != typeof r && i.isFunction(r[s]) && r[s].apply(this, n.slice(1)),
                this.triggerMethod.apply(this, n)
            },
            this)
        }
    }),
    s.CompositeView = s.CollectionView.extend({
        constructor: function() {
            s.CollectionView.apply(this, arguments)
        },
        _initialEvents: function() {
            this.collection && (this.listenTo(this.collection, "add", this._onCollectionAdd), this.listenTo(this.collection, "remove", this._onCollectionRemove), this.listenTo(this.collection, "reset", this._renderChildren), this.sort && this.listenTo(this.collection, "sort", this._sortViews))
        },
        getChildView: function() {
            var t = this.getOption("childView") || this.constructor;
            if (!t) throw new s.Error({
                name: "NoChildViewError",
                message: 'A "childView" must be specified'
            });
            return t
        },
        serializeData: function() {
            var t = {};
            return this.model && (t = i.partial(this.serializeModel, this.model).apply(this, arguments)),
            t
        },
        render: function() {
            return this._ensureViewIsIntact(),
            this.isRendered = !0,
            this.resetChildViewContainer(),
            this.triggerMethod("before:render", this),
            this._renderTemplate(),
            this._renderChildren(),
            this.triggerMethod("render", this),
            this
        },
        _renderChildren: function() {
            this.isRendered && s.CollectionView.prototype._renderChildren.call(this)
        },
        _renderTemplate: function() {
            var t = {};
            t = this.serializeData(),
            t = this.mixinTemplateHelpers(t),
            this.triggerMethod("before:render:template");
            var e = this.getTemplate(),
            i = s.Renderer.render(e, t, this);
            this.attachElContent(i),
            this.bindUIElements(),
            this.triggerMethod("render:template")
        },
        attachElContent: function(t) {
            return this.$el.html(t),
            this
        },
        attachBuffer: function(t, e) {
            var i = this.getChildViewContainer(t);
            i.append(e)
        },
        _insertAfter: function(t) {
            var e = this.getChildViewContainer(this);
            e.append(t.el)
        },
        getChildViewContainer: function(t) {
            if ("$childViewContainer" in t) return t.$childViewContainer;
            var e, n = s.getOption(t, "childViewContainer");
            if (n) {
                var o = i.isFunction(n) ? n.call(t) : n;
                if (e = "@" === o.charAt(0) && t.ui ? t.ui[o.substr(4)] : t.$(o), e.length <= 0) throw new s.Error({
                    name: "ChildViewContainerMissingError",
                    message: 'The specified "childViewContainer" was not found: ' + t.childViewContainer
                })
            } else e = t.$el;
            return t.$childViewContainer = e,
            e
        },
        resetChildViewContainer: function() {
            this.$childViewContainer && delete this.$childViewContainer
        }
    }),
    s.LayoutView = s.ItemView.extend({
        regionClass: s.Region,
        constructor: function(t) {
            t = t || {},
            this._firstRender = !0,
            this._initializeRegions(t),
            s.ItemView.call(this, t)
        },
        render: function() {
            return this._ensureViewIsIntact(),
            this._firstRender ? this._firstRender = !1 : this._reInitializeRegions(),
            s.ItemView.prototype.render.apply(this, arguments)
        },
        destroy: function() {
            return this.isDestroyed ? this: (this.regionManager.destroy(), s.ItemView.prototype.destroy.apply(this, arguments))
        },
        addRegion: function(t, e) {
            var i = {};
            return i[t] = e,
            this._buildRegions(i)[t]
        },
        addRegions: function(t) {
            return this.regions = i.extend({},
            this.regions, t),
            this._buildRegions(t)
        },
        removeRegion: function(t) {
            return delete this.regions[t],
            this.regionManager.removeRegion(t)
        },
        getRegion: function(t) {
            return this.regionManager.get(t)
        },
        getRegions: function() {
            return this.regionManager.getRegions()
        },
        _buildRegions: function(t) {
            var e = this,
            i = {
                regionClass: this.getOption("regionClass"),
                parentEl: function() {
                    return e.$el
                }
            };
            return this.regionManager.addRegions(t, i)
        },
        _initializeRegions: function(t) {
            var e;
            this._initRegionManager(),
            e = i.isFunction(this.regions) ? this.regions(t) : this.regions || {};
            var n = this.getOption.call(t, "regions");
            i.isFunction(n) && (n = n.call(this, t)),
            i.extend(e, n),
            e = this.normalizeUIValues(e),
            this.addRegions(e)
        },
        _reInitializeRegions: function() {
            this.regionManager.emptyRegions(),
            this.regionManager.each(function(t) {
                t.reset()
            })
        },
        getRegionManager: function() {
            return new s.RegionManager
        },
        _initRegionManager: function() {
            this.regionManager = this.getRegionManager(),
            this.listenTo(this.regionManager, "before:add:region",
            function(t) {
                this.triggerMethod("before:add:region", t)
            }),
            this.listenTo(this.regionManager, "add:region",
            function(t, e) {
                this[t] = e,
                this.triggerMethod("add:region", t, e)
            }),
            this.listenTo(this.regionManager, "before:remove:region",
            function(t) {
                this.triggerMethod("before:remove:region", t)
            }),
            this.listenTo(this.regionManager, "remove:region",
            function(t, e) {
                delete this[t],
                this.triggerMethod("remove:region", t, e)
            })
        }
    }),
    s.Behavior = function(t, e) {
        function i(e, i) {
            this.view = i,
            this.defaults = t.result(this, "defaults") || {},
            this.options = t.extend({},
            this.defaults, e),
            this.$ = function() {
                return this.view.$.apply(this.view, arguments)
            },
            this.initialize.apply(this, arguments)
        }
        return t.extend(i.prototype, e.Events, {
            initialize: function() {},
            destroy: function() {
                this.stopListening()
            },
            proxyViewProperties: function(t) {
                this.$el = t.$el,
                this.el = t.el
            },
            triggerMethod: s.triggerMethod,
            getOption: s.proxyGetOption,
            bindEntityEvents: s.proxyBindEntityEvents,
            unbindEntityEvents: s.proxyUnbindEntityEvents
        }),
        i.extend = s.extend,
        i
    } (i, e),
    s.Behaviors = function(t, e) {
        function i(t, n) {
            return e.isObject(t.behaviors) ? (n = i.parseBehaviors(t, n || e.result(t, "behaviors")), i.wrap(t, n, e.keys(s)), n) : {}
        }
        function n(t, i) {
            this._view = t,
            this._viewUI = e.result(t, "ui"),
            this._behaviors = i,
            this._triggers = {}
        }
        var s = {
            behaviorTriggers: function(t, e) {
                var i = new n(this, e);
                return i.buildBehaviorTriggers()
            },
            behaviorEvents: function(i, n) {
                var s = {},
                o = e.result(this, "ui");
                return e.each(n,
                function(i, n) {
                    var r = {},
                    a = e.clone(e.result(i, "events")) || {},
                    l = e.result(i, "ui"),
                    c = e.extend({},
                    o, l);
                    a = t.normalizeUIKeys(a, c),
                    e.each(e.keys(a),
                    function(t) {
                        var s = new Array(n + 2).join(" "),
                        o = t + s,
                        l = e.isFunction(a[t]) ? a[t] : i[a[t]];
                        r[o] = e.bind(l, i)
                    }),
                    s = e.extend(s, r)
                }),
                s
            }
        };
        return e.extend(i, {
            behaviorsLookup: function() {
                throw new t.Error({
                    message: "You must define where your behaviors are stored.",
                    url: "marionette.behaviors.html#behaviorslookup"
                })
            },
            getBehaviorClass: function(t, n) {
                return t.behaviorClass ? t.behaviorClass: e.isFunction(i.behaviorsLookup) ? i.behaviorsLookup.apply(this, arguments)[n] : i.behaviorsLookup[n]
            },
            parseBehaviors: function(t, n) {
                return e.chain(n).map(function(n, s) {
                    var o = i.getBehaviorClass(n, s),
                    r = new o(n, t),
                    a = i.parseBehaviors(t, e.result(r, "behaviors"));
                    return [r].concat(a)
                }).flatten().value()
            },
            wrap: function(t, i, n) {
                e.each(n,
                function(n) {
                    t[n] = e.partial(s[n], t[n], i)
                })
            }
        }),
        e.extend(n.prototype, {
            buildBehaviorTriggers: function() {
                return e.each(this._behaviors, this._buildTriggerHandlersForBehavior, this),
                this._triggers
            },
            _buildTriggerHandlersForBehavior: function(i, n) {
                var s = e.extend({},
                this._viewUI, e.result(i, "ui")),
                o = e.clone(e.result(i, "triggers")) || {};
                o = t.normalizeUIKeys(o, s),
                e.each(o, e.partial(this._setHandlerForBehavior, i, n), this)
            },
            _setHandlerForBehavior: function(t, e, i, n) {
                var s = n.replace(/^\S+/,
                function(t) {
                    return t + ".behaviortriggers" + e
                });
                this._triggers[s] = this._view._buildViewTrigger(i)
            }
        }),
        i
    } (s, i),
    s.AppRouter = e.Router.extend({
        constructor: function(t) {
            e.Router.apply(this, arguments),
            this.options = t || {};
            var i = this.getOption("appRoutes"),
            n = this._getController();
            this.processAppRoutes(n, i),
            this.on("route", this._processOnRoute, this)
        },
        appRoute: function(t, e) {
            var i = this._getController();
            this._addAppRoute(i, t, e)
        },
        _processOnRoute: function(t, e) {
            var n = i.invert(this.getOption("appRoutes"))[t];
            i.isFunction(this.onRoute) && this.onRoute(t, n, e)
        },
        processAppRoutes: function(t, e) {
            if (e) {
                var n = i.keys(e).reverse();
                i.each(n,
                function(i) {
                    this._addAppRoute(t, i, e[i])
                },
                this)
            }
        },
        _getController: function() {
            return this.getOption("controller")
        },
        _addAppRoute: function(t, e, n) {
            var o = t[n];
            if (!o) throw new s.Error('Method "' + n + '" was not found on the controller');
            this.route(e, n, i.bind(o, t))
        },
        getOption: s.proxyGetOption
    }),
    s.Application = function(t) {
        this.options = t,
        this._initializeRegions(t),
        this._initCallbacks = new s.Callbacks,
        this.submodules = {},
        i.extend(this, t),
        this._initChannel(),
        this.initialize.apply(this, arguments)
    },
    i.extend(s.Application.prototype, e.Events, {
        initialize: function() {},
        execute: function() {
            this.commands.execute.apply(this.commands, arguments)
        },
        request: function() {
            return this.reqres.request.apply(this.reqres, arguments)
        },
        addInitializer: function(t) {
            this._initCallbacks.add(t)
        },
        start: function(t) {
            this.triggerMethod("before:start", t),
            this._initCallbacks.run(t, this),
            this.triggerMethod("start", t)
        },
        addRegions: function(t) {
            return this._regionManager.addRegions(t)
        },
        emptyRegions: function() {
            return this._regionManager.emptyRegions()
        },
        removeRegion: function(t) {
            return this._regionManager.removeRegion(t)
        },
        getRegion: function(t) {
            return this._regionManager.get(t)
        },
        getRegions: function() {
            return this._regionManager.getRegions()
        },
        module: function(t, e) {
            var i = s.Module.getClass(e),
            n = o.call(arguments);
            return n.unshift(this),
            i.create.apply(i, n)
        },
        getRegionManager: function() {
            return new s.RegionManager
        },
        _initializeRegions: function(t) {
            var e = i.isFunction(this.regions) ? this.regions(t) : this.regions || {};
            this._initRegionManager();
            var n = s.getOption(t, "regions");
            return i.isFunction(n) && (n = n.call(this, t)),
            i.extend(e, n),
            this.addRegions(e),
            this
        },
        _initRegionManager: function() {
            this._regionManager = this.getRegionManager(),
            this.listenTo(this._regionManager, "before:add:region",
            function(t) {
                this.triggerMethod("before:add:region", t)
            }),
            this.listenTo(this._regionManager, "add:region",
            function(t, e) {
                this[t] = e,
                this.triggerMethod("add:region", t, e)
            }),
            this.listenTo(this._regionManager, "before:remove:region",
            function(t) {
                this.triggerMethod("before:remove:region", t)
            }),
            this.listenTo(this._regionManager, "remove:region",
            function(t, e) {
                delete this[t],
                this.triggerMethod("remove:region", t, e)
            })
        },
        _initChannel: function() {
            this.channelName = i.result(this, "channelName") || "global",
            this.channel = i.result(this, "channel") || e.Wreqr.radio.channel(this.channelName),
            this.vent = i.result(this, "vent") || this.channel.vent,
            this.commands = i.result(this, "commands") || this.channel.commands,
            this.reqres = i.result(this, "reqres") || this.channel.reqres
        },
        triggerMethod: s.triggerMethod,
        getOption: s.proxyGetOption
    }),
    s.Application.extend = s.extend,
    s.Module = function(t, e, n) {
        this.moduleName = t,
        this.options = i.extend({},
        this.options, n),
        this.initialize = n.initialize || this.initialize,
        this.submodules = {},
        this._setupInitializersAndFinalizers(),
        this.app = e,
        i.isFunction(this.initialize) && this.initialize(t, e, this.options)
    },
    s.Module.extend = s.extend,
    i.extend(s.Module.prototype, e.Events, {
        startWithParent: !0,
        initialize: function() {},
        addInitializer: function(t) {
            this._initializerCallbacks.add(t)
        },
        addFinalizer: function(t) {
            this._finalizerCallbacks.add(t)
        },
        start: function(t) {
            this._isInitialized || (i.each(this.submodules,
            function(e) {
                e.startWithParent && e.start(t)
            }), this.triggerMethod("before:start", t), this._initializerCallbacks.run(t, this), this._isInitialized = !0, this.triggerMethod("start", t))
        },
        stop: function() {
            this._isInitialized && (this._isInitialized = !1, this.triggerMethod("before:stop"), i.each(this.submodules,
            function(t) {
                t.stop()
            }), this._finalizerCallbacks.run(void 0, this), this._initializerCallbacks.reset(), this._finalizerCallbacks.reset(), this.triggerMethod("stop"))
        },
        addDefinition: function(t, e) {
            this._runModuleDefinition(t, e)
        },
        _runModuleDefinition: function(t, n) {
            if (t) {
                var o = i.flatten([this, this.app, e, s, e.$, i, n]);
                t.apply(this, o)
            }
        },
        _setupInitializersAndFinalizers: function() {
            this._initializerCallbacks = new s.Callbacks,
            this._finalizerCallbacks = new s.Callbacks
        },
        triggerMethod: s.triggerMethod
    }),
    i.extend(s.Module, {
        create: function(t, e, n) {
            var s = t,
            r = o.call(arguments);
            r.splice(0, 3),
            e = e.split(".");
            var a = e.length,
            l = [];
            return l[a - 1] = n,
            i.each(e,
            function(e, i) {
                var o = s;
                s = this._getModule(o, e, t, n),
                this._addModuleDefinition(o, s, l[i], r)
            },
            this),
            s
        },
        _getModule: function(t, e, n, s) {
            var o = i.extend({},
            s),
            r = this.getClass(s),
            a = t[e];
            return a || (a = new r(e, n, o), t[e] = a, t.submodules[e] = a),
            a
        },
        getClass: function(t) {
            var e = s.Module;
            return t ? t.prototype instanceof e ? t: t.moduleClass || e: e
        },
        _addModuleDefinition: function(t, e, i, n) {
            var s = this._getDefine(i),
            o = this._getStartWithParent(i, e);
            s && e.addDefinition(s, n),
            this._addStartWithParent(t, e, o)
        },
        _getStartWithParent: function(t, e) {
            var n;
            return i.isFunction(t) && t.prototype instanceof s.Module ? (n = e.constructor.prototype.startWithParent, i.isUndefined(n) ? !0 : n) : i.isObject(t) ? (n = t.startWithParent, i.isUndefined(n) ? !0 : n) : !0
        },
        _getDefine: function(t) {
            return ! i.isFunction(t) || t.prototype instanceof s.Module ? i.isObject(t) ? t.define: null: t
        },
        _addStartWithParent: function(t, e, i) {
            e.startWithParent = e.startWithParent && i,
            e.startWithParent && !e.startWithParentIsConfigured && (e.startWithParentIsConfigured = !0, t.addInitializer(function(t) {
                e.startWithParent && e.start(t)
            }))
        }
    }),
    s
}),
define("msgbus", ["backbone.wreqr"],
function(t) {
    "use strict";
    return {
        reqres: new t.RequestResponse,
        commands: new t.Commands,
        events: new t.EventAggregator
    }
}),
define("models/Component", ["backbone"],
function(t) {
    "use strict";
    return t.Model.extend({
        initialize: function() {},
        defaults: {
            componentId: "",
            componentName: "组件名",
            free: !0,
            templateId: "",
            themeId: "",
            hasMarginTop: "no"
        },
        validate: function() {},
        getValues: function(t) {
            var e = $.extend(!0, {},
            t);
            if ("products" == e.componentType) {
                var i = e.data;
                for (var n in e.data) {
                    var s = [],
                    o = e.data[n].products;
                    for (var r in o) s.push({
                        id: o[r].id
                    });
                    i[n].products = s
                }
                e.activeTab = 0,
                e.data = i
            }
           /* if ("exttypeset" == e.componentType) {
                e.toggle = null;
                var i = {};
                for (var n in e.data) i[n] = {
                    id: e.data[n].id,
                    visible: e.data[n].visible
                };
                e.data = i
            }*/
            return e
        }
    })
}),
define("collections/ComponentList", ["backbone", "models/Component"],
function(t, e) {
    "use strict";
    return t.Collection.extend({
        model: e
    })
}),
define("collections/PageElementList", ["backbone", "models/Component"],
function(t, e) {
    "use strict";
    return t.Collection.extend({
        model: e
    })
}),
define("models/Page", ["backbone"],
function(t) {
    "use strict";
    return t.Model.extend({
        initialize: function() {},
        defaults: {},
        validate: function() {}
    })
}),
define("collections/PageList", ["backbone", "models/Page"],
function(t, e) {
    "use strict";
    return t.Collection.extend({
        model: e
    })
}),
define("collections", ["require", "collections/ComponentList", "collections/PageElementList", "collections/PageList"],
function(t) {
    "use strict";
    var e = t("collections/ComponentList"),
    i = t("collections/PageElementList"),
    n = t("collections/PageList");
    return {
        componentList: new e,
        pageElementList: new i,
        pageList: new n
    }
}),
define("text", ["module"],
function(t) {
    "use strict";
    var e, i, n, s, o, r = ["Msxml2.XMLHTTP", "Microsoft.XMLHTTP", "Msxml2.XMLHTTP.4.0"],
    a = /^\s*<\?xml(\s)+version=[\'\"](\d)*.(\d)*[\'\"](\s)*\?>/im,
    l = /<body[^>]*>\s*([\s\S]+)\s*<\/body>/im,
    c = "undefined" != typeof location && location.href,
    h = c && location.protocol && location.protocol.replace(/\:/, ""),
    u = c && location.hostname,
    d = c && (location.port || void 0),
    p = {},
    f = t.config && t.config() || {};
    return e = {
        version: "2.0.14",
        strip: function(t) {
            if (t) {
                t = t.replace(a, "");
                var e = t.match(l);
                e && (t = e[1])
            } else t = "";
            return t
        },
        jsEscape: function(t) {
            return t.replace(/(['\\])/g, "\\$1").replace(/[\f]/g, "\\f").replace(/[\b]/g, "\\b").replace(/[\n]/g, "\\n").replace(/[\t]/g, "\\t").replace(/[\r]/g, "\\r").replace(/[\u2028]/g, "\\u2028").replace(/[\u2029]/g, "\\u2029")
        },
        createXhr: f.createXhr ||
        function() {
            var t, e, i;
            if ("undefined" != typeof XMLHttpRequest) return new XMLHttpRequest;
            if ("undefined" != typeof ActiveXObject) for (e = 0; 3 > e; e += 1) {
                i = r[e];
                try {
                    t = new ActiveXObject(i)
                } catch(n) {}
                if (t) {
                    r = [i];
                    break
                }
            }
            return t
        },
        parseName: function(t) {
            var e, i, n, s = !1,
            o = t.lastIndexOf("."),
            r = 0 === t.indexOf("./") || 0 === t.indexOf("../");
            return - 1 !== o && (!r || o > 1) ? (e = t.substring(0, o), i = t.substring(o + 1)) : e = t,
            n = i || e,
            o = n.indexOf("!"),
            -1 !== o && (s = "strip" === n.substring(o + 1), n = n.substring(0, o), i ? i = n: e = n),
            {
                moduleName: e,
                ext: i,
                strip: s
            }
        },
        xdRegExp: /^((\w+)\:)?\/\/([^\/\\]+)/,
        useXhr: function(t, i, n, s) {
            var o, r, a, l = e.xdRegExp.exec(t);
            return l ? (o = l[2], r = l[3], r = r.split(":"), a = r[1], r = r[0], !(o && o !== i || r && r.toLowerCase() !== n.toLowerCase() || (a || r) && a !== s)) : !0
        },
        finishLoad: function(t, i, n, s) {
            n = i ? e.strip(n) : n,
            f.isBuild && (p[t] = n),
            s(n)
        },
        load: function(t, i, n, s) {
            if (s && s.isBuild && !s.inlineText) return void n();
            f.isBuild = s && s.isBuild;
            var o = e.parseName(t),
            r = o.moduleName + (o.ext ? "." + o.ext: ""),
            a = i.toUrl(r),
            l = f.useXhr || e.useXhr;
            return 0 === a.indexOf("empty:") ? void n() : void(!c || l(a, h, u, d) ? e.get(a,
            function(i) {
                e.finishLoad(t, o.strip, i, n)
            },
            function(t) {
                n.error && n.error(t)
            }) : i([r],
            function(t) {
                e.finishLoad(o.moduleName + "." + o.ext, o.strip, t, n)
            }))
        },
        write: function(t, i, n) {
            if (p.hasOwnProperty(i)) {
                var s = e.jsEscape(p[i]);
                n.asModule(t + "!" + i, "define(function () { return '" + s + "';});\n")
            }
        },
        writeFile: function(t, i, n, s, o) {
            var r = e.parseName(i),
            a = r.ext ? "." + r.ext: "",
            l = r.moduleName + a,
            c = n.toUrl(r.moduleName + a) + ".js";
            e.load(l, n,
            function() {
                var i = function(t) {
                    return s(c, t)
                };
                i.asModule = function(t, e) {
                    return s.asModule(t, c, e)
                },
                e.write(t, l, i, o)
            },
            o)
        }
    },
    "node" === f.env || !f.env && "undefined" != typeof process && process.versions && process.versions.node && !process.versions["node-webkit"] && !process.versions["atom-shell"] ? (i = require.nodeRequire("fs"), e.get = function(t, e, n) {
        try {
            var s = i.readFileSync(t, "utf8");
            "﻿" === s[0] && (s = s.substring(1)),
            e(s)
        } catch(o) {
            n && n(o)
        }
    }) : "xhr" === f.env || !f.env && e.createXhr() ? e.get = function(t, i, n, s) {
        var o, r = e.createXhr();
        if (r.open("GET", t, !0), s) for (o in s) s.hasOwnProperty(o) && r.setRequestHeader(o.toLowerCase(), s[o]);
        f.onXhr && f.onXhr(r, t),
        r.onreadystatechange = function() {
            var e, s;
            4 === r.readyState && (e = r.status || 0, e > 399 && 600 > e ? (s = new Error(t + " HTTP status: " + e), s.xhr = r, n && n(s)) : i(r.responseText), f.onXhrComplete && f.onXhrComplete(r, t))
        },
        r.send(null)
    }: "rhino" === f.env || !f.env && "undefined" != typeof Packages && "undefined" != typeof java ? e.get = function(t, e) {
        var i, n, s = "utf-8",
        o = new java.io.File(t),
        r = java.lang.System.getProperty("line.separator"),
        a = new java.io.BufferedReader(new java.io.InputStreamReader(new java.io.FileInputStream(o), s)),
        l = "";
        try {
            for (i = new java.lang.StringBuffer, n = a.readLine(), n && n.length() && 65279 === n.charAt(0) && (n = n.substring(1)), null !== n && i.append(n); null !== (n = a.readLine());) i.append(r),
            i.append(n);
            l = String(i.toString())
        } finally {
            a.close()
        }
        e(l)
    }: ("xpconnect" === f.env || !f.env && "undefined" != typeof Components && Components.classes && Components.interfaces) && (n = Components.classes, s = Components.interfaces, Components.utils["import"]("resource://gre/modules/FileUtils.jsm"), o = "@mozilla.org/windows-registry-key;1" in n, e.get = function(t, e) {
        var i, r, a, l = {};
        o && (t = t.replace(/\//g, "\\")),
        a = new FileUtils.File(t);
        try {
            i = n["@mozilla.org/network/file-input-stream;1"].createInstance(s.nsIFileInputStream),
            i.init(a, 1, 0, !1),
            r = n["@mozilla.org/intl/converter-input-stream;1"].createInstance(s.nsIConverterInputStream),
            r.init(i, "utf-8", i.available(), s.nsIConverterInputStream.DEFAULT_REPLACEMENT_CHARACTER),
            r.readString(i.available(), l),
            r.close(),
            i.close(),
            e(l.value)
        } catch(c) {
            throw new Error((a && a.path || "") + ": " + c)
        }
    }),
    e
}),
define("text!templates/pageItemView.tmpl", [],
function() {
    return '<div class="view">\n    <label><%= name %></label><a href= "/pages/<%= id %>">编辑</a>\n    <% if (home == 0 && McMore.current_page.id != id) { %>\n        <a class="delete-page" data-id="<%= id %>" href="javascript:void(0)">删除</a>\n    <% } %>\n</div>\n<input class="edit" value="<%= name %>">'
}),
define("text!templates/pageItemCompositeView.tmpl", [],
function() {
    return '<div class="top">\n    <form>\n    <div class="search"><input type="text" id="search-field" placeholder="搜索页面"/></div>\n    <ul class="list-unstyled">\n    <li class="action-1" id="add-page">添加</li>\n    </ul>\n\n    </form>\n</div>\n<div class="content">\n    <ul id="page-list" class="list-unstyled">\n\n    </ul>\n</div>'
}),
define("text!templates/pageElementView.tmpl", [],
function() {
    return '<ul class="list-unstyled">\n  <li>商品列表</li>\n  <li>商品列表</li>\n  <li>商品列表</li>\n  <li>商品列表</li>\n  <li>商品列表</li>\n  <li>商品列表</li>\n  <li>商品列表</li>\n  <li>商品列表</li>\n</ul>'
}),
define("text!templates/componentItemView.tmpl", [],
function() {
    return '<div class="component {{ alias }}" data-component="{{ alias }}">\n    <div class="thumb"></div>\n    <div class="title">{{ title }}</div>\n</div>'
}),
define("text!templates/componentItemCompositeView.tmpl", [],
function() {
    return '<div class="top"><span>全部部件</span></div>\n<div class="content" id="component-list">\n\n</div>'
}),
define("templates", ["require", "text!templates/pageItemView.tmpl", "text!templates/pageItemCompositeView.tmpl", "text!templates/pageElementView.tmpl", "text!templates/componentItemView.tmpl", "text!templates/componentItemCompositeView.tmpl"],
function(t) {
    "use strict";
    return {
        pageItemView: t("text!templates/pageItemView.tmpl"),
        pageItemCompositeView: t("text!templates/pageItemCompositeView.tmpl"),
        pageElementView: t("text!templates/pageElementView.tmpl"),
        componentItemView: t("text!templates/componentItemView.tmpl"),
        componentItemCompositeView: t("text!templates/componentItemCompositeView.tmpl")
    }
}),
define("views/PageItemView", ["backbone.marionette", "mustache", "templates"],
function(t, e, i) {
    "use strict";
    return t.ItemView.extend({
        template: function(t) {
            return _.template(i.pageItemView, t)
        },
        tagName: "li",
        className: function() {
            return this.model.get("id") == McMore.current_page.id ? "active": void 0
        },
        ui: {
            deletePage: "#delete_page",
            edit: ".edit",
            label: "label"
        },
        events: {
            "click @ui.label": "onSelectPage",
            "dblclick @ui.label": "onEditPage",
            "keydown @ui.edit": "onEditKeydown",
            "focusout @ui.edit": "onEditFocusout"
        },
        modelEvents: {
            change: "render"
        },
        onSelectPage: function() {
            this.model.set("selected", 1),
            this.$el.addClass("selecting")
        },
        onEditPage: function() {
            this.$el.addClass("editing"),
            this.ui.edit.focus()
        },
        onEditFocusout: function() {
            var t = this.ui.edit.val().trim();
            t ? (this.model.set("name", t), this.$el.removeClass("editing"), $.ajax({
                url: "/pages/update/" + this.model.get("id"),
                data: {
                    name: t
                },
                dataType: "json",
                type: "post"
            })) : this._restore()
        },
        onEditKeydown: function(t) {
            var e = 13,
            i = 27;
            switch (t.which) {
            case e:
                this.onEditFocusout();
                break;
            case i:
                this._restore()
            }
        },
        _restore: function() {
            this.ui.edit.val(this.model.get("name")),
            this.$el.removeClass("editing")
        }
    })
}),
function(t) {
    "function" == typeof define && define.amd ? define("jquery-ui", ["jquery"], t) : t(jQuery)
} (function(t) {
    function e(e, n) {
        var s, o, r, a = e.nodeName.toLowerCase();
        return "area" === a ? (s = e.parentNode, o = s.name, e.href && o && "map" === s.nodeName.toLowerCase() ? (r = t("img[usemap='#" + o + "']")[0], !!r && i(r)) : !1) : (/^(input|select|textarea|button|object)$/.test(a) ? !e.disabled: "a" === a ? e.href || n: n) && i(e)
    }
    function i(e) {
        return t.expr.filters.visible(e) && !t(e).parents().addBack().filter(function() {
            return "hidden" === t.css(this, "visibility")
        }).length
    }
    function n(t) {
        for (var e, i; t.length && t[0] !== document;) {
            if (e = t.css("position"), ("absolute" === e || "relative" === e || "fixed" === e) && (i = parseInt(t.css("zIndex"), 10), !isNaN(i) && 0 !== i)) return i;
            t = t.parent()
        }
        return 0
    }
    function s() {
        this._curInst = null,
        this._keyEvent = !1,
        this._disabledInputs = [],
        this._datepickerShowing = !1,
        this._inDialog = !1,
        this._mainDivId = "ui-datepicker-div",
        this._inlineClass = "ui-datepicker-inline",
        this._appendClass = "ui-datepicker-append",
        this._triggerClass = "ui-datepicker-trigger",
        this._dialogClass = "ui-datepicker-dialog",
        this._disableClass = "ui-datepicker-disabled",
        this._unselectableClass = "ui-datepicker-unselectable",
        this._currentClass = "ui-datepicker-current-day",
        this._dayOverClass = "ui-datepicker-days-cell-over",
        this.regional = [],
        this.regional[""] = {
            closeText: "Done",
            prevText: "Prev",
            nextText: "Next",
            currentText: "Today",
            monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            dayNamesShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            dayNamesMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
            weekHeader: "Wk",
            dateFormat: "mm/dd/yy",
            firstDay: 0,
            isRTL: !1,
            showMonthAfterYear: !1,
            yearSuffix: ""
        },
        this._defaults = {
            showOn: "focus",
            showAnim: "fadeIn",
            showOptions: {},
            defaultDate: null,
            appendText: "",
            buttonText: "...",
            buttonImage: "",
            buttonImageOnly: !1,
            hideIfNoPrevNext: !1,
            navigationAsDateFormat: !1,
            gotoCurrent: !1,
            changeMonth: !1,
            changeYear: !1,
            yearRange: "c-10:c+10",
            showOtherMonths: !1,
            selectOtherMonths: !1,
            showWeek: !1,
            calculateWeek: this.iso8601Week,
            shortYearCutoff: "+10",
            minDate: null,
            maxDate: null,
            duration: "fast",
            beforeShowDay: null,
            beforeShow: null,
            onSelect: null,
            onChangeMonthYear: null,
            onClose: null,
            numberOfMonths: 1,
            showCurrentAtPos: 0,
            stepMonths: 1,
            stepBigMonths: 12,
            altField: "",
            altFormat: "",
            constrainInput: !0,
            showButtonPanel: !1,
            autoSize: !1,
            disabled: !1
        },
        t.extend(this._defaults, this.regional[""]),
        this.regional.en = t.extend(!0, {},
        this.regional[""]),
        this.regional["en-US"] = t.extend(!0, {},
        this.regional.en),
        this.dpDiv = o(t("<div id='" + this._mainDivId + "' class='ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all'></div>"))
    }
    function o(e) {
        var i = "button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a";
        return e.delegate(i, "mouseout",
        function() {
            t(this).removeClass("ui-state-hover"),
            -1 !== this.className.indexOf("ui-datepicker-prev") && t(this).removeClass("ui-datepicker-prev-hover"),
            -1 !== this.className.indexOf("ui-datepicker-next") && t(this).removeClass("ui-datepicker-next-hover")
        }).delegate(i, "mouseover", r)
    }
    function r() {
        t.datepicker._isDisabledDatepicker(v.inline ? v.dpDiv.parent()[0] : v.input[0]) || (t(this).parents(".ui-datepicker-calendar").find("a").removeClass("ui-state-hover"), t(this).addClass("ui-state-hover"), -1 !== this.className.indexOf("ui-datepicker-prev") && t(this).addClass("ui-datepicker-prev-hover"), -1 !== this.className.indexOf("ui-datepicker-next") && t(this).addClass("ui-datepicker-next-hover"))
    }
    function a(e, i) {
        t.extend(e, i);
        for (var n in i) null == i[n] && (e[n] = i[n]);
        return e
    }
    function l(t) {
        return function() {
            var e = this.element.val();
            t.apply(this, arguments),
            this._refresh(),
            e !== this.element.val() && this._trigger("change")
        }
    }
    t.ui = t.ui || {},
    t.extend(t.ui, {
        version: "1.11.4",
        keyCode: {
            BACKSPACE: 8,
            COMMA: 188,
            DELETE: 46,
            DOWN: 40,
            END: 35,
            ENTER: 13,
            ESCAPE: 27,
            HOME: 36,
            LEFT: 37,
            PAGE_DOWN: 34,
            PAGE_UP: 33,
            PERIOD: 190,
            RIGHT: 39,
            SPACE: 32,
            TAB: 9,
            UP: 38
        }
    }),
    t.fn.extend({
        scrollParent: function(e) {
            var i = this.css("position"),
            n = "absolute" === i,
            s = e ? /(auto|scroll|hidden)/: /(auto|scroll)/,
            o = this.parents().filter(function() {
                var e = t(this);
                return n && "static" === e.css("position") ? !1 : s.test(e.css("overflow") + e.css("overflow-y") + e.css("overflow-x"))
            }).eq(0);
            return "fixed" !== i && o.length ? o: t(this[0].ownerDocument || document)
        },
        uniqueId: function() {
            var t = 0;
            return function() {
                return this.each(function() {
                    this.id || (this.id = "ui-id-" + ++t)
                })
            }
        } (),
        removeUniqueId: function() {
            return this.each(function() { / ^ui - id - \d + $ / .test(this.id) && t(this).removeAttr("id")
            })
        }
    }),
    t.extend(t.expr[":"], {
        data: t.expr.createPseudo ? t.expr.createPseudo(function(e) {
            return function(i) {
                return !! t.data(i, e)
            }
        }) : function(e, i, n) {
            return !! t.data(e, n[3])
        },
        focusable: function(i) {
            return e(i, !isNaN(t.attr(i, "tabindex")))
        },
        tabbable: function(i) {
            var n = t.attr(i, "tabindex"),
            s = isNaN(n);
            return (s || n >= 0) && e(i, !s)
        }
    }),
    t("<a>").outerWidth(1).jquery || t.each(["Width", "Height"],
    function(e, i) {
        function n(e, i, n, o) {
            return t.each(s,
            function() {
                i -= parseFloat(t.css(e, "padding" + this)) || 0,
                n && (i -= parseFloat(t.css(e, "border" + this + "Width")) || 0),
                o && (i -= parseFloat(t.css(e, "margin" + this)) || 0)
            }),
            i
        }
        var s = "Width" === i ? ["Left", "Right"] : ["Top", "Bottom"],
        o = i.toLowerCase(),
        r = {
            innerWidth: t.fn.innerWidth,
            innerHeight: t.fn.innerHeight,
            outerWidth: t.fn.outerWidth,
            outerHeight: t.fn.outerHeight
        };
        t.fn["inner" + i] = function(e) {
            return void 0 === e ? r["inner" + i].call(this) : this.each(function() {
                t(this).css(o, n(this, e) + "px")
            })
        },
        t.fn["outer" + i] = function(e, s) {
            return "number" != typeof e ? r["outer" + i].call(this, e) : this.each(function() {
                t(this).css(o, n(this, e, !0, s) + "px")
            })
        }
    }),
    t.fn.addBack || (t.fn.addBack = function(t) {
        return this.add(null == t ? this.prevObject: this.prevObject.filter(t))
    }),
    t("<a>").data("a-b", "a").removeData("a-b").data("a-b") && (t.fn.removeData = function(e) {
        return function(i) {
            return arguments.length ? e.call(this, t.camelCase(i)) : e.call(this)
        }
    } (t.fn.removeData)),
    t.ui.ie = !!/msie [\w.]+/.exec(navigator.userAgent.toLowerCase()),
    t.fn.extend({
        focus: function(e) {
            return function(i, n) {
                return "number" == typeof i ? this.each(function() {
                    var e = this;
                    setTimeout(function() {
                        t(e).focus(),
                        n && n.call(e)
                    },
                    i)
                }) : e.apply(this, arguments)
            }
        } (t.fn.focus),
        disableSelection: function() {
            var t = "onselectstart" in document.createElement("div") ? "selectstart": "mousedown";
            return function() {
                return this.bind(t + ".ui-disableSelection",
                function(t) {
                    t.preventDefault()
                })
            }
        } (),
        enableSelection: function() {
            return this.unbind(".ui-disableSelection")
        },
        zIndex: function(e) {
            if (void 0 !== e) return this.css("zIndex", e);
            if (this.length) for (var i, n, s = t(this[0]); s.length && s[0] !== document;) {
                if (i = s.css("position"), ("absolute" === i || "relative" === i || "fixed" === i) && (n = parseInt(s.css("zIndex"), 10), !isNaN(n) && 0 !== n)) return n;
                s = s.parent()
            }
            return 0
        }
    }),
    t.ui.plugin = {
        add: function(e, i, n) {
            var s, o = t.ui[e].prototype;
            for (s in n) o.plugins[s] = o.plugins[s] || [],
            o.plugins[s].push([i, n[s]])
        },
        call: function(t, e, i, n) {
            var s, o = t.plugins[e];
            if (o && (n || t.element[0].parentNode && 11 !== t.element[0].parentNode.nodeType)) for (s = 0; s < o.length; s++) t.options[o[s][0]] && o[s][1].apply(t.element, i)
        }
    };
    var c = 0,
    h = Array.prototype.slice;
    t.cleanData = function(e) {
        return function(i) {
            var n, s, o;
            for (o = 0; null != (s = i[o]); o++) try {
                n = t._data(s, "events"),
                n && n.remove && t(s).triggerHandler("remove")
            } catch(r) {}
            e(i)
        }
    } (t.cleanData),
    t.widget = function(e, i, n) {
        var s, o, r, a, l = {},
        c = e.split(".")[0];
        return e = e.split(".")[1],
        s = c + "-" + e,
        n || (n = i, i = t.Widget),
        t.expr[":"][s.toLowerCase()] = function(e) {
            return !! t.data(e, s)
        },
        t[c] = t[c] || {},
        o = t[c][e],
        r = t[c][e] = function(t, e) {
            return this._createWidget ? void(arguments.length && this._createWidget(t, e)) : new r(t, e)
        },
        t.extend(r, o, {
            version: n.version,
            _proto: t.extend({},
            n),
            _childConstructors: []
        }),
        a = new i,
        a.options = t.widget.extend({},
        a.options),
        t.each(n,
        function(e, n) {
            return t.isFunction(n) ? void(l[e] = function() {
                var t = function() {
                    return i.prototype[e].apply(this, arguments)
                },
                s = function(t) {
                    return i.prototype[e].apply(this, t)
                };
                return function() {
                    var e, i = this._super,
                    o = this._superApply;
                    return this._super = t,
                    this._superApply = s,
                    e = n.apply(this, arguments),
                    this._super = i,
                    this._superApply = o,
                    e
                }
            } ()) : void(l[e] = n)
        }),
        r.prototype = t.widget.extend(a, {
            widgetEventPrefix: o ? a.widgetEventPrefix || e: e
        },
        l, {
            constructor: r,
            namespace: c,
            widgetName: e,
            widgetFullName: s
        }),
        o ? (t.each(o._childConstructors,
        function(e, i) {
            var n = i.prototype;
            t.widget(n.namespace + "." + n.widgetName, r, i._proto)
        }), delete o._childConstructors) : i._childConstructors.push(r),
        t.widget.bridge(e, r),
        r
    },
    t.widget.extend = function(e) {
        for (var i, n, s = h.call(arguments, 1), o = 0, r = s.length; r > o; o++) for (i in s[o]) n = s[o][i],
        s[o].hasOwnProperty(i) && void 0 !== n && (e[i] = t.isPlainObject(n) ? t.isPlainObject(e[i]) ? t.widget.extend({},
        e[i], n) : t.widget.extend({},
        n) : n);
        return e
    },
    t.widget.bridge = function(e, i) {
        var n = i.prototype.widgetFullName || e;
        t.fn[e] = function(s) {
            var o = "string" == typeof s,
            r = h.call(arguments, 1),
            a = this;
            return o ? this.each(function() {
                var i, o = t.data(this, n);
                return "instance" === s ? (a = o, !1) : o ? t.isFunction(o[s]) && "_" !== s.charAt(0) ? (i = o[s].apply(o, r), i !== o && void 0 !== i ? (a = i && i.jquery ? a.pushStack(i.get()) : i, !1) : void 0) : t.error("no such method '" + s + "' for " + e + " widget instance") : t.error("cannot call methods on " + e + " prior to initialization; attempted to call method '" + s + "'")
            }) : (r.length && (s = t.widget.extend.apply(null, [s].concat(r))), this.each(function() {
                var e = t.data(this, n);
                e ? (e.option(s || {}), e._init && e._init()) : t.data(this, n, new i(s, this))
            })),
            a
        }
    },
    t.Widget = function() {},
    t.Widget._childConstructors = [],
    t.Widget.prototype = {
        widgetName: "widget",
        widgetEventPrefix: "",
        defaultElement: "<div>",
        options: {
            disabled: !1,
            create: null
        },
        _createWidget: function(e, i) {
            i = t(i || this.defaultElement || this)[0],
            this.element = t(i),
            this.uuid = c++,
            this.eventNamespace = "." + this.widgetName + this.uuid,
            this.bindings = t(),
            this.hoverable = t(),
            this.focusable = t(),
            i !== this && (t.data(i, this.widgetFullName, this), this._on(!0, this.element, {
                remove: function(t) {
                    t.target === i && this.destroy()
                }
            }), this.document = t(i.style ? i.ownerDocument: i.document || i), this.window = t(this.document[0].defaultView || this.document[0].parentWindow)),
            this.options = t.widget.extend({},
            this.options, this._getCreateOptions(), e),
            this._create(),
            this._trigger("create", null, this._getCreateEventData()),
            this._init()
        },
        _getCreateOptions: t.noop,
        _getCreateEventData: t.noop,
        _create: t.noop,
        _init: t.noop,
        destroy: function() {
            this._destroy(),
            this.element.unbind(this.eventNamespace).removeData(this.widgetFullName).removeData(t.camelCase(this.widgetFullName)),
            this.widget().unbind(this.eventNamespace).removeAttr("aria-disabled").removeClass(this.widgetFullName + "-disabled ui-state-disabled"),
            this.bindings.unbind(this.eventNamespace),
            this.hoverable.removeClass("ui-state-hover"),
            this.focusable.removeClass("ui-state-focus")
        },
        _destroy: t.noop,
        widget: function() {
            return this.element
        },
        option: function(e, i) {
            var n, s, o, r = e;
            if (0 === arguments.length) return t.widget.extend({},
            this.options);
            if ("string" == typeof e) if (r = {},
            n = e.split("."), e = n.shift(), n.length) {
                for (s = r[e] = t.widget.extend({},
                this.options[e]), o = 0; o < n.length - 1; o++) s[n[o]] = s[n[o]] || {},
                s = s[n[o]];
                if (e = n.pop(), 1 === arguments.length) return void 0 === s[e] ? null: s[e];
                s[e] = i
            } else {
                if (1 === arguments.length) return void 0 === this.options[e] ? null: this.options[e];
                r[e] = i
            }
            return this._setOptions(r),
            this
        },
        _setOptions: function(t) {
            var e;
            for (e in t) this._setOption(e, t[e]);
            return this
        },
        _setOption: function(t, e) {
            return this.options[t] = e,
            "disabled" === t && (this.widget().toggleClass(this.widgetFullName + "-disabled", !!e), e && (this.hoverable.removeClass("ui-state-hover"), this.focusable.removeClass("ui-state-focus"))),
            this
        },
        enable: function() {
            return this._setOptions({
                disabled: !1
            })
        },
        disable: function() {
            return this._setOptions({
                disabled: !0
            })
        },
        _on: function(e, i, n) {
            var s, o = this;
            "boolean" != typeof e && (n = i, i = e, e = !1),
            n ? (i = s = t(i), this.bindings = this.bindings.add(i)) : (n = i, i = this.element, s = this.widget()),
            t.each(n,
            function(n, r) {
                function a() {
                    return e || o.options.disabled !== !0 && !t(this).hasClass("ui-state-disabled") ? ("string" == typeof r ? o[r] : r).apply(o, arguments) : void 0
                }
                "string" != typeof r && (a.guid = r.guid = r.guid || a.guid || t.guid++);
                var l = n.match(/^([\w:-]*)\s*(.*)$/),
                c = l[1] + o.eventNamespace,
                h = l[2];
                h ? s.delegate(h, c, a) : i.bind(c, a)
            })
        },
        _off: function(e, i) {
            i = (i || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace,
            e.unbind(i).undelegate(i),
            this.bindings = t(this.bindings.not(e).get()),
            this.focusable = t(this.focusable.not(e).get()),
            this.hoverable = t(this.hoverable.not(e).get())
        },
        _delay: function(t, e) {
            function i() {
                return ("string" == typeof t ? n[t] : t).apply(n, arguments)
            }
            var n = this;
            return setTimeout(i, e || 0)
        },
        _hoverable: function(e) {
            this.hoverable = this.hoverable.add(e),
            this._on(e, {
                mouseenter: function(e) {
                    t(e.currentTarget).addClass("ui-state-hover")
                },
                mouseleave: function(e) {
                    t(e.currentTarget).removeClass("ui-state-hover")
                }
            })
        },
        _focusable: function(e) {
            this.focusable = this.focusable.add(e),
            this._on(e, {
                focusin: function(e) {
                    t(e.currentTarget).addClass("ui-state-focus")
                },
                focusout: function(e) {
                    t(e.currentTarget).removeClass("ui-state-focus")
                }
            })
        },
        _trigger: function(e, i, n) {
            var s, o, r = this.options[e];
            if (n = n || {},
            i = t.Event(i), i.type = (e === this.widgetEventPrefix ? e: this.widgetEventPrefix + e).toLowerCase(), i.target = this.element[0], o = i.originalEvent) for (s in o) s in i || (i[s] = o[s]);
            return this.element.trigger(i, n),
            !(t.isFunction(r) && r.apply(this.element[0], [i].concat(n)) === !1 || i.isDefaultPrevented())
        }
    },
    t.each({
        show: "fadeIn",
        hide: "fadeOut"
    },
    function(e, i) {
        t.Widget.prototype["_" + e] = function(n, s, o) {
            "string" == typeof s && (s = {
                effect: s
            });
            var r, a = s ? s === !0 || "number" == typeof s ? i: s.effect || i: e;
            s = s || {},
            "number" == typeof s && (s = {
                duration: s
            }),
            r = !t.isEmptyObject(s),
            s.complete = o,
            s.delay && n.delay(s.delay),
            r && t.effects && t.effects.effect[a] ? n[e](s) : a !== e && n[a] ? n[a](s.duration, s.easing, o) : n.queue(function(i) {
                t(this)[e](),
                o && o.call(n[0]),
                i()
            })
        }
    });
    var u = (t.widget, !1);
    t(document).mouseup(function() {
        u = !1
    });
    t.widget("ui.mouse", {
        version: "1.11.4",
        options: {
            cancel: "input,textarea,button,select,option",
            distance: 1,
            delay: 0
        },
        _mouseInit: function() {
            var e = this;
            this.element.bind("mousedown." + this.widgetName,
            function(t) {
                return e._mouseDown(t)
            }).bind("click." + this.widgetName,
            function(i) {
                return ! 0 === t.data(i.target, e.widgetName + ".preventClickEvent") ? (t.removeData(i.target, e.widgetName + ".preventClickEvent"), i.stopImmediatePropagation(), !1) : void 0
            }),
            this.started = !1
        },
        _mouseDestroy: function() {
            this.element.unbind("." + this.widgetName),
            this._mouseMoveDelegate && this.document.unbind("mousemove." + this.widgetName, this._mouseMoveDelegate).unbind("mouseup." + this.widgetName, this._mouseUpDelegate)
        },
        _mouseDown: function(e) {
            if (!u) {
                this._mouseMoved = !1,
                this._mouseStarted && this._mouseUp(e),
                this._mouseDownEvent = e;
                var i = this,
                n = 1 === e.which,
                s = "string" == typeof this.options.cancel && e.target.nodeName ? t(e.target).closest(this.options.cancel).length: !1;
                return n && !s && this._mouseCapture(e) ? (this.mouseDelayMet = !this.options.delay, this.mouseDelayMet || (this._mouseDelayTimer = setTimeout(function() {
                    i.mouseDelayMet = !0
                },
                this.options.delay)), this._mouseDistanceMet(e) && this._mouseDelayMet(e) && (this._mouseStarted = this._mouseStart(e) !== !1, !this._mouseStarted) ? (e.preventDefault(), !0) : (!0 === t.data(e.target, this.widgetName + ".preventClickEvent") && t.removeData(e.target, this.widgetName + ".preventClickEvent"), this._mouseMoveDelegate = function(t) {
                    return i._mouseMove(t)
                },
                this._mouseUpDelegate = function(t) {
                    return i._mouseUp(t)
                },
                this.document.bind("mousemove." + this.widgetName, this._mouseMoveDelegate).bind("mouseup." + this.widgetName, this._mouseUpDelegate), e.preventDefault(), u = !0, !0)) : !0
            }
        },
        _mouseMove: function(e) {
            if (this._mouseMoved) {
                if (t.ui.ie && (!document.documentMode || document.documentMode < 9) && !e.button) return this._mouseUp(e);
                if (!e.which) return this._mouseUp(e)
            }
            return (e.which || e.button) && (this._mouseMoved = !0),
            this._mouseStarted ? (this._mouseDrag(e), e.preventDefault()) : (this._mouseDistanceMet(e) && this._mouseDelayMet(e) && (this._mouseStarted = this._mouseStart(this._mouseDownEvent, e) !== !1, this._mouseStarted ? this._mouseDrag(e) : this._mouseUp(e)), !this._mouseStarted)
        },
        _mouseUp: function(e) {
            return this.document.unbind("mousemove." + this.widgetName, this._mouseMoveDelegate).unbind("mouseup." + this.widgetName, this._mouseUpDelegate),
            this._mouseStarted && (this._mouseStarted = !1, e.target === this._mouseDownEvent.target && t.data(e.target, this.widgetName + ".preventClickEvent", !0), this._mouseStop(e)),
            u = !1,
            !1
        },
        _mouseDistanceMet: function(t) {
            return Math.max(Math.abs(this._mouseDownEvent.pageX - t.pageX), Math.abs(this._mouseDownEvent.pageY - t.pageY)) >= this.options.distance
        },
        _mouseDelayMet: function() {
            return this.mouseDelayMet
        },
        _mouseStart: function() {},
        _mouseDrag: function() {},
        _mouseStop: function() {},
        _mouseCapture: function() {
            return ! 0
        }
    }); !
    function() {
        function e(t, e, i) {
            return [parseFloat(t[0]) * (p.test(t[0]) ? e / 100 : 1), parseFloat(t[1]) * (p.test(t[1]) ? i / 100 : 1)]
        }
        function i(e, i) {
            return parseInt(t.css(e, i), 10) || 0
        }
        function n(e) {
            var i = e[0];
            return 9 === i.nodeType ? {
                width: e.width(),
                height: e.height(),
                offset: {
                    top: 0,
                    left: 0
                }
            }: t.isWindow(i) ? {
                width: e.width(),
                height: e.height(),
                offset: {
                    top: e.scrollTop(),
                    left: e.scrollLeft()
                }
            }: i.preventDefault ? {
                width: 0,
                height: 0,
                offset: {
                    top: i.pageY,
                    left: i.pageX
                }
            }: {
                width: e.outerWidth(),
                height: e.outerHeight(),
                offset: e.offset()
            }
        }
        t.ui = t.ui || {};
        var s, o, r = Math.max,
        a = Math.abs,
        l = Math.round,
        c = /left|center|right/,
        h = /top|center|bottom/,
        u = /[\+\-]\d+(\.[\d]+)?%?/,
        d = /^\w+/,
        p = /%$/,
        f = t.fn.position;
        t.position = {
            scrollbarWidth: function() {
                if (void 0 !== s) return s;
                var e, i, n = t("<div style='display:block;position:absolute;width:50px;height:50px;overflow:hidden;'><div style='height:100px;width:auto;'></div></div>"),
                o = n.children()[0];
                return t("body").append(n),
                e = o.offsetWidth,
                n.css("overflow", "scroll"),
                i = o.offsetWidth,
                e === i && (i = n[0].clientWidth),
                n.remove(),
                s = e - i
            },
            getScrollInfo: function(e) {
                var i = e.isWindow || e.isDocument ? "": e.element.css("overflow-x"),
                n = e.isWindow || e.isDocument ? "": e.element.css("overflow-y"),
                s = "scroll" === i || "auto" === i && e.width < e.element[0].scrollWidth,
                o = "scroll" === n || "auto" === n && e.height < e.element[0].scrollHeight;
                return {
                    width: o ? t.position.scrollbarWidth() : 0,
                    height: s ? t.position.scrollbarWidth() : 0
                }
            },
            getWithinInfo: function(e) {
                var i = t(e || window),
                n = t.isWindow(i[0]),
                s = !!i[0] && 9 === i[0].nodeType;
                return {
                    element: i,
                    isWindow: n,
                    isDocument: s,
                    offset: i.offset() || {
                        left: 0,
                        top: 0
                    },
                    scrollLeft: i.scrollLeft(),
                    scrollTop: i.scrollTop(),
                    width: n || s ? i.width() : i.outerWidth(),
                    height: n || s ? i.height() : i.outerHeight()
                }
            }
        },
        t.fn.position = function(s) {
            if (!s || !s.of) return f.apply(this, arguments);
            s = t.extend({},
            s);
            var p, m, g, v, y, b, _ = t(s.of),
            w = t.position.getWithinInfo(s.within),
            x = t.position.getScrollInfo(w),
            C = (s.collision || "flip").split(" "),
            k = {};
            return b = n(_),
            _[0].preventDefault && (s.at = "left top"),
            m = b.width,
            g = b.height,
            v = b.offset,
            y = t.extend({},
            v),
            t.each(["my", "at"],
            function() {
                var t, e, i = (s[this] || "").split(" ");
                1 === i.length && (i = c.test(i[0]) ? i.concat(["center"]) : h.test(i[0]) ? ["center"].concat(i) : ["center", "center"]),
                i[0] = c.test(i[0]) ? i[0] : "center",
                i[1] = h.test(i[1]) ? i[1] : "center",
                t = u.exec(i[0]),
                e = u.exec(i[1]),
                k[this] = [t ? t[0] : 0, e ? e[0] : 0],
                s[this] = [d.exec(i[0])[0], d.exec(i[1])[0]]
            }),
            1 === C.length && (C[1] = C[0]),
            "right" === s.at[0] ? y.left += m: "center" === s.at[0] && (y.left += m / 2),
            "bottom" === s.at[1] ? y.top += g: "center" === s.at[1] && (y.top += g / 2),
            p = e(k.at, m, g),
            y.left += p[0],
            y.top += p[1],
            this.each(function() {
                var n, c, h = t(this),
                u = h.outerWidth(),
                d = h.outerHeight(),
                f = i(this, "marginLeft"),
                b = i(this, "marginTop"),
                T = u + f + i(this, "marginRight") + x.width,
                D = d + b + i(this, "marginBottom") + x.height,
                E = t.extend({},
                y),
                M = e(k.my, h.outerWidth(), h.outerHeight());
                "right" === s.my[0] ? E.left -= u: "center" === s.my[0] && (E.left -= u / 2),
                "bottom" === s.my[1] ? E.top -= d: "center" === s.my[1] && (E.top -= d / 2),
                E.left += M[0],
                E.top += M[1],
                o || (E.left = l(E.left), E.top = l(E.top)),
                n = {
                    marginLeft: f,
                    marginTop: b
                },
                t.each(["left", "top"],
                function(e, i) {
                    t.ui.position[C[e]] && t.ui.position[C[e]][i](E, {
                        targetWidth: m,
                        targetHeight: g,
                        elemWidth: u,
                        elemHeight: d,
                        collisionPosition: n,
                        collisionWidth: T,
                        collisionHeight: D,
                        offset: [p[0] + M[0], p[1] + M[1]],
                        my: s.my,
                        at: s.at,
                        within: w,
                        elem: h
                    })
                }),
                s.using && (c = function(t) {
                    var e = v.left - E.left,
                    i = e + m - u,
                    n = v.top - E.top,
                    o = n + g - d,
                    l = {
                        target: {
                            element: _,
                            left: v.left,
                            top: v.top,
                            width: m,
                            height: g
                        },
                        element: {
                            element: h,
                            left: E.left,
                            top: E.top,
                            width: u,
                            height: d
                        },
                        horizontal: 0 > i ? "left": e > 0 ? "right": "center",
                        vertical: 0 > o ? "top": n > 0 ? "bottom": "middle"
                    };
                    u > m && a(e + i) < m && (l.horizontal = "center"),
                    d > g && a(n + o) < g && (l.vertical = "middle"),
                    l.important = r(a(e), a(i)) > r(a(n), a(o)) ? "horizontal": "vertical",
                    s.using.call(this, t, l)
                }),
                h.offset(t.extend(E, {
                    using: c
                }))
            })
        },
        t.ui.position = {
            fit: {
                left: function(t, e) {
                    var i, n = e.within,
                    s = n.isWindow ? n.scrollLeft: n.offset.left,
                    o = n.width,
                    a = t.left - e.collisionPosition.marginLeft,
                    l = s - a,
                    c = a + e.collisionWidth - o - s;
                    e.collisionWidth > o ? l > 0 && 0 >= c ? (i = t.left + l + e.collisionWidth - o - s, t.left += l - i) : t.left = c > 0 && 0 >= l ? s: l > c ? s + o - e.collisionWidth: s: l > 0 ? t.left += l: c > 0 ? t.left -= c: t.left = r(t.left - a, t.left)
                },
                top: function(t, e) {
                    var i, n = e.within,
                    s = n.isWindow ? n.scrollTop: n.offset.top,
                    o = e.within.height,
                    a = t.top - e.collisionPosition.marginTop,
                    l = s - a,
                    c = a + e.collisionHeight - o - s;
                    e.collisionHeight > o ? l > 0 && 0 >= c ? (i = t.top + l + e.collisionHeight - o - s, t.top += l - i) : t.top = c > 0 && 0 >= l ? s: l > c ? s + o - e.collisionHeight: s: l > 0 ? t.top += l: c > 0 ? t.top -= c: t.top = r(t.top - a, t.top)
                }
            },
            flip: {
                left: function(t, e) {
                    var i, n, s = e.within,
                    o = s.offset.left + s.scrollLeft,
                    r = s.width,
                    l = s.isWindow ? s.scrollLeft: s.offset.left,
                    c = t.left - e.collisionPosition.marginLeft,
                    h = c - l,
                    u = c + e.collisionWidth - r - l,
                    d = "left" === e.my[0] ? -e.elemWidth: "right" === e.my[0] ? e.elemWidth: 0,
                    p = "left" === e.at[0] ? e.targetWidth: "right" === e.at[0] ? -e.targetWidth: 0,
                    f = -2 * e.offset[0];
                    0 > h ? (i = t.left + d + p + f + e.collisionWidth - r - o, (0 > i || i < a(h)) && (t.left += d + p + f)) : u > 0 && (n = t.left - e.collisionPosition.marginLeft + d + p + f - l, (n > 0 || a(n) < u) && (t.left += d + p + f))
                },
                top: function(t, e) {
                    var i, n, s = e.within,
                    o = s.offset.top + s.scrollTop,
                    r = s.height,
                    l = s.isWindow ? s.scrollTop: s.offset.top,
                    c = t.top - e.collisionPosition.marginTop,
                    h = c - l,
                    u = c + e.collisionHeight - r - l,
                    d = "top" === e.my[1],
                    p = d ? -e.elemHeight: "bottom" === e.my[1] ? e.elemHeight: 0,
                    f = "top" === e.at[1] ? e.targetHeight: "bottom" === e.at[1] ? -e.targetHeight: 0,
                    m = -2 * e.offset[1];
                    0 > h ? (n = t.top + p + f + m + e.collisionHeight - r - o, (0 > n || n < a(h)) && (t.top += p + f + m)) : u > 0 && (i = t.top - e.collisionPosition.marginTop + p + f + m - l, (i > 0 || a(i) < u) && (t.top += p + f + m))
                }
            },
            flipfit: {
                left: function() {
                    t.ui.position.flip.left.apply(this, arguments),
                    t.ui.position.fit.left.apply(this, arguments)
                },
                top: function() {
                    t.ui.position.flip.top.apply(this, arguments),
                    t.ui.position.fit.top.apply(this, arguments)
                }
            }
        },
        function() {
            var e, i, n, s, r, a = document.getElementsByTagName("body")[0],
            l = document.createElement("div");
            e = document.createElement(a ? "div": "body"),
            n = {
                visibility: "hidden",
                width: 0,
                height: 0,
                border: 0,
                margin: 0,
                background: "none"
            },
            a && t.extend(n, {
                position: "absolute",
                left: "-1000px",
                top: "-1000px"
            });
            for (r in n) e.style[r] = n[r];
            e.appendChild(l),
            i = a || document.documentElement,
            i.insertBefore(e, i.firstChild),
            l.style.cssText = "position: absolute; left: 10.7432222px;",
            s = t(l).offset().left,
            o = s > 10 && 11 > s,
            e.innerHTML = "",
            i.removeChild(e)
        } ()
    } ();
    t.ui.position,
    t.widget("ui.accordion", {
        version: "1.11.4",
        options: {
            active: 0,
            animate: {},
            collapsible: !1,
            event: "click",
            header: "> li > :first-child,> :not(li):even",
            heightStyle: "auto",
            icons: {
                activeHeader: "ui-icon-triangle-1-s",
                header: "ui-icon-triangle-1-e"
            },
            activate: null,
            beforeActivate: null
        },
        hideProps: {
            borderTopWidth: "hide",
            borderBottomWidth: "hide",
            paddingTop: "hide",
            paddingBottom: "hide",
            height: "hide"
        },
        showProps: {
            borderTopWidth: "show",
            borderBottomWidth: "show",
            paddingTop: "show",
            paddingBottom: "show",
            height: "show"
        },
        _create: function() {
            var e = this.options;
            this.prevShow = this.prevHide = t(),
            this.element.addClass("ui-accordion ui-widget ui-helper-reset").attr("role", "tablist"),
            e.collapsible || e.active !== !1 && null != e.active || (e.active = 0),
            this._processPanels(),
            e.active < 0 && (e.active += this.headers.length),
            this._refresh()
        },
        _getCreateEventData: function() {
            return {
                header: this.active,
                panel: this.active.length ? this.active.next() : t()
            }
        },
        _createIcons: function() {
            var e = this.options.icons;
            e && (t("<span>").addClass("ui-accordion-header-icon ui-icon " + e.header).prependTo(this.headers), this.active.children(".ui-accordion-header-icon").removeClass(e.header).addClass(e.activeHeader), this.headers.addClass("ui-accordion-icons"))
        },
        _destroyIcons: function() {
            this.headers.removeClass("ui-accordion-icons").children(".ui-accordion-header-icon").remove()
        },
        _destroy: function() {
            var t;
            this.element.removeClass("ui-accordion ui-widget ui-helper-reset").removeAttr("role"),
            this.headers.removeClass("ui-accordion-header ui-accordion-header-active ui-state-default ui-corner-all ui-state-active ui-state-disabled ui-corner-top").removeAttr("role").removeAttr("aria-expanded").removeAttr("aria-selected").removeAttr("aria-controls").removeAttr("tabIndex").removeUniqueId(),
            this._destroyIcons(),
            t = this.headers.next().removeClass("ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content ui-accordion-content-active ui-state-disabled").css("display", "").removeAttr("role").removeAttr("aria-hidden").removeAttr("aria-labelledby").removeUniqueId(),
            "content" !== this.options.heightStyle && t.css("height", "")
        },
        _setOption: function(t, e) {
            return "active" === t ? void this._activate(e) : ("event" === t && (this.options.event && this._off(this.headers, this.options.event), this._setupEvents(e)), this._super(t, e), "collapsible" !== t || e || this.options.active !== !1 || this._activate(0), "icons" === t && (this._destroyIcons(), e && this._createIcons()), void("disabled" === t && (this.element.toggleClass("ui-state-disabled", !!e).attr("aria-disabled", e), this.headers.add(this.headers.next()).toggleClass("ui-state-disabled", !!e))))
        },
        _keydown: function(e) {
            if (!e.altKey && !e.ctrlKey) {
                var i = t.ui.keyCode,
                n = this.headers.length,
                s = this.headers.index(e.target),
                o = !1;
                switch (e.keyCode) {
                case i.RIGHT:
                case i.DOWN:
                    o = this.headers[(s + 1) % n];
                    break;
                case i.LEFT:
                case i.UP:
                    o = this.headers[(s - 1 + n) % n];
                    break;
                case i.SPACE:
                case i.ENTER:
                    this._eventHandler(e);
                    break;
                case i.HOME:
                    o = this.headers[0];
                    break;
                case i.END:
                    o = this.headers[n - 1]
                }
                o && (t(e.target).attr("tabIndex", -1), t(o).attr("tabIndex", 0), o.focus(), e.preventDefault())
            }
        },
        _panelKeyDown: function(e) {
            e.keyCode === t.ui.keyCode.UP && e.ctrlKey && t(e.currentTarget).prev().focus()
        },
        refresh: function() {
            var e = this.options;
            this._processPanels(),
            e.active === !1 && e.collapsible === !0 || !this.headers.length ? (e.active = !1, this.active = t()) : e.active === !1 ? this._activate(0) : this.active.length && !t.contains(this.element[0], this.active[0]) ? this.headers.length === this.headers.find(".ui-state-disabled").length ? (e.active = !1, this.active = t()) : this._activate(Math.max(0, e.active - 1)) : e.active = this.headers.index(this.active),
            this._destroyIcons(),
            this._refresh()
        },
        _processPanels: function() {
            var t = this.headers,
            e = this.panels;
            this.headers = this.element.find(this.options.header).addClass("ui-accordion-header ui-state-default ui-corner-all"),
            this.panels = this.headers.next().addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom").filter(":not(.ui-accordion-content-active)").hide(),
            e && (this._off(t.not(this.headers)), this._off(e.not(this.panels)))
        },
        _refresh: function() {
            var e, i = this.options,
            n = i.heightStyle,
            s = this.element.parent();
            this.active = this._findActive(i.active).addClass("ui-accordion-header-active ui-state-active ui-corner-top").removeClass("ui-corner-all"),
            this.active.next().addClass("ui-accordion-content-active").show(),
            this.headers.attr("role", "tab").each(function() {
                var e = t(this),
                i = e.uniqueId().attr("id"),
                n = e.next(),
                s = n.uniqueId().attr("id");
                e.attr("aria-controls", s),
                n.attr("aria-labelledby", i)
            }).next().attr("role", "tabpanel"),
            this.headers.not(this.active).attr({
                "aria-selected": "false",
                "aria-expanded": "false",
                tabIndex: -1
            }).next().attr({
                "aria-hidden": "true"
            }).hide(),
            this.active.length ? this.active.attr({
                "aria-selected": "true",
                "aria-expanded": "true",
                tabIndex: 0
            }).next().attr({
                "aria-hidden": "false"
            }) : this.headers.eq(0).attr("tabIndex", 0),
            this._createIcons(),
            this._setupEvents(i.event),
            "fill" === n ? (e = s.height(), this.element.siblings(":visible").each(function() {
                var i = t(this),
                n = i.css("position");
                "absolute" !== n && "fixed" !== n && (e -= i.outerHeight(!0))
            }), this.headers.each(function() {
                e -= t(this).outerHeight(!0)
            }), this.headers.next().each(function() {
                t(this).height(Math.max(0, e - t(this).innerHeight() + t(this).height()))
            }).css("overflow", "auto")) : "auto" === n && (e = 0, this.headers.next().each(function() {
                e = Math.max(e, t(this).css("height", "").height())
            }).height(e))
        },
        _activate: function(e) {
            var i = this._findActive(e)[0];
            i !== this.active[0] && (i = i || this.active[0], this._eventHandler({
                target: i,
                currentTarget: i,
                preventDefault: t.noop
            }))
        },
        _findActive: function(e) {
            return "number" == typeof e ? this.headers.eq(e) : t()
        },
        _setupEvents: function(e) {
            var i = {
                keydown: "_keydown"
            };
            e && t.each(e.split(" "),
            function(t, e) {
                i[e] = "_eventHandler"
            }),
            this._off(this.headers.add(this.headers.next())),
            this._on(this.headers, i),
            this._on(this.headers.next(), {
                keydown: "_panelKeyDown"
            }),
            this._hoverable(this.headers),
            this._focusable(this.headers)
        },
        _eventHandler: function(e) {
            var i = this.options,
            n = this.active,
            s = t(e.currentTarget),
            o = s[0] === n[0],
            r = o && i.collapsible,
            a = r ? t() : s.next(),
            l = n.next(),
            c = {
                oldHeader: n,
                oldPanel: l,
                newHeader: r ? t() : s,
                newPanel: a
            };
            e.preventDefault(),
            o && !i.collapsible || this._trigger("beforeActivate", e, c) === !1 || (i.active = r ? !1 : this.headers.index(s), this.active = o ? t() : s, this._toggle(c), n.removeClass("ui-accordion-header-active ui-state-active"), i.icons && n.children(".ui-accordion-header-icon").removeClass(i.icons.activeHeader).addClass(i.icons.header), o || (s.removeClass("ui-corner-all").addClass("ui-accordion-header-active ui-state-active ui-corner-top"), i.icons && s.children(".ui-accordion-header-icon").removeClass(i.icons.header).addClass(i.icons.activeHeader), s.next().addClass("ui-accordion-content-active")))
        },
        _toggle: function(e) {
            var i = e.newPanel,
            n = this.prevShow.length ? this.prevShow: e.oldPanel;
            this.prevShow.add(this.prevHide).stop(!0, !0),
            this.prevShow = i,
            this.prevHide = n,
            this.options.animate ? this._animate(i, n, e) : (n.hide(), i.show(), this._toggleComplete(e)),
            n.attr({
                "aria-hidden": "true"
            }),
            n.prev().attr({
                "aria-selected": "false",
                "aria-expanded": "false"
            }),
            i.length && n.length ? n.prev().attr({
                tabIndex: -1,
                "aria-expanded": "false"
            }) : i.length && this.headers.filter(function() {
                return 0 === parseInt(t(this).attr("tabIndex"), 10)
            }).attr("tabIndex", -1),
            i.attr("aria-hidden", "false").prev().attr({
                "aria-selected": "true",
                "aria-expanded": "true",
                tabIndex: 0
            })
        },
        _animate: function(t, e, i) {
            var n, s, o, r = this,
            a = 0,
            l = t.css("box-sizing"),
            c = t.length && (!e.length || t.index() < e.index()),
            h = this.options.animate || {},
            u = c && h.down || h,
            d = function() {
                r._toggleComplete(i)
            };
            return "number" == typeof u && (o = u),
            "string" == typeof u && (s = u),
            s = s || u.easing || h.easing,
            o = o || u.duration || h.duration,
            e.length ? t.length ? (n = t.show().outerHeight(), e.animate(this.hideProps, {
                duration: o,
                easing: s,
                step: function(t, e) {
                    e.now = Math.round(t)
                }
            }), void t.hide().animate(this.showProps, {
                duration: o,
                easing: s,
                complete: d,
                step: function(t, i) {
                    i.now = Math.round(t),
                    "height" !== i.prop ? "content-box" === l && (a += i.now) : "content" !== r.options.heightStyle && (i.now = Math.round(n - e.outerHeight() - a), a = 0)
                }
            })) : e.animate(this.hideProps, o, s, d) : t.animate(this.showProps, o, s, d)
        },
        _toggleComplete: function(t) {
            var e = t.oldPanel;
            e.removeClass("ui-accordion-content-active").prev().removeClass("ui-corner-top").addClass("ui-corner-all"),
            e.length && (e.parent()[0].className = e.parent()[0].className),
            this._trigger("activate", null, t)
        }
    }),
    t.widget("ui.menu", {
        version: "1.11.4",
        defaultElement: "<ul>",
        delay: 300,
        options: {
            icons: {
                submenu: "ui-icon-carat-1-e"
            },
            items: "> *",
            menus: "ul",
            position: {
                my: "left-1 top",
                at: "right top"
            },
            role: "menu",
            blur: null,
            focus: null,
            select: null
        },
        _create: function() {
            this.activeMenu = this.element,
            this.mouseHandled = !1,
            this.element.uniqueId().addClass("ui-menu ui-widget ui-widget-content").toggleClass("ui-menu-icons", !!this.element.find(".ui-icon").length).attr({
                role: this.options.role,
                tabIndex: 0
            }),
            this.options.disabled && this.element.addClass("ui-state-disabled").attr("aria-disabled", "true"),
            this._on({
                "mousedown .ui-menu-item": function(t) {
                    t.preventDefault()
                },
                "click .ui-menu-item": function(e) {
                    var i = t(e.target); ! this.mouseHandled && i.not(".ui-state-disabled").length && (this.select(e), e.isPropagationStopped() || (this.mouseHandled = !0), i.has(".ui-menu").length ? this.expand(e) : !this.element.is(":focus") && t(this.document[0].activeElement).closest(".ui-menu").length && (this.element.trigger("focus", [!0]), this.active && 1 === this.active.parents(".ui-menu").length && clearTimeout(this.timer)))
                },
                "mouseenter .ui-menu-item": function(e) {
                    if (!this.previousFilter) {
                        var i = t(e.currentTarget);
                        i.siblings(".ui-state-active").removeClass("ui-state-active"),
                        this.focus(e, i)
                    }
                },
                mouseleave: "collapseAll",
                "mouseleave .ui-menu": "collapseAll",
                focus: function(t, e) {
                    var i = this.active || this.element.find(this.options.items).eq(0);
                    e || this.focus(t, i)
                },
                blur: function(e) {
                    this._delay(function() {
                        t.contains(this.element[0], this.document[0].activeElement) || this.collapseAll(e)
                    })
                },
                keydown: "_keydown"
            }),
            this.refresh(),
            this._on(this.document, {
                click: function(t) {
                    this._closeOnDocumentClick(t) && this.collapseAll(t),
                    this.mouseHandled = !1
                }
            })
        },
        _destroy: function() {
            this.element.removeAttr("aria-activedescendant").find(".ui-menu").addBack().removeClass("ui-menu ui-widget ui-widget-content ui-menu-icons ui-front").removeAttr("role").removeAttr("tabIndex").removeAttr("aria-labelledby").removeAttr("aria-expanded").removeAttr("aria-hidden").removeAttr("aria-disabled").removeUniqueId().show(),
            this.element.find(".ui-menu-item").removeClass("ui-menu-item").removeAttr("role").removeAttr("aria-disabled").removeUniqueId().removeClass("ui-state-hover").removeAttr("tabIndex").removeAttr("role").removeAttr("aria-haspopup").children().each(function() {
                var e = t(this);
                e.data("ui-menu-submenu-carat") && e.remove()
            }),
            this.element.find(".ui-menu-divider").removeClass("ui-menu-divider ui-widget-content")
        },
        _keydown: function(e) {
            var i, n, s, o, r = !0;
            switch (e.keyCode) {
            case t.ui.keyCode.PAGE_UP:
                this.previousPage(e);
                break;
            case t.ui.keyCode.PAGE_DOWN:
                this.nextPage(e);
                break;
            case t.ui.keyCode.HOME:
                this._move("first", "first", e);
                break;
            case t.ui.keyCode.END:
                this._move("last", "last", e);
                break;
            case t.ui.keyCode.UP:
                this.previous(e);
                break;
            case t.ui.keyCode.DOWN:
                this.next(e);
                break;
            case t.ui.keyCode.LEFT:
                this.collapse(e);
                break;
            case t.ui.keyCode.RIGHT:
                this.active && !this.active.is(".ui-state-disabled") && this.expand(e);
                break;
            case t.ui.keyCode.ENTER:
            case t.ui.keyCode.SPACE:
                this._activate(e);
                break;
            case t.ui.keyCode.ESCAPE:
                this.collapse(e);
                break;
            default:
                r = !1,
                n = this.previousFilter || "",
                s = String.fromCharCode(e.keyCode),
                o = !1,
                clearTimeout(this.filterTimer),
                s === n ? o = !0 : s = n + s,
                i = this._filterMenuItems(s),
                i = o && -1 !== i.index(this.active.next()) ? this.active.nextAll(".ui-menu-item") : i,
                i.length || (s = String.fromCharCode(e.keyCode), i = this._filterMenuItems(s)),
                i.length ? (this.focus(e, i), this.previousFilter = s, this.filterTimer = this._delay(function() {
                    delete this.previousFilter
                },
                1e3)) : delete this.previousFilter
            }
            r && e.preventDefault()
        },
        _activate: function(t) {
            this.active.is(".ui-state-disabled") || (this.active.is("[aria-haspopup='true']") ? this.expand(t) : this.select(t))
        },
        refresh: function() {
            var e, i, n = this,
            s = this.options.icons.submenu,
            o = this.element.find(this.options.menus);
            this.element.toggleClass("ui-menu-icons", !!this.element.find(".ui-icon").length),
            o.filter(":not(.ui-menu)").addClass("ui-menu ui-widget ui-widget-content ui-front").hide().attr({
                role: this.options.role,
                "aria-hidden": "true",
                "aria-expanded": "false"
            }).each(function() {
                var e = t(this),
                i = e.parent(),
                n = t("<span>").addClass("ui-menu-icon ui-icon " + s).data("ui-menu-submenu-carat", !0);
                i.attr("aria-haspopup", "true").prepend(n),
                e.attr("aria-labelledby", i.attr("id"))
            }),
            e = o.add(this.element),
            i = e.find(this.options.items),
            i.not(".ui-menu-item").each(function() {
                var e = t(this);
                n._isDivider(e) && e.addClass("ui-widget-content ui-menu-divider")
            }),
            i.not(".ui-menu-item, .ui-menu-divider").addClass("ui-menu-item").uniqueId().attr({
                tabIndex: -1,
                role: this._itemRole()
            }),
            i.filter(".ui-state-disabled").attr("aria-disabled", "true"),
            this.active && !t.contains(this.element[0], this.active[0]) && this.blur()
        },
        _itemRole: function() {
            return {
                menu: "menuitem",
                listbox: "option"
            } [this.options.role]
        },
        _setOption: function(t, e) {
            "icons" === t && this.element.find(".ui-menu-icon").removeClass(this.options.icons.submenu).addClass(e.submenu),
            "disabled" === t && this.element.toggleClass("ui-state-disabled", !!e).attr("aria-disabled", e),
            this._super(t, e)
        },
        focus: function(t, e) {
            var i, n;
            this.blur(t, t && "focus" === t.type),
            this._scrollIntoView(e),
            this.active = e.first(),
            n = this.active.addClass("ui-state-focus").removeClass("ui-state-active"),
            this.options.role && this.element.attr("aria-activedescendant", n.attr("id")),
            this.active.parent().closest(".ui-menu-item").addClass("ui-state-active"),
            t && "keydown" === t.type ? this._close() : this.timer = this._delay(function() {
                this._close()
            },
            this.delay),
            i = e.children(".ui-menu"),
            i.length && t && /^mouse/.test(t.type) && this._startOpening(i),
            this.activeMenu = e.parent(),
            this._trigger("focus", t, {
                item: e
            })
        },
        _scrollIntoView: function(e) {
            var i, n, s, o, r, a;
            this._hasScroll() && (i = parseFloat(t.css(this.activeMenu[0], "borderTopWidth")) || 0, n = parseFloat(t.css(this.activeMenu[0], "paddingTop")) || 0, s = e.offset().top - this.activeMenu.offset().top - i - n, o = this.activeMenu.scrollTop(), r = this.activeMenu.height(), a = e.outerHeight(), 0 > s ? this.activeMenu.scrollTop(o + s) : s + a > r && this.activeMenu.scrollTop(o + s - r + a))
        },
        blur: function(t, e) {
            e || clearTimeout(this.timer),
            this.active && (this.active.removeClass("ui-state-focus"), this.active = null, this._trigger("blur", t, {
                item: this.active
            }))
        },
        _startOpening: function(t) {
            clearTimeout(this.timer),
            "true" === t.attr("aria-hidden") && (this.timer = this._delay(function() {
                this._close(),
                this._open(t)
            },
            this.delay))
        },
        _open: function(e) {
            var i = t.extend({
                of: this.active
            },
            this.options.position);
            clearTimeout(this.timer),
            this.element.find(".ui-menu").not(e.parents(".ui-menu")).hide().attr("aria-hidden", "true"),
            e.show().removeAttr("aria-hidden").attr("aria-expanded", "true").position(i)
        },
        collapseAll: function(e, i) {
            clearTimeout(this.timer),
            this.timer = this._delay(function() {
                var n = i ? this.element: t(e && e.target).closest(this.element.find(".ui-menu"));
                n.length || (n = this.element),
                this._close(n),
                this.blur(e),
                this.activeMenu = n
            },
            this.delay)
        },
        _close: function(t) {
            t || (t = this.active ? this.active.parent() : this.element),
            t.find(".ui-menu").hide().attr("aria-hidden", "true").attr("aria-expanded", "false").end().find(".ui-state-active").not(".ui-state-focus").removeClass("ui-state-active")
        },
        _closeOnDocumentClick: function(e) {
            return ! t(e.target).closest(".ui-menu").length
        },
        _isDivider: function(t) {
            return ! /[^\-\u2014\u2013\s]/.test(t.text())
        },
        collapse: function(t) {
            var e = this.active && this.active.parent().closest(".ui-menu-item", this.element);
            e && e.length && (this._close(), this.focus(t, e))
        },
        expand: function(t) {
            var e = this.active && this.active.children(".ui-menu ").find(this.options.items).first();
            e && e.length && (this._open(e.parent()), this._delay(function() {
                this.focus(t, e)
            }))
        },
        next: function(t) {
            this._move("next", "first", t)
        },
        previous: function(t) {
            this._move("prev", "last", t)
        },
        isFirstItem: function() {
            return this.active && !this.active.prevAll(".ui-menu-item").length
        },
        isLastItem: function() {
            return this.active && !this.active.nextAll(".ui-menu-item").length
        },
        _move: function(t, e, i) {
            var n;
            this.active && (n = "first" === t || "last" === t ? this.active["first" === t ? "prevAll": "nextAll"](".ui-menu-item").eq( - 1) : this.active[t + "All"](".ui-menu-item").eq(0)),
            n && n.length && this.active || (n = this.activeMenu.find(this.options.items)[e]()),
            this.focus(i, n)
        },
        nextPage: function(e) {
            var i, n, s;
            return this.active ? void(this.isLastItem() || (this._hasScroll() ? (n = this.active.offset().top, s = this.element.height(), this.active.nextAll(".ui-menu-item").each(function() {
                return i = t(this),
                i.offset().top - n - s < 0
            }), this.focus(e, i)) : this.focus(e, this.activeMenu.find(this.options.items)[this.active ? "last": "first"]()))) : void this.next(e)
        },
        previousPage: function(e) {
            var i, n, s;
            return this.active ? void(this.isFirstItem() || (this._hasScroll() ? (n = this.active.offset().top, s = this.element.height(), this.active.prevAll(".ui-menu-item").each(function() {
                return i = t(this),
                i.offset().top - n + s > 0
            }), this.focus(e, i)) : this.focus(e, this.activeMenu.find(this.options.items).first()))) : void this.next(e)
        },
        _hasScroll: function() {
            return this.element.outerHeight() < this.element.prop("scrollHeight")
        },
        select: function(e) {
            this.active = this.active || t(e.target).closest(".ui-menu-item");
            var i = {
                item: this.active
            };
            this.active.has(".ui-menu").length || this.collapseAll(e, !0),
            this._trigger("select", e, i)
        },
        _filterMenuItems: function(e) {
            var i = e.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&"),
            n = new RegExp("^" + i, "i");
            return this.activeMenu.find(this.options.items).filter(".ui-menu-item").filter(function() {
                return n.test(t.trim(t(this).text()))
            })
        }
    });
    t.widget("ui.autocomplete", {
        version: "1.11.4",
        defaultElement: "<input>",
        options: {
            appendTo: null,
            autoFocus: !1,
            delay: 300,
            minLength: 1,
            position: {
                my: "left top",
                at: "left bottom",
                collision: "none"
            },
            source: null,
            change: null,
            close: null,
            focus: null,
            open: null,
            response: null,
            search: null,
            select: null
        },
        requestIndex: 0,
        pending: 0,
        _create: function() {
            var e, i, n, s = this.element[0].nodeName.toLowerCase(),
            o = "textarea" === s,
            r = "input" === s;
            this.isMultiLine = o ? !0 : r ? !1 : this.element.prop("isContentEditable"),
            this.valueMethod = this.element[o || r ? "val": "text"],
            this.isNewMenu = !0,
            this.element.addClass("ui-autocomplete-input").attr("autocomplete", "off"),
            this._on(this.element, {
                keydown: function(s) {
                    if (this.element.prop("readOnly")) return e = !0,
                    n = !0,
                    void(i = !0);
                    e = !1,
                    n = !1,
                    i = !1;
                    var o = t.ui.keyCode;
                    switch (s.keyCode) {
                    case o.PAGE_UP:
                        e = !0,
                        this._move("previousPage", s);
                        break;
                    case o.PAGE_DOWN:
                        e = !0,
                        this._move("nextPage", s);
                        break;
                    case o.UP:
                        e = !0,
                        this._keyEvent("previous", s);
                        break;
                    case o.DOWN:
                        e = !0,
                        this._keyEvent("next", s);
                        break;
                    case o.ENTER:
                        this.menu.active && (e = !0, s.preventDefault(), this.menu.select(s));
                        break;
                    case o.TAB:
                        this.menu.active && this.menu.select(s);
                        break;
                    case o.ESCAPE:
                        this.menu.element.is(":visible") && (this.isMultiLine || this._value(this.term), this.close(s), s.preventDefault());
                        break;
                    default:
                        i = !0,
                        this._searchTimeout(s)
                    }
                },
                keypress: function(n) {
                    if (e) return e = !1,
                    void((!this.isMultiLine || this.menu.element.is(":visible")) && n.preventDefault());
                    if (!i) {
                        var s = t.ui.keyCode;
                        switch (n.keyCode) {
                        case s.PAGE_UP:
                            this._move("previousPage", n);
                            break;
                        case s.PAGE_DOWN:
                            this._move("nextPage", n);
                            break;
                        case s.UP:
                            this._keyEvent("previous", n);
                            break;
                        case s.DOWN:
                            this._keyEvent("next", n)
                        }
                    }
                },
                input: function(t) {
                    return n ? (n = !1, void t.preventDefault()) : void this._searchTimeout(t)
                },
                focus: function() {
                    this.selectedItem = null,
                    this.previous = this._value()
                },
                blur: function(t) {
                    return this.cancelBlur ? void delete this.cancelBlur: (clearTimeout(this.searching), this.close(t), void this._change(t))
                }
            }),
            this._initSource(),
            this.menu = t("<ul>").addClass("ui-autocomplete ui-front").appendTo(this._appendTo()).menu({
                role: null
            }).hide().menu("instance"),
            this._on(this.menu.element, {
                mousedown: function(e) {
                    e.preventDefault(),
                    this.cancelBlur = !0,
                    this._delay(function() {
                        delete this.cancelBlur
                    });
                    var i = this.menu.element[0];
                    t(e.target).closest(".ui-menu-item").length || this._delay(function() {
                        var e = this;
                        this.document.one("mousedown",
                        function(n) {
                            n.target === e.element[0] || n.target === i || t.contains(i, n.target) || e.close()
                        })
                    })
                },
                menufocus: function(e, i) {
                    var n, s;
                    return this.isNewMenu && (this.isNewMenu = !1, e.originalEvent && /^mouse/.test(e.originalEvent.type)) ? (this.menu.blur(), void this.document.one("mousemove",
                    function() {
                        t(e.target).trigger(e.originalEvent)
                    })) : (s = i.item.data("ui-autocomplete-item"), !1 !== this._trigger("focus", e, {
                        item: s
                    }) && e.originalEvent && /^key/.test(e.originalEvent.type) && this._value(s.value), n = i.item.attr("aria-label") || s.value, void(n && t.trim(n).length && (this.liveRegion.children().hide(), t("<div>").text(n).appendTo(this.liveRegion))))
                },
                menuselect: function(t, e) {
                    var i = e.item.data("ui-autocomplete-item"),
                    n = this.previous;
                    this.element[0] !== this.document[0].activeElement && (this.element.focus(), this.previous = n, this._delay(function() {
                        this.previous = n,
                        this.selectedItem = i
                    })),
                    !1 !== this._trigger("select", t, {
                        item: i
                    }) && this._value(i.value),
                    this.term = this._value(),
                    this.close(t),
                    this.selectedItem = i
                }
            }),
            this.liveRegion = t("<span>", {
                role: "status",
                "aria-live": "assertive",
                "aria-relevant": "additions"
            }).addClass("ui-helper-hidden-accessible").appendTo(this.document[0].body),
            this._on(this.window, {
                beforeunload: function() {
                    this.element.removeAttr("autocomplete")
                }
            })
        },
        _destroy: function() {
            clearTimeout(this.searching),
            this.element.removeClass("ui-autocomplete-input").removeAttr("autocomplete"),
            this.menu.element.remove(),
            this.liveRegion.remove()
        },
        _setOption: function(t, e) {
            this._super(t, e),
            "source" === t && this._initSource(),
            "appendTo" === t && this.menu.element.appendTo(this._appendTo()),
            "disabled" === t && e && this.xhr && this.xhr.abort()
        },
        _appendTo: function() {
            var e = this.options.appendTo;
            return e && (e = e.jquery || e.nodeType ? t(e) : this.document.find(e).eq(0)),
            e && e[0] || (e = this.element.closest(".ui-front")),
            e.length || (e = this.document[0].body),
            e
        },
        _initSource: function() {
            var e, i, n = this;
            t.isArray(this.options.source) ? (e = this.options.source, this.source = function(i, n) {
                n(t.ui.autocomplete.filter(e, i.term))
            }) : "string" == typeof this.options.source ? (i = this.options.source, this.source = function(e, s) {
                n.xhr && n.xhr.abort(),
                n.xhr = t.ajax({
                    url: i,
                    data: e,
                    dataType: "json",
                    success: function(t) {
                        s(t)
                    },
                    error: function() {
                        s([])
                    }
                })
            }) : this.source = this.options.source
        },
        _searchTimeout: function(t) {
            clearTimeout(this.searching),
            this.searching = this._delay(function() {
                var e = this.term === this._value(),
                i = this.menu.element.is(":visible"),
                n = t.altKey || t.ctrlKey || t.metaKey || t.shiftKey; (!e || e && !i && !n) && (this.selectedItem = null, this.search(null, t))
            },
            this.options.delay)
        },
        search: function(t, e) {
            return t = null != t ? t: this._value(),
            this.term = this._value(),
            t.length < this.options.minLength ? this.close(e) : this._trigger("search", e) !== !1 ? this._search(t) : void 0
        },
        _search: function(t) {
            this.pending++,
            this.element.addClass("ui-autocomplete-loading"),
            this.cancelSearch = !1,
            this.source({
                term: t
            },
            this._response())
        },
        _response: function() {
            var e = ++this.requestIndex;
            return t.proxy(function(t) {
                e === this.requestIndex && this.__response(t),
                this.pending--,
                this.pending || this.element.removeClass("ui-autocomplete-loading")
            },
            this)
        },
        __response: function(t) {
            t && (t = this._normalize(t)),
            this._trigger("response", null, {
                content: t
            }),
            !this.options.disabled && t && t.length && !this.cancelSearch ? (this._suggest(t), this._trigger("open")) : this._close()
        },
        close: function(t) {
            this.cancelSearch = !0,
            this._close(t)
        },
        _close: function(t) {
            this.menu.element.is(":visible") && (this.menu.element.hide(), this.menu.blur(), this.isNewMenu = !0, this._trigger("close", t))
        },
        _change: function(t) {
            this.previous !== this._value() && this._trigger("change", t, {
                item: this.selectedItem
            })
        },
        _normalize: function(e) {
            return e.length && e[0].label && e[0].value ? e: t.map(e,
            function(e) {
                return "string" == typeof e ? {
                    label: e,
                    value: e
                }: t.extend({},
                e, {
                    label: e.label || e.value,
                    value: e.value || e.label
                })
            })
        },
        _suggest: function(e) {
            var i = this.menu.element.empty();
            this._renderMenu(i, e),
            this.isNewMenu = !0,
            this.menu.refresh(),
            i.show(),

            this._resizeMenu(),
            i.position(t.extend({
                of: this.element
            },
            this.options.position)),
            this.options.autoFocus && this.menu.next()
        },
        _resizeMenu: function() {
            var t = this.menu.element;
            t.outerWidth(Math.max(t.width("").outerWidth() + 1, this.element.outerWidth()))
        },
        _renderMenu: function(e, i) {
            var n = this;
            t.each(i,
            function(t, i) {
                n._renderItemData(e, i)
            })
        },
        _renderItemData: function(t, e) {
            return this._renderItem(t, e).data("ui-autocomplete-item", e)
        },
        _renderItem: function(e, i) {
            return t("<li>").text(i.label).appendTo(e)
        },
        _move: function(t, e) {
            return this.menu.element.is(":visible") ? this.menu.isFirstItem() && /^previous/.test(t) || this.menu.isLastItem() && /^next/.test(t) ? (this.isMultiLine || this._value(this.term), void this.menu.blur()) : void this.menu[t](e) : void this.search(null, e)
        },
        widget: function() {
            return this.menu.element
        },
        _value: function() {
            return this.valueMethod.apply(this.element, arguments)
        },
        _keyEvent: function(t, e) { (!this.isMultiLine || this.menu.element.is(":visible")) && (this._move(t, e), e.preventDefault())
        }
    }),
    t.extend(t.ui.autocomplete, {
        escapeRegex: function(t) {
            return t.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&")
        },
        filter: function(e, i) {
            var n = new RegExp(t.ui.autocomplete.escapeRegex(i), "i");
            return t.grep(e,
            function(t) {
                return n.test(t.label || t.value || t)
            })
        }
    }),
    t.widget("ui.autocomplete", t.ui.autocomplete, {
        options: {
            messages: {
                noResults: "No search results.",
                results: function(t) {
                    return t + (t > 1 ? " results are": " result is") + " available, use up and down arrow keys to navigate."
                }
            }
        },
        __response: function(e) {
            var i;
            this._superApply(arguments),
            this.options.disabled || this.cancelSearch || (i = e && e.length ? this.options.messages.results(e.length) : this.options.messages.noResults, this.liveRegion.children().hide(), t("<div>").text(i).appendTo(this.liveRegion))
        }
    });
    var d, p = (t.ui.autocomplete, "ui-button ui-widget ui-state-default ui-corner-all"),
    f = "ui-button-icons-only ui-button-icon-only ui-button-text-icons ui-button-text-icon-primary ui-button-text-icon-secondary ui-button-text-only",
    m = function() {
        var e = t(this);
        setTimeout(function() {
            e.find(":ui-button").button("refresh")
        },
        1)
    },
    g = function(e) {
        var i = e.name,
        n = e.form,
        s = t([]);
        return i && (i = i.replace(/'/g, "\\'"), s = n ? t(n).find("[name='" + i + "'][type=radio]") : t("[name='" + i + "'][type=radio]", e.ownerDocument).filter(function() {
            return ! this.form
        })),
        s
    };
    t.widget("ui.button", {
        version: "1.11.4",
        defaultElement: "<button>",
        options: {
            disabled: null,
            text: !0,
            label: null,
            icons: {
                primary: null,
                secondary: null
            }
        },
        _create: function() {
            this.element.closest("form").unbind("reset" + this.eventNamespace).bind("reset" + this.eventNamespace, m),
            "boolean" != typeof this.options.disabled ? this.options.disabled = !!this.element.prop("disabled") : this.element.prop("disabled", this.options.disabled),
            this._determineButtonType(),
            this.hasTitle = !!this.buttonElement.attr("title");
            var e = this,
            i = this.options,
            n = "checkbox" === this.type || "radio" === this.type,
            s = n ? "": "ui-state-active";
            null === i.label && (i.label = "input" === this.type ? this.buttonElement.val() : this.buttonElement.html()),
            this._hoverable(this.buttonElement),
            this.buttonElement.addClass(p).attr("role", "button").bind("mouseenter" + this.eventNamespace,
            function() {
                i.disabled || this === d && t(this).addClass("ui-state-active")
            }).bind("mouseleave" + this.eventNamespace,
            function() {
                i.disabled || t(this).removeClass(s)
            }).bind("click" + this.eventNamespace,
            function(t) {
                i.disabled && (t.preventDefault(), t.stopImmediatePropagation())
            }),
            this._on({
                focus: function() {
                    this.buttonElement.addClass("ui-state-focus")
                },
                blur: function() {
                    this.buttonElement.removeClass("ui-state-focus")
                }
            }),
            n && this.element.bind("change" + this.eventNamespace,
            function() {
                e.refresh()
            }),
            "checkbox" === this.type ? this.buttonElement.bind("click" + this.eventNamespace,
            function() {
                return i.disabled ? !1 : void 0
            }) : "radio" === this.type ? this.buttonElement.bind("click" + this.eventNamespace,
            function() {
                if (i.disabled) return ! 1;
                t(this).addClass("ui-state-active"),
                e.buttonElement.attr("aria-pressed", "true");
                var n = e.element[0];
                g(n).not(n).map(function() {
                    return t(this).button("widget")[0]
                }).removeClass("ui-state-active").attr("aria-pressed", "false")
            }) : (this.buttonElement.bind("mousedown" + this.eventNamespace,
            function() {
                return i.disabled ? !1 : (t(this).addClass("ui-state-active"), d = this, void e.document.one("mouseup",
                function() {
                    d = null
                }))
            }).bind("mouseup" + this.eventNamespace,
            function() {
                return i.disabled ? !1 : void t(this).removeClass("ui-state-active")
            }).bind("keydown" + this.eventNamespace,
            function(e) {
                return i.disabled ? !1 : void((e.keyCode === t.ui.keyCode.SPACE || e.keyCode === t.ui.keyCode.ENTER) && t(this).addClass("ui-state-active"))
            }).bind("keyup" + this.eventNamespace + " blur" + this.eventNamespace,
            function() {
                t(this).removeClass("ui-state-active")
            }), this.buttonElement.is("a") && this.buttonElement.keyup(function(e) {
                e.keyCode === t.ui.keyCode.SPACE && t(this).click()
            })),
            this._setOption("disabled", i.disabled),
            this._resetButton()
        },
        _determineButtonType: function() {
            var t, e, i;
            this.type = this.element.is("[type=checkbox]") ? "checkbox": this.element.is("[type=radio]") ? "radio": this.element.is("input") ? "input": "button",
            "checkbox" === this.type || "radio" === this.type ? (t = this.element.parents().last(), e = "label[for='" + this.element.attr("id") + "']", this.buttonElement = t.find(e), this.buttonElement.length || (t = t.length ? t.siblings() : this.element.siblings(), this.buttonElement = t.filter(e), this.buttonElement.length || (this.buttonElement = t.find(e))), this.element.addClass("ui-helper-hidden-accessible"), i = this.element.is(":checked"), i && this.buttonElement.addClass("ui-state-active"), this.buttonElement.prop("aria-pressed", i)) : this.buttonElement = this.element
        },
        widget: function() {
            return this.buttonElement
        },
        _destroy: function() {
            this.element.removeClass("ui-helper-hidden-accessible"),
            this.buttonElement.removeClass(p + " ui-state-active " + f).removeAttr("role").removeAttr("aria-pressed").html(this.buttonElement.find(".ui-button-text").html()),
            this.hasTitle || this.buttonElement.removeAttr("title")
        },
        _setOption: function(t, e) {
            return this._super(t, e),
            "disabled" === t ? (this.widget().toggleClass("ui-state-disabled", !!e), this.element.prop("disabled", !!e), void(e && this.buttonElement.removeClass("checkbox" === this.type || "radio" === this.type ? "ui-state-focus": "ui-state-focus ui-state-active"))) : void this._resetButton()
        },
        refresh: function() {
            var e = this.element.is("input, button") ? this.element.is(":disabled") : this.element.hasClass("ui-button-disabled");
            e !== this.options.disabled && this._setOption("disabled", e),
            "radio" === this.type ? g(this.element[0]).each(function() {
                t(this).is(":checked") ? t(this).button("widget").addClass("ui-state-active").attr("aria-pressed", "true") : t(this).button("widget").removeClass("ui-state-active").attr("aria-pressed", "false")
            }) : "checkbox" === this.type && (this.element.is(":checked") ? this.buttonElement.addClass("ui-state-active").attr("aria-pressed", "true") : this.buttonElement.removeClass("ui-state-active").attr("aria-pressed", "false"))
        },
        _resetButton: function() {
            if ("input" === this.type) return void(this.options.label && this.element.val(this.options.label));
            var e = this.buttonElement.removeClass(f),
            i = t("<span></span>", this.document[0]).addClass("ui-button-text").html(this.options.label).appendTo(e.empty()).text(),
            n = this.options.icons,
            s = n.primary && n.secondary,
            o = [];
            n.primary || n.secondary ? (this.options.text && o.push("ui-button-text-icon" + (s ? "s": n.primary ? "-primary": "-secondary")), n.primary && e.prepend("<span class='ui-button-icon-primary ui-icon " + n.primary + "'></span>"), n.secondary && e.append("<span class='ui-button-icon-secondary ui-icon " + n.secondary + "'></span>"), this.options.text || (o.push(s ? "ui-button-icons-only": "ui-button-icon-only"), this.hasTitle || e.attr("title", t.trim(i)))) : o.push("ui-button-text-only"),
            e.addClass(o.join(" "))
        }
    }),
    t.widget("ui.buttonset", {
        version: "1.11.4",
        options: {
            items: "button, input[type=button], input[type=submit], input[type=reset], input[type=checkbox], input[type=radio], a, :data(ui-button)"
        },
        _create: function() {
            this.element.addClass("ui-buttonset")
        },
        _init: function() {
            this.refresh()
        },
        _setOption: function(t, e) {
            "disabled" === t && this.buttons.button("option", t, e),
            this._super(t, e)
        },
        refresh: function() {
            var e = "rtl" === this.element.css("direction"),
            i = this.element.find(this.options.items),
            n = i.filter(":ui-button");
            i.not(":ui-button").button(),
            n.button("refresh"),
            this.buttons = i.map(function() {
                return t(this).button("widget")[0]
            }).removeClass("ui-corner-all ui-corner-left ui-corner-right").filter(":first").addClass(e ? "ui-corner-right": "ui-corner-left").end().filter(":last").addClass(e ? "ui-corner-left": "ui-corner-right").end().end()
        },
        _destroy: function() {
            this.element.removeClass("ui-buttonset"),
            this.buttons.map(function() {
                return t(this).button("widget")[0]
            }).removeClass("ui-corner-left ui-corner-right").end().button("destroy")
        }
    });
    t.ui.button;
    t.extend(t.ui, {
        datepicker: {
            version: "1.11.4"
        }
    });
    var v;
    t.extend(s.prototype, {
        markerClassName: "hasDatepicker",
        maxRows: 4,
        _widgetDatepicker: function() {
            return this.dpDiv
        },
        setDefaults: function(t) {
            return a(this._defaults, t || {}),
            this
        },
        _attachDatepicker: function(e, i) {
            var n, s, o;
            n = e.nodeName.toLowerCase(),
            s = "div" === n || "span" === n,
            e.id || (this.uuid += 1, e.id = "dp" + this.uuid),
            o = this._newInst(t(e), s),
            o.settings = t.extend({},
            i || {}),
            "input" === n ? this._connectDatepicker(e, o) : s && this._inlineDatepicker(e, o)
        },
        _newInst: function(e, i) {
            var n = e[0].id.replace(/([^A-Za-z0-9_\-])/g, "\\\\$1");
            return {
                id: n,
                input: e,
                selectedDay: 0,
                selectedMonth: 0,
                selectedYear: 0,
                drawMonth: 0,
                drawYear: 0,
                inline: i,
                dpDiv: i ? o(t("<div class='" + this._inlineClass + " ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all'></div>")) : this.dpDiv
            }
        },
        _connectDatepicker: function(e, i) {
            var n = t(e);
            i.append = t([]),
            i.trigger = t([]),
            n.hasClass(this.markerClassName) || (this._attachments(n, i), n.addClass(this.markerClassName).keydown(this._doKeyDown).keypress(this._doKeyPress).keyup(this._doKeyUp), this._autoSize(i), t.data(e, "datepicker", i), i.settings.disabled && this._disableDatepicker(e))
        },
        _attachments: function(e, i) {
            var n, s, o, r = this._get(i, "appendText"),
            a = this._get(i, "isRTL");
            i.append && i.append.remove(),
            r && (i.append = t("<span class='" + this._appendClass + "'>" + r + "</span>"), e[a ? "before": "after"](i.append)),
            e.unbind("focus", this._showDatepicker),
            i.trigger && i.trigger.remove(),
            n = this._get(i, "showOn"),
            ("focus" === n || "both" === n) && e.focus(this._showDatepicker),
            ("button" === n || "both" === n) && (s = this._get(i, "buttonText"), o = this._get(i, "buttonImage"), i.trigger = t(this._get(i, "buttonImageOnly") ? t("<img/>").addClass(this._triggerClass).attr({
                src: o,
                alt: s,
                title: s
            }) : t("<button type='button'></button>").addClass(this._triggerClass).html(o ? t("<img/>").attr({
                src: o,
                alt: s,
                title: s
            }) : s)), e[a ? "before": "after"](i.trigger), i.trigger.click(function() {
                return t.datepicker._datepickerShowing && t.datepicker._lastInput === e[0] ? t.datepicker._hideDatepicker() : t.datepicker._datepickerShowing && t.datepicker._lastInput !== e[0] ? (t.datepicker._hideDatepicker(), t.datepicker._showDatepicker(e[0])) : t.datepicker._showDatepicker(e[0]),
                !1
            }))
        },
        _autoSize: function(t) {
            if (this._get(t, "autoSize") && !t.inline) {
                var e, i, n, s, o = new Date(2009, 11, 20),
                r = this._get(t, "dateFormat");
                r.match(/[DM]/) && (e = function(t) {
                    for (i = 0, n = 0, s = 0; s < t.length; s++) t[s].length > i && (i = t[s].length, n = s);
                    return n
                },
                o.setMonth(e(this._get(t, r.match(/MM/) ? "monthNames": "monthNamesShort"))), o.setDate(e(this._get(t, r.match(/DD/) ? "dayNames": "dayNamesShort")) + 20 - o.getDay())),
                t.input.attr("size", this._formatDate(t, o).length)
            }
        },
        _inlineDatepicker: function(e, i) {
            var n = t(e);
            n.hasClass(this.markerClassName) || (n.addClass(this.markerClassName).append(i.dpDiv), t.data(e, "datepicker", i), this._setDate(i, this._getDefaultDate(i), !0), this._updateDatepicker(i), this._updateAlternate(i), i.settings.disabled && this._disableDatepicker(e), i.dpDiv.css("display", "block"))
        },
        _dialogDatepicker: function(e, i, n, s, o) {
            var r, l, c, h, u, d = this._dialogInst;
            return d || (this.uuid += 1, r = "dp" + this.uuid, this._dialogInput = t("<input type='text' id='" + r + "' style='position: absolute; top: -100px; width: 0px;'/>"), this._dialogInput.keydown(this._doKeyDown), t("body").append(this._dialogInput), d = this._dialogInst = this._newInst(this._dialogInput, !1), d.settings = {},
            t.data(this._dialogInput[0], "datepicker", d)),
            a(d.settings, s || {}),
            i = i && i.constructor === Date ? this._formatDate(d, i) : i,
            this._dialogInput.val(i),
            this._pos = o ? o.length ? o: [o.pageX, o.pageY] : null,
            this._pos || (l = document.documentElement.clientWidth, c = document.documentElement.clientHeight, h = document.documentElement.scrollLeft || document.body.scrollLeft, u = document.documentElement.scrollTop || document.body.scrollTop, this._pos = [l / 2 - 100 + h, c / 2 - 150 + u]),
            this._dialogInput.css("left", this._pos[0] + 20 + "px").css("top", this._pos[1] + "px"),
            d.settings.onSelect = n,
            this._inDialog = !0,
            this.dpDiv.addClass(this._dialogClass),
            this._showDatepicker(this._dialogInput[0]),
            t.blockUI && t.blockUI(this.dpDiv),
            t.data(this._dialogInput[0], "datepicker", d),
            this
        },
        _destroyDatepicker: function(e) {
            var i, n = t(e),
            s = t.data(e, "datepicker");
            n.hasClass(this.markerClassName) && (i = e.nodeName.toLowerCase(), t.removeData(e, "datepicker"), "input" === i ? (s.append.remove(), s.trigger.remove(), n.removeClass(this.markerClassName).unbind("focus", this._showDatepicker).unbind("keydown", this._doKeyDown).unbind("keypress", this._doKeyPress).unbind("keyup", this._doKeyUp)) : ("div" === i || "span" === i) && n.removeClass(this.markerClassName).empty(), v === s && (v = null))
        },
        _enableDatepicker: function(e) {
            var i, n, s = t(e),
            o = t.data(e, "datepicker");
            s.hasClass(this.markerClassName) && (i = e.nodeName.toLowerCase(), "input" === i ? (e.disabled = !1, o.trigger.filter("button").each(function() {
                this.disabled = !1
            }).end().filter("img").css({
                opacity: "1.0",
                cursor: ""
            })) : ("div" === i || "span" === i) && (n = s.children("." + this._inlineClass), n.children().removeClass("ui-state-disabled"), n.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled", !1)), this._disabledInputs = t.map(this._disabledInputs,
            function(t) {
                return t === e ? null: t
            }))
        },
        _disableDatepicker: function(e) {
            var i, n, s = t(e),
            o = t.data(e, "datepicker");
            s.hasClass(this.markerClassName) && (i = e.nodeName.toLowerCase(), "input" === i ? (e.disabled = !0, o.trigger.filter("button").each(function() {
                this.disabled = !0
            }).end().filter("img").css({
                opacity: "0.5",
                cursor: "default"
            })) : ("div" === i || "span" === i) && (n = s.children("." + this._inlineClass), n.children().addClass("ui-state-disabled"), n.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled", !0)), this._disabledInputs = t.map(this._disabledInputs,
            function(t) {
                return t === e ? null: t
            }), this._disabledInputs[this._disabledInputs.length] = e)
        },
        _isDisabledDatepicker: function(t) {
            if (!t) return ! 1;
            for (var e = 0; e < this._disabledInputs.length; e++) if (this._disabledInputs[e] === t) return ! 0;
            return ! 1
        },
        _getInst: function(e) {
            try {
                return t.data(e, "datepicker")
            } catch(i) {
                throw "Missing instance data for this datepicker"
            }
        },
        _optionDatepicker: function(e, i, n) {
            var s, o, r, l, c = this._getInst(e);
            return 2 === arguments.length && "string" == typeof i ? "defaults" === i ? t.extend({},
            t.datepicker._defaults) : c ? "all" === i ? t.extend({},
            c.settings) : this._get(c, i) : null: (s = i || {},
            "string" == typeof i && (s = {},
            s[i] = n), void(c && (this._curInst === c && this._hideDatepicker(), o = this._getDateDatepicker(e, !0), r = this._getMinMaxDate(c, "min"), l = this._getMinMaxDate(c, "max"), a(c.settings, s), null !== r && void 0 !== s.dateFormat && void 0 === s.minDate && (c.settings.minDate = this._formatDate(c, r)), null !== l && void 0 !== s.dateFormat && void 0 === s.maxDate && (c.settings.maxDate = this._formatDate(c, l)), "disabled" in s && (s.disabled ? this._disableDatepicker(e) : this._enableDatepicker(e)), this._attachments(t(e), c), this._autoSize(c), this._setDate(c, o), this._updateAlternate(c), this._updateDatepicker(c))))
        },
        _changeDatepicker: function(t, e, i) {
            this._optionDatepicker(t, e, i)
        },
        _refreshDatepicker: function(t) {
            var e = this._getInst(t);
            e && this._updateDatepicker(e)
        },
        _setDateDatepicker: function(t, e) {
            var i = this._getInst(t);
            i && (this._setDate(i, e), this._updateDatepicker(i), this._updateAlternate(i))
        },
        _getDateDatepicker: function(t, e) {
            var i = this._getInst(t);
            return i && !i.inline && this._setDateFromField(i, e),
            i ? this._getDate(i) : null
        },
        _doKeyDown: function(e) {
            var i, n, s, o = t.datepicker._getInst(e.target),
            r = !0,
            a = o.dpDiv.is(".ui-datepicker-rtl");
            if (o._keyEvent = !0, t.datepicker._datepickerShowing) switch (e.keyCode) {
            case 9:
                t.datepicker._hideDatepicker(),
                r = !1;
                break;
            case 13:
                return s = t("td." + t.datepicker._dayOverClass + ":not(." + t.datepicker._currentClass + ")", o.dpDiv),
                s[0] && t.datepicker._selectDay(e.target, o.selectedMonth, o.selectedYear, s[0]),
                i = t.datepicker._get(o, "onSelect"),
                i ? (n = t.datepicker._formatDate(o), i.apply(o.input ? o.input[0] : null, [n, o])) : t.datepicker._hideDatepicker(),
                !1;
            case 27:
                t.datepicker._hideDatepicker();
                break;
            case 33:
                t.datepicker._adjustDate(e.target, e.ctrlKey ? -t.datepicker._get(o, "stepBigMonths") : -t.datepicker._get(o, "stepMonths"), "M");
                break;
            case 34:
                t.datepicker._adjustDate(e.target, e.ctrlKey ? +t.datepicker._get(o, "stepBigMonths") : +t.datepicker._get(o, "stepMonths"), "M");
                break;
            case 35:
                (e.ctrlKey || e.metaKey) && t.datepicker._clearDate(e.target),
                r = e.ctrlKey || e.metaKey;
                break;
            case 36:
                (e.ctrlKey || e.metaKey) && t.datepicker._gotoToday(e.target),
                r = e.ctrlKey || e.metaKey;
                break;
            case 37:
                (e.ctrlKey || e.metaKey) && t.datepicker._adjustDate(e.target, a ? 1 : -1, "D"),
                r = e.ctrlKey || e.metaKey,
                e.originalEvent.altKey && t.datepicker._adjustDate(e.target, e.ctrlKey ? -t.datepicker._get(o, "stepBigMonths") : -t.datepicker._get(o, "stepMonths"), "M");
                break;
            case 38:
                (e.ctrlKey || e.metaKey) && t.datepicker._adjustDate(e.target, -7, "D"),
                r = e.ctrlKey || e.metaKey;
                break;
            case 39:
                (e.ctrlKey || e.metaKey) && t.datepicker._adjustDate(e.target, a ? -1 : 1, "D"),
                r = e.ctrlKey || e.metaKey,
                e.originalEvent.altKey && t.datepicker._adjustDate(e.target, e.ctrlKey ? +t.datepicker._get(o, "stepBigMonths") : +t.datepicker._get(o, "stepMonths"), "M");
                break;
            case 40:
                (e.ctrlKey || e.metaKey) && t.datepicker._adjustDate(e.target, 7, "D"),
                r = e.ctrlKey || e.metaKey;
                break;
            default:
                r = !1
            } else 36 === e.keyCode && e.ctrlKey ? t.datepicker._showDatepicker(this) : r = !1;
            r && (e.preventDefault(), e.stopPropagation())
        },
        _doKeyPress: function(e) {
            var i, n, s = t.datepicker._getInst(e.target);
            return t.datepicker._get(s, "constrainInput") ? (i = t.datepicker._possibleChars(t.datepicker._get(s, "dateFormat")), n = String.fromCharCode(null == e.charCode ? e.keyCode: e.charCode), e.ctrlKey || e.metaKey || " " > n || !i || i.indexOf(n) > -1) : void 0
        },
        _doKeyUp: function(e) {
            var i, n = t.datepicker._getInst(e.target);
            if (n.input.val() !== n.lastVal) try {
                i = t.datepicker.parseDate(t.datepicker._get(n, "dateFormat"), n.input ? n.input.val() : null, t.datepicker._getFormatConfig(n)),
                i && (t.datepicker._setDateFromField(n), t.datepicker._updateAlternate(n), t.datepicker._updateDatepicker(n))
            } catch(s) {}
            return ! 0
        },
        _showDatepicker: function(e) {
            if (e = e.target || e, "input" !== e.nodeName.toLowerCase() && (e = t("input", e.parentNode)[0]), !t.datepicker._isDisabledDatepicker(e) && t.datepicker._lastInput !== e) {
                var i, s, o, r, l, c, h;
                i = t.datepicker._getInst(e),
                t.datepicker._curInst && t.datepicker._curInst !== i && (t.datepicker._curInst.dpDiv.stop(!0, !0), i && t.datepicker._datepickerShowing && t.datepicker._hideDatepicker(t.datepicker._curInst.input[0])),
                s = t.datepicker._get(i, "beforeShow"),
                o = s ? s.apply(e, [e, i]) : {},
                o !== !1 && (a(i.settings, o), i.lastVal = null, t.datepicker._lastInput = e, t.datepicker._setDateFromField(i), t.datepicker._inDialog && (e.value = ""), t.datepicker._pos || (t.datepicker._pos = t.datepicker._findPos(e), t.datepicker._pos[1] += e.offsetHeight), r = !1, t(e).parents().each(function() {
                    return r |= "fixed" === t(this).css("position"),
                    !r
                }), l = {
                    left: t.datepicker._pos[0],
                    top: t.datepicker._pos[1]
                },
                t.datepicker._pos = null, i.dpDiv.empty(), i.dpDiv.css({
                    position: "absolute",
                    display: "block",
                    top: "-1000px"
                }), t.datepicker._updateDatepicker(i), l = t.datepicker._checkOffset(i, l, r), i.dpDiv.css({
                    position: t.datepicker._inDialog && t.blockUI ? "static": r ? "fixed": "absolute",
                    display: "none",
                    left: l.left + "px",
                    top: l.top + "px"
                }), i.inline || (c = t.datepicker._get(i, "showAnim"), h = t.datepicker._get(i, "duration"), i.dpDiv.css("z-index", n(t(e)) + 1), t.datepicker._datepickerShowing = !0, t.effects && t.effects.effect[c] ? i.dpDiv.show(c, t.datepicker._get(i, "showOptions"), h) : i.dpDiv[c || "show"](c ? h: null), t.datepicker._shouldFocusInput(i) && i.input.focus(), t.datepicker._curInst = i))
            }
        },
        _updateDatepicker: function(e) {
            this.maxRows = 4,
            v = e,
            e.dpDiv.empty().append(this._generateHTML(e)),
            this._attachHandlers(e);
            var i, n = this._getNumberOfMonths(e),
            s = n[1],
            o = 17,
            a = e.dpDiv.find("." + this._dayOverClass + " a");
            a.length > 0 && r.apply(a.get(0)),
            e.dpDiv.removeClass("ui-datepicker-multi-2 ui-datepicker-multi-3 ui-datepicker-multi-4").width(""),
            s > 1 && e.dpDiv.addClass("ui-datepicker-multi-" + s).css("width", o * s + "em"),
            e.dpDiv[(1 !== n[0] || 1 !== n[1] ? "add": "remove") + "Class"]("ui-datepicker-multi"),
            e.dpDiv[(this._get(e, "isRTL") ? "add": "remove") + "Class"]("ui-datepicker-rtl"),
            e === t.datepicker._curInst && t.datepicker._datepickerShowing && t.datepicker._shouldFocusInput(e) && e.input.focus(),
            e.yearshtml && (i = e.yearshtml, setTimeout(function() {
                i === e.yearshtml && e.yearshtml && e.dpDiv.find("select.ui-datepicker-year:first").replaceWith(e.yearshtml),
                i = e.yearshtml = null
            },
            0))
        },
        _shouldFocusInput: function(t) {
            return t.input && t.input.is(":visible") && !t.input.is(":disabled") && !t.input.is(":focus")
        },
        _checkOffset: function(e, i, n) {
            var s = e.dpDiv.outerWidth(),
            o = e.dpDiv.outerHeight(),
            r = e.input ? e.input.outerWidth() : 0,
            a = e.input ? e.input.outerHeight() : 0,
            l = document.documentElement.clientWidth + (n ? 0 : t(document).scrollLeft()),
            c = document.documentElement.clientHeight + (n ? 0 : t(document).scrollTop());
            return i.left -= this._get(e, "isRTL") ? s - r: 0,
            i.left -= n && i.left === e.input.offset().left ? t(document).scrollLeft() : 0,
            i.top -= n && i.top === e.input.offset().top + a ? t(document).scrollTop() : 0,
            i.left -= Math.min(i.left, i.left + s > l && l > s ? Math.abs(i.left + s - l) : 0),
            i.top -= Math.min(i.top, i.top + o > c && c > o ? Math.abs(o + a) : 0),
            i
        },
        _findPos: function(e) {
            for (var i, n = this._getInst(e), s = this._get(n, "isRTL"); e && ("hidden" === e.type || 1 !== e.nodeType || t.expr.filters.hidden(e));) e = e[s ? "previousSibling": "nextSibling"];
            return i = t(e).offset(),
            [i.left, i.top]
        },
        _hideDatepicker: function(e) {
            var i, n, s, o, r = this._curInst; ! r || e && r !== t.data(e, "datepicker") || this._datepickerShowing && (i = this._get(r, "showAnim"), n = this._get(r, "duration"), s = function() {
                t.datepicker._tidyDialog(r)
            },
            t.effects && (t.effects.effect[i] || t.effects[i]) ? r.dpDiv.hide(i, t.datepicker._get(r, "showOptions"), n, s) : r.dpDiv["slideDown" === i ? "slideUp": "fadeIn" === i ? "fadeOut": "hide"](i ? n: null, s), i || s(), this._datepickerShowing = !1, o = this._get(r, "onClose"), o && o.apply(r.input ? r.input[0] : null, [r.input ? r.input.val() : "", r]), this._lastInput = null, this._inDialog && (this._dialogInput.css({
                position: "absolute",
                left: "0",
                top: "-100px"
            }), t.blockUI && (t.unblockUI(), t("body").append(this.dpDiv))), this._inDialog = !1)
        },
        _tidyDialog: function(t) {
            t.dpDiv.removeClass(this._dialogClass).unbind(".ui-datepicker-calendar")
        },
        _checkExternalClick: function(e) {
            if (t.datepicker._curInst) {
                var i = t(e.target),
                n = t.datepicker._getInst(i[0]); (i[0].id !== t.datepicker._mainDivId && 0 === i.parents("#" + t.datepicker._mainDivId).length && !i.hasClass(t.datepicker.markerClassName) && !i.closest("." + t.datepicker._triggerClass).length && t.datepicker._datepickerShowing && (!t.datepicker._inDialog || !t.blockUI) || i.hasClass(t.datepicker.markerClassName) && t.datepicker._curInst !== n) && t.datepicker._hideDatepicker()
            }
        },
        _adjustDate: function(e, i, n) {
            var s = t(e),
            o = this._getInst(s[0]);
            this._isDisabledDatepicker(s[0]) || (this._adjustInstDate(o, i + ("M" === n ? this._get(o, "showCurrentAtPos") : 0), n), this._updateDatepicker(o))
        },
        _gotoToday: function(e) {
            var i, n = t(e),
            s = this._getInst(n[0]);
            this._get(s, "gotoCurrent") && s.currentDay ? (s.selectedDay = s.currentDay, s.drawMonth = s.selectedMonth = s.currentMonth, s.drawYear = s.selectedYear = s.currentYear) : (i = new Date, s.selectedDay = i.getDate(), s.drawMonth = s.selectedMonth = i.getMonth(), s.drawYear = s.selectedYear = i.getFullYear()),
            this._notifyChange(s),
            this._adjustDate(n)
        },
        _selectMonthYear: function(e, i, n) {
            var s = t(e),
            o = this._getInst(s[0]);
            o["selected" + ("M" === n ? "Month": "Year")] = o["draw" + ("M" === n ? "Month": "Year")] = parseInt(i.options[i.selectedIndex].value, 10),
            this._notifyChange(o),
            this._adjustDate(s)
        },
        _selectDay: function(e, i, n, s) {
            var o, r = t(e);
            t(s).hasClass(this._unselectableClass) || this._isDisabledDatepicker(r[0]) || (o = this._getInst(r[0]), o.selectedDay = o.currentDay = t("a", s).html(), o.selectedMonth = o.currentMonth = i, o.selectedYear = o.currentYear = n, this._selectDate(e, this._formatDate(o, o.currentDay, o.currentMonth, o.currentYear)))
        },
        _clearDate: function(e) {
            var i = t(e);
            this._selectDate(i, "")
        },
        _selectDate: function(e, i) {
            var n, s = t(e),
            o = this._getInst(s[0]);
            i = null != i ? i: this._formatDate(o),
            o.input && o.input.val(i),
            this._updateAlternate(o),
            n = this._get(o, "onSelect"),
            n ? n.apply(o.input ? o.input[0] : null, [i, o]) : o.input && o.input.trigger("change"),
            o.inline ? this._updateDatepicker(o) : (this._hideDatepicker(), this._lastInput = o.input[0], "object" != typeof o.input[0] && o.input.focus(), this._lastInput = null)
        },
        _updateAlternate: function(e) {
            var i, n, s, o = this._get(e, "altField");
            o && (i = this._get(e, "altFormat") || this._get(e, "dateFormat"), n = this._getDate(e), s = this.formatDate(i, n, this._getFormatConfig(e)), t(o).each(function() {
                t(this).val(s)
            }))
        },
        noWeekends: function(t) {
            var e = t.getDay();
            return [e > 0 && 6 > e, ""]
        },
        iso8601Week: function(t) {
            var e, i = new Date(t.getTime());
            return i.setDate(i.getDate() + 4 - (i.getDay() || 7)),
            e = i.getTime(),
            i.setMonth(0),
            i.setDate(1),
            Math.floor(Math.round((e - i) / 864e5) / 7) + 1
        },
        parseDate: function(e, i, n) {
            if (null == e || null == i) throw "Invalid arguments";
            if (i = "object" == typeof i ? i.toString() : i + "", "" === i) return null;
            var s, o, r, a, l = 0,
            c = (n ? n.shortYearCutoff: null) || this._defaults.shortYearCutoff,
            h = "string" != typeof c ? c: (new Date).getFullYear() % 100 + parseInt(c, 10),
            u = (n ? n.dayNamesShort: null) || this._defaults.dayNamesShort,
            d = (n ? n.dayNames: null) || this._defaults.dayNames,
            p = (n ? n.monthNamesShort: null) || this._defaults.monthNamesShort,
            f = (n ? n.monthNames: null) || this._defaults.monthNames,
            m = -1,
            g = -1,
            v = -1,
            y = -1,
            b = !1,
            _ = function(t) {
                var i = s + 1 < e.length && e.charAt(s + 1) === t;
                return i && s++,
                i
            },
            w = function(t) {
                var e = _(t),
                n = "@" === t ? 14 : "!" === t ? 20 : "y" === t && e ? 4 : "o" === t ? 3 : 2,
                s = "y" === t ? n: 1,
                o = new RegExp("^\\d{" + s + "," + n + "}"),
                r = i.substring(l).match(o);
                if (!r) throw "Missing number at position " + l;
                return l += r[0].length,
                parseInt(r[0], 10)
            },
            x = function(e, n, s) {
                var o = -1,
                r = t.map(_(e) ? s: n,
                function(t, e) {
                    return [[e, t]]
                }).sort(function(t, e) {
                    return - (t[1].length - e[1].length)
                });
                if (t.each(r,
                function(t, e) {
                    var n = e[1];
                    return i.substr(l, n.length).toLowerCase() === n.toLowerCase() ? (o = e[0], l += n.length, !1) : void 0
                }), -1 !== o) return o + 1;
                throw "Unknown name at position " + l
            },
            C = function() {
                if (i.charAt(l) !== e.charAt(s)) throw "Unexpected literal at position " + l;
                l++
            };
            for (s = 0; s < e.length; s++) if (b)"'" !== e.charAt(s) || _("'") ? C() : b = !1;
            else switch (e.charAt(s)) {
            case "d":
                v = w("d");
                break;
            case "D":
                x("D", u, d);
                break;
            case "o":
                y = w("o");
                break;
            case "m":
                g = w("m");
                break;
            case "M":
                g = x("M", p, f);
                break;
            case "y":
                m = w("y");
                break;
            case "@":
                a = new Date(w("@")),
                m = a.getFullYear(),
                g = a.getMonth() + 1,
                v = a.getDate();
                break;
            case "!":
                a = new Date((w("!") - this._ticksTo1970) / 1e4),
                m = a.getFullYear(),
                g = a.getMonth() + 1,
                v = a.getDate();
                break;
            case "'":
                _("'") ? C() : b = !0;
                break;
            default:
                C()
            }
            if (l < i.length && (r = i.substr(l), !/^\s+/.test(r))) throw "Extra/unparsed characters found in date: " + r;
            if ( - 1 === m ? m = (new Date).getFullYear() : 100 > m && (m += (new Date).getFullYear() - (new Date).getFullYear() % 100 + (h >= m ? 0 : -100)), y > -1) for (g = 1, v = y;;) {
                if (o = this._getDaysInMonth(m, g - 1), o >= v) break;
                g++,
                v -= o
            }
            if (a = this._daylightSavingAdjust(new Date(m, g - 1, v)), a.getFullYear() !== m || a.getMonth() + 1 !== g || a.getDate() !== v) throw "Invalid date";
            return a
        },
        ATOM: "yy-mm-dd",
        COOKIE: "D, dd M yy",
        ISO_8601: "yy-mm-dd",
        RFC_822: "D, d M y",
        RFC_850: "DD, dd-M-y",
        RFC_1036: "D, d M y",
        RFC_1123: "D, d M yy",
        RFC_2822: "D, d M yy",
        RSS: "D, d M y",
        TICKS: "!",
        TIMESTAMP: "@",
        W3C: "yy-mm-dd",
        _ticksTo1970: 24 * (718685 + Math.floor(492.5) - Math.floor(19.7) + Math.floor(4.925)) * 60 * 60 * 1e7,
        formatDate: function(t, e, i) {
            if (!e) return "";
            var n, s = (i ? i.dayNamesShort: null) || this._defaults.dayNamesShort,
            o = (i ? i.dayNames: null) || this._defaults.dayNames,
            r = (i ? i.monthNamesShort: null) || this._defaults.monthNamesShort,
            a = (i ? i.monthNames: null) || this._defaults.monthNames,
            l = function(e) {
                var i = n + 1 < t.length && t.charAt(n + 1) === e;
                return i && n++,
                i
            },
            c = function(t, e, i) {
                var n = "" + e;
                if (l(t)) for (; n.length < i;) n = "0" + n;
                return n
            },
            h = function(t, e, i, n) {
                return l(t) ? n[e] : i[e]
            },
            u = "",
            d = !1;
            if (e) for (n = 0; n < t.length; n++) if (d)"'" !== t.charAt(n) || l("'") ? u += t.charAt(n) : d = !1;
            else switch (t.charAt(n)) {
            case "d":
                u += c("d", e.getDate(), 2);
                break;
            case "D":
                u += h("D", e.getDay(), s, o);
                break;
            case "o":
                u += c("o", Math.round((new Date(e.getFullYear(), e.getMonth(), e.getDate()).getTime() - new Date(e.getFullYear(), 0, 0).getTime()) / 864e5), 3);
                break;
            case "m":
                u += c("m", e.getMonth() + 1, 2);
                break;
            case "M":
                u += h("M", e.getMonth(), r, a);
                break;
            case "y":
                u += l("y") ? e.getFullYear() : (e.getYear() % 100 < 10 ? "0": "") + e.getYear() % 100;
                break;
            case "@":
                u += e.getTime();
                break;
            case "!":
                u += 1e4 * e.getTime() + this._ticksTo1970;
                break;
            case "'":
                l("'") ? u += "'": d = !0;
                break;
            default:
                u += t.charAt(n)
            }
            return u
        },
        _possibleChars: function(t) {
            var e, i = "",
            n = !1,
            s = function(i) {
                var n = e + 1 < t.length && t.charAt(e + 1) === i;
                return n && e++,
                n
            };
            for (e = 0; e < t.length; e++) if (n)"'" !== t.charAt(e) || s("'") ? i += t.charAt(e) : n = !1;
            else switch (t.charAt(e)) {
            case "d":
            case "m":
            case "y":
            case "@":
                i += "0123456789";
                break;
            case "D":
            case "M":
                return null;
            case "'":
                s("'") ? i += "'": n = !0;
                break;
            default:
                i += t.charAt(e)
            }
            return i
        },
        _get: function(t, e) {
            return void 0 !== t.settings[e] ? t.settings[e] : this._defaults[e]
        },
        _setDateFromField: function(t, e) {
            if (t.input.val() !== t.lastVal) {
                var i = this._get(t, "dateFormat"),
                n = t.lastVal = t.input ? t.input.val() : null,
                s = this._getDefaultDate(t),
                o = s,
                r = this._getFormatConfig(t);
                try {
                    o = this.parseDate(i, n, r) || s
                } catch(a) {
                    n = e ? "": n
                }
                t.selectedDay = o.getDate(),
                t.drawMonth = t.selectedMonth = o.getMonth(),
                t.drawYear = t.selectedYear = o.getFullYear(),
                t.currentDay = n ? o.getDate() : 0,
                t.currentMonth = n ? o.getMonth() : 0,
                t.currentYear = n ? o.getFullYear() : 0,
                this._adjustInstDate(t)
            }
        },
        _getDefaultDate: function(t) {
            return this._restrictMinMax(t, this._determineDate(t, this._get(t, "defaultDate"), new Date))
        },
        _determineDate: function(e, i, n) {
            var s = function(t) {
                var e = new Date;
                return e.setDate(e.getDate() + t),
                e
            },
            o = function(i) {
                try {
                    return t.datepicker.parseDate(t.datepicker._get(e, "dateFormat"), i, t.datepicker._getFormatConfig(e))
                } catch(n) {}
                for (var s = (i.toLowerCase().match(/^c/) ? t.datepicker._getDate(e) : null) || new Date, o = s.getFullYear(), r = s.getMonth(), a = s.getDate(), l = /([+\-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g, c = l.exec(i); c;) {
                    switch (c[2] || "d") {
                    case "d":
                    case "D":
                        a += parseInt(c[1], 10);
                        break;
                    case "w":
                    case "W":
                        a += 7 * parseInt(c[1], 10);
                        break;
                    case "m":
                    case "M":
                        r += parseInt(c[1], 10),
                        a = Math.min(a, t.datepicker._getDaysInMonth(o, r));
                        break;
                    case "y":
                    case "Y":
                        o += parseInt(c[1], 10),
                        a = Math.min(a, t.datepicker._getDaysInMonth(o, r))
                    }
                    c = l.exec(i)
                }
                return new Date(o, r, a)
            },
            r = null == i || "" === i ? n: "string" == typeof i ? o(i) : "number" == typeof i ? isNaN(i) ? n: s(i) : new Date(i.getTime());
            return r = r && "Invalid Date" === r.toString() ? n: r,
            r && (r.setHours(0), r.setMinutes(0), r.setSeconds(0), r.setMilliseconds(0)),
            this._daylightSavingAdjust(r)
        },
        _daylightSavingAdjust: function(t) {
            return t ? (t.setHours(t.getHours() > 12 ? t.getHours() + 2 : 0), t) : null
        },
        _setDate: function(t, e, i) {
            var n = !e,
            s = t.selectedMonth,
            o = t.selectedYear,
            r = this._restrictMinMax(t, this._determineDate(t, e, new Date));
            t.selectedDay = t.currentDay = r.getDate(),
            t.drawMonth = t.selectedMonth = t.currentMonth = r.getMonth(),
            t.drawYear = t.selectedYear = t.currentYear = r.getFullYear(),
            s === t.selectedMonth && o === t.selectedYear || i || this._notifyChange(t),
            this._adjustInstDate(t),
            t.input && t.input.val(n ? "": this._formatDate(t))
        },
        _getDate: function(t) {
            var e = !t.currentYear || t.input && "" === t.input.val() ? null: this._daylightSavingAdjust(new Date(t.currentYear, t.currentMonth, t.currentDay));
            return e
        },
        _attachHandlers: function(e) {
            var i = this._get(e, "stepMonths"),
            n = "#" + e.id.replace(/\\\\/g, "\\");
            e.dpDiv.find("[data-handler]").map(function() {
                var e = {
                    prev: function() {
                        t.datepicker._adjustDate(n, -i, "M")
                    },
                    next: function() {
                        t.datepicker._adjustDate(n, +i, "M")
                    },
                    hide: function() {
                        t.datepicker._hideDatepicker()
                    },
                    today: function() {
                        t.datepicker._gotoToday(n)
                    },
                    selectDay: function() {
                        return t.datepicker._selectDay(n, +this.getAttribute("data-month"), +this.getAttribute("data-year"), this),
                        !1
                    },
                    selectMonth: function() {
                        return t.datepicker._selectMonthYear(n, this, "M"),
                        !1
                    },
                    selectYear: function() {
                        return t.datepicker._selectMonthYear(n, this, "Y"),
                        !1
                    }
                };
                t(this).bind(this.getAttribute("data-event"), e[this.getAttribute("data-handler")])
            })
        },
        _generateHTML: function(t) {
            var e, i, n, s, o, r, a, l, c, h, u, d, p, f, m, g, v, y, b, _, w, x, C, k, T, D, E, M, I, S, P, A, N, O, z, H, j, R, $, L = new Date,
            W = this._daylightSavingAdjust(new Date(L.getFullYear(), L.getMonth(), L.getDate())),
            F = this._get(t, "isRTL"),
            V = this._get(t, "showButtonPanel"),
            B = this._get(t, "hideIfNoPrevNext"),
            q = this._get(t, "navigationAsDateFormat"),
            U = this._getNumberOfMonths(t),
            Y = this._get(t, "showCurrentAtPos"),
            X = this._get(t, "stepMonths"),
            K = 1 !== U[0] || 1 !== U[1],
            G = this._daylightSavingAdjust(t.currentDay ? new Date(t.currentYear, t.currentMonth, t.currentDay) : new Date(9999, 9, 9)),
            J = this._getMinMaxDate(t, "min"),
            Q = this._getMinMaxDate(t, "max"),
            Z = t.drawMonth - Y,
            te = t.drawYear;
            if (0 > Z && (Z += 12, te--), Q) for (e = this._daylightSavingAdjust(new Date(Q.getFullYear(), Q.getMonth() - U[0] * U[1] + 1, Q.getDate())), e = J && J > e ? J: e; this._daylightSavingAdjust(new Date(te, Z, 1)) > e;) Z--,
            0 > Z && (Z = 11, te--);
            for (t.drawMonth = Z, t.drawYear = te, i = this._get(t, "prevText"), i = q ? this.formatDate(i, this._daylightSavingAdjust(new Date(te, Z - X, 1)), this._getFormatConfig(t)) : i, n = this._canAdjustMonth(t, -1, te, Z) ? "<a class='ui-datepicker-prev ui-corner-all' data-handler='prev' data-event='click' title='" + i + "'><span class='ui-icon ui-icon-circle-triangle-" + (F ? "e": "w") + "'>" + i + "</span></a>": B ? "": "<a class='ui-datepicker-prev ui-corner-all ui-state-disabled' title='" + i + "'><span class='ui-icon ui-icon-circle-triangle-" + (F ? "e": "w") + "'>" + i + "</span></a>", s = this._get(t, "nextText"), s = q ? this.formatDate(s, this._daylightSavingAdjust(new Date(te, Z + X, 1)), this._getFormatConfig(t)) : s, o = this._canAdjustMonth(t, 1, te, Z) ? "<a class='ui-datepicker-next ui-corner-all' data-handler='next' data-event='click' title='" + s + "'><span class='ui-icon ui-icon-circle-triangle-" + (F ? "w": "e") + "'>" + s + "</span></a>": B ? "": "<a class='ui-datepicker-next ui-corner-all ui-state-disabled' title='" + s + "'><span class='ui-icon ui-icon-circle-triangle-" + (F ? "w": "e") + "'>" + s + "</span></a>", r = this._get(t, "currentText"), a = this._get(t, "gotoCurrent") && t.currentDay ? G: W, r = q ? this.formatDate(r, a, this._getFormatConfig(t)) : r, l = t.inline ? "": "<button type='button' class='ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all' data-handler='hide' data-event='click'>" + this._get(t, "closeText") + "</button>", c = V ? "<div class='ui-datepicker-buttonpane ui-widget-content'>" + (F ? l: "") + (this._isInRange(t, a) ? "<button type='button' class='ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all' data-handler='today' data-event='click'>" + r + "</button>": "") + (F ? "": l) + "</div>": "", h = parseInt(this._get(t, "firstDay"), 10), h = isNaN(h) ? 0 : h, u = this._get(t, "showWeek"), d = this._get(t, "dayNames"), p = this._get(t, "dayNamesMin"), f = this._get(t, "monthNames"), m = this._get(t, "monthNamesShort"), g = this._get(t, "beforeShowDay"), v = this._get(t, "showOtherMonths"), y = this._get(t, "selectOtherMonths"), b = this._getDefaultDate(t), _ = "", x = 0; x < U[0]; x++) {
                for (C = "", this.maxRows = 4, k = 0; k < U[1]; k++) {
                    if (T = this._daylightSavingAdjust(new Date(te, Z, t.selectedDay)), D = " ui-corner-all", E = "", K) {
                        if (E += "<div class='ui-datepicker-group", U[1] > 1) switch (k) {
                        case 0:
                            E += " ui-datepicker-group-first",
                            D = " ui-corner-" + (F ? "right": "left");
                            break;
                        case U[1] - 1 : E += " ui-datepicker-group-last",
                            D = " ui-corner-" + (F ? "left": "right");
                            break;
                        default:
                            E += " ui-datepicker-group-middle",
                            D = ""
                        }
                        E += "'>"
                    }
                    for (E += "<div class='ui-datepicker-header ui-widget-header ui-helper-clearfix" + D + "'>" + (/all|left/.test(D) && 0 === x ? F ? o: n: "") + (/all|right/.test(D) && 0 === x ? F ? n: o: "") + this._generateMonthYearHeader(t, Z, te, J, Q, x > 0 || k > 0, f, m) + "</div><table class='ui-datepicker-calendar'><thead><tr>", M = u ? "<th class='ui-datepicker-week-col'>" + this._get(t, "weekHeader") + "</th>": "", w = 0; 7 > w; w++) I = (w + h) % 7,
                    M += "<th scope='col'" + ((w + h + 6) % 7 >= 5 ? " class='ui-datepicker-week-end'": "") + "><span title='" + d[I] + "'>" + p[I] + "</span></th>";
                    for (E += M + "</tr></thead><tbody>", S = this._getDaysInMonth(te, Z), te === t.selectedYear && Z === t.selectedMonth && (t.selectedDay = Math.min(t.selectedDay, S)), P = (this._getFirstDayOfMonth(te, Z) - h + 7) % 7, A = Math.ceil((P + S) / 7), N = K && this.maxRows > A ? this.maxRows: A, this.maxRows = N, O = this._daylightSavingAdjust(new Date(te, Z, 1 - P)), z = 0; N > z; z++) {
                        for (E += "<tr>", H = u ? "<td class='ui-datepicker-week-col'>" + this._get(t, "calculateWeek")(O) + "</td>": "", w = 0; 7 > w; w++) j = g ? g.apply(t.input ? t.input[0] : null, [O]) : [!0, ""],
                        R = O.getMonth() !== Z,
                        $ = R && !y || !j[0] || J && J > O || Q && O > Q,
                        H += "<td class='" + ((w + h + 6) % 7 >= 5 ? " ui-datepicker-week-end": "") + (R ? " ui-datepicker-other-month": "") + (O.getTime() === T.getTime() && Z === t.selectedMonth && t._keyEvent || b.getTime() === O.getTime() && b.getTime() === T.getTime() ? " " + this._dayOverClass: "") + ($ ? " " + this._unselectableClass + " ui-state-disabled": "") + (R && !v ? "": " " + j[1] + (O.getTime() === G.getTime() ? " " + this._currentClass: "") + (O.getTime() === W.getTime() ? " ui-datepicker-today": "")) + "'" + (R && !v || !j[2] ? "": " title='" + j[2].replace(/'/g, "&#39;") + "'") + ($ ? "": " data-handler='selectDay' data-event='click' data-month='" + O.getMonth() + "' data-year='" + O.getFullYear() + "'") + ">" + (R && !v ? "&#xa0;": $ ? "<span class='ui-state-default'>" + O.getDate() + "</span>": "<a class='ui-state-default" + (O.getTime() === W.getTime() ? " ui-state-highlight": "") + (O.getTime() === G.getTime() ? " ui-state-active": "") + (R ? " ui-priority-secondary": "") + "' href='#'>" + O.getDate() + "</a>") + "</td>",
                        O.setDate(O.getDate() + 1),
                        O = this._daylightSavingAdjust(O);
                        E += H + "</tr>"
                    }
                    Z++,
                    Z > 11 && (Z = 0, te++),
                    E += "</tbody></table>" + (K ? "</div>" + (U[0] > 0 && k === U[1] - 1 ? "<div class='ui-datepicker-row-break'></div>": "") : ""),
                    C += E
                }
                _ += C
            }
            return _ += c,
            t._keyEvent = !1,
            _
        },
        _generateMonthYearHeader: function(t, e, i, n, s, o, r, a) {
            var l, c, h, u, d, p, f, m, g = this._get(t, "changeMonth"),
            v = this._get(t, "changeYear"),
            y = this._get(t, "showMonthAfterYear"),
            b = "<div class='ui-datepicker-title'>",
            _ = "";
            if (o || !g) _ += "<span class='ui-datepicker-month'>" + r[e] + "</span>";
            else {
                for (l = n && n.getFullYear() === i, c = s && s.getFullYear() === i, _ += "<select class='ui-datepicker-month' data-handler='selectMonth' data-event='change'>", h = 0; 12 > h; h++)(!l || h >= n.getMonth()) && (!c || h <= s.getMonth()) && (_ += "<option value='" + h + "'" + (h === e ? " selected='selected'": "") + ">" + a[h] + "</option>");
                _ += "</select>"
            }
            if (y || (b += _ + (!o && g && v ? "": "&#xa0;")), !t.yearshtml) if (t.yearshtml = "", o || !v) b += "<span class='ui-datepicker-year'>" + i + "</span>";
            else {
                for (u = this._get(t, "yearRange").split(":"), d = (new Date).getFullYear(), p = function(t) {
                    var e = t.match(/c[+\-].*/) ? i + parseInt(t.substring(1), 10) : t.match(/[+\-].*/) ? d + parseInt(t, 10) : parseInt(t, 10);
                    return isNaN(e) ? d: e
                },
                f = p(u[0]), m = Math.max(f, p(u[1] || "")), f = n ? Math.max(f, n.getFullYear()) : f, m = s ? Math.min(m, s.getFullYear()) : m, t.yearshtml += "<select class='ui-datepicker-year' data-handler='selectYear' data-event='change'>"; m >= f; f++) t.yearshtml += "<option value='" + f + "'" + (f === i ? " selected='selected'": "") + ">" + f + "</option>";
                t.yearshtml += "</select>",
                b += t.yearshtml,
                t.yearshtml = null
            }
            return b += this._get(t, "yearSuffix"),
            y && (b += (!o && g && v ? "": "&#xa0;") + _),
            b += "</div>"
        },
        _adjustInstDate: function(t, e, i) {
            var n = t.drawYear + ("Y" === i ? e: 0),
            s = t.drawMonth + ("M" === i ? e: 0),
            o = Math.min(t.selectedDay, this._getDaysInMonth(n, s)) + ("D" === i ? e: 0),
            r = this._restrictMinMax(t, this._daylightSavingAdjust(new Date(n, s, o)));
            t.selectedDay = r.getDate(),
            t.drawMonth = t.selectedMonth = r.getMonth(),
            t.drawYear = t.selectedYear = r.getFullYear(),
            ("M" === i || "Y" === i) && this._notifyChange(t)
        },
        _restrictMinMax: function(t, e) {
            var i = this._getMinMaxDate(t, "min"),
            n = this._getMinMaxDate(t, "max"),
            s = i && i > e ? i: e;
            return n && s > n ? n: s
        },
        _notifyChange: function(t) {
            var e = this._get(t, "onChangeMonthYear");
            e && e.apply(t.input ? t.input[0] : null, [t.selectedYear, t.selectedMonth + 1, t])
        },
        _getNumberOfMonths: function(t) {
            var e = this._get(t, "numberOfMonths");
            return null == e ? [1, 1] : "number" == typeof e ? [1, e] : e
        },
        _getMinMaxDate: function(t, e) {
            return this._determineDate(t, this._get(t, e + "Date"), null)
        },
        _getDaysInMonth: function(t, e) {
            return 32 - this._daylightSavingAdjust(new Date(t, e, 32)).getDate()
        },
        _getFirstDayOfMonth: function(t, e) {
            return new Date(t, e, 1).getDay()
        },
        _canAdjustMonth: function(t, e, i, n) {
            var s = this._getNumberOfMonths(t),
            o = this._daylightSavingAdjust(new Date(i, n + (0 > e ? e: s[0] * s[1]), 1));
            return 0 > e && o.setDate(this._getDaysInMonth(o.getFullYear(), o.getMonth())),
            this._isInRange(t, o)
        },
        _isInRange: function(t, e) {
            var i, n, s = this._getMinMaxDate(t, "min"),
            o = this._getMinMaxDate(t, "max"),
            r = null,
            a = null,
            l = this._get(t, "yearRange");
            return l && (i = l.split(":"), n = (new Date).getFullYear(), r = parseInt(i[0], 10), a = parseInt(i[1], 10), i[0].match(/[+\-].*/) && (r += n), i[1].match(/[+\-].*/) && (a += n)),
            (!s || e.getTime() >= s.getTime()) && (!o || e.getTime() <= o.getTime()) && (!r || e.getFullYear() >= r) && (!a || e.getFullYear() <= a)
        },
        _getFormatConfig: function(t) {
            var e = this._get(t, "shortYearCutoff");
            return e = "string" != typeof e ? e: (new Date).getFullYear() % 100 + parseInt(e, 10),
            {
                shortYearCutoff: e,
                dayNamesShort: this._get(t, "dayNamesShort"),
                dayNames: this._get(t, "dayNames"),
                monthNamesShort: this._get(t, "monthNamesShort"),
                monthNames: this._get(t, "monthNames")
            }
        },
        _formatDate: function(t, e, i, n) {
            e || (t.currentDay = t.selectedDay, t.currentMonth = t.selectedMonth, t.currentYear = t.selectedYear);
            var s = e ? "object" == typeof e ? e: this._daylightSavingAdjust(new Date(n, i, e)) : this._daylightSavingAdjust(new Date(t.currentYear, t.currentMonth, t.currentDay));
            return this.formatDate(this._get(t, "dateFormat"), s, this._getFormatConfig(t))
        }
    }),
    t.fn.datepicker = function(e) {
        if (!this.length) return this;
        t.datepicker.initialized || (t(document).mousedown(t.datepicker._checkExternalClick), t.datepicker.initialized = !0),
        0 === t("#" + t.datepicker._mainDivId).length && t("body").append(t.datepicker.dpDiv);
        var i = Array.prototype.slice.call(arguments, 1);
        return "string" != typeof e || "isDisabled" !== e && "getDate" !== e && "widget" !== e ? "option" === e && 2 === arguments.length && "string" == typeof arguments[1] ? t.datepicker["_" + e + "Datepicker"].apply(t.datepicker, [this[0]].concat(i)) : this.each(function() {
            "string" == typeof e ? t.datepicker["_" + e + "Datepicker"].apply(t.datepicker, [this].concat(i)) : t.datepicker._attachDatepicker(this, e)
        }) : t.datepicker["_" + e + "Datepicker"].apply(t.datepicker, [this[0]].concat(i))
    },
    t.datepicker = new s,
    t.datepicker.initialized = !1,
    t.datepicker.uuid = (new Date).getTime(),
    t.datepicker.version = "1.11.4";
    t.datepicker;
    t.widget("ui.draggable", t.ui.mouse, {
        version: "1.11.4",
        widgetEventPrefix: "drag",
        options: {
            addClasses: !0,
            appendTo: "parent",
            axis: !1,
            connectToSortable: !1,
            containment: !1,
            cursor: "auto",
            cursorAt: !1,
            grid: !1,
            handle: !1,
            helper: "original",
            iframeFix: !1,
            opacity: !1,
            refreshPositions: !1,
            revert: !1,
            revertDuration: 500,
            scope: "default",
            scroll: !0,
            scrollSensitivity: 20,
            scrollSpeed: 20,
            snap: !1,
            snapMode: "both",
            snapTolerance: 20,
            stack: !1,
            zIndex: !1,
            drag: null,
            start: null,
            stop: null
        },
        _create: function() {
            "original" === this.options.helper && this._setPositionRelative(),
            this.options.addClasses && this.element.addClass("ui-draggable"),
            this.options.disabled && this.element.addClass("ui-draggable-disabled"),
            this._setHandleClassName(),
            this._mouseInit()
        },
        _setOption: function(t, e) {
            this._super(t, e),
            "handle" === t && (this._removeHandleClassName(), this._setHandleClassName())
        },
        _destroy: function() {
            return (this.helper || this.element).is(".ui-draggable-dragging") ? void(this.destroyOnClear = !0) : (this.element.removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled"), this._removeHandleClassName(), void this._mouseDestroy())
        },
        _mouseCapture: function(e) {
            var i = this.options;
            return this._blurActiveElement(e),
            this.helper || i.disabled || t(e.target).closest(".ui-resizable-handle").length > 0 ? !1 : (this.handle = this._getHandle(e), this.handle ? (this._blockFrames(i.iframeFix === !0 ? "iframe": i.iframeFix), !0) : !1)
        },
        _blockFrames: function(e) {
            this.iframeBlocks = this.document.find(e).map(function() {
                var e = t(this);
                return t("<div>").css("position", "absolute").appendTo(e.parent()).outerWidth(e.outerWidth()).outerHeight(e.outerHeight()).offset(e.offset())[0]
            })
        },
        _unblockFrames: function() {
            this.iframeBlocks && (this.iframeBlocks.remove(), delete this.iframeBlocks)
        },
        _blurActiveElement: function(e) {
            var i = this.document[0];
            if (this.handleElement.is(e.target)) try {
                i.activeElement && "body" !== i.activeElement.nodeName.toLowerCase() && t(i.activeElement).blur()
            } catch(n) {}
        },
        _mouseStart: function(e) {
            var i = this.options;
            return this.helper = this._createHelper(e),
            this.helper.addClass("ui-draggable-dragging"),
            this._cacheHelperProportions(),
            t.ui.ddmanager && (t.ui.ddmanager.current = this),
            this._cacheMargins(),
            this.cssPosition = this.helper.css("position"),
            this.scrollParent = this.helper.scrollParent(!0),
            this.offsetParent = this.helper.offsetParent(),
            this.hasFixedAncestor = this.helper.parents().filter(function() {
                return "fixed" === t(this).css("position")
            }).length > 0,
            this.positionAbs = this.element.offset(),
            this._refreshOffsets(e),
            this.originalPosition = this.position = this._generatePosition(e, !1),
            this.originalPageX = e.pageX,
            this.originalPageY = e.pageY,
            i.cursorAt && this._adjustOffsetFromHelper(i.cursorAt),
            this._setContainment(),
            this._trigger("start", e) === !1 ? (this._clear(), !1) : (this._cacheHelperProportions(), t.ui.ddmanager && !i.dropBehaviour && t.ui.ddmanager.prepareOffsets(this, e), this._normalizeRightBottom(), this._mouseDrag(e, !0), t.ui.ddmanager && t.ui.ddmanager.dragStart(this, e), !0)
        },
        _refreshOffsets: function(t) {
            this.offset = {
                top: this.positionAbs.top - this.margins.top,
                left: this.positionAbs.left - this.margins.left,
                scroll: !1,
                parent: this._getParentOffset(),
                relative: this._getRelativeOffset()
            },
            this.offset.click = {
                left: t.pageX - this.offset.left,
                top: t.pageY - this.offset.top
            }
        },
        _mouseDrag: function(e, i) {
            if (this.hasFixedAncestor && (this.offset.parent = this._getParentOffset()), this.position = this._generatePosition(e, !0), this.positionAbs = this._convertPositionTo("absolute"), !i) {
                var n = this._uiHash();
                if (this._trigger("drag", e, n) === !1) return this._mouseUp({}),
                !1;
                this.position = n.position
            }
            return this.helper[0].style.left = this.position.left + "px",
            this.helper[0].style.top = this.position.top + "px",
            t.ui.ddmanager && t.ui.ddmanager.drag(this, e),
            !1
        },
        _mouseStop: function(e) {
            var i = this,
            n = !1;
            return t.ui.ddmanager && !this.options.dropBehaviour && (n = t.ui.ddmanager.drop(this, e)),
            this.dropped && (n = this.dropped, this.dropped = !1),
            "invalid" === this.options.revert && !n || "valid" === this.options.revert && n || this.options.revert === !0 || t.isFunction(this.options.revert) && this.options.revert.call(this.element, n) ? t(this.helper).animate(this.originalPosition, parseInt(this.options.revertDuration, 10),
            function() {
                i._trigger("stop", e) !== !1 && i._clear()
            }) : this._trigger("stop", e) !== !1 && this._clear(),
            !1
        },
        _mouseUp: function(e) {
            return this._unblockFrames(),
            t.ui.ddmanager && t.ui.ddmanager.dragStop(this, e),
            this.handleElement.is(e.target) && this.element.focus(),
            t.ui.mouse.prototype._mouseUp.call(this, e)
        },
        cancel: function() {
            return this.helper.is(".ui-draggable-dragging") ? this._mouseUp({}) : this._clear(),
            this
        },
        _getHandle: function(e) {
            return this.options.handle ? !!t(e.target).closest(this.element.find(this.options.handle)).length: !0
        },
        _setHandleClassName: function() {
            this.handleElement = this.options.handle ? this.element.find(this.options.handle) : this.element,
            this.handleElement.addClass("ui-draggable-handle")
        },
        _removeHandleClassName: function() {
            this.handleElement.removeClass("ui-draggable-handle")
        },
        _createHelper: function(e) {
            var i = this.options,
            n = t.isFunction(i.helper),
            s = n ? t(i.helper.apply(this.element[0], [e])) : "clone" === i.helper ? this.element.clone().removeAttr("id") : this.element;
            return s.parents("body").length || s.appendTo("parent" === i.appendTo ? this.element[0].parentNode: i.appendTo),
            n && s[0] === this.element[0] && this._setPositionRelative(),
            s[0] === this.element[0] || /(fixed|absolute)/.test(s.css("position")) || s.css("position", "absolute"),
            s
        },
        _setPositionRelative: function() { / ^( ? :r | a | f) / .test(this.element.css("position")) || (this.element[0].style.position = "relative")
        },
        _adjustOffsetFromHelper: function(e) {
            "string" == typeof e && (e = e.split(" ")),
            t.isArray(e) && (e = {
                left: +e[0],
                top: +e[1] || 0
            }),
            "left" in e && (this.offset.click.left = e.left + this.margins.left),
            "right" in e && (this.offset.click.left = this.helperProportions.width - e.right + this.margins.left),
            "top" in e && (this.offset.click.top = e.top + this.margins.top),
            "bottom" in e && (this.offset.click.top = this.helperProportions.height - e.bottom + this.margins.top)
        },
        _isRootNode: function(t) {
            return /(html|body)/i.test(t.tagName) || t === this.document[0]
        },
        _getParentOffset: function() {
            var e = this.offsetParent.offset(),
            i = this.document[0];
            return "absolute" === this.cssPosition && this.scrollParent[0] !== i && t.contains(this.scrollParent[0], this.offsetParent[0]) && (e.left += this.scrollParent.scrollLeft(), e.top += this.scrollParent.scrollTop()),
            this._isRootNode(this.offsetParent[0]) && (e = {
                top: 0,
                left: 0
            }),
            {
                top: e.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
                left: e.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
            }
        },
        _getRelativeOffset: function() {
            if ("relative" !== this.cssPosition) return {
                top: 0,
                left: 0
            };
            var t = this.element.position(),
            e = this._isRootNode(this.scrollParent[0]);
            return {
                top: t.top - (parseInt(this.helper.css("top"), 10) || 0) + (e ? 0 : this.scrollParent.scrollTop()),
                left: t.left - (parseInt(this.helper.css("left"), 10) || 0) + (e ? 0 : this.scrollParent.scrollLeft())
            }
        },
        _cacheMargins: function() {
            this.margins = {
                left: parseInt(this.element.css("marginLeft"), 10) || 0,
                top: parseInt(this.element.css("marginTop"), 10) || 0,
                right: parseInt(this.element.css("marginRight"), 10) || 0,
                bottom: parseInt(this.element.css("marginBottom"), 10) || 0
            }
        },
        _cacheHelperProportions: function() {
            this.helperProportions = {
                width: this.helper.outerWidth(),
                height: this.helper.outerHeight()
            }
        },
        _setContainment: function() {
            var e, i, n, s = this.options,
            o = this.document[0];
            return this.relativeContainer = null,
            s.containment ? "window" === s.containment ? void(this.containment = [t(window).scrollLeft() - this.offset.relative.left - this.offset.parent.left, t(window).scrollTop() - this.offset.relative.top - this.offset.parent.top, t(window).scrollLeft() + t(window).width() - this.helperProportions.width - this.margins.left, t(window).scrollTop() + (t(window).height() || o.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top]) : "document" === s.containment ? void(this.containment = [0, 0, t(o).width() - this.helperProportions.width - this.margins.left, (t(o).height() || o.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top]) : s.containment.constructor === Array ? void(this.containment = s.containment) : ("parent" === s.containment && (s.containment = this.helper[0].parentNode), i = t(s.containment), n = i[0], void(n && (e = /(scroll|auto)/.test(i.css("overflow")), this.containment = [(parseInt(i.css("borderLeftWidth"), 10) || 0) + (parseInt(i.css("paddingLeft"), 10) || 0), (parseInt(i.css("borderTopWidth"), 10) || 0) + (parseInt(i.css("paddingTop"), 10) || 0), (e ? Math.max(n.scrollWidth, n.offsetWidth) : n.offsetWidth) - (parseInt(i.css("borderRightWidth"), 10) || 0) - (parseInt(i.css("paddingRight"), 10) || 0) - this.helperProportions.width - this.margins.left - this.margins.right, (e ? Math.max(n.scrollHeight, n.offsetHeight) : n.offsetHeight) - (parseInt(i.css("borderBottomWidth"), 10) || 0) - (parseInt(i.css("paddingBottom"), 10) || 0) - this.helperProportions.height - this.margins.top - this.margins.bottom], this.relativeContainer = i))) : void(this.containment = null)
        },
        _convertPositionTo: function(t, e) {
            e || (e = this.position);
            var i = "absolute" === t ? 1 : -1,
            n = this._isRootNode(this.scrollParent[0]);
            return {
                top: e.top + this.offset.relative.top * i + this.offset.parent.top * i - ("fixed" === this.cssPosition ? -this.offset.scroll.top: n ? 0 : this.offset.scroll.top) * i,
                left: e.left + this.offset.relative.left * i + this.offset.parent.left * i - ("fixed" === this.cssPosition ? -this.offset.scroll.left: n ? 0 : this.offset.scroll.left) * i
            }
        },
        _generatePosition: function(t, e) {
            var i, n, s, o, r = this.options,
            a = this._isRootNode(this.scrollParent[0]),
            l = t.pageX,
            c = t.pageY;
            return a && this.offset.scroll || (this.offset.scroll = {
                top: this.scrollParent.scrollTop(),
                left: this.scrollParent.scrollLeft()
            }),
            e && (this.containment && (this.relativeContainer ? (n = this.relativeContainer.offset(), i = [this.containment[0] + n.left, this.containment[1] + n.top, this.containment[2] + n.left, this.containment[3] + n.top]) : i = this.containment, t.pageX - this.offset.click.left < i[0] && (l = i[0] + this.offset.click.left), t.pageY - this.offset.click.top < i[1] && (c = i[1] + this.offset.click.top), t.pageX - this.offset.click.left > i[2] && (l = i[2] + this.offset.click.left), t.pageY - this.offset.click.top > i[3] && (c = i[3] + this.offset.click.top)), r.grid && (s = r.grid[1] ? this.originalPageY + Math.round((c - this.originalPageY) / r.grid[1]) * r.grid[1] : this.originalPageY, c = i ? s - this.offset.click.top >= i[1] || s - this.offset.click.top > i[3] ? s: s - this.offset.click.top >= i[1] ? s - r.grid[1] : s + r.grid[1] : s, o = r.grid[0] ? this.originalPageX + Math.round((l - this.originalPageX) / r.grid[0]) * r.grid[0] : this.originalPageX, l = i ? o - this.offset.click.left >= i[0] || o - this.offset.click.left > i[2] ? o: o - this.offset.click.left >= i[0] ? o - r.grid[0] : o + r.grid[0] : o), "y" === r.axis && (l = this.originalPageX), "x" === r.axis && (c = this.originalPageY)),
            {
                top: c - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + ("fixed" === this.cssPosition ? -this.offset.scroll.top: a ? 0 : this.offset.scroll.top),
                left: l - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + ("fixed" === this.cssPosition ? -this.offset.scroll.left: a ? 0 : this.offset.scroll.left)
            }
        },
        _clear: function() {
            this.helper.removeClass("ui-draggable-dragging"),
            this.helper[0] === this.element[0] || this.cancelHelperRemoval || this.helper.remove(),
            this.helper = null,
            this.cancelHelperRemoval = !1,
            this.destroyOnClear && this.destroy()
        },
        _normalizeRightBottom: function() {
            "y" !== this.options.axis && "auto" !== this.helper.css("right") && (this.helper.width(this.helper.width()), this.helper.css("right", "auto")),
            "x" !== this.options.axis && "auto" !== this.helper.css("bottom") && (this.helper.height(this.helper.height()), this.helper.css("bottom", "auto"))
        },
        _trigger: function(e, i, n) {
            return n = n || this._uiHash(),
            t.ui.plugin.call(this, e, [i, n, this], !0),
            /^(drag|start|stop)/.test(e) && (this.positionAbs = this._convertPositionTo("absolute"), n.offset = this.positionAbs),
            t.Widget.prototype._trigger.call(this, e, i, n)
        },
        plugins: {},
        _uiHash: function() {
            return {
                helper: this.helper,
                position: this.position,
                originalPosition: this.originalPosition,
                offset: this.positionAbs
            }
        }
    }),
    t.ui.plugin.add("draggable", "connectToSortable", {
        start: function(e, i, n) {
            var s = t.extend({},
            i, {
                item: n.element
            });
            n.sortables = [],
            t(n.options.connectToSortable).each(function() {
                var i = t(this).sortable("instance");
                i && !i.options.disabled && (n.sortables.push(i), i.refreshPositions(), i._trigger("activate", e, s))
            })
        },
        stop: function(e, i, n) {
            var s = t.extend({},
            i, {
                item: n.element
            });
            n.cancelHelperRemoval = !1,
            t.each(n.sortables,
            function() {
                var t = this;
                t.isOver ? (t.isOver = 0, n.cancelHelperRemoval = !0, t.cancelHelperRemoval = !1, t._storedCSS = {
                    position: t.placeholder.css("position"),
                    top: t.placeholder.css("top"),
                    left: t.placeholder.css("left")
                },
                t._mouseStop(e), t.options.helper = t.options._helper) : (t.cancelHelperRemoval = !0, t._trigger("deactivate", e, s))
            })
        },
        drag: function(e, i, n) {
            t.each(n.sortables,
            function() {
                var s = !1,
                o = this;
                o.positionAbs = n.positionAbs,
                o.helperProportions = n.helperProportions,
                o.offset.click = n.offset.click,
                o._intersectsWith(o.containerCache) && (s = !0, t.each(n.sortables,
                function() {
                    return this.positionAbs = n.positionAbs,
                    this.helperProportions = n.helperProportions,
                    this.offset.click = n.offset.click,
                    this !== o && this._intersectsWith(this.containerCache) && t.contains(o.element[0], this.element[0]) && (s = !1),
                    s
                })),
                s ? (o.isOver || (o.isOver = 1, n._parent = i.helper.parent(), o.currentItem = i.helper.appendTo(o.element).data("ui-sortable-item", !0), o.options._helper = o.options.helper, o.options.helper = function() {
                    return i.helper[0]
                },
                e.target = o.currentItem[0], o._mouseCapture(e, !0), o._mouseStart(e, !0, !0), o.offset.click.top = n.offset.click.top, o.offset.click.left = n.offset.click.left, o.offset.parent.left -= n.offset.parent.left - o.offset.parent.left, o.offset.parent.top -= n.offset.parent.top - o.offset.parent.top, n._trigger("toSortable", e), n.dropped = o.element, t.each(n.sortables,
                function() {
                    this.refreshPositions()
                }), n.currentItem = n.element, o.fromOutside = n), o.currentItem && (o._mouseDrag(e), i.position = o.position)) : o.isOver && (o.isOver = 0, o.cancelHelperRemoval = !0, o.options._revert = o.options.revert, o.options.revert = !1, o._trigger("out", e, o._uiHash(o)), o._mouseStop(e, !0), o.options.revert = o.options._revert, o.options.helper = o.options._helper, o.placeholder && o.placeholder.remove(), i.helper.appendTo(n._parent), n._refreshOffsets(e), i.position = n._generatePosition(e, !0), n._trigger("fromSortable", e), n.dropped = !1, t.each(n.sortables,
                function() {
                    this.refreshPositions()
                }))
            })
        }
    }),
    t.ui.plugin.add("draggable", "cursor", {
        start: function(e, i, n) {
            var s = t("body"),
            o = n.options;
            s.css("cursor") && (o._cursor = s.css("cursor")),
            s.css("cursor", o.cursor)
        },
        stop: function(e, i, n) {
            var s = n.options;
            s._cursor && t("body").css("cursor", s._cursor)
        }
    }),
    t.ui.plugin.add("draggable", "opacity", {
        start: function(e, i, n) {
            var s = t(i.helper),
            o = n.options;
            s.css("opacity") && (o._opacity = s.css("opacity")),
            s.css("opacity", o.opacity)
        },
        stop: function(e, i, n) {
            var s = n.options;
            s._opacity && t(i.helper).css("opacity", s._opacity)
        }
    }),
    t.ui.plugin.add("draggable", "scroll", {
        start: function(t, e, i) {
            i.scrollParentNotHidden || (i.scrollParentNotHidden = i.helper.scrollParent(!1)),
            i.scrollParentNotHidden[0] !== i.document[0] && "HTML" !== i.scrollParentNotHidden[0].tagName && (i.overflowOffset = i.scrollParentNotHidden.offset())
        },
        drag: function(e, i, n) {
            var s = n.options,
            o = !1,
            r = n.scrollParentNotHidden[0],
            a = n.document[0];
            r !== a && "HTML" !== r.tagName ? (s.axis && "x" === s.axis || (n.overflowOffset.top + r.offsetHeight - e.pageY < s.scrollSensitivity ? r.scrollTop = o = r.scrollTop + s.scrollSpeed: e.pageY - n.overflowOffset.top < s.scrollSensitivity && (r.scrollTop = o = r.scrollTop - s.scrollSpeed)), s.axis && "y" === s.axis || (n.overflowOffset.left + r.offsetWidth - e.pageX < s.scrollSensitivity ? r.scrollLeft = o = r.scrollLeft + s.scrollSpeed: e.pageX - n.overflowOffset.left < s.scrollSensitivity && (r.scrollLeft = o = r.scrollLeft - s.scrollSpeed))) : (s.axis && "x" === s.axis || (e.pageY - t(a).scrollTop() < s.scrollSensitivity ? o = t(a).scrollTop(t(a).scrollTop() - s.scrollSpeed) : t(window).height() - (e.pageY - t(a).scrollTop()) < s.scrollSensitivity && (o = t(a).scrollTop(t(a).scrollTop() + s.scrollSpeed))), s.axis && "y" === s.axis || (e.pageX - t(a).scrollLeft() < s.scrollSensitivity ? o = t(a).scrollLeft(t(a).scrollLeft() - s.scrollSpeed) : t(window).width() - (e.pageX - t(a).scrollLeft()) < s.scrollSensitivity && (o = t(a).scrollLeft(t(a).scrollLeft() + s.scrollSpeed)))),
            o !== !1 && t.ui.ddmanager && !s.dropBehaviour && t.ui.ddmanager.prepareOffsets(n, e)
        }
    }),
    t.ui.plugin.add("draggable", "snap", {
        start: function(e, i, n) {
            var s = n.options;
            n.snapElements = [],
            t(s.snap.constructor !== String ? s.snap.items || ":data(ui-draggable)": s.snap).each(function() {
                var e = t(this),
                i = e.offset();
                this !== n.element[0] && n.snapElements.push({
                    item: this,
                    width: e.outerWidth(),
                    height: e.outerHeight(),
                    top: i.top,
                    left: i.left
                })
            })
        },
        drag: function(e, i, n) {
            var s, o, r, a, l, c, h, u, d, p, f = n.options,
            m = f.snapTolerance,
            g = i.offset.left,
            v = g + n.helperProportions.width,
            y = i.offset.top,
            b = y + n.helperProportions.height;
            for (d = n.snapElements.length - 1; d >= 0; d--) l = n.snapElements[d].left - n.margins.left,
            c = l + n.snapElements[d].width,
            h = n.snapElements[d].top - n.margins.top,
            u = h + n.snapElements[d].height,
            l - m > v || g > c + m || h - m > b || y > u + m || !t.contains(n.snapElements[d].item.ownerDocument, n.snapElements[d].item) ? (n.snapElements[d].snapping && n.options.snap.release && n.options.snap.release.call(n.element, e, t.extend(n._uiHash(), {
                snapItem: n.snapElements[d].item
            })), n.snapElements[d].snapping = !1) : ("inner" !== f.snapMode && (s = Math.abs(h - b) <= m, o = Math.abs(u - y) <= m, r = Math.abs(l - v) <= m, a = Math.abs(c - g) <= m, s && (i.position.top = n._convertPositionTo("relative", {
                top: h - n.helperProportions.height,
                left: 0
            }).top), o && (i.position.top = n._convertPositionTo("relative", {
                top: u,
                left: 0
            }).top), r && (i.position.left = n._convertPositionTo("relative", {
                top: 0,
                left: l - n.helperProportions.width
            }).left), a && (i.position.left = n._convertPositionTo("relative", {
                top: 0,
                left: c
            }).left)), p = s || o || r || a, "outer" !== f.snapMode && (s = Math.abs(h - y) <= m, o = Math.abs(u - b) <= m, r = Math.abs(l - g) <= m, a = Math.abs(c - v) <= m, s && (i.position.top = n._convertPositionTo("relative", {
                top: h,
                left: 0
            }).top), o && (i.position.top = n._convertPositionTo("relative", {
                top: u - n.helperProportions.height,
                left: 0
            }).top), r && (i.position.left = n._convertPositionTo("relative", {
                top: 0,
                left: l
            }).left), a && (i.position.left = n._convertPositionTo("relative", {
                top: 0,
                left: c - n.helperProportions.width
            }).left)), !n.snapElements[d].snapping && (s || o || r || a || p) && n.options.snap.snap && n.options.snap.snap.call(n.element, e, t.extend(n._uiHash(), {
                snapItem: n.snapElements[d].item
            })), n.snapElements[d].snapping = s || o || r || a || p)
        }
    }),
    t.ui.plugin.add("draggable", "stack", {
        start: function(e, i, n) {
            var s, o = n.options,
            r = t.makeArray(t(o.stack)).sort(function(e, i) {
                return (parseInt(t(e).css("zIndex"), 10) || 0) - (parseInt(t(i).css("zIndex"), 10) || 0)
            });
            r.length && (s = parseInt(t(r[0]).css("zIndex"), 10) || 0, t(r).each(function(e) {
                t(this).css("zIndex", s + e)
            }), this.css("zIndex", s + r.length))
        }
    }),
    t.ui.plugin.add("draggable", "zIndex", {
        start: function(e, i, n) {
            var s = t(i.helper),
            o = n.options;
            s.css("zIndex") && (o._zIndex = s.css("zIndex")),
            s.css("zIndex", o.zIndex)
        },
        stop: function(e, i, n) {
            var s = n.options;
            s._zIndex && t(i.helper).css("zIndex", s._zIndex)
        }
    });
    t.ui.draggable;
    t.widget("ui.resizable", t.ui.mouse, {
        version: "1.11.4",
        widgetEventPrefix: "resize",
        options: {
            alsoResize: !1,
            animate: !1,
            animateDuration: "slow",
            animateEasing: "swing",
            aspectRatio: !1,
            autoHide: !1,
            containment: !1,
            ghost: !1,
            grid: !1,
            handles: "e,s,se",
            helper: !1,
            maxHeight: null,
            maxWidth: null,
            minHeight: 10,
            minWidth: 10,
            zIndex: 90,
            resize: null,
            start: null,
            stop: null
        },
        _num: function(t) {
            return parseInt(t, 10) || 0
        },
        _isNumber: function(t) {
            return ! isNaN(parseInt(t, 10))
        },
        _hasScroll: function(e, i) {
            if ("hidden" === t(e).css("overflow")) return ! 1;
            var n = i && "left" === i ? "scrollLeft": "scrollTop",
            s = !1;
            return e[n] > 0 ? !0 : (e[n] = 1, s = e[n] > 0, e[n] = 0, s)
        },
        _create: function() {
            var e, i, n, s, o, r = this,
            a = this.options;
            if (this.element.addClass("ui-resizable"), t.extend(this, {
                _aspectRatio: !!a.aspectRatio,
                aspectRatio: a.aspectRatio,
                originalElement: this.element,
                _proportionallyResizeElements: [],
                _helper: a.helper || a.ghost || a.animate ? a.helper || "ui-resizable-helper": null
            }), this.element[0].nodeName.match(/^(canvas|textarea|input|select|button|img)$/i) && (this.element.wrap(t("<div class='ui-wrapper' style='overflow: hidden;'></div>").css({
                position: this.element.css("position"),
                width: this.element.outerWidth(),
                height: this.element.outerHeight(),
                top: this.element.css("top"),
                left: this.element.css("left")
            })), this.element = this.element.parent().data("ui-resizable", this.element.resizable("instance")), this.elementIsWrapper = !0, this.element.css({
                marginLeft: this.originalElement.css("marginLeft"),
                marginTop: this.originalElement.css("marginTop"),
                marginRight: this.originalElement.css("marginRight"),
                marginBottom: this.originalElement.css("marginBottom")
            }), this.originalElement.css({
                marginLeft: 0,
                marginTop: 0,
                marginRight: 0,
                marginBottom: 0
            }), this.originalResizeStyle = this.originalElement.css("resize"), this.originalElement.css("resize", "none"), this._proportionallyResizeElements.push(this.originalElement.css({
                position: "static",
                zoom: 1,
                display: "block"
            })), this.originalElement.css({
                margin: this.originalElement.css("margin")
            }), this._proportionallyResize()), this.handles = a.handles || (t(".ui-resizable-handle", this.element).length ? {
                n: ".ui-resizable-n",
                e: ".ui-resizable-e",
                s: ".ui-resizable-s",
                w: ".ui-resizable-w",
                se: ".ui-resizable-se",
                sw: ".ui-resizable-sw",
                ne: ".ui-resizable-ne",
                nw: ".ui-resizable-nw"
            }: "e,s,se"), this._handles = t(), this.handles.constructor === String) for ("all" === this.handles && (this.handles = "n,e,s,w,se,sw,ne,nw"), e = this.handles.split(","), this.handles = {},
            i = 0; i < e.length; i++) n = t.trim(e[i]),
            o = "ui-resizable-" + n,
            s = t("<div class='ui-resizable-handle " + o + "'></div>"),
            s.css({
                zIndex: a.zIndex
            }),
            "se" === n && s.addClass("ui-icon ui-icon-gripsmall-diagonal-se"),
            this.handles[n] = ".ui-resizable-" + n,
            this.element.append(s);
            this._renderAxis = function(e) {
                var i, n, s, o;
                e = e || this.element;
                for (i in this.handles) this.handles[i].constructor === String ? this.handles[i] = this.element.children(this.handles[i]).first().show() : (this.handles[i].jquery || this.handles[i].nodeType) && (this.handles[i] = t(this.handles[i]), this._on(this.handles[i], {
                    mousedown: r._mouseDown
                })),
                this.elementIsWrapper && this.originalElement[0].nodeName.match(/^(textarea|input|select|button)$/i) && (n = t(this.handles[i], this.element), o = /sw|ne|nw|se|n|s/.test(i) ? n.outerHeight() : n.outerWidth(), s = ["padding", /ne|nw|n/.test(i) ? "Top": /se|sw|s/.test(i) ? "Bottom": /^e$/.test(i) ? "Right": "Left"].join(""), e.css(s, o), this._proportionallyResize()),
                this._handles = this._handles.add(this.handles[i])
            },
            this._renderAxis(this.element),
            this._handles = this._handles.add(this.element.find(".ui-resizable-handle")),
            this._handles.disableSelection(),
            this._handles.mouseover(function() {
                r.resizing || (this.className && (s = this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i)), r.axis = s && s[1] ? s[1] : "se")
            }),
            a.autoHide && (this._handles.hide(), t(this.element).addClass("ui-resizable-autohide").mouseenter(function() {
                a.disabled || (t(this).removeClass("ui-resizable-autohide"), r._handles.show())
            }).mouseleave(function() {
                a.disabled || r.resizing || (t(this).addClass("ui-resizable-autohide"), r._handles.hide())
            })),
            this._mouseInit()
        },
        _destroy: function() {
            this._mouseDestroy();
            var e, i = function(e) {
                t(e).removeClass("ui-resizable ui-resizable-disabled ui-resizable-resizing").removeData("resizable").removeData("ui-resizable").unbind(".resizable").find(".ui-resizable-handle").remove()
            };
            return this.elementIsWrapper && (i(this.element), e = this.element, this.originalElement.css({
                position: e.css("position"),
                width: e.outerWidth(),
                height: e.outerHeight(),
                top: e.css("top"),
                left: e.css("left")
            }).insertAfter(e), e.remove()),
            this.originalElement.css("resize", this.originalResizeStyle),
            i(this.originalElement),
            this
        },
        _mouseCapture: function(e) {
            var i, n, s = !1;
            for (i in this.handles) n = t(this.handles[i])[0],
            (n === e.target || t.contains(n, e.target)) && (s = !0);
            return ! this.options.disabled && s
        },
        _mouseStart: function(e) {
            var i, n, s, o = this.options,
            r = this.element;
            return this.resizing = !0,
            this._renderProxy(),
            i = this._num(this.helper.css("left")),
            n = this._num(this.helper.css("top")),
            o.containment && (i += t(o.containment).scrollLeft() || 0, n += t(o.containment).scrollTop() || 0),
            this.offset = this.helper.offset(),
            this.position = {
                left: i,
                top: n
            },
            this.size = this._helper ? {
                width: this.helper.width(),
                height: this.helper.height()
            }: {
                width: r.width(),
                height: r.height()
            },
            this.originalSize = this._helper ? {
                width: r.outerWidth(),
                height: r.outerHeight()
            }: {
                width: r.width(),
                height: r.height()
            },
            this.sizeDiff = {
                width: r.outerWidth() - r.width(),
                height: r.outerHeight() - r.height()
            },
            this.originalPosition = {
                left: i,
                top: n
            },
            this.originalMousePosition = {
                left: e.pageX,
                top: e.pageY
            },
            this.aspectRatio = "number" == typeof o.aspectRatio ? o.aspectRatio: this.originalSize.width / this.originalSize.height || 1,
            s = t(".ui-resizable-" + this.axis).css("cursor"),
            t("body").css("cursor", "auto" === s ? this.axis + "-resize": s),
            r.addClass("ui-resizable-resizing"),
            this._propagate("start", e),
            !0
        },
        _mouseDrag: function(e) {
            var i, n, s = this.originalMousePosition,
            o = this.axis,
            r = e.pageX - s.left || 0,
            a = e.pageY - s.top || 0,
            l = this._change[o];
            return this._updatePrevProperties(),
            l ? (i = l.apply(this, [e, r, a]), this._updateVirtualBoundaries(e.shiftKey), (this._aspectRatio || e.shiftKey) && (i = this._updateRatio(i, e)), i = this._respectSize(i, e), this._updateCache(i), this._propagate("resize", e), n = this._applyChanges(), !this._helper && this._proportionallyResizeElements.length && this._proportionallyResize(), t.isEmptyObject(n) || (this._updatePrevProperties(), this._trigger("resize", e, this.ui()), this._applyChanges()), !1) : !1
        },
        _mouseStop: function(e) {
            this.resizing = !1;
            var i, n, s, o, r, a, l, c = this.options,
            h = this;
            return this._helper && (i = this._proportionallyResizeElements, n = i.length && /textarea/i.test(i[0].nodeName), s = n && this._hasScroll(i[0], "left") ? 0 : h.sizeDiff.height, o = n ? 0 : h.sizeDiff.width, r = {
                width: h.helper.width() - o,
                height: h.helper.height() - s
            },
            a = parseInt(h.element.css("left"), 10) + (h.position.left - h.originalPosition.left) || null, l = parseInt(h.element.css("top"), 10) + (h.position.top - h.originalPosition.top) || null, c.animate || this.element.css(t.extend(r, {
                top: l,
                left: a
            })), h.helper.height(h.size.height), h.helper.width(h.size.width), this._helper && !c.animate && this._proportionallyResize()),
            t("body").css("cursor", "auto"),
            this.element.removeClass("ui-resizable-resizing"),
            this._propagate("stop", e),
            this._helper && this.helper.remove(),
            !1
        },
        _updatePrevProperties: function() {
            this.prevPosition = {
                top: this.position.top,
                left: this.position.left
            },
            this.prevSize = {
                width: this.size.width,
                height: this.size.height
            }
        },
        _applyChanges: function() {
            var t = {};
            return this.position.top !== this.prevPosition.top && (t.top = this.position.top + "px"),
            this.position.left !== this.prevPosition.left && (t.left = this.position.left + "px"),
            this.size.width !== this.prevSize.width && (t.width = this.size.width + "px"),
            this.size.height !== this.prevSize.height && (t.height = this.size.height + "px"),
            this.helper.css(t),
            t
        },
        _updateVirtualBoundaries: function(t) {
            var e, i, n, s, o, r = this.options;
            o = {
                minWidth: this._isNumber(r.minWidth) ? r.minWidth: 0,
                maxWidth: this._isNumber(r.maxWidth) ? r.maxWidth: 1 / 0,
                minHeight: this._isNumber(r.minHeight) ? r.minHeight: 0,
                maxHeight: this._isNumber(r.maxHeight) ? r.maxHeight: 1 / 0
            },
            (this._aspectRatio || t) && (e = o.minHeight * this.aspectRatio, n = o.minWidth / this.aspectRatio, i = o.maxHeight * this.aspectRatio, s = o.maxWidth / this.aspectRatio, e > o.minWidth && (o.minWidth = e), n > o.minHeight && (o.minHeight = n), i < o.maxWidth && (o.maxWidth = i), s < o.maxHeight && (o.maxHeight = s)),
            this._vBoundaries = o
        },
        _updateCache: function(t) {
            this.offset = this.helper.offset(),
            this._isNumber(t.left) && (this.position.left = t.left),
            this._isNumber(t.top) && (this.position.top = t.top),
            this._isNumber(t.height) && (this.size.height = t.height),
            this._isNumber(t.width) && (this.size.width = t.width)
        },
        _updateRatio: function(t) {
            var e = this.position,
            i = this.size,
            n = this.axis;
            return this._isNumber(t.height) ? t.width = t.height * this.aspectRatio: this._isNumber(t.width) && (t.height = t.width / this.aspectRatio),
            "sw" === n && (t.left = e.left + (i.width - t.width), t.top = null),
            "nw" === n && (t.top = e.top + (i.height - t.height), t.left = e.left + (i.width - t.width)),
            t
        },
        _respectSize: function(t) {
            var e = this._vBoundaries,
            i = this.axis,
            n = this._isNumber(t.width) && e.maxWidth && e.maxWidth < t.width,
            s = this._isNumber(t.height) && e.maxHeight && e.maxHeight < t.height,
            o = this._isNumber(t.width) && e.minWidth && e.minWidth > t.width,
            r = this._isNumber(t.height) && e.minHeight && e.minHeight > t.height,
            a = this.originalPosition.left + this.originalSize.width,
            l = this.position.top + this.size.height,
            c = /sw|nw|w/.test(i),
            h = /nw|ne|n/.test(i);
            return o && (t.width = e.minWidth),
            r && (t.height = e.minHeight),
            n && (t.width = e.maxWidth),
            s && (t.height = e.maxHeight),
            o && c && (t.left = a - e.minWidth),
            n && c && (t.left = a - e.maxWidth),
            r && h && (t.top = l - e.minHeight),
            s && h && (t.top = l - e.maxHeight),
            t.width || t.height || t.left || !t.top ? t.width || t.height || t.top || !t.left || (t.left = null) : t.top = null,
            t
        },
        _getPaddingPlusBorderDimensions: function(t) {
            for (var e = 0,
            i = [], n = [t.css("borderTopWidth"), t.css("borderRightWidth"), t.css("borderBottomWidth"), t.css("borderLeftWidth")], s = [t.css("paddingTop"), t.css("paddingRight"), t.css("paddingBottom"), t.css("paddingLeft")]; 4 > e; e++) i[e] = parseInt(n[e], 10) || 0,
            i[e] += parseInt(s[e], 10) || 0;
            return {
                height: i[0] + i[2],
                width: i[1] + i[3]
            }
        },
        _proportionallyResize: function() {
            if (this._proportionallyResizeElements.length) for (var t, e = 0,
            i = this.helper || this.element; e < this._proportionallyResizeElements.length; e++) t = this._proportionallyResizeElements[e],
            this.outerDimensions || (this.outerDimensions = this._getPaddingPlusBorderDimensions(t)),
            t.css({
                height: i.height() - this.outerDimensions.height || 0,
                width: i.width() - this.outerDimensions.width || 0
            })
        },
        _renderProxy: function() {
            var e = this.element,
            i = this.options;
            this.elementOffset = e.offset(),
            this._helper ? (this.helper = this.helper || t("<div style='overflow:hidden;'></div>"), this.helper.addClass(this._helper).css({
                width: this.element.outerWidth() - 1,
                height: this.element.outerHeight() - 1,
                position: "absolute",
                left: this.elementOffset.left + "px",
                top: this.elementOffset.top + "px",
                zIndex: ++i.zIndex
            }), this.helper.appendTo("body").disableSelection()) : this.helper = this.element
        },
        _change: {
            e: function(t, e) {
                return {
                    width: this.originalSize.width + e
                }
            },
            w: function(t, e) {
                var i = this.originalSize,
                n = this.originalPosition;
                return {
                    left: n.left + e,
                    width: i.width - e
                }
            },
            n: function(t, e, i) {
                var n = this.originalSize,
                s = this.originalPosition;
                return {
                    top: s.top + i,
                    height: n.height - i
                }
            },
            s: function(t, e, i) {
                return {
                    height: this.originalSize.height + i
                }
            },
            se: function(e, i, n) {
                return t.extend(this._change.s.apply(this, arguments), this._change.e.apply(this, [e, i, n]))
            },
            sw: function(e, i, n) {
                return t.extend(this._change.s.apply(this, arguments), this._change.w.apply(this, [e, i, n]))
            },
            ne: function(e, i, n) {
                return t.extend(this._change.n.apply(this, arguments), this._change.e.apply(this, [e, i, n]))
            },
            nw: function(e, i, n) {
                return t.extend(this._change.n.apply(this, arguments), this._change.w.apply(this, [e, i, n]))
            }
        },
        _propagate: function(e, i) {
            t.ui.plugin.call(this, e, [i, this.ui()]),
            "resize" !== e && this._trigger(e, i, this.ui())
        },
        plugins: {},
        ui: function() {
            return {
                originalElement: this.originalElement,
                element: this.element,
                helper: this.helper,
                position: this.position,
                size: this.size,
                originalSize: this.originalSize,
                originalPosition: this.originalPosition
            }
        }
    }),
    t.ui.plugin.add("resizable", "animate", {
        stop: function(e) {
            var i = t(this).resizable("instance"),
            n = i.options,
            s = i._proportionallyResizeElements,
            o = s.length && /textarea/i.test(s[0].nodeName),
            r = o && i._hasScroll(s[0], "left") ? 0 : i.sizeDiff.height,
            a = o ? 0 : i.sizeDiff.width,
            l = {
                width: i.size.width - a,
                height: i.size.height - r
            },
            c = parseInt(i.element.css("left"), 10) + (i.position.left - i.originalPosition.left) || null,
            h = parseInt(i.element.css("top"), 10) + (i.position.top - i.originalPosition.top) || null;
            i.element.animate(t.extend(l, h && c ? {
                top: h,
                left: c
            }: {}), {
                duration: n.animateDuration,
                easing: n.animateEasing,
                step: function() {
                    var n = {
                        width: parseInt(i.element.css("width"), 10),
                        height: parseInt(i.element.css("height"), 10),
                        top: parseInt(i.element.css("top"), 10),
                        left: parseInt(i.element.css("left"), 10)
                    };
                    s && s.length && t(s[0]).css({
                        width: n.width,
                        height: n.height
                    }),
                    i._updateCache(n),
                    i._propagate("resize", e)
                }
            })
        }
    }),
    t.ui.plugin.add("resizable", "containment", {
        start: function() {
            var e, i, n, s, o, r, a, l = t(this).resizable("instance"),
            c = l.options,
            h = l.element,
            u = c.containment,
            d = u instanceof t ? u.get(0) : /parent/.test(u) ? h.parent().get(0) : u;
            d && (l.containerElement = t(d), /document/.test(u) || u === document ? (l.containerOffset = {
                left: 0,
                top: 0
            },
            l.containerPosition = {
                left: 0,
                top: 0
            },
            l.parentData = {
                element: t(document),
                left: 0,
                top: 0,
                width: t(document).width(),
                height: t(document).height() || document.body.parentNode.scrollHeight
            }) : (e = t(d), i = [], t(["Top", "Right", "Left", "Bottom"]).each(function(t, n) {
                i[t] = l._num(e.css("padding" + n))
            }), l.containerOffset = e.offset(), l.containerPosition = e.position(), l.containerSize = {
                height: e.innerHeight() - i[3],
                width: e.innerWidth() - i[1]
            },
            n = l.containerOffset, s = l.containerSize.height, o = l.containerSize.width, r = l._hasScroll(d, "left") ? d.scrollWidth: o, a = l._hasScroll(d) ? d.scrollHeight: s, l.parentData = {
                element: d,
                left: n.left,
                top: n.top,
                width: r,
                height: a
            }))
        },
        resize: function(e) {
            var i, n, s, o, r = t(this).resizable("instance"),
            a = r.options,
            l = r.containerOffset,
            c = r.position,
            h = r._aspectRatio || e.shiftKey,
            u = {
                top: 0,
                left: 0
            },
            d = r.containerElement,
            p = !0;
            d[0] !== document && /static/.test(d.css("position")) && (u = l),
            c.left < (r._helper ? l.left: 0) && (r.size.width = r.size.width + (r._helper ? r.position.left - l.left: r.position.left - u.left), h && (r.size.height = r.size.width / r.aspectRatio, p = !1), r.position.left = a.helper ? l.left: 0),
            c.top < (r._helper ? l.top: 0) && (r.size.height = r.size.height + (r._helper ? r.position.top - l.top: r.position.top), h && (r.size.width = r.size.height * r.aspectRatio, p = !1), r.position.top = r._helper ? l.top: 0),
            s = r.containerElement.get(0) === r.element.parent().get(0),
            o = /relative|absolute/.test(r.containerElement.css("position")),
            s && o ? (r.offset.left = r.parentData.left + r.position.left, r.offset.top = r.parentData.top + r.position.top) : (r.offset.left = r.element.offset().left, r.offset.top = r.element.offset().top),
            i = Math.abs(r.sizeDiff.width + (r._helper ? r.offset.left - u.left: r.offset.left - l.left)),
            n = Math.abs(r.sizeDiff.height + (r._helper ? r.offset.top - u.top: r.offset.top - l.top)),
            i + r.size.width >= r.parentData.width && (r.size.width = r.parentData.width - i, h && (r.size.height = r.size.width / r.aspectRatio, p = !1)),
            n + r.size.height >= r.parentData.height && (r.size.height = r.parentData.height - n, h && (r.size.width = r.size.height * r.aspectRatio, p = !1)),
            p || (r.position.left = r.prevPosition.left, r.position.top = r.prevPosition.top, r.size.width = r.prevSize.width, r.size.height = r.prevSize.height)
        },
        stop: function() {
            var e = t(this).resizable("instance"),
            i = e.options,
            n = e.containerOffset,
            s = e.containerPosition,
            o = e.containerElement,
            r = t(e.helper),
            a = r.offset(),
            l = r.outerWidth() - e.sizeDiff.width,
            c = r.outerHeight() - e.sizeDiff.height;
            e._helper && !i.animate && /relative/.test(o.css("position")) && t(this).css({
                left: a.left - s.left - n.left,
                width: l,
                height: c
            }),
            e._helper && !i.animate && /static/.test(o.css("position")) && t(this).css({
                left: a.left - s.left - n.left,
                width: l,
                height: c
            })
        }
    }),
    t.ui.plugin.add("resizable", "alsoResize", {
        start: function() {
            var e = t(this).resizable("instance"),
            i = e.options;
            t(i.alsoResize).each(function() {
                var e = t(this);
                e.data("ui-resizable-alsoresize", {
                    width: parseInt(e.width(), 10),
                    height: parseInt(e.height(), 10),
                    left: parseInt(e.css("left"), 10),
                    top: parseInt(e.css("top"), 10)
                })
            })
        },
        resize: function(e, i) {
            var n = t(this).resizable("instance"),
            s = n.options,
            o = n.originalSize,
            r = n.originalPosition,
            a = {
                height: n.size.height - o.height || 0,
                width: n.size.width - o.width || 0,
                top: n.position.top - r.top || 0,
                left: n.position.left - r.left || 0
            };
            t(s.alsoResize).each(function() {
                var e = t(this),
                n = t(this).data("ui-resizable-alsoresize"),
                s = {},
                o = e.parents(i.originalElement[0]).length ? ["width", "height"] : ["width", "height", "top", "left"];
                t.each(o,
                function(t, e) {
                    var i = (n[e] || 0) + (a[e] || 0);
                    i && i >= 0 && (s[e] = i || null)
                }),
                e.css(s)
            })
        },
        stop: function() {
            t(this).removeData("resizable-alsoresize")
        }
    }),
    t.ui.plugin.add("resizable", "ghost", {
        start: function() {
            var e = t(this).resizable("instance"),
            i = e.options,
            n = e.size;
            e.ghost = e.originalElement.clone(),
            e.ghost.css({
                opacity: .25,
                display: "block",
                position: "relative",
                height: n.height,
                width: n.width,
                margin: 0,
                left: 0,
                top: 0
            }).addClass("ui-resizable-ghost").addClass("string" == typeof i.ghost ? i.ghost: ""),
            e.ghost.appendTo(e.helper)
        },
        resize: function() {
            var e = t(this).resizable("instance");
            e.ghost && e.ghost.css({
                position: "relative",
                height: e.size.height,
                width: e.size.width
            })
        },
        stop: function() {
            var e = t(this).resizable("instance");
            e.ghost && e.helper && e.helper.get(0).removeChild(e.ghost.get(0))
        }
    }),
    t.ui.plugin.add("resizable", "grid", {
        resize: function() {
            var e, i = t(this).resizable("instance"),
            n = i.options,
            s = i.size,
            o = i.originalSize,
            r = i.originalPosition,
            a = i.axis,
            l = "number" == typeof n.grid ? [n.grid, n.grid] : n.grid,
            c = l[0] || 1,
            h = l[1] || 1,
            u = Math.round((s.width - o.width) / c) * c,
            d = Math.round((s.height - o.height) / h) * h,
            p = o.width + u,
            f = o.height + d,
            m = n.maxWidth && n.maxWidth < p,
            g = n.maxHeight && n.maxHeight < f,
            v = n.minWidth && n.minWidth > p,
            y = n.minHeight && n.minHeight > f;
            n.grid = l,
            v && (p += c),
            y && (f += h),
            m && (p -= c),
            g && (f -= h),
            /^(se|s|e)$/.test(a) ? (i.size.width = p, i.size.height = f) : /^(ne)$/.test(a) ? (i.size.width = p, i.size.height = f, i.position.top = r.top - d) : /^(sw)$/.test(a) ? (i.size.width = p, i.size.height = f, i.position.left = r.left - u) : ((0 >= f - h || 0 >= p - c) && (e = i._getPaddingPlusBorderDimensions(this)), f - h > 0 ? (i.size.height = f, i.position.top = r.top - d) : (f = h - e.height, i.size.height = f, i.position.top = r.top + o.height - f), p - c > 0 ? (i.size.width = p, i.position.left = r.left - u) : (p = c - e.width, i.size.width = p, i.position.left = r.left + o.width - p))
        }
    });
    t.ui.resizable,
    t.widget("ui.dialog", {
        version: "1.11.4",
        options: {
            appendTo: "body",
            autoOpen: !0,
            buttons: [],
            closeOnEscape: !0,
            closeText: "Close",
            dialogClass: "",
            draggable: !0,
            hide: null,
            height: "auto",
            maxHeight: null,
            maxWidth: null,
            minHeight: 150,
            minWidth: 150,
            modal: !1,
            position: {
                my: "center",
                at: "center",
                of: window,
                collision: "fit",
                using: function(e) {
                    var i = t(this).css(e).offset().top;
                    0 > i && t(this).css("top", e.top - i)
                }
            },
            resizable: !0,
            show: null,
            title: null,
            width: 300,
            beforeClose: null,
            close: null,
            drag: null,
            dragStart: null,
            dragStop: null,
            focus: null,
            open: null,
            resize: null,
            resizeStart: null,
            resizeStop: null
        },
        sizeRelatedOptions: {
            buttons: !0,
            height: !0,
            maxHeight: !0,
            maxWidth: !0,
            minHeight: !0,
            minWidth: !0,
            width: !0
        },
        resizableRelatedOptions: {
            maxHeight: !0,
            maxWidth: !0,
            minHeight: !0,
            minWidth: !0
        },
        _create: function() {
            this.originalCss = {
                display: this.element[0].style.display,
                width: this.element[0].style.width,
                minHeight: this.element[0].style.minHeight,
                maxHeight: this.element[0].style.maxHeight,
                height: this.element[0].style.height
            },
            this.originalPosition = {
                parent: this.element.parent(),
                index: this.element.parent().children().index(this.element)
            },
            this.originalTitle = this.element.attr("title"),
            this.options.title = this.options.title || this.originalTitle,
            this._createWrapper(),
            this.element.show().removeAttr("title").addClass("ui-dialog-content ui-widget-content").appendTo(this.uiDialog),
            this._createTitlebar(),
            this._createButtonPane(),
            this.options.draggable && t.fn.draggable && this._makeDraggable(),
            this.options.resizable && t.fn.resizable && this._makeResizable(),
            this._isOpen = !1,
            this._trackFocus()
        },
        _init: function() {
            this.options.autoOpen && this.open()
        },
        _appendTo: function() {
            var e = this.options.appendTo;
            return e && (e.jquery || e.nodeType) ? t(e) : this.document.find(e || "body").eq(0)
        },
        _destroy: function() {
            var t, e = this.originalPosition;
            this._untrackInstance(),
            this._destroyOverlay(),
            this.element.removeUniqueId().removeClass("ui-dialog-content ui-widget-content").css(this.originalCss).detach(),
            this.uiDialog.stop(!0, !0).remove(),
            this.originalTitle && this.element.attr("title", this.originalTitle),
            t = e.parent.children().eq(e.index),
            t.length && t[0] !== this.element[0] ? t.before(this.element) : e.parent.append(this.element)
        },
        widget: function() {
            return this.uiDialog
        },
        disable: t.noop,
        enable: t.noop,
        close: function(e) {
            var i, n = this;
            if (this._isOpen && this._trigger("beforeClose", e) !== !1) {
                if (this._isOpen = !1, this._focusedElement = null, this._destroyOverlay(), this._untrackInstance(), !this.opener.filter(":focusable").focus().length) try {
                    i = this.document[0].activeElement,
                    i && "body" !== i.nodeName.toLowerCase() && t(i).blur()
                } catch(s) {}
                this._hide(this.uiDialog, this.options.hide,
                function() {
                    n._trigger("close", e)
                })
            }
        },
        isOpen: function() {
            return this._isOpen
        },
        moveToTop: function() {
            this._moveToTop()
        },
        _moveToTop: function(e, i) {
            var n = !1,
            s = this.uiDialog.siblings(".ui-front:visible").map(function() {
                return + t(this).css("z-index")
            }).get(),
            o = Math.max.apply(null, s);
            return o >= +this.uiDialog.css("z-index") && (this.uiDialog.css("z-index", o + 1), n = !0),
            n && !i && this._trigger("focus", e),
            n
        },
        open: function() {
            var e = this;
            return this._isOpen ? void(this._moveToTop() && this._focusTabbable()) : (this._isOpen = !0, this.opener = t(this.document[0].activeElement), this._size(), this._position(), this._createOverlay(), this._moveToTop(null, !0), this.overlay && this.overlay.css("z-index", this.uiDialog.css("z-index") - 1), this._show(this.uiDialog, this.options.show,
            function() {
                e._focusTabbable(),
                e._trigger("focus")
            }), this._makeFocusTarget(), void this._trigger("open"))
        },
        _focusTabbable: function() {
            var t = this._focusedElement;
            t || (t = this.element.find("[autofocus]")),
            t.length || (t = this.element.find(":tabbable")),
            t.length || (t = this.uiDialogButtonPane.find(":tabbable")),
            t.length || (t = this.uiDialogTitlebarClose.filter(":tabbable")),
            t.length || (t = this.uiDialog),
            t.eq(0).focus()
        },
        _keepFocus: function(e) {
            function i() {
                var e = this.document[0].activeElement,
                i = this.uiDialog[0] === e || t.contains(this.uiDialog[0], e);
                i || this._focusTabbable()
            }
            e.preventDefault(),
            i.call(this),
            this._delay(i)
        },
        _createWrapper: function() {
            this.uiDialog = t("<div>").addClass("ui-dialog ui-widget ui-widget-content ui-corner-all ui-front " + this.options.dialogClass).hide().attr({
                tabIndex: -1,
                role: "dialog"
            }).appendTo(this._appendTo()),
            this._on(this.uiDialog, {
                keydown: function(e) {
                    if (this.options.closeOnEscape && !e.isDefaultPrevented() && e.keyCode && e.keyCode === t.ui.keyCode.ESCAPE) return e.preventDefault(),
                    void this.close(e);
                    if (e.keyCode === t.ui.keyCode.TAB && !e.isDefaultPrevented()) {
                        var i = this.uiDialog.find(":tabbable"),
                        n = i.filter(":first"),
                        s = i.filter(":last");
                        e.target !== s[0] && e.target !== this.uiDialog[0] || e.shiftKey ? e.target !== n[0] && e.target !== this.uiDialog[0] || !e.shiftKey || (this._delay(function() {
                            s.focus()
                        }), e.preventDefault()) : (this._delay(function() {
                            n.focus()
                        }), e.preventDefault())
                    }
                },
                mousedown: function(t) {
                    this._moveToTop(t) && this._focusTabbable()
                }
            }),
            this.element.find("[aria-describedby]").length || this.uiDialog.attr({
                "aria-describedby": this.element.uniqueId().attr("id")
            })
        },
        _createTitlebar: function() {
            var e;
            this.uiDialogTitlebar = t("<div>").addClass("ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix").prependTo(this.uiDialog),
            this._on(this.uiDialogTitlebar, {
                mousedown: function(e) {
                    t(e.target).closest(".ui-dialog-titlebar-close") || this.uiDialog.focus()
                }
            }),
            this.uiDialogTitlebarClose = t("<button type='button'></button>").button({
                label: this.options.closeText,
                icons: {
                    primary: "ui-icon-closethick"
                },
                text: !1
            }).addClass("ui-dialog-titlebar-close").appendTo(this.uiDialogTitlebar),
            this._on(this.uiDialogTitlebarClose, {
                click: function(t) {
                    t.preventDefault(),
                    this.close(t)
                }
            }),
            e = t("<span>").uniqueId().addClass("ui-dialog-title").prependTo(this.uiDialogTitlebar),
            this._title(e),
            this.uiDialog.attr({
                "aria-labelledby": e.attr("id")
            })
        },
        _title: function(t) {
            this.options.title || t.html("&#160;"),
            t.text(this.options.title)
        },
        _createButtonPane: function() {
            this.uiDialogButtonPane = t("<div>").addClass("ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"),
            this.uiButtonSet = t("<div>").addClass("ui-dialog-buttonset").appendTo(this.uiDialogButtonPane),
            this._createButtons()
        },
        _createButtons: function() {
            var e = this,
            i = this.options.buttons;
            return this.uiDialogButtonPane.remove(),
            this.uiButtonSet.empty(),
            t.isEmptyObject(i) || t.isArray(i) && !i.length ? void this.uiDialog.removeClass("ui-dialog-buttons") : (t.each(i,
            function(i, n) {
                var s, o;
                n = t.isFunction(n) ? {
                    click: n,
                    text: i
                }: n,
                n = t.extend({
                    type: "button"
                },
                n),
                s = n.click,
                n.click = function() {
                    s.apply(e.element[0], arguments)
                },
                o = {
                    icons: n.icons,
                    text: n.showText
                },
                delete n.icons,
                delete n.showText,
                t("<button></button>", n).button(o).appendTo(e.uiButtonSet)
            }), this.uiDialog.addClass("ui-dialog-buttons"), void this.uiDialogButtonPane.appendTo(this.uiDialog))
        },
        _makeDraggable: function() {
            function e(t) {
                return {
                    position: t.position,
                    offset: t.offset
                }
            }
            var i = this,
            n = this.options;
            this.uiDialog.draggable({
                cancel: ".ui-dialog-content, .ui-dialog-titlebar-close",
                handle: ".ui-dialog-titlebar",
                containment: "document",
                start: function(n, s) {
                    t(this).addClass("ui-dialog-dragging"),
                    i._blockFrames(),
                    i._trigger("dragStart", n, e(s))
                },
                drag: function(t, n) {
                    i._trigger("drag", t, e(n))
                },
                stop: function(s, o) {
                    var r = o.offset.left - i.document.scrollLeft(),
                    a = o.offset.top - i.document.scrollTop();
                    n.position = {
                        my: "left top",
                        at: "left" + (r >= 0 ? "+": "") + r + " top" + (a >= 0 ? "+": "") + a,
                        of: i.window
                    },
                    t(this).removeClass("ui-dialog-dragging"),
                    i._unblockFrames(),
                    i._trigger("dragStop", s, e(o))
                }
            })
        },
        _makeResizable: function() {
            function e(t) {
                return {
                    originalPosition: t.originalPosition,
                    originalSize: t.originalSize,
                    position: t.position,
                    size: t.size
                }
            }
            var i = this,
            n = this.options,
            s = n.resizable,
            o = this.uiDialog.css("position"),
            r = "string" == typeof s ? s: "n,e,s,w,se,sw,ne,nw";
            this.uiDialog.resizable({
                cancel: ".ui-dialog-content",
                containment: "document",
                alsoResize: this.element,
                maxWidth: n.maxWidth,
                maxHeight: n.maxHeight,
                minWidth: n.minWidth,
                minHeight: this._minHeight(),
                handles: r,
                start: function(n, s) {
                    t(this).addClass("ui-dialog-resizing"),
                    i._blockFrames(),
                    i._trigger("resizeStart", n, e(s))
                },
                resize: function(t, n) {
                    i._trigger("resize", t, e(n))
                },
                stop: function(s, o) {
                    var r = i.uiDialog.offset(),
                    a = r.left - i.document.scrollLeft(),
                    l = r.top - i.document.scrollTop();
                    n.height = i.uiDialog.height(),
                    n.width = i.uiDialog.width(),
                    n.position = {
                        my: "left top",
                        at: "left" + (a >= 0 ? "+": "") + a + " top" + (l >= 0 ? "+": "") + l,
                        of: i.window
                    },
                    t(this).removeClass("ui-dialog-resizing"),
                    i._unblockFrames(),
                    i._trigger("resizeStop", s, e(o))
                }
            }).css("position", o)
        },
        _trackFocus: function() {
            this._on(this.widget(), {
                focusin: function(e) {
                    this._makeFocusTarget(),
                    this._focusedElement = t(e.target)
                }
            })
        },
        _makeFocusTarget: function() {
            this._untrackInstance(),
            this._trackingInstances().unshift(this)
        },
        _untrackInstance: function() {
            var e = this._trackingInstances(),
            i = t.inArray(this, e); - 1 !== i && e.splice(i, 1)
        },
        _trackingInstances: function() {
            var t = this.document.data("ui-dialog-instances");
            return t || (t = [], this.document.data("ui-dialog-instances", t)),
            t
        },
        _minHeight: function() {
            var t = this.options;
            return "auto" === t.height ? t.minHeight: Math.min(t.minHeight, t.height)
        },
        _position: function() {
            var t = this.uiDialog.is(":visible");
            t || this.uiDialog.show(),
            this.uiDialog.position(this.options.position),
            t || this.uiDialog.hide()
        },
        _setOptions: function(e) {
            var i = this,
            n = !1,
            s = {};
            t.each(e,
            function(t, e) {
                i._setOption(t, e),
                t in i.sizeRelatedOptions && (n = !0),
                t in i.resizableRelatedOptions && (s[t] = e)
            }),
            n && (this._size(), this._position()),
            this.uiDialog.is(":data(ui-resizable)") && this.uiDialog.resizable("option", s)
        },
        _setOption: function(t, e) {
            var i, n, s = this.uiDialog;
            "dialogClass" === t && s.removeClass(this.options.dialogClass).addClass(e),
            "disabled" !== t && (this._super(t, e), "appendTo" === t && this.uiDialog.appendTo(this._appendTo()), "buttons" === t && this._createButtons(), "closeText" === t && this.uiDialogTitlebarClose.button({
                label: "" + e
            }), "draggable" === t && (i = s.is(":data(ui-draggable)"), i && !e && s.draggable("destroy"), !i && e && this._makeDraggable()), "position" === t && this._position(), "resizable" === t && (n = s.is(":data(ui-resizable)"), n && !e && s.resizable("destroy"), n && "string" == typeof e && s.resizable("option", "handles", e), n || e === !1 || this._makeResizable()), "title" === t && this._title(this.uiDialogTitlebar.find(".ui-dialog-title")))
        },
        _size: function() {
            var t, e, i, n = this.options;
            this.element.show().css({
                width: "auto",
                minHeight: 0,
                maxHeight: "none",
                height: 0
            }),
            n.minWidth > n.width && (n.width = n.minWidth),
            t = this.uiDialog.css({
                height: "auto",
                width: n.width
            }).outerHeight(),
            e = Math.max(0, n.minHeight - t),
            i = "number" == typeof n.maxHeight ? Math.max(0, n.maxHeight - t) : "none",
            "auto" === n.height ? this.element.css({
                minHeight: e,
                maxHeight: i,
                height: "auto"
            }) : this.element.height(Math.max(0, n.height - t)),
            this.uiDialog.is(":data(ui-resizable)") && this.uiDialog.resizable("option", "minHeight", this._minHeight())
        },
        _blockFrames: function() {
            this.iframeBlocks = this.document.find("iframe").map(function() {
                var e = t(this);
                return t("<div>").css({
                    position: "absolute",
                    width: e.outerWidth(),
                    height: e.outerHeight()
                }).appendTo(e.parent()).offset(e.offset())[0]
            })
        },
        _unblockFrames: function() {
            this.iframeBlocks && (this.iframeBlocks.remove(), delete this.iframeBlocks)
        },
        _allowInteraction: function(e) {
            return t(e.target).closest(".ui-dialog").length ? !0 : !!t(e.target).closest(".ui-datepicker").length
        },
        _createOverlay: function() {
            if (this.options.modal) {
                var e = !0;
                this._delay(function() {
                    e = !1
                }),
                this.document.data("ui-dialog-overlays") || this._on(this.document, {
                    focusin: function(t) {
                        e || this._allowInteraction(t) || (t.preventDefault(), this._trackingInstances()[0]._focusTabbable())
                    }
                }),
                this.overlay = t("<div>").addClass("ui-widget-overlay ui-front").appendTo(this._appendTo()),
                this._on(this.overlay, {
                    mousedown: "_keepFocus"
                }),
                this.document.data("ui-dialog-overlays", (this.document.data("ui-dialog-overlays") || 0) + 1)
            }
        },
        _destroyOverlay: function() {
            if (this.options.modal && this.overlay) {
                var t = this.document.data("ui-dialog-overlays") - 1;
                t ? this.document.data("ui-dialog-overlays", t) : this.document.unbind("focusin").removeData("ui-dialog-overlays"),
                this.overlay.remove(),
                this.overlay = null
            }
        }
    });
    t.widget("ui.droppable", {
        version: "1.11.4",
        widgetEventPrefix: "drop",
        options: {
            accept: "*",
            activeClass: !1,
            addClasses: !0,
            greedy: !1,
            hoverClass: !1,
            scope: "default",
            tolerance: "intersect",
            activate: null,
            deactivate: null,
            drop: null,
            out: null,
            over: null
        },
        _create: function() {
            var e, i = this.options,
            n = i.accept;
            this.isover = !1,
            this.isout = !0,
            this.accept = t.isFunction(n) ? n: function(t) {
                return t.is(n)
            },
            this.proportions = function() {
                return arguments.length ? void(e = arguments[0]) : e ? e: e = {
                    width: this.element[0].offsetWidth,
                    height: this.element[0].offsetHeight
                }
            },
            this._addToManager(i.scope),
            i.addClasses && this.element.addClass("ui-droppable")
        },
        _addToManager: function(e) {
            t.ui.ddmanager.droppables[e] = t.ui.ddmanager.droppables[e] || [],
            t.ui.ddmanager.droppables[e].push(this)
        },
        _splice: function(t) {
            for (var e = 0; e < t.length; e++) t[e] === this && t.splice(e, 1)
        },
        _destroy: function() {
            var e = t.ui.ddmanager.droppables[this.options.scope];
            this._splice(e),
            this.element.removeClass("ui-droppable ui-droppable-disabled")
        },
        _setOption: function(e, i) {
            if ("accept" === e) this.accept = t.isFunction(i) ? i: function(t) {
                return t.is(i)
            };
            else if ("scope" === e) {
                var n = t.ui.ddmanager.droppables[this.options.scope];
                this._splice(n),
                this._addToManager(i)
            }
            this._super(e, i)
        },
        _activate: function(e) {
            var i = t.ui.ddmanager.current;
            this.options.activeClass && this.element.addClass(this.options.activeClass),
            i && this._trigger("activate", e, this.ui(i))
        },
        _deactivate: function(e) {
            var i = t.ui.ddmanager.current;
            this.options.activeClass && this.element.removeClass(this.options.activeClass),
            i && this._trigger("deactivate", e, this.ui(i))
        },
        _over: function(e) {
            var i = t.ui.ddmanager.current;
            i && (i.currentItem || i.element)[0] !== this.element[0] && this.accept.call(this.element[0], i.currentItem || i.element) && (this.options.hoverClass && this.element.addClass(this.options.hoverClass), this._trigger("over", e, this.ui(i)))
        },
        _out: function(e) {
            var i = t.ui.ddmanager.current;
            i && (i.currentItem || i.element)[0] !== this.element[0] && this.accept.call(this.element[0], i.currentItem || i.element) && (this.options.hoverClass && this.element.removeClass(this.options.hoverClass), this._trigger("out", e, this.ui(i)))
        },
        _drop: function(e, i) {
            var n = i || t.ui.ddmanager.current,
            s = !1;
            return n && (n.currentItem || n.element)[0] !== this.element[0] ? (this.element.find(":data(ui-droppable)").not(".ui-draggable-dragging").each(function() {
                var i = t(this).droppable("instance");
                return i.options.greedy && !i.options.disabled && i.options.scope === n.options.scope && i.accept.call(i.element[0], n.currentItem || n.element) && t.ui.intersect(n, t.extend(i, {
                    offset: i.element.offset()
                }), i.options.tolerance, e) ? (s = !0, !1) : void 0
            }), s ? !1 : this.accept.call(this.element[0], n.currentItem || n.element) ? (this.options.activeClass && this.element.removeClass(this.options.activeClass), this.options.hoverClass && this.element.removeClass(this.options.hoverClass), this._trigger("drop", e, this.ui(n)), this.element) : !1) : !1
        },
        ui: function(t) {
            return {
                draggable: t.currentItem || t.element,
                helper: t.helper,
                position: t.position,
                offset: t.positionAbs
            }
        }
    }),
    t.ui.intersect = function() {
        function t(t, e, i) {
            return t >= e && e + i > t
        }
        return function(e, i, n, s) {
            if (!i.offset) return ! 1;
            var o = (e.positionAbs || e.position.absolute).left + e.margins.left,
            r = (e.positionAbs || e.position.absolute).top + e.margins.top,
            a = o + e.helperProportions.width,
            l = r + e.helperProportions.height,
            c = i.offset.left,
            h = i.offset.top,
            u = c + i.proportions().width,
            d = h + i.proportions().height;
            switch (n) {
            case "fit":
                return o >= c && u >= a && r >= h && d >= l;
            case "intersect":
                return c < o + e.helperProportions.width / 2 && a - e.helperProportions.width / 2 < u && h < r + e.helperProportions.height / 2 && l - e.helperProportions.height / 2 < d;
            case "pointer":
                return t(s.pageY, h, i.proportions().height) && t(s.pageX, c, i.proportions().width);
            case "touch":
                return (r >= h && d >= r || l >= h && d >= l || h > r && l > d) && (o >= c && u >= o || a >= c && u >= a || c > o && a > u);
            default:
                return ! 1
            }
        }
    } (),
    t.ui.ddmanager = {
        current: null,
        droppables: {
            "default": []
        },
        prepareOffsets: function(e, i) {
            var n, s, o = t.ui.ddmanager.droppables[e.options.scope] || [],
            r = i ? i.type: null,
            a = (e.currentItem || e.element).find(":data(ui-droppable)").addBack();
            t: for (n = 0; n < o.length; n++) if (! (o[n].options.disabled || e && !o[n].accept.call(o[n].element[0], e.currentItem || e.element))) {
                for (s = 0; s < a.length; s++) if (a[s] === o[n].element[0]) {
                    o[n].proportions().height = 0;
                    continue t
                }
                o[n].visible = "none" !== o[n].element.css("display"),
                o[n].visible && ("mousedown" === r && o[n]._activate.call(o[n], i), o[n].offset = o[n].element.offset(), o[n].proportions({
                    width: o[n].element[0].offsetWidth,
                    height: o[n].element[0].offsetHeight
                }))
            }
        },
        drop: function(e, i) {
            var n = !1;
            return t.each((t.ui.ddmanager.droppables[e.options.scope] || []).slice(),
            function() {
                this.options && (!this.options.disabled && this.visible && t.ui.intersect(e, this, this.options.tolerance, i) && (n = this._drop.call(this, i) || n), !this.options.disabled && this.visible && this.accept.call(this.element[0], e.currentItem || e.element) && (this.isout = !0, this.isover = !1, this._deactivate.call(this, i)))
            }),
            n
        },
        dragStart: function(e, i) {
            e.element.parentsUntil("body").bind("scroll.droppable",
            function() {
                e.options.refreshPositions || t.ui.ddmanager.prepareOffsets(e, i)
            })
        },
        drag: function(e, i) {
            e.options.refreshPositions && t.ui.ddmanager.prepareOffsets(e, i),
            t.each(t.ui.ddmanager.droppables[e.options.scope] || [],
            function() {
                if (!this.options.disabled && !this.greedyChild && this.visible) {
                    var n, s, o, r = t.ui.intersect(e, this, this.options.tolerance, i),
                    a = !r && this.isover ? "isout": r && !this.isover ? "isover": null;
                    a && (this.options.greedy && (s = this.options.scope, o = this.element.parents(":data(ui-droppable)").filter(function() {
                        return t(this).droppable("instance").options.scope === s
                    }), o.length && (n = t(o[0]).droppable("instance"), n.greedyChild = "isover" === a)), n && "isover" === a && (n.isover = !1, n.isout = !0, n._out.call(n, i)), this[a] = !0, this["isout" === a ? "isover": "isout"] = !1, this["isover" === a ? "_over": "_out"].call(this, i), n && "isout" === a && (n.isout = !1, n.isover = !0, n._over.call(n, i)))
                }
            })
        },
        dragStop: function(e, i) {
            e.element.parentsUntil("body").unbind("scroll.droppable"),
            e.options.refreshPositions || t.ui.ddmanager.prepareOffsets(e, i)
        }
    };
    var y = (t.ui.droppable, "ui-effects-"),
    b = t;
    t.effects = {
        effect: {}
    },
    function(t, e) {
        function i(t, e, i) {
            var n = u[e.type] || {};
            return null == t ? i || !e.def ? null: e.def: (t = n.floor ? ~~t: parseFloat(t), isNaN(t) ? e.def: n.mod ? (t + n.mod) % n.mod: 0 > t ? 0 : n.max < t ? n.max: t)
        }
        function n(e) {
            var i = c(),
            n = i._rgba = [];
            return e = e.toLowerCase(),
            f(l,
            function(t, s) {
                var o, r = s.re.exec(e),
                a = r && s.parse(r),
                l = s.space || "rgba";
                return a ? (o = i[l](a), i[h[l].cache] = o[h[l].cache], n = i._rgba = o._rgba, !1) : void 0
            }),
            n.length ? ("0,0,0,0" === n.join() && t.extend(n, o.transparent), i) : o[e]
        }
        function s(t, e, i) {
            return i = (i + 1) % 1,
            1 > 6 * i ? t + (e - t) * i * 6 : 1 > 2 * i ? e: 2 > 3 * i ? t + (e - t) * (2 / 3 - i) * 6 : t
        }
        var o, r = "backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor",
        a = /^([\-+])=\s*(\d+\.?\d*)/,
        l = [{
            re: /rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
            parse: function(t) {
                return [t[1], t[2], t[3], t[4]]
            }
        },
        {
            re: /rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
            parse: function(t) {
                return [2.55 * t[1], 2.55 * t[2], 2.55 * t[3], t[4]]
            }
        },
        {
            re: /#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/,
            parse: function(t) {
                return [parseInt(t[1], 16), parseInt(t[2], 16), parseInt(t[3], 16)]
            }
        },
        {
            re: /#([a-f0-9])([a-f0-9])([a-f0-9])/,
            parse: function(t) {
                return [parseInt(t[1] + t[1], 16), parseInt(t[2] + t[2], 16), parseInt(t[3] + t[3], 16)]
            }
        },
        {
            re: /hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
            space: "hsla",
            parse: function(t) {
                return [t[1], t[2] / 100, t[3] / 100, t[4]]
            }
        }],
        c = t.Color = function(e, i, n, s) {
            return new t.Color.fn.parse(e, i, n, s)
        },
        h = {
            rgba: {
                props: {
                    red: {
                        idx: 0,
                        type: "byte"
                    },
                    green: {
                        idx: 1,
                        type: "byte"
                    },
                    blue: {
                        idx: 2,
                        type: "byte"
                    }
                }
            },
            hsla: {
                props: {
                    hue: {
                        idx: 0,
                        type: "degrees"
                    },
                    saturation: {
                        idx: 1,
                        type: "percent"
                    },
                    lightness: {
                        idx: 2,
                        type: "percent"
                    }
                }
            }
        },
        u = {
            "byte": {
                floor: !0,
                max: 255
            },
            percent: {
                max: 1
            },
            degrees: {
                mod: 360,
                floor: !0
            }
        },
        d = c.support = {},
        p = t("<p>")[0],
        f = t.each;
        p.style.cssText = "background-color:rgba(1,1,1,.5)",
        d.rgba = p.style.backgroundColor.indexOf("rgba") > -1,
        f(h,
        function(t, e) {
            e.cache = "_" + t,
            e.props.alpha = {
                idx: 3,
                type: "percent",
                def: 1
            }
        }),
        c.fn = t.extend(c.prototype, {
            parse: function(s, r, a, l) {
                if (s === e) return this._rgba = [null, null, null, null],
                this; (s.jquery || s.nodeType) && (s = t(s).css(r), r = e);
                var u = this,
                d = t.type(s),
                p = this._rgba = [];
                return r !== e && (s = [s, r, a, l], d = "array"),
                "string" === d ? this.parse(n(s) || o._default) : "array" === d ? (f(h.rgba.props,
                function(t, e) {
                    p[e.idx] = i(s[e.idx], e)
                }), this) : "object" === d ? (s instanceof c ? f(h,
                function(t, e) {
                    s[e.cache] && (u[e.cache] = s[e.cache].slice())
                }) : f(h,
                function(e, n) {
                    var o = n.cache;
                    f(n.props,
                    function(t, e) {
                        if (!u[o] && n.to) {
                            if ("alpha" === t || null == s[t]) return;
                            u[o] = n.to(u._rgba)
                        }
                        u[o][e.idx] = i(s[t], e, !0)
                    }),
                    u[o] && t.inArray(null, u[o].slice(0, 3)) < 0 && (u[o][3] = 1, n.from && (u._rgba = n.from(u[o])))
                }), this) : void 0
            },
            is: function(t) {
                var e = c(t),
                i = !0,
                n = this;
                return f(h,
                function(t, s) {
                    var o, r = e[s.cache];
                    return r && (o = n[s.cache] || s.to && s.to(n._rgba) || [], f(s.props,
                    function(t, e) {
                        return null != r[e.idx] ? i = r[e.idx] === o[e.idx] : void 0
                    })),
                    i
                }),
                i
            },
            _space: function() {
                var t = [],
                e = this;
                return f(h,
                function(i, n) {
                    e[n.cache] && t.push(i)
                }),
                t.pop()
            },
            transition: function(t, e) {
                var n = c(t),
                s = n._space(),
                o = h[s],
                r = 0 === this.alpha() ? c("transparent") : this,
                a = r[o.cache] || o.to(r._rgba),
                l = a.slice();
                return n = n[o.cache],
                f(o.props,
                function(t, s) {
                    var o = s.idx,
                    r = a[o],
                    c = n[o],
                    h = u[s.type] || {};
                    null !== c && (null === r ? l[o] = c: (h.mod && (c - r > h.mod / 2 ? r += h.mod: r - c > h.mod / 2 && (r -= h.mod)), l[o] = i((c - r) * e + r, s)))
                }),
                this[s](l)
            },
            blend: function(e) {
                if (1 === this._rgba[3]) return this;
                var i = this._rgba.slice(),
                n = i.pop(),
                s = c(e)._rgba;
                return c(t.map(i,
                function(t, e) {
                    return (1 - n) * s[e] + n * t
                }))
            },
            toRgbaString: function() {
                var e = "rgba(",
                i = t.map(this._rgba,
                function(t, e) {
                    return null == t ? e > 2 ? 1 : 0 : t
                });
                return 1 === i[3] && (i.pop(), e = "rgb("),
                e + i.join() + ")"
            },
            toHslaString: function() {
                var e = "hsla(",
                i = t.map(this.hsla(),
                function(t, e) {
                    return null == t && (t = e > 2 ? 1 : 0),
                    e && 3 > e && (t = Math.round(100 * t) + "%"),
                    t
                });
                return 1 === i[3] && (i.pop(), e = "hsl("),
                e + i.join() + ")"
            },
            toHexString: function(e) {
                var i = this._rgba.slice(),
                n = i.pop();
                return e && i.push(~~ (255 * n)),
                "#" + t.map(i,
                function(t) {
                    return t = (t || 0).toString(16),
                    1 === t.length ? "0" + t: t
                }).join("")
            },
            toString: function() {
                return 0 === this._rgba[3] ? "transparent": this.toRgbaString()
            }
        }),
        c.fn.parse.prototype = c.fn,
        h.hsla.to = function(t) {
            if (null == t[0] || null == t[1] || null == t[2]) return [null, null, null, t[3]];
            var e, i, n = t[0] / 255,
            s = t[1] / 255,
            o = t[2] / 255,
            r = t[3],
            a = Math.max(n, s, o),
            l = Math.min(n, s, o),
            c = a - l,
            h = a + l,
            u = .5 * h;
            return e = l === a ? 0 : n === a ? 60 * (s - o) / c + 360 : s === a ? 60 * (o - n) / c + 120 : 60 * (n - s) / c + 240,
            i = 0 === c ? 0 : .5 >= u ? c / h: c / (2 - h),
            [Math.round(e) % 360, i, u, null == r ? 1 : r]
        },
        h.hsla.from = function(t) {
            if (null == t[0] || null == t[1] || null == t[2]) return [null, null, null, t[3]];
            var e = t[0] / 360,
            i = t[1],
            n = t[2],
            o = t[3],
            r = .5 >= n ? n * (1 + i) : n + i - n * i,
            a = 2 * n - r;
            return [Math.round(255 * s(a, r, e + 1 / 3)), Math.round(255 * s(a, r, e)), Math.round(255 * s(a, r, e - 1 / 3)), o]
        },
        f(h,
        function(n, s) {
            var o = s.props,
            r = s.cache,
            l = s.to,
            h = s.from;
            c.fn[n] = function(n) {
                if (l && !this[r] && (this[r] = l(this._rgba)), n === e) return this[r].slice();
                var s, a = t.type(n),
                u = "array" === a || "object" === a ? n: arguments,
                d = this[r].slice();
                return f(o,
                function(t, e) {
                    var n = u["object" === a ? t: e.idx];
                    null == n && (n = d[e.idx]),
                    d[e.idx] = i(n, e)
                }),
                h ? (s = c(h(d)), s[r] = d, s) : c(d)
            },
            f(o,
            function(e, i) {
                c.fn[e] || (c.fn[e] = function(s) {
                    var o, r = t.type(s),
                    l = "alpha" === e ? this._hsla ? "hsla": "rgba": n,
                    c = this[l](),
                    h = c[i.idx];
                    return "undefined" === r ? h: ("function" === r && (s = s.call(this, h), r = t.type(s)), null == s && i.empty ? this: ("string" === r && (o = a.exec(s), o && (s = h + parseFloat(o[2]) * ("+" === o[1] ? 1 : -1))), c[i.idx] = s, this[l](c)))
                })
            })
        }),
        c.hook = function(e) {
            var i = e.split(" ");
            f(i,
            function(e, i) {
                t.cssHooks[i] = {
                    set: function(e, s) {
                        var o, r, a = "";
                        if ("transparent" !== s && ("string" !== t.type(s) || (o = n(s)))) {
                            if (s = c(o || s), !d.rgba && 1 !== s._rgba[3]) {
                                for (r = "backgroundColor" === i ? e.parentNode: e; ("" === a || "transparent" === a) && r && r.style;) try {
                                    a = t.css(r, "backgroundColor"),
                                    r = r.parentNode
                                } catch(l) {}
                                s = s.blend(a && "transparent" !== a ? a: "_default")
                            }
                            s = s.toRgbaString()
                        }
                        try {
                            e.style[i] = s
                        } catch(l) {}
                    }
                },
                t.fx.step[i] = function(e) {
                    e.colorInit || (e.start = c(e.elem, i), e.end = c(e.end), e.colorInit = !0),
                    t.cssHooks[i].set(e.elem, e.start.transition(e.end, e.pos))
                }
            })
        },
        c.hook(r),
        t.cssHooks.borderColor = {
            expand: function(t) {
                var e = {};
                return f(["Top", "Right", "Bottom", "Left"],
                function(i, n) {
                    e["border" + n + "Color"] = t
                }),
                e
            }
        },
        o = t.Color.names = {
            aqua: "#00ffff",
            black: "#000000",
            blue: "#0000ff",
            fuchsia: "#ff00ff",
            gray: "#808080",
            green: "#008000",
            lime: "#00ff00",
            maroon: "#800000",
            navy: "#000080",
            olive: "#808000",
            purple: "#800080",
            red: "#ff0000",
            silver: "#c0c0c0",
            teal: "#008080",
            white: "#ffffff",
            yellow: "#ffff00",
            transparent: [null, null, null, 0],
            _default: "#ffffff"
        }
    } (b),
    function() {
        function e(e) {
            var i, n, s = e.ownerDocument.defaultView ? e.ownerDocument.defaultView.getComputedStyle(e, null) : e.currentStyle,
            o = {};
            if (s && s.length && s[0] && s[s[0]]) for (n = s.length; n--;) i = s[n],
            "string" == typeof s[i] && (o[t.camelCase(i)] = s[i]);
            else for (i in s)"string" == typeof s[i] && (o[i] = s[i]);
            return o
        }
        function i(e, i) {
            var n, o, r = {};
            for (n in i) o = i[n],
            e[n] !== o && (s[n] || (t.fx.step[n] || !isNaN(parseFloat(o))) && (r[n] = o));
            return r
        }
        var n = ["add", "remove", "toggle"],
        s = {
            border: 1,
            borderBottom: 1,
            borderColor: 1,
            borderLeft: 1,
            borderRight: 1,
            borderTop: 1,
            borderWidth: 1,
            margin: 1,
            padding: 1
        };
        t.each(["borderLeftStyle", "borderRightStyle", "borderBottomStyle", "borderTopStyle"],
        function(e, i) {
            t.fx.step[i] = function(t) { ("none" !== t.end && !t.setAttr || 1 === t.pos && !t.setAttr) && (b.style(t.elem, i, t.end), t.setAttr = !0)
            }
        }),
        t.fn.addBack || (t.fn.addBack = function(t) {
            return this.add(null == t ? this.prevObject: this.prevObject.filter(t))
        }),
        t.effects.animateClass = function(s, o, r, a) {
            var l = t.speed(o, r, a);
            return this.queue(function() {
                var o, r = t(this),
                a = r.attr("class") || "",
                c = l.children ? r.find("*").addBack() : r;
                c = c.map(function() {
                    var i = t(this);
                    return {
                        el: i,
                        start: e(this)
                    }
                }),
                o = function() {
                    t.each(n,
                    function(t, e) {
                        s[e] && r[e + "Class"](s[e])
                    })
                },
                o(),
                c = c.map(function() {
                    return this.end = e(this.el[0]),
                    this.diff = i(this.start, this.end),
                    this
                }),
                r.attr("class", a),
                c = c.map(function() {
                    var e = this,
                    i = t.Deferred(),
                    n = t.extend({},
                    l, {
                        queue: !1,
                        complete: function() {
                            i.resolve(e)
                        }
                    });
                    return this.el.animate(this.diff, n),
                    i.promise()
                }),
                t.when.apply(t, c.get()).done(function() {
                    o(),
                    t.each(arguments,
                    function() {
                        var e = this.el;
                        t.each(this.diff,
                        function(t) {
                            e.css(t, "")
                        })
                    }),
                    l.complete.call(r[0])
                })
            })
        },
        t.fn.extend({
            addClass: function(e) {
                return function(i, n, s, o) {
                    return n ? t.effects.animateClass.call(this, {
                        add: i
                    },
                    n, s, o) : e.apply(this, arguments)
                }
            } (t.fn.addClass),
            removeClass: function(e) {
                return function(i, n, s, o) {
                    return arguments.length > 1 ? t.effects.animateClass.call(this, {
                        remove: i
                    },
                    n, s, o) : e.apply(this, arguments)
                }
            } (t.fn.removeClass),
            toggleClass: function(e) {
                return function(i, n, s, o, r) {
                    return "boolean" == typeof n || void 0 === n ? s ? t.effects.animateClass.call(this, n ? {
                        add: i
                    }: {
                        remove: i
                    },
                    s, o, r) : e.apply(this, arguments) : t.effects.animateClass.call(this, {
                        toggle: i
                    },
                    n, s, o)
                }
            } (t.fn.toggleClass),
            switchClass: function(e, i, n, s, o) {
                return t.effects.animateClass.call(this, {
                    add: i,
                    remove: e
                },
                n, s, o)
            }
        })
    } (),
    function() {
        function e(e, i, n, s) {
            return t.isPlainObject(e) && (i = e, e = e.effect),
            e = {
                effect: e
            },
            null == i && (i = {}),
            t.isFunction(i) && (s = i, n = null, i = {}),
            ("number" == typeof i || t.fx.speeds[i]) && (s = n, n = i, i = {}),
            t.isFunction(n) && (s = n, n = null),
            i && t.extend(e, i),
            n = n || i.duration,
            e.duration = t.fx.off ? 0 : "number" == typeof n ? n: n in t.fx.speeds ? t.fx.speeds[n] : t.fx.speeds._default,
            e.complete = s || i.complete,
            e
        }
        function i(e) {
            return ! e || "number" == typeof e || t.fx.speeds[e] ? !0 : "string" != typeof e || t.effects.effect[e] ? t.isFunction(e) ? !0 : "object" != typeof e || e.effect ? !1 : !0 : !0
        }
        t.extend(t.effects, {
            version: "1.11.4",
            save: function(t, e) {
                for (var i = 0; i < e.length; i++) null !== e[i] && t.data(y + e[i], t[0].style[e[i]])
            },
            restore: function(t, e) {
                var i, n;
                for (n = 0; n < e.length; n++) null !== e[n] && (i = t.data(y + e[n]), void 0 === i && (i = ""), t.css(e[n], i))
            },
            setMode: function(t, e) {
                return "toggle" === e && (e = t.is(":hidden") ? "show": "hide"),
                e
            },
            getBaseline: function(t, e) {
                var i, n;
                switch (t[0]) {
                case "top":
                    i = 0;
                    break;
                case "middle":
                    i = .5;
                    break;
                case "bottom":
                    i = 1;
                    break;
                default:
                    i = t[0] / e.height
                }
                switch (t[1]) {
                case "left":
                    n = 0;
                    break;
                case "center":
                    n = .5;
                    break;
                case "right":
                    n = 1;
                    break;
                default:
                    n = t[1] / e.width
                }
                return {
                    x: n,
                    y: i
                }
            },
            createWrapper: function(e) {
                if (e.parent().is(".ui-effects-wrapper")) return e.parent();
                var i = {
                    width: e.outerWidth(!0),
                    height: e.outerHeight(!0),
                    "float": e.css("float")
                },
                n = t("<div></div>").addClass("ui-effects-wrapper").css({
                    fontSize: "100%",
                    background: "transparent",
                    border: "none",
                    margin: 0,
                    padding: 0
                }),
                s = {
                    width: e.width(),
                    height: e.height()
                },
                o = document.activeElement;
                try {
                    o.id
                } catch(r) {
                    o = document.body
                }
                return e.wrap(n),
                (e[0] === o || t.contains(e[0], o)) && t(o).focus(),
                n = e.parent(),
                "static" === e.css("position") ? (n.css({
                    position: "relative"
                }), e.css({
                    position: "relative"
                })) : (t.extend(i, {
                    position: e.css("position"),
                    zIndex: e.css("z-index")
                }), t.each(["top", "left", "bottom", "right"],
                function(t, n) {
                    i[n] = e.css(n),
                    isNaN(parseInt(i[n], 10)) && (i[n] = "auto")
                }), e.css({
                    position: "relative",
                    top: 0,
                    left: 0,
                    right: "auto",
                    bottom: "auto"
                })),
                e.css(s),
                n.css(i).show()
            },
            removeWrapper: function(e) {
                var i = document.activeElement;
                return e.parent().is(".ui-effects-wrapper") && (e.parent().replaceWith(e), (e[0] === i || t.contains(e[0], i)) && t(i).focus()),
                e
            },
            setTransition: function(e, i, n, s) {
                return s = s || {},
                t.each(i,
                function(t, i) {
                    var o = e.cssUnit(i);
                    o[0] > 0 && (s[i] = o[0] * n + o[1])
                }),
                s
            }
        }),
        t.fn.extend({
            effect: function() {
                function i(e) {
                    function i() {
                        t.isFunction(o) && o.call(s[0]),
                        t.isFunction(e) && e()
                    }
                    var s = t(this),
                    o = n.complete,
                    a = n.mode; (s.is(":hidden") ? "hide" === a: "show" === a) ? (s[a](), i()) : r.call(s[0], n, i)
                }
                var n = e.apply(this, arguments),
                s = n.mode,
                o = n.queue,
                r = t.effects.effect[n.effect];
                return t.fx.off || !r ? s ? this[s](n.duration, n.complete) : this.each(function() {
                    n.complete && n.complete.call(this)
                }) : o === !1 ? this.each(i) : this.queue(o || "fx", i)
            },
            show: function(t) {
                return function(n) {
                    if (i(n)) return t.apply(this, arguments);
                    var s = e.apply(this, arguments);
                    return s.mode = "show",
                    this.effect.call(this, s)
                }
            } (t.fn.show),
            hide: function(t) {
                return function(n) {
                    if (i(n)) return t.apply(this, arguments);
                    var s = e.apply(this, arguments);
                    return s.mode = "hide",
                    this.effect.call(this, s)
                }
            } (t.fn.hide),
            toggle: function(t) {
                return function(n) {
                    if (i(n) || "boolean" == typeof n) return t.apply(this, arguments);
                    var s = e.apply(this, arguments);
                    return s.mode = "toggle",
                    this.effect.call(this, s)
                }
            } (t.fn.toggle),
            cssUnit: function(e) {
                var i = this.css(e),
                n = [];
                return t.each(["em", "px", "%", "pt"],
                function(t, e) {
                    i.indexOf(e) > 0 && (n = [parseFloat(i), e])
                }),
                n
            }
        })
    } (),
    function() {
        var e = {};
        t.each(["Quad", "Cubic", "Quart", "Quint", "Expo"],
        function(t, i) {
            e[i] = function(e) {
                return Math.pow(e, t + 2)
            }
        }),
        t.extend(e, {
            Sine: function(t) {
                return 1 - Math.cos(t * Math.PI / 2)
            },
            Circ: function(t) {
                return 1 - Math.sqrt(1 - t * t)
            },
            Elastic: function(t) {
                return 0 === t || 1 === t ? t: -Math.pow(2, 8 * (t - 1)) * Math.sin((80 * (t - 1) - 7.5) * Math.PI / 15)
            },
            Back: function(t) {
                return t * t * (3 * t - 2)
            },
            Bounce: function(t) {
                for (var e, i = 4; t < ((e = Math.pow(2, --i)) - 1) / 11;);
                return 1 / Math.pow(4, 3 - i) - 7.5625 * Math.pow((3 * e - 2) / 22 - t, 2)
            }
        }),
        t.each(e,
        function(e, i) {
            t.easing["easeIn" + e] = i,
            t.easing["easeOut" + e] = function(t) {
                return 1 - i(1 - t)
            },
            t.easing["easeInOut" + e] = function(t) {
                return.5 > t ? i(2 * t) / 2 : 1 - i( - 2 * t + 2) / 2
            }
        })
    } ();
    t.effects,
    t.effects.effect.blind = function(e, i) {
        var n, s, o, r = t(this),
        a = /up|down|vertical/,
        l = /up|left|vertical|horizontal/,
        c = ["position", "top", "bottom", "left", "right", "height", "width"],
        h = t.effects.setMode(r, e.mode || "hide"),
        u = e.direction || "up",
        d = a.test(u),
        p = d ? "height": "width",
        f = d ? "top": "left",
        m = l.test(u),
        g = {},
        v = "show" === h;
        r.parent().is(".ui-effects-wrapper") ? t.effects.save(r.parent(), c) : t.effects.save(r, c),
        r.show(),
        n = t.effects.createWrapper(r).css({
            overflow: "hidden"
        }),
        s = n[p](),
        o = parseFloat(n.css(f)) || 0,
        g[p] = v ? s: 0,
        m || (r.css(d ? "bottom": "right", 0).css(d ? "top": "left", "auto").css({
            position: "absolute"
        }), g[f] = v ? o: s + o),
        v && (n.css(p, 0), m || n.css(f, o + s)),
        n.animate(g, {
            duration: e.duration,
            easing: e.easing,
            queue: !1,
            complete: function() {
                "hide" === h && r.hide(),
                t.effects.restore(r, c),
                t.effects.removeWrapper(r),
                i()
            }
        })
    },
    t.effects.effect.bounce = function(e, i) {
        var n, s, o, r = t(this),
        a = ["position", "top", "bottom", "left", "right", "height", "width"],
        l = t.effects.setMode(r, e.mode || "effect"),
        c = "hide" === l,
        h = "show" === l,
        u = e.direction || "up",
        d = e.distance,
        p = e.times || 5,
        f = 2 * p + (h || c ? 1 : 0),
        m = e.duration / f,
        g = e.easing,
        v = "up" === u || "down" === u ? "top": "left",
        y = "up" === u || "left" === u,
        b = r.queue(),
        _ = b.length;
        for ((h || c) && a.push("opacity"), t.effects.save(r, a), r.show(), t.effects.createWrapper(r), d || (d = r["top" === v ? "outerHeight": "outerWidth"]() / 3), h && (o = {
            opacity: 1
        },
        o[v] = 0, r.css("opacity", 0).css(v, y ? 2 * -d: 2 * d).animate(o, m, g)), c && (d /= Math.pow(2, p - 1)), o = {},
        o[v] = 0, n = 0; p > n; n++) s = {},
        s[v] = (y ? "-=": "+=") + d,
        r.animate(s, m, g).animate(o, m, g),
        d = c ? 2 * d: d / 2;
        c && (s = {
            opacity: 0
        },
        s[v] = (y ? "-=": "+=") + d, r.animate(s, m, g)),
        r.queue(function() {
            c && r.hide(),
            t.effects.restore(r, a),
            t.effects.removeWrapper(r),
            i()
        }),
        _ > 1 && b.splice.apply(b, [1, 0].concat(b.splice(_, f + 1))),
        r.dequeue()
    },
    t.effects.effect.clip = function(e, i) {
        var n, s, o, r = t(this),
        a = ["position", "top", "bottom", "left", "right", "height", "width"],
        l = t.effects.setMode(r, e.mode || "hide"),
        c = "show" === l,
        h = e.direction || "vertical",
        u = "vertical" === h,
        d = u ? "height": "width",
        p = u ? "top": "left",
        f = {};
        t.effects.save(r, a),
        r.show(),
        n = t.effects.createWrapper(r).css({
            overflow: "hidden"
        }),
        s = "IMG" === r[0].tagName ? n: r,
        o = s[d](),
        c && (s.css(d, 0), s.css(p, o / 2)),
        f[d] = c ? o: 0,
        f[p] = c ? 0 : o / 2,
        s.animate(f, {
            queue: !1,
            duration: e.duration,
            easing: e.easing,
            complete: function() {
                c || r.hide(),
                t.effects.restore(r, a),
                t.effects.removeWrapper(r),
                i()
            }
        })
    },
    t.effects.effect.drop = function(e, i) {
        var n, s = t(this),
        o = ["position", "top", "bottom", "left", "right", "opacity", "height", "width"],
        r = t.effects.setMode(s, e.mode || "hide"),
        a = "show" === r,
        l = e.direction || "left",
        c = "up" === l || "down" === l ? "top": "left",
        h = "up" === l || "left" === l ? "pos": "neg",
        u = {
            opacity: a ? 1 : 0
        };
        t.effects.save(s, o),
        s.show(),
        t.effects.createWrapper(s),
        n = e.distance || s["top" === c ? "outerHeight": "outerWidth"](!0) / 2,
        a && s.css("opacity", 0).css(c, "pos" === h ? -n: n),
        u[c] = (a ? "pos" === h ? "+=": "-=": "pos" === h ? "-=": "+=") + n,
        s.animate(u, {
            queue: !1,
            duration: e.duration,
            easing: e.easing,
            complete: function() {
                "hide" === r && s.hide(),
                t.effects.restore(s, o),
                t.effects.removeWrapper(s),
                i()
            }
        })
    },
    t.effects.effect.explode = function(e, i) {
        function n() {
            b.push(this),
            b.length === u * d && s()
        }
        function s() {
            p.css({
                visibility: "visible"
            }),
            t(b).remove(),
            m || p.hide(),
            i()
        }
        var o, r, a, l, c, h, u = e.pieces ? Math.round(Math.sqrt(e.pieces)) : 3,
        d = u,
        p = t(this),
        f = t.effects.setMode(p, e.mode || "hide"),
        m = "show" === f,
        g = p.show().css("visibility", "hidden").offset(),
        v = Math.ceil(p.outerWidth() / d),
        y = Math.ceil(p.outerHeight() / u),
        b = [];
        for (o = 0; u > o; o++) for (l = g.top + o * y, h = o - (u - 1) / 2, r = 0; d > r; r++) a = g.left + r * v,
        c = r - (d - 1) / 2,
        p.clone().appendTo("body").wrap("<div></div>").css({
            position: "absolute",
            visibility: "visible",
            left: -r * v,
            top: -o * y
        }).parent().addClass("ui-effects-explode").css({
            position: "absolute",
            overflow: "hidden",
            width: v,
            height: y,
            left: a + (m ? c * v: 0),
            top: l + (m ? h * y: 0),
            opacity: m ? 0 : 1
        }).animate({
            left: a + (m ? 0 : c * v),
            top: l + (m ? 0 : h * y),
            opacity: m ? 1 : 0
        },
        e.duration || 500, e.easing, n)
    },
    t.effects.effect.fade = function(e, i) {
        var n = t(this),
        s = t.effects.setMode(n, e.mode || "toggle");
        n.animate({
            opacity: s
        },
        {
            queue: !1,
            duration: e.duration,
            easing: e.easing,
            complete: i
        })
    },
    t.effects.effect.fold = function(e, i) {
        var n, s, o = t(this),
        r = ["position", "top", "bottom", "left", "right", "height", "width"],
        a = t.effects.setMode(o, e.mode || "hide"),
        l = "show" === a,
        c = "hide" === a,
        h = e.size || 15,
        u = /([0-9]+)%/.exec(h),
        d = !!e.horizFirst,
        p = l !== d,
        f = p ? ["width", "height"] : ["height", "width"],
        m = e.duration / 2,
        g = {},
        v = {};
        t.effects.save(o, r),
        o.show(),
        n = t.effects.createWrapper(o).css({
            overflow: "hidden"
        }),
        s = p ? [n.width(), n.height()] : [n.height(), n.width()],
        u && (h = parseInt(u[1], 10) / 100 * s[c ? 0 : 1]),
        l && n.css(d ? {
            height: 0,
            width: h
        }: {
            height: h,
            width: 0
        }),
        g[f[0]] = l ? s[0] : h,
        v[f[1]] = l ? s[1] : 0,
        n.animate(g, m, e.easing).animate(v, m, e.easing,
        function() {
            c && o.hide(),
            t.effects.restore(o, r),
            t.effects.removeWrapper(o),
            i()
        })
    },
    t.effects.effect.highlight = function(e, i) {
        var n = t(this),
        s = ["backgroundImage", "backgroundColor", "opacity"],
        o = t.effects.setMode(n, e.mode || "show"),
        r = {
            backgroundColor: n.css("backgroundColor")
        };
        "hide" === o && (r.opacity = 0),
        t.effects.save(n, s),
        n.show().css({
            backgroundImage: "none",
            backgroundColor: e.color || "#ffff99"
        }).animate(r, {
            queue: !1,
            duration: e.duration,
            easing: e.easing,
            complete: function() {
                "hide" === o && n.hide(),
                t.effects.restore(n, s),
                i()
            }
        })
    },
    t.effects.effect.size = function(e, i) {
        var n, s, o, r = t(this),
        a = ["position", "top", "bottom", "left", "right", "width", "height", "overflow", "opacity"],
        l = ["position", "top", "bottom", "left", "right", "overflow", "opacity"],
        c = ["width", "height", "overflow"],
        h = ["fontSize"],
        u = ["borderTopWidth", "borderBottomWidth", "paddingTop", "paddingBottom"],
        d = ["borderLeftWidth", "borderRightWidth", "paddingLeft", "paddingRight"],
        p = t.effects.setMode(r, e.mode || "effect"),
        f = e.restore || "effect" !== p,
        m = e.scale || "both",
        g = e.origin || ["middle", "center"],
        v = r.css("position"),
        y = f ? a: l,
        b = {
            height: 0,
            width: 0,
            outerHeight: 0,
            outerWidth: 0
        };
        "show" === p && r.show(),
        n = {
            height: r.height(),
            width: r.width(),
            outerHeight: r.outerHeight(),
            outerWidth: r.outerWidth()
        },
        "toggle" === e.mode && "show" === p ? (r.from = e.to || b, r.to = e.from || n) : (r.from = e.from || ("show" === p ? b: n), r.to = e.to || ("hide" === p ? b: n)),
        o = {
            from: {
                y: r.from.height / n.height,
                x: r.from.width / n.width
            },
            to: {
                y: r.to.height / n.height,
                x: r.to.width / n.width
            }
        },
        ("box" === m || "both" === m) && (o.from.y !== o.to.y && (y = y.concat(u), r.from = t.effects.setTransition(r, u, o.from.y, r.from), r.to = t.effects.setTransition(r, u, o.to.y, r.to)), o.from.x !== o.to.x && (y = y.concat(d), r.from = t.effects.setTransition(r, d, o.from.x, r.from), r.to = t.effects.setTransition(r, d, o.to.x, r.to))),
        ("content" === m || "both" === m) && o.from.y !== o.to.y && (y = y.concat(h).concat(c), r.from = t.effects.setTransition(r, h, o.from.y, r.from), r.to = t.effects.setTransition(r, h, o.to.y, r.to)),
        t.effects.save(r, y),
        r.show(),
        t.effects.createWrapper(r),
        r.css("overflow", "hidden").css(r.from),
        g && (s = t.effects.getBaseline(g, n), r.from.top = (n.outerHeight - r.outerHeight()) * s.y, r.from.left = (n.outerWidth - r.outerWidth()) * s.x, r.to.top = (n.outerHeight - r.to.outerHeight) * s.y, r.to.left = (n.outerWidth - r.to.outerWidth) * s.x),
        r.css(r.from),
        ("content" === m || "both" === m) && (u = u.concat(["marginTop", "marginBottom"]).concat(h), d = d.concat(["marginLeft", "marginRight"]), c = a.concat(u).concat(d), r.find("*[width]").each(function() {
            var i = t(this),
            n = {
                height: i.height(),
                width: i.width(),
                outerHeight: i.outerHeight(),
                outerWidth: i.outerWidth()
            };
            f && t.effects.save(i, c),
            i.from = {
                height: n.height * o.from.y,
                width: n.width * o.from.x,
                outerHeight: n.outerHeight * o.from.y,
                outerWidth: n.outerWidth * o.from.x
            },
            i.to = {
                height: n.height * o.to.y,
                width: n.width * o.to.x,
                outerHeight: n.height * o.to.y,
                outerWidth: n.width * o.to.x
            },
            o.from.y !== o.to.y && (i.from = t.effects.setTransition(i, u, o.from.y, i.from), i.to = t.effects.setTransition(i, u, o.to.y, i.to)),
            o.from.x !== o.to.x && (i.from = t.effects.setTransition(i, d, o.from.x, i.from), i.to = t.effects.setTransition(i, d, o.to.x, i.to)),
            i.css(i.from),
            i.animate(i.to, e.duration, e.easing,
            function() {
                f && t.effects.restore(i, c)
            })
        })),
        r.animate(r.to, {
            queue: !1,
            duration: e.duration,
            easing: e.easing,
            complete: function() {
                0 === r.to.opacity && r.css("opacity", r.from.opacity),
                "hide" === p && r.hide(),
                t.effects.restore(r, y),
                f || ("static" === v ? r.css({
                    position: "relative",
                    top: r.to.top,
                    left: r.to.left
                }) : t.each(["top", "left"],
                function(t, e) {
                    r.css(e,
                    function(e, i) {
                        var n = parseInt(i, 10),
                        s = t ? r.to.left: r.to.top;
                        return "auto" === i ? s + "px": n + s + "px"
                    })
                })),
                t.effects.removeWrapper(r),
                i()
            }
        })
    },
    t.effects.effect.scale = function(e, i) {
        var n = t(this),
        s = t.extend(!0, {},
        e),
        o = t.effects.setMode(n, e.mode || "effect"),
        r = parseInt(e.percent, 10) || (0 === parseInt(e.percent, 10) ? 0 : "hide" === o ? 0 : 100),
        a = e.direction || "both",
        l = e.origin,
        c = {
            height: n.height(),
            width: n.width(),
            outerHeight: n.outerHeight(),
            outerWidth: n.outerWidth()
        },
        h = {
            y: "horizontal" !== a ? r / 100 : 1,
            x: "vertical" !== a ? r / 100 : 1
        };
        s.effect = "size",
        s.queue = !1,
        s.complete = i,
        "effect" !== o && (s.origin = l || ["middle", "center"], s.restore = !0),
        s.from = e.from || ("show" === o ? {
            height: 0,
            width: 0,
            outerHeight: 0,
            outerWidth: 0
        }: c),
        s.to = {
            height: c.height * h.y,
            width: c.width * h.x,
            outerHeight: c.outerHeight * h.y,
            outerWidth: c.outerWidth * h.x
        },
        s.fade && ("show" === o && (s.from.opacity = 0, s.to.opacity = 1), "hide" === o && (s.from.opacity = 1, s.to.opacity = 0)),
        n.effect(s)
    },
    t.effects.effect.puff = function(e, i) {
        var n = t(this),
        s = t.effects.setMode(n, e.mode || "hide"),
        o = "hide" === s,
        r = parseInt(e.percent, 10) || 150,
        a = r / 100,
        l = {
            height: n.height(),
            width: n.width(),
            outerHeight: n.outerHeight(),
            outerWidth: n.outerWidth()
        };
        t.extend(e, {
            effect: "scale",
            queue: !1,
            fade: !0,
            mode: s,
            complete: i,
            percent: o ? r: 100,
            from: o ? l: {
                height: l.height * a,
                width: l.width * a,
                outerHeight: l.outerHeight * a,
                outerWidth: l.outerWidth * a
            }
        }),
        n.effect(e)
    },
    t.effects.effect.pulsate = function(e, i) {
        var n, s = t(this),
        o = t.effects.setMode(s, e.mode || "show"),
        r = "show" === o,
        a = "hide" === o,
        l = r || "hide" === o,
        c = 2 * (e.times || 5) + (l ? 1 : 0),
        h = e.duration / c,
        u = 0,
        d = s.queue(),
        p = d.length;
        for ((r || !s.is(":visible")) && (s.css("opacity", 0).show(), u = 1), n = 1; c > n; n++) s.animate({
            opacity: u
        },
        h, e.easing),
        u = 1 - u;
        s.animate({
            opacity: u
        },
        h, e.easing),
        s.queue(function() {
            a && s.hide(),
            i()
        }),
        p > 1 && d.splice.apply(d, [1, 0].concat(d.splice(p, c + 1))),
        s.dequeue()
    },
    t.effects.effect.shake = function(e, i) {
        var n, s = t(this),
        o = ["position", "top", "bottom", "left", "right", "height", "width"],
        r = t.effects.setMode(s, e.mode || "effect"),
        a = e.direction || "left",
        l = e.distance || 20,
        c = e.times || 3,
        h = 2 * c + 1,
        u = Math.round(e.duration / h),
        d = "up" === a || "down" === a ? "top": "left",
        p = "up" === a || "left" === a,
        f = {},
        m = {},
        g = {},
        v = s.queue(),
        y = v.length;
        for (t.effects.save(s, o), s.show(), t.effects.createWrapper(s), f[d] = (p ? "-=": "+=") + l, m[d] = (p ? "+=": "-=") + 2 * l, g[d] = (p ? "-=": "+=") + 2 * l, s.animate(f, u, e.easing), n = 1; c > n; n++) s.animate(m, u, e.easing).animate(g, u, e.easing);
        s.animate(m, u, e.easing).animate(f, u / 2, e.easing).queue(function() {
            "hide" === r && s.hide(),
            t.effects.restore(s, o),
            t.effects.removeWrapper(s),
            i()
        }),
        y > 1 && v.splice.apply(v, [1, 0].concat(v.splice(y, h + 1))),
        s.dequeue()
    },
    t.effects.effect.slide = function(e, i) {
        var n, s = t(this),
        o = ["position", "top", "bottom", "left", "right", "width", "height"],
        r = t.effects.setMode(s, e.mode || "show"),
        a = "show" === r,
        l = e.direction || "left",
        c = "up" === l || "down" === l ? "top": "left",
        h = "up" === l || "left" === l,
        u = {};
        t.effects.save(s, o),
        s.show(),
        n = e.distance || s["top" === c ? "outerHeight": "outerWidth"](!0),
        t.effects.createWrapper(s).css({
            overflow: "hidden"
        }),
        a && s.css(c, h ? isNaN(n) ? "-" + n: -n: n),
        u[c] = (a ? h ? "+=": "-=": h ? "-=": "+=") + n,
        s.animate(u, {
            queue: !1,
            duration: e.duration,
            easing: e.easing,
            complete: function() {
                "hide" === r && s.hide(),
                t.effects.restore(s, o),
                t.effects.removeWrapper(s),
                i()
            }
        })
    },
    t.effects.effect.transfer = function(e, i) {
        var n = t(this),
        s = t(e.to),
        o = "fixed" === s.css("position"),
        r = t("body"),
        a = o ? r.scrollTop() : 0,
        l = o ? r.scrollLeft() : 0,
        c = s.offset(),
        h = {
            top: c.top - a,
            left: c.left - l,
            height: s.innerHeight(),
            width: s.innerWidth()
        },
        u = n.offset(),
        d = t("<div class='ui-effects-transfer'></div>").appendTo(document.body).addClass(e.className).css({
            top: u.top - a,
            left: u.left - l,
            height: n.innerHeight(),
            width: n.innerWidth(),
            position: o ? "fixed": "absolute"
        }).animate(h, e.duration, e.easing,
        function() {
            d.remove(),
            i()
        })
    },
    t.widget("ui.progressbar", {
        version: "1.11.4",
        options: {
            max: 100,
            value: 0,
            change: null,
            complete: null
        },
        min: 0,
        _create: function() {
            this.oldValue = this.options.value = this._constrainedValue(),
            this.element.addClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").attr({
                role: "progressbar",
                "aria-valuemin": this.min
            }),
            this.valueDiv = t("<div class='ui-progressbar-value ui-widget-header ui-corner-left'></div>").appendTo(this.element),
            this._refreshValue()
        },
        _destroy: function() {
            this.element.removeClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow"),
            this.valueDiv.remove()
        },
        value: function(t) {
            return void 0 === t ? this.options.value: (this.options.value = this._constrainedValue(t), void this._refreshValue())
        },
        _constrainedValue: function(t) {
            return void 0 === t && (t = this.options.value),
            this.indeterminate = t === !1,
            "number" != typeof t && (t = 0),
            this.indeterminate ? !1 : Math.min(this.options.max, Math.max(this.min, t))
        },
        _setOptions: function(t) {
            var e = t.value;
            delete t.value,
            this._super(t),
            this.options.value = this._constrainedValue(e),
            this._refreshValue()
        },
        _setOption: function(t, e) {
            "max" === t && (e = Math.max(this.min, e)),
            "disabled" === t && this.element.toggleClass("ui-state-disabled", !!e).attr("aria-disabled", e),
            this._super(t, e)
        },
        _percentage: function() {
            return this.indeterminate ? 100 : 100 * (this.options.value - this.min) / (this.options.max - this.min)
        },
        _refreshValue: function() {
            var e = this.options.value,
            i = this._percentage();
            this.valueDiv.toggle(this.indeterminate || e > this.min).toggleClass("ui-corner-right", e === this.options.max).width(i.toFixed(0) + "%"),
            this.element.toggleClass("ui-progressbar-indeterminate", this.indeterminate),
            this.indeterminate ? (this.element.removeAttr("aria-valuenow"), this.overlayDiv || (this.overlayDiv = t("<div class='ui-progressbar-overlay'></div>").appendTo(this.valueDiv))) : (this.element.attr({
                "aria-valuemax": this.options.max,
                "aria-valuenow": e
            }), this.overlayDiv && (this.overlayDiv.remove(), this.overlayDiv = null)),
            this.oldValue !== e && (this.oldValue = e, this._trigger("change")),
            e === this.options.max && this._trigger("complete")
        }
    }),
    t.widget("ui.selectable", t.ui.mouse, {
        version: "1.11.4",
        options: {
            appendTo: "body",
            autoRefresh: !0,
            distance: 0,
            filter: "*",
            tolerance: "touch",
            selected: null,
            selecting: null,
            start: null,
            stop: null,
            unselected: null,
            unselecting: null
        },
        _create: function() {
            var e, i = this;
            this.element.addClass("ui-selectable"),
            this.dragged = !1,
            this.refresh = function() {
                e = t(i.options.filter, i.element[0]),
                e.addClass("ui-selectee"),
                e.each(function() {
                    var e = t(this),
                    i = e.offset();
                    t.data(this, "selectable-item", {
                        element: this,
                        $element: e,
                        left: i.left,
                        top: i.top,
                        right: i.left + e.outerWidth(),
                        bottom: i.top + e.outerHeight(),
                        startselected: !1,
                        selected: e.hasClass("ui-selected"),
                        selecting: e.hasClass("ui-selecting"),
                        unselecting: e.hasClass("ui-unselecting")
                    })
                })
            },
            this.refresh(),
            this.selectees = e.addClass("ui-selectee"),
            this._mouseInit(),
            this.helper = t("<div class='ui-selectable-helper'></div>")
        },
        _destroy: function() {
            this.selectees.removeClass("ui-selectee").removeData("selectable-item"),
            this.element.removeClass("ui-selectable ui-selectable-disabled"),
            this._mouseDestroy()
        },
        _mouseStart: function(e) {
            var i = this,
            n = this.options;
            this.opos = [e.pageX, e.pageY],
            this.options.disabled || (this.selectees = t(n.filter, this.element[0]), this._trigger("start", e), t(n.appendTo).append(this.helper), this.helper.css({
                left: e.pageX,
                top: e.pageY,
                width: 0,
                height: 0
            }), n.autoRefresh && this.refresh(), this.selectees.filter(".ui-selected").each(function() {
                var n = t.data(this, "selectable-item");
                n.startselected = !0,
                e.metaKey || e.ctrlKey || (n.$element.removeClass("ui-selected"), n.selected = !1, n.$element.addClass("ui-unselecting"), n.unselecting = !0, i._trigger("unselecting", e, {
                    unselecting: n.element
                }))
            }), t(e.target).parents().addBack().each(function() {
                var n, s = t.data(this, "selectable-item");
                return s ? (n = !e.metaKey && !e.ctrlKey || !s.$element.hasClass("ui-selected"), s.$element.removeClass(n ? "ui-unselecting": "ui-selected").addClass(n ? "ui-selecting": "ui-unselecting"), s.unselecting = !n, s.selecting = n, s.selected = n, n ? i._trigger("selecting", e, {
                    selecting: s.element
                }) : i._trigger("unselecting", e, {
                    unselecting: s.element
                }), !1) : void 0
            }))
        },
        _mouseDrag: function(e) {
            if (this.dragged = !0, !this.options.disabled) {
                var i, n = this,
                s = this.options,
                o = this.opos[0],
                r = this.opos[1],
                a = e.pageX,
                l = e.pageY;
                return o > a && (i = a, a = o, o = i),
                r > l && (i = l, l = r, r = i),
                this.helper.css({
                    left: o,
                    top: r,
                    width: a - o,
                    height: l - r
                }),
                this.selectees.each(function() {
                    var i = t.data(this, "selectable-item"),
                    c = !1;
                    i && i.element !== n.element[0] && ("touch" === s.tolerance ? c = !(i.left > a || i.right < o || i.top > l || i.bottom < r) : "fit" === s.tolerance && (c = i.left > o && i.right < a && i.top > r && i.bottom < l), c ? (i.selected && (i.$element.removeClass("ui-selected"), i.selected = !1), i.unselecting && (i.$element.removeClass("ui-unselecting"), i.unselecting = !1), i.selecting || (i.$element.addClass("ui-selecting"), i.selecting = !0, n._trigger("selecting", e, {
                        selecting: i.element
                    }))) : (i.selecting && ((e.metaKey || e.ctrlKey) && i.startselected ? (i.$element.removeClass("ui-selecting"), i.selecting = !1, i.$element.addClass("ui-selected"), i.selected = !0) : (i.$element.removeClass("ui-selecting"), i.selecting = !1, i.startselected && (i.$element.addClass("ui-unselecting"), i.unselecting = !0), n._trigger("unselecting", e, {
                        unselecting: i.element
                    }))), i.selected && (e.metaKey || e.ctrlKey || i.startselected || (i.$element.removeClass("ui-selected"), i.selected = !1, i.$element.addClass("ui-unselecting"), i.unselecting = !0, n._trigger("unselecting", e, {
                        unselecting: i.element
                    })))))
                }),
                !1
            }
        },
        _mouseStop: function(e) {
            var i = this;
            return this.dragged = !1,
            t(".ui-unselecting", this.element[0]).each(function() {
                var n = t.data(this, "selectable-item");
                n.$element.removeClass("ui-unselecting"),
                n.unselecting = !1,
                n.startselected = !1,
                i._trigger("unselected", e, {
                    unselected: n.element
                })
            }),
            t(".ui-selecting", this.element[0]).each(function() {
                var n = t.data(this, "selectable-item");
                n.$element.removeClass("ui-selecting").addClass("ui-selected"),
                n.selecting = !1,
                n.selected = !0,
                n.startselected = !0,
                i._trigger("selected", e, {
                    selected: n.element
                })
            }),
            this._trigger("stop", e),
            this.helper.remove(),
            !1
        }
    }),
    t.widget("ui.selectmenu", {
        version: "1.11.4",
        defaultElement: "<select>",
        options: {
            appendTo: null,
            disabled: null,
            icons: {
                button: "ui-icon-triangle-1-s"
            },
            position: {
                my: "left top",
                at: "left bottom",
                collision: "none"
            },
            width: null,
            change: null,
            close: null,
            focus: null,
            open: null,
            select: null
        },
        _create: function() {
            var t = this.element.uniqueId().attr("id");
            this.ids = {
                element: t,
                button: t + "-button",
                menu: t + "-menu"
            },
            this._drawButton(),
            this._drawMenu(),
            this.options.disabled && this.disable()
        },
        _drawButton: function() {
            var e = this;
            this.label = t("label[for='" + this.ids.element + "']").attr("for", this.ids.button),
            this._on(this.label, {
                click: function(t) {
                    this.button.focus(),
                    t.preventDefault()
                }
            }),
            this.element.hide(),
            this.button = t("<span>", {
                "class": "ui-selectmenu-button ui-widget ui-state-default ui-corner-all",
                tabindex: this.options.disabled ? -1 : 0,
                id: this.ids.button,
                role: "combobox",
                "aria-expanded": "false",
                "aria-autocomplete": "list",
                "aria-owns": this.ids.menu,
                "aria-haspopup": "true"
            }).insertAfter(this.element),
            t("<span>", {
                "class": "ui-icon " + this.options.icons.button
            }).prependTo(this.button),
            this.buttonText = t("<span>", {
                "class": "ui-selectmenu-text"
            }).appendTo(this.button),
            this._setText(this.buttonText, this.element.find("option:selected").text()),
            this._resizeButton(),
            this._on(this.button, this._buttonEvents),
            this.button.one("focusin",
            function() {
                e.menuItems || e._refreshMenu()
            }),
            this._hoverable(this.button),
            this._focusable(this.button)
        },
        _drawMenu: function() {
            var e = this;
            this.menu = t("<ul>", {
                "aria-hidden": "true",
                "aria-labelledby": this.ids.button,
                id: this.ids.menu
            }),
            this.menuWrap = t("<div>", {
                "class": "ui-selectmenu-menu ui-front"
            }).append(this.menu).appendTo(this._appendTo()),
            this.menuInstance = this.menu.menu({
                role: "listbox",
                select: function(t, i) {
                    t.preventDefault(),
                    e._setSelection(),
                    e._select(i.item.data("ui-selectmenu-item"), t)
                },
                focus: function(t, i) {
                    var n = i.item.data("ui-selectmenu-item");
                    null != e.focusIndex && n.index !== e.focusIndex && (e._trigger("focus", t, {
                        item: n
                    }), e.isOpen || e._select(n, t)),
                    e.focusIndex = n.index,
                    e.button.attr("aria-activedescendant", e.menuItems.eq(n.index).attr("id"))
                }
            }).menu("instance"),
            this.menu.addClass("ui-corner-bottom").removeClass("ui-corner-all"),
            this.menuInstance._off(this.menu, "mouseleave"),
            this.menuInstance._closeOnDocumentClick = function() {
                return ! 1
            },
            this.menuInstance._isDivider = function() {
                return ! 1
            }
        },
        refresh: function() {
            this._refreshMenu(),
            this._setText(this.buttonText, this._getSelectedItem().text()),
            this.options.width || this._resizeButton()
        },
        _refreshMenu: function() {
            this.menu.empty();
            var t, e = this.element.find("option");
            e.length && (this._parseOptions(e), this._renderMenu(this.menu, this.items), this.menuInstance.refresh(), this.menuItems = this.menu.find("li").not(".ui-selectmenu-optgroup"), t = this._getSelectedItem(), this.menuInstance.focus(null, t), this._setAria(t.data("ui-selectmenu-item")), this._setOption("disabled", this.element.prop("disabled")))
        },
        open: function(t) {
            this.options.disabled || (this.menuItems ? (this.menu.find(".ui-state-focus").removeClass("ui-state-focus"), this.menuInstance.focus(null, this._getSelectedItem())) : this._refreshMenu(), this.isOpen = !0, this._toggleAttr(), this._resizeMenu(), this._position(), this._on(this.document, this._documentClick), this._trigger("open", t))
        },
        _position: function() {
            this.menuWrap.position(t.extend({
                of: this.button
            },
            this.options.position))
        },
        close: function(t) {
            this.isOpen && (this.isOpen = !1, this._toggleAttr(), this.range = null, this._off(this.document), this._trigger("close", t))
        },
        widget: function() {
            return this.button
        },
        menuWidget: function() {
            return this.menu
        },
        _renderMenu: function(e, i) {
            var n = this,
            s = "";
            t.each(i,
            function(i, o) {
                o.optgroup !== s && (t("<li>", {
                    "class": "ui-selectmenu-optgroup ui-menu-divider" + (o.element.parent("optgroup").prop("disabled") ? " ui-state-disabled": ""),
                    text: o.optgroup
                }).appendTo(e), s = o.optgroup),
                n._renderItemData(e, o)
            })
        },
        _renderItemData: function(t, e) {
            return this._renderItem(t, e).data("ui-selectmenu-item", e)
        },
        _renderItem: function(e, i) {
            var n = t("<li>");
            return i.disabled && n.addClass("ui-state-disabled"),
            this._setText(n, i.label),
            n.appendTo(e)
        },
        _setText: function(t, e) {
            e ? t.text(e) : t.html("&#160;")
        },
        _move: function(t, e) {
            var i, n, s = ".ui-menu-item";
            this.isOpen ? i = this.menuItems.eq(this.focusIndex) : (i = this.menuItems.eq(this.element[0].selectedIndex), s += ":not(.ui-state-disabled)"),
            n = "first" === t || "last" === t ? i["first" === t ? "prevAll": "nextAll"](s).eq( - 1) : i[t + "All"](s).eq(0),
            n.length && this.menuInstance.focus(e, n)
        },
        _getSelectedItem: function() {
            return this.menuItems.eq(this.element[0].selectedIndex)
        },
        _toggle: function(t) {
            this[this.isOpen ? "close": "open"](t)
        },
        _setSelection: function() {
            var t;
            this.range && (window.getSelection ? (t = window.getSelection(), t.removeAllRanges(), t.addRange(this.range)) : this.range.select(), this.button.focus())
        },
        _documentClick: {
            mousedown: function(e) {
                this.isOpen && (t(e.target).closest(".ui-selectmenu-menu, #" + this.ids.button).length || this.close(e))
            }
        },
        _buttonEvents: {
            mousedown: function() {
                var t;
                window.getSelection ? (t = window.getSelection(), t.rangeCount && (this.range = t.getRangeAt(0))) : this.range = document.selection.createRange()
            },
            click: function(t) {
                this._setSelection(),
                this._toggle(t)
            },
            keydown: function(e) {
                var i = !0;
                switch (e.keyCode) {
                case t.ui.keyCode.TAB:
                case t.ui.keyCode.ESCAPE:
                    this.close(e),
                    i = !1;
                    break;
                case t.ui.keyCode.ENTER:
                    this.isOpen && this._selectFocusedItem(e);
                    break;
                case t.ui.keyCode.UP:
                    e.altKey ? this._toggle(e) : this._move("prev", e);
                    break;
                case t.ui.keyCode.DOWN:
                    e.altKey ? this._toggle(e) : this._move("next", e);
                    break;
                case t.ui.keyCode.SPACE:
                    this.isOpen ? this._selectFocusedItem(e) : this._toggle(e);
                    break;
                case t.ui.keyCode.LEFT:
                    this._move("prev", e);
                    break;
                case t.ui.keyCode.RIGHT:
                    this._move("next", e);
                    break;
                case t.ui.keyCode.HOME:
                case t.ui.keyCode.PAGE_UP:
                    this._move("first", e);
                    break;
                case t.ui.keyCode.END:
                case t.ui.keyCode.PAGE_DOWN:
                    this._move("last", e);
                    break;
                default:
                    this.menu.trigger(e),
                    i = !1
                }
                i && e.preventDefault()
            }
        },
        _selectFocusedItem: function(t) {
            var e = this.menuItems.eq(this.focusIndex);
            e.hasClass("ui-state-disabled") || this._select(e.data("ui-selectmenu-item"), t)
        },
        _select: function(t, e) {
            var i = this.element[0].selectedIndex;
            this.element[0].selectedIndex = t.index,
            this._setText(this.buttonText, t.label),
            this._setAria(t),
            this._trigger("select", e, {
                item: t
            }),
            t.index !== i && this._trigger("change", e, {
                item: t
            }),
            this.close(e)
        },
        _setAria: function(t) {
            var e = this.menuItems.eq(t.index).attr("id");
            this.button.attr({
                "aria-labelledby": e,
                "aria-activedescendant": e
            }),
            this.menu.attr("aria-activedescendant", e)
        },
        _setOption: function(t, e) {
            "icons" === t && this.button.find("span.ui-icon").removeClass(this.options.icons.button).addClass(e.button),
            this._super(t, e),
            "appendTo" === t && this.menuWrap.appendTo(this._appendTo()),
            "disabled" === t && (this.menuInstance.option("disabled", e), this.button.toggleClass("ui-state-disabled", e).attr("aria-disabled", e), this.element.prop("disabled", e), e ? (this.button.attr("tabindex", -1), this.close()) : this.button.attr("tabindex", 0)),
            "width" === t && this._resizeButton()
        },
        _appendTo: function() {
            var e = this.options.appendTo;
            return e && (e = e.jquery || e.nodeType ? t(e) : this.document.find(e).eq(0)),
            e && e[0] || (e = this.element.closest(".ui-front")),
            e.length || (e = this.document[0].body),
            e
        },
        _toggleAttr: function() {
            this.button.toggleClass("ui-corner-top", this.isOpen).toggleClass("ui-corner-all", !this.isOpen).attr("aria-expanded", this.isOpen),
            this.menuWrap.toggleClass("ui-selectmenu-open", this.isOpen),
            this.menu.attr("aria-hidden", !this.isOpen)
        },
        _resizeButton: function() {
            var t = this.options.width;
            t || (t = this.element.show().outerWidth(), this.element.hide()),
            this.button.outerWidth(t)
        },
        _resizeMenu: function() {
            this.menu.outerWidth(Math.max(this.button.outerWidth(), this.menu.width("").outerWidth() + 1))
        },
        _getCreateOptions: function() {
            return {
                disabled: this.element.prop("disabled")
            }
        },
        _parseOptions: function(e) {
            var i = [];
            e.each(function(e, n) {
                var s = t(n),
                o = s.parent("optgroup");
                i.push({
                    element: s,
                    index: e,
                    value: s.val(),
                    label: s.text(),
                    optgroup: o.attr("label") || "",
                    disabled: o.prop("disabled") || s.prop("disabled")
                })
            }),
            this.items = i
        },
        _destroy: function() {
            this.menuWrap.remove(),
            this.button.remove(),
            this.element.show(),
            this.element.removeUniqueId(),
            this.label.attr("for", this.ids.element)
        }
    }),
    t.widget("ui.slider", t.ui.mouse, {
        version: "1.11.4",
        widgetEventPrefix: "slide",
        options: {
            animate: !1,
            distance: 0,
            max: 100,
            min: 0,
            orientation: "horizontal",
            range: !1,
            step: 1,
            value: 0,
            values: null,
            change: null,
            slide: null,
            start: null,
            stop: null
        },
        numPages: 5,
        _create: function() {
            this._keySliding = !1,
            this._mouseSliding = !1,
            this._animateOff = !0,
            this._handleIndex = null,
            this._detectOrientation(),
            this._mouseInit(),
            this._calculateNewMax(),
            this.element.addClass("ui-slider ui-slider-" + this.orientation + " ui-widget ui-widget-content ui-corner-all"),
            this._refresh(),
            this._setOption("disabled", this.options.disabled),
            this._animateOff = !1
        },
        _refresh: function() {
            this._createRange(),
            this._createHandles(),
            this._setupEvents(),
            this._refreshValue()
        },
        _createHandles: function() {
            var e, i, n = this.options,
            s = this.element.find(".ui-slider-handle").addClass("ui-state-default ui-corner-all"),
            o = "<span class='ui-slider-handle ui-state-default ui-corner-all' tabindex='0'></span>",
            r = [];
            for (i = n.values && n.values.length || 1, s.length > i && (s.slice(i).remove(), s = s.slice(0, i)), e = s.length; i > e; e++) r.push(o);
            this.handles = s.add(t(r.join("")).appendTo(this.element)),
            this.handle = this.handles.eq(0),
            this.handles.each(function(e) {
                t(this).data("ui-slider-handle-index", e)
            })
        },
        _createRange: function() {
            var e = this.options,
            i = "";
            e.range ? (e.range === !0 && (e.values ? e.values.length && 2 !== e.values.length ? e.values = [e.values[0], e.values[0]] : t.isArray(e.values) && (e.values = e.values.slice(0)) : e.values = [this._valueMin(), this._valueMin()]), this.range && this.range.length ? this.range.removeClass("ui-slider-range-min ui-slider-range-max").css({
                left: "",
                bottom: ""
            }) : (this.range = t("<div></div>").appendTo(this.element), i = "ui-slider-range ui-widget-header ui-corner-all"), this.range.addClass(i + ("min" === e.range || "max" === e.range ? " ui-slider-range-" + e.range: ""))) : (this.range && this.range.remove(), this.range = null)
        },
        _setupEvents: function() {
            this._off(this.handles),
            this._on(this.handles, this._handleEvents),
            this._hoverable(this.handles),
            this._focusable(this.handles)
        },
        _destroy: function() {
            this.handles.remove(),
            this.range && this.range.remove(),
            this.element.removeClass("ui-slider ui-slider-horizontal ui-slider-vertical ui-widget ui-widget-content ui-corner-all"),
            this._mouseDestroy()
        },
        _mouseCapture: function(e) {
            var i, n, s, o, r, a, l, c, h = this,
            u = this.options;
            return u.disabled ? !1 : (this.elementSize = {
                width: this.element.outerWidth(),
                height: this.element.outerHeight()
            },
            this.elementOffset = this.element.offset(), i = {
                x: e.pageX,
                y: e.pageY
            },
            n = this._normValueFromMouse(i), s = this._valueMax() - this._valueMin() + 1, this.handles.each(function(e) {
                var i = Math.abs(n - h.values(e)); (s > i || s === i && (e === h._lastChangedValue || h.values(e) === u.min)) && (s = i, o = t(this), r = e)
            }), a = this._start(e, r), a === !1 ? !1 : (this._mouseSliding = !0, this._handleIndex = r, o.addClass("ui-state-active").focus(), l = o.offset(), c = !t(e.target).parents().addBack().is(".ui-slider-handle"), this._clickOffset = c ? {
                left: 0,
                top: 0
            }: {
                left: e.pageX - l.left - o.width() / 2,
                top: e.pageY - l.top - o.height() / 2 - (parseInt(o.css("borderTopWidth"), 10) || 0) - (parseInt(o.css("borderBottomWidth"), 10) || 0) + (parseInt(o.css("marginTop"), 10) || 0)
            },
            this.handles.hasClass("ui-state-hover") || this._slide(e, r, n), this._animateOff = !0, !0))
        },
        _mouseStart: function() {
            return ! 0
        },
        _mouseDrag: function(t) {
            var e = {
                x: t.pageX,
                y: t.pageY
            },
            i = this._normValueFromMouse(e);
            return this._slide(t, this._handleIndex, i),
            !1
        },
        _mouseStop: function(t) {
            return this.handles.removeClass("ui-state-active"),
            this._mouseSliding = !1,
            this._stop(t, this._handleIndex),
            this._change(t, this._handleIndex),
            this._handleIndex = null,
            this._clickOffset = null,
            this._animateOff = !1,
            !1
        },
        _detectOrientation: function() {
            this.orientation = "vertical" === this.options.orientation ? "vertical": "horizontal"
        },
        _normValueFromMouse: function(t) {
            var e, i, n, s, o;
            return "horizontal" === this.orientation ? (e = this.elementSize.width, i = t.x - this.elementOffset.left - (this._clickOffset ? this._clickOffset.left: 0)) : (e = this.elementSize.height, i = t.y - this.elementOffset.top - (this._clickOffset ? this._clickOffset.top: 0)),
            n = i / e,
            n > 1 && (n = 1),
            0 > n && (n = 0),
            "vertical" === this.orientation && (n = 1 - n),
            s = this._valueMax() - this._valueMin(),
            o = this._valueMin() + n * s,
            this._trimAlignValue(o)
        },
        _start: function(t, e) {
            var i = {
                handle: this.handles[e],
                value: this.value()
            };
            return this.options.values && this.options.values.length && (i.value = this.values(e), i.values = this.values()),
            this._trigger("start", t, i)
        },
        _slide: function(t, e, i) {
            var n, s, o;
            this.options.values && this.options.values.length ? (n = this.values(e ? 0 : 1), 2 === this.options.values.length && this.options.range === !0 && (0 === e && i > n || 1 === e && n > i) && (i = n), i !== this.values(e) && (s = this.values(), s[e] = i, o = this._trigger("slide", t, {
                handle: this.handles[e],
                value: i,
                values: s
            }), n = this.values(e ? 0 : 1), o !== !1 && this.values(e, i))) : i !== this.value() && (o = this._trigger("slide", t, {
                handle: this.handles[e],
                value: i
            }), o !== !1 && this.value(i))
        },
        _stop: function(t, e) {
            var i = {
                handle: this.handles[e],
                value: this.value()
            };
            this.options.values && this.options.values.length && (i.value = this.values(e), i.values = this.values()),
            this._trigger("stop", t, i)
        },
        _change: function(t, e) {
            if (!this._keySliding && !this._mouseSliding) {
                var i = {
                    handle: this.handles[e],
                    value: this.value()
                };
                this.options.values && this.options.values.length && (i.value = this.values(e), i.values = this.values()),
                this._lastChangedValue = e,
                this._trigger("change", t, i)
            }
        },
        value: function(t) {
            return arguments.length ? (this.options.value = this._trimAlignValue(t), this._refreshValue(), void this._change(null, 0)) : this._value()
        },
        values: function(e, i) {
            var n, s, o;
            if (arguments.length > 1) return this.options.values[e] = this._trimAlignValue(i),
            this._refreshValue(),
            void this._change(null, e);
            if (!arguments.length) return this._values();
            if (!t.isArray(arguments[0])) return this.options.values && this.options.values.length ? this._values(e) : this.value();
            for (n = this.options.values, s = arguments[0], o = 0; o < n.length; o += 1) n[o] = this._trimAlignValue(s[o]),
            this._change(null, o);
            this._refreshValue()
        },
        _setOption: function(e, i) {
            var n, s = 0;
            switch ("range" === e && this.options.range === !0 && ("min" === i ? (this.options.value = this._values(0), this.options.values = null) : "max" === i && (this.options.value = this._values(this.options.values.length - 1), this.options.values = null)), t.isArray(this.options.values) && (s = this.options.values.length), "disabled" === e && this.element.toggleClass("ui-state-disabled", !!i), this._super(e, i), e) {
            case "orientation":
                this._detectOrientation(),
                this.element.removeClass("ui-slider-horizontal ui-slider-vertical").addClass("ui-slider-" + this.orientation),
                this._refreshValue(),
                this.handles.css("horizontal" === i ? "bottom": "left", "");
                break;
            case "value":
                this._animateOff = !0,
                this._refreshValue(),
                this._change(null, 0),
                this._animateOff = !1;
                break;
            case "values":
                for (this._animateOff = !0, this._refreshValue(), n = 0; s > n; n += 1) this._change(null, n);
                this._animateOff = !1;
                break;
            case "step":
            case "min":
            case "max":
                this._animateOff = !0,
                this._calculateNewMax(),
                this._refreshValue(),
                this._animateOff = !1;
                break;
            case "range":
                this._animateOff = !0,
                this._refresh(),
                this._animateOff = !1
            }
        },
        _value: function() {
            var t = this.options.value;
            return t = this._trimAlignValue(t)
        },
        _values: function(t) {
            var e, i, n;
            if (arguments.length) return e = this.options.values[t],
            e = this._trimAlignValue(e);
            if (this.options.values && this.options.values.length) {
                for (i = this.options.values.slice(), n = 0; n < i.length; n += 1) i[n] = this._trimAlignValue(i[n]);
                return i
            }
            return []
        },
        _trimAlignValue: function(t) {
            if (t <= this._valueMin()) return this._valueMin();
            if (t >= this._valueMax()) return this._valueMax();
            var e = this.options.step > 0 ? this.options.step: 1,
            i = (t - this._valueMin()) % e,
            n = t - i;
            return 2 * Math.abs(i) >= e && (n += i > 0 ? e: -e),
            parseFloat(n.toFixed(5))
        },
        _calculateNewMax: function() {
            var t = this.options.max,
            e = this._valueMin(),
            i = this.options.step,
            n = Math.floor( + (t - e).toFixed(this._precision()) / i) * i;
            t = n + e,
            this.max = parseFloat(t.toFixed(this._precision()))
        },
        _precision: function() {
            var t = this._precisionOf(this.options.step);
            return null !== this.options.min && (t = Math.max(t, this._precisionOf(this.options.min))),
            t
        },
        _precisionOf: function(t) {
            var e = t.toString(),
            i = e.indexOf(".");
            return - 1 === i ? 0 : e.length - i - 1
        },
        _valueMin: function() {
            return this.options.min
        },
        _valueMax: function() {
            return this.max
        },
        _refreshValue: function() {
            var e, i, n, s, o, r = this.options.range,
            a = this.options,
            l = this,
            c = this._animateOff ? !1 : a.animate,
            h = {};
            this.options.values && this.options.values.length ? this.handles.each(function(n) {
                i = (l.values(n) - l._valueMin()) / (l._valueMax() - l._valueMin()) * 100,
                h["horizontal" === l.orientation ? "left": "bottom"] = i + "%",
                t(this).stop(1, 1)[c ? "animate": "css"](h, a.animate),
                l.options.range === !0 && ("horizontal" === l.orientation ? (0 === n && l.range.stop(1, 1)[c ? "animate": "css"]({
                    left: i + "%"
                },
                a.animate), 1 === n && l.range[c ? "animate": "css"]({
                    width: i - e + "%"
                },
                {
                    queue: !1,
                    duration: a.animate
                })) : (0 === n && l.range.stop(1, 1)[c ? "animate": "css"]({
                    bottom: i + "%"
                },
                a.animate), 1 === n && l.range[c ? "animate": "css"]({
                    height: i - e + "%"
                },
                {
                    queue: !1,
                    duration: a.animate
                }))),
                e = i
            }) : (n = this.value(), s = this._valueMin(), o = this._valueMax(), i = o !== s ? (n - s) / (o - s) * 100 : 0, h["horizontal" === this.orientation ? "left": "bottom"] = i + "%", this.handle.stop(1, 1)[c ? "animate": "css"](h, a.animate), "min" === r && "horizontal" === this.orientation && this.range.stop(1, 1)[c ? "animate": "css"]({
                width: i + "%"
            },
            a.animate), "max" === r && "horizontal" === this.orientation && this.range[c ? "animate": "css"]({
                width: 100 - i + "%"
            },
            {
                queue: !1,
                duration: a.animate
            }), "min" === r && "vertical" === this.orientation && this.range.stop(1, 1)[c ? "animate": "css"]({
                height: i + "%"
            },
            a.animate), "max" === r && "vertical" === this.orientation && this.range[c ? "animate": "css"]({
                height: 100 - i + "%"
            },
            {
                queue: !1,
                duration: a.animate
            }))
        },
        _handleEvents: {
            keydown: function(e) {
                var i, n, s, o, r = t(e.target).data("ui-slider-handle-index");
                switch (e.keyCode) {
                case t.ui.keyCode.HOME:
                case t.ui.keyCode.END:
                case t.ui.keyCode.PAGE_UP:
                case t.ui.keyCode.PAGE_DOWN:
                case t.ui.keyCode.UP:
                case t.ui.keyCode.RIGHT:
                case t.ui.keyCode.DOWN:
                case t.ui.keyCode.LEFT:
                    if (e.preventDefault(), !this._keySliding && (this._keySliding = !0, t(e.target).addClass("ui-state-active"), i = this._start(e, r), i === !1)) return
                }
                switch (o = this.options.step, n = s = this.options.values && this.options.values.length ? this.values(r) : this.value(), e.keyCode) {
                case t.ui.keyCode.HOME:
                    s = this._valueMin();
                    break;
                case t.ui.keyCode.END:
                    s = this._valueMax();
                    break;
                case t.ui.keyCode.PAGE_UP:
                    s = this._trimAlignValue(n + (this._valueMax() - this._valueMin()) / this.numPages);
                    break;
                case t.ui.keyCode.PAGE_DOWN:
                    s = this._trimAlignValue(n - (this._valueMax() - this._valueMin()) / this.numPages);
                    break;
                case t.ui.keyCode.UP:
                case t.ui.keyCode.RIGHT:
                    if (n === this._valueMax()) return;
                    s = this._trimAlignValue(n + o);
                    break;
                case t.ui.keyCode.DOWN:
                case t.ui.keyCode.LEFT:
                    if (n === this._valueMin()) return;
                    s = this._trimAlignValue(n - o)
                }
                this._slide(e, r, s)
            },
            keyup: function(e) {
                var i = t(e.target).data("ui-slider-handle-index");
                this._keySliding && (this._keySliding = !1, this._stop(e, i), this._change(e, i), t(e.target).removeClass("ui-state-active"))
            }
        }
    }),
    t.widget("ui.sortable", t.ui.mouse, {
        version: "1.11.4",
        widgetEventPrefix: "sort",
        ready: !1,
        options: {
            appendTo: "parent",
            axis: !1,
            connectWith: !1,
            containment: !1,
            cursor: "auto",
            cursorAt: !1,
            dropOnEmpty: !0,
            forcePlaceholderSize: !1,
            forceHelperSize: !1,
            grid: !1,
            handle: !1,
            helper: "original",
            items: "> *",
            opacity: !1,
            placeholder: !1,
            revert: !1,
            scroll: !0,
            scrollSensitivity: 20,
            scrollSpeed: 20,
            scope: "default",
            tolerance: "intersect",
            zIndex: 1e3,
            activate: null,
            beforeStop: null,
            change: null,
            deactivate: null,
            out: null,
            over: null,
            receive: null,
            remove: null,
            sort: null,
            start: null,
            stop: null,
            update: null
        },
        _isOverAxis: function(t, e, i) {
            return t >= e && e + i > t
        },
        _isFloating: function(t) {
            return /left|right/.test(t.css("float")) || /inline|table-cell/.test(t.css("display"))
        },
        _create: function() {
            this.containerCache = {},
            this.element.addClass("ui-sortable"),
            this.refresh(),
            this.offset = this.element.offset(),
            this._mouseInit(),
            this._setHandleClassName(),
            this.ready = !0
        },
        _setOption: function(t, e) {
            this._super(t, e),
            "handle" === t && this._setHandleClassName()
        },
        _setHandleClassName: function() {
            this.element.find(".ui-sortable-handle").removeClass("ui-sortable-handle"),
            t.each(this.items,
            function() { (this.instance.options.handle ? this.item.find(this.instance.options.handle) : this.item).addClass("ui-sortable-handle")
            })
        },
        _destroy: function() {
            this.element.removeClass("ui-sortable ui-sortable-disabled").find(".ui-sortable-handle").removeClass("ui-sortable-handle"),
            this._mouseDestroy();
            for (var t = this.items.length - 1; t >= 0; t--) this.items[t].item.removeData(this.widgetName + "-item");
            return this
        },
        _mouseCapture: function(e, i) {
            var n = null,
            s = !1,
            o = this;
            return this.reverting ? !1 : this.options.disabled || "static" === this.options.type ? !1 : (this._refreshItems(e), t(e.target).parents().each(function() {
                return t.data(this, o.widgetName + "-item") === o ? (n = t(this), !1) : void 0
            }), t.data(e.target, o.widgetName + "-item") === o && (n = t(e.target)), n && (!this.options.handle || i || (t(this.options.handle, n).find("*").addBack().each(function() {
                this === e.target && (s = !0)
            }), s)) ? (this.currentItem = n, this._removeCurrentsFromItems(), !0) : !1)
        },
        _mouseStart: function(e, i, n) {
            var s, o, r = this.options;
            if (this.currentContainer = this, this.refreshPositions(), this.helper = this._createHelper(e), this._cacheHelperProportions(), this._cacheMargins(), this.scrollParent = this.helper.scrollParent(), this.offset = this.currentItem.offset(), this.offset = {
                top: this.offset.top - this.margins.top,
                left: this.offset.left - this.margins.left
            },
            t.extend(this.offset, {
                click: {
                    left: e.pageX - this.offset.left,
                    top: e.pageY - this.offset.top
                },
                parent: this._getParentOffset(),
                relative: this._getRelativeOffset()
            }), this.helper.css("position", "absolute"), this.cssPosition = this.helper.css("position"), this.originalPosition = this._generatePosition(e), this.originalPageX = e.pageX, this.originalPageY = e.pageY, r.cursorAt && this._adjustOffsetFromHelper(r.cursorAt), this.domPosition = {
                prev: this.currentItem.prev()[0],
                parent: this.currentItem.parent()[0]
            },
            this.helper[0] !== this.currentItem[0] && this.currentItem.hide(), this._createPlaceholder(), r.containment && this._setContainment(), r.cursor && "auto" !== r.cursor && (o = this.document.find("body"), this.storedCursor = o.css("cursor"), o.css("cursor", r.cursor), this.storedStylesheet = t("<style>*{ cursor: " + r.cursor + " !important; }</style>").appendTo(o)), r.opacity && (this.helper.css("opacity") && (this._storedOpacity = this.helper.css("opacity")), this.helper.css("opacity", r.opacity)), r.zIndex && (this.helper.css("zIndex") && (this._storedZIndex = this.helper.css("zIndex")), this.helper.css("zIndex", r.zIndex)), this.scrollParent[0] !== this.document[0] && "HTML" !== this.scrollParent[0].tagName && (this.overflowOffset = this.scrollParent.offset()), this._trigger("start", e, this._uiHash()), this._preserveHelperProportions || this._cacheHelperProportions(), !n) for (s = this.containers.length - 1; s >= 0; s--) this.containers[s]._trigger("activate", e, this._uiHash(this));
            return t.ui.ddmanager && (t.ui.ddmanager.current = this),
            t.ui.ddmanager && !r.dropBehaviour && t.ui.ddmanager.prepareOffsets(this, e),
            this.dragging = !0,
            this.helper.addClass("ui-sortable-helper"),
            this._mouseDrag(e),
            !0
        },
        _mouseDrag: function(e) {
            var i, n, s, o, r = this.options,
            a = !1;
            for (this.position = this._generatePosition(e), this.positionAbs = this._convertPositionTo("absolute"), this.lastPositionAbs || (this.lastPositionAbs = this.positionAbs), this.options.scroll && (this.scrollParent[0] !== this.document[0] && "HTML" !== this.scrollParent[0].tagName ? (this.overflowOffset.top + this.scrollParent[0].offsetHeight - e.pageY < r.scrollSensitivity ? this.scrollParent[0].scrollTop = a = this.scrollParent[0].scrollTop + r.scrollSpeed: e.pageY - this.overflowOffset.top < r.scrollSensitivity && (this.scrollParent[0].scrollTop = a = this.scrollParent[0].scrollTop - r.scrollSpeed), this.overflowOffset.left + this.scrollParent[0].offsetWidth - e.pageX < r.scrollSensitivity ? this.scrollParent[0].scrollLeft = a = this.scrollParent[0].scrollLeft + r.scrollSpeed: e.pageX - this.overflowOffset.left < r.scrollSensitivity && (this.scrollParent[0].scrollLeft = a = this.scrollParent[0].scrollLeft - r.scrollSpeed)) : (e.pageY - this.document.scrollTop() < r.scrollSensitivity ? a = this.document.scrollTop(this.document.scrollTop() - r.scrollSpeed) : this.window.height() - (e.pageY - this.document.scrollTop()) < r.scrollSensitivity && (a = this.document.scrollTop(this.document.scrollTop() + r.scrollSpeed)), e.pageX - this.document.scrollLeft() < r.scrollSensitivity ? a = this.document.scrollLeft(this.document.scrollLeft() - r.scrollSpeed) : this.window.width() - (e.pageX - this.document.scrollLeft()) < r.scrollSensitivity && (a = this.document.scrollLeft(this.document.scrollLeft() + r.scrollSpeed))), a !== !1 && t.ui.ddmanager && !r.dropBehaviour && t.ui.ddmanager.prepareOffsets(this, e)), this.positionAbs = this._convertPositionTo("absolute"), this.options.axis && "y" === this.options.axis || (this.helper[0].style.left = this.position.left + "px"), this.options.axis && "x" === this.options.axis || (this.helper[0].style.top = this.position.top + "px"), i = this.items.length - 1; i >= 0; i--) if (n = this.items[i], s = n.item[0], o = this._intersectsWithPointer(n), o && n.instance === this.currentContainer && s !== this.currentItem[0] && this.placeholder[1 === o ? "next": "prev"]()[0] !== s && !t.contains(this.placeholder[0], s) && ("semi-dynamic" === this.options.type ? !t.contains(this.element[0], s) : !0)) {
                if (this.direction = 1 === o ? "down": "up", "pointer" !== this.options.tolerance && !this._intersectsWithSides(n)) break;
                this._rearrange(e, n),
                this._trigger("change", e, this._uiHash());
                break
            }
            return this._contactContainers(e),
            t.ui.ddmanager && t.ui.ddmanager.drag(this, e),
            this._trigger("sort", e, this._uiHash()),
            this.lastPositionAbs = this.positionAbs,
            !1
        },
        _mouseStop: function(e, i) {
            if (e) {
                if (t.ui.ddmanager && !this.options.dropBehaviour && t.ui.ddmanager.drop(this, e), this.options.revert) {
                    var n = this,
                    s = this.placeholder.offset(),
                    o = this.options.axis,
                    r = {};
                    o && "x" !== o || (r.left = s.left - this.offset.parent.left - this.margins.left + (this.offsetParent[0] === this.document[0].body ? 0 : this.offsetParent[0].scrollLeft)),
                    o && "y" !== o || (r.top = s.top - this.offset.parent.top - this.margins.top + (this.offsetParent[0] === this.document[0].body ? 0 : this.offsetParent[0].scrollTop)),
                    this.reverting = !0,
                    t(this.helper).animate(r, parseInt(this.options.revert, 10) || 500,
                    function() {
                        n._clear(e)
                    })
                } else this._clear(e, i);
                return ! 1
            }
        },
        cancel: function() {
            if (this.dragging) {
                this._mouseUp({
                    target: null
                }),
                "original" === this.options.helper ? this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper") : this.currentItem.show();
                for (var e = this.containers.length - 1; e >= 0; e--) this.containers[e]._trigger("deactivate", null, this._uiHash(this)),
                this.containers[e].containerCache.over && (this.containers[e]._trigger("out", null, this._uiHash(this)), this.containers[e].containerCache.over = 0)
            }
            return this.placeholder && (this.placeholder[0].parentNode && this.placeholder[0].parentNode.removeChild(this.placeholder[0]), "original" !== this.options.helper && this.helper && this.helper[0].parentNode && this.helper.remove(), t.extend(this, {
                helper: null,
                dragging: !1,
                reverting: !1,
                _noFinalSort: null
            }), this.domPosition.prev ? t(this.domPosition.prev).after(this.currentItem) : t(this.domPosition.parent).prepend(this.currentItem)),
            this
        },
        serialize: function(e) {
            var i = this._getItemsAsjQuery(e && e.connected),
            n = [];
            return e = e || {},
            t(i).each(function() {
                var i = (t(e.item || this).attr(e.attribute || "id") || "").match(e.expression || /(.+)[\-=_](.+)/);
                i && n.push((e.key || i[1] + "[]") + "=" + (e.key && e.expression ? i[1] : i[2]))
            }),
            !n.length && e.key && n.push(e.key + "="),
            n.join("&")
        },
        toArray: function(e) {
            var i = this._getItemsAsjQuery(e && e.connected),
            n = [];
            return e = e || {},
            i.each(function() {
                n.push(t(e.item || this).attr(e.attribute || "id") || "")
            }),
            n
        },
        _intersectsWith: function(t) {
            var e = this.positionAbs.left,
            i = e + this.helperProportions.width,
            n = this.positionAbs.top,
            s = n + this.helperProportions.height,
            o = t.left,
            r = o + t.width,
            a = t.top,
            l = a + t.height,
            c = this.offset.click.top,
            h = this.offset.click.left,
            u = "x" === this.options.axis || n + c > a && l > n + c,
            d = "y" === this.options.axis || e + h > o && r > e + h,
            p = u && d;
            return "pointer" === this.options.tolerance || this.options.forcePointerForContainers || "pointer" !== this.options.tolerance && this.helperProportions[this.floating ? "width": "height"] > t[this.floating ? "width": "height"] ? p: o < e + this.helperProportions.width / 2 && i - this.helperProportions.width / 2 < r && a < n + this.helperProportions.height / 2 && s - this.helperProportions.height / 2 < l
        },
        _intersectsWithPointer: function(t) {
            var e = "x" === this.options.axis || this._isOverAxis(this.positionAbs.top + this.offset.click.top, t.top, t.height),
            i = "y" === this.options.axis || this._isOverAxis(this.positionAbs.left + this.offset.click.left, t.left, t.width),
            n = e && i,
            s = this._getDragVerticalDirection(),
            o = this._getDragHorizontalDirection();
            return n ? this.floating ? o && "right" === o || "down" === s ? 2 : 1 : s && ("down" === s ? 2 : 1) : !1
        },
        _intersectsWithSides: function(t) {
            var e = this._isOverAxis(this.positionAbs.top + this.offset.click.top, t.top + t.height / 2, t.height),
            i = this._isOverAxis(this.positionAbs.left + this.offset.click.left, t.left + t.width / 2, t.width),
            n = this._getDragVerticalDirection(),
            s = this._getDragHorizontalDirection();
            return this.floating && s ? "right" === s && i || "left" === s && !i: n && ("down" === n && e || "up" === n && !e)
        },
        _getDragVerticalDirection: function() {
            var t = this.positionAbs.top - this.lastPositionAbs.top;
            return 0 !== t && (t > 0 ? "down": "up")
        },
        _getDragHorizontalDirection: function() {
            var t = this.positionAbs.left - this.lastPositionAbs.left;
            return 0 !== t && (t > 0 ? "right": "left")
        },
        refresh: function(t) {
            return this._refreshItems(t),
            this._setHandleClassName(),
            this.refreshPositions(),
            this
        },
        _connectWith: function() {
            var t = this.options;
            return t.connectWith.constructor === String ? [t.connectWith] : t.connectWith
        },
        _getItemsAsjQuery: function(e) {
            function i() {
                a.push(this)
            }
            var n, s, o, r, a = [],
            l = [],
            c = this._connectWith();
            if (c && e) for (n = c.length - 1; n >= 0; n--) for (o = t(c[n], this.document[0]), s = o.length - 1; s >= 0; s--) r = t.data(o[s], this.widgetFullName),
            r && r !== this && !r.options.disabled && l.push([t.isFunction(r.options.items) ? r.options.items.call(r.element) : t(r.options.items, r.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"), r]);
            for (l.push([t.isFunction(this.options.items) ? this.options.items.call(this.element, null, {
                options: this.options,
                item: this.currentItem
            }) : t(this.options.items, this.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"), this]), n = l.length - 1; n >= 0; n--) l[n][0].each(i);
            return t(a)
        },
        _removeCurrentsFromItems: function() {
            var e = this.currentItem.find(":data(" + this.widgetName + "-item)");
            this.items = t.grep(this.items,
            function(t) {
                for (var i = 0; i < e.length; i++) if (e[i] === t.item[0]) return ! 1;
                return ! 0
            })
        },
        _refreshItems: function(e) {
            this.items = [],
            this.containers = [this];
            var i, n, s, o, r, a, l, c, h = this.items,
            u = [[t.isFunction(this.options.items) ? this.options.items.call(this.element[0], e, {
                item: this.currentItem
            }) : t(this.options.items, this.element), this]],
            d = this._connectWith();
            if (d && this.ready) for (i = d.length - 1; i >= 0; i--) for (s = t(d[i], this.document[0]), n = s.length - 1; n >= 0; n--) o = t.data(s[n], this.widgetFullName),
            o && o !== this && !o.options.disabled && (u.push([t.isFunction(o.options.items) ? o.options.items.call(o.element[0], e, {
                item: this.currentItem
            }) : t(o.options.items, o.element), o]), this.containers.push(o));
            for (i = u.length - 1; i >= 0; i--) for (r = u[i][1], a = u[i][0], n = 0, c = a.length; c > n; n++) l = t(a[n]),
            l.data(this.widgetName + "-item", r),
            h.push({
                item: l,
                instance: r,
                width: 0,
                height: 0,
                left: 0,
                top: 0
            })
        },
        refreshPositions: function(e) {
            this.floating = this.items.length ? "x" === this.options.axis || this._isFloating(this.items[0].item) : !1,
            this.offsetParent && this.helper && (this.offset.parent = this._getParentOffset());
            var i, n, s, o;
            for (i = this.items.length - 1; i >= 0; i--) n = this.items[i],
            n.instance !== this.currentContainer && this.currentContainer && n.item[0] !== this.currentItem[0] || (s = this.options.toleranceElement ? t(this.options.toleranceElement, n.item) : n.item, e || (n.width = s.outerWidth(), n.height = s.outerHeight()), o = s.offset(), n.left = o.left, n.top = o.top);
            if (this.options.custom && this.options.custom.refreshContainers) this.options.custom.refreshContainers.call(this);
            else for (i = this.containers.length - 1; i >= 0; i--) o = this.containers[i].element.offset(),
            this.containers[i].containerCache.left = o.left,
            this.containers[i].containerCache.top = o.top,
            this.containers[i].containerCache.width = this.containers[i].element.outerWidth(),
            this.containers[i].containerCache.height = this.containers[i].element.outerHeight();
            return this
        },
        _createPlaceholder: function(e) {
            e = e || this;
            var i, n = e.options;
            n.placeholder && n.placeholder.constructor !== String || (i = n.placeholder, n.placeholder = {
                element: function() {
                    var n = e.currentItem[0].nodeName.toLowerCase(),
                    s = t("<" + n + ">", e.document[0]).addClass(i || e.currentItem[0].className + " ui-sortable-placeholder").removeClass("ui-sortable-helper");
                    return "tbody" === n ? e._createTrPlaceholder(e.currentItem.find("tr").eq(0), t("<tr>", e.document[0]).appendTo(s)) : "tr" === n ? e._createTrPlaceholder(e.currentItem, s) : "img" === n && s.attr("src", e.currentItem.attr("src")),
                    i || s.css("visibility", "hidden"),
                    s
                },
                update: function(t, s) { (!i || n.forcePlaceholderSize) && (s.height() || s.height(e.currentItem.innerHeight() - parseInt(e.currentItem.css("paddingTop") || 0, 10) - parseInt(e.currentItem.css("paddingBottom") || 0, 10)), s.width() || s.width(e.currentItem.innerWidth() - parseInt(e.currentItem.css("paddingLeft") || 0, 10) - parseInt(e.currentItem.css("paddingRight") || 0, 10)))
                }
            }),
            e.placeholder = t(n.placeholder.element.call(e.element, e.currentItem)),
            e.currentItem.after(e.placeholder),
            n.placeholder.update(e, e.placeholder)
        },
        _createTrPlaceholder: function(e, i) {
            var n = this;
            e.children().each(function() {
                t("<td>&#160;</td>", n.document[0]).attr("colspan", t(this).attr("colspan") || 1).appendTo(i)
            })
        },
        _contactContainers: function(e) {
            var i, n, s, o, r, a, l, c, h, u, d = null,
            p = null;
            for (i = this.containers.length - 1; i >= 0; i--) if (!t.contains(this.currentItem[0], this.containers[i].element[0])) if (this._intersectsWith(this.containers[i].containerCache)) {
                if (d && t.contains(this.containers[i].element[0], d.element[0])) continue;
                d = this.containers[i],
                p = i
            } else this.containers[i].containerCache.over && (this.containers[i]._trigger("out", e, this._uiHash(this)), this.containers[i].containerCache.over = 0);
            if (d) if (1 === this.containers.length) this.containers[p].containerCache.over || (this.containers[p]._trigger("over", e, this._uiHash(this)), this.containers[p].containerCache.over = 1);
            else {
                for (s = 1e4, o = null, h = d.floating || this._isFloating(this.currentItem), r = h ? "left": "top", a = h ? "width": "height", u = h ? "clientX": "clientY", n = this.items.length - 1; n >= 0; n--) t.contains(this.containers[p].element[0], this.items[n].item[0]) && this.items[n].item[0] !== this.currentItem[0] && (l = this.items[n].item.offset()[r], c = !1, e[u] - l > this.items[n][a] / 2 && (c = !0), Math.abs(e[u] - l) < s && (s = Math.abs(e[u] - l), o = this.items[n], this.direction = c ? "up": "down"));
                if (!o && !this.options.dropOnEmpty) return;
                if (this.currentContainer === this.containers[p]) return void(this.currentContainer.containerCache.over || (this.containers[p]._trigger("over", e, this._uiHash()), this.currentContainer.containerCache.over = 1));
                o ? this._rearrange(e, o, null, !0) : this._rearrange(e, null, this.containers[p].element, !0),
                this._trigger("change", e, this._uiHash()),
                this.containers[p]._trigger("change", e, this._uiHash(this)),
                this.currentContainer = this.containers[p],
                this.options.placeholder.update(this.currentContainer, this.placeholder),
                this.containers[p]._trigger("over", e, this._uiHash(this)),
                this.containers[p].containerCache.over = 1
            }
        },
        _createHelper: function(e) {
            var i = this.options,
            n = t.isFunction(i.helper) ? t(i.helper.apply(this.element[0], [e, this.currentItem])) : "clone" === i.helper ? this.currentItem.clone() : this.currentItem;
            return n.parents("body").length || t("parent" !== i.appendTo ? i.appendTo: this.currentItem[0].parentNode)[0].appendChild(n[0]),
            n[0] === this.currentItem[0] && (this._storedCSS = {
                width: this.currentItem[0].style.width,
                height: this.currentItem[0].style.height,
                position: this.currentItem.css("position"),
                top: this.currentItem.css("top"),
                left: this.currentItem.css("left")
            }),
            (!n[0].style.width || i.forceHelperSize) && n.width(this.currentItem.width()),
            (!n[0].style.height || i.forceHelperSize) && n.height(this.currentItem.height()),
            n
        },
        _adjustOffsetFromHelper: function(e) {
            "string" == typeof e && (e = e.split(" ")),
            t.isArray(e) && (e = {
                left: +e[0],
                top: +e[1] || 0
            }),
            "left" in e && (this.offset.click.left = e.left + this.margins.left),
            "right" in e && (this.offset.click.left = this.helperProportions.width - e.right + this.margins.left),
            "top" in e && (this.offset.click.top = e.top + this.margins.top),
            "bottom" in e && (this.offset.click.top = this.helperProportions.height - e.bottom + this.margins.top)
        },
        _getParentOffset: function() {
            this.offsetParent = this.helper.offsetParent();
            var e = this.offsetParent.offset();
            return "absolute" === this.cssPosition && this.scrollParent[0] !== this.document[0] && t.contains(this.scrollParent[0], this.offsetParent[0]) && (e.left += this.scrollParent.scrollLeft(), e.top += this.scrollParent.scrollTop()),
            (this.offsetParent[0] === this.document[0].body || this.offsetParent[0].tagName && "html" === this.offsetParent[0].tagName.toLowerCase() && t.ui.ie) && (e = {
                top: 0,
                left: 0
            }),
            {
                top: e.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
                left: e.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
            }
        },
        _getRelativeOffset: function() {
            if ("relative" === this.cssPosition) {
                var t = this.currentItem.position();
                return {
                    top: t.top - (parseInt(this.helper.css("top"), 10) || 0) + this.scrollParent.scrollTop(),
                    left: t.left - (parseInt(this.helper.css("left"), 10) || 0) + this.scrollParent.scrollLeft()
                }
            }
            return {
                top: 0,
                left: 0
            }
        },
        _cacheMargins: function() {
            this.margins = {
                left: parseInt(this.currentItem.css("marginLeft"), 10) || 0,
                top: parseInt(this.currentItem.css("marginTop"), 10) || 0
            }
        },
        _cacheHelperProportions: function() {
            this.helperProportions = {
                width: this.helper.outerWidth(),
                height: this.helper.outerHeight()
            }
        },
        _setContainment: function() {
            var e, i, n, s = this.options;
            "parent" === s.containment && (s.containment = this.helper[0].parentNode),
            ("document" === s.containment || "window" === s.containment) && (this.containment = [0 - this.offset.relative.left - this.offset.parent.left, 0 - this.offset.relative.top - this.offset.parent.top, "document" === s.containment ? this.document.width() : this.window.width() - this.helperProportions.width - this.margins.left, ("document" === s.containment ? this.document.width() : this.window.height() || this.document[0].body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top]),
            /^(document|window|parent)$/.test(s.containment) || (e = t(s.containment)[0], i = t(s.containment).offset(), n = "hidden" !== t(e).css("overflow"), this.containment = [i.left + (parseInt(t(e).css("borderLeftWidth"), 10) || 0) + (parseInt(t(e).css("paddingLeft"), 10) || 0) - this.margins.left, i.top + (parseInt(t(e).css("borderTopWidth"), 10) || 0) + (parseInt(t(e).css("paddingTop"), 10) || 0) - this.margins.top, i.left + (n ? Math.max(e.scrollWidth, e.offsetWidth) : e.offsetWidth) - (parseInt(t(e).css("borderLeftWidth"), 10) || 0) - (parseInt(t(e).css("paddingRight"), 10) || 0) - this.helperProportions.width - this.margins.left, i.top + (n ? Math.max(e.scrollHeight, e.offsetHeight) : e.offsetHeight) - (parseInt(t(e).css("borderTopWidth"), 10) || 0) - (parseInt(t(e).css("paddingBottom"), 10) || 0) - this.helperProportions.height - this.margins.top])
        },
        _convertPositionTo: function(e, i) {
            i || (i = this.position);
            var n = "absolute" === e ? 1 : -1,
            s = "absolute" !== this.cssPosition || this.scrollParent[0] !== this.document[0] && t.contains(this.scrollParent[0], this.offsetParent[0]) ? this.scrollParent: this.offsetParent,
            o = /(html|body)/i.test(s[0].tagName);
            return {
                top: i.top + this.offset.relative.top * n + this.offset.parent.top * n - ("fixed" === this.cssPosition ? -this.scrollParent.scrollTop() : o ? 0 : s.scrollTop()) * n,
                left: i.left + this.offset.relative.left * n + this.offset.parent.left * n - ("fixed" === this.cssPosition ? -this.scrollParent.scrollLeft() : o ? 0 : s.scrollLeft()) * n
            }
        },
        _generatePosition: function(e) {
            var i, n, s = this.options,
            o = e.pageX,
            r = e.pageY,
            a = "absolute" !== this.cssPosition || this.scrollParent[0] !== this.document[0] && t.contains(this.scrollParent[0], this.offsetParent[0]) ? this.scrollParent: this.offsetParent,
            l = /(html|body)/i.test(a[0].tagName);
            return "relative" !== this.cssPosition || this.scrollParent[0] !== this.document[0] && this.scrollParent[0] !== this.offsetParent[0] || (this.offset.relative = this._getRelativeOffset()),
            this.originalPosition && (this.containment && (e.pageX - this.offset.click.left < this.containment[0] && (o = this.containment[0] + this.offset.click.left), e.pageY - this.offset.click.top < this.containment[1] && (r = this.containment[1] + this.offset.click.top), e.pageX - this.offset.click.left > this.containment[2] && (o = this.containment[2] + this.offset.click.left), e.pageY - this.offset.click.top > this.containment[3] && (r = this.containment[3] + this.offset.click.top)), s.grid && (i = this.originalPageY + Math.round((r - this.originalPageY) / s.grid[1]) * s.grid[1], r = this.containment ? i - this.offset.click.top >= this.containment[1] && i - this.offset.click.top <= this.containment[3] ? i: i - this.offset.click.top >= this.containment[1] ? i - s.grid[1] : i + s.grid[1] : i, n = this.originalPageX + Math.round((o - this.originalPageX) / s.grid[0]) * s.grid[0], o = this.containment ? n - this.offset.click.left >= this.containment[0] && n - this.offset.click.left <= this.containment[2] ? n: n - this.offset.click.left >= this.containment[0] ? n - s.grid[0] : n + s.grid[0] : n)),
            {
                top: r - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + ("fixed" === this.cssPosition ? -this.scrollParent.scrollTop() : l ? 0 : a.scrollTop()),
                left: o - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + ("fixed" === this.cssPosition ? -this.scrollParent.scrollLeft() : l ? 0 : a.scrollLeft())
            }
        },
        _rearrange: function(t, e, i, n) {
            i ? i[0].appendChild(this.placeholder[0]) : e.item[0].parentNode.insertBefore(this.placeholder[0], "down" === this.direction ? e.item[0] : e.item[0].nextSibling),
            this.counter = this.counter ? ++this.counter: 1;
            var s = this.counter;
            this._delay(function() {
                s === this.counter && this.refreshPositions(!n)
            })
        },
        _clear: function(t, e) {
            function i(t, e, i) {
                return function(n) {
                    i._trigger(t, n, e._uiHash(e))
                }
            }
            this.reverting = !1;
            var n, s = [];
            if (!this._noFinalSort && this.currentItem.parent().length && this.placeholder.before(this.currentItem), this._noFinalSort = null, this.helper[0] === this.currentItem[0]) {
                for (n in this._storedCSS)("auto" === this._storedCSS[n] || "static" === this._storedCSS[n]) && (this._storedCSS[n] = "");
                this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")
            } else this.currentItem.show();
            for (this.fromOutside && !e && s.push(function(t) {
                this._trigger("receive", t, this._uiHash(this.fromOutside))
            }), !this.fromOutside && this.domPosition.prev === this.currentItem.prev().not(".ui-sortable-helper")[0] && this.domPosition.parent === this.currentItem.parent()[0] || e || s.push(function(t) {
                this._trigger("update", t, this._uiHash())
            }), this !== this.currentContainer && (e || (s.push(function(t) {
                this._trigger("remove", t, this._uiHash())
            }), s.push(function(t) {
                return function(e) {
                    t._trigger("receive", e, this._uiHash(this))
                }
            }.call(this, this.currentContainer)), s.push(function(t) {
                return function(e) {
                    t._trigger("update", e, this._uiHash(this))
                }
            }.call(this, this.currentContainer)))), n = this.containers.length - 1; n >= 0; n--) e || s.push(i("deactivate", this, this.containers[n])),
            this.containers[n].containerCache.over && (s.push(i("out", this, this.containers[n])), this.containers[n].containerCache.over = 0);
            if (this.storedCursor && (this.document.find("body").css("cursor", this.storedCursor), this.storedStylesheet.remove()), this._storedOpacity && this.helper.css("opacity", this._storedOpacity), this._storedZIndex && this.helper.css("zIndex", "auto" === this._storedZIndex ? "": this._storedZIndex), this.dragging = !1, e || this._trigger("beforeStop", t, this._uiHash()), this.placeholder[0].parentNode.removeChild(this.placeholder[0]), this.cancelHelperRemoval || (this.helper[0] !== this.currentItem[0] && this.helper.remove(), this.helper = null), !e) {
                for (n = 0; n < s.length; n++) s[n].call(this, t);
                this._trigger("stop", t, this._uiHash())
            }
            return this.fromOutside = !1,
            !this.cancelHelperRemoval
        },
        _trigger: function() {
            t.Widget.prototype._trigger.apply(this, arguments) === !1 && this.cancel()
        },
        _uiHash: function(e) {
            var i = e || this;
            return {
                helper: i.helper,
                placeholder: i.placeholder || t([]),
                position: i.position,
                originalPosition: i.originalPosition,
                offset: i.positionAbs,
                item: i.currentItem,
                sender: e ? e.element: null
            }
        }
    }),
    t.widget("ui.spinner", {
        version: "1.11.4",
        defaultElement: "<input>",
        widgetEventPrefix: "spin",
        options: {
            culture: null,
            icons: {
                down: "ui-icon-triangle-1-s",
                up: "ui-icon-triangle-1-n"
            },
            incremental: !0,
            max: null,
            min: null,
            numberFormat: null,
            page: 10,
            step: 1,
            change: null,
            spin: null,
            start: null,
            stop: null
        },
        _create: function() {
            this._setOption("max", this.options.max),
            this._setOption("min", this.options.min),
            this._setOption("step", this.options.step),
            "" !== this.value() && this._value(this.element.val(), !0),
            this._draw(),
            this._on(this._events),
            this._refresh(),
            this._on(this.window, {
                beforeunload: function() {
                    this.element.removeAttr("autocomplete")
                }
            })
        },
        _getCreateOptions: function() {
            var e = {},
            i = this.element;
            return t.each(["min", "max", "step"],
            function(t, n) {
                var s = i.attr(n);
                void 0 !== s && s.length && (e[n] = s)
            }),
            e
        },
        _events: {
            keydown: function(t) {
                this._start(t) && this._keydown(t) && t.preventDefault()
            },
            keyup: "_stop",
            focus: function() {
                this.previous = this.element.val()
            },
            blur: function(t) {
                return this.cancelBlur ? void delete this.cancelBlur: (this._stop(), this._refresh(), void(this.previous !== this.element.val() && this._trigger("change", t)))
            },
            mousewheel: function(t, e) {
                if (e) {
                    if (!this.spinning && !this._start(t)) return ! 1;
                    this._spin((e > 0 ? 1 : -1) * this.options.step, t),
                    clearTimeout(this.mousewheelTimer),
                    this.mousewheelTimer = this._delay(function() {
                        this.spinning && this._stop(t)
                    },
                    100),
                    t.preventDefault()
                }
            },
            "mousedown .ui-spinner-button": function(e) {
                function i() {
                    var t = this.element[0] === this.document[0].activeElement;
                    t || (this.element.focus(), this.previous = n, this._delay(function() {
                        this.previous = n
                    }))
                }
                var n;
                n = this.element[0] === this.document[0].activeElement ? this.previous: this.element.val(),
                e.preventDefault(),
                i.call(this),
                this.cancelBlur = !0,
                this._delay(function() {
                    delete this.cancelBlur,
                    i.call(this)
                }),
                this._start(e) !== !1 && this._repeat(null, t(e.currentTarget).hasClass("ui-spinner-up") ? 1 : -1, e)
            },
            "mouseup .ui-spinner-button": "_stop",
            "mouseenter .ui-spinner-button": function(e) {
                return t(e.currentTarget).hasClass("ui-state-active") ? this._start(e) === !1 ? !1 : void this._repeat(null, t(e.currentTarget).hasClass("ui-spinner-up") ? 1 : -1, e) : void 0
            },
            "mouseleave .ui-spinner-button": "_stop"
        },
        _draw: function() {
            var t = this.uiSpinner = this.element.addClass("ui-spinner-input").attr("autocomplete", "off").wrap(this._uiSpinnerHtml()).parent().append(this._buttonHtml());
            this.element.attr("role", "spinbutton"),
            this.buttons = t.find(".ui-spinner-button").attr("tabIndex", -1).button().removeClass("ui-corner-all"),
            this.buttons.height() > Math.ceil(.5 * t.height()) && t.height() > 0 && t.height(t.height()),
            this.options.disabled && this.disable()
        },
        _keydown: function(e) {
            var i = this.options,
            n = t.ui.keyCode;
            switch (e.keyCode) {
            case n.UP:
                return this._repeat(null, 1, e),
                !0;
            case n.DOWN:
                return this._repeat(null, -1, e),
                !0;
            case n.PAGE_UP:
                return this._repeat(null, i.page, e),
                !0;
            case n.PAGE_DOWN:
                return this._repeat(null, -i.page, e),
                !0
            }
            return ! 1
        },
        _uiSpinnerHtml: function() {
            return "<span class='ui-spinner ui-widget ui-widget-content ui-corner-all'></span>"
        },
        _buttonHtml: function() {
            return "<a class='ui-spinner-button ui-spinner-up ui-corner-tr'><span class='ui-icon " + this.options.icons.up + "'>&#9650;</span></a><a class='ui-spinner-button ui-spinner-down ui-corner-br'><span class='ui-icon " + this.options.icons.down + "'>&#9660;</span></a>"
        },
        _start: function(t) {
            return this.spinning || this._trigger("start", t) !== !1 ? (this.counter || (this.counter = 1), this.spinning = !0, !0) : !1
        },
        _repeat: function(t, e, i) {
            t = t || 500,
            clearTimeout(this.timer),
            this.timer = this._delay(function() {
                this._repeat(40, e, i)
            },
            t),
            this._spin(e * this.options.step, i)
        },
        _spin: function(t, e) {
            var i = this.value() || 0;
            this.counter || (this.counter = 1),
            i = this._adjustValue(i + t * this._increment(this.counter)),
            this.spinning && this._trigger("spin", e, {
                value: i
            }) === !1 || (this._value(i), this.counter++)
        },
        _increment: function(e) {
            var i = this.options.incremental;
            return i ? t.isFunction(i) ? i(e) : Math.floor(e * e * e / 5e4 - e * e / 500 + 17 * e / 200 + 1) : 1
        },
        _precision: function() {
            var t = this._precisionOf(this.options.step);
            return null !== this.options.min && (t = Math.max(t, this._precisionOf(this.options.min))),
            t
        },
        _precisionOf: function(t) {
            var e = t.toString(),
            i = e.indexOf(".");
            return - 1 === i ? 0 : e.length - i - 1
        },
        _adjustValue: function(t) {
            var e, i, n = this.options;
            return e = null !== n.min ? n.min: 0,
            i = t - e,
            i = Math.round(i / n.step) * n.step,
            t = e + i,
            t = parseFloat(t.toFixed(this._precision())),
            null !== n.max && t > n.max ? n.max: null !== n.min && t < n.min ? n.min: t
        },
        _stop: function(t) {
            this.spinning && (clearTimeout(this.timer), clearTimeout(this.mousewheelTimer), this.counter = 0, this.spinning = !1, this._trigger("stop", t))
        },
        _setOption: function(t, e) {
            if ("culture" === t || "numberFormat" === t) {
                var i = this._parse(this.element.val());
                return this.options[t] = e,
                void this.element.val(this._format(i))
            } ("max" === t || "min" === t || "step" === t) && "string" == typeof e && (e = this._parse(e)),
            "icons" === t && (this.buttons.first().find(".ui-icon").removeClass(this.options.icons.up).addClass(e.up), this.buttons.last().find(".ui-icon").removeClass(this.options.icons.down).addClass(e.down)),
            this._super(t, e),
            "disabled" === t && (this.widget().toggleClass("ui-state-disabled", !!e), this.element.prop("disabled", !!e), this.buttons.button(e ? "disable": "enable"))
        },
        _setOptions: l(function(t) {
            this._super(t)
        }),
        _parse: function(t) {
            return "string" == typeof t && "" !== t && (t = window.Globalize && this.options.numberFormat ? Globalize.parseFloat(t, 10, this.options.culture) : +t),
            "" === t || isNaN(t) ? null: t
        },
        _format: function(t) {
            return "" === t ? "": window.Globalize && this.options.numberFormat ? Globalize.format(t, this.options.numberFormat, this.options.culture) : t
        },
        _refresh: function() {
            this.element.attr({
                "aria-valuemin": this.options.min,
                "aria-valuemax": this.options.max,
                "aria-valuenow": this._parse(this.element.val())
            })
        },
        isValid: function() {
            var t = this.value();
            return null === t ? !1 : t === this._adjustValue(t)
        },
        _value: function(t, e) {
            var i;
            "" !== t && (i = this._parse(t), null !== i && (e || (i = this._adjustValue(i)), t = this._format(i))),
            this.element.val(t),
            this._refresh()
        },
        _destroy: function() {
            this.element.removeClass("ui-spinner-input").prop("disabled", !1).removeAttr("autocomplete").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow"),
            this.uiSpinner.replaceWith(this.element)
        },
        stepUp: l(function(t) {
            this._stepUp(t)
        }),
        _stepUp: function(t) {
            this._start() && (this._spin((t || 1) * this.options.step), this._stop())
        },
        stepDown: l(function(t) {
            this._stepDown(t)
        }),
        _stepDown: function(t) {
            this._start() && (this._spin((t || 1) * -this.options.step), this._stop())
        },
        pageUp: l(function(t) {
            this._stepUp((t || 1) * this.options.page)
        }),
        pageDown: l(function(t) {
            this._stepDown((t || 1) * this.options.page)
        }),
        value: function(t) {
            return arguments.length ? void l(this._value).call(this, t) : this._parse(this.element.val())
        },
        widget: function() {
            return this.uiSpinner
        }
    }),
    t.widget("ui.tabs", {
        version: "1.11.4",
        delay: 300,
        options: {
            active: null,
            collapsible: !1,
            event: "click",
            heightStyle: "content",
            hide: null,
            show: null,
            activate: null,
            beforeActivate: null,
            beforeLoad: null,
            load: null
        },
        _isLocal: function() {
            var t = /#.*$/;
            return function(e) {
                var i, n;
                e = e.cloneNode(!1),
                i = e.href.replace(t, ""),
                n = location.href.replace(t, "");
                try {
                    i = decodeURIComponent(i)
                } catch(s) {}
                try {
                    n = decodeURIComponent(n)
                } catch(s) {}
                return e.hash.length > 1 && i === n
            }
        } (),
        _create: function() {
            var e = this,
            i = this.options;
            this.running = !1,
            this.element.addClass("ui-tabs ui-widget ui-widget-content ui-corner-all").toggleClass("ui-tabs-collapsible", i.collapsible),
            this._processTabs(),
            i.active = this._initialActive(),
            t.isArray(i.disabled) && (i.disabled = t.unique(i.disabled.concat(t.map(this.tabs.filter(".ui-state-disabled"),
            function(t) {
                return e.tabs.index(t)
            }))).sort()),
            this.active = this.options.active !== !1 && this.anchors.length ? this._findActive(i.active) : t(),
            this._refresh(),
            this.active.length && this.load(i.active)
        },
        _initialActive: function() {
            var e = this.options.active,
            i = this.options.collapsible,
            n = location.hash.substring(1);
            return null === e && (n && this.tabs.each(function(i, s) {
                return t(s).attr("aria-controls") === n ? (e = i, !1) : void 0
            }), null === e && (e = this.tabs.index(this.tabs.filter(".ui-tabs-active"))), (null === e || -1 === e) && (e = this.tabs.length ? 0 : !1)),
            e !== !1 && (e = this.tabs.index(this.tabs.eq(e)), -1 === e && (e = i ? !1 : 0)),
            !i && e === !1 && this.anchors.length && (e = 0),
            e
        },
        _getCreateEventData: function() {
            return {
                tab: this.active,
                panel: this.active.length ? this._getPanelForTab(this.active) : t()
            }
        },
        _tabKeydown: function(e) {
            var i = t(this.document[0].activeElement).closest("li"),
            n = this.tabs.index(i),
            s = !0;
            if (!this._handlePageNav(e)) {
                switch (e.keyCode) {
                case t.ui.keyCode.RIGHT:
                case t.ui.keyCode.DOWN:
                    n++;
                    break;
                case t.ui.keyCode.UP:
                case t.ui.keyCode.LEFT:
                    s = !1,
                    n--;
                    break;
                case t.ui.keyCode.END:
                    n = this.anchors.length - 1;
                    break;
                case t.ui.keyCode.HOME:
                    n = 0;
                    break;
                case t.ui.keyCode.SPACE:
                    return e.preventDefault(),
                    clearTimeout(this.activating),
                    void this._activate(n);
                case t.ui.keyCode.ENTER:
                    return e.preventDefault(),
                    clearTimeout(this.activating),
                    void this._activate(n === this.options.active ? !1 : n);
                default:
                    return
                }
                e.preventDefault(),
                clearTimeout(this.activating),
                n = this._focusNextTab(n, s),
                e.ctrlKey || e.metaKey || (i.attr("aria-selected", "false"), this.tabs.eq(n).attr("aria-selected", "true"), this.activating = this._delay(function() {
                    this.option("active", n)
                },
                this.delay))
            }
        },
        _panelKeydown: function(e) {
            this._handlePageNav(e) || e.ctrlKey && e.keyCode === t.ui.keyCode.UP && (e.preventDefault(), this.active.focus())
        },
        _handlePageNav: function(e) {
            return e.altKey && e.keyCode === t.ui.keyCode.PAGE_UP ? (this._activate(this._focusNextTab(this.options.active - 1, !1)), !0) : e.altKey && e.keyCode === t.ui.keyCode.PAGE_DOWN ? (this._activate(this._focusNextTab(this.options.active + 1, !0)), !0) : void 0
        },
        _findNextTab: function(e, i) {
            function n() {
                return e > s && (e = 0),
                0 > e && (e = s),
                e
            }
            for (var s = this.tabs.length - 1; - 1 !== t.inArray(n(), this.options.disabled);) e = i ? e + 1 : e - 1;
            return e
        },
        _focusNextTab: function(t, e) {
            return t = this._findNextTab(t, e),
            this.tabs.eq(t).focus(),
            t
        },
        _setOption: function(t, e) {
            return "active" === t ? void this._activate(e) : "disabled" === t ? void this._setupDisabled(e) : (this._super(t, e), "collapsible" === t && (this.element.toggleClass("ui-tabs-collapsible", e), e || this.options.active !== !1 || this._activate(0)), "event" === t && this._setupEvents(e), void("heightStyle" === t && this._setupHeightStyle(e)))
        },
        _sanitizeSelector: function(t) {
            return t ? t.replace(/[!"$%&'()*+,.\/:;<=>?@\[\]\^`{|}~]/g, "\\$&") : ""
        },
        refresh: function() {
            var e = this.options,
            i = this.tablist.children(":has(a[href])");
            e.disabled = t.map(i.filter(".ui-state-disabled"),
            function(t) {
                return i.index(t)
            }),
            this._processTabs(),
            e.active !== !1 && this.anchors.length ? this.active.length && !t.contains(this.tablist[0], this.active[0]) ? this.tabs.length === e.disabled.length ? (e.active = !1, this.active = t()) : this._activate(this._findNextTab(Math.max(0, e.active - 1), !1)) : e.active = this.tabs.index(this.active) : (e.active = !1, this.active = t()),
            this._refresh()
        },
        _refresh: function() {
            this._setupDisabled(this.options.disabled),
            this._setupEvents(this.options.event),
            this._setupHeightStyle(this.options.heightStyle),
            this.tabs.not(this.active).attr({
                "aria-selected": "false",
                "aria-expanded": "false",
                tabIndex: -1
            }),
            this.panels.not(this._getPanelForTab(this.active)).hide().attr({
                "aria-hidden": "true"
            }),
            this.active.length ? (this.active.addClass("ui-tabs-active ui-state-active").attr({
                "aria-selected": "true",
                "aria-expanded": "true",
                tabIndex: 0
            }), this._getPanelForTab(this.active).show().attr({
                "aria-hidden": "false"
            })) : this.tabs.eq(0).attr("tabIndex", 0)
        },
        _processTabs: function() {
            var e = this,
            i = this.tabs,
            n = this.anchors,
            s = this.panels;
            this.tablist = this._getList().addClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all").attr("role", "tablist").delegate("> li", "mousedown" + this.eventNamespace,
            function(e) {
                t(this).is(".ui-state-disabled") && e.preventDefault()
            }).delegate(".ui-tabs-anchor", "focus" + this.eventNamespace,
            function() {
                t(this).closest("li").is(".ui-state-disabled") && this.blur()
            }),
            this.tabs = this.tablist.find("> li:has(a[href])").addClass("ui-state-default ui-corner-top").attr({
                role: "tab",
                tabIndex: -1
            }),
            this.anchors = this.tabs.map(function() {
                return t("a", this)[0]
            }).addClass("ui-tabs-anchor").attr({
                role: "presentation",
                tabIndex: -1
            }),
            this.panels = t(),
            this.anchors.each(function(i, n) {
                var s, o, r, a = t(n).uniqueId().attr("id"),
                l = t(n).closest("li"),
                c = l.attr("aria-controls");
                e._isLocal(n) ? (s = n.hash, r = s.substring(1), o = e.element.find(e._sanitizeSelector(s))) : (r = l.attr("aria-controls") || t({}).uniqueId()[0].id, s = "#" + r, o = e.element.find(s), o.length || (o = e._createPanel(r), o.insertAfter(e.panels[i - 1] || e.tablist)), o.attr("aria-live", "polite")),
                o.length && (e.panels = e.panels.add(o)),
                c && l.data("ui-tabs-aria-controls", c),
                l.attr({
                    "aria-controls": r,
                    "aria-labelledby": a
                }),
                o.attr("aria-labelledby", a)
            }),
            this.panels.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").attr("role", "tabpanel"),
            i && (this._off(i.not(this.tabs)), this._off(n.not(this.anchors)), this._off(s.not(this.panels)))
        },
        _getList: function() {
            return this.tablist || this.element.find("ol,ul").eq(0)
        },
        _createPanel: function(e) {
            return t("<div>").attr("id", e).addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").data("ui-tabs-destroy", !0)
        },
        _setupDisabled: function(e) {
            t.isArray(e) && (e.length ? e.length === this.anchors.length && (e = !0) : e = !1);
            for (var i, n = 0; i = this.tabs[n]; n++) e === !0 || -1 !== t.inArray(n, e) ? t(i).addClass("ui-state-disabled").attr("aria-disabled", "true") : t(i).removeClass("ui-state-disabled").removeAttr("aria-disabled");
            this.options.disabled = e
        },
        _setupEvents: function(e) {
            var i = {};
            e && t.each(e.split(" "),
            function(t, e) {
                i[e] = "_eventHandler"
            }),
            this._off(this.anchors.add(this.tabs).add(this.panels)),
            this._on(!0, this.anchors, {
                click: function(t) {
                    t.preventDefault()
                }
            }),
            this._on(this.anchors, i),
            this._on(this.tabs, {
                keydown: "_tabKeydown"
            }),
            this._on(this.panels, {
                keydown: "_panelKeydown"
            }),
            this._focusable(this.tabs),
            this._hoverable(this.tabs)
        },
        _setupHeightStyle: function(e) {
            var i, n = this.element.parent();
            "fill" === e ? (i = n.height(), i -= this.element.outerHeight() - this.element.height(), this.element.siblings(":visible").each(function() {
                var e = t(this),
                n = e.css("position");
                "absolute" !== n && "fixed" !== n && (i -= e.outerHeight(!0))
            }), this.element.children().not(this.panels).each(function() {
                i -= t(this).outerHeight(!0)
            }), this.panels.each(function() {
                t(this).height(Math.max(0, i - t(this).innerHeight() + t(this).height()))
            }).css("overflow", "auto")) : "auto" === e && (i = 0, this.panels.each(function() {
                i = Math.max(i, t(this).height("").height())
            }).height(i))
        },
        _eventHandler: function(e) {
            var i = this.options,
            n = this.active,
            s = t(e.currentTarget),
            o = s.closest("li"),
            r = o[0] === n[0],
            a = r && i.collapsible,
            l = a ? t() : this._getPanelForTab(o),
            c = n.length ? this._getPanelForTab(n) : t(),
            h = {
                oldTab: n,
                oldPanel: c,
                newTab: a ? t() : o,
                newPanel: l
            };
            e.preventDefault(),
            o.hasClass("ui-state-disabled") || o.hasClass("ui-tabs-loading") || this.running || r && !i.collapsible || this._trigger("beforeActivate", e, h) === !1 || (i.active = a ? !1 : this.tabs.index(o), this.active = r ? t() : o, this.xhr && this.xhr.abort(), c.length || l.length || t.error("jQuery UI Tabs: Mismatching fragment identifier."), l.length && this.load(this.tabs.index(o), e), this._toggle(e, h))
        },
        _toggle: function(e, i) {
            function n() {
                o.running = !1,
                o._trigger("activate", e, i)
            }
            function s() {
                i.newTab.closest("li").addClass("ui-tabs-active ui-state-active"),
                r.length && o.options.show ? o._show(r, o.options.show, n) : (r.show(), n())
            }
            var o = this,
            r = i.newPanel,
            a = i.oldPanel;
            this.running = !0,
            a.length && this.options.hide ? this._hide(a, this.options.hide,
            function() {
                i.oldTab.closest("li").removeClass("ui-tabs-active ui-state-active"),
                s()
            }) : (i.oldTab.closest("li").removeClass("ui-tabs-active ui-state-active"), a.hide(), s()),
            a.attr("aria-hidden", "true"),
            i.oldTab.attr({
                "aria-selected": "false",
                "aria-expanded": "false"
            }),
            r.length && a.length ? i.oldTab.attr("tabIndex", -1) : r.length && this.tabs.filter(function() {
                return 0 === t(this).attr("tabIndex")
            }).attr("tabIndex", -1),
            r.attr("aria-hidden", "false"),
            i.newTab.attr({
                "aria-selected": "true",
                "aria-expanded": "true",
                tabIndex: 0
            })
        },
        _activate: function(e) {
            var i, n = this._findActive(e);
            n[0] !== this.active[0] && (n.length || (n = this.active), i = n.find(".ui-tabs-anchor")[0], this._eventHandler({
                target: i,
                currentTarget: i,
                preventDefault: t.noop
            }))
        },
        _findActive: function(e) {
            return e === !1 ? t() : this.tabs.eq(e)
        },
        _getIndex: function(t) {
            return "string" == typeof t && (t = this.anchors.index(this.anchors.filter("[href$='" + t + "']"))),
            t
        },
        _destroy: function() {
            this.xhr && this.xhr.abort(),
            this.element.removeClass("ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-collapsible"),
            this.tablist.removeClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all").removeAttr("role"),
            this.anchors.removeClass("ui-tabs-anchor").removeAttr("role").removeAttr("tabIndex").removeUniqueId(),
            this.tablist.unbind(this.eventNamespace),
            this.tabs.add(this.panels).each(function() {
                t.data(this, "ui-tabs-destroy") ? t(this).remove() : t(this).removeClass("ui-state-default ui-state-active ui-state-disabled ui-corner-top ui-corner-bottom ui-widget-content ui-tabs-active ui-tabs-panel").removeAttr("tabIndex").removeAttr("aria-live").removeAttr("aria-busy").removeAttr("aria-selected").removeAttr("aria-labelledby").removeAttr("aria-hidden").removeAttr("aria-expanded").removeAttr("role")
            }),
            this.tabs.each(function() {
                var e = t(this),
                i = e.data("ui-tabs-aria-controls");
                i ? e.attr("aria-controls", i).removeData("ui-tabs-aria-controls") : e.removeAttr("aria-controls")
            }),
            this.panels.show(),
            "content" !== this.options.heightStyle && this.panels.css("height", "")
        },
        enable: function(e) {
            var i = this.options.disabled;
            i !== !1 && (void 0 === e ? i = !1 : (e = this._getIndex(e), i = t.isArray(i) ? t.map(i,
            function(t) {
                return t !== e ? t: null
            }) : t.map(this.tabs,
            function(t, i) {
                return i !== e ? i: null
            })), this._setupDisabled(i))
        },
        disable: function(e) {
            var i = this.options.disabled;
            if (i !== !0) {
                if (void 0 === e) i = !0;
                else {
                    if (e = this._getIndex(e), -1 !== t.inArray(e, i)) return;
                    i = t.isArray(i) ? t.merge([e], i).sort() : [e]
                }
                this._setupDisabled(i)
            }
        },
        load: function(e, i) {
            e = this._getIndex(e);
            var n = this,
            s = this.tabs.eq(e),
            o = s.find(".ui-tabs-anchor"),
            r = this._getPanelForTab(s),
            a = {
                tab: s,
                panel: r
            },
            l = function(t, e) {
                "abort" === e && n.panels.stop(!1, !0),
                s.removeClass("ui-tabs-loading"),
                r.removeAttr("aria-busy"),
                t === n.xhr && delete n.xhr
            };
            this._isLocal(o[0]) || (this.xhr = t.ajax(this._ajaxSettings(o, i, a)), this.xhr && "canceled" !== this.xhr.statusText && (s.addClass("ui-tabs-loading"), r.attr("aria-busy", "true"), this.xhr.done(function(t, e, s) {
                setTimeout(function() {
                    r.html(t),
                    n._trigger("load", i, a),
                    l(s, e)
                },
                1)
            }).fail(function(t, e) {
                setTimeout(function() {
                    l(t, e)
                },
                1)
            })))
        },
        _ajaxSettings: function(e, i, n) {
            var s = this;
            return {
                url: e.attr("href"),
                beforeSend: function(e, o) {
                    return s._trigger("beforeLoad", i, t.extend({
                        jqXHR: e,
                        ajaxSettings: o
                    },
                    n))
                }
            }
        },
        _getPanelForTab: function(e) {
            var i = t(e).attr("aria-controls");
            return this.element.find(this._sanitizeSelector("#" + i))
        }
    }),
    t.widget("ui.tooltip", {
        version: "1.11.4",
        options: {
            content: function() {
                var e = t(this).attr("title") || "";
                return t("<a>").text(e).html()
            },
            hide: !0,
            items: "[title]:not([disabled])",
            position: {
                my: "left top+15",
                at: "left bottom",
                collision: "flipfit flip"
            },
            show: !0,
            tooltipClass: null,
            track: !1,
            close: null,
            open: null
        },
        _addDescribedBy: function(e, i) {
            var n = (e.attr("aria-describedby") || "").split(/\s+/);
            n.push(i),
            e.data("ui-tooltip-id", i).attr("aria-describedby", t.trim(n.join(" ")))
        },
        _removeDescribedBy: function(e) {
            var i = e.data("ui-tooltip-id"),
            n = (e.attr("aria-describedby") || "").split(/\s+/),
            s = t.inArray(i, n); - 1 !== s && n.splice(s, 1),
            e.removeData("ui-tooltip-id"),
            n = t.trim(n.join(" ")),
            n ? e.attr("aria-describedby", n) : e.removeAttr("aria-describedby")
        },
        _create: function() {
            this._on({
                mouseover: "open",
                focusin: "open"
            }),
            this.tooltips = {},
            this.parents = {},
            this.options.disabled && this._disable(),
            this.liveRegion = t("<div>").attr({
                role: "log",
                "aria-live": "assertive",
                "aria-relevant": "additions"
            }).addClass("ui-helper-hidden-accessible").appendTo(this.document[0].body)
        },
        _setOption: function(e, i) {
            var n = this;
            return "disabled" === e ? (this[i ? "_disable": "_enable"](), void(this.options[e] = i)) : (this._super(e, i), void("content" === e && t.each(this.tooltips,
            function(t, e) {
                n._updateContent(e.element)
            })))
        },
        _disable: function() {
            var e = this;
            t.each(this.tooltips,
            function(i, n) {
                var s = t.Event("blur");
                s.target = s.currentTarget = n.element[0],
                e.close(s, !0)
            }),
            this.element.find(this.options.items).addBack().each(function() {
                var e = t(this);
                e.is("[title]") && e.data("ui-tooltip-title", e.attr("title")).removeAttr("title")
            })
        },
        _enable: function() {
            this.element.find(this.options.items).addBack().each(function() {
                var e = t(this);
                e.data("ui-tooltip-title") && e.attr("title", e.data("ui-tooltip-title"))
            })
        },
        open: function(e) {
            var i = this,
            n = t(e ? e.target: this.element).closest(this.options.items);
            n.length && !n.data("ui-tooltip-id") && (n.attr("title") && n.data("ui-tooltip-title", n.attr("title")), n.data("ui-tooltip-open", !0), e && "mouseover" === e.type && n.parents().each(function() {
                var e, n = t(this);
                n.data("ui-tooltip-open") && (e = t.Event("blur"), e.target = e.currentTarget = this, i.close(e, !0)),
                n.attr("title") && (n.uniqueId(), i.parents[this.id] = {
                    element: this,
                    title: n.attr("title")
                },
                n.attr("title", ""))
            }), this._registerCloseHandlers(e, n), this._updateContent(n, e))
        },
        _updateContent: function(t, e) {
            var i, n = this.options.content,
            s = this,
            o = e ? e.type: null;
            return "string" == typeof n ? this._open(e, t, n) : (i = n.call(t[0],
            function(i) {
                s._delay(function() {
                    t.data("ui-tooltip-open") && (e && (e.type = o), this._open(e, t, i))
                })
            }), void(i && this._open(e, t, i)))
        },
        _open: function(e, i, n) {
            function s(t) {
                c.of = t,
                r.is(":hidden") || r.position(c)
            }
            var o, r, a, l, c = t.extend({},
            this.options.position);
            if (n) {
                if (o = this._find(i)) return void o.tooltip.find(".ui-tooltip-content").html(n);
                i.is("[title]") && (e && "mouseover" === e.type ? i.attr("title", "") : i.removeAttr("title")),
                o = this._tooltip(i),
                r = o.tooltip,
                this._addDescribedBy(i, r.attr("id")),
                r.find(".ui-tooltip-content").html(n),
                this.liveRegion.children().hide(),
                n.clone ? (l = n.clone(), l.removeAttr("id").find("[id]").removeAttr("id")) : l = n,
                t("<div>").html(l).appendTo(this.liveRegion),
                this.options.track && e && /^mouse/.test(e.type) ? (this._on(this.document, {
                    mousemove: s
                }), s(e)) : r.position(t.extend({
                    of: i
                },
                this.options.position)),
                r.hide(),
                this._show(r, this.options.show),
                this.options.show && this.options.show.delay && (a = this.delayedShow = setInterval(function() {
                    r.is(":visible") && (s(c.of), clearInterval(a))
                },
                t.fx.interval)),
                this._trigger("open", e, {
                    tooltip: r
                })
            }
        },
        _registerCloseHandlers: function(e, i) {
            var n = {
                keyup: function(e) {
                    if (e.keyCode === t.ui.keyCode.ESCAPE) {
                        var n = t.Event(e);
                        n.currentTarget = i[0],
                        this.close(n, !0)
                    }
                }
            };
            i[0] !== this.element[0] && (n.remove = function() {
                this._removeTooltip(this._find(i).tooltip)
            }),
            e && "mouseover" !== e.type || (n.mouseleave = "close"),
            e && "focusin" !== e.type || (n.focusout = "close"),
            this._on(!0, i, n)
        },
        close: function(e) {
            var i, n = this,
            s = t(e ? e.currentTarget: this.element),
            o = this._find(s);
            return o ? (i = o.tooltip, void(o.closing || (clearInterval(this.delayedShow), s.data("ui-tooltip-title") && !s.attr("title") && s.attr("title", s.data("ui-tooltip-title")), this._removeDescribedBy(s), o.hiding = !0, i.stop(!0), this._hide(i, this.options.hide,
            function() {
                n._removeTooltip(t(this))
            }), s.removeData("ui-tooltip-open"), this._off(s, "mouseleave focusout keyup"), s[0] !== this.element[0] && this._off(s, "remove"), this._off(this.document, "mousemove"), e && "mouseleave" === e.type && t.each(this.parents,
            function(e, i) {
                t(i.element).attr("title", i.title),
                delete n.parents[e]
            }), o.closing = !0, this._trigger("close", e, {
                tooltip: i
            }), o.hiding || (o.closing = !1)))) : void s.removeData("ui-tooltip-open")
        },
        _tooltip: function(e) {
            var i = t("<div>").attr("role", "tooltip").addClass("ui-tooltip ui-widget ui-corner-all ui-widget-content " + (this.options.tooltipClass || "")),
            n = i.uniqueId().attr("id");
            return t("<div>").addClass("ui-tooltip-content").appendTo(i),
            i.appendTo(this.document[0].body),
            this.tooltips[n] = {
                element: e,
                tooltip: i
            }
        },
        _find: function(t) {
            var e = t.data("ui-tooltip-id");
            return e ? this.tooltips[e] : null
        },
        _removeTooltip: function(t) {
            t.remove(),
            delete this.tooltips[t.attr("id")]
        },
        _destroy: function() {
            var e = this;
            t.each(this.tooltips,
            function(i, n) {
                var s = t.Event("blur"),
                o = n.element;
                s.target = s.currentTarget = o[0],
                e.close(s, !0),
                t("#" + i).remove(),
                o.data("ui-tooltip-title") && (o.attr("title") || o.attr("title", o.data("ui-tooltip-title")), o.removeData("ui-tooltip-title"))
            }),
            this.liveRegion.remove()
        }
    })
}),
define("views/PageItemCompositeView", ["backbone.marionette", "mustache", "views/PageItemView", "templates", "jquery-ui"],
function(t, e, i, n) {
    "use strict";
    return t.CompositeView.extend({
        template: function(t) {
            return e.parse(n.pageItemCompositeView),
            e.render(n.pageItemCompositeView, t)
        },
        className: "region-warp",
        childView: i,
        childViewContainer: "#page-list",
        ui: {
            addButton: "#add-page",
            moveUpButton: "#move-up-page",
            moveDownButton: "#move-down-page",
            deleteButton: ".delete-page",
            pages: "#page-list",
            searchField: "#search-field"
        },
        events: {
            "click @ui.addButton": "addPage",
            "click @ui.moveUpButton": "sortPage",
            "click @ui.moveDownButton": "sortPage",
            "click @ui.deleteButton": "deletePage",
            "keyup @ui.searchField": "searchPage"
        },
        onRender: function() {},
        addPage: function() {
            var t = this;
            $.ajax({
                url: "/pages/new",
                dataType: "json",
                type: "post",
                success: function(e) {
                    return 1 != e.state ? (alert(e.msg), !1) : void t.collection.reset(e.models)
                }
            })
        },
        sortPage: function() {
            this.collection.sort()
        },
        deletePage: function(t) {
            var e = $(t.currentTarget),
            i = e.data("id"),
            n = this;
            $.ajax({
                url: "/pages/delete/" + i,
                dataType: "json",
                type: "post",
                success: function(t) {
                    return 1 != t.state ? (alert(t.msg), !1) : void n.collection.reset(t.models)
                }
            })
        },
        searchPage: function() {
            var t = this.ui.searchField.val().trim();
            return "" == t ? void $(this.ui.pages).find("li").removeClass("hide") : void $(this.ui.pages).find("li").each(function() {
                var e = $(this),
                i = e.find("label").text();
                i.indexOf(t) > -1 ? e.removeClass("hide") : e.addClass("hide")
            })
        }
    })
}),
define("views/ComponentItemView", ["backbone.marionette", "mustache", "templates", "jquery-ui"],
function(t, e, i) {
    "use strict";
    return t.ItemView.extend({
        template: function(t) {
            return e.parse(i.componentItemView),
            e.render(i.componentItemView, t)
        },
        tagName: "div",
        className: "item",
        ui: {
            component: ".component"
        },
        events: {},
        onRender: function() {
            this.ui.component.draggable({
                connectToSortable: "#preview-region .region-warp",
                helper: "clone",
                revert: "invalid"
            })
        }
    })
}),
define("views/ComponentItemCompositeView", ["backbone.marionette", "mustache", "views/ComponentItemView", "templates", "jquery-ui"],
function(t, e, i, n) {
    "use strict";
    return t.CompositeView.extend({
        template: function(t) {
            return e.parse(n.componentItemCompositeView),
            e.render(n.componentItemCompositeView, t)
        },
        className: "region-warp",
        childView: i,
        childViewContainer: "#component-list",
        ui: {},
        events: {},
        onRender: function() {}
    })
}),
define("views/PageElementView", ["backbone.marionette"],
function(t) {
    "use strict";
    return t.CompositeView.extend({
        className: "region-warp",
        initialize: function() {},
        render: function() {
            var t = this.collection.models.length;
            this.collection.models.length > 0 && this.addElement(this, this.collection.models[0], 0, t)
        },
        addElement: function(t, e, i, n) {
            var s = e.get("componentType");
            require(["components/" + s + "/main"],
            function(o) {
                var r = new o.Controller({
                    model: e
                }),
                a = r.getPreviewView(),
                l = $('<div id="model_' + e.cid + '" class="component ' + s + '"></div>'),
                c = $(a.render().el);
                l.append(c),
                $("#preview-region .region-warp").append(l),
                ++i < n && t.addElement(t, t.collection.models[i], i, n)
            })
        }
    })
}),
define("application", ["backbone.marionette", "msgbus", "collections", "views/PageItemCompositeView", "views/ComponentItemCompositeView", "views/PageElementView"],
function(t, e, i, n, s, o) {
    "use strict";
    var r = new t.Application({
        regions: {
            componentRegion: "#component-region",
            previewRegion: "#preview-region",
            configRegion: "#config-region"
        },
        collections: i
    });
    return r.addInitializer(function() {
        i.pageList.add(McMore.page.itemList),
        i.pageElementList.add(McMore.page.pageElement),
        i.componentList.add(McMore.components),
        r.componentRegion.show(new s({
            collection: i.componentList
        })),
        r.previewRegion.show(new o({
            collection: i.pageElementList
        }))
    }),
    r.on("start",
    function() {
        $(".cover").remove(),
        r.sortResult = $("#preview-region .region-warp").sortable({
            forcePlaceholderSize: !0,
            placeholder: "place_holder",
            start: function() {},
            stop: function(t, i) {
                var n = i.item.attr("data-component");
                n && e.reqres.request("component:instance", n, i.item)
            }
        })
    }),
    e.events.on("component:config:show",
    function(t) {
        return r.configRegion.show(t)
    }),
    e.reqres.setHandlers({
        "component:instance": function(t, e, n) {
            require(["components/" + t + "/main"],
            function(t) {
                var s = "undefined" == typeof n ? t.defaults: n,
                o = new t.Controller({
                    model: i.pageElementList.add($.extend(!0, {},
                    s))
                }),
                r = o.getPreviewView();
                "undefined" == typeof n && r.showConfig(),
                e.removeClass("ui-draggable ui-draggable-handle"),
                e.removeAttr("style"),
                e.removeAttr("data-component"),
                e.attr("id", "model_" + o.model.cid),
                e.html(r.render().el)
            })
        }
    }),
    r.resetPreviewView = function(t) {
        i.pageElementList.reset(t),
        r.previewRegion.show(new o({
            collection: i.pageElementList
        })),
        r.start()
    },
    McMore.collections = i,
    r
}),
function(t) {
    "function" == typeof define && define.amd ? define("jquery-form", ["jquery"], t) : t("undefined" != typeof jQuery ? jQuery: window.Zepto)
} (function(t) {
    "use strict";
    function e(e) {
        var i = e.data;
        e.isDefaultPrevented() || (e.preventDefault(), t(e.target).ajaxSubmit(i))
    }
    function i(e) {
        var i = e.target,
        n = t(i);
        if (!n.is("[type=submit],[type=image]")) {
            var s = n.closest("[type=submit]");
            if (0 === s.length) return;
            i = s[0]
        }
        var o = this;
        if (o.clk = i, "image" == i.type) if (void 0 !== e.offsetX) o.clk_x = e.offsetX,
        o.clk_y = e.offsetY;
        else if ("function" == typeof t.fn.offset) {
            var r = n.offset();
            o.clk_x = e.pageX - r.left,
            o.clk_y = e.pageY - r.top
        } else o.clk_x = e.pageX - i.offsetLeft,
        o.clk_y = e.pageY - i.offsetTop;
        setTimeout(function() {
            o.clk = o.clk_x = o.clk_y = null
        },
        100)
    }
    function n() {
        if (t.fn.ajaxSubmit.debug) {
            var e = "[jquery.form] " + Array.prototype.join.call(arguments, "");
            window.console && window.console.log ? window.console.log(e) : window.opera && window.opera.postError && window.opera.postError(e)
        }
    }
    var s = {};
    s.fileapi = void 0 !== t("<input type='file'/>").get(0).files,
    s.formdata = void 0 !== window.FormData;
    var o = !!t.fn.prop;
    t.fn.attr2 = function() {
        if (!o) return this.attr.apply(this, arguments);
        var t = this.prop.apply(this, arguments);
        return t && t.jquery || "string" == typeof t ? t: this.attr.apply(this, arguments)
    },
    t.fn.ajaxSubmit = function(e) {
        function i(i) {
            var n, s, o = t.param(i, e.traditional).split("&"),
            r = o.length,
            a = [];
            for (n = 0; r > n; n++) o[n] = o[n].replace(/\+/g, " "),
            s = o[n].split("="),
            a.push([decodeURIComponent(s[0]), decodeURIComponent(s[1])]);
            return a
        }
        function r(n) {
            for (var s = new FormData,
            o = 0; o < n.length; o++) s.append(n[o].name, n[o].value);
            if (e.extraData) {
                var r = i(e.extraData);
                for (o = 0; o < r.length; o++) r[o] && s.append(r[o][0], r[o][1])
            }
            e.data = null;
            var a = t.extend(!0, {},
            t.ajaxSettings, e, {
                contentType: !1,
                processData: !1,
                cache: !1,
                type: l || "POST"
            });
            e.uploadProgress && (a.xhr = function() {
                var i = t.ajaxSettings.xhr();
                return i.upload && i.upload.addEventListener("progress",
                function(t) {
                    var i = 0,
                    n = t.loaded || t.position,
                    s = t.total;
                    t.lengthComputable && (i = Math.ceil(n / s * 100)),
                    e.uploadProgress(t, n, s, i)
                },
                !1),
                i
            }),
            a.data = null;
            var c = a.beforeSend;
            return a.beforeSend = function(t, i) {
                i.data = e.formData ? e.formData: s,
                c && c.call(this, t, i)
            },
            t.ajax(a)
        }
        function a(i) {
            function s(t) {
                var e = null;
                try {
                    t.contentWindow && (e = t.contentWindow.document)
                } catch(i) {
                    n("cannot get iframe.contentWindow document: " + i)
                }
                if (e) return e;
                try {
                    e = t.contentDocument ? t.contentDocument: t.document
                } catch(i) {
                    n("cannot get iframe.contentDocument: " + i),
                    e = t.document
                }
                return e
            }
            function r() {
                function e() {
                    try {
                        var t = s(v).readyState;
                        n("state = " + t),
                        t && "uninitialized" == t.toLowerCase() && setTimeout(e, 50)
                    } catch(i) {
                        n("Server abort: ", i, " (", i.name, ")"),
                        a(D),
                        x && clearTimeout(x),
                        x = void 0
                    }
                }
                var i = u.attr2("target"),
                o = u.attr2("action");
                C.setAttribute("target", f),
                (!l || /post/i.test(l)) && C.setAttribute("method", "POST"),
                o != d.url && C.setAttribute("action", d.url),
                d.skipEncodingOverride || l && !/post/i.test(l) || u.attr({
                    encoding: "multipart/form-data",
                    enctype: "multipart/form-data"
                }),
                d.timeout && (x = setTimeout(function() {
                    w = !0,
                    a(T)
                },
                d.timeout));
                var r = [];
                try {
                    if (d.extraData) for (var c in d.extraData) d.extraData.hasOwnProperty(c) && r.push(t.isPlainObject(d.extraData[c]) && d.extraData[c].hasOwnProperty("name") && d.extraData[c].hasOwnProperty("value") ? t('<input type="hidden" name="' + d.extraData[c].name + '">').val(d.extraData[c].value).appendTo(C)[0] : t('<input type="hidden" name="' + c + '">').val(d.extraData[c]).appendTo(C)[0]);
                    d.iframeTarget || g.appendTo("body"),
                    v.attachEvent ? v.attachEvent("onload", a) : v.addEventListener("load", a, !1),
                    setTimeout(e, 15);
                    try {
                        C.submit()
                    } catch(h) {
                        var p = document.createElement("form").submit;
                        p.apply(C)
                    }
                } finally {
                    C.setAttribute("action", o),
                    i ? C.setAttribute("target", i) : u.removeAttr("target"),
                    t(r).remove()
                }
            }
            function a(e) {
                if (!y.aborted && !P) {
                    if (S = s(v), S || (n("cannot access response document"), e = D), e === T && y) return y.abort("timeout"),
                    void k.reject(y, "timeout");
                    if (e == D && y) return y.abort("server abort"),
                    void k.reject(y, "error", "server abort");
                    if (S && S.location.href != d.iframeSrc || w) {
                        v.detachEvent ? v.detachEvent("onload", a) : v.removeEventListener("load", a, !1);
                        var i, o = "success";
                        try {
                            if (w) throw "timeout";
                            var r = "xml" == d.dataType || S.XMLDocument || t.isXMLDoc(S);
                            if (n("isXml=" + r), !r && window.opera && (null === S.body || !S.body.innerHTML) && --A) return n("requeing onLoad callback, DOM not available"),
                            void setTimeout(a, 250);
                            var l = S.body ? S.body: S.documentElement;
                            y.responseText = l ? l.innerHTML: null,
                            y.responseXML = S.XMLDocument ? S.XMLDocument: S,
                            r && (d.dataType = "xml"),
                            y.getResponseHeader = function(t) {
                                var e = {
                                    "content-type": d.dataType
                                };
                                return e[t.toLowerCase()]
                            },
                            l && (y.status = Number(l.getAttribute("status")) || y.status, y.statusText = l.getAttribute("statusText") || y.statusText);
                            var c = (d.dataType || "").toLowerCase(),
                            h = /(json|script|text)/.test(c);
                            if (h || d.textarea) {
                                var u = S.getElementsByTagName("textarea")[0];
                                if (u) y.responseText = u.value,
                                y.status = Number(u.getAttribute("status")) || y.status,
                                y.statusText = u.getAttribute("statusText") || y.statusText;
                                else if (h) {
                                    var f = S.getElementsByTagName("pre")[0],
                                    m = S.getElementsByTagName("body")[0];
                                    f ? y.responseText = f.textContent ? f.textContent: f.innerText: m && (y.responseText = m.textContent ? m.textContent: m.innerText)
                                }
                            } else "xml" == c && !y.responseXML && y.responseText && (y.responseXML = N(y.responseText));
                            try {
                                I = z(y, c, d)
                            } catch(b) {
                                o = "parsererror",
                                y.error = i = b || o
                            }
                        } catch(b) {
                            n("error caught: ", b),
                            o = "error",
                            y.error = i = b || o
                        }
                        y.aborted && (n("upload aborted"), o = null),
                        y.status && (o = y.status >= 200 && y.status < 300 || 304 === y.status ? "success": "error"),
                        "success" === o ? (d.success && d.success.call(d.context, I, "success", y), k.resolve(y.responseText, "success", y), p && t.event.trigger("ajaxSuccess", [y, d])) : o && (void 0 === i && (i = y.statusText), d.error && d.error.call(d.context, y, o, i), k.reject(y, "error", i), p && t.event.trigger("ajaxError", [y, d, i])),
                        p && t.event.trigger("ajaxComplete", [y, d]),
                        p && !--t.active && t.event.trigger("ajaxStop"),
                        d.complete && d.complete.call(d.context, y, o),
                        P = !0,
                        d.timeout && clearTimeout(x),
                        setTimeout(function() {
                            d.iframeTarget ? g.attr("src", d.iframeSrc) : g.remove(),
                            y.responseXML = null
                        },
                        100)
                    }
                }
            }
            var c, h, d, p, f, g, v, y, b, _, w, x, C = u[0],
            k = t.Deferred();
            if (k.abort = function(t) {
                y.abort(t)
            },
            i) for (h = 0; h < m.length; h++) c = t(m[h]),
            o ? c.prop("disabled", !1) : c.removeAttr("disabled");
            if (d = t.extend(!0, {},
            t.ajaxSettings, e), d.context = d.context || d, f = "jqFormIO" + (new Date).getTime(), d.iframeTarget ? (g = t(d.iframeTarget), _ = g.attr2("name"), _ ? f = _: g.attr2("name", f)) : (g = t('<iframe name="' + f + '" src="' + d.iframeSrc + '" />'), g.css({
                position: "absolute",
                top: "-1000px",
                left: "-1000px"
            })), v = g[0], y = {
                aborted: 0,
                responseText: null,
                responseXML: null,
                status: 0,
                statusText: "n/a",
                getAllResponseHeaders: function() {},
                getResponseHeader: function() {},
                setRequestHeader: function() {},
                abort: function(e) {
                    var i = "timeout" === e ? "timeout": "aborted";
                    n("aborting upload... " + i),
                    this.aborted = 1;
                    try {
                        v.contentWindow.document.execCommand && v.contentWindow.document.execCommand("Stop")
                    } catch(s) {}
                    g.attr("src", d.iframeSrc),
                    y.error = i,
                    d.error && d.error.call(d.context, y, i, e),
                    p && t.event.trigger("ajaxError", [y, d, i]),
                    d.complete && d.complete.call(d.context, y, i)
                }
            },
            p = d.global, p && 0 === t.active++&&t.event.trigger("ajaxStart"), p && t.event.trigger("ajaxSend", [y, d]), d.beforeSend && d.beforeSend.call(d.context, y, d) === !1) return d.global && t.active--,
            k.reject(),
            k;
            if (y.aborted) return k.reject(),
            k;
            b = C.clk,
            b && (_ = b.name, _ && !b.disabled && (d.extraData = d.extraData || {},
            d.extraData[_] = b.value, "image" == b.type && (d.extraData[_ + ".x"] = C.clk_x, d.extraData[_ + ".y"] = C.clk_y)));
            var T = 1,
            D = 2,
            E = t("meta[name=csrf-token]").attr("content"),
            M = t("meta[name=csrf-param]").attr("content");
            M && E && (d.extraData = d.extraData || {},
            d.extraData[M] = E),
            d.forceSync ? r() : setTimeout(r, 10);
            var I, S, P, A = 50,
            N = t.parseXML ||
            function(t, e) {
                return window.ActiveXObject ? (e = new ActiveXObject("Microsoft.XMLDOM"), e.async = "false", e.loadXML(t)) : e = (new DOMParser).parseFromString(t, "text/xml"),
                e && e.documentElement && "parsererror" != e.documentElement.nodeName ? e: null
            },
            O = t.parseJSON ||
            function(t) {
                return window.eval("(" + t + ")")
            },
            z = function(e, i, n) {
                var s = e.getResponseHeader("content-type") || "",
                o = "xml" === i || !i && s.indexOf("xml") >= 0,
                r = o ? e.responseXML: e.responseText;
                return o && "parsererror" === r.documentElement.nodeName && t.error && t.error("parsererror"),
                n && n.dataFilter && (r = n.dataFilter(r, i)),
                "string" == typeof r && ("json" === i || !i && s.indexOf("json") >= 0 ? r = O(r) : ("script" === i || !i && s.indexOf("javascript") >= 0) && t.globalEval(r)),
                r
            };
            return k
        }
        if (!this.length) return n("ajaxSubmit: skipping submit process - no element selected"),
        this;
        var l, c, h, u = this;
        "function" == typeof e ? e = {
            success: e
        }: void 0 === e && (e = {}),
        l = e.type || this.attr2("method"),
        c = e.url || this.attr2("action"),
        h = "string" == typeof c ? t.trim(c) : "",
        h = h || window.location.href || "",
        h && (h = (h.match(/^([^#]+)/) || [])[1]),
        e = t.extend(!0, {
            url: h,
            success: t.ajaxSettings.success,
            type: l || t.ajaxSettings.type,
            iframeSrc: /^https/i.test(window.location.href || "") ? "javascript:false": "about:blank"
        },
        e);
        var d = {};
        if (this.trigger("form-pre-serialize", [this, e, d]), d.veto) return n("ajaxSubmit: submit vetoed via form-pre-serialize trigger"),
        this;
        if (e.beforeSerialize && e.beforeSerialize(this, e) === !1) return n("ajaxSubmit: submit aborted via beforeSerialize callback"),
        this;
        var p = e.traditional;
        void 0 === p && (p = t.ajaxSettings.traditional);
        var f, m = [],
        g = this.formToArray(e.semantic, m);
        if (e.data && (e.extraData = e.data, f = t.param(e.data, p)), e.beforeSubmit && e.beforeSubmit(g, this, e) === !1) return n("ajaxSubmit: submit aborted via beforeSubmit callback"),
        this;
        if (this.trigger("form-submit-validate", [g, this, e, d]), d.veto) return n("ajaxSubmit: submit vetoed via form-submit-validate trigger"),
        this;
        var v = t.param(g, p);
        f && (v = v ? v + "&" + f: f),
        "GET" == e.type.toUpperCase() ? (e.url += (e.url.indexOf("?") >= 0 ? "&": "?") + v, e.data = null) : e.data = v;
        var y = [];
        if (e.resetForm && y.push(function() {
            u.resetForm()
        }), e.clearForm && y.push(function() {
            u.clearForm(e.includeHidden)
        }), !e.dataType && e.target) {
            var b = e.success ||
            function() {};
            y.push(function(i) {
                var n = e.replaceTarget ? "replaceWith": "html";
                t(e.target)[n](i).each(b, arguments)
            })
        } else e.success && y.push(e.success);
        if (e.success = function(t, i, n) {
            for (var s = e.context || this,
            o = 0,
            r = y.length; r > o; o++) y[o].apply(s, [t, i, n || u, u])
        },
        e.error) {
            var _ = e.error;
            e.error = function(t, i, n) {
                var s = e.context || this;
                _.apply(s, [t, i, n, u])
            }
        }
        if (e.complete) {
            var w = e.complete;
            e.complete = function(t, i) {
                var n = e.context || this;
                w.apply(n, [t, i, u])
            }
        }
        var x = t("input[type=file]:enabled", this).filter(function() {
            return "" !== t(this).val()
        }),
        C = x.length > 0,
        k = "multipart/form-data",
        T = u.attr("enctype") == k || u.attr("encoding") == k,
        D = s.fileapi && s.formdata;
        n("fileAPI :" + D);
        var E, M = (C || T) && !D;
        e.iframe !== !1 && (e.iframe || M) ? e.closeKeepAlive ? t.get(e.closeKeepAlive,
        function() {
            E = a(g)
        }) : E = a(g) : E = (C || T) && D ? r(g) : t.ajax(e),
        u.removeData("jqxhr").data("jqxhr", E);
        for (var I = 0; I < m.length; I++) m[I] = null;
        return this.trigger("form-submit-notify", [this, e]),
        this
    },
    t.fn.ajaxForm = function(s) {
        if (s = s || {},
        s.delegation = s.delegation && t.isFunction(t.fn.on), !s.delegation && 0 === this.length) {
            var o = {
                s: this.selector,
                c: this.context
            };
            return ! t.isReady && o.s ? (n("DOM not ready, queuing ajaxForm"), t(function() {
                t(o.s, o.c).ajaxForm(s)
            }), this) : (n("terminating; zero elements found by selector" + (t.isReady ? "": " (DOM not ready)")), this)
        }
        return s.delegation ? (t(document).off("submit.form-plugin", this.selector, e).off("click.form-plugin", this.selector, i).on("submit.form-plugin", this.selector, s, e).on("click.form-plugin", this.selector, s, i), this) : this.ajaxFormUnbind().bind("submit.form-plugin", s, e).bind("click.form-plugin", s, i)
    },
    t.fn.ajaxFormUnbind = function() {
        return this.unbind("submit.form-plugin click.form-plugin")
    },
    t.fn.formToArray = function(e, i) {
        var n = [];
        if (0 === this.length) return n;
        var o = this[0],
        r = e ? o.getElementsByTagName("*") : o.elements;
        if (!r) return n;
        var a, l, c, h, u, d, p;
        for (a = 0, d = r.length; d > a; a++) if (u = r[a], c = u.name, c && !u.disabled) if (e && o.clk && "image" == u.type) o.clk == u && (n.push({
            name: c,
            value: t(u).val(),
            type: u.type
        }), n.push({
            name: c + ".x",
            value: o.clk_x
        },
        {
            name: c + ".y",
            value: o.clk_y
        }));
        else if (h = t.fieldValue(u, !0), h && h.constructor == Array) for (i && i.push(u), l = 0, p = h.length; p > l; l++) n.push({
            name: c,
            value: h[l]
        });
        else if (s.fileapi && "file" == u.type) {
            i && i.push(u);
            var f = u.files;
            if (f.length) for (l = 0; l < f.length; l++) n.push({

                name: c,
                value: f[l],
                type: u.type
            });
            else n.push({
                name: c,
                value: "",
                type: u.type
            })
        } else null !== h && "undefined" != typeof h && (i && i.push(u), n.push({
            name: c,
            value: h,
            type: u.type,
            required: u.required
        }));
        if (!e && o.clk) {
            var m = t(o.clk),
            g = m[0];
            c = g.name,
            c && !g.disabled && "image" == g.type && (n.push({
                name: c,
                value: m.val()
            }), n.push({
                name: c + ".x",
                value: o.clk_x
            },
            {
                name: c + ".y",
                value: o.clk_y
            }))
        }
        return n
    },
    t.fn.formSerialize = function(e) {
        return t.param(this.formToArray(e))
    },
    t.fn.fieldSerialize = function(e) {
        var i = [];
        return this.each(function() {
            var n = this.name;
            if (n) {
                var s = t.fieldValue(this, e);
                if (s && s.constructor == Array) for (var o = 0,
                r = s.length; r > o; o++) i.push({
                    name: n,
                    value: s[o]
                });
                else null !== s && "undefined" != typeof s && i.push({
                    name: this.name,
                    value: s
                })
            }
        }),
        t.param(i)
    },
    t.fn.fieldValue = function(e) {
        for (var i = [], n = 0, s = this.length; s > n; n++) {
            var o = this[n],
            r = t.fieldValue(o, e);
            null === r || "undefined" == typeof r || r.constructor == Array && !r.length || (r.constructor == Array ? t.merge(i, r) : i.push(r))
        }
        return i
    },
    t.fieldValue = function(e, i) {
        var n = e.name,
        s = e.type,
        o = e.tagName.toLowerCase();
        if (void 0 === i && (i = !0), i && (!n || e.disabled || "reset" == s || "button" == s || ("checkbox" == s || "radio" == s) && !e.checked || ("submit" == s || "image" == s) && e.form && e.form.clk != e || "select" == o && -1 == e.selectedIndex)) return null;
        if ("select" == o) {
            var r = e.selectedIndex;
            if (0 > r) return null;
            for (var a = [], l = e.options, c = "select-one" == s, h = c ? r + 1 : l.length, u = c ? r: 0; h > u; u++) {
                var d = l[u];
                if (d.selected) {
                    var p = d.value;
                    if (p || (p = d.attributes && d.attributes.value && !d.attributes.value.specified ? d.text: d.value), c) return p;
                    a.push(p)
                }
            }
            return a
        }
        return t(e).val()
    },
    t.fn.clearForm = function(e) {
        return this.each(function() {
            t("input,select,textarea", this).clearFields(e)
        })
    },
    t.fn.clearFields = t.fn.clearInputs = function(e) {
        var i = /^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i;
        return this.each(function() {
            var n = this.type,
            s = this.tagName.toLowerCase();
            i.test(n) || "textarea" == s ? this.value = "": "checkbox" == n || "radio" == n ? this.checked = !1 : "select" == s ? this.selectedIndex = -1 : "file" == n ? /MSIE/.test(navigator.userAgent) ? t(this).replaceWith(t(this).clone(!0)) : t(this).val("") : e && (e === !0 && /hidden/.test(n) || "string" == typeof e && t(this).is(e)) && (this.value = "")
        })
    },
    t.fn.resetForm = function() {
        return this.each(function() { ("function" == typeof this.reset || "object" == typeof this.reset && !this.reset.nodeType) && this.reset()
        })
    },
    t.fn.enable = function(t) {
        return void 0 === t && (t = !0),
        this.each(function() {
            this.disabled = !t
        })
    },
    t.fn.selected = function(e) {
        return void 0 === e && (e = !0),
        this.each(function() {
            var i = this.type;
            if ("checkbox" == i || "radio" == i) this.checked = e;
            else if ("option" == this.tagName.toLowerCase()) {
                var n = t(this).parent("select");
                e && n[0] && "select-one" == n[0].type && n.find("option").selected(!1),
                this.selected = e
            }
        })
    },
    t.fn.ajaxSubmit.debug = !1
}),
define("startup", ["jquery", "application", "jquery-form"],
function(t, e) {
    function n() {
        var e = t("#industry-list").val();
        t("#theme-list option.list").each(function() {
            var i = t(this).data("industry");
            i != e ? t(this).hide().attr("disabled", !0) : t(this).show().removeAttr("disabled")
        }),
        t("body").removeAttr("class"),
        t("#theme-list option").removeAttr("selected").eq(0).attr("selected", "selected")
    }
    function s() {
        var t = e.collections.pageElementList.models,
        n = {};
        for (i in t) n[t[i].cid] = t[i].getValues(t[i].attributes);
        return n = JSON.stringify(n)
    }
    function o() {
        return McMore.current_page.options = "",
        JSON.stringify(McMore.current_page)
    }
    t(window).bind("beforeunload",
    function() {
        return "在离开页面前请确保您的操作已保存，确定离开此页面吗？"
    }),
    t("#industry-list").on("change",
    function() {
        n()
    }),
    t("#industry-list").length > 0 && n(),
    t("#theme-list").on("change",
    function() {
        var e = t(this).val();
        t("body").removeAttr("class").addClass(e)
    }),
    t("#save-template").click(function() {
        var i = s(),
        n = e.sortResult.sortable("serialize", {
            key: "sort"
        }),
        o = {};
        o.id = t("#page-id").val(),
        o.theme = t("#theme-list").val(),
        o.name = t("#template-name").val(),
        o.industry_id = t("#industry-list").val(),
        t("#image-upload").ajaxSubmit({
            success: function(e) {
                1 == e.state ? t.ajax({
                    url: "/templates",
                    type: "post",
                    data: {
                        elements: i,
                        sort: n,
                        thumb: e.url,
                        page_options: JSON.stringify(o)
                    },
                    dataType: "json",
                    success: function(t) {
                        alert(t.msg)
                    }
                }) : alert(e.msg)
            }
        })
    }),
    t(".theme-item").on("click",
    function() {
        t(".theme-item").removeClass("active"),
        t(this).addClass("active");
        var e = t(this).data("code"),
        i = t(this).data("id");
        McMore.current_page.theme = e,
        t("body").removeAttr("class").addClass(e),
        t(".template-item").each(function() {
            t(this).data("theme") != i ? t(this).addClass("hide") : t(this).removeClass("hide")
        })
    }),
    t(".theme-item").on("mouseover",
    function() {
        var e = t(this).data("code");
        t("body").removeAttr("class").addClass(e)
    }),
    t(".select-themes").on("mouseleave",
    function() {
        var e = McMore.current_page.theme;
        t("body").removeAttr("class").addClass(e)
    }),
    t(".template-item").on("click",
    function() {
        if (!confirm("选择模板后会重置所有配置，你是否确定选择该模板？")) return ! 1;
        var i = t(this).data("id");
        t.ajax({
            url: "/templates/" + i + "/config",
            type: "get",
            dataType: "json",
            success: function(i) {
                t(".component-config-wrapper").slideUp(300),
                e.resetPreviewView(i.options)
            }
        })
    }),
    t(".versions").delegate(".version-item", "click",
    function() {
        t(".version-item").removeClass("active"),
        t(this).addClass("active");
        var i = t(this).data("id"),
        n = "/versions/" + i;
        t(this).hasClass("current") ? (n = "/versions/" + i + "?current=1", t("#version-id").val(t("#draft-v").data("id"))) : t("#version-id").val(i),
        t.ajax({
            url: n,
            type: "get",
            dataType: "json",
            success: function(i) {
                t(".select-themes dl").addClass("hide"),
                t('.theme-item[data-code="' + i.theme + '"]').click().parent().removeClass("hide"),
                t(".component-config-wrapper").slideUp(300),
                e.resetPreviewView(i.options)
            }
        })
    }),
    t("body").on("click",
    function() {
        t(".qrcode-wrapper").fadeOut()
    }),
    t("#save-preview").click(function() {
        var i = s(),
        n = e.sortResult.sortable("serialize", {
            key: "sort"
        }),
        r = o(),
        a = t("#version-id").val();
        t.ajax({
            url: "/versions/" + a,
            type: "post",
            data: {
                elements: i,
                sort: n,
                page_options: r
            },
            dataType: "json",
            success: function(e) {
                1 == e.state ? t(".qrcode-wrapper.preview").fadeIn() : (t(".qrcode-wrapper").fadeOut(), alert(e.msg))
            }
        })
    }),
    t("#publish").click(function() {
        var i = s(),
        n = e.sortResult.sortable("serialize", {
            key: "sort"
        }),
        r = o();
        t.ajax({
            url: "/pages/" + McMore.current_page.id,
            type: "post",
            data: {
                elements: i,
                sort: n,
                page_options: r
            },
            dataType: "json",
            success: function(e) {
                1 == e.state ? t(".qrcode-wrapper.view").fadeIn() : (t(".qrcode-wrapper").fadeOut(), alert(e.msg))
            }
        })
    }),
    t("#save").click(function() {
        var i = s(),
        n = e.sortResult.sortable("serialize", {
            key: "sort"
        }),
        r = o(),
        a = t("#version-id").val();
        t.ajax({
            url: _save_url,
            type: "post",
            data: {
                elements: i,
                sort: n,
                page_options: r
            },
            dataType: "json",
            success: function(e) {
                1 == e.state ? (alert("保存成功"), t(".version-item").removeClass("active"), t('.version-item[data-id="' + a + '"]').addClass("active")) : alert(e.msg)
            }
        })
    }),
    t("#save-to").click(function() {
        t("#version-name").val(""),
        t(".version-form-wrapper").fadeToggle()
    }),
    t("#cancel-button").click(function() {
        t(".version-form-wrapper").fadeOut()
    }),
    t("#save-to-button").click(function() {
        var i = s(),
        n = e.sortResult.sortable("serialize", {
            key: "sort"
        }),
        r = o(),
        a = t("#version-name").val();
        t.ajax({
            url: "/versions/0",
            type: "post",
            data: {
                elements: i,
                sort: n,
                page_options: r,
                version_name: a
            },
            dataType: "json",
            success: function(e) {
                1 == e.state ? (t("#draft-v").after('<li class="version-wrapper"><span class="version-item" data-id="' + e.vid + '">' + e.name + '</span> <span class="delete-v" data-id="' + e.vid + '">删除</span></li>'), alert("保存成功"), t(".version-form-wrapper").fadeOut()) : alert(e.msg)
            }
        })
    }),
    t(".versions").delegate(".delete-v", "click",
    function() {
        var e = t(this).data("id"),
        i = t(this).closest("li");
        t.ajax({
            url: "/versions/" + e + "/delete",
            type: "post",
            dataType: "json",
            success: function(e) {
                1 == e.state ? i.slideUp(function() {
                    t(this).remove()
                }) : alert(e.msg)
            }
        })
    })
}),
function(t) {
    function e(t, e, s) {
        var o = t[0],
        r = /er/.test(s) ? _indeterminate: /bl/.test(s) ? f: d,
        a = s == _update ? {
            checked: o[d],
            disabled: o[f],
            indeterminate: "true" == t.attr(_indeterminate) || "false" == t.attr(_determinate)
        }: o[r];
        if (/^(ch|di|in)/.test(s) && !a) i(t, r);
        else if (/^(un|en|de)/.test(s) && a) n(t, r);
        else if (s == _update) for (var l in a) a[l] ? i(t, l, !0) : n(t, l, !0);
        else e && "toggle" != s || (e || t[_callback]("ifClicked"), a ? o[_type] !== u && n(t, r) : i(t, r))
    }
    function i(e, i, s) {
        var h = e[0],
        m = e.parent(),
        g = i == d,
        v = i == _indeterminate,
        y = i == f,
        b = v ? _determinate: g ? p: "enabled",
        _ = o(e, b + r(h[_type])),
        w = o(e, i + r(h[_type]));
        if (h[i] !== !0) {
            if (!s && i == d && h[_type] == u && h.name) {
                var x = e.closest("form"),
                C = 'input[name="' + h.name + '"]';
                C = x.length ? x.find(C) : t(C),
                C.each(function() {
                    this !== h && t(this).data(l) && n(t(this), i)
                })
            }
            v ? (h[i] = !0, h[d] && n(e, d, "force")) : (s || (h[i] = !0), g && h[_indeterminate] && n(e, _indeterminate, !1)),
            a(e, g, i, s)
        }
        h[f] && o(e, _cursor, !0) && m.find("." + c).css(_cursor, "default"),
        m[_add](w || o(e, i) || ""),
        m.attr("role") && !v && m.attr("aria-" + (y ? f: d), "true"),
        m[_remove](_ || o(e, b) || "")
    }
    function n(t, e, i) {
        var n = t[0],
        s = t.parent(),
        l = e == d,
        h = e == _indeterminate,
        u = e == f,
        m = h ? _determinate: l ? p: "enabled",
        g = o(t, m + r(n[_type])),
        v = o(t, e + r(n[_type]));
        n[e] !== !1 && ((h || !i || "force" == i) && (n[e] = !1), a(t, l, m, i)),
        !n[f] && o(t, _cursor, !0) && s.find("." + c).css(_cursor, "pointer"),
        s[_remove](v || o(t, e) || ""),
        s.attr("role") && !h && s.attr("aria-" + (u ? f: d), "false"),
        s[_add](g || o(t, m) || "")
    }
    function s(e, i) {
        e.data(l) && (e.parent().html(e.attr("style", e.data(l).s || "")), i && e[_callback](i), e.off(".i").unwrap(), t(_label + '[for="' + e[0].id + '"]').add(e.closest(_label)).off(".i"))
    }
    function o(t, e, i) {
        return t.data(l) ? t.data(l).o[e + (i ? "": "Class")] : void 0
    }
    function r(t) {
        return t.charAt(0).toUpperCase() + t.slice(1)
    }
    function a(t, e, i, n) {
        n || (e && t[_callback]("ifToggled"), t[_callback]("ifChanged")[_callback]("if" + r(i)))
    }
    var l = "iCheck",
    c = l + "-helper",
    h = "checkbox",
    u = "radio",
    d = "checked",
    p = "un" + d,
    f = "disabled";
    _determinate = "determinate",
    _indeterminate = "in" + _determinate,
    _update = "update",
    _type = "type",
    _click = "click",
    _touch = "touchbegin.i touchend.i",
    _add = "addClass",
    _remove = "removeClass",
    _callback = "trigger",
    _label = "label",
    _cursor = "cursor",
    _mobile = /ipad|iphone|ipod|android|blackberry|windows phone|opera mini|silk/i.test(navigator.userAgent),
    t.fn[l] = function(o, r) {
        var a = 'input[type="' + h + '"], input[type="' + u + '"]',
        p = t(),
        m = function(e) {
            e.each(function() {
                var e = t(this);
                p = p.add(e.is(a) ? e: e.find(a))
            })
        };
        if (/^(check|uncheck|toggle|indeterminate|determinate|disable|enable|update|destroy)$/i.test(o)) return o = o.toLowerCase(),
        m(this),
        p.each(function() {
            var i = t(this);
            "destroy" == o ? s(i, "ifDestroyed") : e(i, !0, o),
            t.isFunction(r) && r()
        });
        if ("object" != typeof o && o) return this;
        var g = t.extend({
            checkedClass: d,
            disabledClass: f,
            indeterminateClass: _indeterminate,
            labelHover: !0
        },
        o),
        v = g.handle,
        y = g.hoverClass || "hover",
        b = g.focusClass || "focus",
        _ = g.activeClass || "active",
        w = !!g.labelHover,
        x = g.labelHoverClass || "hover",
        C = 0 | ("" + g.increaseArea).replace("%", "");
        return (v == h || v == u) && (a = 'input[type="' + v + '"]'),
        -50 > C && (C = -50),
        m(this),
        p.each(function() {
            var o = t(this);
            s(o);
            var r, a = this,
            p = a.id,
            m = -C + "%",
            v = 100 + 2 * C + "%",
            k = {
                position: "absolute",
                top: m,
                left: m,
                display: "block",
                width: v,
                height: v,
                margin: 0,
                padding: 0,
                background: "#fff",
                border: 0,
                opacity: 0
            },
            T = _mobile ? {
                position: "absolute",
                visibility: "hidden"
            }: C ? k: {
                position: "absolute",
                opacity: 0
            },
            D = a[_type] == h ? g.checkboxClass || "i" + h: g.radioClass || "i" + u,
            E = t(_label + '[for="' + p + '"]').add(o.closest(_label)),
            M = !!g.aria,
            I = l + "-" + Math.random().toString(36).substr(2, 6),
            S = '<div class="' + D + '" ' + (M ? 'role="' + a[_type] + '" ': "");
            M && E.each(function() {
                S += 'aria-labelledby="',
                this.id ? S += this.id: (this.id = I, S += I),
                S += '"'
            }),
            S = o.wrap(S + "/>")[_callback]("ifCreated").parent().append(g.insert),
            r = t('<ins class="' + c + '"/>').css(k).appendTo(S),
            o.data(l, {
                o: g,
                s: o.attr("style")
            }).css(T),
            !!g.inheritClass && S[_add](a.className || ""),
            !!g.inheritID && p && S.attr("id", l + "-" + p),
            "static" == S.css("position") && S.css("position", "relative"),
            e(o, !0, _update),
            E.length && E.on(_click + ".i mouseover.i mouseout.i " + _touch,
            function(i) {
                var n = i[_type],
                s = t(this);
                if (!a[f]) {
                    if (n == _click) {
                        if (t(i.target).is("a")) return;
                        e(o, !1, !0)
                    } else w && (/ut|nd/.test(n) ? (S[_remove](y), s[_remove](x)) : (S[_add](y), s[_add](x)));
                    if (!_mobile) return ! 1;
                    i.stopPropagation()
                }
            }),
            o.on(_click + ".i focus.i blur.i keyup.i keydown.i keypress.i",
            function(t) {
                var e = t[_type],
                s = t.keyCode;
                return e == _click ? !1 : "keydown" == e && 32 == s ? (a[_type] == u && a[d] || (a[d] ? n(o, d) : i(o, d)), !1) : void("keyup" == e && a[_type] == u ? !a[d] && i(o, d) : /us|ur/.test(e) && S["blur" == e ? _remove: _add](b))
            }),
            r.on(_click + " mousedown mouseup mouseover mouseout " + _touch,
            function(t) {
                var i = t[_type],
                n = /wn|up/.test(i) ? _: y;
                if (!a[f]) {
                    if (i == _click ? e(o, !1, !0) : (/wn|er|in/.test(i) ? S[_add](n) : S[_remove](n + " " + _), E.length && w && n == y && E[/ut|nd/.test(i) ? _remove: _add](x)), !_mobile) return ! 1;
                    t.stopPropagation()
                }
            })
        })
    }
} (window.jQuery || window.Zepto),
define("iCheck",
function() {}),
function(t, e, i, n) {
    "use strict";
    var s = i("html"),
    o = i(t),
    r = i(e),
    a = i.fancybox = function() {
        a.open.apply(this, arguments)
    },
    l = navigator.userAgent.match(/msie/i),
    c = null,
    h = e.createTouch !== n,
    u = function(t) {
        return t && t.hasOwnProperty && t instanceof i
    },
    d = function(t) {
        return t && "string" === i.type(t)
    },
    p = function(t) {
        return d(t) && t.indexOf("%") > 0
    },
    f = function(t) {
        return t && !(t.style.overflow && "hidden" === t.style.overflow) && (t.clientWidth && t.scrollWidth > t.clientWidth || t.clientHeight && t.scrollHeight > t.clientHeight)
    },
    m = function(t, e) {
        var i = parseInt(t, 10) || 0;
        return e && p(t) && (i = a.getViewport()[e] / 100 * i),
        Math.ceil(i)
    },
    g = function(t, e) {
        return m(t, e) + "px"
    };
    i.extend(a, {
        version: "2.1.5",
        defaults: {
            padding: 15,
            margin: 20,
            width: 800,
            height: 600,
            minWidth: 100,
            minHeight: 100,
            maxWidth: 9999,
            maxHeight: 9999,
            pixelRatio: 1,
            autoSize: !0,
            autoHeight: !1,
            autoWidth: !1,
            autoResize: !0,
            autoCenter: !h,
            fitToView: !0,
            aspectRatio: !1,
            topRatio: .5,
            leftRatio: .5,
            scrolling: "auto",
            wrapCSS: "",
            arrows: !0,
            closeBtn: !0,
            closeClick: !1,
            nextClick: !1,
            mouseWheel: !0,
            autoPlay: !1,
            playSpeed: 3e3,
            preload: 3,
            modal: !1,
            loop: !0,
            ajax: {
                dataType: "html",
                headers: {
                    "X-fancyBox": !0
                }
            },
            iframe: {
                scrolling: "auto",
                preload: !0
            },
            swf: {
                wmode: "transparent",
                allowfullscreen: "true",
                allowscriptaccess: "always"
            },
            keys: {
                next: {
                    13 : "left",
                    34 : "up",
                    39 : "left",
                    40 : "up"
                },
                prev: {
                    8 : "right",
                    33 : "down",
                    37 : "right",
                    38 : "down"
                },
                close: [27],
                play: [32],
                toggle: [70]
            },
            direction: {
                next: "left",
                prev: "right"
            },
            scrollOutside: !0,
            index: 0,
            type: null,
            href: null,
            content: null,
            title: null,
            tpl: {
                wrap: '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>',
                image: '<img class="fancybox-image" src="{href}" alt="" />',
                iframe: '<iframe id="fancybox-frame{rnd}" name="fancybox-frame{rnd}" class="fancybox-iframe" frameborder="0" vspace="0" hspace="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen' + (l ? ' allowtransparency="true"': "") + "></iframe>",
                error: '<p class="fancybox-error">The requested content cannot be loaded.<br/>Please try again later.</p>',
                closeBtn: '<a title="Close" class="fancybox-item fancybox-close" href="javascript:;"></a>',
                next: '<a title="Next" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
                prev: '<a title="Previous" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>'
            },
            openEffect: "fade",
            openSpeed: 250,
            openEasing: "swing",
            openOpacity: !0,
            openMethod: "zoomIn",
            closeEffect: "fade",
            closeSpeed: 250,
            closeEasing: "swing",
            closeOpacity: !0,
            closeMethod: "zoomOut",
            nextEffect: "elastic",
            nextSpeed: 250,
            nextEasing: "swing",
            nextMethod: "changeIn",
            prevEffect: "elastic",
            prevSpeed: 250,
            prevEasing: "swing",
            prevMethod: "changeOut",
            helpers: {
                overlay: !0,
                title: !0
            },
            onCancel: i.noop,
            beforeLoad: i.noop,
            afterLoad: i.noop,
            beforeShow: i.noop,
            afterShow: i.noop,
            beforeChange: i.noop,
            beforeClose: i.noop,
            afterClose: i.noop
        },
        group: {},
        opts: {},
        previous: null,
        coming: null,
        current: null,
        isActive: !1,
        isOpen: !1,
        isOpened: !1,
        wrap: null,
        skin: null,
        outer: null,
        inner: null,
        player: {
            timer: null,
            isActive: !1
        },
        ajaxLoad: null,
        imgPreload: null,
        transitions: {},
        helpers: {},
        open: function(t, e) {
            return t && (i.isPlainObject(e) || (e = {}), !1 !== a.close(!0)) ? (i.isArray(t) || (t = u(t) ? i(t).get() : [t]), i.each(t,
            function(s, o) {
                var r, l, c, h, p, f, m, g = {};
                "object" === i.type(o) && (o.nodeType && (o = i(o)), u(o) ? (g = {
                    href: o.data("fancybox-href") || o.attr("href"),
                    title: o.data("fancybox-title") || o.attr("title"),
                    isDom: !0,
                    element: o
                },
                i.metadata && i.extend(!0, g, o.metadata())) : g = o),
                r = e.href || g.href || (d(o) ? o: null),
                l = e.title !== n ? e.title: g.title || "",
                c = e.content || g.content,
                h = c ? "html": e.type || g.type,
                !h && g.isDom && (h = o.data("fancybox-type"), h || (p = o.prop("class").match(/fancybox\.(\w+)/), h = p ? p[1] : null)),
                d(r) && (h || (a.isImage(r) ? h = "image": a.isSWF(r) ? h = "swf": "#" === r.charAt(0) ? h = "inline": d(o) && (h = "html", c = o)), "ajax" === h && (f = r.split(/\s+/, 2), r = f.shift(), m = f.shift())),
                c || ("inline" === h ? r ? c = i(d(r) ? r.replace(/.*(?=#[^\s]+$)/, "") : r) : g.isDom && (c = o) : "html" === h ? c = r: h || r || !g.isDom || (h = "inline", c = o)),
                i.extend(g, {
                    href: r,
                    type: h,
                    content: c,
                    title: l,
                    selector: m
                }),
                t[s] = g
            }), a.opts = i.extend(!0, {},
            a.defaults, e), e.keys !== n && (a.opts.keys = e.keys ? i.extend({},
            a.defaults.keys, e.keys) : !1), a.group = t, a._start(a.opts.index)) : void 0
        },
        cancel: function() {
            var t = a.coming;
            t && !1 !== a.trigger("onCancel") && (a.hideLoading(), a.ajaxLoad && a.ajaxLoad.abort(), a.ajaxLoad = null, a.imgPreload && (a.imgPreload.onload = a.imgPreload.onerror = null), t.wrap && t.wrap.stop(!0, !0).trigger("onReset").remove(), a.coming = null, a.current || a._afterZoomOut(t))
        },
        close: function(t) {
            a.cancel(),
            !1 !== a.trigger("beforeClose") && (a.unbindEvents(), a.isActive && (a.isOpen && t !== !0 ? (a.isOpen = a.isOpened = !1, a.isClosing = !0, i(".fancybox-item, .fancybox-nav").remove(), a.wrap.stop(!0, !0).removeClass("fancybox-opened"), a.transitions[a.current.closeMethod]()) : (i(".fancybox-wrap").stop(!0).trigger("onReset").remove(), a._afterZoomOut())))
        },
        play: function(t) {
            var e = function() {
                clearTimeout(a.player.timer)
            },
            i = function() {
                e(),
                a.current && a.player.isActive && (a.player.timer = setTimeout(a.next, a.current.playSpeed))
            },
            n = function() {
                e(),
                r.unbind(".player"),
                a.player.isActive = !1,
                a.trigger("onPlayEnd")
            },
            s = function() {
                a.current && (a.current.loop || a.current.index < a.group.length - 1) && (a.player.isActive = !0, r.bind({
                    "onCancel.player beforeClose.player": n,
                    "onUpdate.player": i,
                    "beforeLoad.player": e
                }), i(), a.trigger("onPlayStart"))
            };
            t === !0 || !a.player.isActive && t !== !1 ? s() : n()
        },
        next: function(t) {
            var e = a.current;
            e && (d(t) || (t = e.direction.next), a.jumpto(e.index + 1, t, "next"))
        },
        prev: function(t) {
            var e = a.current;
            e && (d(t) || (t = e.direction.prev), a.jumpto(e.index - 1, t, "prev"))
        },
        jumpto: function(t, e, i) {
            var s = a.current;
            s && (t = m(t), a.direction = e || s.direction[t >= s.index ? "next": "prev"], a.router = i || "jumpto", s.loop && (0 > t && (t = s.group.length + t % s.group.length), t %= s.group.length), s.group[t] !== n && (a.cancel(), a._start(t)))
        },
        reposition: function(t, e) {
            var n, s = a.current,
            o = s ? s.wrap: null;
            o && (n = a._getPosition(e), t && "scroll" === t.type ? (delete n.position, o.stop(!0, !0).animate(n, 200)) : (o.css(n), s.pos = i.extend({},
            s.dim, n)))
        },
        update: function(t) {
            var e = t && t.type,
            i = !e || "orientationchange" === e;
            i && (clearTimeout(c), c = null),
            a.isOpen && !c && (c = setTimeout(function() {
                var n = a.current;
                n && !a.isClosing && (a.wrap.removeClass("fancybox-tmp"), (i || "load" === e || "resize" === e && n.autoResize) && a._setDimension(), "scroll" === e && n.canShrink || a.reposition(t), a.trigger("onUpdate"), c = null)
            },
            i && !h ? 0 : 300))
        },
        toggle: function(t) {
            a.isOpen && (a.current.fitToView = "boolean" === i.type(t) ? t: !a.current.fitToView, h && (a.wrap.removeAttr("style").addClass("fancybox-tmp"), a.trigger("onUpdate")), a.update())
        },
        hideLoading: function() {
            r.unbind(".loading"),
            i("#fancybox-loading").remove()
        },
        showLoading: function() {
            var t, e;
            a.hideLoading(),
            t = i('<div id="fancybox-loading"><div></div></div>').click(a.cancel).appendTo("body"),
            r.bind("keydown.loading",
            function(t) {
                27 === (t.which || t.keyCode) && (t.preventDefault(), a.cancel())
            }),
            a.defaults.fixed || (e = a.getViewport(), t.css({
                position: "absolute",
                top: .5 * e.h + e.y,
                left: .5 * e.w + e.x
            }))
        },
        getViewport: function() {
            var e = a.current && a.current.locked || !1,
            i = {
                x: o.scrollLeft(),
                y: o.scrollTop()
            };
            return e ? (i.w = e[0].clientWidth, i.h = e[0].clientHeight) : (i.w = h && t.innerWidth ? t.innerWidth: o.width(), i.h = h && t.innerHeight ? t.innerHeight: o.height()),
            i
        },
        unbindEvents: function() {
            a.wrap && u(a.wrap) && a.wrap.unbind(".fb"),
            r.unbind(".fb"),
            o.unbind(".fb")
        },
        bindEvents: function() {
            var t, e = a.current;
            e && (o.bind("orientationchange.fb" + (h ? "": " resize.fb") + (e.autoCenter && !e.locked ? " scroll.fb": ""), a.update), t = e.keys, t && r.bind("keydown.fb",
            function(s) {
                var o = s.which || s.keyCode,
                r = s.target || s.srcElement;
                return 27 === o && a.coming ? !1 : void(s.ctrlKey || s.altKey || s.shiftKey || s.metaKey || r && (r.type || i(r).is("[contenteditable]")) || i.each(t,
                function(t, r) {
                    return e.group.length > 1 && r[o] !== n ? (a[t](r[o]), s.preventDefault(), !1) : i.inArray(o, r) > -1 ? (a[t](), s.preventDefault(), !1) : void 0
                }))
            }), i.fn.mousewheel && e.mouseWheel && a.wrap.bind("mousewheel.fb",
            function(t, n, s, o) {
                for (var r = t.target || null,
                l = i(r), c = !1; l.length && !(c || l.is(".fancybox-skin") || l.is(".fancybox-wrap"));) c = f(l[0]),
                l = i(l).parent();
                0 === n || c || a.group.length > 1 && !e.canShrink && (o > 0 || s > 0 ? a.prev(o > 0 ? "down": "left") : (0 > o || 0 > s) && a.next(0 > o ? "up": "right"), t.preventDefault())
            }))
        },
        trigger: function(t, e) {
            var n, s = e || a.coming || a.current;
            if (s) {
                if (i.isFunction(s[t]) && (n = s[t].apply(s, Array.prototype.slice.call(arguments, 1))), n === !1) return ! 1;
                s.helpers && i.each(s.helpers,
                function(e, n) {
                    n && a.helpers[e] && i.isFunction(a.helpers[e][t]) && a.helpers[e][t](i.extend(!0, {},
                    a.helpers[e].defaults, n), s)
                }),
                r.trigger(t)
            }
        },
        isImage: function(t) {
            return d(t) && t.match(/(^data:image\/.*,)|(\.(jp(e|g|eg)|gif|png|bmp|webp|svg)((\?|#).*)?$)/i)
        },
        isSWF: function(t) {
            return d(t) && t.match(/\.(swf)((\?|#).*)?$/i)
        },
        _start: function(t) {
            var e, n, s, o, r, l = {};
            if (t = m(t), e = a.group[t] || null, !e) return ! 1;
            if (l = i.extend(!0, {},
            a.opts, e), o = l.margin, r = l.padding, "number" === i.type(o) && (l.margin = [o, o, o, o]), "number" === i.type(r) && (l.padding = [r, r, r, r]), l.modal && i.extend(!0, l, {
                closeBtn: !1,
                closeClick: !1,
                nextClick: !1,
                arrows: !1,
                mouseWheel: !1,
                keys: null,
                helpers: {
                    overlay: {
                        closeClick: !1
                    }
                }
            }), l.autoSize && (l.autoWidth = l.autoHeight = !0), "auto" === l.width && (l.autoWidth = !0), "auto" === l.height && (l.autoHeight = !0), l.group = a.group, l.index = t, a.coming = l, !1 === a.trigger("beforeLoad")) return void(a.coming = null);
            if (s = l.type, n = l.href, !s) return a.coming = null,
            a.current && a.router && "jumpto" !== a.router ? (a.current.index = t, a[a.router](a.direction)) : !1;
            if (a.isActive = !0, ("image" === s || "swf" === s) && (l.autoHeight = l.autoWidth = !1, l.scrolling = "visible"), "image" === s && (l.aspectRatio = !0), "iframe" === s && h && (l.scrolling = "scroll"), l.wrap = i(l.tpl.wrap).addClass("fancybox-" + (h ? "mobile": "desktop") + " fancybox-type-" + s + " fancybox-tmp " + l.wrapCSS).appendTo(l.parent || "body"), i.extend(l, {
                skin: i(".fancybox-skin", l.wrap),
                outer: i(".fancybox-outer", l.wrap),
                inner: i(".fancybox-inner", l.wrap)
            }), i.each(["Top", "Right", "Bottom", "Left"],
            function(t, e) {
                l.skin.css("padding" + e, g(l.padding[t]))
            }), a.trigger("onReady"), "inline" === s || "html" === s) {
                if (!l.content || !l.content.length) return a._error("content")
            } else if (!n) return a._error("href");
            "image" === s ? a._loadImage() : "ajax" === s ? a._loadAjax() : "iframe" === s ? a._loadIframe() : a._afterLoad()
        },
        _error: function(t) {
            i.extend(a.coming, {
                type: "html",
                autoWidth: !0,
                autoHeight: !0,
                minWidth: 0,
                minHeight: 0,
                scrolling: "no",
                hasError: t,
                content: a.coming.tpl.error
            }),
            a._afterLoad()
        },
        _loadImage: function() {
            var t = a.imgPreload = new Image;
            t.onload = function() {
                this.onload = this.onerror = null,
                a.coming.width = this.width / a.opts.pixelRatio,
                a.coming.height = this.height / a.opts.pixelRatio,
                a._afterLoad()
            },
            t.onerror = function() {
                this.onload = this.onerror = null,
                a._error("image")
            },
            t.src = a.coming.href,
            t.complete !== !0 && a.showLoading()
        },
        _loadAjax: function() {
            var t = a.coming;
            a.showLoading(),
            a.ajaxLoad = i.ajax(i.extend({},
            t.ajax, {
                url: t.href,
                error: function(t, e) {
                    a.coming && "abort" !== e ? a._error("ajax", t) : a.hideLoading()
                },
                success: function(e, i) {
                    "success" === i && (t.content = e, a._afterLoad())
                }
            }))
        },
        _loadIframe: function() {
            var t = a.coming,
            e = i(t.tpl.iframe.replace(/\{rnd\}/g, (new Date).getTime())).attr("scrolling", h ? "auto": t.iframe.scrolling).attr("src", t.href);
            i(t.wrap).bind("onReset",
            function() {
                try {
                    i(this).find("iframe").hide().attr("src", "//about:blank").end().empty()
                } catch(t) {}
            }),
            t.iframe.preload && (a.showLoading(), e.one("load",
            function() {
                i(this).data("ready", 1),
                h || i(this).bind("load.fb", a.update),
                i(this).parents(".fancybox-wrap").width("100%").removeClass("fancybox-tmp").show(),
                a._afterLoad()
            })),
            t.content = e.appendTo(t.inner),
            t.iframe.preload || a._afterLoad()
        },
        _preloadImages: function() {
            var t, e, i = a.group,
            n = a.current,
            s = i.length,
            o = n.preload ? Math.min(n.preload, s - 1) : 0;
            for (e = 1; o >= e; e += 1) t = i[(n.index + e) % s],
            "image" === t.type && t.href && ((new Image).src = t.href)
        },
        _afterLoad: function() {
            var t, e, n, s, o, r, l = a.coming,
            c = a.current,
            h = "fancybox-placeholder";
            if (a.hideLoading(), l && a.isActive !== !1) {
                if (!1 === a.trigger("afterLoad", l, c)) return l.wrap.stop(!0).trigger("onReset").remove(),
                void(a.coming = null);
                switch (c && (a.trigger("beforeChange", c), c.wrap.stop(!0).removeClass("fancybox-opened").find(".fancybox-item, .fancybox-nav").remove()), a.unbindEvents(), t = l, e = l.content, n = l.type, s = l.scrolling, i.extend(a, {
                    wrap: t.wrap,
                    skin: t.skin,
                    outer: t.outer,
                    inner: t.inner,
                    current: t,
                    previous: c
                }), o = t.href, n) {
                case "inline":
                case "ajax":
                case "html":
                    t.selector ? e = i("<div>").html(e).find(t.selector) : u(e) && (e.data(h) || e.data(h, i('<div class="' + h + '"></div>').insertAfter(e).hide()), e = e.show().detach(), t.wrap.bind("onReset",
                    function() {
                        i(this).find(e).length && e.hide().replaceAll(e.data(h)).data(h, !1)
                    }));
                    break;
                case "image":
                    e = t.tpl.image.replace("{href}", o);
                    break;
                case "swf":
                    e = '<object id="fancybox-swf" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%"><param name="movie" value="' + o + '"></param>',
                    r = "",
                    i.each(t.swf,
                    function(t, i) {
                        e += '<param name="' + t + '" value="' + i + '"></param>',
                        r += " " + t + '="' + i + '"'
                    }),
                    e += '<embed src="' + o + '" type="application/x-shockwave-flash" width="100%" height="100%"' + r + "></embed></object>"
                }
                u(e) && e.parent().is(t.inner) || t.inner.append(e),
                a.trigger("beforeShow"),
                t.inner.css("overflow", "yes" === s ? "scroll": "no" === s ? "hidden": s),
                a._setDimension(),
                a.reposition(),
                a.isOpen = !1,
                a.coming = null,
                a.bindEvents(),
                a.isOpened ? c.prevMethod && a.transitions[c.prevMethod]() : i(".fancybox-wrap").not(t.wrap).stop(!0).trigger("onReset").remove(),
                a.transitions[a.isOpened ? t.nextMethod: t.openMethod](),
                a._preloadImages()
            }
        },
        _setDimension: function() {
            var t, e, n, s, o, r, l, c, h, u, d, f, v, y, b, _ = a.getViewport(),
            w = 0,
            x = !1,
            C = !1,
            k = a.wrap,
            T = a.skin,
            D = a.inner,
            E = a.current,
            M = E.width,
            I = E.height,
            S = E.minWidth,
            P = E.minHeight,
            A = E.maxWidth,
            N = E.maxHeight,
            O = E.scrolling,
            z = E.scrollOutside ? E.scrollbarWidth: 0,
            H = E.margin,
            j = m(H[1] + H[3]),
            R = m(H[0] + H[2]);
            if (k.add(T).add(D).width("auto").height("auto").removeClass("fancybox-tmp"), t = m(T.outerWidth(!0) - T.width()), e = m(T.outerHeight(!0) - T.height()), n = j + t, s = R + e, o = p(M) ? (_.w - n) * m(M) / 100 : M, r = p(I) ? (_.h - s) * m(I) / 100 : I, "iframe" === E.type) {
                if (y = E.content, E.autoHeight && 1 === y.data("ready")) try {
                    y[0].contentWindow.document.location && (D.width(o).height(9999), b = y.contents().find("body"), z && b.css("overflow-x", "hidden"), r = b.outerHeight(!0))
                } catch($) {}
            } else(E.autoWidth || E.autoHeight) && (D.addClass("fancybox-tmp"), E.autoWidth || D.width(o), E.autoHeight || D.height(r), E.autoWidth && (o = D.width()), E.autoHeight && (r = D.height()), D.removeClass("fancybox-tmp"));
            if (M = m(o), I = m(r), h = o / r, S = m(p(S) ? m(S, "w") - n: S), A = m(p(A) ? m(A, "w") - n: A), P = m(p(P) ? m(P, "h") - s: P), N = m(p(N) ? m(N, "h") - s: N), l = A, c = N, E.fitToView && (A = Math.min(_.w - n, A), N = Math.min(_.h - s, N)), f = _.w - j, v = _.h - R, E.aspectRatio ? (M > A && (M = A, I = m(M / h)), I > N && (I = N, M = m(I * h)), S > M && (M = S, I = m(M / h)), P > I && (I = P, M = m(I * h))) : (M = Math.max(S, Math.min(M, A)), E.autoHeight && "iframe" !== E.type && (D.width(M), I = D.height()), I = Math.max(P, Math.min(I, N))), E.fitToView) if (D.width(M).height(I), k.width(M + t), u = k.width(), d = k.height(), E.aspectRatio) for (; (u > f || d > v) && M > S && I > P && !(w++>19);) I = Math.max(P, Math.min(N, I - 10)),
            M = m(I * h),
            S > M && (M = S, I = m(M / h)),
            M > A && (M = A, I = m(M / h)),
            D.width(M).height(I),
            k.width(M + t),
            u = k.width(),
            d = k.height();
            else M = Math.max(S, Math.min(M, M - (u - f))),
            I = Math.max(P, Math.min(I, I - (d - v)));
            z && "auto" === O && r > I && f > M + t + z && (M += z),
            D.width(M).height(I),
            k.width(M + t),
            u = k.width(),
            d = k.height(),
            x = (u > f || d > v) && M > S && I > P,
            C = E.aspectRatio ? l > M && c > I && o > M && r > I: (l > M || c > I) && (o > M || r > I),
            i.extend(E, {
                dim: {
                    width: g(u),
                    height: g(d)
                },
                origWidth: o,
                origHeight: r,
                canShrink: x,
                canExpand: C,
                wPadding: t,
                hPadding: e,
                wrapSpace: d - T.outerHeight(!0),
                skinSpace: T.height() - I
            }),
            !y && E.autoHeight && I > P && N > I && !C && D.height("auto")
        },
        _getPosition: function(t) {
            var e = a.current,
            i = a.getViewport(),
            n = e.margin,
            s = a.wrap.width() + n[1] + n[3],
            o = a.wrap.height() + n[0] + n[2],
            r = {
                position: "absolute",
                top: n[0],
                left: n[3]
            };
            return e.autoCenter && e.fixed && !t && o <= i.h && s <= i.w ? r.position = "fixed": e.locked || (r.top += i.y, r.left += i.x),
            r.top = g(Math.max(r.top, r.top + (i.h - o) * e.topRatio)),
            r.left = g(Math.max(r.left, r.left + (i.w - s) * e.leftRatio)),
            r
        },
        _afterZoomIn: function() {
            var t = a.current;
            t && (a.isOpen = a.isOpened = !0, a.wrap.css("overflow", "visible").addClass("fancybox-opened"), a.update(), (t.closeClick || t.nextClick && a.group.length > 1) && a.inner.css("cursor", "pointer").bind("click.fb",
            function(e) {
                i(e.target).is("a") || i(e.target).parent().is("a") || (e.preventDefault(), a[t.closeClick ? "close": "next"]())
            }), t.closeBtn && i(t.tpl.closeBtn).appendTo(a.skin).bind("click.fb",
            function(t) {
                t.preventDefault(),
                a.close()
            }), t.arrows && a.group.length > 1 && ((t.loop || t.index > 0) && i(t.tpl.prev).appendTo(a.outer).bind("click.fb", a.prev), (t.loop || t.index < a.group.length - 1) && i(t.tpl.next).appendTo(a.outer).bind("click.fb", a.next)), a.trigger("afterShow"), t.loop || t.index !== t.group.length - 1 ? a.opts.autoPlay && !a.player.isActive && (a.opts.autoPlay = !1, a.play()) : a.play(!1))
        },
        _afterZoomOut: function(t) {
            t = t || a.current,
            i(".fancybox-wrap").trigger("onReset").remove(),
            i.extend(a, {
                group: {},
                opts: {},
                router: !1,
                current: null,
                isActive: !1,
                isOpened: !1,
                isOpen: !1,
                isClosing: !1,
                wrap: null,
                skin: null,
                outer: null,
                inner: null
            }),
            a.trigger("afterClose", t)
        }
    }),
    a.transitions = {
        getOrigPosition: function() {
            var t = a.current,
            e = t.element,
            i = t.orig,
            n = {},
            s = 50,
            o = 50,
            r = t.hPadding,
            l = t.wPadding,
            c = a.getViewport();
            return ! i && t.isDom && e.is(":visible") && (i = e.find("img:first"), i.length || (i = e)),
            u(i) ? (n = i.offset(), i.is("img") && (s = i.outerWidth(), o = i.outerHeight())) : (n.top = c.y + (c.h - o) * t.topRatio, n.left = c.x + (c.w - s) * t.leftRatio),
            ("fixed" === a.wrap.css("position") || t.locked) && (n.top -= c.y, n.left -= c.x),
            n = {
                top: g(n.top - r * t.topRatio),
                left: g(n.left - l * t.leftRatio),
                width: g(s + l),
                height: g(o + r)
            }
        },
        step: function(t, e) {
            var i, n, s, o = e.prop,
            r = a.current,
            l = r.wrapSpace,
            c = r.skinSpace; ("width" === o || "height" === o) && (i = e.end === e.start ? 1 : (t - e.start) / (e.end - e.start), a.isClosing && (i = 1 - i), n = "width" === o ? r.wPadding: r.hPadding, s = t - n, a.skin[o](m("width" === o ? s: s - l * i)), a.inner[o](m("width" === o ? s: s - l * i - c * i)))
        },
        zoomIn: function() {
            var t = a.current,
            e = t.pos,
            n = t.openEffect,
            s = "elastic" === n,
            o = i.extend({
                opacity: 1
            },
            e);
            delete o.position,
            s ? (e = this.getOrigPosition(), t.openOpacity && (e.opacity = .1)) : "fade" === n && (e.opacity = .1),
            a.wrap.css(e).animate(o, {
                duration: "none" === n ? 0 : t.openSpeed,
                easing: t.openEasing,
                step: s ? this.step: null,
                complete: a._afterZoomIn
            })
        },
        zoomOut: function() {
            var t = a.current,
            e = t.closeEffect,
            i = "elastic" === e,
            n = {
                opacity: .1
            };
            i && (n = this.getOrigPosition(), t.closeOpacity && (n.opacity = .1)),
            a.wrap.animate(n, {
                duration: "none" === e ? 0 : t.closeSpeed,
                easing: t.closeEasing,
                step: i ? this.step: null,
                complete: a._afterZoomOut
            })
        },
        changeIn: function() {
            var t, e = a.current,
            i = e.nextEffect,
            n = e.pos,
            s = {
                opacity: 1
            },
            o = a.direction,
            r = 200;
            n.opacity = .1,
            "elastic" === i && (t = "down" === o || "up" === o ? "top": "left", "down" === o || "right" === o ? (n[t] = g(m(n[t]) - r), s[t] = "+=" + r + "px") : (n[t] = g(m(n[t]) + r), s[t] = "-=" + r + "px")),
            "none" === i ? a._afterZoomIn() : a.wrap.css(n).animate(s, {
                duration: e.nextSpeed,
                easing: e.nextEasing,
                complete: a._afterZoomIn
            })
        },
        changeOut: function() {
            var t = a.previous,
            e = t.prevEffect,
            n = {
                opacity: .1
            },
            s = a.direction,
            o = 200;
            "elastic" === e && (n["down" === s || "up" === s ? "top": "left"] = ("up" === s || "left" === s ? "-": "+") + "=" + o + "px"),
            t.wrap.animate(n, {
                duration: "none" === e ? 0 : t.prevSpeed,
                easing: t.prevEasing,
                complete: function() {
                    i(this).trigger("onReset").remove()
                }
            })
        }
    },
    a.helpers.overlay = {
        defaults: {
            closeClick: !0,
            speedOut: 200,
            showEarly: !0,
            css: {},
            locked: !h,
            fixed: !0
        },
        overlay: null,
        fixed: !1,
        el: i("html"),
        create: function(t) {
            t = i.extend({},
            this.defaults, t),
            this.overlay && this.close(),
            this.overlay = i('<div class="fancybox-overlay"></div>').appendTo(a.coming ? a.coming.parent: t.parent),
            this.fixed = !1,
            t.fixed && a.defaults.fixed && (this.overlay.addClass("fancybox-overlay-fixed"), this.fixed = !0)
        },
        open: function(t) {
            var e = this;
            t = i.extend({},
            this.defaults, t),
            this.overlay ? this.overlay.unbind(".overlay").width("auto").height("auto") : this.create(t),
            this.fixed || (o.bind("resize.overlay", i.proxy(this.update, this)), this.update()),
            t.closeClick && this.overlay.bind("click.overlay",
            function(t) {
                return i(t.target).hasClass("fancybox-overlay") ? (a.isActive ? a.close() : e.close(), !1) : void 0
            }),
            this.overlay.css(t.css).show()
        },
        close: function() {
            var t, e;
            o.unbind("resize.overlay"),
            this.el.hasClass("fancybox-lock") && (i(".fancybox-margin").removeClass("fancybox-margin"), t = o.scrollTop(), e = o.scrollLeft(), this.el.removeClass("fancybox-lock"), o.scrollTop(t).scrollLeft(e)),
            i(".fancybox-overlay").remove().hide(),
            i.extend(this, {
                overlay: null,
                fixed: !1
            })
        },
        update: function() {
            var t, i = "100%";
            this.overlay.width(i).height("100%"),
            l ? (t = Math.max(e.documentElement.offsetWidth, e.body.offsetWidth), r.width() > t && (i = r.width())) : r.width() > o.width() && (i = r.width()),
            this.overlay.width(i).height(r.height())
        },
        onReady: function(t, e) {
            var n = this.overlay;
            i(".fancybox-overlay").stop(!0, !0),
            n || this.create(t),
            t.locked && this.fixed && e.fixed && (n || (this.margin = r.height() > o.height() ? i("html").css("margin-right").replace("px", "") : !1), e.locked = this.overlay.append(e.wrap), e.fixed = !1),
            t.showEarly === !0 && this.beforeShow.apply(this, arguments)
        },
        beforeShow: function(t, e) {
            var n, s;
            e.locked && (this.margin !== !1 && (i("*").filter(function() {
                return "fixed" === i(this).css("position") && !i(this).hasClass("fancybox-overlay") && !i(this).hasClass("fancybox-wrap")
            }).addClass("fancybox-margin"), this.el.addClass("fancybox-margin")), n = o.scrollTop(), s = o.scrollLeft(), this.el.addClass("fancybox-lock"), o.scrollTop(n).scrollLeft(s)),
            this.open(t)
        },
        onUpdate: function() {
            this.fixed || this.update()
        },
        afterClose: function(t) {
            this.overlay && !a.coming && this.overlay.fadeOut(t.speedOut, i.proxy(this.close, this))
        }
    },
    a.helpers.title = {
        defaults: {
            type: "float",
            position: "bottom"
        },
        beforeShow: function(t) {
            var e, n, s = a.current,
            o = s.title,
            r = t.type;
            if (i.isFunction(o) && (o = o.call(s.element, s)), d(o) && "" !== i.trim(o)) {
                switch (e = i('<div class="fancybox-title fancybox-title-' + r + '-wrap">' + o + "</div>"), r) {
                case "inside":
                    n = a.skin;
                    break;
                case "outside":
                    n = a.wrap;
                    break;
                case "over":
                    n = a.inner;
                    break;
                default:
                    n = a.skin,
                    e.appendTo("body"),
                    l && e.width(e.width()),
                    e.wrapInner('<span class="child"></span>'),
                    a.current.margin[2] += Math.abs(m(e.css("margin-bottom")))
                }
                e["top" === t.position ? "prependTo": "appendTo"](n)
            }
        }
    },
    i.fn.fancybox = function(t) {
        var e, n = i(this),
        s = this.selector || "",
        o = function(o) {
            var r, l, c = i(this).blur(),
            h = e;
            o.ctrlKey || o.altKey || o.shiftKey || o.metaKey || c.is(".fancybox-wrap") || (r = t.groupAttr || "data-fancybox-group", l = c.attr(r), l || (r = "rel", l = c.get(0)[r]), l && "" !== l && "nofollow" !== l && (c = s.length ? i(s) : n, c = c.filter("[" + r + '="' + l + '"]'), h = c.index(this)), t.index = h, a.open(c, t) !== !1 && o.preventDefault())
        };
        return t = t || {},
        e = t.index || 0,
        s && t.live !== !1 ? r.undelegate(s, "click.fb-start").delegate(s + ":not('.fancybox-item, .fancybox-nav')", "click.fb-start", o) : n.unbind("click.fb-start").bind("click.fb-start", o),
        this.filter("[data-fancybox-start=1]").trigger("click"),
        this
    },
    r.ready(function() {
        var e, o;
        i.scrollbarWidth === n && (i.scrollbarWidth = function() {
            var t = i('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo("body"),
            e = t.children(),
            n = e.innerWidth() - e.height(99).innerWidth();
            return t.remove(),
            n
        }),
        i.support.fixedPosition === n && (i.support.fixedPosition = function() {
            var t = i('<div style="position:fixed;top:20px;"></div>').appendTo("body"),
            e = 20 === t[0].offsetTop || 15 === t[0].offsetTop;
            return t.remove(),
            e
        } ()),
        i.extend(a.defaults, {
            scrollbarWidth: i.scrollbarWidth(),
            fixed: i.support.fixedPosition,
            parent: i("body")
        }),
        e = i(t).width(),
        s.addClass("fancybox-lock-test"),
        o = i(t).width(),
        s.removeClass("fancybox-lock-test"),
        i("<style type='text/css'>.fancybox-margin{margin-right:" + (o - e) + "px;}</style>").appendTo("head")
    })
} (window, document, jQuery),
define("fancybox",
function() {}),
define("components/base", ["backbone.marionette", "mustache", "msgbus", "collections", "iCheck", "fancybox"],
function(t, e, i, n) {
    "use strict";
    var s = {};
    return s.defaults = {},
    s.ConfigView = t.ItemView.extend({
        initialize: function() {
            this.ui = _.extend(this.configUi, this.ui),
            this.events = _.extend(this.configEvent, this.events)
        },
        tagName: "div",
        className: "component-config-wrapper",
        configUi: {
            input_change: "input.with-role",
            img_change: "div.select-wrapper",
            select_change: "select",
            upload_image: ".image-upload"
        },
        configEvent: {
            "change @ui.input_change": "updateModelByInputChange",
            "click @ui.img_change": "updateModelByClick",
            "change @ui.select_change": "updateModelBySelectChange",
            "click @ui.upload_image": "uploadImage"
        },
        onRender: function() {
            var t = this;
            this.$el.find(".spin-button").spinner({
                min: 1,
                stop: function() {
                    var e = t.$el.find(".spin-button").spinner("value");
                    t.model.set("dataLimit", e)
                }
            }),
            this.$el.find("input").iCheck({
                checkboxClass: "icheckbox"
            }).on("ifChecked",
            function() {
                t.model.set($(this).data("role"), $(this).val())
            }),
            this.$el.addClass(this.model.cid),
            "function" == typeof this._onRender && this._onRender()
        },
        updateModelByInputChange: function(t) {
            var e = $(t.currentTarget);
            this.model.set(e.data("role"), e.val())
        },
        updateModelByClick: function(t) {
            var e = $(t.currentTarget);
            if (!e.hasClass("active")) {
                var i = e.data("role"),
                n = e.data("val");
                $('[data-role="' + i + '"]').removeClass("active"),
                e.addClass("active"),
                this.model.set(i, n)
            }
        },
        updateModelBySelectChange: function(t) {
            var e = $(t.currentTarget);
            e.data("role") && this.model.set(e.data("role"), e.val())
        },
        uploadImage: function(t) {
            var e = $(t.currentTarget);
            this.$el.find(".image-upload").removeClass("selected"),
            e.addClass("selected"),
            $.fancybox.open({
                href: _upload_url + "&name=" + e.data("name"),
                type: "iframe",
                fitToView: !1,
                width: 650,
                padding: 0,
                height: "70%",
                autoSize: !1,
                closeClick: !1,
                closeBtn: !1,
                scrolling: "no"
            })
        }
    }),
    s.PreviewView = t.ItemView.extend({
        initialize: function() {
            this.componentInstance = this.getOption("componentInstance"),
            this.ui = _.extend(this.previewUi, this.ui),
            this.events = _.extend(this.previewEvent, this.events)
        },
        tagName: "div",
        className: "component-wrapper clearfix",
        modelEvents: {
            change: "render"
        },
        previewUi: {
            config: ".grid-drag-handle .edit",
            "delete": ".grid-drag-handle .delete",
            copy: ".grid-drag-handle .copy"
        },
        previewEvent: {
            "click @ui.config": "showConfig",
            "click @ui.delete": "deleteComponent",
            "click @ui.copy": "copyComponent"
        },
        onRender: function() {
            "function" == typeof this._onRender && this._onRender()
        },
        onDestroy: function() {
            n.pageElementList.remove(this.model),
            this.$el.parent().remove()
        },
        showConfig: function() {
            i.events.trigger("component:config:show", this.componentInstance.getConfigView())
        },
        copyComponent: function() {
            var t = this.$el.parent().clone();
            this.$el.parent().after(t),
            i.reqres.request("component:instance", this.model.get("componentType"), t, this.model.attributes)
        },
        deleteComponent: function() {
            var t = this;
            this.$el.slideUp(300,
            function() {
                t.destroy()
            }),
            $(".component-config-wrapper." + t.model.cid).slideUp(300)
        }
    }),
    s.Controller = t.Controller.extend({
        initialize: function(t) {
            this.cid = _.uniqueId("component"),
            this.model = t.model,
            this.configViewOptions = t.configViewOptions,
            this.previewViewOptions = t.previewViewOptions
        },
        getConfigView: function() {
            var e = this.configView;
            if (!e) throw new t.Error({
                name: "NoConfigViewError",
                message: 'A "configView" must be specified'
            });
            return new e(_.extend({
                model: this.model
            },
            this.configViewOptions))
        },
        getPreviewView: function() {
            var e = this.previewView;
            if (!e) throw new t.Error({
                name: "NoPreviewViewError",
                message: 'A "previewView" must be specified'
            });
            return new e(_.extend({
                model: this.model,
                componentInstance: this
            },
            this.previewViewOptions))
        }
    }),
    s
}),
define("text!components/exttypeset/templates/config.html", [],
function() {
    return '<div id=\'component-banner-form\'>\n    <form>\n        <div class="section">扩展排版</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">栏目标题</div>\n            <div class="field">\n                无：<input <% if(hasTitle == \'no\') print(\'checked="checked"\') %> data-role=\'hasTitle\' type=\'radio\'\n                name=\'has_title\' class=\'with-role field-has-title\' value=\'no\'/>\n                有：<input <% if(hasTitle == \'yes\') print(\'checked="checked"\') %> data-role=\'hasTitle\' type=\'radio\'\n                name=\'has_title\' class=\'with-role field-has-title\' value=\'yes\'/>\n                <div class="section-title-info clearfix">\n                    <input data-role="componentName" type="text" class="field-title with-role" placeholder="栏目标题"\n                           value="<%= componentName %>"/>\n                    <br/><br/>标题样式<br/>\n\n                    <div class="select-wrapper <% if(titleTheme == \'title-theme-1\') print(\'active\') %>"\n                         data-role="titleTheme"\n                         data-val="title-theme-1">\n                        <img src="/static/editPage/images/exttypeset/images/title_theme_1.png">\n\n                        <div class="selected-icon"></div>\n                    </div>\n                    <div class="select-wrapper <% if(titleTheme == \'title-theme-2\') print(\'active\') %>"\n                         data-role="titleTheme"\n                         data-val="title-theme-2">\n                        <img src="/static/editPage/images/exttypeset/images/title_theme_2.png">\n\n                        <div class="selected-icon"></div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top\' value=\'yes\'/>\n            </div>\n        </div>\n   <div class="section">橱窗结构(超出图标数，则不显示)</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="title-theme">\n                <div class="select-wrapper <% if(templateId == \'tpl_1\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_1">\n                    <img src="/static/editPage/images/exttypeset/images/tpl_1.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n                <div class="select-wrapper <% if(templateId == \'tpl_2\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_2">\n                    <img src="/static/editPage/images/exttypeset/images/tpl_2.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n                <div class="select-wrapper <% if(templateId == \'tpl_3\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_3">\n                    <img src="/static/editPage/images/exttypeset/images/tpl_3.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n                <div class="select-wrapper <% if(templateId == \'tpl_4\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_4">\n                    <img src="/static/editPage/images/exttypeset/images/tpl_4.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n <div class="select-wrapper <% if(templateId == \'tpl_5\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_5">\n                    <img src="/static/editPage/images/exttypeset/images/tpl_5.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n <div class="select-wrapper <% if(templateId == \'tpl_6\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_6">\n                    <img src="/static/editPage/images/exttypeset/images/tpl_6.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n <div class="select-wrapper <% if(templateId == \'tpl_7\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_7">\n                    <img src="/static/editPage/images/exttypeset/images/tpl_7.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n</form><div id="imgsizeinfobox" style="clear:both;"><%= imgsize %></div>  <div class="clearfix"></div>  <div class="section ">排版结构 <a class="add-exttypeset float-right" href="javascript:void(0)">+添加图片</a>\n</div>\n        <div id="exttypesets-list" class="exttypeset-list clearfix">\n        <form>\n            <ul class="list-unstyled">\n                <% for (i in data){ %>\n                <li class="exttypeset clearfix" title="可拖动进行排序" >\n    <div class="delete-item"><img src="/static/editPage/images/delete_item.png"/></div>                              <div class="exttypeset-img image-upload" data-name="exttypeset">\n                        <img src="<%= data[i].src %>" />\n                    </div>\n                          <div class="exttypeset-link">\n                        <div class="input-group">\n                            <input type="text" class="form-control" name="exttypeset_link" value="<%= data[i].link %>"\n                                   placeholder="链接:http://www.example.com"/>\n                            <span class="input-group-addon select_url">浏览</span>\n                        </div>\n\n                    </div>\n                </li>\n                <% } %>\n            </ul>\n        </form>\n    </div>\n</div>'
}),
define("text!components/exttypeset/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div\n        class=" component-exttypeset clearfix <%= themeId %> <%= templateId %><% if (hasMarginTop ==\'yes\') print(\' margin-top\')%>">\n    <% if (hasTitle == \'yes\') { %>\n    <div class="component-title <%= titleTheme %>"><span><%= componentName %></span></div>\n    <% } %>\n    <div id="activity" class="component-data clearfix <% if (hasTitle == \'no\') { %>no-title<% } %>">\n\n    </div>\n</div>\n\n'
}),
define("text!components/exttypeset/templates/tpl_1.html", [],
function() {
     return '<div class="exttypeset clearfix exttypeset_<%= key %>">\n  <span class="ext_key"><%= key %></span> <a href="<%= link %>">  <img class="prod-img" src="<%= src %>"/>\n   </a>\n  </div>\n'

}),
define("text!components/exttypeset/templates/tpl_5.html", [],
function() {
     return '<div class="exttypeset clearfix exttypeset_<%= key %>">\n  <span class="ext_key"><%= key %></span> <a href="<%= link %>"> <img class="prod-img" src="<%= src %>"/>\n   </a>\n  </div>\n'

}),

define("components/exttypeset/assets", ["require", "text!components/exttypeset/templates/config.html", "text!components/exttypeset/templates/preview.html", "text!components/exttypeset/templates/tpl_1.html", "text!components/exttypeset/templates/tpl_5.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/exttypeset/templates/config.html"),
            preview: t("text!components/exttypeset/templates/preview.html"),
            tpl_1: t("text!components/exttypeset/templates/tpl_1.html"),
			tpl_5: t("text!components/exttypeset/templates/tpl_5.html")
        }
    }
}),
define("components/exttypeset/main", ["components/base", "components/exttypeset/assets"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = McMore.componentDefault.exttypeset,
    i.tpls = {
        tpl_1: n.tpl_1,
		tpl_2: n.tpl_1,
		tpl_3: n.tpl_1,
		tpl_4: n.tpl_1,
		tpl_5: n.tpl_5,
		tpl_6: n.tpl_1,
		tpl_7: n.tpl_1
    },  
	i.imgsize = {
        tpl_1: "图1：120px*145px;<br>图2：195px*70px;<br>图3：95px*70px;<br>图4：95px*70px;",
		tpl_2: "图1：120px*145px;<br>图2：195px*70px;<br>图3：195px*70px;",
		tpl_3: "图1：120px*145px;<br>图2：95px*70px;<br>图3：95px*70px;<br>图4：95px*70px;<br>图5：95px*70px;",
		tpl_4: "全图：157px*70px;",
		tpl_5: "全图：76px*140px;",
		tpl_6: "图1：195px*70px;<br>图2：120px*145px;<br>图3：95px*70px;<br>图4：95px*70px;",
		tpl_7: "图1：195px*70px;<br>图2：120px*145px;<br>图3：195px*70px;",
    },  
	i.PreviewView = t.PreviewView.extend({
        template: function(t) {
            var e = $(_.template(n.preview, t));
			var _key = 1;
            return _(t.data).each(function(n, s) {
					n.key = _key++;	
                    e.find(".component-data").append(_.template(i.tpls[t.templateId], n)) 
					$('#imgsizeinfobox').html(i.imgsize[t.templateId]);
            }),
            e
        }
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
			t['imgsize'] = i.imgsize[t.templateId];
            var e = $(_.template(n.config, t));
            return e
        },
       ui: {
            delete_item: ".delete-item",
            add_item: ".add-exttypeset",
            data_change: "#exttypesets-list li input",
            select_url: ".select_url"
        },
        events: {
            "click @ui.delete_item": "deleteItem",
            "click @ui.add_item": "addItem",
            "change @ui.data_change": "updateExttypesetData",
            "click @ui.select_url": "selectUrl"
        },
        _onRender: function() {			
            var t = this;
			    this.$el.find("#exttypesets-list ul").sortable({
                axis: "y",
                placeholder: "ui-state-highlight",
                forcePlaceholderSize: !0,
                update: function() {
                    t.updateExttypesetData()
                }
            }),
            McMore.uploadImageCallback = function(e) {
                $(".image-upload.selected img").attr("src", e.url),
                t.updateExttypesetData()
            }
        },
		deleteItem: function(t) {
            var e = $(t.currentTarget),
            i = this;
            e.closest("li").slideUp(300,
            function() {
                $(this).remove(),
                i.updateExttypesetData()
            })
        },
        addItem: function() {
            $('<li class="exttypeset clearfix"><div class="delete-item"><img src="/static/editPage/images/delete_item.png"/></div><div class="exttypeset-img image-upload" data-name="exttypeset"><img src="/static/editPage/images/exttypeset/images/d.png"/></div><div class="exttypeset-link"><div class="input-group"><input class="form-control" type="text" placeholder="链接:http://www.example.com" value="" name="exttypeset_link"><span class="input-group-addon select_url">浏览</span></div></div></li>').appendTo($("#exttypesets-list ul")),
            this.updateExttypesetData()
        },
        updateExttypesetData: function() {
            var t = [];
            this.$el.find("#exttypesets-list ul li").each(function() {
                var e = {
                    name: $(this).find(".exttypeset-name input").val(),
					nameb:$(this).find(".exttypeset-nameb input").val(),
                    src: $(this).find(".exttypeset-img img").attr("src"),
                    link: $(this).find(".exttypeset-link input").val()
                };
                t.push(e)
            }),
            this.model.set("data", t)
        },
        selectUrl: function(t) {
            var e = $(t.currentTarget),
            i = this;
            McMore.selectUrlCallback = function(t) {
                var n = e.closest(".input-group").find("input");
                n.val(t),
                i.updateExttypesetData(),
                $.fancybox.close()
            },
            $.fancybox.open({
                href: alinks,
                type: "iframe",
                fitToView: !1,
                width: 850,
                padding: 0,
                height: "70%",
                autoSize: !1,
                closeClick: !1,
                closeBtn: !1
            })
        }
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),


define("text!components/extmenu/templates/config.html", [],
function() {
    return '<div id=\'component-banner-form\'>\n    <form>\n        <div class="section">扩展排版</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">栏目标题</div>\n            <div class="field">\n                无：<input <% if(hasTitle == \'no\') print(\'checked="checked"\') %> data-role=\'hasTitle\' type=\'radio\'\n                name=\'has_title\' class=\'with-role field-has-title\' value=\'no\'/>\n                有：<input <% if(hasTitle == \'yes\') print(\'checked="checked"\') %> data-role=\'hasTitle\' type=\'radio\'\n                name=\'has_title\' class=\'with-role field-has-title\' value=\'yes\'/>\n                <div class="section-title-info clearfix">\n                    <input data-role="componentName" type="text" class="field-title with-role" placeholder="栏目标题"\n                           value="<%= componentName %>"/>\n                    <br/><br/>标题样式<br/>\n\n                    <div class="select-wrapper <% if(titleTheme == \'title-theme-1\') print(\'active\') %>"\n                         data-role="titleTheme"\n                         data-val="title-theme-1">\n                        <img src="/static/editPage/images/extmenu/images/title_theme_1.png">\n\n                        <div class="selected-icon"></div>\n                    </div>\n                    <div class="select-wrapper <% if(titleTheme == \'title-theme-2\') print(\'active\') %>"\n                         data-role="titleTheme"\n                         data-val="title-theme-2">\n                        <img src="/static/editPage/images/extmenu/images/title_theme_2.png">\n\n                        <div class="selected-icon"></div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top\' value=\'yes\'/>\n            </div>\n        </div>\n   <div class="section">橱窗结构</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="title-theme">\n                <div class="select-wrapper <% if(templateId == \'tpl_1\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_1">\n                    <img src="/static/editPage/images/extmenu/images/tpl_1.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n                <div class="select-wrapper <% if(templateId == \'tpl_2\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_2">\n                    <img src="/static/editPage/images/extmenu/images/tpl_2.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n               </form> <div class="clearfix"></div>  <div class="section ">排版结构 <a class="add-extmenu float-right" href="javascript:void(0)">+添加图片</a>\n</div>\n        <div id="extmenus-list" class="extmenu-list clearfix">\n        <form>\n      <div>图片尺寸：100px*100px;</div>      <ul class="list-unstyled">\n                <% for (i in data){ %>\n                <li class="extmenu clearfix" title="可拖动进行排序" >\n    <div class="delete-item"><img src="/static/editPage/images/delete_item.png"/></div>                              <div class="extmenu-img image-upload" data-name="extmenu">\n                        <img src="<%= data[i].src %>" />\n                    </div>\n                    <div class="extmenu-name">\n                        <input type="text" class="form-control" name="extmenu_name" value="<%= data[i].name %>" placeholder="主标题"/>\n                    </div>\n                  <div class="extmenu-link">\n                        <div class="input-group">\n                            <input type="text" class="form-control" name="extmenu_link" value="<%= data[i].link %>"\n                                   placeholder="链接:http://www.example.com"/>\n                            <span class="input-group-addon select_url">浏览</span>\n                        </div>\n\n                    </div>\n                </li>\n                <% } %>\n            </ul>\n        </form>\n    </div>\n</div>'
}),
define("text!components/extmenu/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div\n        class=" component-extmenu clearfix <%= themeId %> <%= templateId %><% if (hasMarginTop ==\'yes\') print(\' margin-top\')%>">\n    <% if (hasTitle == \'yes\') { %>\n    <div class="component-title <%= titleTheme %>"><span><%= componentName %></span></div>\n    <% } %>\n    <div id="activity" class="component-data clearfix <% if (hasTitle == \'no\') { %>no-title<% } %>">\n\n    </div>\n</div>\n\n'
}),
define("text!components/extmenu/templates/tpl_1.html", [],
function() {
     return '<div class="extmenu clearfix tpl_1" style="width:<%= width %>">\n <a href="<%= link %>"><div class="meun-img"><img src="<%= src %>"/>\n</div><% if (name != \'\') { %><div class="meun-name"><span><%= name %></span></div> <% } %></a>\n  </div>\n'

}),
define("components/extmenu/assets", ["require", "text!components/extmenu/templates/config.html", "text!components/extmenu/templates/preview.html", "text!components/extmenu/templates/tpl_1.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/extmenu/templates/config.html"),
            preview: t("text!components/extmenu/templates/preview.html"),
            tpl_1: t("text!components/extmenu/templates/tpl_1.html")
        }
    }
}),
define("components/extmenu/main", ["components/base", "components/extmenu/assets"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = McMore.componentDefault.extmenu,
    i.tpls = {
        tpl_1: n.tpl_1,
		tpl_2: n.tpl_1,
		
    },
	i.PreviewView = t.PreviewView.extend({
        template: function(t) {

  var e = $(_.template(n.preview, t)),
            s = [],
            o = [];
            _(t.data).each(function(t) {
                o.push(t)
            });
            var r = 1 / _.size(o);
            return r = 100 * r.toFixed(2) + "%",
            _(o).each(function(e) {
                (e.width = r, s.push(_.template(i.tpls[t.templateId], e)))
            }),
            e.find(".component-data").html(s.join("")),
            e
        }
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
            var e = $(_.template(n.config, t));
            return e
        },
       ui: {
            delete_item: ".delete-item",
            add_item: ".add-extmenu",
            data_change: "#extmenus-list li input",
            select_url: ".select_url"
        },
        events: {
            "click @ui.delete_item": "deleteItem",
            "click @ui.add_item": "addItem",
            "change @ui.data_change": "updateextmenuData",
            "click @ui.select_url": "selectUrl"
        },
        _onRender: function() {			
            var t = this;
			    this.$el.find("#extmenus-list ul").sortable({
                axis: "y",
                placeholder: "ui-state-highlight",
                forcePlaceholderSize: !0,
                update: function() {
                    t.updateextmenuData()
                }
            }),
            McMore.uploadImageCallback = function(e) {
                $(".image-upload.selected img").attr("src", e.url),
                t.updateextmenuData()
            }
        },
		deleteItem: function(t) {
            var e = $(t.currentTarget),
            i = this;
            e.closest("li").slideUp(300,
            function() {
                $(this).remove(),
                i.updateextmenuData()
            })
        },
        addItem: function() {
            $('<li class="extmenu clearfix"><div class="delete-item"><img src="/static/editPage/images/delete_item.png"/></div><div class="extmenu-img image-upload" data-name="extmenu"><img src="/static/editPage/images/extmenu/images/d.png"/></div><div class="extmenu-name"><input type="text" name="extmenu_name" placeholder="主标题" class="form-control" value=""/></div><div class="extmenu-link"><div class="input-group"><input class="form-control" type="text" placeholder="链接:http://www.extmenu.com" value="" name="extmenu_link"><span class="input-group-addon select_url">浏览</span></div></div></li>').appendTo($("#extmenus-list ul")),
            this.updateextmenuData()
        },
        updateextmenuData: function() {
            var t = [];
            this.$el.find("#extmenus-list ul li").each(function() {
                var e = {
                    name: $(this).find(".extmenu-name input").val(),
					nameb:$(this).find(".extmenu-nameb input").val(),
                    src: $(this).find(".extmenu-img img").attr("src"),
                    link: $(this).find(".extmenu-link input").val()
                };
                t.push(e)
            }),
            this.model.set("data", t)
        },
        selectUrl: function(t) {
            var e = $(t.currentTarget),
            i = this;
            McMore.selectUrlCallback = function(t) {
                var n = e.closest(".input-group").find("input");
                n.val(t),
                i.updateextmenuData(),
                $.fancybox.close()
            },
            $.fancybox.open({
                href: alinks,
                type: "iframe",
                fitToView: !1,
                width: 850,
                padding: 0,
                height: "70%",
                autoSize: !1,
                closeClick: !1,
                closeBtn: !1
            })
        }
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),



define("text!components/ads/templates/config.html", [],
function() {
    return '<div id=\'component-banner-form\'>\n    <form>\n        <div class="section">广告图片</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\' name=\'has_margin_top\' class=\'field-margin-top\' value=\'yes\'/>\n            </div>\n        </div>\n    </form>\n    <div class="section">\n        广告图片属性\n    </div>\n    <div id="banner-list" class="clearfix">\n        <form>\n            <ul class="list-unstyled">\n                <li class="clearfix">\n                    <div class="banner-img image-upload" data-name="ads">\n                        <img src="<%= data.src %>" title="图片尺寸建议：640x280"/>\n                    </div>\n                    <div class="slide-name">\n                        <input type="text" class="form-control" name="slide_name" value="<%= data.name %>" placeholder="图片alt属性"/>\n                    </div>\n                    <div class="slide-link">\n                        <div class="input-group">\n                            <input type="text" class="form-control" name="slide_link" value="<%= data.link %>" placeholder="图片链接"/>\n                            <span class="input-group-addon select_url">浏览</span>\n                        </div>\n                    </div>\n                </li>\n            </ul>\n        </form>\n    </div>\n</div>'
}),
define("text!components/ads/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div class=" component-ads clearfix <%= themeId %> <%= templateId %><% if (hasMarginTop ==\'yes\') print(\' margin-top\')%>">\n    <div id="banner" class="component-data clearfix">\n\n    </div>\n</div>\n\n'
}),
define("text!components/ads/templates/tpl_1.html", [],
function() {
    return '<div class="banner-img"><a href="<%= link %>"><img src="<%= src %>" alt="<%= name %>"/></a></div>\n\n'
}),
define("components/ads/assets", ["require", "text!components/ads/templates/config.html", "text!components/ads/templates/preview.html", "text!components/ads/templates/tpl_1.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/ads/templates/config.html"),
            preview: t("text!components/ads/templates/preview.html"),
            tpl_1: t("text!components/ads/templates/tpl_1.html")
        }
    }
}),
define("components/ads/main", ["components/base", "components/ads/assets"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = McMore.componentDefault.ads,
    i.tpls = {
        tpl_1: n.tpl_1
    },
    i.PreviewView = t.PreviewView.extend({
        template: function(t) {
            var e = $(_.template(n.preview, t));
            return e.find(".component-data").html(_.template(i.tpls[t.templateId], t.data)),
            e
        }
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
            var e = $(_.template(n.config, t));
            return e
        },
        ui: {
            data_change: "#banner-list li input",
            select_url: ".select_url"
        },
        events: {
            "change @ui.data_change": "updateBannerData",
            "click @ui.select_url": "selectUrl"
        },
        _onRender: function() {
            var t = this;
            McMore.uploadImageCallback = function(e) {
                $(".image-upload.selected img").attr("src", e.url),
                t.updateBannerData()
            }
        },
        updateBannerData: function() {
            var t = {
                name: $("#banner-list .slide-name input").val(),
                src: $("#banner-list .banner-img img").attr("src"),
                link: $("#banner-list .slide-link input").val()
            };
            this.model.set("data", t)
        },
        selectUrl: function(t) {
            var e = $(t.currentTarget),
            i = this;
            McMore.selectUrlCallback = function(t) {
                var n = e.closest(".input-group").find("input");
                n.val(t),
                i.updateBannerData(),
                $.fancybox.close()
            },
            $.fancybox.open({
                href: alinks,
                type: "iframe",
                fitToView: !1,
                width: 850,
                padding: 0,
                height: "70%",
                autoSize: !1,
                closeClick: !1,
                closeBtn: !1
            })
        }
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),
define("text!components/contact/templates/config.html", [],
function() {
    return '<div id=\'component-banner-form\'>\n    <form>\n        <div class="section">联系我们</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\' name=\'has_margin_top\' class=\'field-margin-top\' value=\'yes\'/>\n            </div>\n        </div>\n\n        <div class="section">电话信息</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">电话号码</div>\n            <div class="field">\n                <input type="text" class="with-role" data-role="phone" name="phone" value="<%= phone %>"/>\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">标签</div>\n            <div class="field">\n                <input type="text" name="label" data-role="label" class="with-role" value="<%= label %>"/>\n            </div>\n        </div>\n\n        <div class="section">结构</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="title-theme">\n                <% for (i = 1; i<=2; i++) { %>\n                <div class="select-wrapper <% if(templateId == \'tpl_\'+i) print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_<%= i %>">\n                    <div class="tpl-image tpl_<%= i %>"></div>\n                    <div class="selected-icon"></div>\n                </div>\n                <% } %>\n            </div>\n        </div>\n    </form>\n</div>'
}),
define("text!components/contact/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div class="component-contact clearfix <%= themeId %> <%= templateId %><% if (hasMarginTop ==\'yes\') print(\' margin-top\')%>">\n    <div class="component-data clearfix">\n\n    </div>\n</div>\n\n'
}),
define("text!components/contact/templates/tpl_1.html", [],
function() {
    return '<div class="contact-button-wrapper">\n    <a class="contact-button" href="tel:<%= phone %>">\n        <span><%= label %></span>\n    </a>\n</div>'
}),
define("text!components/contact/templates/tpl_2.html", [],
function() {
    return '<div class="contact-wrapper">\n    <div class="contact-left">\n        <span class="phone"><%= phone %></span>\n        <span class="desc">24小时咨询热线</span>\n    </div>\n    <div class="contact-right">\n        <a class="phone-image" href="tel:<%= phone %>"></a>\n    </div>\n</div>'
}),
define("components/contact/assets", ["require", "text!components/contact/templates/config.html", "text!components/contact/templates/preview.html", "text!components/contact/templates/tpl_1.html", "text!components/contact/templates/tpl_2.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/contact/templates/config.html"),
            preview: t("text!components/contact/templates/preview.html"),
            tpl_1: t("text!components/contact/templates/tpl_1.html"),
            tpl_2: t("text!components/contact/templates/tpl_2.html")
        }
    }
}),
define("components/contact/main", ["components/base", "components/contact/assets"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = McMore.componentDefault.contact,
    i.tpls = {
        tpl_1: n.tpl_1,
        tpl_2: n.tpl_2
    },
    i.PreviewView = t.PreviewView.extend({
        template: function(t) {
            var e = $(_.template(n.preview, t));
            return e.find(".component-data").html(_.template(i.tpls[t.templateId], t)),
            e
        }
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
            var e = $(_.template(n.config, t));
            return e
        },
        ui: {
            data_change: "#banner-list li input"
        },
        events: {
            "change @ui.data_change": "updateBannerData"
        },
        _onRender: function() {
            var t = this;
            McMore.uploadImageCallback = function(e) {
                $(".image-upload.selected img").attr("src", e.url),
                t.updateBannerData()
            }
        },
        updateBannerData: function() {
            var t = {
                name: $("#banner-list .slide-name input").val(),
                src: $("#banner-list .banner-img img").attr("src"),
                link: $("#banner-list .slide-link input").val()
            };
            this.model.set("data", t)
        }
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),
define("text!components/mainmenu/templates/config.html", [],
function() {
    return '<div id=\'component-mainmenu-form\'>\n    <form>\n        <div class="section">导航信息</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <ul class="sortable-field list-unstyled">\n                <% for (i in data) { %>\n                <li data-id="<%= data[i].id %>" title="可拖动进行排序">\n                    <input disabled maxlength="6" class="name-field" type="text" name="name"\n                           value="<%= data[i].name %>"/>\n\n                    <div class="toggle-visible <% if(data[i].visible == 1) print(\'visible\') %>"><% if(data[i].visible ==\n                        1) print(\'显示中\'); else print(\'已隐藏\') %>\n                    </div>\n                    <input class="visible-field" type="hidden" name="visible" value="<%= data[i].visible %>"/>\n                    <input class="active-field" type="hidden" name="active" value="<%= data[i].active %>"/>\n                </li>\n                <% } %>\n            </ul>\n        </div>\n\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top with-role\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\' name=\'has_margin_top\' class=\'field-margin-top with-role\' value=\'yes\'/>\n            </div>\n        </div>\n        <div class="section">导航样式</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="title-theme">\n                <% for (i = 1; i<=4; i++) { %>\n                <div class="select-wrapper <% if(themeId == \'theme-\'+i) print(\'active\') %>" data-role="themeId"\n                     data-val="theme-<%= i %>">\n                    <div class="theme-image theme-<%= i %>"></div>\n                    <div class="selected-icon"></div>\n                </div>\n                <% } %>\n            </div>\n        </div>\n    </form>\n\n</div>'
}),
define("text!components/mainmenu/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div class="component-mainmenu clearfix <%= themeId %> <%= templateId %><% if (hasMarginTop ==\'yes\') print(\' margin-top\')%>">\n    <% if (hasTitle == \'yes\') { %>\n    <div class="clearfix component-title <%= titleTheme %>"><%= componentName %></div>\n    <% } %>\n    <div class="component-data clearfix">\n        <div class="iscroll-wrapper">\n            <div class="scroll-content">\n            </div>\n        </div>\n    </div>\n</div>\n\n'
}),
define("text!components/mainmenu/templates/tpl_1.html", [],
function() {
    return '<div class="nav-item clearfix">\n    <a href="#" <% if(active == 1) print(\'class="active"\') %>>\n    <span class="nav-item-name"><span><%= name %></span></span>\n    </a>\n</div>\n'
}),
define("components/mainmenu/assets", ["require", "text!components/mainmenu/templates/config.html", "text!components/mainmenu/templates/preview.html", "text!components/mainmenu/templates/tpl_1.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/mainmenu/templates/config.html"),
            preview: t("text!components/mainmenu/templates/preview.html"),
            tpl_1: t("text!components/mainmenu/templates/tpl_1.html")
        }
    }
}),
define("components/mainmenu/main", ["components/base", "components/mainmenu/assets"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = McMore.componentDefault.mainmenu,
    i.tpls = {
        tpl_1: n.tpl_1
    },
    i.PreviewView = t.PreviewView.extend({
        template: function(t) {
            var e = $(_.template(n.preview, t)),
            s = [],
            o = [];
            return _(t.data).each(function(t) {
                1 == t.visible && o.push(t)
            }),
            _(o).each(function(e) {
                1 == e.visible && s.push(_.template(i.tpls[t.templateId], e))
            }),
            e.find(".component-data .scroll-content").html(s.join("")),
            e
        },
        _onRender: function() {}
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
            var e = $(_.template(n.config, t));
            return e
        },
        ui: {
            toggle_visible: ".toggle-visible"
        },
        events: {
            "click @ui.toggle_visible": "toggleVisible"
        },
        _onRender: function() {
            var t = this;
            this.$el.find(".sortable-field").sortable({
                axis: "y",
                placeholder: "ui-state-highlight",
                forcePlaceholderSize: !0,
                update: function() {
                    t.updateData()
                }
            })
        },
        updateData: function() {
            var t = [];
            this.$el.find(".sortable-field li").each(function() {
                var e = {
                    id: $(this).data("id"),
                    name: $(this).find(".name-field").val(),
                    visible: parseInt($(this).find(".visible-field").val()),
                    active: $(this).find(".active-field").val()
                };
                t.push(e)
            }),
            this.model.set("data", t)
        },
        toggleVisible: function(t) {
            var e = $(t.currentTarget),
            i = e.closest("li");
            e.hasClass("visible") ? (i.find(".visible-field").val(0), e.removeClass("visible").text("已隐藏")) : (i.find(".visible-field").val(1), e.addClass("visible").text("显示中")),
            this.updateData()
        }
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),
define("text!components/navigator/templates/config.html", [],
function() {
    return '<div id=\'component-navigator-form\'>\n    <form>\n        <div class="section">导航信息</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <ul class="sortable-field list-unstyled">\n                <% for (i in data) { %>\n                <li title="可拖动进行排序">\n                    <input disabled maxlength="6" class="name-field" type="text" name="name"\n                           value="<%= data[i].name %>"/>\n\n                    <div class="toggle-visible <% if(data[i].visible == 1) print(\'visible\') %>"><% if(data[i].visible ==\n                        1) print(\'显示中\'); else print(\'已隐藏\') %>\n                    </div>\n                    <input class="visible-field" type="hidden" name="visible" value="<%= data[i].visible %>"/>\n                    <input class="link-field" type="hidden" name="link" value="<%= data[i].link %>"/>\n                    <input class="code-field" type="hidden" name="code" value="<%= data[i].code %>"/>\n                </li>\n                <% } %>\n            </ul>\n        </div>\n\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\' name=\'has_margin_top\' class=\'field-margin-top\' value=\'yes\'/>\n            </div>\n        </div>\n        <div class="section">导航样式</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="title-theme">\n                <% for (i = 1; i<=4; i++) { %>\n                <div class="select-wrapper <% if(themeId == \'theme-\'+i) print(\'active\') %>" data-role="themeId"\n                     data-val="theme-<%= i %>">\n                    <div class="theme-image theme-<%= i %>"></div>\n                    <div class="selected-icon"></div>\n                </div>\n                <% } %>\n            </div>\n        </div>\n    </form>\n</div>'
}),
define("text!components/navigator/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div class="component-navigator clearfix <%= themeId %> <%= templateId %><% if (hasMarginTop ==\'yes\') print(\' margin-top\')%>">\n    <div class="component-data clearfix">\n    </div>\n</div>\n'
}),
define("text!components/navigator/templates/tpl_1.html", [],
function() {
    return '<div class="nav-item clearfix" style="width:<%= width %>">\n    <a href="#">\n        <div class="nav-item-img <%= code %>"></div>\n        <div class="nav-item-name"><span><%= name %></span></div>\n    </a>\n</div>\n'
}),
define("text!components/navigator/templates/tpl_2.html", [],
function() {
    return '<div class="product clearfix">\n    <img class="prod-img" src="<%= image.url %>"/>\n\n    <div class="prod-name"><%= name %></div>\n    <div class="prod-parprice">￥<%= par_price %></div>\n    <div class="prod-saleprice">￥<%= sale_price %></div>\n</div>\n'
}),
define("components/navigator/assets", ["require", "text!components/navigator/templates/config.html", "text!components/navigator/templates/preview.html", "text!components/navigator/templates/tpl_1.html", "text!components/navigator/templates/tpl_2.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/navigator/templates/config.html"),
            preview: t("text!components/navigator/templates/preview.html"),
            tpl_1: t("text!components/navigator/templates/tpl_1.html"),
            tpl_2: t("text!components/navigator/templates/tpl_2.html")
        }
    }
}),
define("components/navigator/main", ["components/base", "components/navigator/assets"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = McMore.componentDefault.navigator,
    i.tpls = {
        tpl_1: n.tpl_1,
        tpl_2: n.tpl_2
    },
    i.PreviewView = t.PreviewView.extend({
        template: function(t) {
            var e = $(_.template(n.preview, t)),
            s = [],
            o = [];
            _(t.data).each(function(t) {
                1 == t.visible && o.push(t)
            });
            var r = 1 / _.size(o);
            return r = 100 * r.toFixed(2) + "%",
            _(o).each(function(e) {
                1 == e.visible && (e.width = r, s.push(_.template(i.tpls[t.templateId], e)))
            }),
            e.find(".component-data").html(s.join("")),
            e
        },
        _onRender: function() {
            this.$el.find("#slideshow .slide").length < 2 || this.$el.find("#slideshow").owlCarousel({
                items: 1,
                loop: !0,
                autoplay: !0,
                rtl: 0 == this.model.get("rtl") ? !1 : !0,
                autoplayTimeout: this.model.get("autoplayTimeout")
            })
        }
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
            var e = $(_.template(n.config, t));
            return e
        },
        ui: {
            toggle_visible: ".toggle-visible"
        },
        events: {
            "click @ui.toggle_visible": "toggleVisible"
        },
        _onRender: function() {
            var t = this;
            this.$el.find(".sortable-field").sortable({
                axis: "y",
                placeholder: "ui-state-highlight",
                forcePlaceholderSize: !0,
                update: function() {
                    t.updateData()
                }
            })
        },
        updateData: function() {
            var t = [];
            this.$el.find(".sortable-field li").each(function() {
                var e = {
                    name: $(this).find(".name-field").val(),
                    visible: parseInt($(this).find(".visible-field").val()),
                    link: $(this).find(".link-field").val(),
                    code: $(this).find(".code-field").val()
                };
                t.push(e)
            }),
            this.model.set("data", t)
        },
        toggleVisible: function(t) {
            var e = $(t.currentTarget),
            i = e.closest("li");
            e.hasClass("visible") ? (i.find(".visible-field").val(0), e.removeClass("visible").text("已隐藏")) : (i.find(".visible-field").val(1), e.addClass("visible").text("显示中")),
            this.updateData()
        }
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),
define("text!components/products/templates/config.html", [],
function() {
    return '<div id=\'component-product-form\'>\n    <form>\n        <div class="section">橱窗信息</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">栏目标题</div>\n            <div class="field">\n                无：<input <% if(hasTitle == \'no\') print(\'checked="checked"\') %> data-role=\'hasTitle\' type=\'radio\'\n                name=\'has_title\' class=\'with-role field-has-title\' value=\'no\'/>\n                有：<input <% if(hasTitle == \'yes\') print(\'checked="checked"\') %> data-role=\'hasTitle\' type=\'radio\'\n                name=\'has_title\' class=\'with-role field-has-title\' value=\'yes\'/>\n                <div class="section-title-info clearfix">\n                    <input data-role="componentName" type="text" class="field-title with-role" placeholder="栏目标题"\n                           value="<%= componentName %>"/>\n                    <br/><br/>标题样式<br/>\n\n                    <div class="select-wrapper <% if(titleTheme == \'title-theme-1\') print(\'active\') %>"\n                         data-role="titleTheme" data-val="title-theme-1">\n                        <img src="/static/editPage/images/products/images/title_theme_1.png">\n\n                        <div class="selected-icon"></div>\n                    </div>\n                    <div class="select-wrapper <% if(titleTheme == \'title-theme-2\') print(\'active\') %>"\n                         data-role="titleTheme" data-val="title-theme-2">\n                        <img src="/static/editPage/images/products/images/title_theme_2.png">\n\n                        <div class="selected-icon"></div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">边框</div>\n            <div class="field">\n                无：<input <% if(hasBorder == \'no\') print(\'checked="checked"\') %> data-role=\'hasBorder\' type=\'radio\'\n                name=\'has_border\' class=\'field-border with-role\' value=\'no\'/>\n                有：<input <% if(hasBorder == \'yes\') print(\'checked="checked"\') %> data-role=\'hasBorder\' type=\'radio\'\n                name=\'has_border\' class=\'field-border with-role\' value=\'yes\'/>\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">商品数量</div>\n            <div class="field">\n                <input data-role=\'dataLimit\' type=\'text\' name=\'product_num\' value="<%= dataLimit %>"\n                       class=\'product-num spin-button with-role\'/>\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top with-role\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\' name=\'has_margin_top\' class=\'field-margin-top with-role\' value=\'yes\'/>\n            </div>\n        </div>\n   <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">显示销量</div>\n            <div class="field">\n                隐藏：<input <% if(showSaleNum == \'no\') print(\'checked="checked"\') %> data-role=\'showSaleNum\' type=\'radio\'\n                name=\'show_sale_num\' class=\'field-margin-top with-role\' value=\'no\'/>\n                显示：<input <% if(showSaleNum == \'yes\') print(\'checked="checked"\') %> data-role=\'showSaleNum\'\n                type=\'radio\' name=\'show_sale_num\' class=\'field-margin-top with-role\' value=\'yes\'/>\n            </div>\n        </div>\n    <!---->   <!--<div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">橱窗样式</div> \n            <div class="field">\n                <select data-role="themeId">\n                    <option value="1">1</option>\n                    <option value="2">2</option>\n                    <option value="3">3</option>\n                </select>\n            </div>\n        </div>-->\n        <div class="section">橱窗结构</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="title-theme">\n                <div class="select-wrapper <% if(templateId == \'tpl_1\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_1">\n                    <img src="/static/editPage/images/products/images/product_tpl_1.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n                <div class="select-wrapper <% if(templateId == \'tpl_2\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_2">\n                    <img src="/static/editPage/images/products/images/product_tpl_2.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n                <div class="select-wrapper <% if(templateId == \'tpl_3\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_3">\n                    <img src="/static/editPage/images/products/images/product_tpl_3.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n                <div class="select-wrapper <% if(templateId == \'tpl_4\') print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_4">\n                    <img src="/static/editPage/images/products/images/product_tpl_4.png">\n\n                    <div class="selected-icon"></div>\n                </div>\n            </div>\n        </div>\n    </form>\n\n    <div class="section">\n        <div class="update-data">橱窗标签</div>\n        <div class="description">双击标签可编辑标签名</div>\n    </div>\n    <div id="products-tab">\n        <ul class="tabs clearfix list-unstyled">\n            <% for (i in data) { %>\n            <li data-tab="#tab-<%= i %>" data-index="<%= i %>"\n                class="<%= activeTab == i ? \'active\' : \'\' %> <%= data[i].visible == 0 ? \'disable\' : \'\' %> ">\n                <div class="tab-detail clearfix">\n                    <% if (i > 0) { %>\n                    <div class="action"></div>\n                    <% } %>\n                    <div class="tab-name"><%= data[i].tabName %></div>\n                </div>\n                <input class="tab-field" data-index="<%= i %>" type="text"/>\n            </li>\n            <% } %>\n        </ul>\n        <% for (i in data) { %>\n        <div id="tab-<%= i %>"\n             class="tab-content <%= activeTab == i ? \'active\' : \'hide\' %> <%= data[i].visible == 0 ? \'disable\' : \'\' %>">\n            <div class="section">\n                <span>商品列表 </span>\n<select class="goods-data-type"  data-index="<%= i %>" ><option value="custom"  <% if(data[i].goodsDataType == \'custom\')print(\'selected="selected"\') %>>自定义</option><option value="recommend" <% if(data[i].goodsDataType == \'recommend\') print(\'selected="selected"\') %>>推荐</option><option value="new" <% if(data[i].goodsDataType == \'new\') print(\'selected="selected"\') %>>新品</option><option value="hot" <% if(data[i].goodsDataType == \'hot\') print(\'selected="selected"\') %>>热卖</option></select>\n<a class="add-product <%= data[i].goodsVisible == 1 ? \'\' : \'hide\' %>" data-fancybox-type="iframe" href="javascript:void(0)">+添加商品</a>\n            </div>\n            <div class="products-list clearfix <%= data[i].goodsVisible == 1 ? \'\' : \'hide\' %>">\n                <ul class="sortable-list list-unstyled">\n                    <% var products = data[i].products; %>\n                    <% for (j in products){ %>\n                    <% if (products[j].id != null) { %>\n                    <li class="product clearfix" data-id="<%= products[j].id %>" title="可拖动进行排序">\n                        <div class="delete-item"><img src="/static/editPage/images/delete_item.png"/></div>\n                        <img class="prod-img" src="<%= products[j].thumb ? products[j].thumb.url : \'\' %>"/>\n\n                        <div class="prod-name" data-saleprice="<%=products[j].sale_price %>"\n                             data-parprice="<%=products[j].par_price %>" data-sale="<%=products[j].sale_count %>"  data-vip_price="<%=products[j].vip_price %>">\n                            <%=products[j].name%>\n                        </div>\n                        <div class="prod-price">￥<%=products[j].sale_price %></div>\n                    </li>\n                    <% } %>\n                    <% } %>\n                </ul>\n            </div>\n        </div>\n        <% } %>\n    </div>\n</div>'
}),
define("text!components/products/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div class=" component-product clearfix <%= themeId %> <%= templateId %> <% if (hasMarginTop ==\'yes\') print(\'margin-top\') %> <% if (hasBorder ==\'yes\') print(\'has-border\') %>">\n    <% if (hasTitle == \'yes\') { %>\n    <div class="component-title <%= titleTheme %>"><span><%= componentName %></span></div>\n    <% } %>\n    <div class="component-data clearfix ">\n        <% if(tabsVisible == 1) { %>\n        <div class="tab-list-menu">\n            <ul class="clearfix list-unstyled">\n                <% for(k in data) {%>\n                <% if(data[k].visible == 1) { %>\n                <li class="<%= k == activeTab ? \'active\' : \'\' %>" style="width: <%= tabsWidth %>"><%= data[k].tabName\n                    %>\n                </li>\n                <% } %>\n                <% } %>\n            </ul>\n        </div>\n        <% } %>\n    </div>\n</div>\n\n'
}),
define("text!components/products/templates/tpl_1.html", [],
function() {
	return '<div class="product clearfix">\n    <img class="prod-img" src="<%= thumb ? thumb.url : \'\' %>"/>\n    <div class="prod-name"><span><%= name %></span></div>\n    <% if (vip_price > 0) { %><div class="prod-parprice"><span>￥<%= sale_price %></span></div>\n<div class="prod-saleprice" ><span>VIP￥<%= vip_price %></span></div>\n <% }else{ %><div class="prod-parprice"><s><span>￥<%=par_price %></span></s></div><div class="prod-saleprice"><span>￥<%= sale_price %></span></div>\n<% } %>     <% if (showSaleNum ==\'yes\'){%><div class="prod-sale"><span>已售<%= sale_count %>件</span></div>\n  <% } %></div>\n'
}),
define("text!components/products/templates/tpl_2.html", [],
function() {
    return '<div class="product clearfix">\n    <img class="prod-img" src="<%= thumb ? thumb.url : \'\' %>"/>\n    <div class="prod-name"><span><%= name %></span></div>\n    <div class="prod-parprice"><span>￥<%= par_price %></span> <% if (showSaleNum ==\'yes\'){%><span class="fr">已售<%= sale_count %>件</span>\n  <% } %></div>\n    <div class="prod-saleprice"><span>￥<%= sale_price %></span><% if (vip_price > 0) { %><span class="fr">VIP￥<%= vip_price %></span>\n<% } %></div>\n</div>\n'
}),
define("text!components/products/templates/tpl_3.html", [],
function() {
    return '<div class="product clearfix">\n    <img class="prod-img" src="<%= thumb ? thumb.url : \'\' %>"/>\n\n    <div class="prod-name"><span><%= name %></span></div>\n    <% if (vip_price > 0) { %><div class="prod-saleprice"><span>VIP￥<%=  vip_price  %></span></div>\n <% } %>   <div class="prod-saleprice"><span>￥<%= sale_price  %></span></div>\n  <div class="prod-parprice"><span>￥<%=   par_price %></span></div>\n <% if (showSaleNum ==\'yes\'){%><div class="prod-sale"><span>已售<%= sale_count %>件</span></div>\n<% } %></div>\n'
}),
define("text!components/products/templates/tpl_4.html", [],
function() {
    return '<div class="product clearfix">\n    <img class="prod-img" src="<%= thumb ? thumb.url : \'\' %>"/>\n\n    <div class="prod-name"><span><%= name %></span></div>\n    <div class="prod-saleprice"><span><%= vip_price > 0 ?"VIP￥"+ vip_price : "￥"+sale_price %></span></div>\n</div>\n'
}),
define("components/products/assets", ["require", "text!components/products/templates/config.html", "text!components/products/templates/preview.html", "text!components/products/templates/tpl_1.html", "text!components/products/templates/tpl_2.html", "text!components/products/templates/tpl_3.html", "text!components/products/templates/tpl_4.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/products/templates/config.html"),
            preview: t("text!components/products/templates/preview.html"),
            tpl_1: t("text!components/products/templates/tpl_1.html"),
            tpl_2: t("text!components/products/templates/tpl_2.html"),
            tpl_3: t("text!components/products/templates/tpl_3.html"),
            tpl_4: t("text!components/products/templates/tpl_4.html")
        }
    }
}),
define("components/products/main", ["components/base", "components/products/assets"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = McMore.componentDefault.products,
    i.tpls = {
        tpl_1: n.tpl_1,
        tpl_2: n.tpl_2,
        tpl_3: n.tpl_3,
        tpl_4: n.tpl_4
    },
    i.PreviewView = t.PreviewView.extend({
        template: function(t) {
            var e = $(_.template(n.preview, t));
            return _(t.data).each(function(n, s) {
                if (1 == n.visible) {
                    var o = [],
                    r = n.products;
                    _(r).each(function(e, n) {
						e.showSaleNum = t.showSaleNum;
                        n >= t.dataLimit || o.push(_.template(i.tpls[t.templateId], e))
                    });
                    var a = "hide";
                    t.activeTab == s && (a = "");
                    var l = $('<div class="clearfix ' + a + " tab-" + s + '"></div>').html(o.join(""));
                    e.find(".component-data").append(l)
                }
            }),
            e
        }
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
            var e = $(_.template(n.config, t));
            return e
        },
        ui: {
            add_item: ".add-product",
            delete_item: ".delete-item",
            update_data: ".update-data",
            tab: ".tabs > li",
            tab_field: ".tab-field",
            tab_action: ".tab-detail .action",
			goods_data_type: ".goods-data-type",
        },
        events: {
            "click @ui.add_item": "addProducts",
            "click @ui.delete_item": "deleteItem",
            "click @ui.update_data": "updateProductsData",
            "click @ui.tab": "selectTab",
            "dblclick @ui.tab": "editTab",
            "blur @ui.tab_field": "changeTabName",
            "click @ui.tab_action": "toggleDisableTab",
			"change @ui.goods_data_type": "updateProductsDataType",
        },
        _onRender: function() {
            var t = this;
            window.productsList = this.$el.find("ul.sortable-list").sortable({
                axis: "y",
                placeholder: "ui-state-highlight",
                update: function() {
                    t.updateProductsData()
                }
            })
        },
        updateProductsData: function() {
            var t = this.model,
            e = [];
            this.$el.find(".tab-content").each(function() {
                var i = $(this).index() - 1;
                if (!$(this).hasClass("hide")) {
                    var n = [];
                    $(this).find("ul.sortable-list li").each(function() {
                        var t = {
                            id: $(this).data("id"),
                            name: $(this).find(".prod-name").text(),
                            sale_price: $(this).find(".prod-name").data("saleprice"),
                            par_price: $(this).find(".prod-name").data("parprice"),
                            sale_count: $(this).find(".prod-name").data("sale"),
							vip_price: $(this).find(".prod-name").data("vip_price"),
                            thumb: {
                                url: $(this).find(".prod-img").attr("src")
                            }
                        };
                        n.push(t)
                    }),
                    e = t.get("data"),
                    e[i].products = n,
                    t.set("data", e),
                    t.set("toggle", Math.random())
                }
            })
        },
        deleteItem: function(t) {
            var e = $(t.currentTarget),
            i = this;
            e.closest("li").slideUp(300,
            function() {
                $(this).remove(),
                i.updateProductsData()
            })
        },
        addProducts: function(t) {
            var e = ($(t.currentTarget), []);
            $(".tab-content.active .products-list li").each(function() {
                e.push($(this).data("id"))
            });
            var i = e.join(",");
            "" === i && (i = "null"),
            $.fancybox.open({
                href: _products_select + "?pIds=" + i,
                type: "iframe",
                fitToView: !0,
                width: "90%",
                height: "80%",
                autoSize: !0,
                closeClick: !1
            })
        },
        selectTab: function(t) {
            var e = $(t.currentTarget);
            $(".tabs > li").removeClass("active"),
            e.addClass("active"),
            $(".tab-content").addClass("hide").removeClass("active"),
            $(e.data("tab")).removeClass("hide").addClass("active"),
            e.hasClass("disable") || this.model.set("activeTab", e.data("index"))
        },
        editTab: function(t) {
            var e = $(t.currentTarget);
            $(".tabs > li").removeClass("editing"),
            e.addClass("editing");
            var i = $(".tab-name", e).text();
            $("input", e).val("").focus().val(i)
        },
        changeTabName: function(t) {
            var e = $(t.currentTarget),
            i = e.val(),
            n = e.data("index");
            $(".tab-name").eq(n).text(i);
            var s = this.model,
            o = s.get("data");
            return o[n].tabName = i,
            s.set("data", o),
            s.set("toggle", Math.random()),
            $(".tabs > li").removeClass("editing"),
            !1
        },
        toggleDisableTab: function(t) {
            var e = $(t.currentTarget),
            i = e.closest("li");
            i.toggleClass("disable"),
            $(i.data("tab")).toggleClass("disable");
            var n = 4 - $(".tabs > li.disable").length,
            s = (100 / n).toFixed(2) + "%",
            o = this.model,
            r = o.get("data");
            return r[i.data("index")].visible = !i.hasClass("disable"),
            o.set("data", r),
            1 == n ? (o.set("tabsVisible", 0), o.set("activeTab", 0)) : (o.set("tabsVisible", 1), o.set("tabsWidth", s)),
            !i.hasClass("disable") && i.hasClass("active") ? o.set("activeTab", i.data("index")) : o.set("activeTab", 0),
            !1
        },
		updateProductsDataType: function(t) {     
			var e = $(t.currentTarget),
            i = e.val(),
            n = e.data("index");
            var s = this.model,
            o = s.get("data");
			var tc = e.parents('.tab-content');
			if (i == 'custom'){
				o[n].goodsVisible = 1;
				tc.find('.add-product').removeClass("hide");
				tc.find('.products-list').removeClass("hide");
			}else{
				o[n].goodsVisible = 0;
				tc.find('.add-product').addClass("hide");
				tc.find('.products-list').addClass("hide");				
			}
			tc.find('add-product ')
            return o[n].goodsDataType = i, s.set("data", o),
            !1
        },
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),
define("text!components/search/templates/config.html", [],
function() {
    return '<div id=\'component-search-form\'>\n    <form>\n        <div class="section">搜索框设置</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\' name=\'has_margin_top\' class=\'field-margin-top\' value=\'yes\'/>\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">Logo</div>\n            <div class="field">\n                <div class="logo image-upload" data-name="logo">\n                    <img src="<%= logo %>"/>\n                </div>\n            </div>\n        </div>\n\n        <div class="section">选择模板</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="title-theme">\n                <% for (i = 1; i<=5; i++) { %>\n                <div class="select-wrapper <% if(templateId == \'tpl_\'+i) print(\'active\') %>" data-role="templateId"\n                     data-val="tpl_<%= i %>">\n                    <div class="tpl-image tpl_<%= i %>"></div>\n                    <div class="selected-icon"></div>\n                </div>\n                <% } %>\n            </div>\n        </div>\n\n        <div class="section">选择搜索框样式</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="title-theme">\n                <% for (i = 1; i<=3; i++) { %>\n                <div class="select-wrapper <% if(themeId == \'theme-\'+i) print(\'active\') %>" data-role="themeId"\n                     data-val="theme-<%= i %>">\n                    <div class="theme-image theme-<%= i %>"></div>\n                    <div class="selected-icon"></div>\n                </div>\n                <% } %>\n            </div>\n        </div>\n\n        <div class="section">搜索关键字提示</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <input data-role="placeholder" type="text" class="field-title with-role" placeholder="搜索关键字提示"\n                   value="<%= placeholder %>"/>\n        </div>\n    </form>\n</div>'
}),
define("text!components/search/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div class="component-search clearfix <%= themeId %> <%= templateId %><% if (hasMarginTop ==\'yes\') print(\' margin-top\')%>">\n    <div id="search" class="component-data clearfix">\n\n    </div>\n</div>\n\n'
}),
define("text!components/search/templates/tpl_1.html", [],
function() {
    return '<div class="logo">\n    <a href=""><img src="<%= logo %>"/></a>\n</div>\n<div class="search-form">\n    <form>\n        <input type="text" class="search-keyword" name="keyword" placeholder="<%= placeholder %>"/>\n        <input type="submit" class="search-submit"/>\n    </form>\n</div>\n<div class="links">\n    <ul class="list-unstyled">\n        <li><a href=#>\n            <div class="search-image"></div>\n        </a></li>\n    </ul>\n</div>\n\n'
}),
define("text!components/search/templates/tpl_2.html", [],
function() {
    return '<div class="logo">\n    <a href=""><img src="<%= logo %>"/></a>\n</div>\n<div class="search-form">\n    <form>\n        <input type="text" class="search-keyword" name="keyword" placeholder="<%= placeholder %>"/>\n        <input type="submit" class="search-submit"/>\n    </form>\n</div>'
}),
define("text!components/search/templates/tpl_3.html", [],
function() {
    return '<div class="logo">\n    <a href=""><img src="<%= logo %>"/></a>\n</div>\n<div class="links">\n    <ul class="list-unstyled">\n        <li><a href=#>\n            <div class="search-image icon_1"></div>\n        </a></li>\n        <li><a href=#>\n            <div class="search-image icon_2"></div>\n        </a></li>\n        <li><a href=#>\n            <div class="search-image icon_3"></div>\n        </a></li>\n    </ul>\n</div>\n\n'
}),
define("text!components/search/templates/tpl_4.html", [],
function() {
    return '<div class="logo">\n    <a href=""><img src="<%= logo %>"/></a>\n</div>\n<div class="links">\n    <ul class="list-unstyled">\n        <li><a href=#><span>登录</span></a></li>\n        <li><a href=#><span>注册</span></a></li>\n    </ul>\n</div>\n\n'
}),
define("text!components/search/templates/tpl_5.html", [],
function() {
    return '<div class="logo">\n    <a href=""><img src="<%= logo %>"/></a>\n</div>\n<div class="links">\n    <ul class="list-unstyled">\n        <li><a href=#>\n            <div class="search-image"></div>\n        </a></li>\n    </ul>\n</div>\n\n'
}),
define("components/search/assets", ["require", "text!components/search/templates/config.html", "text!components/search/templates/preview.html", "text!components/search/templates/tpl_1.html", "text!components/search/templates/tpl_2.html", "text!components/search/templates/tpl_3.html", "text!components/search/templates/tpl_4.html", "text!components/search/templates/tpl_5.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/search/templates/config.html"),
            preview: t("text!components/search/templates/preview.html"),
            tpl_1: t("text!components/search/templates/tpl_1.html"),
            tpl_2: t("text!components/search/templates/tpl_2.html"),
            tpl_3: t("text!components/search/templates/tpl_3.html"),
            tpl_4: t("text!components/search/templates/tpl_4.html"),
            tpl_5: t("text!components/search/templates/tpl_5.html")
        }
    }
}),
define("components/search/main", ["components/base", "components/search/assets"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = {
        componentId: "",
        componentType: "search",
        componentName: "搜索条",
        placeholder: "请输入商品名称",
        titleTheme: "title-theme-1",
        hasMarginTop: "no",
        templateId: "tpl_1",
        themeId: "theme-1",
        logo: "/static/editPage/images/search/images/default_logo.png"
    },
    i.tpls = {
        tpl_1: n.tpl_1,
        tpl_2: n.tpl_2,
        tpl_3: n.tpl_3,
        tpl_4: n.tpl_4,
        tpl_5: n.tpl_5
    },
    i.PreviewView = t.PreviewView.extend({
        template: function(t) {
            var e = $(_.template(n.preview, t));
            return e.find(".component-data").html(_.template(i.tpls[t.templateId], t)),
            e
        }
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
            var e = $(_.template(n.config, t));
            return e
        },
        ui: {},
        events: {},
        _onRender: function() {
            var t = this;
            McMore.uploadImageCallback = function(e) {
                $(".image-upload.selected img").attr("src", e.url),
                t.model.set("logo", e.url)
            }
        }
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),
define("text!components/slideshow/templates/config.html", [],
function() {
    return '<div id=\'component-slideshow-form\'>\n    <form>\n        <div class="section">轮播设置</div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">滚动方向</div>\n            <div class="field">\n                <select data-role="rtl">\n                    <option value="0"\n                    <% if(rtl == 0) print (\'selected="selected"\') %>>从右向左</option>\n                    <option value="1"\n                    <% if(rtl == 1) print (\'selected="selected"\') %>>从左向右</option>\n                </select>\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">时间间隔</div>\n            <div class="field">\n                <select data-role="autoplayTimeout">\n                    <% for(i=1;i<=10;i++){ %>\n                    <option value="<%= i*1000 %>"\n                    <% if( autoplayTimeout == i*1000) print (\'selected="selected"\') %>><%= i %></option>\n                    <% } %>\n                </select>后自动播放下一张图片\n            </div>\n        </div>\n        <div class=\'filed-wrapper clearfix\'>\n            <div class="field-label">上间距</div>\n            <div class="field">\n                无：<input <% if(hasMarginTop == \'no\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\' type=\'radio\'\n                name=\'has_margin_top\' class=\'field-margin-top with-role\' value=\'no\'/>\n                有：<input <% if(hasMarginTop == \'yes\') print(\'checked="checked"\') %> data-role=\'hasMarginTop\'\n                type=\'radio\' name=\'has_margin_top\' class=\'field-margin-top with-role\' value=\'yes\'/>\n            </div>\n        </div>\n\n    </form>\n    <div class="section">\n        幻灯片信息 <a class="add-slide float-right" href="javascript:void(0)">+添加图片</a>\n    </div>\n    <div id="slides-list" class="slide-list clearfix">\n        <form>\n            <ul class="list-unstyled">\n                <% for (i in data){ %>\n                <li class="slide clearfix" title="可拖动进行排序" \\>\n                    <div class="delete-item"><img src="/static/editPage/images/delete_item.png"/></div>\n                    <div class="slide-img image-upload" data-name="slide">\n                        <img src="<%= data[i].src %>" title="图片尺寸建议：640x280"/>\n                    </div>\n                    <div class="slide-name">\n                        <input type="text" class="form-control" name="slide_name" value="<%= data[i].name %>" placeholder="图片alt属性"/>\n                    </div>\n                    <div class="slide-link">\n                        <div class="input-group">\n                            <input type="text" class="form-control" name="slide_link" value="<%= data[i].link %>"\n                                   placeholder="链接:http://www.example.com"/>\n                            <span class="input-group-addon select_url">浏览</span>\n                        </div>\n\n                    </div>\n                </li>\n                <% } %>\n            </ul>\n        </form>\n    </div>\n</div>'
}),
define("text!components/slideshow/templates/preview.html", [],
function() {
    return '<div class="grid-drag-handle">\n    <div class="edit">编辑</div>\n    <div class="delete">删除</div>\n    <div class="copy">复制</div>\n</div>\n<div class="component-slideshow clearfix <%= themeId %> <%= templateId %><% if (hasMarginTop ==\'yes\') print(\' margin-top\')%>">\n    <div id="slideshow" class="component-data clearfix <% if (hasBorder ==\'yes\') print(\'has-border\') %>">\n\n    </div>\n</div>\n\n'
}),
define("text!components/slideshow/templates/tpl_1.html", [],
function() {
    return '<div class="slide">\n    <div class="slide-img"><a href="<%= link %>"><img src="<%= src %>" alt="<%= name %>"/></a></div>\n</div>\n'
}),
define("components/slideshow/assets", ["require", "text!components/slideshow/templates/config.html", "text!components/slideshow/templates/preview.html", "text!components/slideshow/templates/tpl_1.html"],
function(t) {
    "use strict";
    return {
        templates: {
            config: t("text!components/slideshow/templates/config.html"),
            preview: t("text!components/slideshow/templates/preview.html"),
            tpl_1: t("text!components/slideshow/templates/tpl_1.html")
        }
    }
}),
function(t, e, i, n) {
    function s(e, i) {
        this.settings = null,
        this.options = t.extend({},
        s.Defaults, i),
        this.$element = t(e),
        this.drag = t.extend({},
        d),
        this.state = t.extend({},
        p),
        this.e = t.extend({},
        f),
        this._plugins = {},
        this._supress = {},
        this._current = null,
        this._speed = null,
        this._coordinates = [],
        this._breakpoint = null,
        this._width = null,
        this._items = [],
        this._clones = [],
        this._mergers = [],
        this._invalidated = {},
        this._pipe = [],
        t.each(s.Plugins, t.proxy(function(t, e) {
            this._plugins[t[0].toLowerCase() + t.slice(1)] = new e(this)
        },
        this)),
        t.each(s.Pipe, t.proxy(function(e, i) {
            this._pipe.push({
                filter: i.filter,
                run: t.proxy(i.run, this)
            })
        },
        this)),
        this.setup(),
        this.initialize()
    }
    function o(t) {
        if (t.touches !== n) return {
            x: t.touches[0].pageX,
            y: t.touches[0].pageY
        };
        if (t.touches === n) {
            if (t.pageX !== n) return {
                x: t.pageX,
                y: t.pageY
            };
            if (t.pageX === n) return {
                x: t.clientX,
                y: t.clientY
            }
        }
    }
    function r(t) {
        var e, n, s = i.createElement("div"),
        o = t;
        for (e in o) if (n = o[e], "undefined" != typeof s.style[n]) return s = null,
        [n, e];
        return [!1]
    }
    function a() {
        return r(["transition", "WebkitTransition", "MozTransition", "OTransition"])[1]
    }
    function l() {
        return r(["transform", "WebkitTransform", "MozTransform", "OTransform", "msTransform"])[0]
    }
    function c() {
        return r(["perspective", "webkitPerspective", "MozPerspective", "OPerspective", "MsPerspective"])[0]
    }
    function h() {
        return "ontouchstart" in e || !!navigator.msMaxTouchPoints
    }
    function u() {
        return e.navigator.msPointerEnabled
    }
    var d, p, f;
    d = {
        start: 0,
        startX: 0,
        startY: 0,
        current: 0,
        currentX: 0,
        currentY: 0,
        offsetX: 0,
        offsetY: 0,
        distance: null,
        startTime: 0,
        endTime: 0,
        updatedX: 0,
        targetEl: null
    },
    p = {
        isTouch: !1,
        isScrolling: !1,
        isSwiping: !1,
        direction: !1,
        inMotion: !1
    },
    f = {
        _onDragStart: null,
        _onDragMove: null,
        _onDragEnd: null,
        _transitionEnd: null,
        _resizer: null,
        _responsiveCall: null,
        _goToLoop: null,
        _checkVisibile: null
    },
    s.Defaults = {
        items: 3,
        loop: !1,
        center: !1,
        mouseDrag: !0,
        touchDrag: !0,
        pullDrag: !0,
        freeDrag: !1,
        margin: 0,
        stagePadding: 0,
        merge: !1,
        mergeFit: !0,
        autoWidth: !1,
        startPosition: 0,
        rtl: !1,
        smartSpeed: 250,
        fluidSpeed: !1,
        dragEndSpeed: !1,
        responsive: {},
        responsiveRefreshRate: 200,
        responsiveBaseElement: e,
        responsiveClass: !1,
        fallbackEasing: "swing",
        info: !1,
        nestedItemSelector: !1,
        itemElement: "div",
        stageElement: "div",
        themeClass: "owl-theme",
        baseClass: "owl-carousel",
        itemClass: "owl-item",
        centerClass: "center",
        activeClass: "active"
    },
    s.Width = {
        Default: "default",
        Inner: "inner",
        Outer: "outer"
    },
    s.Plugins = {},
    s.Pipe = [{
        filter: ["width", "items", "settings"],
        run: function(t) {
            t.current = this._items && this._items[this.relative(this._current)]
        }
    },
    {
        filter: ["items", "settings"],
        run: function() {
            var t = this._clones,
            e = this.$stage.children(".cloned"); (e.length !== t.length || !this.settings.loop && t.length > 0) && (this.$stage.children(".cloned").remove(), this._clones = [])
        }
    },
    {
        filter: ["items", "settings"],
        run: function() {
            var t, e, i = this._clones,
            n = this._items,
            s = this.settings.loop ? i.length - Math.max(2 * this.settings.items, 4) : 0;
            for (t = 0, e = Math.abs(s / 2); e > t; t++) s > 0 ? (this.$stage.children().eq(n.length + i.length - 1).remove(), i.pop(), this.$stage.children().eq(0).remove(), i.pop()) : (i.push(i.length / 2), this.$stage.append(n[i[i.length - 1]].clone().addClass("cloned")), i.push(n.length - 1 - (i.length - 1) / 2), this.$stage.prepend(n[i[i.length - 1]].clone().addClass("cloned")))
        }
    },
    {
        filter: ["width", "items", "settings"],
        run: function() {
            var t, e, i, n = this.settings.rtl ? 1 : -1,
            s = (this.width() / this.settings.items).toFixed(3),
            o = 0;
            for (this._coordinates = [], e = 0, i = this._clones.length + this._items.length; i > e; e++) t = this._mergers[this.relative(e)],
            t = this.settings.mergeFit && Math.min(t, this.settings.items) || t,
            o += (this.settings.autoWidth ? this._items[this.relative(e)].width() + this.settings.margin: s * t) * n,
            this._coordinates.push(o)
        }
    },
    {
        filter: ["width", "items", "settings"],
        run: function() {
            var e, i, n = (this.width() / this.settings.items).toFixed(3),
            s = {
                width: Math.abs(this._coordinates[this._coordinates.length - 1]) + 2 * this.settings.stagePadding,
                "padding-left": this.settings.stagePadding || "",
                "padding-right": this.settings.stagePadding || ""
            };
            if (this.$stage.css(s), s = {
                width: this.settings.autoWidth ? "auto": n - this.settings.margin
            },
            s[this.settings.rtl ? "margin-left": "margin-right"] = this.settings.margin, !this.settings.autoWidth && t.grep(this._mergers,
            function(t) {
                return t > 1
            }).length > 0) for (e = 0, i = this._coordinates.length; i > e; e++) s.width = Math.abs(this._coordinates[e]) - Math.abs(this._coordinates[e - 1] || 0) - this.settings.margin,
            this.$stage.children().eq(e).css(s);
            else this.$stage.children().css(s)
        }
    },
    {
        filter: ["width", "items", "settings"],
        run: function(t) {
            t.current && this.reset(this.$stage.children().index(t.current))
        }
    },
    {
        filter: ["position"],
        run: function() {
            this.animate(this.coordinates(this._current))
        }
    },
    {
        filter: ["width", "position", "items", "settings"],
        run: function() {
            var t, e, i, n, s = this.settings.rtl ? 1 : -1,
            o = 2 * this.settings.stagePadding,
            r = this.coordinates(this.current()) + o,
            a = r + this.width() * s,
            l = [];
            for (i = 0, n = this._coordinates.length; n > i; i++) t = this._coordinates[i - 1] || 0,
            e = Math.abs(this._coordinates[i]) + o * s,
            (this.op(t, "<=", r) && this.op(t, ">", a) || this.op(e, "<", r) && this.op(e, ">", a)) && l.push(i);
            this.$stage.children("." + this.settings.activeClass).removeClass(this.settings.activeClass),
            this.$stage.children(":eq(" + l.join("), :eq(") + ")").addClass(this.settings.activeClass),
            this.settings.center && (this.$stage.children("." + this.settings.centerClass).removeClass(this.settings.centerClass), this.$stage.children().eq(this.current()).addClass(this.settings.centerClass))
        }
    }],
    s.prototype.initialize = function() {
        if (this.trigger("initialize"), this.$element.addClass(this.settings.baseClass).addClass(this.settings.themeClass).toggleClass("owl-rtl", this.settings.rtl), this.browserSupport(), this.settings.autoWidth && this.state.imagesLoaded !== !0) {
            var e, i, s;
            if (e = this.$element.find("img"), i = this.settings.nestedItemSelector ? "." + this.settings.nestedItemSelector: n, s = this.$element.children(i).width(), e.length && 0 >= s) return this.preloadAutoWidthImages(e),
            !1
        }
        this.$element.addClass("owl-loading"),
        this.$stage = t("<" + this.settings.stageElement + ' class="owl-stage"/>').wrap('<div class="owl-stage-outer">'),
        this.$element.append(this.$stage.parent()),
        this.replace(this.$element.children().not(this.$stage.parent())),
        this._width = (this.$element.width() < 320 ? 320 : this.$element.width()),
        this.refresh(),
        this.$element.removeClass("owl-loading").addClass("owl-loaded"),
        this.eventsCall(),
        this.internalEvents(),
        this.addTriggerableEvents(),
        this.trigger("initialized")
    },
    s.prototype.setup = function() {
        var e = this.viewport(),
        i = this.options.responsive,
        n = -1,
        s = null;
        i ? (t.each(i,
        function(t) {
            e >= t && t > n && (n = Number(t))
        }), s = t.extend({},
        this.options, i[n]), delete s.responsive, s.responsiveClass && this.$element.attr("class",
        function(t, e) {
            return e.replace(/\b owl-responsive-\S+/g, "")
        }).addClass("owl-responsive-" + n)) : s = t.extend({},
        this.options),
        (null === this.settings || this._breakpoint !== n) && (this.trigger("change", {
            property: {
                name: "settings",
                value: s
            }
        }), this._breakpoint = n, this.settings = s, this.invalidate("settings"), this.trigger("changed", {
            property: {
                name: "settings",
                value: this.settings
            }
        }))
    },
    s.prototype.optionsLogic = function() {
        this.$element.toggleClass("owl-center", this.settings.center),
        this.settings.loop && this._items.length < this.settings.items && (this.settings.loop = !1),
        this.settings.autoWidth && (this.settings.stagePadding = !1, this.settings.merge = !1)
    },
    s.prototype.prepare = function(e) {
        var i = this.trigger("prepare", {
            content: e
        });
        return i.data || (i.data = t("<" + this.settings.itemElement + "/>").addClass(this.settings.itemClass).append(e)),
        this.trigger("prepared", {
            content: i.data
        }),
        i.data
    },
    s.prototype.update = function() {
        for (var e = 0,
        i = this._pipe.length,
        n = t.proxy(function(t) {
            return this[t]
        },
        this._invalidated), s = {}; i > e;)(this._invalidated.all || t.grep(this._pipe[e].filter, n).length > 0) && this._pipe[e].run(s),
        e++;
        this._invalidated = {}
    },
    s.prototype.width = function(t) {
        switch (t = t || s.Width.Default) {
        case s.Width.Inner:
        case s.Width.Outer:
            return this._width;
        default:
            return this._width - 2 * this.settings.stagePadding + this.settings.margin
        }
    },
    s.prototype.refresh = function() {
        if (0 === this._items.length) return ! 1; (new Date).getTime();
        this.trigger("refresh"),
        this.setup(),
        this.optionsLogic(),
        this.$stage.addClass("owl-refresh"),
        this.update(),
        this.$stage.removeClass("owl-refresh"),
        this.state.orientation = e.orientation,
        this.watchVisibility(),
        this.trigger("refreshed")
    },
    s.prototype.eventsCall = function() {
        this.e._onDragStart = t.proxy(function(t) {
            this.onDragStart(t)
        },
        this),
        this.e._onDragMove = t.proxy(function(t) {
            this.onDragMove(t)
        },
        this),
        this.e._onDragEnd = t.proxy(function(t) {
            this.onDragEnd(t)
        },
        this),
        this.e._onResize = t.proxy(function(t) {
            this.onResize(t)
        },
        this),
        this.e._transitionEnd = t.proxy(function(t) {
            this.transitionEnd(t)
        },
        this),
        this.e._preventClick = t.proxy(function(t) {
            this.preventClick(t)
        },
        this)
    },
    s.prototype.onThrottledResize = function() {
        e.clearTimeout(this.resizeTimer),
        this.resizeTimer = e.setTimeout(this.e._onResize, this.settings.responsiveRefreshRate)
    },
    s.prototype.onResize = function() {
        return this._items.length ? this._width === this.$element.width() ? !1 : this.trigger("resize").isDefaultPrevented() ? !1 : (this._width = this.$element.width(), this.invalidate("width"), this.refresh(), void this.trigger("resized")) : !1
    },
    s.prototype.eventsRouter = function(t) {
        var e = t.type;
        "mousedown" === e || "touchstart" === e ? this.onDragStart(t) : "mousemove" === e || "touchmove" === e ? this.onDragMove(t) : "mouseup" === e || "touchend" === e ? this.onDragEnd(t) : "touchcancel" === e && this.onDragEnd(t)
    },
    s.prototype.internalEvents = function() {
        var i = (h(), u());
        this.settings.mouseDrag ? (this.$stage.on("mousedown", t.proxy(function(t) {
            this.eventsRouter(t)
        },
        this)), this.$stage.on("dragstart",
        function() {
            return ! 1
        }), this.$stage.get(0).onselectstart = function() {
            return ! 1
        }) : this.$element.addClass("owl-text-select-on"),
        this.settings.touchDrag && !i && this.$stage.on("touchstart touchcancel", t.proxy(function(t) {
            this.eventsRouter(t)
        },
        this)),
        this.transitionEndVendor && this.on(this.$stage.get(0), this.transitionEndVendor, this.e._transitionEnd, !1),
        this.settings.responsive !== !1 && this.on(e, "resize", t.proxy(this.onThrottledResize, this))
    },
    s.prototype.onDragStart = function(n) {
        var s, r, a, l;
        if (s = n.originalEvent || n || e.event, 3 === s.which || this.state.isTouch) return ! 1;
        if ("mousedown" === s.type && this.$stage.addClass("owl-grab"), this.trigger("drag"), this.drag.startTime = (new Date).getTime(), this.speed(0), this.state.isTouch = !0, this.state.isScrolling = !1, this.state.isSwiping = !1, this.drag.distance = 0, r = o(s).x, a = o(s).y, this.drag.offsetX = this.$stage.position().left, this.drag.offsetY = this.$stage.position().top, this.settings.rtl && (this.drag.offsetX = this.$stage.position().left + this.$stage.width() - this.width() + this.settings.margin), this.state.inMotion && this.support3d) l = this.getTransformProperty(),
        this.drag.offsetX = l,
        this.animate(l),
        this.state.inMotion = !0;
        else if (this.state.inMotion && !this.support3d) return this.state.inMotion = !1,
        !1;
        this.drag.startX = r - this.drag.offsetX,
        this.drag.startY = a - this.drag.offsetY,
        this.drag.start = r - this.drag.startX,
        this.drag.targetEl = s.target || s.srcElement,
        this.drag.updatedX = this.drag.start,
        ("IMG" === this.drag.targetEl.tagName || "A" === this.drag.targetEl.tagName) && (this.drag.targetEl.draggable = !1),
        t(i).on("mousemove.owl.dragEvents mouseup.owl.dragEvents touchmove.owl.dragEvents touchend.owl.dragEvents", t.proxy(function(t) {
            this.eventsRouter(t)
        },
        this))
    },
    s.prototype.onDragMove = function(t) {
        var i, s, r, a, l, c;
        this.state.isTouch && (this.state.isScrolling || (i = t.originalEvent || t || e.event, s = o(i).x, r = o(i).y, this.drag.currentX = s - this.drag.startX, this.drag.currentY = r - this.drag.startY, this.drag.distance = this.drag.currentX - this.drag.offsetX, this.drag.distance < 0 ? this.state.direction = this.settings.rtl ? "right": "left": this.drag.distance > 0 && (this.state.direction = this.settings.rtl ? "left": "right"), this.settings.loop ? this.op(this.drag.currentX, ">", this.coordinates(this.minimum())) && "right" === this.state.direction ? this.drag.currentX -= (this.settings.center && this.coordinates(0)) - this.coordinates(this._items.length) : this.op(this.drag.currentX, "<", this.coordinates(this.maximum())) && "left" === this.state.direction && (this.drag.currentX += (this.settings.center && this.coordinates(0)) - this.coordinates(this._items.length)) : (a = this.coordinates(this.settings.rtl ? this.maximum() : this.minimum()), l = this.coordinates(this.settings.rtl ? this.minimum() : this.maximum()), c = this.settings.pullDrag ? this.drag.distance / 5 : 0, this.drag.currentX = Math.max(Math.min(this.drag.currentX, a + c), l + c)), (this.drag.distance > 8 || this.drag.distance < -8) && (i.preventDefault !== n ? i.preventDefault() : i.returnValue = !1, this.state.isSwiping = !0), this.drag.updatedX = this.drag.currentX, (this.drag.currentY > 16 || this.drag.currentY < -16) && this.state.isSwiping === !1 && (this.state.isScrolling = !0, this.drag.updatedX = this.drag.start), this.animate(this.drag.updatedX)))
    },
    s.prototype.onDragEnd = function(e) {
        var n, s, o;
        if (this.state.isTouch) {
            if ("mouseup" === e.type && this.$stage.removeClass("owl-grab"), this.trigger("dragged"), this.drag.targetEl.removeAttribute("draggable"), this.state.isTouch = !1, this.state.isScrolling = !1, this.state.isSwiping = !1, 0 === this.drag.distance && this.state.inMotion !== !0) return this.state.inMotion = !1,
            !1;
            this.drag.endTime = (new Date).getTime(),
            n = this.drag.endTime - this.drag.startTime,
            s = Math.abs(this.drag.distance),
            (s > 3 || n > 300) && this.removeClick(this.drag.targetEl),
            o = this.closest(this.drag.updatedX),
            this.speed(this.settings.dragEndSpeed || this.settings.smartSpeed),
            this.current(o),
            this.invalidate("position"),
            this.update(),
            this.settings.pullDrag || this.drag.updatedX !== this.coordinates(o) || this.transitionEnd(),
            this.drag.distance = 0,
            t(i).off(".owl.dragEvents")
        }
    },
    s.prototype.removeClick = function(i) {
        this.drag.targetEl = i,
        t(i).on("click.preventClick", this.e._preventClick),
        e.setTimeout(function() {
            t(i).off("click.preventClick")
        },
        300)
    },
    s.prototype.preventClick = function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = !1,
        e.stopPropagation && e.stopPropagation(),
        t(e.target).off("click.preventClick")
    },
    s.prototype.getTransformProperty = function() {
        var t, i;
        return t = e.getComputedStyle(this.$stage.get(0), null).getPropertyValue(this.vendorName + "transform"),
        t = t.replace(/matrix(3d)?\(|\)/g, "").split(","),
        i = 16 === t.length,
        i !== !0 ? t[4] : t[12]
    },
    s.prototype.closest = function(e) {
        var i = -1,
        n = 30,
        s = this.width(),
        o = this.coordinates();
        return this.settings.freeDrag || t.each(o, t.proxy(function(t, r) {
            return e > r - n && r + n > e ? i = t: this.op(e, "<", r) && this.op(e, ">", o[t + 1] || r - s) && (i = "left" === this.state.direction ? t + 1 : t),
            -1 === i
        },
        this)),
        this.settings.loop || (this.op(e, ">", o[this.minimum()]) ? i = e = this.minimum() : this.op(e, "<", o[this.maximum()]) && (i = e = this.maximum())),
        i
    },
    s.prototype.animate = function(e) {
        this.trigger("translate"),
        this.state.inMotion = this.speed() > 0,
        this.support3d ? this.$stage.css({
            transform: "translate3d(" + e + "px,0px, 0px)",
            transition: this.speed() / 1e3 + "s"
        }) : this.state.isTouch ? this.$stage.css({
            left: e + "px"
        }) : this.$stage.animate({
            left: e
        },
        this.speed() / 1e3, this.settings.fallbackEasing, t.proxy(function() {
            this.state.inMotion && this.transitionEnd()
        },
        this))
    },
    s.prototype.current = function(t) {
        if (t === n) return this._current;
        if (0 === this._items.length) return n;
        if (t = this.normalize(t), this._current !== t) {
            var e = this.trigger("change", {
                property: {
                    name: "position",
                    value: t
                }
            });
            e.data !== n && (t = this.normalize(e.data)),
            this._current = t,
            this.invalidate("position"),
            this.trigger("changed", {
                property: {
                    name: "position",
                    value: this._current
                }
            })
        }
        return this._current
    },
    s.prototype.invalidate = function(t) {
        this._invalidated[t] = !0
    },
    s.prototype.reset = function(t) {
        t = this.normalize(t),
        t !== n && (this._speed = 0, this._current = t, this.suppress(["translate", "translated"]), this.animate(this.coordinates(t)), this.release(["translate", "translated"]))
    },
    s.prototype.normalize = function(e, i) {
        var s = i ? this._items.length: this._items.length + this._clones.length;
        return ! t.isNumeric(e) || 1 > s ? n: e = this._clones.length ? (e % s + s) % s: Math.max(this.minimum(i), Math.min(this.maximum(i), e))
    },
    s.prototype.relative = function(t) {
        return t = this.normalize(t),
        t -= this._clones.length / 2,
        this.normalize(t, !0)
    },
    s.prototype.maximum = function(t) {
        var e, i, n, s = 0,
        o = this.settings;
        if (t) return this._items.length - 1;
        if (!o.loop && o.center) e = this._items.length - 1;
        else if (o.loop || o.center) if (o.loop || o.center) e = this._items.length + o.items;
        else {
            if (!o.autoWidth && !o.merge) throw "Can not detect maximum absolute position.";
            for (revert = o.rtl ? 1 : -1, i = this.$stage.width() - this.$element.width(); (n = this.coordinates(s)) && !(n * revert >= i);) e = ++s
        } else e = this._items.length - o.items;
        return e
    },
    s.prototype.minimum = function(t) {
        return t ? 0 : this._clones.length / 2
    },
    s.prototype.items = function(t) {
        return t === n ? this._items.slice() : (t = this.normalize(t, !0), this._items[t])
    },
    s.prototype.mergers = function(t) {
        return t === n ? this._mergers.slice() : (t = this.normalize(t, !0), this._mergers[t])
    },
    s.prototype.clones = function(e) {
        var i = this._clones.length / 2,
        s = i + this._items.length,
        o = function(t) {
            return t % 2 === 0 ? s + t / 2 : i - (t + 1) / 2
        };
        return e === n ? t.map(this._clones,
        function(t, e) {
            return o(e)
        }) : t.map(this._clones,
        function(t, i) {
            return t === e ? o(i) : null
        })
    },
    s.prototype.speed = function(t) {
        return t !== n && (this._speed = t),
        this._speed
    },
    s.prototype.coordinates = function(e) {
        var i = null;
        return e === n ? t.map(this._coordinates, t.proxy(function(t, e) {
            return this.coordinates(e)
        },
        this)) : (this.settings.center ? (i = this._coordinates[e], i += (this.width() - i + (this._coordinates[e - 1] || 0)) / 2 * (this.settings.rtl ? -1 : 1)) : i = this._coordinates[e - 1] || 0, i)
    },
    s.prototype.duration = function(t, e, i) {
        return Math.min(Math.max(Math.abs(e - t), 1), 6) * Math.abs(i || this.settings.smartSpeed)
    },
    s.prototype.to = function(i, n) {
        if (this.settings.loop) {
            var s = i - this.relative(this.current()),
            o = this.current(),
            r = this.current(),
            a = this.current() + s,
            l = 0 > r - a ? !0 : !1,
            c = this._clones.length + this._items.length;
            a < this.settings.items && l === !1 ? (o = r + this._items.length, this.reset(o)) : a >= c - this.settings.items && l === !0 && (o = r - this._items.length, this.reset(o)),
            e.clearTimeout(this.e._goToLoop),
            this.e._goToLoop = e.setTimeout(t.proxy(function() {
                this.speed(this.duration(this.current(), o + s, n)),
                this.current(o + s),
                this.update()
            },
            this), 30)
        } else this.speed(this.duration(this.current(), i, n)),
        this.current(i),
        this.update()
    },
    s.prototype.next = function(t) {
        t = t || !1,
        this.to(this.relative(this.current()) + 1, t)
    },
    s.prototype.prev = function(t) {
        t = t || !1,
        this.to(this.relative(this.current()) - 1, t)
    },
    s.prototype.transitionEnd = function(t) {
        return t !== n && (t.stopPropagation(), (t.target || t.srcElement || t.originalTarget) !== this.$stage.get(0)) ? !1 : (this.state.inMotion = !1, void this.trigger("translated"))
    },
    s.prototype.viewport = function() {
        var n;
        if (this.options.responsiveBaseElement !== e) n = t(this.options.responsiveBaseElement).width();
        else if (e.innerWidth) n = e.innerWidth;
        else {
            if (!i.documentElement || !i.documentElement.clientWidth) throw "Can not detect viewport width.";
            n = i.documentElement.clientWidth
        }
        return n
    },
    s.prototype.replace = function(e) {
        this.$stage.empty(),
        this._items = [],
        e && (e = e instanceof jQuery ? e: t(e)),
        this.settings.nestedItemSelector && (e = e.find("." + this.settings.nestedItemSelector)),
        e.filter(function() {
            return 1 === this.nodeType
        }).each(t.proxy(function(t, e) {
            e = this.prepare(e),
            this.$stage.append(e),
            this._items.push(e),
            this._mergers.push(1 * e.find("[data-merge]").andSelf("[data-merge]").attr("data-merge") || 1)
        },
        this)),
        this.reset(t.isNumeric(this.settings.startPosition) ? this.settings.startPosition: 0),
        this.invalidate("items")
    },
    s.prototype.add = function(t, e) {
        e = e === n ? this._items.length: this.normalize(e, !0),
        this.trigger("add", {
            content: t,
            position: e
        }),
        0 === this._items.length || e === this._items.length ? (this.$stage.append(t), this._items.push(t), this._mergers.push(1 * t.find("[data-merge]").andSelf("[data-merge]").attr("data-merge") || 1)) : (this._items[e].before(t), this._items.splice(e, 0, t), this._mergers.splice(e, 0, 1 * t.find("[data-merge]").andSelf("[data-merge]").attr("data-merge") || 1)),
        this.invalidate("items"),
        this.trigger("added", {
            content: t,
            position: e
        })
    },
    s.prototype.remove = function(t) {
        t = this.normalize(t, !0),
        t !== n && (this.trigger("remove", {
            content: this._items[t],
            position: t
        }), this._items[t].remove(), this._items.splice(t, 1), this._mergers.splice(t, 1), this.invalidate("items"), this.trigger("removed", {
            content: null,
            position: t
        }))
    },
    s.prototype.addTriggerableEvents = function() {
        var e = t.proxy(function(e, i) {
            return t.proxy(function(t) {
                t.relatedTarget !== this && (this.suppress([i]), e.apply(this, [].slice.call(arguments, 1)), this.release([i]))
            },
            this)
        },
        this);
        t.each({
            next: this.next,
            prev: this.prev,
            to: this.to,
            destroy: this.destroy,
            refresh: this.refresh,
            replace: this.replace,
            add: this.add,
            remove: this.remove
        },
        t.proxy(function(t, i) {
            this.$element.on(t + ".owl.carousel", e(i, t + ".owl.carousel"))
        },
        this))
    },
    s.prototype.watchVisibility = function() {
        function i(t) {
            return t.offsetWidth > 0 && t.offsetHeight > 0
        }
        function n() {
            i(this.$element.get(0)) && (this.$element.removeClass("owl-hidden"), this.refresh(), e.clearInterval(this.e._checkVisibile))
        }
        i(this.$element.get(0)) || (this.$element.addClass("owl-hidden"), e.clearInterval(this.e._checkVisibile), this.e._checkVisibile = e.setInterval(t.proxy(n, this), 500))
    },
    s.prototype.preloadAutoWidthImages = function(e) {
        var i, n, s, o;
        i = 0,
        n = this,
        e.each(function(r, a) {
            s = t(a),
            o = new Image,
            o.onload = function() {
                i++,
                s.attr("src", o.src),
                s.css("opacity", 1),
                i >= e.length && (n.state.imagesLoaded = !0, n.initialize())
            },
            o.src = s.attr("src") || s.attr("data-src") || s.attr("data-src-retina")
        })
    },
    s.prototype.destroy = function() {
        this.$element.hasClass(this.settings.themeClass) && this.$element.removeClass(this.settings.themeClass),
        this.settings.responsive !== !1 && t(e).off("resize.owl.carousel"),
        this.transitionEndVendor && this.off(this.$stage.get(0), this.transitionEndVendor, this.e._transitionEnd);
        for (var n in this._plugins) this._plugins[n].destroy(); (this.settings.mouseDrag || this.settings.touchDrag) && (this.$stage.off("mousedown touchstart touchcancel"), t(i).off(".owl.dragEvents"), this.$stage.get(0).onselectstart = function() {},
        this.$stage.off("dragstart",
        function() {
            return ! 1
        })),
        this.$element.off(".owl"),
        this.$stage.children(".cloned").remove(),
        this.e = null,
        this.$element.removeData("owlCarousel"),
        this.$stage.children().contents().unwrap(),
        this.$stage.children().unwrap(),
        this.$stage.unwrap()
    },
    s.prototype.op = function(t, e, i) {
        var n = this.settings.rtl;
        switch (e) {
        case "<":
            return n ? t > i: i > t;
        case ">":
            return n ? i > t: t > i;
        case ">=":
            return n ? i >= t: t >= i;
        case "<=":
            return n ? t >= i: i >= t
        }
    },
    s.prototype.on = function(t, e, i, n) {
        t.addEventListener ? t.addEventListener(e, i, n) : t.attachEvent && t.attachEvent("on" + e, i)
    },
    s.prototype.off = function(t, e, i, n) {
        t.removeEventListener ? t.removeEventListener(e, i, n) : t.detachEvent && t.detachEvent("on" + e, i)
    },
    s.prototype.trigger = function(e, i, n) {
        var s = {
            item: {
                count: this._items.length,
                index: this.current()
            }
        },
        o = t.camelCase(t.grep(["on", e, n],
        function(t) {
            return t
        }).join("-").toLowerCase()),
        r = t.Event([e, "owl", n || "carousel"].join(".").toLowerCase(), t.extend({
            relatedTarget: this
        },
        s, i));
        return this._supress[e] || (t.each(this._plugins,
        function(t, e) {
            e.onTrigger && e.onTrigger(r)
        }), this.$element.trigger(r), this.settings && "function" == typeof this.settings[o] && this.settings[o].apply(this, r)),
        r
    },
    s.prototype.suppress = function(e) {
        t.each(e, t.proxy(function(t, e) {
            this._supress[e] = !0
        },
        this))
    },
    s.prototype.release = function(e) {
        t.each(e, t.proxy(function(t, e) {
            delete this._supress[e]
        },
        this))
    },
    s.prototype.browserSupport = function() {
        if (this.support3d = c(), this.support3d) {
            this.transformVendor = l();
            var t = ["transitionend", "webkitTransitionEnd", "transitionend", "oTransitionEnd"];
            this.transitionEndVendor = t[a()],
            this.vendorName = this.transformVendor.replace(/Transform/i, ""),
            this.vendorName = "" !== this.vendorName ? "-" + this.vendorName.toLowerCase() + "-": ""
        }
        this.state.orientation = e.orientation
    },
    t.fn.owlCarousel = function(e) {
        return this.each(function() {
            t(this).data("owlCarousel") || t(this).data("owlCarousel", new s(this, e))
        })
    },
    t.fn.owlCarousel.Constructor = s
} (window.Zepto || window.jQuery, window, document),
function(t, e) {
    var i = function(e) {
        this._core = e,
        this._loaded = [],
        this._handlers = {
            "initialized.owl.carousel change.owl.carousel": t.proxy(function(e) {
                if (e.namespace && this._core.settings && this._core.settings.lazyLoad && (e.property && "position" == e.property.name || "initialized" == e.type)) for (var i = this._core.settings,
                n = i.center && Math.ceil(i.items / 2) || i.items, s = i.center && -1 * n || 0, o = (e.property && e.property.value || this._core.current()) + s, r = this._core.clones().length, a = t.proxy(function(t, e) {
                    this.load(e)
                },
                this); s++<n;) this.load(r / 2 + this._core.relative(o)),
                r && t.each(this._core.clones(this._core.relative(o++)), a)
            },
            this)
        },
        this._core.options = t.extend({},
        i.Defaults, this._core.options),
        this._core.$element.on(this._handlers)
    };
    i.Defaults = {
        lazyLoad: !1
    },
    i.prototype.load = function(i) {
        var n = this._core.$stage.children().eq(i),
        s = n && n.find(".owl-lazy"); ! s || t.inArray(n.get(0), this._loaded) > -1 || (s.each(t.proxy(function(i, n) {
            var s, o = t(n),
            r = e.devicePixelRatio > 1 && o.attr("data-src-retina") || o.attr("data-src");
            this._core.trigger("load", {
                element: o,
                url: r
            },
            "lazy"),
            o.is("img") ? o.one("load.owl.lazy", t.proxy(function() {
                o.css("opacity", 1),
                this._core.trigger("loaded", {
                    element: o,
                    url: r
                },
                "lazy")
            },
            this)).attr("src", r) : (s = new Image, s.onload = t.proxy(function() {
                o.css({
                    "background-image": "url(" + r + ")",
                    opacity: "1"
                }),
                this._core.trigger("loaded", {
                    element: o,
                    url: r
                },
                "lazy")
            },
            this), s.src = r)
        },
        this)), this._loaded.push(n.get(0)))
    },
    i.prototype.destroy = function() {
        var t, e;
        for (t in this.handlers) this._core.$element.off(t, this.handlers[t]);
        for (e in Object.getOwnPropertyNames(this))"function" != typeof this[e] && (this[e] = null)
    },
    t.fn.owlCarousel.Constructor.Plugins.Lazy = i
} (window.Zepto || window.jQuery, window, document),
function(t) {
    var e = function(i) {
        this._core = i,
        this._handlers = {
            "initialized.owl.carousel": t.proxy(function() {
                this._core.settings.autoHeight && this.update()
            },
            this),
            "changed.owl.carousel": t.proxy(function(t) {
                this._core.settings.autoHeight && "position" == t.property.name && this.update()
            },
            this),
            "loaded.owl.lazy": t.proxy(function(t) {
                this._core.settings.autoHeight && t.element.closest("." + this._core.settings.itemClass) === this._core.$stage.children().eq(this._core.current()) && this.update()
            },
            this)
        },
        this._core.options = t.extend({},
        e.Defaults, this._core.options),
        this._core.$element.on(this._handlers)
    };
    e.Defaults = {
        autoHeight: !1,
        autoHeightClass: "owl-height"
    },
    e.prototype.update = function() {
        this._core.$stage.parent().height(this._core.$stage.children().eq(this._core.current()).height()).addClass(this._core.settings.autoHeightClass)
    },
    e.prototype.destroy = function() {
        var t, e;
        for (t in this._handlers) this._core.$element.off(t, this._handlers[t]);
        for (e in Object.getOwnPropertyNames(this))"function" != typeof this[e] && (this[e] = null)
    },
    t.fn.owlCarousel.Constructor.Plugins.AutoHeight = e
} (window.Zepto || window.jQuery, window, document),
function(t, e, i) {
    var n = function(e) {
        this._core = e,
        this._videos = {},
        this._playing = null,
        this._fullscreen = !1,
        this._handlers = {
            "resize.owl.carousel": t.proxy(function(t) {
                this._core.settings.video && !this.isInFullScreen() && t.preventDefault()
            },
            this),
            "refresh.owl.carousel changed.owl.carousel": t.proxy(function() {
                this._playing && this.stop()
            },
            this),
            "prepared.owl.carousel": t.proxy(function(e) {
                var i = t(e.content).find(".owl-video");
                i.length && (i.css("display", "none"), this.fetch(i, t(e.content)))
            },
            this)
        },
        this._core.options = t.extend({},
        n.Defaults, this._core.options),
        this._core.$element.on(this._handlers),
        this._core.$element.on("click.owl.video", ".owl-video-play-icon", t.proxy(function(t) {
            this.play(t)
        },
        this))
    };
    n.Defaults = {
        video: !1,
        videoHeight: !1,
        videoWidth: !1
    },
    n.prototype.fetch = function(t, e) {
        var i = t.attr("data-vimeo-id") ? "vimeo": "youtube",
        n = t.attr("data-vimeo-id") || t.attr("data-youtube-id"),
        s = t.attr("data-width") || this._core.settings.videoWidth,
        o = t.attr("data-height") || this._core.settings.videoHeight,
        r = t.attr("href");
        if (!r) throw new Error("Missing video URL.");
        if (n = r.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/), n[3].indexOf("youtu") > -1) i = "youtube";
        else {
            if (! (n[3].indexOf("vimeo") > -1)) throw new Error("Video URL not supported.");
            i = "vimeo"
        }
        n = n[6],
        this._videos[r] = {
            type: i,
            id: n,
            width: s,
            height: o
        },
        e.attr("data-video", r),
        this.thumbnail(t, this._videos[r])
    },
    n.prototype.thumbnail = function(e, i) {
        var n, s, o, r = i.width && i.height ? 'style="width:' + i.width + "px;height:" + i.height + 'px;"': "",
        a = e.find("img"),
        l = "src",
        c = "",
        h = this._core.settings,
        u = function(t) {
            s = '<div class="owl-video-play-icon"></div>',
            n = h.lazyLoad ? '<div class="owl-video-tn ' + c + '" ' + l + '="' + t + '"></div>': '<div class="owl-video-tn" style="opacity:1;background-image:url(' + t + ')"></div>',
            e.after(n),
            e.after(s)
        };
        return e.wrap('<div class="owl-video-wrapper"' + r + "></div>"),
        this._core.settings.lazyLoad && (l = "data-src", c = "owl-lazy"),
        a.length ? (u(a.attr(l)), a.remove(), !1) : void("youtube" === i.type ? (o = "http://img.youtube.com/vi/" + i.id + "/hqdefault.jpg", u(o)) : "vimeo" === i.type && t.ajax({
            type: "GET",
            url: "http://vimeo.com/api/v2/video/" + i.id + ".json",
            jsonp: "callback",
            dataType: "jsonp",
            success: function(t) {
                o = t[0].thumbnail_large,
                u(o)
            }
        }))
    },
    n.prototype.stop = function() {
        this._core.trigger("stop", null, "video"),
        this._playing.find(".owl-video-frame").remove(),
        this._playing.removeClass("owl-video-playing"),
        this._playing = null
    },
    n.prototype.play = function(e) {
        this._core.trigger("play", null, "video"),
        this._playing && this.stop();
        var i, n, s = t(e.target || e.srcElement),
        o = s.closest("." + this._core.settings.itemClass),
        r = this._videos[o.attr("data-video")],
        a = r.width || "100%",
        l = r.height || this._core.$stage.height();
        "youtube" === r.type ? i = '<iframe width="' + a + '" height="' + l + '" src="http://www.youtube.com/embed/' + r.id + "?autoplay=1&v=" + r.id + '" frameborder="0" allowfullscreen></iframe>': "vimeo" === r.type && (i = '<iframe src="http://player.vimeo.com/video/' + r.id + '?autoplay=1" width="' + a + '" height="' + l + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'),
        o.addClass("owl-video-playing"),
        this._playing = o,
        n = t('<div style="height:' + l + "px; width:" + a + 'px" class="owl-video-frame">' + i + "</div>"),
        s.after(n)
    },
    n.prototype.isInFullScreen = function() {
        var n = i.fullscreenElement || i.mozFullScreenElement || i.webkitFullscreenElement;
        return n && t(n).parent().hasClass("owl-video-frame") && (this._core.speed(0), this._fullscreen = !0),
        n && this._fullscreen && this._playing ? !1 : this._fullscreen ? (this._fullscreen = !1, !1) : this._playing && this._core.state.orientation !== e.orientation ? (this._core.state.orientation = e.orientation, !1) : !0
    },
    n.prototype.destroy = function() {
        var t, e;
        this._core.$element.off("click.owl.video");
        for (t in this._handlers) this._core.$element.off(t, this._handlers[t]);
        for (e in Object.getOwnPropertyNames(this))"function" != typeof this[e] && (this[e] = null)
    },
    t.fn.owlCarousel.Constructor.Plugins.Video = n
} (window.Zepto || window.jQuery, window, document),
function(t, e, i, n) {
    var s = function(e) {
        this.core = e,
        this.core.options = t.extend({},
        s.Defaults, this.core.options),
        this.swapping = !0,
        this.previous = n,
        this.next = n,
        this.handlers = {
            "change.owl.carousel": t.proxy(function(t) {
                "position" == t.property.name && (this.previous = this.core.current(), this.next = t.property.value)
            },
            this),
            "drag.owl.carousel dragged.owl.carousel translated.owl.carousel": t.proxy(function(t) {
                this.swapping = "translated" == t.type
            },
            this),
            "translate.owl.carousel": t.proxy(function() {
                this.swapping && (this.core.options.animateOut || this.core.options.animateIn) && this.swap()
            },
            this)
        },
        this.core.$element.on(this.handlers)
    };
    s.Defaults = {
        animateOut: !1,
        animateIn: !1
    },
    s.prototype.swap = function() {
        if (1 === this.core.settings.items && this.core.support3d) {
            this.core.speed(0);
            var e, i = t.proxy(this.clear, this),
            n = this.core.$stage.children().eq(this.previous),
            s = this.core.$stage.children().eq(this.next),
            o = this.core.settings.animateIn,
            r = this.core.settings.animateOut;
            this.core.current() !== this.previous && (r && (e = this.core.coordinates(this.previous) - this.core.coordinates(this.next), n.css({
                left: e + "px"
            }).addClass("animated owl-animated-out").addClass(r).one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", i)), o && s.addClass("animated owl-animated-in").addClass(o).one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", i))
        }
    },
    s.prototype.clear = function(e) {
        t(e.target).css({
            left: ""
        }).removeClass("animated owl-animated-out owl-animated-in").removeClass(this.core.settings.animateIn).removeClass(this.core.settings.animateOut),
        this.core.transitionEnd()
    },
    s.prototype.destroy = function() {
        var t, e;
        for (t in this.handlers) this.core.$element.off(t, this.handlers[t]);
        for (e in Object.getOwnPropertyNames(this))"function" != typeof this[e] && (this[e] = null)
    },
    t.fn.owlCarousel.Constructor.Plugins.Animate = s
} (window.Zepto || window.jQuery, window, document),
function(t, e, i) {
    var n = function(e) {
        this.core = e,
        this.core.options = t.extend({},
        n.Defaults, this.core.options),
        this.handlers = {
            "translated.owl.carousel refreshed.owl.carousel": t.proxy(function() {
                this.autoplay()
            },
            this),
            "play.owl.autoplay": t.proxy(function(t, e, i) {
                this.play(e, i)
            },
            this),
            "stop.owl.autoplay": t.proxy(function() {
                this.stop()
            },
            this),
            "mouseover.owl.autoplay": t.proxy(function() {
                this.core.settings.autoplayHoverPause && this.pause()
            },
            this),
            "mouseleave.owl.autoplay": t.proxy(function() {
                this.core.settings.autoplayHoverPause && this.autoplay()
            },
            this)
        },
        this.core.$element.on(this.handlers)
    };
    n.Defaults = {
        autoplay: !1,
        autoplayTimeout: 5e3,
        autoplayHoverPause: !1,
        autoplaySpeed: !1
    },
    n.prototype.autoplay = function() {
        this.core.settings.autoplay && !this.core.state.videoPlay ? (e.clearInterval(this.interval), this.interval = e.setInterval(t.proxy(function() {
            this.play()
        },
        this), this.core.settings.autoplayTimeout)) : e.clearInterval(this.interval)
    },
    n.prototype.play = function() {
        return i.hidden === !0 || this.core.state.isTouch || this.core.state.isScrolling || this.core.state.isSwiping || this.core.state.inMotion ? void 0 : this.core.settings.autoplay === !1 ? void e.clearInterval(this.interval) : void this.core.next(this.core.settings.autoplaySpeed)
    },
    n.prototype.stop = function() {
        e.clearInterval(this.interval)
    },
    n.prototype.pause = function() {
        e.clearInterval(this.interval)
    },
    n.prototype.destroy = function() {
        var t, i;
        e.clearInterval(this.interval);
        for (t in this.handlers) this.core.$element.off(t, this.handlers[t]);
        for (i in Object.getOwnPropertyNames(this))"function" != typeof this[i] && (this[i] = null)
    },
    t.fn.owlCarousel.Constructor.Plugins.autoplay = n
} (window.Zepto || window.jQuery, window, document),
function(t) {
    "use strict";
    var e = function(i) {
        this._core = i,
        this._initialized = !1,
        this._pages = [],
        this._controls = {},
        this._templates = [],
        this.$element = this._core.$element,
        this._overrides = {
            next: this._core.next,
            prev: this._core.prev,
            to: this._core.to
        },
        this._handlers = {
            "prepared.owl.carousel": t.proxy(function(e) {
                this._core.settings.dotsData && this._templates.push(t(e.content).find("[data-dot]").andSelf("[data-dot]").attr("data-dot"))
            },
            this),
            "add.owl.carousel": t.proxy(function(e) {
                this._core.settings.dotsData && this._templates.splice(e.position, 0, t(e.content).find("[data-dot]").andSelf("[data-dot]").attr("data-dot"))
            },
            this),
            "remove.owl.carousel prepared.owl.carousel": t.proxy(function(t) {
                this._core.settings.dotsData && this._templates.splice(t.position, 1)
            },
            this),
            "change.owl.carousel": t.proxy(function(t) {
                if ("position" == t.property.name && !this._core.state.revert && !this._core.settings.loop && this._core.settings.navRewind) {
                    var e = this._core.current(),
                    i = this._core.maximum(),
                    n = this._core.minimum();
                    t.data = t.property.value > i ? e >= i ? n: i: t.property.value < n ? i: t.property.value
                }
            },
            this),
            "changed.owl.carousel": t.proxy(function(t) {
                "position" == t.property.name && this.draw()
            },
            this),
            "refreshed.owl.carousel": t.proxy(function() {
                this._initialized || (this.initialize(), this._initialized = !0),
                this._core.trigger("refresh", null, "navigation"),
                this.update(),
                this.draw(),
                this._core.trigger("refreshed", null, "navigation")
            },
            this)
        },
        this._core.options = t.extend({},
        e.Defaults, this._core.options),
        this.$element.on(this._handlers)
    };
    e.Defaults = {
        nav: !1,
        navRewind: !0,
        navText: ["prev", "next"],
        navSpeed: !1,
        navElement: "div",
        navContainer: !1,
        navContainerClass: "owl-nav",
        navClass: ["owl-prev", "owl-next"],
        slideBy: 1,
        dotClass: "owl-dot",
        dotsClass: "owl-dots",
        dots: !0,
        dotsEach: !1,
        dotData: !1,
        dotsSpeed: !1,
        dotsContainer: !1,
        controlsClass: "owl-controls"
    },
    e.prototype.initialize = function() {
        var e, i, n = this._core.settings;
        n.dotsData || (this._templates = [t("<div>").addClass(n.dotClass).append(t("<span>")).prop("outerHTML")]),
        n.navContainer && n.dotsContainer || (this._controls.$container = t("<div>").addClass(n.controlsClass).appendTo(this.$element)),
        this._controls.$indicators = n.dotsContainer ? t(n.dotsContainer) : t("<div>").hide().addClass(n.dotsClass).appendTo(this._controls.$container),
        this._controls.$indicators.on("click", "div", t.proxy(function(e) {
            var i = t(e.target).parent().is(this._controls.$indicators) ? t(e.target).index() : t(e.target).parent().index();
            e.preventDefault(),
            this.to(i, n.dotsSpeed)
        },
        this)),
        e = n.navContainer ? t(n.navContainer) : t("<div>").addClass(n.navContainerClass).prependTo(this._controls.$container),
        this._controls.$next = t("<" + n.navElement + ">"),
        this._controls.$previous = this._controls.$next.clone(),
        this._controls.$previous.addClass(n.navClass[0]).html(n.navText[0]).hide().prependTo(e).on("click", t.proxy(function() {
            this.prev(n.navSpeed)
        },
        this)),
        this._controls.$next.addClass(n.navClass[1]).html(n.navText[1]).hide().appendTo(e).on("click", t.proxy(function() {
            this.next(n.navSpeed)
        },
        this));
        for (i in this._overrides) this._core[i] = t.proxy(this[i], this)
    },
    e.prototype.destroy = function() {
        var t, e, i, n;
        for (t in this._handlers) this.$element.off(t, this._handlers[t]);
        for (e in this._controls) this._controls[e].remove();
        for (n in this.overides) this._core[n] = this._overrides[n];
        for (i in Object.getOwnPropertyNames(this))"function" != typeof this[i] && (this[i] = null)
    },
    e.prototype.update = function() {
        var t, e, i, n = this._core.settings,
        s = this._core.clones().length / 2,
        o = s + this._core.items().length,
        r = n.center || n.autoWidth || n.dotData ? 1 : n.dotsEach || n.items;
        if ("page" !== n.slideBy && (n.slideBy = Math.min(n.slideBy, n.items)), n.dots || "page" == n.slideBy) for (this._pages = [], t = s, e = 0, i = 0; o > t; t++)(e >= r || 0 === e) && (this._pages.push({
            start: t - s,
            end: t - s + r - 1
        }), e = 0, ++i),
        e += this._core.mergers(this._core.relative(t))
    },
    e.prototype.draw = function() {
        var e, i, n = "",
        s = this._core.settings,
        o = (this._core.$stage.children(), this._core.relative(this._core.current()));
        if (!s.nav || s.loop || s.navRewind || (this._controls.$previous.toggleClass("disabled", 0 >= o), this._controls.$next.toggleClass("disabled", o >= this._core.maximum())), this._controls.$previous.toggle(s.nav), this._controls.$next.toggle(s.nav), s.dots) {
            if (e = this._pages.length - this._controls.$indicators.children().length, s.dotData && 0 !== e) {
                for (i = 0; i < this._controls.$indicators.children().length; i++) n += this._templates[this._core.relative(i)];
                this._controls.$indicators.html(n)
            } else e > 0 ? (n = new Array(e + 1).join(this._templates[0]), this._controls.$indicators.append(n)) : 0 > e && this._controls.$indicators.children().slice(e).remove();
            this._controls.$indicators.find(".active").removeClass("active"),
            this._controls.$indicators.children().eq(t.inArray(this.current(), this._pages)).addClass("active")
        }
        this._controls.$indicators.toggle(s.dots)
    },
    e.prototype.onTrigger = function(e) {
        var i = this._core.settings;
        e.page = {
            index: t.inArray(this.current(), this._pages),
            count: this._pages.length,
            size: i && (i.center || i.autoWidth || i.dotData ? 1 : i.dotsEach || i.items)
        }
    },
    e.prototype.current = function() {
        var e = this._core.relative(this._core.current());
        return t.grep(this._pages,
        function(t) {
            return t.start <= e && t.end >= e
        }).pop()
    },
    e.prototype.getPosition = function(e) {
        var i, n, s = this._core.settings;
        return "page" == s.slideBy ? (i = t.inArray(this.current(), this._pages), n = this._pages.length, e ? ++i: --i, i = this._pages[(i % n + n) % n].start) : (i = this._core.relative(this._core.current()), n = this._core.items().length, e ? i += s.slideBy: i -= s.slideBy),
        i
    },
    e.prototype.next = function(e) {
        t.proxy(this._overrides.to, this._core)(this.getPosition(!0), e)
    },
    e.prototype.prev = function(e) {
        t.proxy(this._overrides.to, this._core)(this.getPosition(!1), e)
    },
    e.prototype.to = function(e, i, n) {
        var s;
        n ? t.proxy(this._overrides.to, this._core)(e, i) : (s = this._pages.length, t.proxy(this._overrides.to, this._core)(this._pages[(e % s + s) % s].start, i))
    },
    t.fn.owlCarousel.Constructor.Plugins.Navigation = e
} (window.Zepto || window.jQuery, window, document),
function(t, e) {
    "use strict";
    var i = function(n) {
        this._core = n,
        this._hashes = {},
        this.$element = this._core.$element,
        this._handlers = {
            "initialized.owl.carousel": t.proxy(function() {
                "URLHash" == this._core.settings.startPosition && t(e).trigger("hashchange.owl.navigation")
            },
            this),
            "prepared.owl.carousel": t.proxy(function(e) {
                var i = t(e.content).find("[data-hash]").andSelf("[data-hash]").attr("data-hash");
                this._hashes[i] = e.content
            },
            this)
        },
        this._core.options = t.extend({},
        i.Defaults, this._core.options),
        this.$element.on(this._handlers),
        t(e).on("hashchange.owl.navigation", t.proxy(function() {
            var t = e.location.hash.substring(1),
            i = this._core.$stage.children(),
            n = this._hashes[t] && i.index(this._hashes[t]) || 0;
            return t ? void this._core.to(n, !1, !0) : !1
        },
        this))
    };
    i.Defaults = {
        URLhashListener: !1
    },
    i.prototype.destroy = function() {
        var i, n;
        t(e).off("hashchange.owl.navigation");
        for (i in this._handlers) this._core.$element.off(i, this._handlers[i]);
        for (n in Object.getOwnPropertyNames(this))"function" != typeof this[n] && (this[n] = null)
    },
    t.fn.owlCarousel.Constructor.Plugins.Hash = i
} (window.Zepto || window.jQuery, window, document),
define("owl_carousel",
function() {}),
define("components/slideshow/main", ["components/base", "components/slideshow/assets", "owl_carousel"],
function(t, e) {
    "use strict";
    var i = {},
    n = e.templates;
    return i.defaults = McMore.componentDefault.slideshow,
    i.tpls = {
        tpl_1: n.tpl_1
    },
    i.PreviewView = t.PreviewView.extend({
        template: function(t) {
            var e = $(_.template(n.preview, t)),
            s = [];
            return _(t.data).each(function(e, n) {
                n >= t.dataLimit || s.push(_.template(i.tpls[t.templateId], e))
            }),
            e.find(".component-data").html(s.join("")),
            e
        },
        _onRender: function() {
            this.$el.find("#slideshow .slide").length < 2 || this.$el.find("#slideshow").owlCarousel({
                items: 1,
                loop: !0,
                autoplay: !0,
                rtl: 0 == this.model.get("rtl") ? !1 : !0,
                autoplayTimeout: this.model.get("autoplayTimeout")
            })
        }
    }),
    i.ConfigView = t.ConfigView.extend({
        template: function(t) {
            var e = $(_.template(n.config, t));
            return e
        },
        ui: {
            delete_item: ".delete-item",
            add_item: ".add-slide",
            data_change: "#slides-list li input",
            select_url: ".select_url"
        },
        events: {
            "click @ui.delete_item": "deleteItem",
            "click @ui.add_item": "addItem",
            "change @ui.data_change": "updateSlideData",
            "click @ui.select_url": "selectUrl"
        },
        _onRender: function() {
            var t = this;
            this.$el.find("#slides-list ul").sortable({
                axis: "y",
                placeholder: "ui-state-highlight",
                forcePlaceholderSize: !0,
                update: function() {
                    t.updateSlideData()
                }
            }),
            McMore.uploadImageCallback = function(e) {
                $(".image-upload.selected img").attr("src", e.url),
                t.updateSlideData()
            }
        },
        updateSlideData: function() {
            var t = [];
            this.$el.find("#slides-list ul li").each(function() {
                var e = {
                    name: $(this).find(".slide-name input").val(),
                    src: $(this).find(".slide-img img").attr("src"),
                    link: $(this).find(".slide-link input").val()
                };
                t.push(e)
            }),
            this.model.set("data", t)
        },
        deleteItem: function(t) {
            var e = $(t.currentTarget),
            i = this;
            e.closest("li").slideUp(300,
            function() {
                $(this).remove(),
                i.updateSlideData()
            })
        },
        addItem: function() {
            $('<li class="slide clearfix"><div class="delete-item"><img src="/static/editPage/images/delete_item.png"/></div><div class="slide-img image-upload" data-name="slide"><img src="/static/editPage/images/slideshow/images/slideshow-1.png"/></div><div class="slide-name"><input class="form-control" type="text" name="slide_name" placeholder="图片alt属性" value=""/></div><div class="slide-link"><div class="input-group"><input class="form-control" type="text" placeholder="链接:http://www.example.com" value="" name="slide_link"><span class="input-group-addon select_url">浏览</span></div></div></li>').appendTo($("#slides-list ul")),
            this.updateSlideData()
        },
        selectUrl: function(t) {
            var e = $(t.currentTarget),
            i = this;
            McMore.selectUrlCallback = function(t) {
                var n = e.closest(".input-group").find("input");
                n.val(t),
                i.updateSlideData(),
                $.fancybox.close()
            },
            $.fancybox.open({
                href: alinks,
                type: "iframe",
                fitToView: !1,
                width: 850,
                padding: 0,
                height: "70%",
                autoSize: !1,
                closeClick: !1,
                closeBtn: !1
            })
        }
    }),
    i.Controller = t.Controller.extend({
        configView: i.ConfigView,
        previewView: i.PreviewView
    }),
    i
}),
define("component", ["require",  "components/extmenu/main", "components/exttypeset/main", "components/ads/main", "components/contact/main", "components/mainmenu/main", "components/navigator/main", "components/products/main", "components/search/main", "components/slideshow/main"],
function(t) {
    "use strict";
	 t("components/extmenu/main"),
    t("components/exttypeset/main"),
    t("components/ads/main"),
    t("components/contact/main"),
    t("components/mainmenu/main"),
    t("components/navigator/main"),
    t("components/products/main"),
    t("components/search/main"),
    t("components/slideshow/main")
}),
require.config({
    paths: {
        backbone: "../bower_components/backbone/backbone",
        "backbone.babysitter": "../bower_components/backbone.babysitter/lib/backbone.babysitter",
        "backbone.marionette": "../bower_components/backbone.marionette/lib/core/backbone.marionette",
        "backbone.wreqr": "../bower_components/backbone.wreqr/lib/backbone.wreqr",
        jquery: "../bower_components/jquery/dist/jquery",
        "jquery-ui": "../bower_components/jquery-ui/jquery-ui",
        "jquery-form": "../bower_components/jquery-form/jquery.form",
        mustache: "../bower_components/mustache/mustache",
        css: "../bower_components/require-css/css",
        text: "../bower_components/requirejs-text/text",
        underscore: "../bower_components/underscore/underscore",
        iCheck: "../bower_components/iCheck/icheck",
        fancybox: "../bower_components/fancybox/source/jquery.fancybox",
        owl_carousel: "../bower_components/owl.carousel/owl.carousel"
    },
    shim: {
        backbone: {
            deps: ["mustache"]
        }
    },
    waitSeconds: 15
}),
require(["application", "startup", "component"],
function(t) {
    t.start()
}),
define("main",
function() {});
//# sourceMappingURL=main.js
//# sourceMappingURL=main.js.map
