// pages/submitOrder2/submitOrder2.js
const app = getApp()
var util = require("../../utils/util.js")
var api = require("../../common/api.js")
var md5 = require("../../common/md5.js")
var sms = require("../../common/smscode.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
      pay_account:0.00,
      pay_code : 'miniAppPay'
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
      const _this = this 
      let order_id = options.order_id
      _this.setData({
        order_id: order_id
      })
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {
      var _this = this
      //初始化页面
      api.fetchPost(api.https_path + 'shop/api.order/getInfo', { order_id: _this.data.order_id }, function (err, res) {
        console.log(res)
        if(res.code == 1){
          _this.setData({
            pay_account: res.orderInfo.order_amount, 
          })
        }else{
          api.error_msg('系统繁忙!')
        }
      })
    },

    //选择支付方式
    payType:function(e){
      var _this = this
      _this.setData({
        pay_code: e.detail.value
      })
    },
    //开始支付
    payOrder:function(e){
      var _this = this

      console.log(e)
      let pay_code = _this.data.pay_code;
      let order_id = _this.data.order_id;
      console.log(pay_code);
      if (pay_code == 'cod'){  //测试支付
        api.fetchPost(api.https_path + 'test/api.test/test_pay', { id:order_id }, function (err, res) {
          if (res.code == 1) {
            api.success_msg('支付成功');
            setTimeout(function () {
              wx.redirectTo({
                url: '/pages/orderDetails/orderDetails?order_id=' + order_id,
              })
            }, 1500)

          } else {
            api.error_msg('系统繁忙，稍后再试！')
          }
        })
      }else{
        //微信支付
        api.fetchPost(api.https_path +'/shop/payment/getcode', { 'order_id': order_id, 'pay_code': pay_code }, (err, rs_data) => {
          console.log(rs_data);
          if (rs_data.code == 1) {


            var res_item = rs_data.data
            console.log(res_item.timeStamp)
            //小程序支付
            wx.requestPayment(
              {
                timeStamp: res_item.timeStamp,
                nonceStr: res_item.nonceStr,
                package: res_item.package,
                signType: 'MD5',
                paySign: res_item.paySign,
                success(res) {
                  console.log(res)
                  if (res.errMsg == "requestPayment:ok"){
                    api.success_msg('支付成功');
                    setTimeout(function () {
                      wx.redirectTo({
                        url: '/pages/orderDetails/orderDetails?order_id=' + order_id,
                      })
                    }, 1500)
                  }

                 },
                fail(res) { },
                'complete': function (res) {
                }
              })

            // if (rs_data.code == 1 ) {
            //   
            // }
          } else {
            api.error_msg('网络异常!',1500);
          }
        })


        // api.error_msg('please select other')
      }


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