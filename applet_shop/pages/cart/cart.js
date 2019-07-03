// pages/cart/cart.js
const app = getApp()
var util = require("../../utils/util.js")
var api = require("../../common/api.js")
var md5 = require("../../common/md5.js")
var sms = require("../../common/smscode.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        selectAllStatus: false, // 全选状态，默认全选
        totalPrice: 0.00, // 总价，初始为0
        integerPrice: 0, //整数价格
        decimalPrice: '00', //小数价格
        settlementNum: 0, //结算数量
        allGoodsNum: 0, //商品总数量
        cartlist: [], //购物车商品列表
        number_control: false, //加减控制
        offsetRecord: {
            'index': -1,
            'startX': 0,
            'offset': 0,
            'direction': null
        }, // 偏移记录
        deleteButtonWidth: 150, // 删除按钮的宽度(rpx)
        pixelScale: 1,
        selectAll: false,
        scrollY: true,
        imgUrl: getApp().globalData.imgUrl,
        edit: 1
    },


    onLoad: function(options) {
        let _this = this;
    },


    loadcartInfo: function(action, data) {
        const _this = this
        _this.setData({
            offsetRecord: {
                'index': -1,
                'startX': 0,
                'offset': 0,
                'direction': null
            },
        })
        let loadurl = api.https_path + 'shop/api.Flow/' + action;
        let _data = data

        api.fetchPost(loadurl, _data, function(err, res) {

            if (res.code == 1) {
                let isAllSel;
                if (res.cartInfo.isAllSel == 1) {
                    isAllSel = true;
                } else {
                    isAllSel = false;
                }

                if (res.cartInfo.goodsList) {
                    _this.setData({
                        cartlist: res.cartInfo.goodsList,
                    })
                }
                _this.setData({
                    allGoodsNum: res.cartInfo.allGoodsNum,
                    integerPrice: res.cartInfo.exp_total[0],
                    decimalPrice: res.cartInfo.exp_total[1],
                    settlementNum: res.cartInfo.buyGoodsNum,
                    selectAllStatus: isAllSel,
                    number_control: true,
                })
            }

        })

    },



    /**
     * 表格cell触摸开始事件
     */
    onTableCellTouchStart: function(event) {

        if (event.changedTouches.length != 1) return;
        let index = event.currentTarget.dataset.index;
        let x = event.changedTouches[0].clientX;
        let offset = 0;
        if (this.data.offsetRecord != null && this.data.offsetRecord.index == index) {
            offset = this.data.offsetRecord.offset;
        }
        this.setData({
            offsetRecord: {
                'index': index,
                'startX': x,
                'offset': offset,
                'direction': null
            }
        });
    },
    onEdit(e) {
        this.setData({
            edit: e.target.dataset.index
        })
    },
    /**
     * 表格cell触摸移动事件
     */
    onTableCellTouchMove: function(event) {
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
        this.setData({
            offsetRecord: record
        });
    },

    /**
     * 表格cell触摸结束事件
     */
    onTableCellTouchEnd: function(event) {
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
            this.setData({
                offsetRecord: record
            });
        }
    },

    /**
     * 表格cell删除按钮点击事件
     */
    onDeleteButtonTapped: function(event) {
        const _this = this;
        let rec_id = event.currentTarget.dataset.rec_id;

        wx.showModal({
            content: `确定要删除吗？`,
            showCancel: true,
            success: (res) => {
                if (!res.confirm) return;
                let data = {
                    rec_id: rec_id,
                }

                _this.loadcartInfo('delGoods', data);
            }
        });
    },
    //单选事件
    selectList(e) {
        let _this = this;
        // 获取选中的radio索引
        let index = e.currentTarget.dataset.index;
        // 获取到商品列表数据
        let list = _this.data.cartlist;
        let is_select = list[index].is_select;
        let rec_id = list[index].rec_id;
        let select;
        if (is_select == 1) {
            select = 0;
        } else {
            select = 1;
        }
        let data = {
            rec_id: rec_id,
            is_select: select
        }
        _this.loadcartInfo('setSel', data)
    },


    //全选事件
    selectAll(e) {
        let _this = this;
        let is_select = 0;

        // 默认全选
        _this.data.selectAllStatus = !_this.data.selectAllStatus;

        if (_this.data.selectAllStatus == true) {
            is_select = 1;
        }

        let data = {
            rec_id: 'all',
            is_select: is_select
        }
        _this.loadcartInfo('setSel', data)

    },
    /**
     * 绑定加数量事件
     */
    btn_add(e) {
        const _this = this
        let number_control = _this.data.number_control;
        if (number_control) {
            _this.setData({
                number_control: false,
            })
            // 获取点击的索引
            const index = e.currentTarget.dataset.index;
            // 获取商品数据
            let list = _this.data.cartlist;
            // 获取商品数量
            let num = list[index].goods_number;
            // 点击递增
            num = num + 1;
            list[index].goods_number = num;

            let rec_id = list[index].rec_id
            let data = {
                rec_id: rec_id,
                num: num
            }
            _this.loadcartInfo('editNum', data)
        }

    },



    /**
     * 绑定减数量事件
     */
    btn_minus(e) {
        const _this = this

        let number_control = _this.data.number_control;
        if (number_control) {
            _this.setData({
                number_control: false,
            })

            // 获取点击的索引
            const index = e.currentTarget.dataset.index;

            // 获取商品数据
            let list = _this.data.cartlist;


            // 获取商品数量
            let num = list[index].goods_number;
            // 判断num小于等于1  return; 点击无效
            if (num <= 1) {
                _this.setData({
                    number_control: true,
                })
                return false;
            }
            // else  num大于1  点击减按钮  数量--
            num = num - 1;

            let rec_id = list[index].rec_id
            let data = {
                rec_id: rec_id,
                num: num
            }
            _this.loadcartInfo('editNum', data)
        }
    },

    /**
     * 删除所选商品
     */
    delSelGoods: function() {
        const _this = this
        let goodsList = _this.data.cartlist;

        let rec_ids = [];
        //提取选中的商品
        Object.keys(goodsList).forEach(function(key) {
            if (goodsList[key].is_select == 1) {
                rec_ids.push(goodsList[key].rec_id);
            }
        });

        console.log(rec_ids);
        if (rec_ids.length == 0) {
            api.error_msg('请选择要删除的商品', 1000);
            return;
        }
        let data = {
            recids: rec_ids.join(','),
        }


        _this.loadcartInfo('delSelGoods', data)

    },


    goshop: function() {
        wx.switchTab({
            url: '/pages/index/index',
        })
    },

    checkout: function() {
        wx.navigateTo({
            url: '/pages/payment/payment',
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
        const _this = this
        let data = {};
        _this.loadcartInfo('getList', data);
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

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    }
})