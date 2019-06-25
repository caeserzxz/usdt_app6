// pages/addAddress/addAddress.js
const app = getApp()
var api = require("../../common/api.js")
Page({

    /**
     * 页面的初始数据
     */
    data: {
        isshowarea: 0,
        region_title: "请选择所在地区",
        province_list: [],
        province_name: '',
        province_id: '',
        city_list: [],
        city_name: '',
        city_id: '',
        area_list: [],
        area_name: '',
        area_id: '',
        name: '',
        phone: '',
        address: '',
    },

    /**
     * 显示地区选择框
     */
    showarea: function() {
        const That = this
        api.loading_msg()
        That.provincelist()
        That.setData({
            isshowarea: 1,
        })
    },

    /**
     * 隐藏地区选择框
     */
    hidearea: function() {
        const That = this
        if (That.data.province_name) {
            That.setData({
                isshowarea: 0,
                region_title: That.data.province_name + " " + That.data.city_name + " " + That.data.area_name,
            })
        } else {
            That.setData({
                isshowarea: 0,
                region_title: '请选择所在地区',
            })
        }
    },

    doaddress: function(options) {
        const That = this
        const name = options.detail.value.name
        const phone = options.detail.value.phone
        const address = options.detail.value.address
        if (name == "") {
            api.error_msg("请输入收货人姓名")
            return false
        } else if (phone == "") {
            api.error_msg("请输入收货人电话")
            return false
        } else if (address == "") {
            api.error_msg("请输入详细地址")
            return false
        } else {
            api.fetchPost(api.https_path + '/member/api.address/add', {
                province: That.data.province_id,
                city: That.data.city_id,
                district: That.data.area_id,
                regionIds: That.data.province_id + "," + That.data.city_id + "," + That.data.area_id,
                consignee: name,
                mobile: phone,
                address: address,
            }, function(err, res) {
                if (res.code == 1) {
                    wx.showModal({
                        title: '提示',
                        content: '添加成功,是否继续添加',
                        success(res) {
                            if (res.confirm) {
                                That.setData({
                                    name: '',
                                    phone: '',
                                    address: '',
                                })
                            } else if (res.cancel) {
                                wx.navigateBack({
                                    delta: 1
                                })
                            }
                        }
                    })

                } else {
                    api.error_msg(res.msg)
                }
            })
        }
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const That = this
        api.islogin()
    },

    /**
     * 省级数据输出
     */
    provincelist: function() {
        const That = this
        api.fetchPostOther(api.https_path + '/publics/api.region/getList', {
            'pid': '100000'
        }, function(err, res) {
            if (res.code == 1) {
                That.setData({
                    province_list: res.list,
                    province_name: '',
                    province_id: '',
                    city_list: [],
                    city_name: '',
                    city_id: '',
                    area_list: [],
                    area_name: '',
                    area_id: '',
                })
            }
        })
    },


    /**
     * 市级列表显示
     */
    city_list: function(options) {
        const That = this
        const region_id = options.currentTarget.dataset.region_id
        const region_name = options.currentTarget.dataset.region_name
        api.fetchPostOther(api.https_path + '/publics/api.region/getList', {
            'pid': region_id
        }, function(err, res) {
            if (res.code == 1) {
                That.setData({
                    province_name: region_name,
                    province_id: region_id,
                    province_list: [],
                    city_list: res.list,
                    area_list: [],
                    area_name: '',
                    area_id: '',
                })
            }
        })
    },

    /**
     * 区数据显示
     */
    area_list: function(options) {
        const That = this
        const region_id = options.currentTarget.dataset.region_id
        const region_name = options.currentTarget.dataset.region_name
        api.fetchPostOther(api.https_path + '/publics/api.region/getList', {
            'pid': region_id
        }, function(err, res) {
            if (res.code == 1) {
                That.setData({
                    province_list: [],
                    city_list: [],
                    city_name: region_name,
                    city_id: region_id,
                    area_list: res.list,
                    area_name: '',
                    area_id: '',
                })
            }
        })
    },

    /**
     * 选择所在区
     */
    select_area: function(options) {
        const That = this
        const region_id = options.currentTarget.dataset.region_id
        const region_name = options.currentTarget.dataset.region_name
        That.setData({
            area_name: region_name,
            area_id: region_id,
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