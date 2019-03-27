// pages/myorders/myorders.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    wulimodel: true,
    active1: true,
    active2: false,
    active3: false,
    active4: false,
    active5: false,
    imgUrl: getApp().globalData.imgUrl
  },
  wulimodel: function () {
    this.setData({
      wulimodel: false,
    })
  },
  wuliclose: function () {
    this.setData({
      wulimodel: true,
    })
  },
  tabchang1: function () {
    this.setData({
      active1: true,
      active2: false,
      active3: false,
      active4: false,
      active5: false,
    })
  },
  tabchang2: function () {
    this.setData({
      active1: false,
      active2: true,
      active3: false,
      active4: false,
      active5: false,
    })
  },
  tabchang3: function () {
    this.setData({
      active1: false,
      active2: false,
      active3: true,
      active4: false,
      active5: false,
    })
  },
  tabchang4: function () {
    this.setData({
      active1: false,
      active2: false,
      active3: false,
      active4: true,
      active5: false,
    })
  },
  tabchang5: function () {
    this.setData({
      active1: false,
      active2: false,
      active3: false,
      active4: false,
      active5: true,
    })
  },
  cancelorder: function () {
    wx.showActionSheet({
      itemList: ['我不想买了', '信息填写错误，重新拍', '其他原因'],
      success(res) {
        console.log(res.tapIndex)
      },
      fail(res) {
        console.log(res.errMsg)
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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