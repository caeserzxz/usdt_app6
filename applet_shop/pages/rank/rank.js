// pages/rank/rank.js
const app = getApp()
var api = require("../../common/api.js")
var sliderWidth = 16; // 需要设置slider的宽度，用于计算中间位置
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: getApp().globalData.imgUrl,
    dateTime:'2019.03.15',
    tabs: ["佣金", "团队新增", "直推新增",'团队总人数'],
    activeIndex: 0,
    sliderOffset: 0,
    sliderLeft: 0,
    commissionList:[{
      rankimg: '/images/rank02.png', userimg: '/images/df_tx.png', userNmae:'狂风扫落叶',money:'12331.00'
    }, {
        rankimg: '/images/rank03.png', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '/images/rank04.png', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }, {
        rankimg: '', userimg: '/images/df_tx.png', userNmae: '狂风扫落叶', money: '12331.00'
      }]
  },
  bindDateChange: function (e) {
    let arr = e.detail.value.split('-')
    let date = arr[0] + '.' + arr[1] + '.'+arr[2]
    this.setData({
      dateTime: date
    })
  },
  tabClick: function (e) {
    this.setData({
      sliderOffset: e.currentTarget.offsetLeft,
      activeIndex: e.currentTarget.id
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          sliderLeft: (res.windowWidth / that.data.tabs.length - sliderWidth) / 2,
          sliderOffset: res.windowWidth / that.data.tabs.length * that.data.activeIndex
        });
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