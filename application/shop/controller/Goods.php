<?php
/*------------------------------------------------------ */
//-- 商品
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;
use app\shop\model\GoodsModel;
use app\shop\model\CartModel;

class Goods extends ClientbaseController{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new GoodsModel();
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
		$this->assign('title', '商品列表');
        $keyword = input('keyword','','trim');
        $this->assign('keyword',$keyword );
		$this->assign('input',input());
        if (empty($keyword) == false){//记录搜索关键字
            $this->Model->searchKeys($keyword);
        }
        $this->assign("classList", $this->Model->getClassList(0));//分类
		return $this->fetch('index');
	}
    /*------------------------------------------------------ */
    //-- 商品详细页
    /*------------------------------------------------------ */
    public function info(){
		$this->assign('not_top_nav', true);
        $goods_id = input('id',0,'intval');
        if ($goods_id < 1) return $this->error('传参错误.');
        $goods = $this->Model->info($goods_id);
        if (empty($goods)){
            return $this->error('商品不存在.');
        }
        $this->assign('title', $goods['goods_name']);

		$goods['m_goods_desc'] = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/",'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"',$goods['m_goods_desc']);
        $this->assign('goods', $goods);
        $this->assign('imgsList', $this->Model->getImgsList($goods_id));//获取图片
        $this->assign('skuImgs', $this->Model->getImgsList($goods_id,true,true));//获取sku图片
        $this->assign('isCollect', $this->Model->isCollect($goods_id,$this->userInfo['user_id']));//判断是否收藏
        $CartModel = new CartModel();
        $this->assign('cartInfo', $CartModel->getCartInfo(0));//获取购物车信息
        $this->assign('goods_status',config('config.goods_status'));
        if ($this->is_wx == 1){
            $wxShare = (new \app\weixin\model\WeiXinModel)->getSignPackage();
            $wxShare['img'] = $goods['goods_thumb'];
            $wxShare['title'] = $goods['goods_name'];
            $wxShare['description'] = $goods['description'];
            $wxShare['shareUrl'] = getUrl('','',['id'=>$goods_id]);
            $this->assign('wxShare',$wxShare);
        }
        return $this->fetch('info');
    }
	
	/*------------------------------------------------------ */
    //-- 商品分享页
    /*------------------------------------------------------ */
    public function myCode(){
		$this->checkLogin(false);//验证白名单
		$DividendShareByRole = settings('DividendShareByRole');
		if ($DividendShareByRole == 1 && $this->userInfo['role_id'] < 1){
			return $this->error('请升级身份后再操作.');
		}
		$this->assign('title', '商品二维码');
		$goods_id = input('goods_id',0,'intval');
		$goods = $this->Model->info($goods_id);
		$this->assign('goods', $goods);
        $this->assign('goods_id', $goods_id);
        $shareUrl = getUrl('goods/info','',['id'=>$goods_id]);
        if ($this->is_wx == 1){
            $wxShare = (new \app\weixin\model\WeiXinModel)->getSignPackage();
            $wxShare['img'] = $goods['goods_thumb'];
            $wxShare['title'] = $goods['goods_name'];
            $wxShare['description'] = $goods['description'];
            $wxShare['shareUrl'] = $shareUrl;
            $this->assign('wxShare',$wxShare);
        }

        $this->assign('shareUrl',$shareUrl);
		return $this->fetch('my_code');
	}
	 /*------------------------------------------------------ */
    //-- 商品评论页
    /*------------------------------------------------------ */
    public function comment(){
		$this->assign('title', '商品评论');
		$goods_id = input('goods_id',0,'intval');
		$this->assign('goods_id', $goods_id);
		return $this->fetch('comment');
	}
}?>