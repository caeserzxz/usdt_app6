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
    list: [],
    counts:0,
    https_path:api.https_path,
    checked_ids:[],
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
  //选中事件
  checkboxTap(e) {
    const _this = this
    let id = e.currentTarget.id;
    let list = _this.data.list;
    let len = list.length;
    let checked  = [];
    for (let i = 0; i < len; i++) {
      if (list[i].goods_id == id) {
        list[i].checked = !list[i].checked;
      }
    }
    let selectAll = _this.data.selectAll;
    for (let i = 0; i < list.length; i++) {
      if (list[i].checked == true) {
        checked.push(list[i].goods_id);
      } 
    }
    if (checked.length == len){
      selectAll = true;
    } else {
      selectAll = false;
    }
    _this.setData({
      checked_ids: checked,
      list: list,
      selectAll: selectAll
    });
  },

  //删除收藏
  delgoods:function(e){
    const _this = this
    let goods_id = e.currentTarget.id;
    wx:wx.showModal({
      title: '操作提示',
      content: '你要删除这个收藏吗？',
      success: (res) => {
        if (!res.confirm) return;
        let data = {
          goods_id: goods_id,
        }
        _this.loadInfo('collect', data);
       
        _this.loadInfo('getCollectlist', {});
      }
    })
  },

  //取消收藏
  qxCollect:function(){
    const _this = this
    let ids = _this.data.checked_ids; 
    
    if (ids.length == 0){
      api.error_msg('请先选择需要取消的商品');
      return false;
    }
   
    wx: wx.showModal({
      title: '操作提示',
      content: '确定要取消选中商品收藏吗？',
      success: (res) => {
        if (!res.confirm) return;
        let data = {
          gids: ids.join(','),
        }
        _this.loadInfo('cancelCollect', data);

        _this.loadInfo('getCollectlist', {});
      }
    })
  },



  // checkboxChange(e) {
  //   const _this = this;
  //   console.log('checkbox发生change事件，携带value值为：', e.detail.value)

  //   let list = _this.data.list;
  //   let goods_id = e.detail.value

  //   for (let i = 0; i < list.length; i++) {
  //     if (list[i].goods_id == goods_id) {
  //       list[i].checked = !list[i].checked;
  //     }
  //   }
  //   _this.setData({
  //     list: list
  //   })
  // },


  selall: function(){
    const  _this = this;
    let list = _this.data.list;
    _this.setData({
      selectAll: !_this.data.selectAll
    });

    if (_this.data.selectAll){
      for(let i=0; i<list.length; i++){
        list[i].checked = true
      }
      _this.setData({
        list: list
      });
    } else {
      for (let i = 0; i < list.length; i++) {
        list[i].checked = false
      }
      _this.setData({
        list: list
      });
    }
    // console.log(list)
  },
  /**
     * 表格cell触摸开始事件
     */
  onTableCellTouchStart: function (event) {
    // console.log(event);
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
    wx.showLoading({
      title: '数据加载中...',
    })
    setTimeout(function(){

      wx.hideLoading();
    },1000)
  },


    loadInfo: function (action, data) {
    const  _this = this
    _this.setData({
      offsetRecord: { 'index': -1, 'startX': 0, 'offset': 0, 'direction': null },
      list:[],
      edit:false
    })

      let loadurl = api.https_path + 'shop/api.goods/' + action;
    let _data = data

    api.fetchPost(loadurl, _data, function (err, res) {
      console.log(res)
      if (res.code == 1) {
        if(res.list.length >0){
          for (let i = 0; i < res.list.length; i++) {
            res.list[i].checked = false;
          }
          _this.setData({
            list: res.list,
            counts: res.count
          })
        }
      } 

    })

  },

  goCollect:function(){

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
    const _this = this
    let data = {};
    _this.loadInfo('getCollectlist', data);
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