const app = getApp()
var api = require("../../common/api.js")
var md5 = require("../../common/md5.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    user_avatar: '',
    defultimg: '/images/z-zhead.png',
    goods_id: 0,
    share_token: '', //上级token

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var _this = this
    let goods_id;
    let share_token;
    if (options.scene) {  //扫分享码
      var scene = decodeURIComponent(options.scene);

      share_token = scene.split("$")[0];
      _this.setData({
        share_token: share_token,
      })

    } else {
      goods_id = options.goods_id != undefined ? options.goods_id : 0;
      if (options.goods_id != undefined) {
        _this.setData({
          goods_id: goods_id,
          share_token: options.share_token
        })
      }
      share_token = options.share_token != undefined ? options.share_token : 0;
      if (options.share_token != undefined) {
        _this.setData({
          share_token: share_token
        })
      }
    }

    wx.getSetting({
      success(res) {
        if (res.authSetting['scope.userInfo']) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称
          _this.dologin(goods_id, share_token);
        }
      }
    })

  },

  userInfoHandler: function (e) {
    var _this = this
    if (e.detail.errMsg == 'getUserInfo:ok') {
      let goods_id = _this.data.goods_id;
      let share_token = _this.data.share_token
      _this.dologin(goods_id, share_token); //授权登录
    }
  },

  dologin: function (goods_id, share_token) {
    const _this = this
    wx.getUserInfo({
      success: res => {
        var userInfo = res.userInfo
        var nickName = userInfo.nickName
        var avatarUrl = userInfo.avatarUrl
        var gender = userInfo.gender //性别 0：未知、1：男、2：女
        var province = userInfo.province
        var city = userInfo.city
        var country = userInfo.country
        wx.setStorage({
          key: 'wechat_user_info',
          data: {
            user_avatar: avatarUrl,
            user_name: nickName
          },
        })
        _this.setData({
          user_avatar: avatarUrl
        })
        console.log(api.https_path);
        wx.login({
          success: function (res) {
            if (res.code) {
              let loginurl = api.https_path + '/weixin/api.Miniprogram/do_login'
              let _data = {
                nickName: nickName,
                avatarUrl: avatarUrl,
                gender: gender,
                province: province,
                city: city,
                code: res.code,
                source: 'developers'
              }

              if (share_token) {
                _data.share_token = share_token
              }
              // console.log(_data);
              api.fetchPost(loginurl, _data, function (err, res) {

                console.log(res)
                if (res.code == 0) {
                  api.error_msg(res.msg)
                  return false
                } else {
                  wx.showToast({
                    title: '登录成功',
                    icon: 'success',
                    duration: 2000
                  })
                  api.putcache('user_devtoken', res.developers)
                  setTimeout(function () {
                    if (goods_id > 0) {
                      wx.redirectTo({
                        url: '/pages/productDetails/productDetails?goods_id='+goods_id,
                      })
                    }else{
                      wx.reLaunch({
                        url: '/pages/index/index',
                      })
                    }
                  }, 1000)
                }

              })

            } else {
              console.log('登录失败！' + res.errMsg)
            }
          }
        });
      }
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})