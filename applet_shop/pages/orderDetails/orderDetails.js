// pages/orderDetails/orderDetails.js
const app = getApp()
var api = require("../../common/api.js")
var func = require("../../common/func.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        imgUrl: getApp().globalData.imgUrl,
        order_id: 0,
        isCancel: 0,
        isDel: 0,
        isPay: 0,
        isRefund: 0,
        isReview: 0,
        isSign: 0,
        sign_time: 0,
        pay_time: 0,
        shipping_time: 0,
        orderinfo: []
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        var That = this
        var order_id = options.order_id
        wx.showLoading({
            title: "加载中",
        })
        That.setData({
            order_id: order_id
        })
    },


    loadinfo: function(order_id) {
        var That = this
        api.fetchPost(api.https_path + 'shop/api.order/getInfo', {
            order_id: order_id
        }, function(err, res) {
            if (res.code == 1) {
                console.log(res.orderInfo)
                wx.hideLoading()
                That.setData({
                    isCancel: res.orderInfo.isCancel,
                    isDel: res.orderInfo.isDel,
                    isPay: res.orderInfo.isPay,
                    isRefund: res.orderInfo.isRefund,
                    isReview: res.orderInfo.isReview,
                    isSign: res.orderInfo.isSign,
                    sign_time: func.date('Y-m-d H:i:s', res.orderInfo.sign_time),
                    pay_time: func.date('Y-m-d H:i:s', res.orderInfo.pay_time),
                    shipping_time: func.date('Y-m-d H:i:s', res.orderInfo.shipping_time),
                    orderinfo: res.orderInfo,
                    order_id: order_id
                })
            } else {
                api.error_msg(res.msg)
            }
        })
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
        const That = this
        const order_id = That.data.order_id
        That.loadinfo(order_id)
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