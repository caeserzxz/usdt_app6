<?php

namespace app\shop\controller\api;
use think\Db;
use app\ApiController;
use app\shop\model\AfterSaleModel;
use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\ShippingModel;
/*------------------------------------------------------ */
//-- 售后相关
/*------------------------------------------------------ */

class AfterSale extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new AfterSaleModel();
    }
    /*------------------------------------------------------ */
    //-- 提交售申请
    /*------------------------------------------------------ */
    public function add()
    {

        $inArr['type'] = input('type','','trim');
        $inArr['goods_number'] = input('goods_number',0,'intval');
        $inArr['return_desc'] = input('return_desc','','trim');
        $inArr['rec_id'] = input('rec_id',0,'intval');
        if ($inArr['rec_id'] < 1){
            return $this->error('传参失败.');
        }
        $OrderModel = new OrderModel();
        $OrderGoodsModel = new OrderGoodsModel();
        $goods = $OrderGoodsModel->find($inArr['rec_id']);
        if (empty($goods)){
            return $this->error('没有找到相关商品.');
        }
        $last_num = $goods['goods_number'] -  $goods['after_sale_num'];
        if ($last_num < 1){
            return $this->error('此商品已全部申请售后，不能再申请.');
        }
        if ($inArr['goods_number'] > $last_num){
            return $this->error('商品申请售后数量大于可申请数量.');
        }
        $orderInfo = $OrderModel->info($goods['order_id']);
        if ($orderInfo['isAfterSale'] == 0){
            return $this->error('此订单不能申请售后，请联系客服.');
        }
        //处理图片
        $imgfile = input('imgfile');
        if (empty($imgfile) == false){
            $imgs = array();
            $file_path = config('config._upload_').'aftersale/'.date('Ymd').'/';
            makeDir($file_path);
            foreach ($imgfile as $file){
                $file_name = $file_path.random_str(12).'.jpg';
                file_put_contents($file_name,base64_decode(str_replace('data:image/jpeg;base64,','',$file)));
                $imgs[] = trim($file_name,'.');
            }
            $inArr['imgs'] = join(',',$imgs);
        }
        $inArr['return_settle_money'] = $goods['settle_price'] * $inArr['goods_number'];

        //计算实际可退金额
        $total_sale_price = $OrderGoodsModel->where('order_id',$goods['order_id'])->SUM('sale_price');//计算订单商品单价汇总
        $offer_price = $orderInfo['goods_amount'] - ($orderInfo['order_amount'] - $orderInfo['shipping_fee']);//计算订单总优惠金额
        $return_pre = $goods['sale_price'] / $total_sale_price;//计算当前商品占比
        $return_price = priceFormat($goods['sale_price'] - ($offer_price * $return_pre));
        $inArr['return_money'] = $return_price * $inArr['goods_number'];
        //end

        $inArr['user_id']  = $this->userInfo['user_id'];
        $inArr['add_time'] = $inArr['update_time'] = time();
        $inArr['goods_id'] = $goods['goods_id'];
        $inArr['goods_sn'] = $goods['goods_sn'];
        $inArr['supplyer_id'] = $goods['supplyer_id'];
        $inArr['order_id'] = $orderInfo['order_id'];
        $inArr['order_sn'] = $orderInfo['order_sn'];
        $inArr['as_sn']    = $this->Model->getSn();
        Db::startTrans();//启动事务
        $res = $this->Model->save($inArr);
        if ($res < 1){
            Db::rollback();// 回滚事务
            return $this->error('提交处理失败-1，请重试.');
        }
        $as_id = $this->Model->as_id;
        $upData['after_sale_num'] = ['INC',$inArr['goods_number']];
        $res = $OrderGoodsModel->where('rec_id',$inArr['rec_id'])->update($upData);
        if ($res < 1){
            Db::rollback();// 回滚事务
            return $this->error('提交处理失败-2，请重试.');
        }
        if ($orderInfo['is_after_sale'] != 1){//订单非售后中，执行
            unset($upData);
            $upData['is_after_sale'] = 1;
            $upData['update_time'] = time();
            $res = $OrderModel->where('order_id',$orderInfo['order_id'])->update($upData);
            if ($res < 1){
                Db::rollback();// 回滚事务
                return $this->error('提交处理失败-3，请重试.');
            }
            $OrderModel->_log($orderInfo, '用户申请售后');
            $OrderModel->cleanMemcache($orderInfo['order_id']);
        }
        Db::commit();// 提交事务
        $this->Model->_log($as_id, '用户申请售后',$this->Model->status[0],'user',$this->userInfo['user_id']);
        return $this->success('提交成功，我们将尽快处理.');
    }
    /*------------------------------------------------------ */
    //-- 提交售后退货物流信息
    /*------------------------------------------------------ */
    public function shipping()
    {
        $as_id = input('as_id',0,'intval');
        if ($as_id < 1) return $this->error('传参错误.');
        $shipping_id = input('shipping_id',0,'intval');
        $upData['shipping_no'] = input('shipping_no','','trim');
        if ($shipping_id < 1){
            return $this->error('请选择快递公司.');
        }
        if (empty($upData['shipping_no'])){
            return $this->error('请输入快递单号.');
        }
        $asInfo = $this->Model->find($as_id);
        if (empty($asInfo)){
            return $this->error('没有找到相关售后信息.');
        }
        if ($asInfo['user_id'] != $this->userInfo['user_id']){
            return $this->error('你无权操作.');
        }
        if ($asInfo['status'] != 2){
            return $this->error('售后状态不正确，无法操作.');
        }
        $shippingList = (new ShippingModel)->getRows();
        if (empty($shippingList[$shipping_id])){
            return $this->error('获取快递公司信息失败.');
        }
        $upData['shipping_name'] = $shippingList[$shipping_id]['shipping_name'];
        $upData['shipping_id'] = $shipping_id;
        $upData['status'] = 3;
        $upData['shipping_time'] = time();
        $upData['update_time'] = time();
        $res = $this->Model->where('as_id',$as_id)->update($upData);
        if ($res < 1){
            return $this->error('未知错误，处理失败，请重试。');
        }
        return $this->success('提交成功.');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getList(){
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        $type = input('type','','trim');
        switch ($type){
            case 'all':
                break;
            case 'fail':
                $where[] = ['status', '=', 1];
                break;
            case 'success':
                $where[] = ['status', '>', 1];
                break;
            default:
                break;
        }
        $data = $this->getPageList($this->Model, $where,'*',7);
        $OrderGoodsModel = new OrderGoodsModel();
        foreach ($data['list'] as $key=>$row){
            $row['goods'] = $OrderGoodsModel->find($row['rec_id'])->toArray();
            $row['goods']['exp_price'] = explode('.',$row['goods']['sale_price']);
            $row['add_time'] = dateTpl($row['add_time']);
            $row['status'] = $this->Model->status[$row['status']];
            $return['list'][] = $row;
        }
        $return['page_count'] = $data['page_count'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
}
