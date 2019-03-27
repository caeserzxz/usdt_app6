// pages/address/address.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list: [
      {
        id: "1",
        name: "刘德华",
        addr: "广州市海珠区客村新南港西路丽影广场4栋105室",
        tel: "13423432343",
        default: false
      },
      {
        id: "2",
        name: "张学友",
        addr: "广州市海珠区客村新南港西路丽影广场4栋105室",
        tel: "13423432343",
        default: false
      },
      {
        id: "3",
        name: "黎明",
        addr: "广州市海珠区客村新南港西路丽影广场4栋105室",
        tel: "13423432343",
        default: false
      }
      ,
      {
        id: "3",
        name: "黎明",
        addr: "广州市海珠区客村新南港西路丽影广场4栋105室",
        tel: "13423432343",
        default: false
      }
    ],
    offsetRecord: { 'index': -1, 'startX': 0, 'offset': 0, 'direction': null }, // 偏移记录
    deleteButtonWidth: 120, // 删除按钮的宽度(rpx)
    pixelScale: 1,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  /**
     * 表格cell触摸开始事件
     */
  onTableCellTouchStart: function (event) {
    console.log(event);
    if (event.changedTouches.length != 1) return;
    let index = event.currentTarget.dataset.index;
    let x = event.changedTouches[0].clientX;
    let offset = 0;
    if (this.data.offsetRecord != null && this.data.offsetRecord.index == index) {
      offset = this.data.offsetRecord.offset;
    }
    this.setData({ offsetRecord: { 'index': index, 'startX': x, 'offset': offset, 'direction': null } });
  },
  /**
   * 表格cell触摸移动事件
   */
  onTableCellTouchMove: function (event) {
    if (event.changedTouches.length != 1) return;
    let index = event.currentTarget.dataset.index;
    let record = this.data.offsetRecord;
    if (record == null || index != Reflect.get(record, 'index')) {
      return;
    }
    let clientX = event.changedTouches[0].clientX;
    let startX = Reflect.get(record, 'startX');

    if (Reflect.get(record, 'direction') == undefined) {
      // 记录手势是左滑还是右滑
      let direction = startX >= clientX ? 'left' : 'right';
      Reflect.set(record, 'direction', direction);
    }
    if (Reflect.get(record, 'direction') == 'left') { // 👈滑动
      record.offset = Math.min((startX - clientX) * this.data.pixelScale, this.data.deleteButtonWidth);
    } else { // 👉滑动
      if (record.offset > 0) {
        record.offset = Math.max(this.data.deleteButtonWidth - Math.abs(clientX - startX) * this.data.pixelScale, 0);
      } else {
        record = null;
      }
    }
    this.setData({ offsetRecord: record });
  },
  /**
   * 表格cell触摸结束事件
   */
  onTableCellTouchEnd: function (event) {
    if (event.changedTouches.length != 1) return;
    let index = event.currentTarget.dataset.index;
    let record = this.data.offsetRecord;

    if (record != null && index == Reflect.get(record, 'index')) {
      let offset = Reflect.get(record, 'offset');
      if (offset >= this.data.deleteButtonWidth / 2) {
        Reflect.set(record, 'offset', this.data.deleteButtonWidth);
      } else {
        record = null;
      }
      this.setData({ offsetRecord: record });
    }
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