// pages/cart/cart.js
const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    selectAllStatus: true, // 全选状态，默认全选
    totalPrice: 0.00, // 总价，初始为0
    integerPrice:0,//整数价格
    decimalPrice: '00',//小数价格
    settlementNum:0,//结算数量
    cartlist: [{
      tag: 'ELAN_',
      title: '连帽系腰带鹅绒羽绒服大毛领手工刺连帽系腰带鹅绒羽绒服大毛领手工刺',
      desc: '深蓝色- M码',
      num: 4,
      price: 199,
      selected: true
    }, {
      title: '连帽系腰带鹅绒羽绒服大毛领手工刺连帽系腰带鹅绒羽绒服大毛领手工刺',
      desc: '深蓝色- M码',
      num: 1,
      price: 100,
      selected: true
    }, {
      title: '连帽系腰带鹅绒羽绒服大毛领手工刺连帽系腰带鹅绒羽绒服大毛领手工刺',
      desc: '深蓝色- M码',
      num: 1,
      price: 200,
      selected: true
    }],
    offsetRecord: {
      'index': -1,
      'startX': 0,
      'offset': 0,
      'direction': null
    }, // 偏移记录
    deleteButtonWidth: 150, // 删除按钮的宽度(rpx)
    pixelScale: 1,
    selectAll: false,
    scrollY: true,
    imgUrl: getApp().globalData.imgUrl,
    edit: 1
  },
  onLoad: function(options) {
    let _this = this;
  },

  /**
   * 表格cell触摸开始事件
   */
  onTableCellTouchStart: function(event) {
    console.log(0)
    if (event.changedTouches.length != 1) return;
    let index = event.currentTarget.dataset.index;
    let x = event.changedTouches[0].clientX;
    let offset = 0;
    if (this.data.offsetRecord != null && this.data.offsetRecord.index == index) {
      offset = this.data.offsetRecord.offset;
    }
    this.setData({
      offsetRecord: {
        'index': index,
        'startX': x,
        'offset': offset,
        'direction': null
      }
    });
  },
  onEdit(e) {
    this.setData({
      edit: e.target.dataset.index
    })
  },
  /**
   * 表格cell触摸移动事件
   */
  onTableCellTouchMove: function(event) {
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
    this.setData({
      offsetRecord: record
    });
  },

  /**
   * 表格cell触摸结束事件
   */
  onTableCellTouchEnd: function(event) {
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
      this.setData({
        offsetRecord: record
      });
    }
  },

  /**
   * 表格cell删除按钮点击事件
   */
  onDeleteButtonTapped: function(event) {
    let index = event.currentTarget.dataset.index;
    wx.showModal({
      content: `确定要删除吗？`,
      showCancel: true,
      success: (res) => {
        if (!res.confirm) return;
        let items = Reflect.get(this.data, 'items');
        items.splice(index, 1);
        this.setData({
          items: items,
          offsetRecord: null
        });
      }
    });
  },
  //单选事件
  selectList(e) {
    let that = this;
    // 获取选中的radio索引
    let index = e.currentTarget.dataset.index;
    // 获取到商品列表数据
    let list = that.data.cartlist;
    // 默认全选
    that.data.selectAllStatus = true;
    // 循环数组数据，判断----选中/未选中[selected]
    list[index].selected = !list[index].selected;
    // 如果数组数据全部为selected[true],全选
    for (let i = list.length - 1; i >= 0; i--) {
      if (!list[i].selected) {
        that.data.selectAllStatus = false;
        break;
      }
    }
    // 重新渲染数据
    that.setData({
      cartlist: list,
      selectAllStatus: that.data.selectAllStatus,
    })
    // 调用计算金额数量方法
    that.count_price();
  },
  //全选事件
  selectAll(e){
    let that = this;
    // 获取选中的radio索引
    let index = e.currentTarget.dataset.index;
    // 获取到商品列表数据
    let list = that.data.cartlist;
    // 默认全选
    that.data.selectAllStatus = !that.data.selectAllStatus;

    // 如果数组数据全部为selected[true],全选
    for (let i = list.length - 1; i >= 0; i--) {
      list[i].selected = that.data.selectAllStatus
    } 
    // 重新渲染数据
    that.setData({
      cartlist: list,
      selectAllStatus: that.data.selectAllStatus,
    })
    // 调用计算金额数量方法
    that.count_price();
  },
  /**
   * 绑定加数量事件
   */
  btn_add(e) {
    // 获取点击的索引
    const index = e.currentTarget.dataset.index;
    // 获取商品数据
    let list = this.data.cartlist;
    // 获取商品数量
    let num = list[index].num;
    // 点击递增
    num = num + 1;
    list[index].num = num;
    // 重新渲染 ---显示新的数量
    this.setData({
      cartlist: list
    });
    // 计算金额数量方法
    this.count_price();
  },
  /**
   * 绑定减数量事件
   */
  btn_minus(e) {
    // 获取点击的索引
    const index = e.currentTarget.dataset.index;

    // 获取商品数据
    let list = this.data.cartlist;
    // 获取商品数量
    let num = list[index].num;
    // 判断num小于等于1  return; 点击无效
    if (num <= 1) {
      return false;
    }
    // else  num大于1  点击减按钮  数量--
    num = num - 1;
    list[index].num = num;
    // 渲染页面
    this.setData({
      cartlist: list
    });
    // 调用计算金额方法
    this.count_price();
  },
  /**
   * 计算总价和数量
   */
  count_price() {
    // 获取商品列表数据
    let list = this.data.cartlist;
    // 声明一个变量接收数组列表price
    let total = 0;
    // 结算商品数量
    let num=0;
    // 循环列表得到每个数据
    for (let i = 0; i < list.length; i++) {
      // 判断选中计算价格
      if (list[i].selected) {
        // 所有价格加起来 count_money
        total += list[i].num * list[i].price;
        num+=list[i].num
      }
    }
    total = total.toFixed(2)
    // 最后赋值到data中渲染到页面
    this.setData({
      cartlist: list,
      totalPrice: total,
      settlementNum: num,
      integerPrice: total.split('.')[0],
      decimalPrice: total.split('.')[1]
    });
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
    this.count_price()
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

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  }
})