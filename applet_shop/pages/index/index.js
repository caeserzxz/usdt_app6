const app = getApp()
var api = require("../../common/api.js")
Page({
    data: {
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
        duration: 1000
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
        this.countdown();
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