// pages/register/register.js
const app = getApp()
var api = require("../../common/api.js")
var sms = require("../../common/smscode.js") //倒计时
Page({


    /**
     * 发送验证码
     */
    sendcode: function () {
      const That = this
      const user_account = That.data.account
      if (user_account) {
        const _data = {
          'mobile': user_account,
          'type': 'register',
        }
        api.fetchPost(api.https_path + '/publics/api.sms/sendCode', _data, function (err, res) {
          if (res.code == 1) {
            if (sms.is_send_code == 1) {
              sms.is_send_code = 0
              sms.sendcode(That)
            }
          }else{
            api.error_msg(res.msg)
          }
        });
      } else {
        api.error_msg("请输入手机号码")
        return false
      }
    },

    /**
     * 页面的初始数据
     */
    data: {
        code_title: "获取验证码",
        eyes: false,
        label: true,
        label1: true,
        fly_out: false,
        fly_out1: false,
        isagree: 1,
        register_sms:0,
    },


    open_eyes() {
        this.setData({
            eyes: !this.data.eyes
        })
    },


    account: function(e) {
        var that = this
        var val = e.detail.value;
        that.setData({
            account: val
        });
    },


    focus() {
        this.setData({
            fly_out: true,
        })
    },


    blur() {
        if (this.data.account === '') {
            this.setData({
                fly_out: false
            })
        }
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
      const That = this
      api.getconfig('sms_fun', function (err, data) {
        That.setData({
          register_sms: data.register
        })
      })
    },

    doregister: function(options) {
        const That = this
        const register_sms = That.data.register_sms
        const user_account = options.detail.value.user_account
        const user_passwrod = options.detail.value.user_password
        const user_code = options.detail.value.user_code
        const user_name = options.detail.value.user_name
        if (user_account == "") {
            api.error_msg("请输入登录账号")
            return false
        } else if (user_code == "" && register_sms == 1) {
            api.error_msg("请输入验证码")
            return false
        } else if (user_name == "") {
            api.error_msg("请输入昵称")
            return false
        } else if (user_passwrod == "") {
            api.error_msg("请输入登录密码")
            return false
        } else {
            const loginurl = api.https_path + 'member/api.passport/register'
            const _data = {
                'mobile': user_account,
                'code': user_code,
                'name': user_name,
                'password': user_passwrod,
            }
            api.fetchPost(loginurl, _data, function(err, res) {
                console.log(res)
                if (res.code == 0) {
                    api.error_msg(res.msg)
                    return false
                } else {
                    wx.redirectTo({
                        url: '/pages/login/login'
                    })
                    return true
                }
            });
        }
    },


    /**
     * 沟选是否同意协议
     */
    checkboxChange: function(options) {
        const That = this
        if (options.detail.value[0]) {
            That.setData({
                isagree: 1
            })
        } else {
            That.setData({
                isagree: 0
            })
        }
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    }
})