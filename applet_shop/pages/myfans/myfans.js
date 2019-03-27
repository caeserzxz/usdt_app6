// pages/myorders/myorders.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    wulimodel: true,
    active1: true,
    active2: false,
    active3: false,
    active4: false,
    active5: false,
    imgUrl: getApp().globalData.imgUrl,
    fansDetais: [{
      userimg: 'proimg.jpg',
      userName: '胖子的自我修养',
      userID: '365236',
      userETC: '365236',
      userETH: '365236',
      userETB: '365236',
      userUSDT: '365236',
      userMoney: '365236',
      userIntegration: '365236',
      hiddenVal:true,
      transformVal: '0',//图标旋转角度
    }, {
      userimg: 'proimg.jpg',
      userName: '胖子的自我修养',
      userID: '365236',
      userETC: '365236',
      userETH: '365236',
      userETB: '365236',
      userUSDT: '365236',
      userMoney: '365236',
      userIntegration: '365236',
        hiddenVal: true,
        transformVal: '0',//图标旋转角度
    }, {
      userimg: 'proimg.jpg',
      userName: '胖子的自我修养',
      userID: '365236',
      userETC: '365236',
      userETH: '365236',
      userETB: '365236',
      userUSDT: '365236',
      userMoney: '365236',
        userIntegration: '365236',
        hiddenVal: true,
        transformVal: '0',//图标旋转角度
    }, {
      userimg: 'proimg.jpg',
      userName: '胖子的自我修养',
      userID: '365236',
      userETC: '365236',
      userETH: '365236',
      userETB: '365236',
      userUSDT: '365236',
      userMoney: '365236',
        userIntegration: '365236',
        hiddenVal: true,
        transformVal: '0',//图标旋转角度
    }, {
      userimg: 'proimg.jpg',
      userName: '胖子的自我修养',
      userID: '365236',
      userETC: '365236',
      userETH: '365236',
      userETB: '365236',
      userUSDT: '365236',
      userMoney: '365236',
        userIntegration: '365236',
        hiddenVal: true,
        transformVal: '0',//图标旋转角度
    }, {
      userimg: 'proimg.jpg',
      userName: '胖子的自我修养',
      userID: '365236',
      userETC: '365236',
      userETH: '365236',
      userETB: '365236',
      userUSDT: '365236',
      userMoney: '365236',
        userIntegration: '365236',
        hiddenVal: true,
        transformVal: '0',//图标旋转角度
    }]
  },
  //fans搜索
  selectFans: function (e) {
    wx.navigateTo({
      url: '/pages/selectFans/selectFans'
    })
  },
  wulimodel: function() {
    this.setData({
      wulimodel: false,
    })
  },
  wuliclose: function() {
    this.setData({
      wulimodel: true,
    })
  },
  tabchang1: function() {
    this.setData({
      active1: true,
      active2: false,
      active3: false,
      active4: false,
      active5: false,
    })
  },
  tabchang2: function() {
    this.setData({
      active1: false,
      active2: true,
      active3: false,
      active4: false,
      active5: false,
    })
  },
  tabchang3: function() {
    this.setData({
      active1: false,
      active2: false,
      active3: true,
      active4: false,
      active5: false,
    })
  },
  tabchang4: function() {
    this.setData({
      active1: false,
      active2: false,
      active3: false,
      active4: true,
      active5: false,
    })
  },
  tabchang5: function() {
    this.setData({
      active1: false,
      active2: false,
      active3: false,
      active4: false,
      active5: true,
    })
  },
  openData:function(e){
    let that=this
    let index = e.currentTarget.dataset.index
    let hiddenVal = that.data.fansDetais[index].hiddenVal
    let transformVal = that.data.fansDetais[index].transformVal
    if (hiddenVal == true && transformVal=='0'){
      this.setData({
        ['fansDetais[' + index + '].hiddenVal']: !hiddenVal,
        ['fansDetais[' + index + '].transformVal']:'180'
      })
    }else{
      this.setData({
        ['fansDetais['+index+'].hiddenVal']: !hiddenVal,
        ['fansDetais[' + index + '].transformVal']: '0'
      })
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {

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