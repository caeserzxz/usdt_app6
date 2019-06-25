const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    goods:[],
    qrimg:'',
    memberinfo:[],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let That = this;
    let goods_id = options.goods_id != undefined ? options.goods_id : 0;
    if (options.goods_id != undefined) {
      That.setData({ goods_id: goods_id })
    } else {
      wx.redirectTo({
        url: '/pages/index/index',
      })
      return false
    }

    //获取商品数据
    api.fetchPost(api.https_path + 'shop/api.goods/info', { id: goods_id }, function (err, res) {
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else if (res.list != undefined) {
        That.setData({ goods: res.list.goods, imgBase: api.https_path});
      } else {
        return false
      }
    });

    //获取用户数据
    api.fetchPost(api.https_path + 'member/api.users/getInfo', {}, function (err, res) {
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else if (res.info != undefined) {
        That.setData({ memberinfo: res.info});
      } else {
        return false
      }
    });


    //获取二维码
    api.fetchPost(api.https_path + '/shop/api.goods/get_goods_mini_qrcode', { goods_id: goods_id }, function (err, res) {
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else {
        That.setData({ qrimg: res.qrcode });
      } 
    });
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
  onShareAppMessage: function (res) {
    const _this = this
    let title = _this.data.goods.goods_name
    let token = _this.data.memberinfo.token
    if (res.from === 'button') {
      console.log(123)
    }
    return {
      title: title,
      path: '/pages/productDetails/productDetails?goods_id=' + _this.data.goods_id +'&token=' + token,
      success: function (res) {
        console.log('成功', res)
      }
    }
  }
})