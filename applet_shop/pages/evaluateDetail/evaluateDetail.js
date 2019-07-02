const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const that = this
    api.islogin();
    if (options.rec_id>0) {
      that.setData({ rec_id: options.rec_id })
      that.comment(that);
    }
  },

  //获取评论详情
  comment: function (that) {
    const _data = {
      rec_id: that.data.rec_id,
    }
    api.fetchPost(api.https_path + 'shop/api.comment/getInfo', _data, function (err, res) {
      console.log(res)
      if (res.data) {
        that.setData({
          path: api.https_path,
          data: res.data
        })
      }else{
        that.setData({
          data: []
        }) 
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