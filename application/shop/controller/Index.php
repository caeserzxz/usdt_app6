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
		$tipsubscribe = 0;//是否显示提示关注
        //微信网页访问执行
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')){
            if ($this->userInfo['user_id'] > 0){
                $subscribe = (new \app\weixin\model\WeiXinUsersModel)->where('user_id',$this->userInfo['user_id'])->value('subscribe');
                $tipsubscribe = $subscribe == 1 ? 0 : 1;
            }
        }
        //优惠券提示层背景
        $reg_bonus_bg=settings('reg_bonus_bg');
        $this->assign('reg_bonus_bg', $reg_bonus_bg);

        //首页头条
        $headline = (new \app\shop\model\HeadlineModel)->getBestList();
        $this->assign('headline', $headline);
        
        $this->assign('tipsubscribe', $tipsubscribe);
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
    //-- 自定义首页 -- 新
    /*------------------------------------------------------ */
    public function diypage(){
        $pageid = input('pageid',2,'intval');
        $ShopPageTheme = new \app\shop\model\ShopPageTheme();
        $theme = $ShopPageTheme->find($pageid);
        if (empty($theme)){
            return $this->error('页面不存在.');
        }
        $mkey = 'shopIndex_web_'.$pageid;
        //$body = Cache::get($mkey);
        $page = json_decode($theme['page'],true);
        $tmpPath = '../../customize/';
        $body = '';
        $topfixed = '';
        $this->assign('goodsicon', ['recommand'=>'推荐','hotsale'=>'热销','isnew'=>'新品','sendfree'=>'包邮','istime'=>'限时卖','bigsale'=>'促销']);
        foreach ($page['items'] as $key=>$row) {
            $row['_key'] = $key;

            if ($row['id'] == 'fixedsearch'){//固定顶部搜索额外处理
                $this->assign('diyInfo', $row);
                $topfixed .= $this->fetch($tmpPath.$row['id']);
                continue;
            }
            if ($row['id'] == 'notice'){//公告处理
                if ($row['params']['noticedata'] == 0){
                    $ArticleModel = new \app\mainadmin\model\ArticleModel();
                    $noticeList = $ArticleModel->limit($row['noticenum'])->select()->toArray();
                }else{
                    $noticeList = $row['data'];
                }
                $this->assign('noticeList', $noticeList);
            }elseif ($row['id'] == 'icongroup'){//图标组处理，针对特定名词，查询相应的订单数量

            }elseif ($row['id'] == 'picturew'){//图片橱窗
                foreach ($row['data'] as $arr){
                    $_data[] = $arr;
                }
                $this->assign('_data', $_data);
            }elseif($row['id'] == 'goods'){
                $GoodsModel = new \app\shop\model\GoodsModel();
                foreach ($row['data'] as $key=>$good){
                    if ($good['gid'] > 0 ){
                        $good = $GoodsModel->info($good['gid']);
                        $ginfo['thumb'] = $good['goods_thumb'];
                        $ginfo['title'] = $good['goods_name'];
                        $ginfo['subtitle'] = $good['short_name'];
                        $ginfo['price'] = $good['shop_price'];
                        $ginfo['gid'] = $good['goods_id'];
                        $ginfo['total'] = $good['goods_number'];
                        $ginfo['price'] = $good['shop_price'];
                        $ginfo['productprice'] = $good['market_price'];
                        $ginfo['sales'] = $good['sale_num'];
                        $ginfo['bargain'] = 0;
                        $ginfo['credit'] = null;
                        $ginfo['ctype'] = null;
                        $ginfo['gtype'] = null;
                        $ginfo['linkurl'] = url('shop/goods/info',['id'=>$ginfo['gid']]);
                        $row['data'][$key] = $ginfo;
                    }
                }
            }
            //print_r($row);
            $this->assign('diyInfo', $row);
            $body .= $this->fetch($tmpPath.$row['id']);
        }

        $this->assign('topfixed', $topfixed);
        $this->assign('body', $body);
        $this->assign('page', $page['page']);
        return $this->fetch($tmpPath.'index');
    }
	/*------------------------------------------------------ */
	//-- 自定义首页 -- 旧
	/*------------------------------------------------------ */
	protected function shopIndex(){
		
		$mkey = 'shopIndex_web';
        $ShopPageTheme = new \app\shop\model\ShopPageTheme();
		$theme = $ShopPageTheme->find(1);
		if (empty($theme)) return $this->index(true);
	    $body = Cache::get($mkey);
		if (empty($body)){
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
                                      $rowb['products'][$keyc]['id'] = '#';
								  }else{							 
									  $grow = $GoodsModel->info($rowc['id']);
									  if(!$grow['is_on_sale']){
										 unset($rowb['products'][$keyc]);
										 continue; 
									  }
									  $rowc['thumb']['url'] = $grow['goods_thumb'];
									  $rowc['name'] = $grow['goods_name'];
                                      $rowc['description'] = $grow['description'];
									  $rowc['par_price'] = $grow['market_price'];
									  $rowc['sale_price'] = $grow['shop_price'];
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
							$grows = $GoodsModel->field('goods_id,goods_thumb,goods_name,market_price,shop_price,is_spec,sale_num,virtual_sale,description')->where($where)->where("isputaway = 1 OR (isputaway = 2  AND  added_time < '".$time."' AND shelf_time > '".$time."' )")->order('update_time desc')->limit($row['dataLimit'])->select();
												
							foreach ($grows as $grow){
								$rowc['id'] = $grow['goods_id'];
								$rowc['thumb']['url'] = $grow['goods_thumb'];
								$rowc['name'] = $grow['goods_name'];
                                $rowc['description'] = $grow['description'];
								$rowc['par_price'] = $grow['market_price'];
								$rowc['sale_price'] = $grow['shop_price'];
								$rowc['vip_price'] = 0;
								$rowc['sale_count'] = $grow['sale_num'] + $grow['virtual_sale'];
								$rowb['products'][$rowc['id']] = $rowc;
							}
						 }						
						 $row['data'][$keyb] = $rowb;
					 }
					 
				}
				
				$this->assign('theme_row', $row);
				$_body = $this->fetch('page/'.$row['componentType']);
				$body .= $_body;
			}
			Cache::set($mkey,$body,60);
		}
		$this->assign('theme', $theme);
		$this->assign('body', $body);
		return $this->fetch('page/index');
	}
	
}?>