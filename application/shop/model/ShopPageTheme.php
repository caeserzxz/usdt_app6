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
        Cache::rm('xcx_diy_index','del');
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
    /**
     * 小程序路径替换
	 * $json string 自定义装修内容
     */
    private function xcxPathReplace($json){
        $urla = $urlb = [];
		$repUrl[] = [str_replace('/','\\/',config('config.host_path')),'\/pages\/index\/index'];
    	$repUrl[] = ['\/shop\/goods\/info\/id\/','\/pages\/productDetails\/productDetails?goods_id='];
        $repUrl[] = ['\/member\/center\/index','\/pages\/my\/my'];
        $repUrl[] = ['\/shop\/goods\/index\/cid\/','\/pages\/goodsList\/goodsList?categoryid='];
        $repUrl[] = ['\/shop\/goods\/index','\/pages\/goodsList\/goodsList'];
        $repUrl[] = ['\/shop\/flow\/cart','pages\/cart\/cart'];
        $repUrl[] = ['\/shop\/index\/allsort','\/pages\/classify\/classify'];
        $repUrl[] = ['\/shop\/order\/index','\/pages\/myorders\/myorders'];
        $repUrl[] = ['\/member\/center\/address','\/pages\/address\/address'];
        $repUrl[] = ['\/member\/center\/userinfo','\/pages\/personalData\/personalData'];
        $repUrl[] = ['\/member\/my_team\/index','\/pages\/myfans\/myfans'];
        $repUrl[] = ['\/member\/wallet\/index','\/pages\/myBalance\/myBalance'];
        $repUrl[] = ['\/member\/user_sign\/index','\/pages\/sign\/sign'];
        $repUrl[] = ['\/integral\/goods\/index',''];//积分商城入口，未发现
        $repUrl[] = ['\/distribution\/role_goods\/index',''];//身份商品入口，未发现
        $repUrl[] = ['\/shop\/bonus\/bonuscenterx','\/pages\/couponCenter\/couponCenter'];
        $repUrl[] = ['\/fightgroup\/index\/index','\/pages\/groupBuy\/groupBuy'];
        $repUrl[] = ['\/second\/index\/index','\/pages\/seckill\/seckill'];
        $repUrl[] = ['\/shop\/article\/info\/id\/',''];//文章内容，未发现
        $repUrl[] = ['\/shop\/index\/diypage\/pageid\/',''];//其它装修，未发现

        foreach ($repUrl as $url){
            $urla[] = $url[0];
            $urlb[] = $url[1];
		}
        return str_replace($urla,$urlb,$json);
	}
    /**
     * 获取模板To微信小程序
     */
	public function getToWxApp(){
        $mkey = 'xcx_diy_index';
        $page = Cache::get($mkey);
        if (empty($page) == false){
        	return $page;
		}
        $theme = $this->where('is_index',1)->find();
        if (empty($theme)) return [];
        $host_path = config('config.host_path');
        $page = $this->xcxPathReplace($theme['page']);//替换成小程序路径
        $page = str_replace(['\/upload','\/static'],[$host_path.'\/upload',$host_path.'\/static'],$page);
        $page = json_decode($page,true);
        $GoodsModel = new GoodsModel();
        foreach ($page['items'] as $key=>$row){
            $row['data'] = array_values($row['data']);
            if ($row['id'] == 'notice'){
            	if ($row['params']['noticedata'] == 0){
                    $ArticleModel = new \app\mainadmin\model\ArticleModel();
                    $noticeList = $ArticleModel->where('type',1)->limit($row['params']['noticenum'])->select()->toArray();
                    $row['data'] = [];
					foreach ($noticeList as $notice){
						$_row = [];
                        $_row['title'] = $notice['title'];
                        $_row['linkurl'] = '';
                        $row['data'][] = $_row;
					}
				}
			}elseif ($row['id'] == 'goods'){
            	switch ($row['style']['goodsicon']){
					case 'recommand':
                        $row['style']['goodsicon'] = '推荐';
                        break;
                    case 'hotsale':
                        $row['style']['goodsicon'] = '热销';
                        break;
                    case 'isnew':
                        $row['style']['goodsicon'] = '新上';
                        break;
                    case 'sendfree':
                        $row['style']['goodsicon'] = '包邮';
                        break;
                    case 'istime':
                        $row['style']['goodsicon'] = '限时卖';
                        break;
                    case 'bigsale':
                        $row['style']['goodsicon'] = '促销';
                        break;
					default:
						break;
				}
                if ($row['params']['goodsdata'] > 0){
                    $row['data'] = [];
                    $classList = $GoodsModel->getClassList();
                    $where[] = ['is_delete','=',0];
                    $where[] = ['is_alone_sale','=',1];
                    $where[] = ['isputaway','=',1];
                    $where[] = ['is_promote','=',0];
                    if ($row['params']['goodsdata'] == 1 && $row['params']['cateid'] > 0){
                        $where[] = ['cid','in',$classList[$row['params']['cateid']]['children']];
                    }

                    switch ($row['params']['goodssort']){
                        case 1://按销量
                            $sqlOrder = "sale_num DESC";
                            break;
                        case 2://价格降序
                            $sqlOrder = "shop_price DESC";
                            break;
                        case 3://价格降序
                            $sqlOrder = "shop_price ASC";
                            break;
                        default://综合
                            $sqlOrder = "virtual_sale DESC,virtual_collect DESC,is_best DESC";
                            break;
                    }
                    $goodIds = $GoodsModel->where($where)->order($sqlOrder)->limit($row['params']['goodsnum'])->column('goods_id');
                    foreach ($goodIds as $key=>$gid){
                        $good = $GoodsModel->info($gid);
                        $ginfo['thumb'] = str_replace(['/upload','/static'],[$host_path.'/upload',$host_path.'/static'],$good['goods_thumb']);
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
                        $ginfo['linkurl'] = str_replace('\\/','/',$this->xcxPathReplace('\/shop\/goods\/info\/id\/'.$ginfo['gid']));
                        $row['data'][$key] = $ginfo;
                    }
                }else{
                    foreach ($row['data'] as $key=>$good){
                        if ($good['gid'] > 0 ){
                            $good = $GoodsModel->info($good['gid']);
                            $ginfo['thumb'] = str_replace(['/upload','/static'],[$host_path.'/upload',$host_path.'/static'],$good['goods_thumb']);
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
                            $ginfo['linkurl'] = str_replace('\\/','/',$this->xcxPathReplace('\/shop\/goods\/info\/id\/'.$ginfo['gid']));
                            $row['data'][$key] = $ginfo;
                        }
                    }
                }

				if ($row['params']['goodsscroll'] == 1){//商品划动支持
					if ($row['style']['liststyle'] == 'one'){
						$snum = 1;
					}elseif ($row['style']['liststyle'] == 'block'){
                        $snum = 2;
                    }else{
                        $snum = 3;
					}
					$goods = [];
                    $row['data_temp'] = [];
                    $gi = 0;
					foreach ($row['data'] as $data){
                        $goods[] = $data;
                        $gi++;
						if ($gi % $snum == 0 ){
                            $row['data_temp'][] = $goods;
                            $goods = [];
						}
					}
					if (empty($goods) == false){
                        $row['data_temp'][] = $goods;
					}
					unset($row['data']);
				}
			}
            $page['items'][$key] = $row;
		}
        Cache::set($mkey,$page,5);
        return $page;
	}
}
?>
