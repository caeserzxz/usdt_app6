const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isCollect: false,
    indicatorDots: true,
    autoplay: true,
    interval: 5000,
    duration: 1000,
    imgBase: app.globalData.imgUrl,
    imgUrls: [
      'goods01.png',
      'goods01.png',
      'goods01.png'
    ],
    guigeModel: false,
    goodsnumber: 1, //购买数量
    goodsColor: ['中国红', '藏青色', '铁锈红', '宝蓝色'],
    goodsitem: '中国红',
    goodsSize: ['S码', 'M码', 'L码', 'XL码'],
    sizeitem: 'S码',
    modelShare: false
  },
  openGG() {
    this.setData({
      guigeModel:　true
    });
  },
  closeGG() {
    this.setData({
      guigeModel: false
    });
  },
  collect() {
     this.setData({
       isCollect: !this.data.isCollect
     });
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