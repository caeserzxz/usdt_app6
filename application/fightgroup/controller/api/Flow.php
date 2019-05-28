<?php

namespace app\fightgroup\controller\api;

use app\ApiController;
use think\Db;

use app\fightgroup\model\FightGroupModel;
use app\fightgroup\model\FightGroupListModel;
use app\fightgroup\model\FightGoodsModel;

use app\shop\model\GoodsModel;
use app\member\model\UserAddressModel;
use app\shop\model\CartModel;
use app\mainadmin\model\PaymentModel;
use app\shop\model\BonusModel;
use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\BonusListModel;
/*------------------------------------------------------ */
//-- 购物相关API
/*------------------------------------------------------ */

class Flow extends ApiController
{

    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new FightGroupModel();
    }
    /*------------------------------------------------------ */
    //-- 获取购物车
    /*------------------------------------------------------ */
    public function evalCart(){
        $fg_id = input('fg_id', '0', 'intval');
        $number = input('number',0,'intval');
        $sku_val = input('sku_val',0,'trim');
        $address_id = input('address_id', '0', 'intval');
        $fgInfo = $this->Model->info($fg_id);
        if (empty($fgInfo)) return $this->error('拼团不存在.');
        $goods = $fgInfo['goods'];
        unset($fgInfo['goods']);
        if ($goods['is_spec'] == 1) {//多规格处理
            if (empty($sku_val)){
                return $this->error('传参错误-1.');
            }
            if (empty($goods['sub_goods'][$sku_val])){
                return $this->error('传参错误-2.');
            }
            $return['buyGoods'] = $goods['sub_goods'][$sku_val];
        }else{
            $return['buyGoods'] = $goods;
        }

        $return['buyGoods']['goods_name'] = $goods['goods_name'];
        $return['totalGoodsPrice'] = sprintf("%.2f",$return['buyGoods']['sale_price'] * $number);
        $return['orderTotal'] = $return['totalGoodsPrice'];
        if ($goods['is_spec'] == 1){//多规格
            $skuImgs = (new GoodsModel)->getImgsList($fgInfo['goods_id'], true, true);//获取sku图片
            $return['goods_img'] = empty($skuImgs[$sku_val]) ? $goods['goods_thumb'] : $skuImgs[$sku_val];
        }else{
            $return['goods_img'] = $goods['goods_thumb'];
        }
        $cartList['buyGoodsNum'] = $number;
        $cartList['goodsList'][] = ['goods_id'=>$fgInfo['goods_id']];
        $return['shippingFee'] = $this->evalShippingFee($address_id,$cartList);
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 计算运费
    /*------------------------------------------------------ */
    function evalShippingFee($address_id = 0, &$cartList = [])
    {
        $return['code'] = 1;
        if ($address_id < 1) {
            $return['shipping_fee'] = sprintf("%.2f", 0);
            return $return;
        }
        $addressList = (new UserAddressModel)->getRows();
        $address = $addressList[$address_id];
        $shippingFee = (new CartModel)->evalShippingFee($address,$cartList);
        $shippingFee = reset($shippingFee);//现在只返回默认快递
        $shippingFee['shipping_fee'] = sprintf("%.2f", $shippingFee['shipping_fee'] * 1);
        return $shippingFee;

    }
    /*------------------------------------------------------ */
    //-- 执行下单
    /*------------------------------------------------------ */
    function addOrder()
    {
        $this->checkLogin();//验证登录
        $pay_id = input('pay_id', 0, 'intval');
        if ($pay_id < 0) return $this->error('请选择支付方式.');
        $PaymentModel = new PaymentModel();
        $paymentList = $PaymentModel->getRows(true);
        $payment = $paymentList[$pay_id];
        if (empty($payment)) return $this->error('相关支付方式不存在或已停用.');
        $address_id = input('address_id', 0, 'intval');
        if ($address_id < 0) return $this->error('请设置收货地址后，再操作.');
        $UserAddressModel = new UserAddressModel();
        $addressList = $UserAddressModel->getRows();
        $address = $addressList[$address_id];
        if (empty($address)) return $this->error('相关收货地址不存在.');
        $used_bonus_id = input('used_bonus_id', 0, 'intval');

        $fg_id = input('fg_id', '0', 'intval');
        $join_id = input('join_id', '0', 'intval');
        $number = input('number',0,'intval');
        $sku_val = input('sku_val',0,'trim');

        if ($number < 1) return $this->error('请选择拼团商品.');

        $fgInfo = $this->Model->info($fg_id,false);
        if ($fgInfo['is_on_sale'] == 0){
            return $this->error('拼团活动未开始.');
        }elseif ($fgInfo['is_on_sale'] == 9){
            return $this->error('拼团活动已结束.');
        }

        if ($join_id == 0 && $fgInfo['is_on_sale'] == 2) {
            return $this->error('库存不足，不能发起拼团.');
        }

        $goods = $fgInfo['goods'];
        unset($fgInfo['goods']);
        /* 是否正在销售 */
        if ($goods['is_on_sale'] == 0) {
            return ['code' => -1, 'msg' => '商品【' . $goods['goods_name'] . '】已下架，暂不支持购买！'];
        }

        $sku_id = 0;
        if ($goods['is_spec'] == 1){//多规格
            $buyGoods = $goods['sub_goods'][$sku_val];
            $sku_id = $buyGoods['sku_id'];
            if (empty($buyGoods))  return $this->error('规格商品不存在.');
        }else{
            $buyGoods = $goods;
        }


        if ($fgInfo['limit_num'] < $number){
            return  $this->error('单次限购'.$buyGoods['limit_num'].'件');
        }
        $stock = $buyGoods['fg_number'] - $buyGoods['sale_num'];//计算剩余可销售的拼团数量
        if ($stock < $number){
            if ($stock > 0){
                return  $this->error('当前拼团剩余库存：'.$stock);
            }else{
                return  $this->error('当前拼团已达上限.');
            }
        }
        if ($buyGoods['goods_number'] < 1){
            return  $this->error('当前商品库存不足.');
        }elseif ($buyGoods['goods_number'] < $number){
            return  $this->error('当前剩余库存：'.$buyGoods['goods_number'].'件');
        }



        $time = time();
        Db::startTrans();//启动事务
        $OrderModel = new OrderModel();
        $inArr['supplyer_id'] = $buyGoods['supplyer_id'];//供应商id
        $inArr['order_status'] = 0;
        $inArr['pay_status'] = 0;
        $inArr['shipping_status'] = 0;
        $_log = '生成拼团订单';

        //拼团订单处理
        $FightGroupListModel = new FightGroupListModel();
        if ($join_id > 0 ){
            $fgJoin = $FightGroupListModel->info($join_id);
            if ($fgJoin['status'] == config('config.FG_FULL')){
                return $this->error('当前拼团已满员.');
            }elseif ($fgJoin['status'] == config('config.FG_SEUCCESS')){
                return $this->error('当前拼团已完成.');
            }elseif ($fgJoin['status'] == config('config.FG_FAIL')){
                return $this->error('当前拼团已关闭.');
            }
            $where = [];
            $where[] = ['order_type','=',2];
            $where[] = ['by_id','=',$fgJoin['fg_id']];
            $where[] = ['order_status','in',[0,1]];
            $where[] = ['pid','=',$join_id];
            $where[] = ['user_id','=',$this->userInfo['user_id']];
            $count = $OrderModel->where($where)->count();
            if ($count > 0)  return $this->error('你已参与此拼团，不能重复参与.');
            $order_count = count($fgJoin['order']);
            if ($fgJoin['success_num'] == $order_count+1) {//达到拼团数量
                $fgUpArr['status'] = config('config.FG_FULL');//设置拼团满员
                 $FightGroupListModel->where(['gid'=>$join_id])->update($fgUpArr);
            }

            $inArr['pid'] = $join_id;
        }else{//发起拼团，创建拼团信息
            $where = [];
            $where[] = ['order_type','=',2];
            $where[] = ['by_id','=',$fg_id];
            $where[] = ['order_status','=',config('config.OS_UNCONFIRMED')];
            $where[] = ['user_id','=',$this->userInfo['user_id']];
            $count = $OrderModel->where($where)->count();
            if ($count > 0)  return $this->error('当前拼团你有订单待处理，不能重复参与.');
            $fgInArr['fg_id'] = $fg_id;
            $fgInArr['head_user_id'] = $this->userInfo['user_id'] * 1;
            if ($fgInfo['valid_time'] > 0){
                $fgInArr['fail_time'] = $time + $fgInfo['valid_time'] * 3600;
            }else{
                $fgInArr['fail_time'] = $fgInfo['end_date'];
            }
            $fgInArr['success_num'] =  $fgInfo['success_num'];
            $fgInArr['add_time'] = $time;
            $fgInArr['status'] = 0;

            $res = $FightGroupListModel->save($fgInArr);
            if ($res < 1) {
                Db::rollback();// 回滚事务
                return $this->error('未知原因，发起拼团失败.');
            }
            $inArr['pid'] = $FightGroupListModel->gid;
            $inArr['is_initiate'] = 1;
        }
        //拼团订单处理end
        $cartList['buyGoodsNum'] = $number;
        $cartList['totalGoodsPrice'] = $buyGoods['sale_price'] * $number;
        $cartList['orderTotal'] = $cartList['totalGoodsPrice'];
        $cartList['goodsList'][] = $buyGoods;

        $inArr['use_bonus'] = 0;
        if ($used_bonus_id > 0) {//优惠券验证
            $BonusModel = new BonusModel();
            $bonus = $BonusModel->binfo($used_bonus_id);
            if ($bonus['user_id'] != $this->userInfo['user_id']) {
                return $this->error('优惠券出错，请核实.');
            }
            if ($bonus['info']['stauts'] != 1) {
                return $this->error('优惠券无法使用：' . $bonus['info']['stauts_info']);
            }
            if ($cartList['totalGoodsPrice'] < $bonus['info']['min_amount']) {
                return $this->error('选择的优惠券满￥' . $bonus['info']['min_amount'] . '才可以使用，请核实.');
            }
            $inArr['use_bonus'] = $bonus['info']['type_money'];
        }
        //运费处理
        $shippingFee = (new CartModel)->evalShippingFee($address,$cartList);
        if ($shippingFee === false){
            $inArr['shipping_fee'] = 0;
        }else{
            $shippingFee = reset($shippingFee);//现在只返回默认快递
            $inArr['shipping_fee'] = $shippingFee['shipping_fee'] * 1;
        }

        $inArr['order_amount'] = $cartList['orderTotal'] + $inArr['shipping_fee'] - $inArr['use_bonus'];
        if ($payment['pay_code'] == 'balance') {//如果使用余额，判断用户余额是否足够
            if ($inArr['order_amount'] > $this->userInfo['account']['balance_money']) {
                return $this->error('余额不足，请使用其它支付方式.');
            }

        }
        $inArr['order_type'] = 2;
        $inArr['by_id'] = $fg_id;

        $inArr['buyer_message'] = input('buy_msg', '', 'trim');
        $inArr['consignee'] = $address['consignee'];
        $inArr['address'] = $address['address'];
        $inArr['merger_name'] = $address['merger_name'];
        $inArr['province'] = $address['province'];
        $inArr['city'] = $address['city'];
        $inArr['district'] = $address['district'];
        $inArr['mobile'] = $address['mobile'];
        $inArr['add_time'] = $time;
        $inArr['user_id'] = $this->userInfo['user_id'];
        $inArr['dividend_role_id'] = $this->userInfo['role_id'];
        $inArr['pay_id'] = $payment['pay_id'];
        $inArr['pay_code'] = $payment['pay_code'];
        $inArr['pay_name'] = $payment['pay_name'];
        $inArr['goods_amount'] = $cartList['totalGoodsPrice'];
        $inArr['buy_goods_sn'] = $buyGoods['goods_sn'];
        $inArr['ipadderss'] = request()->ip();
        $inArr['is_pay'] = $payment['is_pay'];//是否需要支付,1线上支付，0，不需要支付，
        $inArr['is_stock'] = 1;
        $inArr['supplyer_id'] = $goods['supplyer_id'];
        if ($goods['supplyer_id'] > 0){
            $inArr['settle_price'] = $buyGoods['settle_price']  * $number;
        }
        $inArr['order_sn'] = $OrderModel->getOrderSn();
        $res = $OrderModel->save($inArr);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return $this->error('未知原因，订单写入失败.');
        }
        $order_id = $OrderModel->order_id;

        $inArr['order_id'] = $order_id;
        $res = $OrderModel->_log($inArr,$_log);
        if (empty($res)) {
            Db::rollback();// 回滚事务
            return $this->error('未知原因，订单日志写入失败.');
        }
        $FightGoodsModel = new FightGoodsModel();
        //执行扣库存
        if ($inArr['is_stock'] == 1) {
            $res = $FightGoodsModel->evalGoodsStore($fg_id,$buyGoods['goods_id'],$sku_id,$number);
            if ($res < 1) {
                Db::rollback();// 回滚事务
                return $this->error('未知错误，更新库存失败.');
            }
        }
        //end
        $inArr = [];
        $inArr['order_id'] = $order_id;
        $inArr['goods_id'] = $buyGoods['goods_id'];
        if ($goods['is_spec'] == 1) {
            $inArr['sku_id'] = $sku_id;
            $inArr['sku_val'] = $buyGoods['sku_val'];
            $inArr['sku_name'] = $buyGoods['sku_name'];
        }
        $inArr['supplyer_id'] = $goods['supplyer_id'];
        $inArr['goods_name'] = $goods['goods_name'];
        $inArr['brand_id'] = $goods['brand_id'];
        $inArr['cid'] = $goods['cid'];
        $inArr['goods_sn'] = $buyGoods['goods_sn'];
        $inArr['goods_number'] = $number;
        if ($sku_id > 0){
            $skuImgs = (new GoodsModel)->getImgsList($buyGoods['goods_id'], true, true);//获取sku图片
            $inArr['pic'] =  empty($skuImgs[$sku_val])?$goods['goods_thumb']:$skuImgs[$sku_val];
        }else{
            $inArr['pic'] = $goods['goods_thumb'];
        }
        $inArr['settle_price'] = $buyGoods['settle_price'];
        $inArr['market_price'] = $buyGoods['market_price'];
        $inArr['shop_price'] = $buyGoods['shop_price'];
        $inArr['sale_price'] = $buyGoods['sale_price'];
        $inArr['goods_weight'] = $buyGoods['goods_weight'];
        $inArr['add_time'] = $time;
        $inArr['user_id'] = $this->userInfo['user_id'];
        $res = (new OrderGoodsModel)->save($inArr);
        if ($res < 1) {
            Db::rollback();// 回滚事务
            return $this->error('未知错误，写入订单商品失败.');
        }

        //处理优惠券
        if ($used_bonus_id > 0) {
            $upArr = array();
            $upArr['user_id'] = $this->userInfo['user_id'];
            $upArr['used_time'] = $time;
            $upArr['order_id'] = $order_id;
            $upArr['order_sn'] = $inArr['order_sn'];
            $BonusListModel = new BonusListModel();
            $res = $BonusListModel->where('bonus_id', $used_bonus_id)->update($upArr);
            if ($res < 1) {
                Db::rollback();// 回滚事务
                return $this->error('未知错误，修改优惠券失败.');
            }
        }
        //end


        $this->Model->where('fg_id',$fg_id)->update(['all_order_num'=>['INC',1],'buy_goods_num'=>['INC',$number]]);
        $this->Model->cleanMemcache($fg_id);
        Db::commit();// 提交事务
        $FightGroupListModel->cleanMemcache($join_id);
        $return['order_id'] = $order_id;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

}
