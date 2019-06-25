// pages/myorders/myorders.js
const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        imgUrl: getApp().globalData.imgUrl,
        lists: [],
        stat: [],
        level: 'all',
        pages: 1,
        isloaddata: true,
        search_content: '',
        hide_tab: 1, //是否隐藏层级数量 1 不隐藏；0 隐藏
    },


    //fans搜索
    searchfans: function(e) {
        const That = this
        const search_content = e.detail.value
        That.setData({
            search_content: search_content,
        })
    },

    /**
     * 点击搜索粉丝
     */
    clicksearch: function() {
        const That = this
        api.loading_msg()
        const search_content = That.data.search_content
        const _pages = 1
        const level = 'all'
        const hide_tab = search_content ? 0 : 1
        That.setData({
            level: level,
            isloaddata: true,
            lists: [],
            pages: 1,
            hide_tab: hide_tab //是否隐藏层级数量 1 不隐藏；0 隐藏
        })
        const _data = {
            is_stat: 0,
            user_id: search_content,
            level: level,
        }
        That.loadlist(_pages, _data)
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const That = this
        const _pages = That.data.pages
        const _data = {
            is_stat: 0,
            level: That.data.level,
        }
        api.loading_msg()
        That.loadlist(_pages, _data)
        That.oneloadlist() //首次加载层级数量
    },


    //第一次加载统计数量
    oneloadlist: function() {
        const That = this
        const _data = {
            is_stat: 1,
            level: 'all',
        }
        const _pages = 1
        api.pagelist(api.https_path + 'member/api._my_team/getlist', _pages, _data, function(err, res) {
            if (res.code == 1) {
                That.setData({
                    stat: res.stat,
                })
            }
        })
    },

    //点击切换层级
    tabchang: function(options) {
        const That = this
        api.loading_msg()
        const level = options.currentTarget.dataset.level
        const _pages = 1
        That.setData({
            level: level,
            isloaddata: true,
            lists: [],
            pages: _pages
        })
        const _data = {
            is_stat: 0,
            level: level,
        }
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
            api.pagelist(api.https_path + 'member/api._my_team/getlist', _pages, _data, function(err, res) {
                if (res.code == 1 && res.list) {
                    if (res.list.length > 0) {
                        for (var i = 0; i < res.list.length; i++) {
                            lists.push(res.list[i])
                        }
                        That.setData({
                            isloaddata: true,
                            lists: lists,
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

    //滚动加载数据
    scrolltloadlist: function() {
        const That = this
        const _pages = That.data.pages
        const isloaddata = That.data.isloaddata
        const _data = {
            is_stat: 0,
            level: That.data.level,
        }
        if (isloaddata == true) {
            That.loadlist(_pages, _data)
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
    // onShareAppMessage: function () {

    // }
})