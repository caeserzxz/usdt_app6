// pages/sign/sign.js
const app = getApp()
var api = require("../../common/api.js")
Page({
    /**
     * 页面的初始数据
     */
    data: {
        isSign: false,
        year: '',
        month: '',
        day: '',
        dateArr: [],
        firstDay: '',
        lastDay: '',
        isSameDay: [], //历史签到时间戳
        cycleArr: [], //环形签到日期
        dayString: new Date().getTime(), //当天时间戳
        modelType: false
    },
    getDate: function() { //获取当月日期
        let mydate = new Date(this.data.dayString);
        let year = mydate.getFullYear();
        let month = mydate.getMonth() + 1;
        let day = mydate.getDate();
        this.setData({
            year: year,
            month: month,
            day: day,
        })
    },
    //画日历
    setDate: function() {
        let Year = this.data.year; //年
        let Month = this.data.month; //月
        let firstDay = new Date(Year, Month - 1, 1).getDay();
        let Arr = [];
        let LastDay = new Date(Year, Month, 0).getDate()
        let isSame = false
        let isSameDay = this.data.isSameDay
        //用当月第一天在一周中的日期值作为当月离第一天的天数
        let tianNums = new Date(Year, Month - 1, 0).getDate();

        for (let i = 1; i <= firstDay; i++) {
            Arr.unshift({
                date: tianNums - i + 1
            });
        }
        //用当月最后一天在一个月中的日期值作为当月的天数
        for (var i = 1; i < LastDay + 1; i++) {
            for (var ii = 0; ii < isSameDay.length; ii++) {
                //是否签到
                isSame = this.IsSame(Year, Month, i, isSameDay[ii])
                if (isSame) {
                    break
                }
            }
            Arr.push({
                'date': i,
                'isSame': isSame
            });
        }
        //当月最后一天未满7天
        for (let i = 1, lastDayWeek = new Date(Year, Month - 1, LastDay).getDay(); i < 7 - lastDayWeek; i++) {
            Arr.push({
                date: i
            });
        }
        this.setData({
            dateArr: Arr,
            firstDay: firstDay,
            lastDay: firstDay + LastDay
        })
    },
    prevMonth: function() { //上一月
        var d = new Date(this.data.year, this.data.month - 2, 1);
        this.setData({
            year: d.getFullYear(),
            month: d.getMonth() + 1
        })
        this.setDate()
    },
    nextMonth: function() { //下一月
        var d = new Date(this.data.year, this.data.month, 1);
        this.setData({
            year: d.getFullYear(),
            month: d.getMonth() + 1
        })
        this.setDate()
    },
    //是否签到
    IsSame: function(Year, Month, Day, d2) {
        d2 = new Date(d2 * 1000);
        return (Year == d2.getFullYear() && Month == d2.getMonth() + 1 && Day == d2.getDate());
    },
    //签到
    doSign: function() {
        let that = this
        let dayString = that.data.dayString
        let Arr = that.data.isSameDay;
        let dateArr = that.data.dateArr
        let dayIndex = new Date(dayString * 1000).getDate() + that.data.firstDay
        dateArr[dayIndex - 1].isSame = true
        Arr.push(dayString)
        if (!that.data.isSign) {
            api.fetchPost(api.https_path + 'member/api.Center/signIng', [], function (err, res) {
                // console.log(res);
                if (res.code == 1) {
                    var isSign = res.is_sign == 1?true:false;
                    that.setData({
                        isSign: isSign,
                    })
                    that.signIndex(that);
                }
            });
            that.setData({
                isSign: true,
                isSameDay: Arr,
                dateArr: dateArr,
                modelType: true
            })
        } else {
            api.error_msg("今天已经签到过！")
        }
    },
    //获得礼物
    getAward: function() {
        if (this.data.isSameDay.length == 7) {
            wx.showToast({
                title: '获得一份礼品',
                icon: 'none',
                duration: 2000
            })
        } else {
            wx.showToast({
                title: '签到未满7天',
                icon: 'none',
                duration: 2000
            })
        }
    },
    // 隐藏签到的积分
    closeModel: function() {
        this.setData({
            modelType: false
        })
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const that = this
        api.islogin();
        that.signIndex(that);
    },
    //获取签到信息
    signIndex: function (that) {
        api.fetchPost(api.https_path + 'member/api.Center/signIndex', [], function (err, res) {
            console.log(res);
            if (res.code == 1 && res.signData) {
                var isSign = res.is_sign == 1?true:false;
                that.setData({
                    isSign: isSign,
                    cycleArr: res.signTime,
                    dayString: res.timeData*1000,
                    isSameDay: res.signData,
                    integral: res.use_integral
                })
            }
            that.getDate();
            that.setDate();
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
    // onShareAppMessage: function() {

    // }
})