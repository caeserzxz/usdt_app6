// pages/saleAfter/saleAfter.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgUrl: getApp().globalData.imgUrl,
    value1: '',
    value2: '',
    active1: '',
    active2: '',
    type1: 1,
    numVal:1
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  inputChange1(e) {
    console.log(e)
    if (e.detail.value) {
      this.setData({
        active1: true
      })
    } else {
      this.setData({
        active1: false
      })
    }
  },
  inputChange2(e) {
    console.log(e)
    if (e.detail.value) {
      this.setData({
        active2: true
      })
    } else {
      this.setData({
        active2: false
      })
    }
  },
  minus:function(e){
    let val=this.data.numVal
    if (val<=1){
       return
    }
    val = val - 1
    this.setData({
      numVal:val
    })
  },
  add: function (e) {
    console.log(0)
    let val = this.data.numVal
    val=val+1
    this.setData({
      numVal: val
    })
  },
  /**
 * 选择服务类型
 */
  onType(e) {

    this.setData({
      type1: e.currentTarget.dataset.index
    })
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