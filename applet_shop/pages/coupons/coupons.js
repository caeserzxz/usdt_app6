// pages/coupons/coupons.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    coupons: [],
    bonus_id: 0,
    rec_id: 0,
    payTotal: 0, //订单总金额（未使用优惠卷）
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    const _this = this

    let rec_id = options.rec_id != undefined ? options.rec_id : 0;
    if (options.rec_id != undefined) {
      _this.setData({
        rec_id: rec_id
      })
    }
    let payTotal = options.payTotal != undefined ? options.payTotal : 0;
    if (options.payTotal != undefined) {
      _this.setData({
        payTotal: payTotal
      })
    }
    
    let bonus_id = options.bonus_id != undefined ? options.bonus_id : 0;
    if (options.bonus_id != undefined) {
      _this.setData({
        bonus_id: bonus_id
      })
    }
    _this.getBonusList();
  },


  //获取优惠券列表
  getBonusList: function() {
    const _this = this;
    let data = {};
    api.fetchPost(api.https_path + 'shop/api.Bonus/getList', data, function(err, res) {
      // console.log(res)
      if (res != null) {
        if (res.code == 0) {
          api.error_msg(res.msg)
          return false;
        }
        let bounsData = res.data;
        _this.setData({
          coupons: bounsData.unused,
        })
        // console.log(bounsData.unused);
      }
    })
  },


  //选择优惠卷
  selectCoupon: function(e) {
    const _this = this
    let bonus_id = e.currentTarget.id;
    let payTotal = parseInt(_this.data.payTotal);
    let bonus = _this.data.coupons[bonus_id]
    let timestamp = (new Date()).getTime()
    let new_time = ~~(timestamp / 1000)
    let min_amount = parseInt(bonus.bonus.min_amount)
    // console.log(new_time)
    // console.log(bonus.bonus) ;

    if (bonus.order_id > 0) {
      api.error_msg('该优惠卷已使用', 1000)
    } else {
      if (payTotal >= min_amount) {
        console.log(1)
        return; 
        if (new_time < bonus.bonus.use_start_date) { //时间未到
          api.error_msg('该优惠卷使用期限未到', 1000)
        } else {
          if (new_time > bonus.bonus.use_end_date) { //时间过期
            api.error_msg('该优惠卷已过期', 1000)
          } else {
            _this.setData({
              bonus_id: bonus_id
            });

            setTimeout(function() {
              let rec_id = _this.data.rec_id
              api.success_msg('选择成功',1500)
              if (rec_id > 0) {
                wx.redirectTo({
                  url: '/pages/payment/payment?bonus_money=' + bonus.bonus.type_money + '&rec_id=' + rec_id + '&bonus_id=' + bonus_id,
                })
              } else {
                wx.redirectTo({
                  url: '/pages/payment/payment?bonus_money=' + bonus.bonus.type_money + '&bonus_id=' + bonus_id,
                })
              }
            }, 1000)

          }
        }
      } else {
        api.error_msg('订单满' + bonus.bonus.min_amount + '可用', 1000)
      }

    }

    // let coupons = this.data.coupons;
    // for(let i=0; i<coupons.length; i++){
    //   if (coupons[i].id == id){
    //     coupons[i].select = true;
    //   } else{
    //     coupons[i].select = false;
    //   }
    // }
    // this.setData({
    //   coupons: coupons
    // });



  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})