// pages/search/search.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    hotkeywords: [], //热门搜索
    keyword: '',
    searchKeys: [], //搜索历史
    default_keyword: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const _this = this
    _this.loadinfo();
  },

  loadinfo: function () {

    const _this = this
    // wx.getStorage({
    //   key: 'searchKeys',
    //   success(res) {
    //     // console.log(res)
    //     _this.setData({
    //       searchKeys: res.data
    //     })
    //   }
    // })
    api.fetchPost(api.https_path + '/shop/api.goods/get_keyword', {}, (err, res) => {
      // console.log(res)
      if (res.code == 1) {
        _this.setData({
          hotkeywords: res.hot_search,
          // searchKeys: res.searchKeys,
          default_keyword: res.default_keyword,
        })
      }

    })


  },


  inputkey: function (e) {
    var _this = this
    _this.setData({
      keyword: e.detail.value
    })
  },

  searchkeyword: function (e) {
    var _this = this
    var keyword = _this.data.keyword
    wx.navigateTo({
      url: '/pages/goodsList/goodsList?keyword=' + keyword
    })
  },


  searchhotkeyword: function (e) {
    console.log()
    let _this = this
    let keyword = e.currentTarget.dataset.keyword
    wx.navigateTo({
      url: '/pages/goodsList/goodsList?keyword=' + keyword
    })
  },

  quxiao: function () {
    wx.switchTab({
      url: '/pages/index/index',

    })


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