const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    leftData: [
      {
        id: '1',
        name: "推荐",
        select: false
      },
      {
        id: '2',
        name: "箱包",
        select: true
      },
      {
        id: '3',
        name: "服装",
        select: false
      },
      {
        id: '4',
        name: "鞋子",
        select: false
      }
    ]
  },
  changeMenu(e) {
    let leftData = this.data.leftData;
    let id = e.currentTarget.id;

    for(let i=0; i<leftData.length; i++){
      if(leftData[i].id == id){
        leftData[i].select = true;
      } else {
        leftData[i].select = false;
      }
    };

    this.setData({
      leftData: leftData
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