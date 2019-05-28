<?php

namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 物流信息
/*------------------------------------------------------ */
class ShippingLogModel extends BaseModel
{
	protected $table = 'shop_shipping_log';
	public  $pk = 'order_id';
	protected $mkey = 'shipping_log_list';
	/*---------------------------------------------------- */
	//-- 列表
	/*------------------------------------------------------ */
	public function getInfo(&$orderInfo){
		$shop_shippping_view_fun = settings('shop_shippping_view_fun');
		if (empty($shop_shippping_view_fun)) return '暂无数据.';
		$info = Cache::get($this->mkey.$orderInfo['order_id']);
		if (empty($info) == false) return $info;
		$info = $this->where('order_id',$orderInfo['order_id'])->find();
		if (empty($info) == false){
			$info = $info->toArray();	
		}
		if ($orderInfo['shipping_status'] == 1){
			 $fun = str_replace('/','\\','/shipping/'.$shop_shippping_view_fun);
       	 	 $Class = new $fun();
			 $res = $Class->getLog($orderInfo['shipping_code'],$orderInfo['invoice_no'],$orderInfo['mobile']);
			 if ($res['code'] == 0) return $res['msg']; 
			
			 if (empty($info)== false){
				 $info['data'] = $res['data'];
				 $this->where('order_id',$orderInfo['order_id'])->update(['data'=>json_encode($res['data'],JSON_UNESCAPED_UNICODE),'update_time'=>time()]);
			 }else{
				 $info['order_id'] = $orderInfo['order_id'];
				 $info['data'] = $res['data'];
				 $this->save(['order_id'=>$orderInfo['order_id'],'data'=>json_encode($res['data'],JSON_UNESCAPED_UNICODE),'update_time'=>time()]);
			 }			
		}else{
			$info['data'] = json_decode($info['data'],true);
		}
		Cache::set($this->mkey.$orderInfo['order_id'],$info,300);
		return $info;
	}


}
