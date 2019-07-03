// pages/balanceDetail/balanceDetail.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    open: false,
    imgUrl: getApp().globalData.imgUrl,
    dateTime: '',
    emptyData: false, //有无数据
    lists: [],
    page: 0,
    type_str: '商城消费',
    type: 'order', //5  withdraw 提现相关 2brokerage 佣金相关 
    load_more: true,
    is_brokerage: false,
    is_withdraw: false,
    is_order: true,
    is_integral: false,
    income: 0, //收益
    expend: 0, //支出
  },


  bindDateChange: function(e) {
    const _this = this
    let arr = e.detail.value.split('-')
    let date = arr[0] + '年' + arr[1] + '月'
    _this.setData({
      dateTime: date,
      page: 0,
      lists: [],
    })
    _this.publicloadlist(_this.data.type, date);

  },


  openDestination: function() {
    this.setData({
      open: !this.data.open
    });
  },

  radioChange: function(e) {
    const _this = this
    let type_str;
    let type = e.detail.value
    let is_order = _this.data.is_order;
    let is_withdraw = _this.data.is_withdraw;
    let is_brokerage = _this.data.is_brokerage;
    let is_integral = _this.data.is_integral;

    if (type == 'withdraw') {
      type_str = "提现相关";
      is_withdraw = true;
      is_order = false;
      is_brokerage = false;
      is_integral = false;
    } else if (type == 'brokerage') {
      type_str = "佣金相关";
      is_withdraw = false;
      is_order = false;
      is_brokerage = true;
      is_integral = false;
    } else if (type == 'order') {
      type_str = "商城消费";
      is_withdraw = false;
      is_order = true;
      is_brokerage = false;
      is_integral = false;
    } else {
      type_str = "积分兑换";
      is_withdraw = false;
      is_order = false;
      is_brokerage = false;
      is_integral = true;
    }
    _this.setData({
      type: type,
      type_str: type_str,
      open: !_this.data.open,
      is_withdraw:is_withdraw,
      is_brokerage: is_brokerage,
      is_order:is_order,
      is_integral: is_integral,
      page: 0,
      lists: [],
    })


    _this.publicloadlist(type, _this.data.dateTime);

  },



  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    const _this = this
    api.islogin();
    _this.publicloadlist(_this.data.type, _this.data.dateTime);
  },

  publicloadlist: function(type, time) {
    const _this = this

    let data = {
      p: _this.data.page + 1,
      type: type,
      time: time
    }

    api.fetchPost(api.https_path + '/member/api.Users/AccountLog', data, function(err, res) {

      console.log(res)
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else {

        _this.setData({
          dateTime: res.time,
          expend: res.expend,
          income: res.income,
        })
        if (res.list.length > 0) {
          _this.setData({
            ["lists[" + _this.data.page + "]"]: res.list,
            load_more: true,
            emptyData: false
          })
        } else {
          api.error_msg("没有更多数据!")
          _this.setData({
            load_more: false,
          })
          if (_this.data.page == 0 && res.list.length == 0) {
            _this.setData({
              emptyData: true,
            })
          }
        }

      }
    });


  },

  /**
   * 滚动加载数据
   */
  scrolltloadlist: function() {
    let _this = this;
    if (_this.data.load_more == true) {
      let more = _this.data.page + 1;
      _this.setData({
        page: more,
      });
      _this.publicloadlist(_this.data.type, _this.data.dateTime);;
    }
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