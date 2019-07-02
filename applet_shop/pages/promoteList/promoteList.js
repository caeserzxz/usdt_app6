const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    curr: 1,
    up: true,
    popin: false,
    //上拉加载限制
    load_more: true,
    //父分类id
    pid: '',
    //搜索排序等数据
    search_data: {
      cid: '',
      brand_id: '',
      sort: '',
      order: '',
      min_price: '',
      max_price: '',
      keyword: '',
      p: 1,
      is_best:0
    },
    keyword: '',
    tmp_data: {
      cid: '',
      brand_id: '',
      min_price: '',
      max_price: '',
    },
    list: [],
    fenlei: [],
    pinpai: []
  },
  openPopup() {
    this.setData({
      popin: true
    });
  },
  closePopup(e) {
    //提交筛选条件
    var That = this;
    this.setData({
      ["search_data.cid"]: That.data.tmp_data.cid,
      ["search_data.brand_id"]: That.data.tmp_data.brand_id,
      ["search_data.min_price"]: That.data.tmp_data.min_price,
      ["search_data.max_price"]: That.data.tmp_data.max_price,
      ["search_data.p"]: 1,
      list: [],
      pinpai: [],
      load_more: true
    });
    That.refresh();
    That.refresh_brand();
    this.setData({
      popin: false
    });
  },
  clean() {
    //清除筛选条件
    this.setData({
      tmp_data: {
        cid: '',
        brand_id: '',
        min_price: '',
        max_price: '',
      }
    });
  },
  flradio(e) {
    let id = e.currentTarget.id;
    let fenlei = this.data.fenlei;
    this.setData({
      ["tmp_data.cid"]: id
    })
  },
  ppradio(e) {
    let id = e.currentTarget.id;
    let pinpai = this.data.pinpai;
    this.setData({
      ["tmp_data.brand_id"]: id
    })
  },
  ipmin(e) {
    this.setData({
      ["tmp_data.min_price"]: e.detail.value
    })
  },
  ipmax(e) {
    this.setData({
      ["tmp_data.max_price"]: e.detail.value
    })
  },
  //价格、销量等排序处理
  changeTab(e) {
    let That = this;
    let curr = e.currentTarget.dataset.curr;
    //切换或新搜，新搜默认升序
    let up = curr == That.data.curr ? !this.data.up : true;
    let str = '';
    switch (curr) {
      case '2':
        str = 'sales';
        break;
      case '3':
        str = 'price';
        break;
      default:
        break;
    }
    let sort = up ? 'ASC' : 'DESC';
    That.setData({
      curr: curr,
      up: up,
      ["search_data.order"]: str,
      ["search_data.sort"]: sort,
      //搜索，跳到第一页
      ["search_data.p"]: 1,
      load_more: true
    });
    That.refresh();
  },
  goSearch() {
    wx.navigateTo({
      url: '/pages/search/search',
    })
  },
  //刷新商品数据主函数
  refresh: function() {
    let That = this;
    That.setData({
      load_more: false
    });
    api.fetchPostOther(api.https_path + 'shop/api.goods/promoteList', That.data.search_data, function(err, res) {
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else if (res.list != undefined) {
        That.setData({
          ["list[" + (That.data.search_data.p - 1) + "]"]: res.list,
          load_more: true,
          path: api.https_path
        })
      } else {
        api.error_msg("没有更多数据!")
        return false
      }
    });
  },
  //刷新品牌数据
  refresh_brand: function() {
    let That = this;
    api.fetchPostOther(api.https_path + 'shop/api.goods/getBrandList', That.data.search_data, function(err, res) {
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else if (res.list != undefined) {
        That.setData({
          pinpai: res.list
        })
      } else {
        return false
      }
    });
  },
  //加载更多数据
  lower: function() {
    let That = this;
    if (That.data.load_more == true) {
      let more = That.data.search_data.p + 1;
      console.log(more);
      That.setData({
        ["search_data.p"]: more
      });
      That.refresh();
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let That = this;
    let pid = options.pid != undefined ? options.pid : 0;
    if (options.categoryid != undefined) {
      That.setData({
        ["search_data.cid"]: options.categoryid,
        pid: pid,
        ["tmp_data.cid"]: options.categoryid,
      })
    }
    let keyword = options.keyword != undefined ? options.keyword : '';

    if (options.keyword != undefined) {
      That.setData({
        ["search_data.keyword"]: options.keyword,
        pid: pid,
        ["tmp_data.keyword"]: options.keyword,
        keyword: options.keyword,
      })
    }
    let is_best = options.is_best != undefined ? options.is_best : '';

    if (options.is_best != undefined) {
      That.setData({
        ["search_data.is_best"]: options.is_best,
        pid: pid,
        
      })

    }
    //载入商品数据
    That.refresh();

    //载入品牌数据
    That.refresh_brand();

    //载入同级分类数据
    api.fetchPostOther(api.https_path + 'shop/api.category/pid_get_category', {
      pid: That.data.pid
    }, function(err, res) {
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else if (res.list != undefined) {
        That.setData({
          fenlei: res.list
        })
      } else {
        return false
      }
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