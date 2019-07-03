// pages/addBank/addBank.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    array: ['中国工商银行', '中国建设银行'],
    index: 0,
    bank_arr: [], //银行列表
  },
  bindPickerChange(e) {
    this.setData({
      index: e.detail.value
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const _this = this
    api.fetchPost(api.https_path + '/publics/api.index/get_bank', {}, function (err, res) {
      console.log(res)
      if (res.code == 1) {
        _this.setData({
          bank_arr: res.bank
        })
      }
    })


  },

  bindPickerChange: function (e) {
    const _this = this;
    console.log('picker发送选择改变，索引为', e.detail.value);
    let code = _this.data.bank_arr[e.detail.value].code
    _this.setData({ //给变量赋值
      code: code,
      index: e.detail.value,
    })
  },


  //绑定银行卡
  formSubmit: function (e) {
    const _this = this;
    console.log('form发生了submit事件，携带数据为：', e.detail.value)

    let bank_branch_name = e.detail.value.bank_branch_name; //支行名称
    let bank_card_number = e.detail.value.bank_card_number; //卡号
    let bank_cardholder = e.detail.value.bank_cardholder; //持卡人
    let bank_cardholder_phone = e.detail.value.bank_cardholder_phone; //电话
    let bank_location_outlet = e.detail.value.bank_location_outlet; //网点所在地
    let bank_name = _this.data.bank_arr[_this.data.index].name; //银行名称
    let bank_code = _this.data.bank_arr[_this.data.index].code; //银行编码

    console.log(bank_code);
    if (bank_branch_name == '' || bank_card_number == '' || bank_cardholder == '' || bank_cardholder_phone == '' || bank_location_outlet == '') {
      api.error_msg('请填写完整!')
    } else {
      let data = {
        bank_branch_name: bank_branch_name, //支行名称
        bank_card_number: bank_card_number, //卡号
        bank_cardholder: bank_cardholder, //持卡人
        bank_cardholder_phone: bank_cardholder_phone, //电话
        bank_location_outlet: bank_location_outlet, //网点所在地
        bank_name: bank_name, //银行名称
        bank_code: bank_code //银行编码
      }
      console.log(data);
      api.fetchPost(api.https_path + '/member/api.withdraw/addBank', data, function (err, res) {
        // console.log(res)
        if (res.code == 1) {
          api.success_msg('添加成功', 1000)
          setTimeout(function () {
            wx.redirectTo({
              url: '/pages/withdrawManage/withdrawManage',
            })
          }, 1500)

        } else {
          api.error_msg(res.msg)
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