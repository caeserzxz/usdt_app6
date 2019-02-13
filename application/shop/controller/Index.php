<?php
/*------------------------------------------------------ */
//-- 商城主页
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\controller;
use app\ClientbaseController;
use think\Db;
use think\facade\Cache;
use app\shop\model\SlideModel;
use app\shop\model\GoodsModel;
use app\shop\model\NavMenuModel;
class Index  extends ClientbaseController{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index($isIndex = false){
		//调用自定义首页
		if ($isIndex == false && settings('shop_index_tpl') == 1){
			return $this->shopIndex();
		}
		$this->assign('title', '首页');
		$this->assign('slideList', SlideModel::getRows());//获取幻灯片
		$this->assign('navMenuList', NavMenuModel::getRows());//获取导航菜单
		$GoodsModel = new GoodsModel();
		$this->assign('classGoods',$GoodsModel->getIndexClass());//获取首页分类
		$this->assign('bestGoods',$GoodsModel->getIndexBestGoods());//获取首页分类
		return $this->fetch('index');
	}
	/*------------------------------------------------------ */
	//-- 分类页
	/*------------------------------------------------------ */
	public function allSort(){
		$this->assign('title', '分类');
		$GoodsModel = new GoodsModel();
		$this->assign('allSort',$GoodsModel->getClassToAllSort());//获取分层分类
		$this->assign('classList',$GoodsModel->getClassList());//获取分类
		return $this->fetch('all_sort');
	}
	/*------------------------------------------------------ */
	//-- 搜索页
	/*------------------------------------------------------ */
	public function search(){
		$this->assign('title', '搜索');
		$this->assign('hotSearch', explode(' ',settings('hot_search')));
        $GoodsModel = new GoodsModel();
        $this->assign('searchKeys', $GoodsModel->searchKeys());
		return $this->fetch('search');
	}
	/*------------------------------------------------------ */
	//-- 自定义首页
	/*------------------------------------------------------ */
	protected function shopIndex(){
		
		$mkey = 'shopIndex_web';
		$theme = Db::table('shop_page_theme')->find();
		if (empty($theme)) return $this->index(true);
	    $body = Cache::get($mkey);
		if (empty($body)){
			$ShopPageTheme = new \app\shop\model\ShopPageTheme();
			$d_products = $ShopPageTheme->defPproducts();			
			$page = json_decode($theme['page'],true);
			$GoodsModel = new GoodsModel();
			foreach ($page['pageElement'] as $key=>$row){
				$_body = '';
				if ($row['componentType'] != 'search'){				
					if ($_body){
						$body .= $_body;
						continue;
					}
				}
				if ($row['componentType'] == 'products'){
					 foreach ($row['data'] as $keyb=>$rowb){
						 if ($rowb['visible'] == 0) continue;
						 if (empty($rowb['goodsDataType']) || $rowb['goodsDataType'] == 'custom'){
							  foreach ($rowb['products'] as $keyc=>$rowc){
								  if (!is_numeric($rowc['id'])){
									$rowb['products'][$keyc] = $d_products[$rowc['id']];
								  }else{							 
									  $grow = $GoodsModel->info($rowc['id']);
									  if(!$grow['is_on_sale']){
										 unset($rowb['products'][$keyc]);
										 continue; 
									  }
									  $rowc['thumb']['url'] = $grow['goods_thumb'];
									  $rowc['name'] = $grow['goods_name'];
									  $rowc['par_price'] = $grow['market_price'];
									  $rowc['sale_price'] = $grow['is_spec'] == 1? $grow['show_price']:$grow['shop_price'];
									  $rowc['vip_price'] = 0;
									  $rowc['sale_count'] = $grow['sale_num']+ $grow['virtual_sale'];
									  $rowb['products'][$keyc] = $rowc;
								  }
							  }
						 }else{
							unset($where,$rowb['products']);
							$where[] = ['is_alone_sale','=',1];							
							if ($rowb['goodsDataType'] == 'recommend'){
								$where[] = ['is_best','=',1];
							}elseif ($rowb['goodsDataType'] == 'new'){
								$where[] = ['is_new','=',1];
							}else{
								$where[] = ['is_hot','=',1];
							}
							$time = time();
							$grows = $GoodsModel->field('goods_id,goods_thumb,goods_name,market_price,shop_price,show_price,is_spec,sale_num,virtual_sale')->where($where)->where("isputaway = 1 OR (isputaway = 2  AND  added_time < '".$time."' AND shelf_time > '".$time."' )")->order('update_time desc')->limit($row['dataLimit'])->select();	
												
							foreach ($grows as $grow){
								$rowc['id'] = $grow['goods_id'];
								$rowc['thumb']['url'] = $grow['goods_thumb'];
								$rowc['name'] = $grow['goods_name'];
								$rowc['par_price'] = $grow['market_price'];
								$rowc['sale_price'] = $grow['is_spec'] == 1 ? $grow['show_price'] : $grow['shop_price'];
								$rowc['vip_price'] = 0;
								$rowc['sale_count'] = $grow['sale_num'] + $grow['virtual_sale'];
								$rowb['products'][$rowc['id']] = $rowc;
							}
						 }						
						 $row['data'][$keyb] = $rowb;
					 }
					 
				}
				
				$this->assign('_key', $_key);
				$this->assign('theme_row', $row);
				$_body = $this->fetch('page/'.$row['componentType']);
				$body .= $_body;
			}
			Cache::set($mkey,$body,300);
		}
		$this->assign('theme', $theme);
		$this->assign('body', $body);
		return $this->fetch('page/index');
	}
	
}?>