// pages/register/register.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    eyes: false,
    label: true,
    label1: true,
    fly_out: false,
    fly_out1: false
  },
  open_eyes() {
    this.setData({
      eyes: !this.data.eyes
    })
  },
  account: function (e) {
    var that = this
    var val = e.detail.value;
    that.setData({
      account: val
    });
  },
  focus() {
    this.setData({
      fly_out: true,
    })
  },
  blur() {
    console.log(this.data.account)
    if (this.data.account === '') {
      this.setData({
        fly_out: false
      })
    }
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