const app = getApp()
var api = require("../../common/api.js")
Page({
  data: {
    duration: 500,
    toptis: true,
    codePop: false,
    imgBase: app.globalData.imgUrl,
    time: 10000, //倒计时总秒数
    showTime: ['00', '00', '00'],
    imgUrls: [
      'swiperimg01.png',
      'swiperimg01.png',
      'swiperimg01.png'
    ],
    imgUrls2: [
      'goods01.png',
      'goods01.png',
      'fenlei01.png',
      'seckillimg.png',
      'youLike01.png',
      'youLike01.png',
      'youLike01.png'
    ],
    imgUrls3: [
      'group01.png',
      'group01.png',
      'group01.png'
    ],
    indicatorDots: true,
    autoplay: true,
    interval: 5000,
    duration: 1000,
    goods_lists: [],
    load_more: true,
    page: 0,
    slideList: [], //幻灯片
    navMenuList: [], //导航栏
    classGoods: [], //分类商品
    bestGoods: [], //猜你喜欢
    https_path:api.https_path,
    shop_index_search_text:'',//搜索栏默认
  },
  switchCode() {
    this.setData({
      codePop: !this.data.codePop
    });
  },
  goSearch() {
    wx.navigateTo({
      url: '/pages/search/search',
    })
  },
  closeToptis() {
    this.setData({
      toptis: false
    });
  },
  changeIndicatorDots(e) {
    this.setData({
      indicatorDots: !this.data.indicatorDots
    })
  },
  changeAutoplay(e) {
    this.setData({
      autoplay: !this.data.autoplay
    })
  },
  intervalChange(e) {
    this.setData({
      interval: e.detail.value
    })
  },
  durationChange(e) {
    this.setData({
      duration: e.detail.value
    })
  },
  // 倒计时
  countdown: function() {
    let that = this;
    let total_micro_second = that.data.time;
    let countDownTime;
    if (total_micro_second <= 0) {
      that.setData({
        time: total_micro_second,
        showTime: ['00', '00', '00']
      });
      return
    } else {
      countDownTime = that.dateformat(total_micro_second) //显示的时间
      total_micro_second -= 1000; //剩余的毫秒数
    }
    that.setData({
      time: total_micro_second,
      showTime: countDownTime
    });
    setTimeout(function() {
      that.countdown();
    }, 1000)
  },

  // 时间格式化输出，如11天03小时25分钟19秒  每1s都会调用一次
  dateformat: function(micro_second) {
    // 总秒数
    var second = Math.floor(micro_second / 1000);
    // 天数
    var day = Math.floor(second / 3600 / 24);
    // 小时
    var hr = Math.floor(second / 3600 % 24);
    // 分钟
    var min = Math.floor(second / 60 % 60);
    // 秒
    var sec = Math.floor(second % 60);
    return [(hr < 10 ? '0' + hr : hr), (min < 10 ? '0' + min : min), (sec < 10 ? '0' + sec : sec)];
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    const _this = this
    wx.showLoading({
      title: '数据加载中',
    })
    _this.loadIndexinfo();
    api.getconfig('', function (err, data) {
      // console.log(data)
      _this.setData({
        shop_index_search_text: data.shop_index_search_text
      })
    })
  },
  //获取首页数据
  loadIndexinfo: function () {
    const _this = this;
    api.fetchPost(api.https_path + '/publics/api.index/get_index_data', {}, function (err, res) {
      wx.hideLoading()
      console.log(res)
      if (res.code == 1) {
        if (res.is_diy == 1){//自定义装修
          _this.setData({
            is_diy: 1,
            diypages: res.diypage,
          })
          wx.getSystemInfo({
            success: function (t) {
              var e = t.windowWidth / 1.7;
              _this.setData({
                swiperheight: e
              });
            }
          })
          if (res.diypage.page.topdiystyle == 1){
            wx.setNavigationBarTitle({
              title: res.diypage.page.title
            }), wx.setNavigationBarColor({
              frontColor: res.diypage.page.titlebarcolor,
              backgroundColor: res.diypage.page.titlebarbg
            })
          }
        }else{//默认首页
          _this.countdown();
          _this.setData({
              is_diy: 0,
              bestGoods: res.bestGoods,
              classGoods: res.classGoods,
              promoteList: res.promoteList,
              navMenuList: res.navMenuList,
              slideList: res.slideList,
          })
        }
      } else {
        api.error_msg("系统繁忙，稍后再试")
      }
    })
  },

  //促销商品
  promoteList: function () {
    const _this = this
    const _data = { is_promote: 1 }
    api.fetchPost(api.https_path + '/shop/api.goods/promoteList', _data, function (err, res) {
      console.log(res)
      if (res.code == 1) {
        var promote = res.list;console.log(promote)
        _this.setData({
          promote: promote
        })
      } else {
        api.error_msg("系统繁忙，稍后再试")
      }
    })
  },
  morebest:function(){
    wx.navigateTo({
      url: '/pages/goodsList/goodsList?is_best=1',
    })
  },
  goshopinfo:function(e){
    let goods_id = e.currentTarget.dataset.goods_id;
    wx.navigateTo({
      url: ' '+goods_id,
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
  //商品翻页
  cutGoods: function (t) {
    var a = this, e = t.currentTarget.dataset.type, i = t.currentTarget.dataset.num, s = t.currentTarget.dataset.id, n = a.data.diypages;
    for (var o in n.items) if (o == s) {
      var r = n.items[o].current || 0;
      "advance" == e ? r < i - 1 ? (n.items[o].current = r + 1, a.setData({
        diypages: n
      })) : (n.items[o].current = 0, a.setData({
        diypages: n
      })) : r > 0 ? (n.items[o].current = r - 1, a.setData({
        diypages: n
      })) : (n.items[o].current = i - 1, a.setData({
        diypages: n
      }));
    }
  },
  //自定义装修跳转调用
  navigate: function (t) {
    var _url = t.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url,
    })
  }, 
  //领取红包
  receivecoupon: function (e) {
    const _this = this
    let type_id = e.currentTarget.id;
    api.fetchPost(api.https_path + '/shop/api.Bonus/receiveFree', { id: type_id }, function (err, res) {
      console.log(res)
      if (res.code == 0) {
        api.error_msg(res.msg)
        return false
      } else {
        api.success_msg(res.msg, 1500);
      }
    });

  },
})