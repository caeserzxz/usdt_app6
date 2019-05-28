<?php
/*------------------------------------------------------ */
//-- 积分商品
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\integral\controller;
use app\ClientbaseController;
use app\integral\model\IntegralGoodsModel;

class Goods extends ClientbaseController{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new IntegralGoodsModel();
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
		$this->assign('title', '积分商品');
        $this->assign('input',input());
        return $this->fetch('index');
	}
    /*------------------------------------------------------ */
    //-- 商品详细页
    /*------------------------------------------------------ */
    public function info(){
		$this->assign('not_top_nav', true);
        $ig_id= input('id',0,'intval');
        if ($ig_id < 1) return $this->error('传参错误.');
        $igInfo = $this->Model->info($ig_id);
        if (empty($igInfo)){
            return $this->error('积分商品不存在.');
        }
        if ($igInfo['is_on_sale'] == 9){
            return $this->error('已兑换商品已下架.');
        }elseif ($igInfo['is_on_sale'] == 0){
            return $this->error('已兑换商品还没上架.');
        }
        $goods = $igInfo['goods'];
        unset($igInfo['goods']);
        $this->assign('title', $goods['goods_name']);
		$goods['m_goods_desc'] = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/",'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"',$goods['m_goods_desc']);
        $this->assign('imgsList', $goods['imgsList']);//获取图片
        $this->assign('skuImgs', $goods['skuImgs']);//获取sku图片
        $this->assign('goods', $goods);
        $this->assign('goods_status',config('config.goods_status'));
        $shareUrl = getUrl('','',['id'=>$ig_id]);
        if ($this->is_wx == 1){
            $wxShare = (new \app\weixin\model\WeiXinModel)->getSignPackage($shareUrl);
            $wxShare['img'] = $goods['goods_thumb'];
            $wxShare['title'] = $goods['goods_name'];
            $wxShare['description'] = $goods['description'];
            $this->assign('wxShare',$wxShare);
        }
        $this->assign('igInfo', $igInfo);
        return $this->fetch('info');
    }
	

}?>