var countdown_time = 10
var is_send_code = 1

function sendcode(_that) {
    if (countdown_time == 1) {
        _that.setData({
            code_title: "重新获取验证码",
        })
        is_send_code = 1
        countdown_time = 10
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

function new_time() {
    var myDate = new Date();
    myDate.getYear(); //获取当前年份(2位)
    myDate.getFullYear(); //获取完整的年份(4位,1970-????)
    var getMonth = myDate.getMonth(); //获取当前月份(0-11,0代表1月)
    myDate.getDate(); //获取当前日(1-31)
    myDate.getDay(); //获取当前星期X(0-6,0代表星期天)
    myDate.getTime(); //获取当前时间(从1970.1.1开始的毫秒数)
    var getHours = myDate.getHours(); //获取当前小时数(0-23)
    myDate.getMinutes(); //获取当前分钟数(0-59)
    var getSeconds = myDate.getSeconds(); //获取当前秒数(0-59)
    myDate.getMilliseconds(); //获取当前毫秒数(0-999)
    if (getMonth < 10) {
        getMonth = '0' + getMonth
    }
    if (getHours < 10) {
        getHours = '0' + getHours
    }
    if (getSeconds < 10) {
        getSeconds = '0' + getSeconds
    }
    var newtime = myDate.getFullYear() + '-' + getMonth + '-' + myDate.getDate() + ' ' + getHours + ':' + myDate.getMinutes() + ':' + getSeconds;
    return newtime;
}


//模块化
module.exports = {
    new_time: new_time,
    countdown_time: countdown_time,
    is_send_code: is_send_code,
    sendcode: sendcode,
}