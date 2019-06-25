// pages/personalData/personalData.js
const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        sexArr: ['男', '女'],
        sexindex: 0,
        birthday: '',
        nick_name: '',
        mobile: '',
        headimgurl: '',
        user_id: '',
        superior: [],
    },

    //获取用户资料
    getmemberinfo: function() {
        const That = this
        api.fetchPost(api.https_path + '/member/api.users/getInfo', {}, function(err, res) {
            if (res.code == 1) {
                That.setData({
                    user_id: res.info.user_id,
                    headimgurl: res.info.headimgurl,
                    sexindex: res.info.sex,
                    birthday: res.info.birthday,
                    mobile: res.info.mobile,
                    nick_name: res.info.nick_name,
                    // superior: res.superior,
                })
            } else {
                api.error_msg(res.msg)
            }
        })
    },

    /**
     * 获取用户性别
     */
    selectsex: function(e) {
        const That = this
        That.setData({
            sexindex: e.detail.value
        })
    },
    /**
     * 获取用户手机号
     */
    mobile: function(e) {
        const That = this
        That.setData({
            mobile: e.detail.value
        })
    },

    /**
     * 获取用户输入昵称
     */
    nickname: function(e) {
        const That = this
        That.setData({
            nick_name: e.detail.value
        })
    },

    /**
     * 获取生日日期
     */
    bindDateChange(e) {
        const That = this
        That.setData({
            birthday: e.detail.value
        })
    },

    /**
     * 修改会员信息
     */
    updateuser: function() {
        const That = this
        console.log(That.data)
        api.fetchPost(api.https_path + '/member/api.users/editInfo', {
            sex: That.data.sexindex,
            mobile: That.data.mobile,
            birthday: That.data.birthday,
            nick_name: That.data.nick_name,
            headimgurl: That.data.headimgurl,
        }, function(err, res) {
            if (res.code == 1) {
                // That.getmemberinfo() //修改成功，重新加载数据
                api.error_msg(res.msg)
            } else {
                api.error_msg(res.msg)
            }
        })
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const That = this
        api.islogin() //判断是否已经登录，没有登录跳转去登录
        That.getmemberinfo()
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