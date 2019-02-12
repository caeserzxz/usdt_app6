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
        $this->assign('title', $goods['goods_name']);
        $this->assign('goods', $goods);
        $imgWhere[] = ['goods_id','=',$goods_id];
        $this->assign('imgsList', $this->Model->getImgsList($imgWhere));//获取图片
        $this->assign('skuImgs', $this->Model->getImgsList($imgWhere,true,true));//获取sku图片
        $this->assign('isCollect', $this->Model->isCollect($goods_id,$this->userInfo['user_id']));//获取sku图片
        $CartModel = new CartModel();
        $this->assign('cartInfo', $CartModel->getCartInfo(0));//获取购物车信息

        return $this->fetch('info');
    }
	
}?>