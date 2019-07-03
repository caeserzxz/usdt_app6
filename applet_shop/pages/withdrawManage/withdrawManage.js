// pages/withdrawManage/withdrawManage.js
const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        panelShow: false,
        offsetRecord: {
            'index': -1,
            'startX': 0,
            'offset': 0,
            'direction': null
        }, // 偏移记录
        deleteButtonWidth: 120, // 删除按钮的宽度(rpx)
        pixelScale: 1,
        list: [],
        nodata: "",
        code_bank: [],
        imgurl: api.https_path,
    },
    openPanle() {
        this.setData({
            panelShow: true
        });
    },
    addbank() {
        wx.navigateTo({
            url: '/pages/addBank/addBank',
        })
    },
    addzfb() {
        wx.navigateTo({
            url: '/pages/zfbAccount/zfbAccount',
        })
    },
    closePanle() {
        this.setData({
            panelShow: false
        });
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const _this = this
        api.islogin()
        _this.loadlist()
    },

    loadlist: function() {
        const _this = this
        wx.showLoading({
            title: '数据加载中',
        })
        api.fetchPost(api.https_path + '/member/api.withdraw/getlist', {}, function(err, res) {
            // console.log(res)
            wx.hideLoading()
            if (res.code == 1) {
                if (res.list.length > 0) {
                    _this.setData({
                        nodata: "加载到数据了",
                        list: res.list
                    })
                }
            }
        })
        api.fetchPost(api.https_path + '/publics/api.index/get_bank', {}, function(err, res) {
            wx.hideLoading()
            if (res.code == 1) {
                _this.setData({
                    code_bank: res.code_bank
                })
            }
        })
    },



    /**
     * 表格cell触摸开始事件
     */
    onTableCellTouchStart: function(event) {
        // console.log(event);
        const That = this
        if (event.changedTouches.length != 1) return;
        let index = event.currentTarget.dataset.index;
        let x = event.changedTouches[0].clientX;
        let offset = 0;
        if (That.data.offsetRecord != null && That.data.offsetRecord.index == index) {
            offset = That.data.offsetRecord.offset;
        }
        That.setData({
            offsetRecord: {
                'index': index,
                'startX': x,
                'offset': offset,
                'direction': null
            }
        });
    },
    /**
     * 表格cell触摸移动事件
     */
    onTableCellTouchMove: function(event) {
        const That = this
        if (event.changedTouches.length != 1) return;
        let index = event.currentTarget.dataset.index;
        let record = That.data.offsetRecord;
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
            record.offset = Math.min((startX - clientX) * That.data.pixelScale, That.data.deleteButtonWidth);
        } else { // 👉滑动
            if (record.offset > 0) {
                record.offset = Math.max(That.data.deleteButtonWidth - Math.abs(clientX - startX) * That.data.pixelScale, 0);
            } else {
                record = null;
            }
        }
        That.setData({
            offsetRecord: record
        });
    },
    /**
     * 表格cell触摸结束事件
     */
    onTableCellTouchEnd: function(event) {
        const That = this
        if (event.changedTouches.length != 1) return;
        let index = event.currentTarget.dataset.index;
        let record = That.data.offsetRecord;
        if (record != null && index == Reflect.get(record, 'index')) {
            let offset = Reflect.get(record, 'offset');
            if (offset >= That.data.deleteButtonWidth / 2) {
                Reflect.set(record, 'offset', That.data.deleteButtonWidth);
            } else {
                record = null;
            }
            That.setData({
                offsetRecord: record
            });
        }
    },
    delaccount: function(e) {
        const _this = this
        wx.showModal({
            content: '确认删除该账号？',
            success(res) {
                if (res.confirm) {
                    // console.log(e)
                    let account_id = e.currentTarget.dataset.id
                    api.fetchPost(api.https_path + '/member/api.withdraw/delaccount', {
                        account_id: account_id
                    }, function(err, res) {

                        if (res.code == 1) {
                            _this.setData({
                                offsetRecord: {
                                    'index': -1,
                                    'startX': 0,
                                    'offset': 0,
                                    'direction': null
                                }, // 偏移记录
                            })
                            _this.loadlist()
                        }
                    })
                } else if (res.cancel) {
                    console.log('用户点击取消')
                }
            }
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
    // onShareAppMessage: function() {

    // }
})