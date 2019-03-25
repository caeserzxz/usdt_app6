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
function jq_ajax(url,data,callback,type,async,dataType)
{
	$('#fallr-wrapper').remove();
	if (typeof(callback) != 'undefined') async = true;
	type = (type != 'get' && type!= 'GET') ? 'POST' : type;
	async = typeof(async) == 'undefined' ? false : async;
	dataType = typeof(dataType) == 'undefined' ? 'json' : dataType;	
	if (async == false){
		$("a").blur();$(".buttons").blur();
	}
	var jq_ajax_result = new Object;	
	if (typeof(data) == 'object')
	{
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
		   jq_ajax_result.status = 0;
		   jq_ajax_result.info = '请求失败请重新尝试，多次失败请联系技术部！';
		   if (callback == '') return false;
	   	   if (typeof(callback) == 'function') return callback(jq_ajax_result);
		   if (typeof(callback) != 'undefined') return eval(callback+'(jq_ajax_result)');
	   }
     });
	
	return jq_ajax_result;
}
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
//提示框
function _alert(e,type,url){
	if (type === true){
		 G.ui.tips.suc(e,url);
	}else{
	 	G.ui.tips.info(e);
	}
}
//确定框
function _confirm(e,f){
	 G.ui.tips.confirm_flag(e,f);
}
/* *
* 区域联动
*/
function loadRegion(sel){	
	var nextsel = $("#"+sel).attr("nextsel");
	if (typeof(nextsel) == 'undefined') return false;
	nextsel = nextsel.split("|"); //字符分割  
	for (i=0;i<nextsel.length ;i++ )   {   
		$("#"+nextsel[i]+" option").each(function(){if ($(this).val() != 0) $(this).remove();});	
	}
	var p_selid = $("#"+sel).val();
	if(p_selid==0) return;	
	 var selected = 0;
	$.getJSON(regionUrl,{pid:p_selid},
		function(data){		   
			if(data.list){
				$.each(data.list,function(idx,item){	
					if (selected == 0) selected = idx;			
					$("<option value="+idx+" >"+item+"</option>").appendTo($("#"+nextsel[0]));
				});	
				loadRegion(nextsel);				
			}
		}
	);	
}
function region_sel(){
	if (!$(".region_sel").attr('id')) return false;		
	$(".region_sel").each(function(){
		var obj = this;
		var sel_val = $(this).attr("sel_val");
		if (typeof(sel_val) != 'undefined'){
			sel_val = sel_val.split("|"); //字符分割
			if (sel_val[0] == 0) return false;
			$.getJSON(regionUrl,{pid:sel_val[0]},
				function(data){
					if(data.list){
						$.each(data.list,function(idx,item){
							$("<option value="+idx+">"+item+"</option>").appendTo(obj);
						});	
						if (sel_val[1] > 0 ){ 
							$(obj).val(sel_val[1]);
						}else{
							 $(obj).find('option:first').attr('selected','selected');
							 loadRegion(obj.id);
						}
					}
				}
			);
		}
	});
	$(".region_sel").unbind('change').bind('change',function(){loadRegion(this.id);});
}
$(function(){region_sel();}); 

//搜索用户
function searchuser(keyword,selects) {
	var user_keyword = $("#"+keyword).val();  	
	$("#"+selects+" option").each(function(){if ($(this).val() != 0) $(this).remove();});	
	var res = jq_ajax(searchUserUrl, 'keyword='+user_keyword);	
	if (res.msg)  _alert(res.msg);
	if (res.code == 0) return false	
    var arr = res.list;
	$.each(res.list,function(i,val){
		$("#"+selects).append("<option value='"+val.user_id+"'>"+val.user_id+'-'+val.mobile+'-'+val.nick_name+"</option>");
	}); 
	   
}
//搜索商品
function searchgoods(keyword,selects) {
	var keyword = $("#"+keyword).val();  	
	$("#"+selects+" option").each(function(){if ($(this).val() != 0) $(this).remove();});	
	var res = jq_ajax(searchGoodsUrl, 'keyword='+keyword);	
	if (res.msg)  _alert(res.msg);
	if (res.code == 0) return false	
    var arr = res.list;
	$.each(res.list,function(i,val){
		$("#"+selects).append("<option value='"+val.goods_id+"' data-goods_name='"+val.goods_name+"'>"+(val.is_spec==1?'多规格':val.goods_sn)+':'+val.goods_name+"</option>"); 
	}); 
	   
}

$(document).on('click','.js_radio_undertake',function(){
	$('.radio_undertake_'+$(this).attr('name')).addClass('hide');
	
	$.each($(this).data('class').split('|'),function(i,v){
		$('.'+v).removeClass('hide');
	})
	
	
})

//全选定义
$(document).on('click','.checkboxAll',function(){
	var input_name = $(this).data("name");
	if ($(this).is(':checked')){
        $('input[name="'+input_name+'"]').each(function(){
            $(this).attr("checked",true);
        });
    }else{
        $('input[name="'+input_name+'"]').each(function(){
            $(this).attr("checked",false);
        });
	}
})