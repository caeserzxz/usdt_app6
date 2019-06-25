// pages/myorders/myorders.js
const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        imgurl: api.https_path,
        pages: 1,
        isloaddata: true,
        list: [],
        nodata: '', //我也是有底线的
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const That = this
        const _data = {
            pages: 1
        }
        That.setData({
            pages: 1,
            isloaddata: true,
            list: [],
        })
        That.datainfo(_data)
    },

    datainfo: function(_data) {
        const That = this
        const pages = That.data.pages
        const isloaddata = That.data.isloaddata
        if (isloaddata == true) {
            That.setData({
                nodata: '正在努力加载中...', //我也是有底线的
                isloaddata: false,
            })
            api.fetchPost(api.https_path + '/shop/api.order/saleafterlist', _data, function(err, res) {
                console.log(res)
                if (res.code == 1) {
                    const list = That.data.list
                    //console.log(res.list)
                    if (res.list.length > 0) {
                        for (var i = 0; i < res.list.length; i++) {
                            list.push(res.list[i])
                        }
                        That.setData({
                            isloaddata: true,
                            list: list,
                            nodata: '', //我也是有底线的
                            pages: parseInt(pages) + 1
                        })
                    } else {
                        That.setData({
                            nodata: '我也是有底线的', //我也是有底线的
                            isloaddata: false,
                        })
                    }
                } else {
                    That.setData({
                        nodata: '网络繁忙，请稍后重试', //我也是有底线的
                        isloaddata: false,
                    })
                }
            })
        }

    },

    scrolltloadlist: function() {
        const That = this
        const _data = {
            pages: That.data.pages
        }
        That.datainfo(_data)
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