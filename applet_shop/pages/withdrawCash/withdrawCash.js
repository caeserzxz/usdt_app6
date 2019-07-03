// pages/topup/topup.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    paytype: {},
    imgUrl: getApp().globalData.imgUrl,
    balance_money: 0,//可提现金额
    withdraw_fee: 0,//手续费
    list: [], //银行卡列表
    code_bank: [], //银行信息
    imgurl: api.https_path,
    account_id: 0,//选中账号卡id
    amount: 0,//提现金额
    fee:0,//手续费比率
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const _this = this
    let balance_money = options.balance_money
    _this.setData({
      balance_money: balance_money
    })
    api.islogin() //判断是否已经登录，没有登录跳转去登录
    _this.loadlist()
    api.getconfig('', function (err, data) {
      // console.log(data)
      _this.setData({
        fee: data.withdraw_fee
      })
    })
  },

  //加载提现方式
  loadlist: function () {
    const _this = this
    wx.showLoading({
      title: '数据加载中',
    })
    setTimeout(function () {
      wx.hideLoading()
    }, 1500)
    api.fetchPost(api.https_path + '/member/api.withdraw/getlist', {}, function (err, res) {
      // console.log(res)
      if (res.code == 1) {
        _this.setData({
          list: res.list
        })

      }
    })
    api.fetchPost(api.https_path + '/publics/api.index/get_bank', {}, function (err, res) {

      if (res.code == 1) {
        _this.setData({
          code_bank: res.code_bank
        })

      }
    })
  },

  //输入提现金额
  onChangeinput: function (e) {
    const _this = this
    let amount = e.detail.value
    if (amount > 0) {
      api.fetchPost(api.https_path + '/member/api.withdraw/checkwithdraw', { amount: amount }, function (err, res) {
        if (res.code == 1) {
          _this.setData({
            withdraw_fee: res.withdraw_fee,
            amount: amount
          })

        } else {
          api.error_msg(res.msg)
        }
      })
    } else {
      _this.setData({
        withdraw_fee: 0
      })
    }
  },

  radioChange: function (e) {
    // console.log(e.detail.value)
    const _this = this
    let account_id = e.detail.value
    _this.setData({
      account_id: account_id
    })

  },


  dowithdraw: function () {
    const _this = this
    let account_id = _this.data.account_id;
    let amount = _this.data.amount
    if (amount == 0) {
      api.error_msg('请输入提现金额');
    } else if (account_id == 0) {
      api.error_msg('请选择提现银行');
    } else {

      api.fetchPost(api.https_path + '/member/api.withdraw/postWithdraw', { amount: amount, account_id: account_id }, function (err, res) {
        if (res.code == 1) {
          api.success_msg(res.msg,1500)
          setTimeout(function(){
            wx.redirectTo({
              url: '/pages/myBalance/myBalance',
            })
          },1500)
        } else {
          api.error_msg(res.msg,1000)
        }
      })


    }



  },

  //全部提现
  allmoney: function () {
    const _this = this
    let balance_money = _this.data.balance_money
    let fee = _this.data.fee;
    let withdraw_fee = balance_money / 100 * fee;
    let amount = parseInt(Number(balance_money) - Number(withdraw_fee.toFixed(2)));
    api.error_msg('最多提现' + amount+'元')
    if (amount > 0) {
      api.fetchPost(api.https_path + '/member/api.withdraw/checkwithdraw', { amount: amount }, function (err, res) {
        if (res.code == 1) {
          _this.setData({
            withdraw_fee:res.withdraw_fee,
            amount: amount
          })

        } else {
          api.error_msg(res.msg)
        }
      })
    } else {
      _this.setData({
        withdraw_fee: 0
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