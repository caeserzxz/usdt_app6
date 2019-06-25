const app = getApp()
var api = require("../../common/api.js")

//获取评论列表
var p = 1;
var commentList = function (that) {
  var p = that.data.p
  const _data = {
    type: that.data.type,
    p: p
  }

  api.fetchPost(api.https_path + 'shop/api.comment/getList', _data, function (err, res) {
    console.log(res)
    var typeId = that.data.currentTab;
    if (typeId==0) {
      that.setData({  
        num1: res.total_count
      });
    }else{
      that.setData({  
        num2: res.total_count
      });
    };
    if (p==1) {
      that.setData({ 
        list: [],
        postType: 1
      });
    };
    if (res.list) {
      var list = that.data.list;
      var lists = res.list;
      for (var i = 0; i < parseInt(lists.length); i++) {
        if(list.indexOf(lists[i]) == -1){
          list.push(lists[i])
        } 
      }
      var postType = res.list.length<5?0:1;
      that.setData({  
        list: list,
        path: api.https_path,
        postType: postType,
      });
    }else{
      var list = that.data.list;
      that.setData({
        list: list
      }) 
    }
  });
}
//获取已评论数
var commentnum2 = function (that) {
  var p = that.data.p
  const _data = {
    type: 'trim',
    p: p
  }
  api.fetchPost(api.https_path + 'shop/api.comment/getList', _data, function (err, res) {
    that.setData({  
      num2: res.total_count
    });
  });
}

Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    currentTab: 0,
    type: 'wait',
    time: 0,
    p: 1,
    list: [],
    num1: 0,
    num2: 0,
    postType: 1
  },
  //点击切换
  clickTab: function (e) {
    var that = this;
    that.setData({
      p: 1,
      list: [],
      postType: 1,
      currentTab: e.currentTarget.id,
    })
    if (e.currentTarget.id == 1) {
      that.setData({
        type: 'trim'
      })
    }else{
      that.setData({
        type: 'wait'
      })
    }
    commentList(that);
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    const that = this

    var time = new Date().getTime();
    that.setData({ time: time });

    api.islogin();
    commentList(that);
    commentnum2(that);
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    const that = this

    var time = new Date().getTime();
    that.setData({ time: time });

    api.islogin();
    commentList(that);
    commentnum2(that);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    var that = this
    var time = that.data.time
    var getTime = new Date().getTime();
    var postType = that.data.postType;
    if (getTime-time >= 1000 && postType == 1) {
      that.setData({
        time: getTime,
      })
      var p = that.data.p
      p++
      that.setData({ p: p })
      commentList(that)
    };
  },
})