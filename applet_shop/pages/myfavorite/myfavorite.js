const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    edit: false,
    selectAll: false,
    offsetRecord: { 'index': -1, 'startX': 0, 'offset': 0, 'direction': null }, // 偏移记录
    deleteButtonWidth: 150, // 删除按钮的宽度(rpx)
    pixelScale: 1,
    list: [
      {
        id: 1,
        title: '元旦狂欢畅销款情侣手表对表送礼礼盒时尚休闲石英情侣对表2只装',
        desc: '铁锈红-M码',
        checked: false
      },
      {
        id: 2,
        title: '连帽系腰带鹅绒羽绒服大毛领手工刺连帽系腰带鹅绒羽绒服大毛领手工刺',
        desc: '深蓝色- M码',
        checked: false
        
      },
      {
        id: 3,
        title: '春节不打烊狂欢畅销款情侣手表对表送礼礼盒连帽系腰带鹅绒羽绒服大毛领手工刺',
        desc: '深蓝色- M码',
        checked: false
      }
    ]
  },
  openEdit() {
    this.setData({
      edit: true
    });
  },
  editDone() {
    this.setData({
      edit: false
    });
  },
  checkboxTap(e) {
    let id = e.currentTarget.id;
    let list = this.data.list;
    let len = list.length;
    let checked  = [];
    for (let i = 0; i < len; i++) {
      if (list[i].id == id) {
        list[i].checked = !list[i].checked;
      }
    }

    let selectAll = this.data.selectAll;
    for (let i = 0; i < list.length; i++) {
      if (list[i].checked == true) {
        checked.push(list[i].id);
      } 
    }
    if (checked.length == len){
      selectAll = true;
    } else {
      selectAll = false;
    }
    this.setData({
      list: list,
      selectAll: selectAll
    });
  },
  del(e){
    let id = e.currentTarget.id;
    wx:wx.showModal({
      title: '操作提示',
      content: '你要删除这个收藏吗？',
    })
  },
  checkboxChange(e) {
    console.log('checkbox发生change事件，携带value值为：', e.detail.value)
    let list = this.data.list;
  },
  selall: function(){
    let that = this;
    let list = this.data.list;
    that.setData({
      selectAll: !this.data.selectAll
    });

    if (this.data.selectAll){
      for(let i=0; i<list.length; i++){
        list[i].checked = true
      }
      that.setData({
        list: list
      });
    } else {
      for (let i = 0; i < list.length; i++) {
        list[i].checked = false
      }
      that.setData({
        list: list
      });
    }
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