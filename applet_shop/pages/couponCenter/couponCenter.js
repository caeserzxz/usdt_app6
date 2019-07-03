// pages/coupons/coupons.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    currentTab: 0,
    emptyData: true,
    list: [], //列表
    load_more: true,
    page: 0, //页数
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let _this = this;
    wx.showLoading({
      title: '加载数据中...',
    })
    _this.couponslist();
  },



  //优惠券列表
  couponslist: function () {
    let _this = this;
    api.fetchPost(api.https_path + '/shop/api.Bonus/getFreeList', { p: _this.data.page + 1 }, function (err, res) {
      // console.log(res)
      wx.hideLoading();
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else {
        _this.setData({
          list: res.data, //列表
        })
      }
    });
  },

  receivecoupon: function (e) {
   const _this = this

    let type_id = e.currentTarget.id;
    
    api.fetchPost(api.https_path + '/shop/api.Bonus/receiveFree', { id: type_id }, function (err, res) {
      console.log(res)
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else {
        api.success_msg(res.msg,1500);
      }
    });

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