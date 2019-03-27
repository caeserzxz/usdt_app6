// pages/coupons/coupons.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    coupons: [
      {
        id: '1',
        name: '新人专享券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '20',
        select: false
      },
      {
        id: '2',
        name: '店铺优惠券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '100',
        select: true
      },
      {
        id: '3',
        name: '新春好礼券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '90',
        select: false
      }
    ]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  selectCoupon: function(e){
   
    let id = e.currentTarget.id;
    let coupons = this.data.coupons;
    for(let i=0; i<coupons.length; i++){
      if (coupons[i].id == id){
        coupons[i].select = true;
      } else{
        coupons[i].select = false;
      }
    }
    this.setData({
      coupons: coupons
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