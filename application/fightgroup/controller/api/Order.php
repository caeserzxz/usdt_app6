<?php
namespace app\fightgroup\controller\api;
use app\ApiController;

use app\shop\model\OrderModel;
use app\fightgroup\model\FightGroupListModel;
/*---------------------------------------------------- */
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
        $where[] = ['order_type','=',2];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['is_del','=',0];
        $type = input('type','','trim');
        switch ($type){
            case 'myInitiate':
                $where[] = ['is_initiate', '=', 1];
                break;
            case 'myJoin':
                $where[] = ['is_initiate', '=', '0'];
                break;
            default:
                break;
        }
        $data = $this->getPageList($this->Model, $where,'order_id',7);
        $lang = lang('fg_order');
        $FightGroupListModel = new FightGroupListModel();
        foreach ($data['list'] as $key=>$order){
            $order = $this->Model->info($order['order_id']);
            $order['fgJoin'] = $FightGroupListModel->info($order['pid']);
            $order['success_num'] = count($order['fgJoin']['order']);
            unset($order['fgJoin']['order']);
            if ($order['is_initiate'] == 1 && $order['ostatus'] == '待付款'){//发起者判断是否支付来获取状态
                $order['fg_status'] = $lang[0];
            }else{
                $order['fg_status'] = $lang[$order['fgJoin']['status']];
            }

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

 	
}
