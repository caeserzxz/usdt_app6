<?php
namespace app\shop\controller\api;
use app\ApiController;

use app\shop\model\OrderModel;
use app\shop\model\SaleafterGoodsModel;
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
        $where[] = ['order_type','in',[0,1]];
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $where[] = ['is_split','=',0];
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
        $mkey = 'OrderIng_'.$order_id;
        $status = Cache::get($mkey);
        if (empty($status) == false){
            $return['msg'] = '请求失败';
            $return['code'] = 0;
            return $this->ajaxReturn($return);
        }
        Cache::set($mkey,1);
        $type = input('type','','trim');
        $config = config('config.');
        $upData['order_id'] = $order_id;
        switch ($type){
            case 'cancel'://取消
                $upData['order_status'] = $config['OS_CANCELED'];
                $upData['cancel_time'] = time();
                $_log = '用户取消订单';
                break;
            case 'sign'://签收
                $upData['shipping_status'] = $config['SS_SIGN'];
                $upData['sign_time'] = time();
                $_log = '用户签收订单';
                break;
            case 'del'://删除
                $upData['is_del'] = 1;
                $_log = '用户删除订单';
                break;
            default:
                return $this->error('没有相关操作.');
                break;
        }
        $res = $this->Model->upInfo($upData);
        Cache::rm($mkey);
        if ($res !== true) return $this->error($res);
        $orderInfo = $this->Model->info($order_id);
        $this->Model->_log($orderInfo,$_log);
        $return['ostatus'] = $orderInfo['ostatus'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);

    }

    /**
     * 售后商品详情
     */
    public function saleaftergoodsdetail(){
        $order_sn = input('order_sn', '');
        $rec_id = input('rec_id', '');
        $order_id = input('order_id', 0, 'intval');
        $data['order_sn'] = $order_sn;
        $data['rec_id'] = $rec_id;
        $data['order_id'] = $order_id;
        $data['user_id'] = $this->userInfo['user_id'];
        $res = $this->Model->saleaftergoods($data);
        if (!is_array($res)) return $this->error($res);
        $res_return['orderinfo'] = $res;
        $res_return['code'] = 1;
        return $this->ajaxReturn($res_return);
    }

    public function dosaleaftergoods()
    {
        try {
            $input = input('');
            $saleafter_type = $input['saleafter_type'];
            $sku_val = $input['sku_val'];
            $sku_name = $input['sku_name'];
            $goods_name = $input['goods_name'];
            $goods_number = $input['goodsnum'];
            $goods_pic = $input['goods_pic'];
            $images_list = $input['images_list'];
            $order_sn = $input['order_sn'];
            $order_id = $input['order_id'];
            $rec_id = $input['rec_id'];
            $refund_amount = $input['refund_amount'];
            $refund_reason_value = $input['refund_reason_value'];
            $is_html = $input['is_html'];
            if ($is_html == 1) {
                $imgfile = $input['imgfile'];
                $images_list = '';
                //处理图片      
                if (empty($imgfile) == false){
                    $file_path = config('config._upload_').'gimg/'.date('Ymd').'/';
                    makeDir($file_path);
                    foreach ($imgfile as $file){
                        $file_name = $file_path.random_str(12).'.jpg';
                        file_put_contents($file_name,base64_decode(str_replace('data:image/jpeg;base64,','',$file)));
                        $images_list  .= trim($file_name,'.').',';              
                    }
                    $images_list = rtrim($images_list, ',');
                }
            }
            if (empty($order_sn)) throw new \Exception("缺少必要参数");
            if (empty($order_id)) throw new \Exception("缺少必要参数");
            if (empty($rec_id)) throw new \Exception("缺少必要参数");
            if (empty($refund_amount)) throw new \Exception("缺少必要参数");
            if (empty($goods_number)) throw new \Exception("请选择退货数量");
            if (empty($refund_reason_value)) throw new \Exception("请输入退款原因");
            $SaleafterGoodsModel = new SaleafterGoodsModel();
            $data['user_id'] = $this->userInfo['user_id'];
            $data['sku_val'] = $sku_val;
            $data['sku_name'] = $sku_name;
            $data['saleafter_type'] = $saleafter_type;
            $data['goods_number'] = $goods_number;
            $data['goods_name'] = $goods_name;
            $data['goods_pic'] = $goods_pic;
            $data['images_list'] = $images_list;
            $data['order_sn'] = $order_sn;
            $data['order_id'] = $order_id;
            $data['rec_id'] = $rec_id;
            $data['refund_amount'] = $refund_amount;
            $data['total_refund_amount'] = $refund_amount * $goods_number;
            $data['refund_reason_value'] = $refund_reason_value;
            $data['add_time'] = time();
            $data['status'] = 0;
            $return = $SaleafterGoodsModel->addSaleafterGoods($data);
            if (is_array($return)) {
                $return['code'] = 1;
                return $this->ajaxReturn($return);
            } else {
                throw new \Exception($return);
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 售后列表数据
     */
    public function saleafterlist(){
        try {
            $SaleafterGoodsModel = new SaleafterGoodsModel();
            $list = $SaleafterGoodsModel->saleafterlist($this->userInfo['user_id']);
            $return['list'] = $list['list'];
            $return['page_count'] = $list['page_count'];
            $return['code'] = 1;
            return $this->ajaxReturn($return);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
 	
}
