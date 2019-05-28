<?php
namespace app\shop\controller\api;
use app\ApiController;

use app\shop\model\OrderModel;
/*------------------------------------------------------ */
//-- 订单相关API
/*------------------------------------------------------ */
class Order extends ApiController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new OrderModel();
    }
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
 	public function getList(){
        $where[] = ['pid','=',0];
        $where[] = ['order_type','in',[0,1]];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['is_del','=',0];
        $type = input('type','','trim');
        switch ($type){
            case 'waitPay':
                $where[] = ['is_pay', '>', 0];
                $where[] = ['order_status', '=', '0'];
                $where[] = ['pay_status', '=', '0'];
                break;
            case 'waitShipping':
                $where[] = ['order_status', '=', '1'];
                $where[] = ['shipping_status', '=', '0'];
                $where['and'][] = "(pay_status = 1 OR is_pay = 0)";
                break;
            case 'waitSign':
                $where[] = ['order_status', '=', '1'];
                $where[] = ['shipping_status', '=', '1'];
                break;
            case 'sign':
                $where[] = ['order_status', '=', '1'];
                $where[] = ['shipping_status', '=', '2'];
                break;
            default:
                break;
        }
        $data = $this->getPageList($this->Model, $where,'order_id',5);
        $config = config('config.');
        foreach ($data['list'] as $key=>$order){
            $order = $this->Model->info($order['order_id'],$config);

            $return['list'][] = $order;
        }
        $return['page_count'] = $data['page_count'];
		$return['code'] = 1;
		return $this->ajaxReturn($return);
	}
    /*------------------------------------------------------ */
    //-- 获取订单详细
    /*------------------------------------------------------ */
    public function getInfo(){
        $order_id = input('order_id',0,'intval');
        if ($order_id < 1) return $this->error('传参错误.');
        $orderInfo = $this->Model->info($order_id);
        if ($orderInfo['user_id'] != $this->userInfo['user_id']) return $this->error('无权访问.');
        $return['orderInfo'] = $orderInfo;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取订单操作
    /*------------------------------------------------------ */
    public function action(){
        $order_id = input('order_id',0,'intval');
        $type = input('type','','trim');
        $config = config('config.');
        $upData['order_id'] = $order_id;
        switch ($type){
            case 'cancel'://取消
                $upData['order_status'] = $config['OS_CANCELED'];
                $upData['cancel_time'] = time();
                break;
            case 'sign'://签收
                $upData['shipping_status'] = $config['SS_SIGN'];
                $upData['sign_time'] = time();
                break;
            case 'del'://删除
                $upData['is_del'] = 1;
                break;
            default:
                return $this->error('没有相关操作.');
                break;
        }
        $res = $this->Model->upInfo($upData);
        if ($res !== true) return $this->error($res);
        $orderInfo = $this->Model->info($order_id);
        $return['ostatus'] = $orderInfo['ostatus'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);

    }
 	
}
