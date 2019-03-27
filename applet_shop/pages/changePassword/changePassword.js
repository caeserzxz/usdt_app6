// pages/changePassword/changePassword.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    time:0,
    onlyTime:30000,
    timeText:'获取验证',
    btnType:false
  },
  getCode:function(e){
    if (this.data.btnType){
      return
    }
    this.setData({
      time: this.data.onlyTime,
      btnType: true
    })
    this.countdown()

  },
  // 倒计时
  countdown: function () {
    let that = this;
    let total_micro_second
    let timeList = that.data.time;
    let countDownTime;
    total_micro_second = timeList
      if (total_micro_second <= 0) {
        countDownTime = '重新获取'
        that.setData({
          btnType: false,
          timeText: countDownTime
        });
        return
      } else {
        countDownTime = that.dateformat(total_micro_second)//显示的时间
        total_micro_second -= 1000;//剩余的毫秒数
        that.setData({
          time: total_micro_second,
          timeText: countDownTime
        });
      }
    setTimeout(function () {
      that.countdown();
    }, 1000)
  },
  // 时间格式化输出，如11天03小时25分钟19秒  每1s都会调用一次
  dateformat: function (micro_second) {
    // 总秒数
    var second = Math.floor(micro_second / 1000);
    // 天数
    var day = Math.floor(second / 3600 / 24);
    // 小时
    var hr = Math.floor(second / 3600 % 24);
    // 分钟
    var min = Math.floor(second / 60 % 60);
    // 秒
    var sec = Math.floor(second % 60);
    return sec+'s后再获取';
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