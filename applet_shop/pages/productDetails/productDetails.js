const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    goods_id: 0, //商品id
    isCollect: 0, //收藏状态
    is_spec: 0, //是否多规格商品
    indicatorDots: true, //轮播图参数
    autoplay: true,
    interval: 5000,
    duration: 1000, //轮播图参数
    imgBase: app.globalData.imgUrl, //图片域名
    imgUrls: [], //商品轮播图
    guigeModel: 0, //购买弹窗
    goods: [], //商品数据
    skuArr: [], //规格模型
    coArr: [], //选中的规格数组  {0:3,1:5}
    specifications: "", //选中的规格 拼接后的字符串  3:5
    skuprice: [], //规格价格数组
    valprice: 0, //普通及规格商品显示单价
    pinglun: [], //评论列表
    plnum: 0, //评论数
    cartInfo: [], //购物车数据
    goodsnumber: 1, //购买数量
    maxnum: 1 //商品库存
  },
  openGG() {
    this.setData({
      guigeModel: 1
    });
  },
  closeGG() {
    this.setData({
      guigeModel: 0
    });
  },
  collect() {
    let That = this;
    api.fetchPost(api.https_path + 'shop/api.goods/collect', {
      goods_id: That.data.goods_id
    }, function(err, res) {
      if (res.code == 1) {
        That.setData({
          isCollect: !That.data.isCollect
        });
      } else {
        api.error_msg(res.msg)
      }
    });

  },
  toindex() {
    wx.switchTab({
      url: '/pages/index/index',
    })
  },
  tocart() {
    wx.switchTab({
      url: '/pages/cart/cart',
    })
  },
  toqrcode() {
    let That = this;
    wx.navigateTo({
      url: '/pages/goodsQR/goodsQR?goods_id=' + That.data.goods_id,
    })
  },
  //商品数量--
  minus: function() {
    let That = this;
    let endnum = 0;
    //大于1则减1
    if (That.data.goodsnumber > 1) {
      endnum = That.data.goodsnumber - 1;
    }
    //小于1则等于1
    if (endnum < 1) {
      endnum = 1;
    }
    //处理input的超库存
    if (endnum > That.data.maxnum) {
      endnum = That.data.maxnum;
    }

    That.setData({
      goodsnumber: endnum
    })
  },
  //商品数量++
  add: function() {
    let That = this;
    let endnum = 0;
    //低于库存则+1，否则等于库存
    endnum = That.data.goodsnumber < That.data.maxnum ? ++That.data.goodsnumber : That.data.maxnum;
    //数量不能小于1
    if (endnum < 1) {
      endnum = 1;
    }
    That.setData({
      goodsnumber: endnum
    })
  },
  input_num: function(e) {
    let That = this;
    That.setData({
      goodsnumber: e.detail.value
    })
  },
  //选规格，切换颜色，调整单价及库存
  changColor: function(e) {
    let That = this;
    let pid = e.target.dataset.pid;
    let id = e.target.dataset.id;
    That.setData({
      ["coArr." + pid]: id
    });
    let coArr = That.data.coArr;
    let skuprice = That.data.skuprice;
    let str = "";
    for (var index in coArr) {
      if (str == "") {
        str += coArr[index];
      } else {
        str += ":" + coArr[index];
      }
    }
    if (skuprice != null && skuprice[str] != undefined) {
      //根据所选属性修改价格及库存
      That.setData({
        valprice: skuprice[str].shop_price,
        maxnum: skuprice[str].goods_number,
        specifications: str
      })
    }
  },
  //添加购物车
  addcart: function() {
    let That = this;
    api.fetchPost(api.https_path + 'shop/api.flow/addcart', {
      goods_id: That.data.goods_id,
      specifications: That.data.specifications,
      type: "oncart",
      number: That.data.goodsnumber
    }, function(err, res) {
      if (res.code == 1) {
        api.success_msg(res.msg, 1000);
      } else {
        api.error_msg(res.msg, 1500);
      }
    })
  },
  buy_now: function() {
    let That = this;
    api.fetchPost(api.https_path + 'shop/api.flow/addcart', {
      goods_id: That.data.goods_id,
      specifications: That.data.specifications,
      type: "oncart",
      number: That.data.goodsnumber
    }, function(err, res) {
      if (res.code == 1) {
        wx.navigateTo({
          url: '/pages/payment/payment?rec_id=' + res.rec_id,
        })
      } else {
        api.error_msg(res.msg)
      }
    });
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let That = this;

  wx.showLoading({
    title: '数据加载中',
  })

    let goods_id;
    let share_token;
    if (options.scene) {
      var scene = decodeURIComponent(options.scene);
      goods_id = scene.split("$")[0];
      share_token = scene.split("$")[1];
      That.setData({
        goods_id: goods_id,
        share_token: share_token,
      })

      That.nologin(goods_id, share_token);
    } else {
      goods_id = options.goods_id != undefined ? options.goods_id : 0;
      if (options.goods_id != undefined) {
        That.setData({
          goods_id: goods_id,
        })
      } else {
        wx.switchTab({
          url: '/pages/index/index',
        })
        return false
      }
    }

    //根据商品id请求接口获取商品数据
    api.fetchPost(api.https_path + 'shop/api.goods/info', {
      id: goods_id
    }, function(err, res) {
      console.log(res)

      wx.hideLoading();
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else if (res.list != undefined) {
        That.setData({
          imgBase: api.https_path,
          imgUrls: res.list.imgsList,
          goods: res.list.goods,
          cartInfo: res.list.cartInfo,
          valprice: res.list.goods.shop_price,
          isCollect: res.list.isCollect
        })
      } else {
        return false
      }
      if (res.list.goods.is_spec == 1) {
        That.setData({
          skuArr: res.list.goods.lstSKUArr,
          skuprice: res.list.goods.sub_goods
        })
      } else {
        That.setData({
          maxnum: res.list.goods.goods_number
        })
      }
    });
    //获取商品评论
    api.fetchPost(api.https_path + 'shop/api.comment/getListByGoods', {
      goods_id: goods_id,
      limit: 1
    }, function(err, res) {
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else if (res.data.list != undefined && res.data.list[0] != undefined) {
        That.setData({
          pinglun: res.data.list[0],
          plnum: res.data.total_count
        })
      } else {
        return false
      }
    })
  },


  nologin: function (goods_id, share_token) {
    const user_devtoken = api.getcache('user_devtoken')
    if (user_devtoken == "") {
      wx.redirectTo({
        url: '/pages/authorizeLogin/authorizeLogin?goods_id=' + goods_id + '&share_token=' + share_token,
      })
      return false
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
  //   onShareAppMessage: function () {

  //   }
})