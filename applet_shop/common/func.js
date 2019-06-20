/** 
 * 时间戳格式化函数 
 * @param  {string} format    格式 
 * @param  {int}    timestamp 要格式化的时间 默认为当前时间 
 * @return {string}           格式化的时间字符串 
 */
function date(format, timestamp) {
    if (timestamp > 0) {
        var formateArr = ['Y', 'm', 'd', 'H', 'i', 's'];
        var returnArr = [];
        var date = new Date(timestamp * 1000);
        returnArr.push(date.getFullYear());
        returnArr.push(formatNumber(date.getMonth() + 1));
        returnArr.push(formatNumber(date.getDate()));
        returnArr.push(formatNumber(date.getHours()));
        returnArr.push(formatNumber(date.getMinutes()));
        returnArr.push(formatNumber(date.getSeconds()));
        for (var i in returnArr) {
            format = format.replace(formateArr[i], returnArr[i]);
        }
        return format
    } else {
        return "----"
    }
}

function formatNumber(n) {
    n = n.toString()
    return n[1] ? n : '0' + n
}

function formatTime(date) {
    var year = date.getFullYear()
    var month = date.getMonth() + 1
    var day = date.getDate()

    var hour = date.getHours()
    var minute = date.getMinutes()
    var second = date.getSeconds()


    return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

//模块化
module.exports = {
    formatTime: formatTime,
    date: date
}