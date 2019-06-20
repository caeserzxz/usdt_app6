// pages/saleAfter/saleAfter.js
const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        goods_name: '',
        goods_pic: '',
        sku_val: '',
        sku_name: '',
        imgUrl: api.https_path,
        service_tag_value: 1,
        numval: 1,
        goodsnum: 1, //商品数量
        refund_reason_title: false,
        refund_reason_value: '',
        refund_amount: 0.00.toFixed(2),
        images_list: [],
        order_sn: '',
        order_id: 0,
        rec_id: 0,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const That = this
        api.islogin()
        That.setData({
            order_sn: options.order_sn,
            order_id: options.order_id,
            rec_id: options.rec_id,
        })
        const _data = {
            order_sn: options.order_sn,
            order_id: options.order_id,
            rec_id: options.rec_id,
        }
        That.loadinfo(_data)
    },

    dosaleafter: function() {
        const That = this
        const goodsnum = That.data.goodsnum
        const images_list = That.data.images_list
        const order_sn = That.data.order_sn
        const order_id = That.data.order_id
        const rec_id = That.data.rec_id
        const refund_amount = That.data.refund_amount
        const refund_reason_value = That.data.refund_reason_value
        if (goodsnum <= 0) {
            api.error_msg("请选择退货数量")
            return false
        }
        if (refund_reason_value == "") {
            api.error_msg("请输入退款原因")
            return false
        }
        const _data = {
            sku_val: That.data.sku_val,
            sku_name: That.data.sku_name,
            goods_name: That.data.goods_name,
            goods_pic: That.data.goods_pic,
            saleafter_type: 1,
            goodsnum: goodsnum,
            images_list: images_list,
            order_sn: order_sn,
            order_id: order_id,
            rec_id: rec_id,
            refund_amount: refund_amount,
            refund_reason_value: refund_reason_value
        }
        api.fetchPost(api.https_path + '/shop/api.order/dosaleaftergoods', _data, function(err, res) {
            if (res.code == 1) {
                wx.showModal({
                    title: '提示',
                    content: '申请成功',
                    showCancel: false,
                    success(res) {
                        wx.navigateBack({
                            delta: 1
                        })
                    }
                })
            } else {
                api.error_msg(res.msg)
            }
        })
    },

    loadinfo: function(_data) {
        const That = this
        api.fetchPost(api.https_path + '/shop/api.order/saleaftergoodsdetail', _data, function(err, res) {
            if (res.code == 1) {
                console.log(res.orderinfo.goodsinfo)
                const goodsinfo = res.orderinfo.goodsinfo
                That.setData({
                    sku_val: goodsinfo.sku_val,
                    sku_name: goodsinfo.sku_name,
                    goodsinfo: goodsinfo,
                    goods_name: goodsinfo.goods_name,
                    goods_pic: goodsinfo.pic,
                    goodsnum: goodsinfo.goods_number,
                    orderinfo: res.orderinfo.orderinfo,
                    refund_amount: res.orderinfo.refund_amount,
                })
            } else {
                api.error_msg(res.msg)
            }
            //console.log(res)
        })
    },

    /**
     * 上传图片
     */
    uploaderimages: function(e) {
        const _this = this
        wx.chooseImage({
            count: 1,
            sizeType: ['original', 'compressed'], //可选择原图或压缩后的图片
            sourceType: ['album', 'camera'], //可选择性开放访问相册、相机
            success: res => {
                // const images = this.data.images.concat(res.tempFilePaths)
                // 限制最多只能留下1张照片
                let tempFilePaths = res.tempFilePaths
                api.loading_msgs("正在上传...")
                wx.uploadFile({
                    url: api.https_path + '/publics/api.index/uploaderimages', // 仅为示例，非真实的接口地址
                    filePath: tempFilePaths[0],
                    name: 'file',
                    formData: {},
                    success(res) {
                        wx.hideToast();
                        let data = JSON.parse(res.data)
                        if (data.code == 1) {
                            wx.hideLoading()
                            console.log(data)
                            const images_list = _this.data.images_list
                            images_list.push(api.https_path + data.src_thumb)
                            _this.setData({
                                images_list: images_list
                            })
                        } else {
                            api.err_msg('上传失败，稍后再试！')
                        }
                    }
                })

            }
        })
    },

    /**
     * 删除图片
     */
    deleteimages: function(options) {
        const That = this
        const imagesindex = options.currentTarget.dataset.imagesindex
        const images_list = That.data.images_list.filter((ele, index) => {
            return index != imagesindex
        })
        That.setData({
            images_list: images_list
        })
    },

    /**
     * 输入退款原因
     */
    refundreason(e) {
        const That = this
        if (e.detail.value) {
            That.setData({
                refund_reason_value: e.detail.value,
                refund_reason_title: true
            })
        } else {
            That.setData({
                refund_reason_value: '',
                refund_reason_title: false
            })
        }
    },

    /**
     * 减掉商品数量
     */
    minus: function(e) {
        const That = this
        let val = That.data.numval
        if (val <= 1) {
            return
        }
        val = val - 1
        That.setData({
            numval: val
        })
    },

    /**
     * 添加商品数量
     */
    add: function(e) {
        const That = this
        const val = parseInt(That.data.numval) + 1
        const goodsnum = That.data.goodsnum
        if (val > goodsnum) {
            That.setData({
                numval: goodsnum
            })
        } else {
            That.setData({
                numval: val
            })
        }

    },
    /**
     * 选择服务类型
     */
    bindservicetag(e) {
        const That = this
        That.setData({
            service_tag_value: e.currentTarget.dataset.index
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

    }
})