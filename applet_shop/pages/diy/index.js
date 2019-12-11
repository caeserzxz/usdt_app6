var e = getApp(), a = (e.requirejs("icons"), e.requirejs("core"));

e.requirejs("base64"), e.requirejs("wxParse/wxParse");
function t(t, a, e) {
  return a in t ? Object.defineProperty(t, a, {
    value: e,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : t[a] = e, t;
}

Page({
    data: {
        route: "home",
        indicatorDots: !0,
        autoplay: !0,
        interval: 5e3,
        duration: 500,
        circular: !0,
        hotimg: "/static/images/hotdot.jpg",
        saleout1: "/static/images/saleout-1.png",
        saleout2: "/static/images/saleout-2.png",
        saleout3: "/static/images/saleout-3.png",
        icons: e.requirejs("icons"),
        diypage: ""
    },
    onReady: function() {},
    onShow: function() {
        var e = this;
       
    },
    onHide: function() {},
    onUnload: function() {}
});