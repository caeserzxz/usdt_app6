<?php
/*------------------------------------------------------ */
//-- 拼团相关
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\fightgroup\controller;
use app\ClientbaseController;
use app\fightgroup\model\FightGroupModel;
use app\fightgroup\model\FightGroupListModel;
use app\shop\model\GoodsModel;
use app\shop\model\OrderModel;
use app\mainadmin\model\PaymentModel;

class Index  extends ClientbaseController{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new FightGroupModel();
    }
    /*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index(){
        $this->assign('title', '拼团活动');
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 拼团详细
    /*------------------------------------------------------ */
    public function info(){
        $this->assign('not_top_nav', true);
        $fg_id = input('fg_id',0,'intval');
        if ($fg_id < 1) return $this->error('传参错误.');
        $fgInfo = $this->Model->info($fg_id);
        if (empty($fgInfo)) return $this->error('拼团不存在.');

        $goods = $fgInfo['goods'];
        unset($fgInfo['goods']);
        list($imgsList,$skuImgs) = $this->Model->getImg($fgInfo['goods_id']);//获取相关图片

        $this->assign('title', $goods['goods_name']);
        $this->assign('fgInfo', $fgInfo);
        $goods['m_goods_desc'] = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/",'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"',$goods['m_goods_desc']);
        $this->assign('goods', $goods);
        $this->assign('imgsList', $imgsList);
        $this->assign('skuImgs', $skuImgs);
        if ($fgInfo['is_on_sale'] == 0){
            return $this->fetch('not_start');
        }elseif ($fgInfo['is_on_sale'] == 9){
            return $this->fetch('over');
        } elseif ($fgInfo['status'] == 0) {
            return $this->fetch('not_open');
        }
        $FightGroupListModel = new FightGroupListModel();
        $this->assign('fgListCount',  $FightGroupListModel->where(['fg_id'=>$fg_id,'status'=>1])->count());
        $this->assign('fgList',  $FightGroupListModel->getList($fg_id,2));
        if ($this->is_wx == 1){
            $wxShare = (new \app\weixin\model\WeiXinModel)->getSignPackage();
            $wxShare['img'] = $fgInfo['cover'];
            $wxShare['title'] = '我正在参与拼团：'.$goods['goods_name'];
            $wxShare['description'] = $fgInfo['share_desc'];
            $wxShare['shareUrl'] = getUrl('','',['fg_id'=>$fg_id]);
            $this->assign('wxShare',$wxShare);
        }
        return $this->fetch('info');
    }

    /*------------------------------------------------------ */
    //-- 参团详细
    /*------------------------------------------------------ */
    public function join()
    {
        $join_id = input('join_id',0,'intval');
        if ($join_id < 1){
            return $this->error('传参错误.');
        }
        $fgJoin = (new FightGroupListModel)->info($join_id);
        if (empty($fgJoin)){
            return $this->error('拼团订单不存在.');
        }

        if ($fgJoin['status'] == config('config.FG_FULL')){
            return $this->error('当前拼团已满员.');
        }elseif ($fgJoin['status'] == config('config.FG_SEUCCESS')){
            return $this->error('当前拼团已完成.');
        }elseif ($fgJoin['status'] == config('config.FG_FAIL')){
            return $this->error('当前拼团已关闭.');
        }

        $fgInfo = $this->Model->info($fgJoin['fg_id']);
        if (empty($fgInfo)) return $this->error('拼团不存在.');

        $goods = $fgInfo['goods'];
        unset($fgInfo['goods']);
        $this->assign('join_id', $join_id);
        $this->assign('fgInfo', $fgInfo);
        $this->assign('goods', $goods);
        $this->assign('fgJoin', $fgJoin);
        list($imgsList,$skuImgs) = $this->Model->getImg($fgInfo['goods_id']);//获取相关图片
        $this->assign('imgsList', $imgsList);
        $this->assign('skuImgs', $skuImgs);
        $this->assign('title','参与拼团');

        if ($fgInfo['is_on_sale'] == 0){
            return $this->fetch('not_start');
        }elseif ($fgInfo['is_on_sale'] == 9){
            return $this->fetch('over');
        }
        $shareUrl = getUrl('','',['join_id'=>$join_id]);
        $this->assign('shareUrl',$shareUrl);
        if ($this->is_wx == 1){
            $wxShare = (new \app\weixin\model\WeiXinModel)->getSignPackage($shareUrl);
            $wxShare['img'] = $fgInfo['cover'];
            $wxShare['title'] = '我正在参与拼团：'.$goods['goods_name'];
            $wxShare['description'] = $fgInfo['share_desc'];
            $this->assign('wxShare',$wxShare);
        }
        return $this->fetch('join');
    }
    /*------------------------------------------------------ */
    //-- 商品分享页
    /*------------------------------------------------------ */
    public function myCode(){
        $this->checkLogin(false);//验证白名单
        $this->assign('title', '商品二维码');
        $fg_id = input('fg_id',0,'intval');
        $fgInfo = $this->Model->info($fg_id,false);
        $this->assign('goods', $fgInfo['goods']);
        $this->assign('fgInfo', $fgInfo);
        $shareUrl = getUrl('','',['fg_id'=>$fg_id]);
        $this->assign('shareUrl', $shareUrl);
        if ($this->is_wx == 1){
            $wxShare = (new \app\weixin\model\WeiXinModel)->getSignPackage($shareUrl);
            $wxShare['img'] = $fgInfo['cover'];
            $wxShare['title'] = '正在拼团：'.$fgInfo['goods']['goods_name'];
            $wxShare['description'] = $fgInfo['share_desc'];
            $this->assign('wxShare',$wxShare);
        }
        return $this->fetch('my_code');
    }
    /*------------------------------------------------------ */
    //-- 查看更多
    /*------------------------------------------------------ */
    public function more(){
        $this->assign('title','正在拼团');
        $fg_id = input('fg_id',0,'intval');
        $fgInfo = $this->Model->info($fg_id);
        if (empty($fgInfo)) return $this->error('拼团不存在.');

        $goods = $fgInfo['goods'];
        unset($fgInfo['goods']);
        $this->assign('fgInfo', $fgInfo);
        $this->assign('goods', $goods);
        $this->assign('fgList',  (new FightGroupListModel)->getList($fg_id));
        return $this->fetch('more');
    }
    /*------------------------------------------------------ */
    //-- 结算页
    /*------------------------------------------------------ */
    public function checkOut(){
        $this->checkLogin(false);//验证白名单
        $join_id = input('join_id',0,'intval');
        $this->assign('title', $join_id<1?'发起拼团':'参与拼团');
        $fg_id = input('fg_id',0,'intval');
        $number = input('number',0,'intval');
        $sku_val = input('sku_val',0,'trim');
        $this->assign('join_id', $join_id);
        $this->assign('fg_id', $fg_id);
        $this->assign('number', $number);
        $this->assign('sku_val', $sku_val);
        $fgInfo = $this->Model->info($fg_id);
        $this->assign('fgInfo', $fgInfo);
        return $this->fetch('check_out');
    }

    /*------------------------------------------------------ */
    //-- 下单完成
    /*------------------------------------------------------ */
    public function done(){
        $order_id = input('order_id',0,'intval');
        $type = input('type','','trim');
        $this->assign('title', '订单支付');
        $OrderModel = new OrderModel();
        $orderInfo = $OrderModel->info($order_id);
        if (empty($orderInfo) || $orderInfo['user_id'] != $this->userInfo['user_id']){
            $this->error('订单不存在.');
        }
        $goPay = 0;
        $payment = (new PaymentModel)->where('pay_id', $orderInfo['pay_id'])->find();
        if ($type == 'add' && $orderInfo['pay_status'] == config('config.PS_UNPAYED')){
            if ($payment['is_pay'] == 1){
                $goPay = 1;
            }
        }
        $this->assign('payment', $payment);
        $this->assign('goPay', $goPay);
        $this->assign('orderInfo', $orderInfo);
        $join_id = $orderInfo['pid'];
        $fgJoin = (new FightGroupListModel)->info($join_id);
        if (empty($fgJoin)){
            return $this->error('拼团订单不存在.');
        }
        $fgInfo = $this->Model->info($fgJoin['fg_id']);
        if (empty($fgInfo)) return $this->error('拼团不存在.');
        unset($fgInfo['goods']);
        $this->assign('join_id', $join_id);
        $this->assign('fgInfo', $fgInfo);
        $this->assign('fgJoin', $fgJoin);
        return $this->fetch('done');
    }

}