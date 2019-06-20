// pages/set/set.js
const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        page: 1,
        timeY: 0,
        timeM: 0,
        timeDate: '',
        postType: 1
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const that = this
        api.islogin();

        var date = new Date();
        var Y = date.getFullYear();
        var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
        var timeDate = Y + '年' + M + '月';
        that.setData({
            timeDate: timeDate,
            timeY: Y,
            timeM: M,
            time: new Date().getTime()
        })

        that.signInfo(that);
    },
    //获取签到信息
    signInfo: function (that) {
        var page = that.data.page
        const _data = {
            page: that.data.page,
            timeY: that.data.timeY,
            timeM: that.data.timeM,
        }
        api.fetchPost(api.https_path + 'member/api.Center/signInfo', _data, function (err, res) {
            console.log(res);
            if (page==1) {
                that.setData({ 
                    info: [],
                    postType: 1
                });
            };
            var info = that.data.info;
            if (res.code == 1 && res.info) {
                var infos = res.info;
                for (var i = 0; i < parseInt(infos.length); i++) {
                    if(info.indexOf(infos[i]) == -1){
                        info.push(infos[i])
                    } 
                }
            }
            var postType = res.info.length<10?0:1;
            console.log(page);
            that.setData({
                info: info,
                data: res.data,
                postType: postType
            })
        });
    },
    // 切换月份
    bindDateChange: function(e) {
        var that = this
        let time = e.detail.value.split('-');
        var timeDate = time[0] + '年' + time[1] + '月';
        that.setData({
            timeDate: timeDate,
            timeY: time[0],
            timeM: time[1],
            page: 1
        })
        that.signInfo(that);
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        var that = this
        var time = that.data.time;
        var getTime = new Date().getTime();
        var postType = that.data.postType;
        if (getTime-time >= 1000 && postType == 1) {
            console.log(123)
            var page = that.data.page
            page++
            that.setData({ 
                page: page,
                time: getTime
            })
            that.signInfo(that)
        };
    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    }
})