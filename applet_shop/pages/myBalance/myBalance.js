// pages/myBalance/myBalance.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgUrl: getApp().globalData.imgUrl,
    today_income: 0.00,//今日收益
    month_income: 0.00,//本月收益
    withdraw_status: 0.00,//是否开启提现
    frozen_amount: 0.00,//冻结金额
    balance_money: 0.00,//佣金
    end_income: 0.00,//累计收益
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const _this = this
    api.islogin()
    _this.loadUserInfo()
  },

  loadUserInfo: function () {
    const _this = this;
    api.fetchPost(api.https_path + "member/api.users/getAccount", {}, function (err, res) {
      console.log(res)
      if (res.code == 1) {
        _this.setData({
          today_income: res.today_income,//今日收益
          month_income: res.month_income,//本月收益
          withdraw_status: res.withdraw_status,//是否开启提现
          frozen_amount: res.frozen_amount,//冻结金额
          balance_money: res.account.balance_money,//余额
          end_income: res.end_income
        })
      } else {
        api.err_msg('系统繁忙');
        setTimeout(function () {
          wx.switchTab({
            url: '/pages/my/my',
          })
        }, 1500)
      }
    })
  },
  gowithdraw: function () {
    const _this = this
    let withdraw_status = _this.data.withdraw_status;
    if (withdraw_status == 1) {
      wx.navigateTo({
        url: '/pages/withdrawCash/withdrawCash?balance_money=' + _this.data.balance_money,
      })
    } else {
      api.err_msg('提现功能已关闭', 2000)
    }
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