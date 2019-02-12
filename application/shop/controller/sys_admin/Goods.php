<?php
namespace app\shop\controller\sys_admin;
use think\Db;

use app\AdminController;
use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;
use app\shop\model\GoodsImgsModel;
use app\shop\model\GoodsVolumePriceModel;
use app\shop\model\GoodsPricesModel;
use app\shop\model\AttributeValModel;
use app\member\model\UsersLevelModel;
use app\distribution\model\DividendRoleModel;
use app\shop\model\ShippingTplModel;

/**
 * 商品相关
 * Class Index
 * @package app\store\controller
 */
class Goods extends AdminController
{
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
    public function initialize(){	
   		parent::initialize();
		$this->Model = new GoodsModel();
		
    }
	
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		$this->getList(true);
        return $this->fetch('index');
    }
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false,$is_delete=0) {
		$where[] = ['store_id','=',$this->store_id];
		$where[] = ['is_delete','=',$is_delete];
		$search['status'] = input('status',0,'intval');
		if ($search['status'] == 1){
			$where[] = ['isputaway','>',0];
		}elseif($search['status'] == 2){
			$where[] = ['isputaway','=',0];
		}
		
		$search['keyword'] =  input('keyword','','trim');
		if (empty($search['keyword']) == false){
			 $where['_string'][] = "( goods_name like '%".$search['keyword']."%')  OR ( goods_sn like '%".$search['keyword']."%')"; 
		}
		
		$this->classList = $this->Model->getClassList();
		$search['cid'] = input('cid',0,'intval');
		if ($search['cid'] > 0 ){
			 $where[] = ['cid','in',$this->classList[$search['cid']]['children']];
		}
		$search['brand_id'] = input('brand_id',0,'intval');
		if ($search['brand_id'] > 0 ){
			 $where[] = ['brand_id','=',$search['brand_id']];
		}
		$search['is_promote'] = input('is_promote',-1,'intval');
		if ($search['is_promote'] >= 0){
			 $where[] = ['is_promote','=',$search['is_promote']];
		}
		
        $this->data = $this->getPageList($this->Model, $where,$this->_field,$this->_pagesize);
		$this->assign("is_delete", $is_delete);
		$this->assign("data", $this->data);
		$this->assign("search", $search);
		$this->assign("classListOpt", arrToSel($this->classList,$search['cid']));
		$this->assign("brandListOpt", arrToSel($this->Model->getBrandList()));
		if ($runData == false){
            $this->data['content'] = $this->fetch('list');
			unset($this->data['list']);
			return $this->success('','',$this->data);
		}
        return true;
    }
	
	/*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
 	public function asInfo($data){
		$ModelList = $this->Model->getModelList();
		$this->assign('modelListOpt',arrToSel($ModelList,$data['type_model']));//商品模型对应属性调用
		$this->assign("classListOpt", arrToSel($this->Model->getClassList(),$data['cid']));//分类
		$this->assign("brandListOpt", arrToSel($this->Model->getBrandList(),$data['brand_id']));//品牌
		$this->assign("skuModelOpt", arrToSel($ModelList,$data['sku_model'],$data['sku_model']));//商品模型对应sku调用
		$this->assign("SupplyListOpt", arrToSel($this->Model->getSupplyList(),$data['supply_id']));//供货商
		$UsersLevelModel = new UsersLevelModel();
		$this->assign("UsersLevel", $UsersLevelModel->getRows());//会员等级
		$DividendRoleModel = new DividendRoleModel();
		$this->assign("UsersRole", $DividendRoleModel->getRows());//分销身份
		$ShippingTplModel = new ShippingTplModel();
		$this->assign("ShippingTpl", $ShippingTplModel->getRows());//运费模板
		
		$products = $specifications = array();
		if ($data['goods_id'] > 0){
			$imgWhere[] = ['goods_id','=',$data['goods_id']];
			$skuImgWhere = $imgWhere;
			$imgWhere[] = ['sku_val','=',''];
			$skuImgWhere[] = ['sku_val','<>',''];
			if ($data['is_spec'] == 1){//多规格
				$GoodsSkuModel = new GoodsSkuModel();
				$gsrows = $GoodsSkuModel->where('goods_id',$data['goods_id'])->select()->toArray();
				foreach($gsrows as $arr){
					$sku = $arr['sku'];
					$skuval[] = $arr['sku_val'];
					$gsarr['sku_id']    = $arr['sku_id'];
					$gsarr['ProductSn'] = $arr['goods_sn'];
					$gsarr['Price'] = $arr['shop_price'];
					$gsarr['MarketPrice'] = $arr['market_price'];
					$gsarr['Store'] = $arr['goods_number'];
					$gsarr['Weight'] = $arr['goods_weight'];
					$gsarr['ProductCode'] = $arr['bar_code'];				
					$products[$arr['sku_val']] = $gsarr;
				}
				$sku = explode(':',$sku);
				foreach ($skuval as $arr){
					$arr = explode(':',$arr);
					foreach ($arr as $key=>$v){
						if (!in_array($v,$specifications[$sku[$key]])){
							$specifications[$sku[$key]][] = $v * 1;
						}
					}
				}
			}
			$goodsPrices = $this->Model->getPrices($data['goods_id']);
			$this->assign('levelPrice',$goodsPrices['level']);
			$this->assign('rolePrice',$goodsPrices['role']);
			$this->assign('VolumePriceList',$this->Model->getVolumePrice($data['goods_id']));
			$this->assign('limit_user_level',explode(',',$data['limit_user_level']));
			$this->assign('limit_user_role',explode(',',$data['limit_user_role']));
		}else{
			$imgWhere[] = ['goods_id','=',0];
			$imgWhere[] = ['store_id','=',$this->store_id];
			$imgWhere[] = ['admin_id','=',AUID];
			$skuImgWhere = $imgWhere;
			$imgWhere[] = ['sku_val','=',''];
			$skuImgWhere[] = ['sku_val','<>',''];
			
		}
		$sku_imgs = $this->Model->getImgsList($skuImgWhere,true);
		foreach($sku_imgs as $arr){
			$products[$arr['sku_val']]['ProductImgId'] = $arr['img_id'];
			$products[$arr['sku_val']]['ProductImg'] = $arr['goods_img'];		
		}
		
		$this->assign('goods_imgs',$this->Model->getImgsList($imgWhere));
		$this->assign('specifications',json_encode($specifications));
		$this->assign('products',json_encode($products));
		
        return $data;
    }
	/*------------------------------------------------------ */
	//-- 获取商品属性
	/*------------------------------------------------------ */
	public function getAttriBute($goods = array()){
		if (empty($goods)){
			$model_id = input('model_id',0,'intval');
			if ($model_id < 0) return $this->error('商品模型传参失败.');
			$goods_id = input('goods_id',0,'intval');
		}else{
			$model_id = $goods['type_model'];
			$goods_id = $goods['goods_id'];
		}		
		if ($goods_id > 0){
			$this->assign('goodsAttr',$this->Model->getAttributeVal($goods_id));
			
		}
		$model_list = $this->Model->getModelList();
		$this->assign('model',$model_list[$model_id]);	
		if (empty($goods) == false) return false;		
		$data['content'] = $this->fetch('attribute');
		return $this->success('','',$data);	
	}
	
    /*------------------------------------------------------ */
	//-- 验证商品数据
	/*------------------------------------------------------ */
	private function checkData($row){
		
		if (empty($row['goods_name'])){
			return $this->error('请输入商品名称.');
		}
		if ($row['cid'] < 1){
			return $this->error('请选择商品分类.');
		}
		
		$GoodsImgsModel = new GoodsImgsModel();
		if ($row['goods_id'] < 1){
			$imgwhere[] = ['goods_id','=',0];
			$imgwhere[] = ['sku_val','=',''];
			$imgwhere[] = ['store_id','=',$this->store_id];
			$imgwhere[] = ['admin_id','=',AUID];
			$imgCount = $GoodsImgsModel->where($imgwhere)->count('img_id');
			if ($imgCount < 1){
				return $this->error('请上传图片后再操作.');
			}
			unset($imgwhere);
		}
		
		if ($row['is_dividend'] == 1 && $row['dividend_num'] < 1){
			 return $this->error('开启分销商品，分销计算数量不能少于1.');
		}
		
		if ($row['is_spec'] == 0){//单规格
			if (empty($row['goods_sn'])){
				return $this->error('请输入商品货号.');
			}
			if ($row['shop_price'] < 0){
				return $this->error('商品销售价不能少于0.');
			}
			if ($row['goods_number'] < 0){
				return $this->error('商品库存不能少于0.');
			}
			
			$where[] = ['goods_sn','=',$row['goods_sn']];
			$where[] = ['store_id','=',$this->store_id];
			if ($row['goods_id'] > 0){
				$where[] = ['goods_id','<>',$row['goods_id']];
			}
			$count = $this->Model->where($where)->count('goods_id');
			if ($count > 0) return $this->error('操作失败:已存在相同的货号，不允许重复添加.');
			unset($where);
			if ($row['bar_code']){
				$where[] = ['store_id','=',$this->store_id];
				$where[] = ['bar_code','=',$row['bar_code']];
				if ($row['goods_id'] > 0){
					$where[] = ['goods_id','<>',$row['goods_id']];
				}
				$count = $this->Model->where($where)->count('goods_id');
				if ($count > 0) return $this->error('操作失败:已存在相同的货号条形码，不允许重复添加.');
				unset($where);
			}
			if ($row['market_price'] > 0){
				if ($row['market_price'] < $row['shop_price']) return $this->error('操作失败:市场价不能少于销售价.');
			}else{
				$row['market_price'] = $row['shop_price'];//不填写市场价，默认与售价一致
			}
		}else{//多规格处理
			$Products = input('post.Products');
			$goods_sn = array();
			$row['goods_number'] = 0;
			foreach ($Products as $prow){
				if (in_array($prow['ProductSn'],$goods_sn)) return $this->error('子商品列表中货号【'.$prow['ProductSn'].'】重复，系统不允许货号重复.');
				$goods_sn[] = $prow['ProductSn'];		
				$row['goods_number'] += $prow['Store'];

                $row['min_price'] = $row['min_price']==0?$prow['Price']:($prow['Price']<$row['min_price']?$prow['Price']:$row['min_price']);
                $row['max_price'] = ($prow['Price']>$row['max_price'])?$prow['Price']:$row['max_price'];
                $row['market_price'] = ($prow['MarketPrice']>$row['market_price'])?$prow['MarketPrice']:$row['market_price'];
			}


			if (empty($goods_sn)) return $this->error('未知错误，未能获取子商品数据.');
			$GoodsSkuModel = new GoodsSkuModel();
			$where[] = ['store_id','=',$this->store_id];
			$where[] = ['goods_sn','in',$goods_sn];
			if ($row['goods_id'] > 0){
				$where[] = ['goods_id','<>',$row['goods_id']];
			}
			$goodsSn = $GoodsSkuModel->where($where)->field('GROUP_CONCAT(goods_sn) as gsn')->find();
			if (empty($goodsSn['gsn']) == false)  return $this->error('子商品货号【'.$goodsSn['gsn'].'】与其它子商品货号重复，货号不允许重复.');
			unset($where);
		}
		//上架处理
		if($row['isputaway'] == 2){
			if(empty($row['added_time']) || empty($row['shelf_time'])) return $this->error('操作失败:请选择上下架的时间.');
			if (!checkDateIsValid($row['added_time'])) return $this->error('操作失败:上下架的开始时间格式不合法.');
			if (!checkDateIsValid($row['shelf_time'])) return $this->error('操作失败:上下架的结束时间格式不合法.');
			$row['added_time'] = strtotime($row['added_time']);
			$row['shelf_time'] = strtotime($row['shelf_time']);
			if($row['added_time'] >= $row['shelf_time']) return $this->error('操作失败:下架时间必须大于上架时间.');				
		}
		//促销处理
		if($row['is_promote'] == 1){
			if(empty($row['promote_start_date']) || empty($row['promote_end_date'])) return $this->error('操作失败:请选择促销的时间.');
			if (!checkDateIsValid($row['promote_start_date'])) return $this->error('操作失败:促销开始时间格式不合法.');
			if (!checkDateIsValid($row['promote_end_date'])) return $this->error('操作失败:促销结束时间格式不合法.');
			$row['promote_start_date'] = strtotime($row['promote_start_date']);
			$row['promote_end_date'] = strtotime($row['promote_end_date']);
			if($row['promote_start_date'] >= $row['promote_end_date']) return $this->error('操作失败::促销开始时间必须大于促销结束时间.');
		}else{
            $row['is_promote'] = 0;
		    unset($row['promote_start_date'],$row['promote_end_date']);
        }
		//限购处理
		$is_quota = input('post.is_quota',0,'intval');
		if ($is_quota == 0) $row['quota_amount'] = 0;
		//获取图片主图
		$GoodsImages = input('post.GoodsImages');
		if ($GoodsImages){
			$row['goods_img'] = $GoodsImages['path'][0];
			$row['goods_thumb'] = str_replace('.','_thumb.',$row['goods_img']);
		}
		//运费模板
		$undertake = input('undertake','0','intval');
		if ($undertake == 0){
			$row['freight_template'] = 0;
		}
		$row['goods_desc'] = input('goods_desc','','trim,stripslashes');		
		$row['m_goods_desc'] = input('m_goods_desc','','trim,stripslashes');
		if (empty($row['goods_desc']) && empty($row['m_goods_desc'])){
			return $this->error('获取商品详情失败，请核实.');
		}
		//会员购买限制
		$row['limit_user_level'] = empty($row['limit_user_level'])?'':join(',',$row['limit_user_level']);
		//身份购买限制
		$row['limit_user_role'] = empty($row['limit_user_role'])?'':join(',',$row['limit_user_role']);
		
		$row['store_id'] = $this->store_id;//门店ID
		return $row;
	}
	
	/*------------------------------------------------------ */
	//-- 添加前调用
	/*------------------------------------------------------ */
	public function beforeAdd($row){
		$row = $this->checkData($row);
		Db::startTrans();//启动事务
        if($row['is_spec'] == 1){
            $row['shop_price'] =  $row['show_price'];
            $Products = input('post.Products');
            foreach ($Products as $prow){
                $row['price_range_min'] = $row['price_range_min']==0?$prow['Price']:($prow['Price']<$row['price_range_min']?$prow['Price']:$row['price_range_min']);
                $row['price_range_max'] = ($prow['Price']>$row['price_range_max'])?$prow['Price']:$row['price_range_max'];
                $row['market_price'] = ($prow['MarketPrice']>$row['market_price'])?$prow['MarketPrice']:$row['market_price'];
            }
        }
        $row['sort_price'] = $row['shop_price'];
		return $row;
	}
	/*------------------------------------------------------ */
	//-- 添加后调用
	/*------------------------------------------------------ */
	public function afterAdd($row){
		$GoodsImages = input('post.GoodsImages');
		$GoodsImgsModel = new GoodsImgsModel();
		foreach ($GoodsImages['id'] as $key=>$img_id){
			$imgwhere = array();
			$imgwhere[] = ['img_id','=',$img_id];
			$imgwhere[] = ['goods_id','=',0];
			$imgwhere[] = ['sku_val','=',''];
			$imgwhere[] = ['store_id','=',$this->store_id];
			$imgwhere[] = ['admin_id','=',AUID];
			$uparr['sort_order'] = $key;
			$uparr['goods_id'] = $row['goods_id'];
			$GoodsImgsModel->where($imgwhere)->update($uparr);
		}
		unset($imgwhere);	
		
		//规格
		if ($row['is_spec'] == 1){
			$GoodsSkuModel = new GoodsSkuModel();
			$specifications = input('post.specifications');
			$sku = join(':',array_values($specifications['id']));
			$Products = input('post.Products');
			foreach ($Products as $prow){
				$_arr = $prow['Price'].$prow['ProductSn'].$prow['Store'];
				if (empty($_arr)) continue;//空值跳过,不执行生成sku
				$inarr['sku'] = $sku;
				$inarr['goods_id'] = $row['goods_id'];
				$inarr['market_price'] = $prow['MarketPrice'];
				$inarr['shop_price'] = $prow['Price'];
				$inarr['goods_sn'] = $prow['ProductSn'];
				$inarr['bar_code'] = $prow['ProductCode'];
				$inarr['goods_number'] = $prow['Store'];
				$inarr['goods_weight'] = $prow['Weight'];				
				$inarr['add_time'] = $inarr['update_time'] = time();
				$sku_val = array_values($prow['SpecVal']['key']);
				$inarr['sku_val'] = join(':',$sku_val);
				if (empty($inarr['sku_val'])){
					return $this->error('操作失败:获取商品SKU值失败，请重试.');
				}
				$inarr['store_id'] = $this->store_id;
				$inarr['sku_model'] = $row['sku_model'];
				$res = $GoodsSkuModel::create($inarr);
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:子商品写入失败，请重试.');
				}
				$imgwhere = array();
				$imgwhere[] = ['goods_id','=',0];
				$imgwhere[] = ['sku_val','=',$inarr['sku_val']];
				$imgwhere[] = ['store_id','=',$this->store_id];
				$imgwhere[] = ['admin_id','=',AUID];
				$GoodsImgsModel->where($imgwhere)->update($uparr);
			}			
		}
		//处理优惠价格阶梯
		$volume_number = input('post.volume_number');
		if (empty($volume_number) == false){
			$volume_price = input('post.volume_price');
			$GoodsVolumePriceModel = new GoodsVolumePriceModel();
			foreach ($volume_number as $key=>$pval){
				if ($pval < 1 || $volume_price[$key] <= 0) continue;				
				unset($inarr);
				$inarr['goods_id'] = $row['goods_id'];
				$inarr['number']   = $pval;
				$inarr['price']    = $volume_price[$key];
				$res = $GoodsVolumePriceModel::create($inarr);
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:写入阶梯价格失败，请重试.');
				}
			}
		}
		//处理商品属性
		$attr_value_list = input('post.attr_value_list');
		if (empty($attr_value_list) == false){
			$AttributeValModel = new AttributeValModel();
			foreach ($attr_value_list as $key=>$attrVal){	
				unset($inarr);
				$attrVal = is_array($attrVal) ? trim(join(',',$attrVal),',') : $attrVal;
				$inarr['goods_id'] = $row['goods_id'];
				$inarr['model_id']   = $row['type_model'];
				$inarr['attr_id']  = $key;
				$inarr['attr_value'] = $attrVal;
				$res = $AttributeValModel::create($inarr);
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:处理商品属性失败，请重试.');
				}
			}
		}
		$GoodsPricesModel = new GoodsPricesModel();
		//处理会员价格
		if ($row['level_price_type'] > 0){
			$level_price = input('post.level_price');
			foreach ($level_price as $key=>$price){
				if ($price <= 0) continue;
				unset($inarr);
				$inarr['goods_id']       = $row['goods_id'];
				$inarr['type']           = 'level';
				$inarr['by_id']          = $key;
				$inarr['price']          = $price * 1;
				$res = $GoodsPricesModel::create($inarr);
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:处理会员价格失败，请重试.');
				}
			}
		}
		//处理身份价格
		if ($row['role_price_type'] > 0){
			$role_price = input('post.role_price');
			foreach ($role_price as $key=>$price){
				if ($price <= 0) continue;
				unset($inarr);
				$inarr['goods_id']       = $row['goods_id'];
				$inarr['type']           = 'role';
				$inarr['by_id']          = $key;
				$inarr['price']          = $price * 1;
				$res = $GoodsPricesModel::create($inarr);
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:处理身份价格失败，请重试.');
				}
			}
		}
    	Db::commit();// 提交事务
        $this->Model->cleanMemcache($row['goods_id']);
		$this->_log($row['goods_id'],'添加商品：'.$row['goods_name']);
		return $this->success('添加成功',url('index'));
	}
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
	public function beforeEdit($row){
		$row = $this->checkData($row);
		if ($row['is_spec'] == 0){
			$goods_number = $row['goods_number'];
            unset($row['goods_number']);
			if ($goods_number > 0){
				$row['goods_number'] = Db::raw('goods_number +'.$goods_number);
			}elseif ($goods_number < 0){
				$row['goods_number'] = Db::raw('goods_number '.$goods_number);
			}
		}else{
            $row['shop_price'] =  $row['show_price'];
        }
        $row['sort_price'] = $row['shop_price'];
        $row['is_best'] =  $row['is_best'] * 1;
        $row['is_hot'] =  $row['is_hot'] * 1;
        $row['is_new'] =  $row['is_new'] * 1;
        $row['virtual_sale'] +=  $row['sale_num'];
        $row['virtual_collect'] +=  $row['collect_num'];		
		Db::startTrans();//启动事务		
		return $row;
	}
	/*------------------------------------------------------ */
	//-- 修改后调用
	/*------------------------------------------------------ */
	public function afterEdit($row){
		if ($row['is_spec'] == 1){
			$GoodsSkuModel = new GoodsSkuModel();	
				
		  	$skudelwhere[] = ['goods_id','=',$row['goods_id']];
			$skudelwhere[] = ['sku_model','<>',$row['sku_model']];
			$sku_count = $GoodsSkuModel->where($skudelwhere)->count('sku_id');
			if ($sku_count > 0){
				$res = $GoodsSkuModel->where($skudelwhere)->delete();
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:删除旧规格商品失败，请重试.');
				}
			}
			$specifications = input('post.specifications');
			$sku = join(':',array_values($specifications['id']));
			$Products = input('post.Products');
			foreach ($Products as $prow){
				$_arr = $prow['Price'].$prow['ProductSn'].$prow['Store'];
				if (empty($_arr)) continue;//空值跳过,不执行生成sku
				$inarr['sku'] = $sku;			
				$inarr['market_price'] = $prow['MarketPrice'];
				$inarr['shop_price'] = $prow['Price'];
				$inarr['goods_sn'] = $prow['ProductSn'];
				$inarr['bar_code'] = $prow['ProductCode'];				
				$inarr['goods_weight'] = $prow['Weight'];
				$sku_val = array_values($prow['SpecVal']['key']);
				$inarr['sku_val'] = join(':',$sku_val);
				if (empty($inarr['sku_val'])){
					return $this->error('操作失败:获取商品SKU值失败，请重试.');
				}
				$inarr['store_id'] = $this->store_id;
				$inarr['update_time'] = time();
				if ($prow['SkuId'] > 0){
					if ($prow['Store'] > 0){
						$inarr['goods_number'] = Db::raw('goods_number +'.$prow['Store']);
					}elseif ($prow['Store'] < 0){
						$inarr['goods_number'] = Db::raw('goods_number '.$prow['Store']);
					}
					$res = $GoodsSkuModel->where('sku_id',$prow['SkuId'])->update($inarr);
				}else{
					$inarr['goods_id'] = $row['goods_id'];
					$inarr['sku_model'] = $row['sku_model'];
					$inarr['add_time'] = $inarr['update_time'];
					$inarr['goods_number'] = $prow['Store'];
					$res = $GoodsSkuModel::create($inarr);					
				}
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:写入新规格商品失败，请重试.');
				}
			}			
		}
		//处理优惠价格阶梯
		$volume_number = input('post.volume_number');
		$volume_price = input('post.volume_price');
		$GoodsVolumePriceModel = new GoodsVolumePriceModel();
		$GoodsVolumePriceModel->where('goods_id',$row['goods_id'])->delete();	
		foreach ($volume_number as $key=>$pval){
			if ($pval < 1) continue;
			$inarr = array();
			$inarr['goods_id']    = $row['goods_id'];
			$inarr['number']  = $pval;
			$inarr['price'] = $volume_price[$key];
			$res = $GoodsVolumePriceModel::create($inarr);
			if ($res < 1){
				Db::rollback();// 回滚事务
				return $this->error('操作失败:写入阶梯价格失败，请重试.');
			}
		}
		//处理商品属性
		$attr_value_list = input('post.attr_value_list');
		$AttributeValModel = new AttributeValModel();
		$AttributeValModel->where('goods_id',$row['goods_id'])->delete();
		foreach ($attr_value_list as $key=>$attrVal){	
			$inarr = array();
			$inarr['goods_id'] = $row['goods_id'];
			$inarr['model_id']   = $row['type_model'];
			$inarr['attr_id']  = $key;
			$inarr['attr_value'] = is_array($attrVal) ? trim(join(',',$attrVal),',') : $attrVal;
			$res = $AttributeValModel::create($inarr);
			if ($res < 1){
				Db::rollback();// 回滚事务
				return $this->error('操作失败:写入商品属性失败，请重试.');
			}
		}
		
		//处理会员价格	
		$GoodsPricesModel = new GoodsPricesModel();
		$GoodsPricesModel->where('goods_id',$row['goods_id'])->delete();
		if ($row['level_price_type'] > 0){
			$level_price = input('post.level_price');
			foreach ($level_price as $key=>$price){
				$inarr = array();
				$inarr['goods_id'] = $row['goods_id'];
				$inarr['type']     = 'level';
				$inarr['by_id']    = $key;
				$inarr['price']    = $price * 1;
				$res = $GoodsPricesModel::create($inarr);
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:处理会员价格失败，请重试.');
				}
			}
		}
		//处理身份价格
		if ($row['role_price_type'] > 0){
			$role_price = input('post.role_price');
			foreach ($role_price as $key=>$price){
				if ($price <= 0) continue;
				unset($inarr);
				$inarr['goods_id']       = $row['goods_id'];
				$inarr['type']           = 'role';
				$inarr['by_id']          = $key;
				$inarr['price']          = $price * 1;
				$res = $GoodsPricesModel::create($inarr);
				if ($res < 1){
					Db::rollback();// 回滚事务
					return $this->error('操作失败:处理身份价格失败，请重试.');
				}
			}
		}
		Db::commit();// 提交事务
		$this->_log($row['goods_id'],'修改商品：'.$row['goods_name']);
		return $this->success('修改成功',url('index'));
	}
	/*------------------------------------------------------ */
	//-- 快捷修改
	/*------------------------------------------------------ */
	public function afterAjax($id,$data){
		$log = '快速修改商品';
		$this->_Log($id,$log);
		$this->Model->cleanMemcache($id);
	}
	/*------------------------------------------------------ */
	//-- 删除商品
	/*------------------------------------------------------ */
	public function del(){
		$goods_id = input('goods_id',0,'intval');
		if ($goods_id<1) return $this->error('传递参数失败！');
		$data['is_delete'] = 1;
		$res = $this->Model->where('goods_id',$goods_id)->update($data);
		if ($res < 1)  return $this->error();
		$this->Model->cleanMemcache($goods_id);
		$this->_log($goods_id,'删除商品放入回收站');//记录日志
		return $this->success('操作成功.');
	}
	/*------------------------------------------------------ */
	//-- 商品回收站
	/*------------------------------------------------------ */
	public function trash(){
		$this->trashList(true);
        return $this->fetch('index');
	}
	/*------------------------------------------------------ */
	//-- 商品回收站查询
	/*------------------------------------------------------ */
	public function trashList($runData = false){
		return $this->getList($runData,1);
	}
	/*------------------------------------------------------ */
	//-- 还原商品
	/*------------------------------------------------------ */
	public function revert(){
		$goods_id = input('goods_id',0,'intval');
		if ($goods_id<1) return $this->error('传递参数失败！');
		$data['is_delete'] = 0;
		$res = $this->Model->where('goods_id',$goods_id)->update($data);
		if ($res < 1)  return $this->error();
		$this->Model->cleanMemcache($goods_id);
		$this->_log($goods_id,'还原商品');//记录日志
		return $this->success('操作成功.');
	}
	/*------------------------------------------------------ */
	//-- 搜索商品
	/*------------------------------------------------------ */
	public function searchBox(){
		$this->_pagesize = 10;
		$this->_field = 'goods_id,goods_sn,goods_name,is_spec,shop_price,show_price';
		$this->getList(true);
		$result['data'] = $this->data;
		if ($this->request->isPost()) return $this->ajaxReturn($result);
		$this->assign("classListOpt", arrToSel($this->classList,input('cid',0,'intval')));
		$this->assign("_menu_index", input('_menu_index','','trim'));
		$this->assign("searchType", input('searchType','','trim'));
		return response($this->fetch());
	}
}
