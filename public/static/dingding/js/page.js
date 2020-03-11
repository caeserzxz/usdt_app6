(function(doc, win) {
  var docEl = doc.documentElement,
    resizeEvt = "orientationchange" in window ? "orientationchange" : "resize",
    recalc = function() {
      var clientWidth = docEl.clientWidth;
      if (clientWidth > 750) {
        clientWidth = 750;
      }
      if (!clientWidth) return;
      docEl.style.fontSize = 75 * (clientWidth / 375) + "px";
    };

  if (!doc.addEventListener) return;
  win.addEventListener(resizeEvt, recalc, false);
  doc.addEventListener("DOMContentLoaded", recalc, false);
  window.onload = function() {
    //键盘收起页面空白问题
    var oldScrollTop = getScrollTop() || 0;
    document.body.addEventListener("focusout", function() {
      //IOS软键盘关闭事件
      var ua = window.navigator.userAgent;
      if (ua.indexOf("iPhone") > 0 || ua.indexOf("iPad") > 0) {
        document.body.scrollTop = oldScrollTop;
        document.documentElement.scrollTop = oldScrollTop;
      }
    });
  };
})(document, window);
function getScrollTop() {
  var scrollTop = 0;
  if (document.documentElement && document.documentElement.scrollTop) {
    scrollTop = document.documentElement.scrollTop;
  } else if (document.body) {
    scrollTop = document.body.scrollTop;
  }
  return scrollTop;
}
