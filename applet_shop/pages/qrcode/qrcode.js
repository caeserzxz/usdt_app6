const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    imgs: [
      {
        id: '1',
        url: "mycode02.png",
        select: true
      },
      {
        id: '2',
        url: "mycode03.png",
        select: false
      },
      {
        id: '3',
        url: "mycode04.png",
        select: false
      }
    ]
  },
  changeImg(e) {
    let imgs = this.data.imgs;
    let id = e.currentTarget.id;

    for (let i = 0; i < imgs.length; i++) {
      if (imgs[i].id == id) {
        imgs[i].select = true;
      } else {
        imgs[i].select = false;
      }
    };

    this.setData({
      imgs: imgs
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