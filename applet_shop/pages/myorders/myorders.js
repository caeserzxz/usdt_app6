// pages/myorders/myorders.js
const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        listscount: 0,
        active: 1,
        wulimodel: true,
        active1: true,
        lists: [],
        pages: 1,
        isloaddata: true,
        orderstauts: 'all',
    },

    wulimodel: function() {
        this.setData({
            wulimodel: false,
        })
    },


    wuliclose: function() {
        this.setData({
            wulimodel: true,
        })
    },

    dopayorder: function (options){
        const That = this
        const order_id = options.currentTarget.dataset.order_id
        const order_sn = options.currentTarget.dataset.order_sn
        wx.redirectTo({
            url: '/pages/payorder/payorder?order_id=' + order_id + '&order_sn=' + order_sn,
        })
    },

    /**
     * 取消订单【已支付状态】
     */
    cancelorder: function(options) {
        const That = this
        const order_id = options.currentTarget.dataset.order_id
        const order_sn = options.currentTarget.dataset.order_sn
        const orderstatus = options.orderstauts
        wx.showModal({
            title: '提示',
            content: '确定要取消订单吗?',
            success(res) {
                if (res.confirm) {
                    const _data = {
                        'order_id': order_id,
                        'type': 'cancel',
                    }
                    api.fetchPost(api.https_path + 'shop/api.order/action', _data, function(err, res) {
                        if (res.code == 1) {
                            const _pages = 1
                            That.setData({
                                isloaddata: true,
                                pages: _pages,
                                lists: [],
                                listscount: 0,
                                orderstauts: orderstatus,
                            })
                            if (orderstatus == 'all') {
                                const orderstatus = ""
                            }
                            const _datalist = {
                                'type': orderstatus
                            }
                            That.loadlist(_pages, _datalist)
                        } else {
                            api.error_msg(res.msg)
                        }
                    })
                } else if (res.cancel) {
                    //console.log('用户点击取消')
                }
            }
        })
    },

    /**
     * 确认收货
     */
    signorder: function(options) {
        const That = this
        const order_id = options.currentTarget.dataset.order_id
        const order_sn = options.currentTarget.dataset.order_sn
        const orderstatus = options.orderstauts
        const _data = {
            'order_id': order_id,
            'type': 'sign',
        }
        api.fetchPost(api.https_path + 'shop/api.order/action', _data, function(err, res) {
            if (res.code == 1) {
                const _pages = 1
                That.setData({
                    isloaddata: true,
                    pages: _pages,
                    lists: [],
                    listscount: 0,
                    orderstauts: orderstatus,
                })
                if (orderstatus == 'all') {
                    const orderstatus = ""
                }
                const _datalist = {
                    'type': orderstatus
                }
                That.loadlist(_pages, _datalist)
            } else {
                api.error_msg(res.msg)
            }
        })
    },

    /**
     * 删除订单
     */
    deleteorder: function(options) {
        const That = this
        const order_id = options.currentTarget.dataset.order_id
        const order_sn = options.currentTarget.dataset.order_sn
        const orderstatus = options.orderstauts
        wx.showModal({
            title: '提示',
            content: '确定要删除吗?',
            success(res) {
                if (res.confirm) {
                    const _data = {
                        'order_id': order_id,
                        'type': 'del',
                    }
                    api.fetchPost(api.https_path + 'shop/api.order/action', _data, function(err, res) {
                        if (res.code == 1) {
                            const _pages = 1
                            That.setData({
                                isloaddata: true,
                                pages: _pages,
                                lists: [],
                                listscount: 0,
                                orderstauts: orderstatus,
                            })
                            if (orderstatus == 'all') {
                                const orderstatus = ""
                            }
                            const _datalist = {
                                'type': orderstatus
                            }
                            That.loadlist(_pages, _datalist)
                        } else {
                            api.error_msg(res.msg)
                        }
                    })
                } else if (res.cancel) {
                    //console.log('用户点击取消')
                }
            }
        })
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        api.islogin()
        const That = this
        const _pages = 1
        const orderstatus = options.orderstauts
        That.setData({
            isloaddata: true,
            listscount: 0,
            lists: [],
            orderstauts: orderstatus,
            pages: _pages
        })
        api.loading_msg()
        if (orderstatus == 'all') {
            const orderstatus = ""
        }
        const _data = {
            'type': orderstatus
        }
        That.loadlist(_pages, _data)
    },

    /**
     * 点击切换状态
     */
    tabchang: function(e) {
        const orderstatus = e.currentTarget.dataset.status_value
        const That = this
        const _pages = 1
        That.setData({
            isloaddata: true,
            pages: _pages,
            lists: [],
            listscount: 0,
            orderstauts: orderstatus,
        })
        if (orderstatus == 'all') {
            const orderstatus = ""
        }
        const _data = {
            'type': orderstatus
        }
        api.loading_msg()
        That.loadlist(_pages, _data)
    },


    /**
     * 滚动加载数据
     */
    scrolltloadlist: function() {
        const That = this
        const _pages = That.data.pages
        const _data = {}
        That.loadlist(_pages, _data)
    },


    /**
     * 滚动加载数据
     */
    loadlist: function(_pages, _data) {
        const That = this
        const lists = That.data.lists
        const isloaddata = That.data.isloaddata
        if (isloaddata == true) {
            That.setData({
                isloaddata: false
            })
            api.pagelist(api.https_path + 'shop/api.order/getList', _pages, _data, function(err, res) {
                console.log(res.list)
                if (res.code == 1 && res.list) {
                    if (res.list.length > 0) {
                        for (var i = 0; i < res.list.length; i++) {
                            lists.push(res.list[i])
                        }
                        That.setData({
                            isloaddata: true,
                            lists: lists,
                            listscount: lists.length,
                            pages: parseInt(_pages) + 1
                        })
                    } else {
                        That.setData({
                            isloaddata: false,
                        })
                    }
                }
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
    // onShareAppMessage: function() {

    // }
})