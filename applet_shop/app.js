//app.js
App({
    onLaunch: function() {
        // 展示本地存储能力
        var logs = wx.getStorageSync('logs') || []
        logs.unshift(Date.now())
        wx.setStorageSync('logs', logs)

        // 登录
        wx.login({
            success: res => {
                // 发送 res.code 到后台换取 openId, sessionKey, unionId
            }
        })
        // 获取用户信息
        wx.getSetting({
            success: res => {
                if (res.authSetting['scope.userInfo']) {}
            }
        })
    },
    globalData: {
        userInfo: null,
        imgUrl: 'https://deking008.gitee.io/zpimg/wximg/'
    },
    requirejs: function (e) {
      return require("utils/" + e + ".js");
    }
})