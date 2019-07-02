const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    wordsNum:0,
    upload_list: []
  },
  getWordsNum:function(e){
    this.setData({
      value: e.detail.value,
      wordsNum:e.detail.cursor
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const that = this
    api.islogin();
    if (options.rec_id>0) {
      that.setData({
        path: api.https_path,
        rec_id: options.rec_id
      })
      that.comment(that);
    }
  },

  //获取评论详情
  comment: function (that) {
    const _data = {
      rec_id: that.data.rec_id,
    }
    api.fetchPost(api.https_path + 'shop/api.comment/getInfo', _data, function (err, res) {
      // console.log(res)
      if (res.data) {
        that.setData({
          path: api.https_path,
          data: res.data
        })
      }else{
        that.setData({
          data: []
        }) 
      }
    });
  },

  //选择图片
  addImg: function(e){
    var that = this;
    wx.chooseImage({
      count: 6, // 默认9
      sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        // console.log(res);
        // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片
        var tempFiles = res.tempFilePaths 
        //循环把图片加入上传列表
        for(var i in tempFiles){
          wx.uploadFile({
            url: api.https_path + '/publics/api.index/uploaderimages',
            filePath: tempFiles[i],
            name: 'file',
            formData: { },
            success(res) {
              var data = JSON.parse(res.data);
              const upload_list = that.data.upload_list
              if (data.code == 1) {
                upload_list.push(data.src)
                that.setData({
                  upload_list: upload_list
                })
              } else {
                  api.error_msg(res.msg)
              };
            }
          })
        }
      }
    })
  },

  //删除已选图片
  delImg: function(e){
    var that = this;
    var id = e.currentTarget.id;
    var upload_list = that.data.upload_list;
    var upload_del = upload_list.splice(id,1);
    that.setData({
      upload_list: upload_list
    })
  },

  //提交评论内容
  submit: function(){
    var that = this;
    var content = that.data.value
    var upload_list = that.data.upload_list
    if (content == '') {
        api.error_msg("请输入评论内容")
        return false
    }
    const _data = {
      rec_id: that.data.rec_id,
      content: content,
      imgfile: upload_list
    }
    api.fetchPost(api.https_path + 'shop/api.comment/appPost', _data, function (err, res) {
      if (res.code == 1) {
        wx.showModal({
            title: '提示',
            content: '评论成功',
            showCancel: false,
            success(res) {
                wx.navigateBack({
                    delta: 1
                })
            }
        })
      } else {
          api.error_msg(res.msg)
      };
    });
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