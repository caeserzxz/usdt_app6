var countdown_time = 60
var is_send_code = 1

function sendcode(_that) {
    if (countdown_time == 1) {
        _that.setData({
            code_title: "重新获取验证码",
        })
        is_send_code = 1
        countdown_time = 60
        return false
    } else {
        countdown_time--
        _that.setData({
            code_title: "还剩于" + countdown_time + "秒",
        })
    }
    setTimeout(function() {
        sendcode(_that)
    }, 1000)
}

//模块化
module.exports = {
    countdown_time: countdown_time,
    is_send_code: is_send_code,
    sendcode: sendcode,
}