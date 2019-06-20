const app = getApp()
var api = require("../../common/api.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgBase: app.globalData.imgUrl,
    https_path:api.https_path,
    imgs: [],
    img:'',
    select:0,
  },
  changeImg(e) {
    const _this = this
    let imgs = _this.data.imgs;
    let id = e.currentTarget.id;
    let url = e.currentTarget.dataset.url;
    console.log(e);
    _this.setData({
      img: url,
      select:id,
    });
   
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const _this = this
    api.islogin();

    wx.showLoading({
      title: '图片生成中',
    })
    api.fetchPost(api.https_path + '/member/api.users/getHeadImg', {}, function (err, res) {
      if(res.code == 1){
        api.fetchPost(api.https_path + '/member/api.users/wechat_qrcode', { headimgurl: res.headimgurl}, function (err, res) {
          wx.hideLoading();
          _this.setData({
            imgs: res.list,
            img: res.list[0],
          })
        })
      }
    })
  },


//保存图片
saveImg:function(){
  const _this = this
  wx.downloadFile({
    url: api.https_path+_this.data.img,
    success: function (res) {
      
      //图片保存到本地
      wx.saveImageToPhotosAlbum({
        filePath: res.tempFilePath,
        success: function (data) {
          api.success.msg('保存成功',1500)
        },
        fail: function (err) {
          console.log(err);
          if (err.errMsg == "saveImageToPhotosAlbum:fail auth deny") {
            console.log("当初用户拒绝，再次发起授权")
            wx.getSetting({
              success(settingdata) {
                console.log(settingdata)
                if (settingdata.authSetting['scope.writePhotosAlbum']) {
                  wx.saveImageToPhotosAlbum({
                    filePath: res.tempFilePath,
                    success: function (data) {
                      api.success.msg('保存成功', 1500)
                    }
                  })

                  console.log('获取权限成功，给出再次点击图片保存到相册的提示。')
                } else {
                  wx.showModal({
                    title: '保存图片',
                    content: '保存图片需要您授权',
                    showCancel: true,
                    confirmText: '确定',

                    success: function (res) {
                      if (res.confirm) {
                        // 打开设置页面  
                        wx.openSetting({
                          success: function (data) {
                            if (data.authSetting['scope.writePhotosAlbum']) {
                              
                            } else {
                              console.log("授权失败");
                            }
                          },
                          fail: function (data) {
                            console.log("openSetting: fail");
                          }
                        });
                      } else if (res.cancel) {
                        console.log('用户点击取消')
                      }

                    }
                  })
                  console.log('获取权限失败，给出不给权限就无法正常使用的提示')
                }
              }
            })
          }
        },
        complete(res) {
          console.log(11);
        }
      })
    }
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
  onShareAppMessage: function (res) {
    const _this = this
    let title = '分享标题'
    let imageUrl = api.https_path+_this.data.img
    if (res.from === 'button') {
      console.log(123)
    }
    return {
      title: title,
      imageUrl: imageUrl,
      path: '/pages/index/index',
      success: function (res) {
        console.log('成功', res)
      }
    }
  }
  
})