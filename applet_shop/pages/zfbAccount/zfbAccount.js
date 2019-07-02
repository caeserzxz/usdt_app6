// pages/zfbAccount/zfbAccount.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  //绑定支付宝
  formSubmit: function (e) {
    const _this = this;

    console.log(e)
    let alipay_user_name = e.detail.value.alipay_user_name; //支付宝名称
    let alipay_account = e.detail.value.alipay_account; //支付宝账号



    if (alipay_user_name == '' || alipay_account == '') {
      api.error_msg('请填写完整!')
    } else {
      let data = {
        alipay_user_name: alipay_user_name, //支付宝名称
        alipay_account: alipay_account, //支付宝账号

      }
      api.fetchPost(api.https_path + '/member/api.withdraw/addAlipay', data, function (err, res) {

        if (res.code == 1) {
          api.success_msg('添加成功', 1000)
          setTimeout(function () {
            wx.redirectTo({
              url: '/pages/withdrawManage/withdrawManage',
            })
          }, 1500)

        } else {
          api.error_msg('系统繁忙，稍后再试')
        }
      })
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