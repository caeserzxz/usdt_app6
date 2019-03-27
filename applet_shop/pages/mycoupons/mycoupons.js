// pages/coupons/coupons.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    currentTab: 0,
    emptyData:true,
    coupons0: [
      {
        name: '新人专享券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '20',
      },
            {
        name: '新人专享券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '20',
      },
      {
        name: '新人专享券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '20',
      },
      {
        name: '新人专享券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '20',
      },
      {
        name: '店铺优惠券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '100',
      },
      {
        name: '新春好礼券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '90',
      }
    ],
    coupons1: [
      {
        name: '新人专享券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '20',
      },
      {
        name: '店铺优惠券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '100',
      },
      {
        name: '新春好礼券',
        stitle: '满0元可用',
        time: '2018.12.20-2019.01.20',
        money: '90',
      }
    ],
    coupons2: []
  },
  //点击切换
  clickTab: function (e) {
    this.setData({
      currentTab: e.currentTarget.id,
      emptyData: true
    })
    if (e.currentTarget.id==0){
      if (this.data.coupons0 == undefined || this.data.coupons0.length == 0){
        this.setData({
          emptyData:false
        })
      }
    } else if (e.currentTarget.id == 1){
      if (this.data.coupons1 == undefined || this.data.coupons1.length == 0) {
        this.setData({
          emptyData: false
        })
      }
    }else{
      if (this.data.coupons2 == undefined || this.data.coupons2.length == 0) {   
        this.setData({
          emptyData: false
        })
      }
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