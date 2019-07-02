// pages/login/login.js
const app = getApp()
var util = require("../../utils/util.js")
var api = require("../../common/api.js")
var md5 = require("../../common/md5.js")
var sms = require("../../common/smscode.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        eyes: false,
        label: true,
        label1: true,
        fly_out: false,
        fly_out1: false,
        code_title: "获取验证码",
        isloginsms: 0,
        user_account: '15625077763',
    },


    open_eyes() {
        this.setData({
            eyes: !this.data.eyes
        })
    },


    account: function(e) {
        var that = this
        var val = e.detail.value
        that.setData({
            user_account: val
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
        api.getconfig('sms_fun', function(err, data) {
            That.setData({
                isloginsms: data.login
            })
        })
    },

    /**
     * 发送验证码
     */
    sendcode: function() {
        const That = this
        const user_account = That.data.user_account
        if (user_account) {
            api.sendsms(user_account, 'login', function(err, res) {
                if (res.code == 1) {
                    if (sms.is_send_code == 1) {
                        sms.is_send_code = 0
                        sms.sendcode(That)
                    }
                }
            });
        } else {
            api.error_msg("请输入手机号码")
            return false
        }
    },

    /**
     * 账号登录
     */
    dologin: function(options) {
        const That = this
        const user_account = options.detail.value.user_account
        const user_passwrod = options.detail.value.user_passwrod
        const user_code = options.detail.value.user_code
        if (user_account == "") {
            api.error_msg("请输入手机号码")
            return false
        } else if (user_passwrod == "") {
            api.error_msg("请输入登录密码")
            return false
        } else {
            const loginurl = api.https_path + 'member/api.passport/login'
            const _data = {
                'mobile': user_account,
                'password': user_passwrod,
                'code': user_code,
                'source': 'developers',
            }
            api.fetchPostOther(loginurl, _data, function(err, res) {
                if (res.code == 0) {
                    api.error_msg(res.msg)
                    return false
                } else {
                    api.putcache('user_devtoken', res.developers)
                    wx.reLaunch({
                        url: '/pages/my/my',
                    })
                }
            });
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
    // onShareAppMessage: function() {

    // }
})