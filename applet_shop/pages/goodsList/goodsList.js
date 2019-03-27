const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    curr: 2,
    up: true,
    popin: false,
    list: [
      {
        img: 'goods01.png',
        url: '',
        price1: "399",
        price1_part:'.88',
        price2: "499.00",
        title: '连帽系腰带鹅绒羽绒服绒羽绒服',
        sale: '3980'
      },
      {
        img: 'goods01.png',
        url: '',
        price1: "399",
        price1_part: '.88',
        price2: "499.00",
        title: 'Whoo后 津率享红华凝率享红华凝',
        sale: '3980'
      },
      {
        img: 'goods01.png',
        url: '',
        price1: "399",
        price1_part: '.88',
        price2: "499.00",
        title: '2018新女款冬装双面呢装双面呢',
        sale: '3980'
      },
      {
        img: 'goods01.png',
        url: '',
        price1: "399",
        price1_part: '.88',
        price2: "499.00",
        title: '2018冬季新款女羽绒服新款女羽绒服',
        sale: '3980'
      }
    ],
    fenlei: [
      {
        id: 1,
        text: "女鞋",
        select: false,
      },
      {
        id: 2,
        text: "母婴",
        select: false,
      },
      {
        id: 3,
        text: "户外",
        select: false,
      },
      {
        id: 4,
        text: "男鞋",
        select: false,
      },
      {
        id: 5,
        text: "运动",
        select: false,
      },
      {
        id: 6,
        text: "女装",
        select: false,
      },
      {
        id: 7,
        text: "生活用品",
        select: false,
      },
      {
        id: 8,
        text: "配饰",
        select: false,
      }
      
    ],
    pinpai: [
      {
        id: 1,
        text: "华为",
        select: false,
      },
      {
        id: 2,
        text: "小米",
        select: false,
      },
      {
        id: 3,
        text: "苹果",
        select: false,
      }
    ]
  },
  openPopup() {
    this.setData({
      popin: true
    });
  },
  closePopup() {
    this.setData({
      popin: false
    });
  },
  clean(){
    let fenlei = this.data.fenlei;
    let pinpai = this.data.pinpai;
    for (let i = 0; i < fenlei.length; i++) {
      fenlei[i].select = false
    };
    for (let i = 0; i < pinpai.length; i++) {
      pinpai[i].select = false
    };
    this.setData({
      fenlei: fenlei,
      pinpai: pinpai
    });
  },
  flradio(e) {
    let id = e.currentTarget.id;
    let fenlei = this.data.fenlei;
    for (let i = 0; i < fenlei.length; i++){
      if (fenlei[i].id == id){
        fenlei[i].select = true
      } else {
        fenlei[i].select = false
      }
    };
    this.setData({
      fenlei: fenlei
    });
  },
  ppradio(e) {
    let id = e.currentTarget.id;
    let pinpai = this.data.pinpai;
    for (let i = 0; i < pinpai.length; i++) {
      if (pinpai[i].id == id) {
        pinpai[i].select = true
      } else {
        pinpai[i].select = false
      }
    };
    this.setData({
      pinpai: pinpai
    });
  },
  changeTab(e) {
    let curr = e.currentTarget.dataset.curr;
    console.log(curr);
    this.setData({
      curr: curr,
      up:　!this.data.up
    });
  },
  goSearch() {
    wx.navigateTo({
      url: '/pages/search/search',
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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