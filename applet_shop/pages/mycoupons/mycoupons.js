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
    unusedlist: [], //未使用列表
    usedlist: [], //已使用列表
    expiredlist: [], //过期列表
    load_more: true,
    page: 0, //页数
    expiredNum: 0,
    unusedNum: 0,

    usedNum:0,
  },



  //点击切换
  clickTab: function(e) {
    const _this = this
    _this.setData({
      currentTab: e.currentTarget.id,
      emptyData: true
    })
    if (e.currentTarget.id == 0) {
      if (_this.data.unusedlist == undefined || _this.data.unusedlist.length == 0) {
        _this.setData({
          emptyData: false
        })
      }
    } else if (e.currentTarget.id == 1) {
      if (_this.data.usedlist == undefined || _this.data.usedlist.length == 0) {
        _this.setData({
          emptyData: false
        })
      }
    } else {
      if (_this.data.expiredlist == undefined || _this.data.expiredlist.length == 0) {
        _this.setData({
          emptyData: false
        })
      }
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let _this = this;
    wx.showLoading({
      title: '加载数据中...',
    })
    _this.couponslist();
  },



  //优惠券列表
  couponslist: function() {
    let _this = this;
    api.fetchPost(api.https_path + '/shop/api.Bonus/getList', {p: _this.data.page + 1}, function(err, res) {
      // console.log(res)
      wx.hideLoading();
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else {
        _this.setData({
          unusedlist: res.data.unused, //未使用列表
          usedlist: res.data.used, //已使用列表
          expiredlist: res.data.expired, //过期列表
          expiredNum:res.data.expiredNum,
          unusedNum: res.data.unusedNum,
          usedNum: res.data.usedNum,
        })
      }
    });
  },

  goshop:function(){
    wx.switchTab({
      url: '/pages/index/index',
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