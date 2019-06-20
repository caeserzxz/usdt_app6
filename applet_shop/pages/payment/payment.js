// pages/payment/payment.js
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
    imgBase: getApp().globalData.imgUrl,
    goodsnumber: 1,
    wordnum: 0,
    rec_id: 0,
    goodsList: [], //商品列表
    totalGoodsPrice: 0, //商品总价格
    totalDiscount: 0, //总折扣价
    bounsData: [],
    orderTotal: 0,
    shippingFee: 0, //运费
    bonus_money: 0, //使用优惠券金额
    payInteger: 0, //实际支付金额整数
    payTotalDecimal: 0, //实际支付金额小数点
    address_id: 0, //地址id
    addressinfo: [], //地址信息
    rec_ids: [], //购物车id集合
    unusedNum: 0, //可使用优惠卷数量
    bonus_id: 0, //使用优惠卷id, 为0不使用
    buy_msg: '', //留言
  },
  //数量减
  minus: function(e) {
    const _this = this
    // 获取点击的索引
    const index = e.currentTarget.dataset.index;

    // 获取商品数据
    let list = _this.data.goodsList;


    // 获取商品数量
    let num = list[index].goods_number;


    if (num <= 1) {
      _this.setData({
        number_control: true,
      })
      return false;
    }
    // else  num大于1  点击减按钮  数量--
    num = num - 1;

    let rec_id = list[index].rec_id
    let data = {
      rec_id: rec_id,
      recids: _this.data.rec_id,
      num: num,
      address_id: _this.data.address_id,
    }
    _this.publicload('editNum', data)

  },
  //数量加
  add: function(e) {
    const _this = this;
    // 获取点击的索引
    const index = e.currentTarget.dataset.index;
    // 获取商品数据
    let list = _this.data.goodsList;
    // 获取商品数量
    let num = list[index].goods_number;
    // 点击递增
    num = num + 1;
    list[index].goods_number = num;

    let rec_id = list[index].rec_id
    let data = {
      rec_id: rec_id,
      recids: _this.data.rec_id,
      num: num,
      address_id: _this.data.address_id,
    }


    _this.publicload('editNum', data)
  },


  getWord(e) {
    const _this = this
    let buy_msg = e.detail.value;
    _this.setData({
      wordnum: e.detail.cursor,
      buy_msg: buy_msg
    });
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
    let bonus_money = options.bonus_money != undefined ? options.bonus_money : 0;
    if (options.bonus_money != undefined) {
      _this.setData({
        bonus_money: bonus_money
      })
    }
    let bonus_id = options.bonus_id != undefined ? options.bonus_id : 0;
    if (options.bonus_id != undefined) {
      _this.setData({
        bonus_id: bonus_id
      })
    }
  },

  //公共调用
  publicload: function(action, data) {

    let _this = this
    let loadurl = api.https_path + 'shop/api.Flow/' + action;
    let _data = data

    api.fetchPost(loadurl, _data, function(err, res) {
      // console.log(res)
      if (res.code == 0) {
        if (res.msg != '') {
          api.error_msg('请选择需购买的商品', 2000)
          setTimeout(function() {
            wx.switchTab({
              url: '/pages/index/index'
            })
          }, 2000);
          return false;
        };
      }
      if (action == 'getList') {
        if (res.cartInfo.allGoodsNum < 1) {
          api.error_msg('请选择需购买的商品', 2000)
          setTimeout(function() {
            wx.switchTab({
              url: '/pages/index/index'
            })
          }, 2000);

          return false;
        }
      }
      let list = res.cartInfo.goodsList;
      let rec_ids = [];
      Object.keys(list).forEach(function(key) {
        rec_ids.push(list[key].rec_id);
      });
      _this.setData({
        totalGoodsPrice: res.cartInfo.totalGoodsPrice,
        totalDiscount: res.cartInfo.totalDiscount,
        orderTotal: res.cartInfo.orderTotal,
        goodsList: list,
        rec_ids: rec_ids,
      })

      if (action == 'editNum') {
        let shippingFee = res.shippingFee.shipping_fee;
        _this.setData({
          shippingFee: shippingFee
        })

      }

      _this.getAddress(rec_ids) //获取收货地址
      _this.evalPrice();

    })

  },


  //计算支付金额
  evalPrice: function() {
    const _this = this;
    let orderTotal = _this.data.orderTotal;
    let shippingFee = _this.data.shippingFee;
    let bonus_money = _this.data.bonus_money;
    let payTotal = Number(orderTotal) + Number(shippingFee) - Number(bonus_money);

    let payTotal_arr = payTotal.toFixed(2);
    payTotal_arr = payTotal_arr.split(".");

    _this.setData({
      payTotal: payTotal, //总支付金额
      payInteger: payTotal_arr[0],
      payTotalDecimal: payTotal_arr[1],
      shippingFee: shippingFee
    })
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
        // let bounsData = res.data;

        // if (_this.data.totalGoodsPrice != 0) {
        //   bounsData.totalGoodsPrice = parseFloat(_this.data.totalGoodsPrice);
        // } else {
        //   bounsData.totalGoodsPrice = -1;
        // }
        _this.setData({
          unusedNum: res.data.unusedNum,
        })
        // console.log(bounsData);
      }
    })
  },

  //计算运费
  evalShippingFee: function(address_id, rec_ids) {
    const _this = this;
    let recids = _this.data.rec_ids
    let data = {
      address_id: _this.data.address_id,
      recids: rec_ids.join(','),
    }
    api.fetchPost(api.https_path + 'shop/api.flow/evalShippingFee', data, function(err, res) {
      if (res.code == 1) {
        _this.setData({
          shippingFee: res.shippingFee.shipping_fee,
        })
      }
      _this.evalPrice();
    })
  },


  //获取收货地址
  getAddress: function(recids) {
    const _this = this;
    let data = {};

    api.fetchPost(api.https_path + 'member/api.address/getList', data, function(err, res) {
      console.log(res)
      if (res.code == 1) {
        _this.setData({
          addressinfo: res.list[0],
          address_id: res.list[0].address_id,

        })
        _this.evalShippingFee(res.list[0].address_id, recids)
      }
    })
  },

  //没有地址
  noaddress: function() {
    const _this = this
    let rec_id = _this.data.rec_id
    if (_this.data.rec_id > 0) {
      wx.navigateTo({
        url: '/pages/address/address?rec_id=' + rec_id,
      })
    } else {
      wx.navigateTo({
        url: '/pages/address/address',
      })
    }
  },

  //去选择优惠券
  selectbonus: function() {
    const _this = this
    let rec_id = _this.data.rec_id
    let bonus_id = _this.data.bonus_id
    if (rec_id > 0) {
      wx.navigateTo({
        url: '/pages/coupons/coupons?rec_id=' + rec_id + '&payTotal=' + _this.data.payTotal + '&bonus_id=' + bonus_id,
      })
    } else {
      wx.navigateTo({
        url: '/pages/coupons/coupons?payTotal=' + _this.data.payTotal + '&bonus_id=' + bonus_id,
      })
    }

  },

  //下单
  doaddorder: function() {
    const _this = this
    // console.log(e)

    let buy_msg = _this.data.buy_msg; //备注
    let address_id = _this.data.address_id //地址id
    let used_bonus_id = _this.data.bonus_id // 优惠卷id
    let rec_ids = _this.data.rec_ids 
    let data = {
      recids: rec_ids.join(','),
      buy_msg: buy_msg,
      used_bonus_id: used_bonus_id,
      address_id: address_id,
      pay_id: 8
    }

    api.fetchPost(api.https_path + '/shop/api.flow/addOrder', data, function(err, res) {
      console.log(res)
      if (res.code == 1) {
        wx.redirectTo({
          url: '/pages/payorder/payorder?order_id=' + res.order_id,
        })

      } else {
        api.error_msg(res.msg)
      }
    })




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

    wx.showLoading({
      title: '数据加载中',
    })
    setTimeout(function() {
      wx.hideLoading()
    }, 1500)
    const _this = this
    let rec_id = _this.data.rec_id;

    let data = {
      is_sel: 1
    };
    if (rec_id != 0) {
      data.recids = rec_id
    }
    _this.publicload('getList', data);
    _this.getBonusList();
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