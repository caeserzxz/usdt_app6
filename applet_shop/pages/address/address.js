// pages/address/address.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    offsetRecord: {
      'index': -1,
      'startX': 0,
      'offset': 0,
      'direction': null
    }, // 偏移记录
    deleteButtonWidth: 120, // 删除按钮的宽度(rpx)
    pixelScale: 1,
    lists: [],
    nodata: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    api.islogin() //判断是否已经登录，没有登录跳转去登录
  },

  /**
   * 设置默认地址
   */
  setdefault: function(options) {
    const That = this
    const address_id = options.currentTarget.dataset.address_id
    api.fetchPost(api.https_path + '/member/api.address/editDefault', {
      address_id: address_id
    }, function(err, res) {
      if (res.code == 1) {
        That.setData({
          lists: [],
        })
        That.loadlists()
      } else {
        api.error_msg(res.msg)
      }
    })
  },

  /**
   * 删除地址
   */
  deleteaddress: function(options) {
    const That = this
    const address_id = options.currentTarget.dataset.address_id
    wx.showModal({
      title: '提示',
      content: '确定要删除吗?',
      success(res) {
        if (res.confirm) {
          api.fetchPost(api.https_path + '/member/api.address/delete', {
            address_id: address_id
          }, function(err, res) {
            if (res.code == 1) {
              That.setData({
                lists: [],
              })
              That.loadlists()
            } else {
              api.error_msg(res.msg)
            }
          })
        } else if (res.cancel) {

        }
      }
    })
  },

  /**
   * 加载收货地址
   */
  loadlists: function() {
    const That = this
    api.fetchPost(api.https_path + '/member/api.address/getList', {}, function(err, res) {
      if (res.code == 1) {
        //console.log(res.list)
        That.setData({
          lists: res.list,
          nodata:false
        })
      }else{
        That.setData({
          nodata: true
        })
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
    const That = this
    That.loadlists()
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
  // onShareAppMessage: function() {

  // }
})