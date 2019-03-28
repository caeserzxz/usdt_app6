const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        bestlsit: [], //推荐
        rowslist: [], //左边
        childlist: [], //右边
        categoryselect: 0,
    },
    changeMenu(e) {
        const That = this
        const cagegoryid = e.currentTarget.id
        That.setData({
            categoryselect: cagegoryid
        });
    },
    goSearch() {
        wx.navigateTo({
            url: '/pages/search/search',
        })
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const That = this
        That.loadcategory()
    },


    loadcategory: function() {
        const That = this
        api.fetchPost(api.https_path + "shop/api.category/getlist", {}, function(err, res) {
            That.setData({
                bestlsit: res.allSort.best,
                bestlsitcount: res.allSort.best.length,
                rowslist: res.allSort.rows,
                childlist: res.classList,
            })
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
        const That = this
        That.loadcategory()
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