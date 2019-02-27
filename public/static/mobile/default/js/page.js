(function (doc, win) {
    var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function () {
            var clientWidth = docEl.clientWidth;
            if(clientWidth > 750){
                clientWidth = 750;
            }
            if (!clientWidth) return;
            docEl.style.fontSize = 75 * (clientWidth / 375) + 'px';
        };

    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);

  var color = [
    {
      main: '#2ac5ff'
    },
    {
      main: '#5FB878'
    },
    {
      main: '#03152A'
    }
  ];

  var curMainColor = color[0].main;

  var theme = function () {
    var body = document.getElementsByTagName("body")[0];
    var style = document.createElement('style');
    style.setAttribute("type", "text/css");
    style.setAttribute("id", "themeColor");
    var styleStr =
      '.weui-btn_primary{background-image: linear-gradient(250deg, ' + curMainColor + ' 0%, ' + curMainColor + ' 100%), linear-gradient(' + curMainColor + ', ' + curMainColor + ');}' +
      '.weui-navbar__item.weui-bar__item--on{color: ' + curMainColor + '}' +
      '.weui-navbar__item.weui-bar__item--on:after{background-color: ' + curMainColor + '; box-shadow: none;}' +
      '.text-blue{ color: ' + curMainColor + '}' +
      '.inline-btn{ border-color: ' + curMainColor + '; background-color:#fff;}' +
      '.code_block .copybtn_box .inline-btn{color: ' + curMainColor + '}' +
      '.weui-dialog__btn{color: ' + curMainColor + '}' +
      '.my_qbtopbg,.page-hd_imgbg,.qbtopbg{ background-color: ' + curMainColor + '}' +
      '.qb_addbtn::after, .qb_addbtn::before{background-color: ' + curMainColor + '}' +
      '.bottom-tabbar a.active .label{ color: ' + curMainColor + '}' +
      '.weui-switch:checked, .weui-switch-cp__input:checked ~ .weui-switch-cp__box{ border-color: ' + curMainColor + '; background-color: ' + curMainColor + '}' +
      '.icon_cp_b{ color: ' + curMainColor + '}' +
      '.bottom-tabbar a.active .icon:before{ color: ' + curMainColor + '}' +
      '.inline-btn.tj_btn{  background-color: '+curMainColor+'}';
    style.innerHTML = styleStr;
    body.appendChild(style);
  }
  window.onload = function () {
   //  theme();
  }

})(document, window);

//弹出窗口定义
function _alert(title,fun){
	var obj = $('.alertBox');
	
	obj.find('.text').html(title);	
	if (typeof(fun) == 'undefined'){
		obj.find('.button').css('display','flex').click(function(){
			obj.hide();
		});
		obj.find('.buttonBox').hide();
	}else if (typeof(fun) == 'string'){
		obj.find('.button').css('display','flex').click(function(){
			window.location.href = fun;
		});	
		obj.find('.buttonBox').css('display','none');
		
	}else{
		obj.find('button').hide();
		obj.find('buttonBox').show();
		obj.find('.cancel').click(function(){
			obj.hide();
		});
		obj.find('.confirm').click(function(){
			obj.hide();
			return fun();
		})
	}
	obj.show();	
}
/* *
* 调用此方法发送HTTP请求。
*
* @public
* @param   {string}    url           请求的URL地址
* @param   {mix}       data          发送参数
* @param   {Function}  callback      回调函数
* @param   {string}    type          请求的方式，有"GET"和"POST"两种
* @param   {boolean}   asyn          是否异步请求的方式,true：异步，false：同步,没有回调函数必须同步否则将发生错误
* @param   {string}    dataType      响应类型，有"JSON"、"XML"和"TEXT"三种
* iqgmy
*/
function jq_ajax(url,data,callback,type,async,dataType){
	if (typeof(callback) != 'undefined') async = true;
	type = (type != 'get' && type!= 'GET') ? 'POST' : type;
	async = typeof(async) == 'undefined' ? false : async;
	dataType = typeof(dataType) == 'undefined' ? 'json' : dataType;	

	var jq_ajax_result = new Object;	
	if (typeof(data) == 'object'){
		var date_str = '';
		for(var key in data ) date_str += key+'='+encodeURIComponent(data[key])+'&';		
		data = date_str;
	}
	$.ajax({
       url:  url,
       type: type,
       data: data,
       dataType: dataType,
	   async: async,
       success: function(result){
		   jq_ajax_result = result;		  
		   if (callback == '') return false;
	   	   if (typeof(callback) == 'function') return callback(result);
		   if (typeof(callback) != 'undefined') return eval(callback+'(result)');
       },
	   error: function(){
		   jq_ajax_result.code = 0;
		   jq_ajax_result.msg = '网络异常，请重新尝试.';
		   if (callback == '') return false;
	   	   if (typeof(callback) == 'function') return callback(jq_ajax_result);
		   if (typeof(callback) != 'undefined') return eval(callback+'(jq_ajax_result)');
	   }
     });
	
	return jq_ajax_result;
}
var isContainer = false;
//侧滑显示
function container(obj) {
	//侧滑显示删除按钮
	var expansion = null; //是否存在展开的list
	var container = document.querySelectorAll(obj);
	for (var i = 0; i < container.length; i++) {
		var x, y, X, Y, swipeX, swipeY;
		container[i].addEventListener('touchstart', function (event) {
			x = event.changedTouches[0].pageX;
			y = event.changedTouches[0].pageY;
			swipeX = true;
			swipeY = true;
			if (expansion) {   //判断是否展开，如果展开则收起
				expansion.className = "";
			}
		});
		container[i].addEventListener('touchmove', function (event) {
			isContainer = true;
			X = event.changedTouches[0].pageX;
			Y = event.changedTouches[0].pageY;
			// 左右滑动
			if (swipeX && Math.abs(X - x) - Math.abs(Y - y) > 0) {
				// 阻止事件冒泡
				event.stopPropagation();
				if (X - x > 1) {   //右滑
					event.preventDefault();
					this.className = "";    //右滑收起
				}
				if (x - X > 1) {   //左滑
					event.preventDefault();
					this.className = "swipeleft";   //左滑展开
					expansion = this;
				}
				swipeY = false;
			}
			// 上下滑动
			if (swipeY && Math.abs(X - x) - Math.abs(Y - y) < 0) {
				swipeX = false;
			}
		});
	}
}
//整合表单数组
$.fn.toJson = function() {
	var arrayValue = $(this).serializeArray();
	var json = {};
	$.each(arrayValue, function() {
		var item = this;
		if (json[item["name"]]) {
			json[item["name"]] += "," + item["value"];
		} else {
			json[item["name"]] = item["value"];
		}
	});
	return json;
};
//计数函数
var countTxtSw = false;
var countTxtStatus = true;
function countTxtEvent(_txt,_txtNum,length){
	var txt = document.getElementById(_txt);
    var txtNum = document.getElementById(_txtNum);
	
	txt.addEventListener("keyup", function(){
		if(countTxtSw == false){
			countTxt(txt,txtNum,length);
		}
	});
	txt.addEventListener("compositionstart", function(){
		countTxtSw = true;
	});
	txt.addEventListener("compositionend", function(){
		countTxtSw = false;
		countTxt(txt,txtNum,length);
	});
}
function countTxt(txt,txtNum,length){
	if(countTxtSw == false&&txt.value.length<=length){        //只有开关关闭时，才赋值
		txtNum.textContent = txt.value.length+'/'+length;
	}else{
	  countTxtStatus = false;
	  txtNum.textContent = length+'/'+length
	  txtNum.style.color = '#F65236';
	}
}



//判断是否存在画布
function isCanvasSupported() {
	var elem = document.createElement('canvas');
	return !!(elem.getContext && elem.getContext('2d'));
}
 
//压缩方法
function compress(event, callback) {
	if ( typeof (FileReader) === 'undefined') {
		console.log("当前浏览器内核不支持base64图标压缩");
		//调用上传方式  不压缩
	} else {
		try {
			var file = event.currentTarget.files[0];
			 if(!/image\/\w+/.test(file.type)){   
        			alert("请确保文件为图像类型");  
        			return false;  
     		 } 
			var reader = new FileReader();
			reader.onload = function (e) {
			var image = $('<img/>');
			image.load(function () {
			console.log("开始压缩");
			var square = 700;
			var canvas = document.createElement('canvas');
			canvas.width = square;
			canvas.height = square;
			var context = canvas.getContext('2d');
		    context.clearRect(0, 0, square, square);
			var imageWidth;
			var imageHeight;
			var offsetX = 0;
			var offsetY = 0;
			if (this.width > this.height) {
		      imageWidth = Math.round(square * this.width / this.height);
		      imageHeight = square;
		      offsetX = - Math.round((imageWidth - square) / 2);
			} else {
		      imageHeight = Math.round(square * this.height / this.width);
		      imageWidth = square;
		      offsetY = - Math.round((imageHeight - square) / 2);
			}
			context.drawImage(this, offsetX, offsetY, imageWidth, imageHeight);
			var data = canvas.toDataURL('image/jpeg');
			 	//压缩完成执行回调
		     	callback(data);
			});
			image.attr('src', e.target.result);
			};
			reader.readAsDataURL(file);
		} catch(e) {
			console.log("压缩失败!");
			//调用上传方式  不压缩
		}
	}
}