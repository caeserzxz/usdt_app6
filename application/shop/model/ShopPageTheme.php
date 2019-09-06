<?php
/*------------------------------------------------------ */
//-- 商城自定义模板
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\shop\model;
use app\BaseModel;
use think\Db;
use think\facade\Cache;
use think\facade\Config;

class ShopPageTheme extends BaseModel {
    protected $mkey = 'ShopPageTheme_';
	protected $table = 'shop_page_theme';
    protected $pk = 'st_id';
    /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm($this->mkey,'del');
		Cache::rm('shopIndex_web','del');
    }
	/*------------------------------------------------------ */
	//-- 获取详情
	/*------------------------------------------------------ */
    public function info()
	{	
		$info = Cache::get($this->mkey);
		if ($info) 	return $info;
		$info = $this->find();
		if ($info) $info = $this->evalInfo($info);
		Cache::set($this->mkey,$info,600);
		return $info;
	}
	/*------------------------------------------------------ */
	//-- 默认商品
	/*------------------------------------------------------ */
	function defPproducts(){
		 $defPproducts['T1'] =array(
					'id' => 'T1',
					'name' => '展示测试商品一',
					'par_price' => '296.00',
					'sale_price' => '399.00',   
					'vip_price' => '100.00',                                     
					'sale_count' => 0,
					'thumb' => array('url'=>'/static/editPage/images/dome/dc77K1423272873.jpg')
				 );
		 $defPproducts['T2'] =array(
					'id' => 'T2',
					'name' => '展示测试商品二',
					'par_price' => '296.00',
					'sale_price' => '369.00',
					'vip_price' => 0,
					'sale_count' => 0,
					'thumb' => array('url'=>'/static/editPage/images/dome/rlDbJ1423273827.jpg')
				 );
		
		return $defPproducts;
	}
	/*------------------------------------------------------ */
	//-- 模板json处理
	/*------------------------------------------------------ */
    public function evalInfo($info)
	{	
		$rows = json_decode($info['page'],true);		
		$defPproducts = $this->defPproducts();
		$goods_cats = (new CategoryModel)->where(['pid'=>0])->select();
		$Goods = new GoodsModel();
		foreach ($rows['pageElement'] as $key=>$row){
			//导航处理
			if ($row['componentType']=='mainmenu'){		
				//进行已有的导航分类判断		
				foreach ($row['data'] as $keyb=>$rowb){
					if (empty($rowb['id'])) continue;
					unset($row['data'][$keyb]);
				}
				//执行是否存在新的分类
				foreach ($goods_cats as $cg){
					$rowb['id'] = $cg['id'];
					$rowb['name'] = empty($cg['mobile_name'])?$cg['name']:$cg['mobile_name'];
					$rowb['visible'] = 1;
					$rowb['active'] = 0;
					$row['data'][] = $rowb;
				}				
			}elseif ($row['componentType']=='products'){//商品处理
			
				foreach ($row['data'] as $keyb=>$rowb){
					foreach ($rowb['products'] as $keyc=>$rowc){						
						if (is_numeric($rowc['id'])){
							$goods = $Goods->field('goods_id,goods_thumb,goods_name,market_price,shop_price,is_spec,sale_num,virtual_sale,description')->where('goods_id',$rowc['id'])->find();
							$rowc['name'] = $goods['goods_name'];
                            $rowc['description'] = $goods['description'];
							$rowc['par_price'] = $goods['market_price'];
							$rowc['sale_price'] = priceShow($goods['shop_price']);
							$rowc['sale_count'] = $goods['sale_num'] + $goods['virtual_sale'];
							$rowc['vip_price'] = 0;
							$rowc['thumb']['url'] = $goods['goods_thumb'];
						}else{
							$rowc = $defPproducts[$rowc['id']];							
						}

						$rowb['products'][$keyc] = $rowc;
					}
					$row['data'][$keyb] = $rowb;
				}
				
			}
			$rows['pageElement'][$key] = $row;
		}
		$info['page'] = json_encode($rows); 
		return $info;
	}
	/*------------------------------------------------------ */
	//-- 默认控件定义
	/*------------------------------------------------------ */
    public function componentDefault()
	{	
		$defPproducts = $this->defPproducts();
		foreach ($defPproducts as $key=>$row){
			$defPproducts[] = $row;
			unset($defPproducts[$key]);
		}
		$component['ads'] = array(
			'componentId' => '',
            'componentType' => 'ads',
            'componentName' => '广告图',
            'titleTheme' => 'title-theme-1',
            'hasMarginTop' => 'no',
            'templateId' => 'tpl_1',
            'themeId' => 'theme-1',
            'data' => array
                (
                    'name' => '图1',
                    'src' => '/static/editPage/images/ads/images/ad.png',
                    'link' => ''
                )		
		);
		$component['contact'] = array(
		 	'componentId' => '',
            'componentType' => 'contact',
            'componentName' => '联系我们',
            'titleTheme' => 'title-theme-1',
            'hasMarginTop' => 'no',
            'templateId' => 'tpl_1',
            'themeId' => 'theme-1',
            'phone' => '4008 020 023',
            'label' => '联系我们'
        );
		$component['mainmenu'] = array(
			'hasTitle' => 'no',
            'componentType' => 'mainmenu',
            'componentId' => '',
            'componentName' => '主菜单',
            'titleTheme' => 'title-theme-1',
            'hasMarginTop' => 'no',
            'templateId' => 'tpl_1',
            'themeId' => 'theme-1',
            'data' => array(array('name' => '首页','active' => 1,'visible' => 1))
		);
		$component['navigator'] = array(
            'componentId' => '',
            'componentType' => 'navigator',
            'componentName' => '导航栏',
            'titleTheme' => 'title-theme-1',
            'hasBorder' => 'no',
            'hasMarginTop' => 'no',
            'templateId' => 'tpl_1',
            'themeId' => 'theme-1',
            'data' => array(
                    array('name' => '购物车','code' => 'shopping-cart','link' => 'shop/flow/cart','visible' => 1),
					array('name' => '我的订单','code' => 'orders','link' => 'shop/order/index','visible' => 1),
					array('name' => '我的信息','code' => 'collect','link' => 'member/center/userinfo','visible' => 1),
                    array('name' => '个人中心','code' => 'profile','link' => 'member/center/index','visible' => 1),
					array('name' => '全部分类','code' => 'categories','link' => 'shop/index/allsort','visible' => 1),
					array('name' => '正品保障','code' => 'guarantee','link' => '#','visible' => 1),
					array('name' => '七天退换','code' => 'returns','link' => '#','visible' => 1),
                    array('name' => '免费维护','code' => 'maintain','link' => '#','visible' => 1),
					array('name' => '全场包邮','code' => 'postage','link' => '#','visible' => 1),
					array('name' => '闪电发货','code' => 'deliver','link' => '#','visible' => 1),
					array('name' => '货到付款','code' => 'cod','link' => '#','visible' => 1),
					array('name' => '客户服务','code' => 'service','link' => '#','visible' => 1)
                )
        );
		$component['slideshow'] = array(
            'componentId' => '',
            'componentName' => '幻灯片',
            'componentType' => 'slideshow',
            'rtl' => 0,
            'hasBorder' => 'no',
            'autoplayTimeout' => 3000,
            'titleTheme' => 'title-theme-1',
            'hasMarginTop' => 'no',
            'templateId' => 'tpl_1',
            'themeId' => 'theme-1',
            'data' => array(
                   array('name' => '图1','src' => '/static/editPage/images/slideshow/images/slideshow-1.png','link' => ''),
				   array('name' => '图2','src' => '/static/editPage/images/slideshow/images/slideshow-2.png','link' => ''),
				   array('name' => '图3','src' => '/static/editPage/images/slideshow/images/slideshow-3.png','link' => '')
				)
        );
		$component['exttypeset'] = array(
            'componentId' => '',
            'componentType' => 'exttypeset',
            'componentName' => '扩展排版',
            'hasTitle' => 'yes',
            'titleTheme' => 'title-theme-1',
            'hasMarginTop' => 'no',
            'templateId' => 'tpl_1',
            'themeId' => 'theme-1',
            'data' => array(
				   array('name' => '标题1','nameb' => '','src' => '/static/editPage/images/exttypeset/images/d.png','link' => ''),
				   array('name' => '标题2','nameb' => '','src' => '/static/editPage/images/exttypeset/images/d.png','link' => ''),
				   array('name' => '标题3','nameb' => '','src' => '/static/editPage/images/exttypeset/images/d.png','link' => ''),
				   array('name' => '标题4','nameb' => '','src' => '/static/editPage/images/exttypeset/images/d.png','link' => ''),
				   array('name' => '标题5','nameb' => '','src' => '/static/editPage/images/exttypeset/images/d.png','link' => '')
			)
        );
		
		$component['extmenu'] = array(
            'componentId' => '',
            'componentType' => 'extmenu',
            'componentName' => '自定菜单',
            'hasTitle' => 'yes',
            'titleTheme' => 'title-theme-1',
            'hasMarginTop' => 'no',
            'templateId' => 'tpl_1',
            'themeId' => 'theme-1',
            'data' => array(
				   array('name' => '','src' => '/static/editPage/images/extmenu/images/d.png','link' => ''),
				   array('name' => '','src' => '/static/editPage/images/extmenu/images/d.png','link' => ''),
				   array('name' => '','src' => '/static/editPage/images/extmenu/images/d.png','link' => ''),
				   array('name' => '','src' => '/static/editPage/images/extmenu/images/d.png','link' => '')
			)
        );
		
		$component['products'] = array(
            'componentId' => '',
            'componentType' => 'products',
            'hasTitle' => 'yes',
            'componentName' => '商品橱窗',
            'titleTheme' => 'title-theme-1',
            'hasBorder' => 'no',
            'hasMarginTop' => 'no',	
			'showSaleNum' => 'no',		
            'dataLimit' => 2,
            'templateId' => 'tpl_1',
            'themeId' => 'theme-1',
            'tabsVisible' => 0,
            'tabsWidth' => '100%',
            'activeTab' => 0,
            'data' => array(
                   array(
				   	  'goodsDataType' => 'custom',
					  'goodsVisible'=>1,
                      'tabName' => '标签一',
                      'visible' => 1,
					  'products' => $defPproducts
					),
					array(
					  'goodsDataType' => 'custom',
					  'goodsVisible'=>1,
                      'tabName' => '标签二',
                      'visible' => 0,
					  'products' => array()
					),
					array(
					  'goodsDataType' => 'custom',
					  'goodsVisible'=>1,
                      'tabName' => '标签三',
                      'visible' => 0,
					  'products' => array()
					),
					array(
					  'goodsDataType' => 'custom',
					  'goodsVisible'=>1,
                      'tabName' => '标签四',
                      'visible' => 0,
					  'products' => array()
					)
             )
        );
		$cg_list = Db::name('shop_goods_category')->where(['pid'=>0])->select();		
		foreach ($cg_list as $cg){
			$rowb['id'] = $cg['id'];
			$rowb['name'] = $cg['name'];
			$rowb['visible'] = 1;
			$rowb['active'] = 0;
			$component['mainmenu']['data'][] = $rowb;
		}			
		return json_encode($component);
	}
}
?>
