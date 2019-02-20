<?php
namespace app\shop\controller\api;
use app\ApiController;

use app\shop\model\OrderModel;
use app\shop\model\ShippingLogModel;
use app\shop\model\ShippingModel;
/*------------------------------------------------------ */
//-- 快递相关API
/*------------------------------------------------------ */
class Shipping extends ApiController
{
	
 	 /*------------------------------------------------------ */
    //-- 获取物流信息
    /*------------------------------------------------------ */
    public function getLog(){
		$order_id = input('order_id',0,'intval');
		if ($order_id < 1) return $this->error('传参错误.');
		$OrderModel = new OrderModel();
		$orderInfo = $OrderModel->info($order_id);		
		if (empty($orderInfo)) return $this->error('订单不存在.');
		if ($orderInfo['shipping_status'] < 1) return $this->error('暂无数据.');
		$ShippingModel = new ShippingModel();
		$shipping = $ShippingModel->getRows();
		$orderInfo['shipping_code'] = $shipping[$orderInfo['shipping_id']]['shipping_code'];
		$ShippingLogModel = new ShippingLogModel();
		$res = $ShippingLogModel->getInfo($orderInfo);
		if (is_array($res) == false) return $this->error($res);	
		foreach ($res['data'] as $key=>$row){
			$row['_time'] = explode(' ',$row['time']);
			$row['isend'] = strstr($row['context'],'签收')?1:0;
			$res['data'][$key] = $row;
		}
		$return['shipping_name'] = $orderInfo['shipping_name'];
		$return['invoice_no'] = $orderInfo['invoice_no'];
		$return['data'] = $res['data'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
		
	}
}
