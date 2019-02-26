<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 商品表
/*------------------------------------------------------ */
class GoodsModel extends BaseModel
{
	protected $table = 'shop_goods';
	public  $pk = 'goods_id';
	protected $mkey = 'shop_goods_mkey_';
	/*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache($goods_id = 0){
		Cache::rm($this->mkey.$goods_id);
		Cache::rm('shop_goods_prices_'.$goods_id);
		Cache::rm('shop_goods_attribute_val_'.$goods_id);
	}
	/*------------------------------------------------------ */
	//-- 获取商品品牌
	/*------------------------------------------------------ */
    public function getBrandList($cid = 0){
        $BrandModel = new BrandModel();
		return $BrandModel->getRows($cid);
	}
	/*------------------------------------------------------ */
	//-- 获取商品分类
	/*------------------------------------------------------ */
    public function getClassList($pid = false){
        $CategoryModel = new CategoryModel();
		return $CategoryModel->getRows($pid);
	}
	/*------------------------------------------------------ */
	//-- 获取首页商品分类
	/*------------------------------------------------------ */
    public function getIndexClass(){
		$mkey = 'IndexClassGoodsList';
		$catList = Cache::get($mkey);
		if (empty($catList) == false) return $catList;
		$CategoryModel = new CategoryModel();
		$catRows = $CategoryModel->getRows();
		$where[] = ['is_index','=',1];
		$where[] = ['status','=',1];
		$catList = $CategoryModel->where($where)->select()->toArray();
		foreach ($catList as $key=>$cat){
			 $gwhere = [];
			 $gwhere[] = ['cid','in',$catRows[$cat['id']]['children']];
			 $gwhere[] = ['isputaway','=',1];
			 $gwhere[] = ['is_delete','=',0];
			 $gwhere[] = ['store_id','=',0];
			 $gooodsList = $this->where($gwhere)->order('is_hot desc,goods_id desc')->limit(3)->column('goods_id');
			 foreach ($gooodsList as $_key=>$goods_id){
				 $gooodsList[$_key] = $this->info($goods_id);
			 }
			 $catList[$key]['gooodsList'] = $gooodsList;
		}
		Cache::set($mkey,$catList,60);
		return $catList;
	}
	/*------------------------------------------------------ */
	//-- 获取首页推荐商品
	/*------------------------------------------------------ */
    public function getIndexBestGoods(){
		$mkey = 'IndexBestGoodsList';
		$goodsList = Cache::get($mkey);
		if (empty($goodsList) == false) return $goodsList;
		$gwhere[] = ['isputaway','=',1];
		$gwhere[] = ['is_delete','=',0];	
		$gwhere[] = ['store_id','=',0];
		$gooodsList = $this->where($gwhere)->order('is_best desc,goods_id desc')->limit(10)->column('goods_id');
		foreach ($gooodsList as $key=>$goods_id){				 
		   $gooodsList[$key] = $this->info($goods_id);
		}
		Cache::set($mkey,$gooodsList,60);
		return $gooodsList;
	}
	/*------------------------------------------------------ */
	//-- 获取分类页相关分类信息
	/*------------------------------------------------------ */
    public function getClassToAllSort(){
		$mkey = 'ClassToAllSort';
		$list = Cache::get($mkey);
		if (empty($list['rows']) == false) return $list;
		$CategoryModel = new CategoryModel();
		$rows = $CategoryModel->getRows();		
		$list['best'] = array();
		$bestids = array();
		foreach ($rows as $row){
			if ($row['status'] == 0) continue;
			$children = explode(',',$row['children']);
			$row['children'] = array();
			if ($row['is_best'] == 1){
				if (in_array($row['id'],$bestids) == false && count($children) > 1){//不存在的,并且有下级才写入	
					$row['children'] = array();				   		
					foreach ($children as $id){
						$rowb = $rows[$id];
						if ($rowb['status'] == 0) continue;
						if ($rowb['pid'] == $row['id']){//必须直属下级才执行
							$row['children'][] = $id;
						}
					}
					if (empty($row['children']) == false){//如果没有直属下级不执行
						$list['best'][] = $row;  
						$bestids = array_merge($bestids,$row['children']);
					}
				}
			}
			if ($row['pid'] > 0) continue;//非顶级分类不执行
			$row['children'] = array();
			foreach ($children as $id){
				$rowb = $rows[$id];
				if ($rowb['status'] == 0 || $rowb['pid'] == 0) continue;
				$row['children'][$rowb['pid']][] = $id;				
			}
			foreach ($row['children'][$row['id']] as $key=>$cid){
				if (empty($row['children'][$cid]) == false){
					unset($row['children'][$row['id']][$key]);
				}
			}
			if (empty($row['children'][$row['id']])){
				unset($row['children'][$row['id']]);
			}			
			$list['rows'][] = $row;
		}
		Cache::set($mkey,$list,60);
		return $list;
	}
	/*------------------------------------------------------ */
	//-- 获取商品模型
	/*------------------------------------------------------ */
    public function getModelList(){
        $GoodsModelModel = new GoodsModelModel();
		return $GoodsModelModel->getRows();
	}
	/*------------------------------------------------------ */
	//-- 获取商品供货商
	/*------------------------------------------------------ */
    public function getSupplyList(){
        $GoodsSupplyModel = new GoodsSupplyModel();
		return $GoodsSupplyModel->getRows();
	}
	/*------------------------------------------------------ */
	//-- 获取商品模型类型数据
	/*------------------------------------------------------ */
    public function getAttributeVal($goods_id = 0){
        $AttributeValModel = new AttributeValModel();
		return $AttributeValModel->getRows($goods_id);
	}
	/*------------------------------------------------------ */
	//-- 获取商品图片
	/*------------------------------------------------------ */
    public function getImgsList($where = array(),$is_sku = false,$byKey = false){
        $GoodsImgsModel = new GoodsImgsModel();
		if ($is_sku == true){
			$where[] = ['sku_val','<>',''];
            $rows = $GoodsImgsModel->where($where)->order('sort_order ASC')->select()->toArray();
            if ($byKey == false) return $rows;
            $list = array();
            foreach ($rows as $row){
                $list[$row['sku_val']] = $row['goods_thumb'];
            }
            return $list;
		}else{
			$where[] = ['sku_val','=',''];
              return $GoodsImgsModel->where($where)->order('sort_order ASC')->select()->toArray();
		}
	}

  	/*------------------------------------------------------ */
	//-- 获取商品价格(等级或)
	/*------------------------------------------------------ */
    public function getPrices($goods_id = 0){
        $GoodsPricesModel = new GoodsPricesModel();
		return $GoodsPricesModel->getRows($goods_id);
	}
   	/*------------------------------------------------------ */
	//-- 获取商品阶梯价格
	/*------------------------------------------------------ */
    public function getVolumePrice($goods_id = 0){
        $GoodsVolumePriceModel = new GoodsVolumePriceModel();
		return  $GoodsVolumePriceModel->getRows($goods_id);
	}
    /*------------------------------------------------------ */
    //-- 是否收藏
    /*------------------------------------------------------ */
    public function isCollect($goods_id = 0,$uid = 0){
        if ($goods_id < 1 || $uid < 1) return 0;
        $GoodsCollectModel = new GoodsCollectModel();
        $where['goods_id'] = $goods_id;
        $where['user_id'] = $uid;
        $where['status'] = 1;
        return $GoodsCollectModel->where($where)->count('collect_id');
    }
	/*------------------------------------------------------ */
	//-- 更新
	/*------------------------------------------------------ */
    public function upInfo(&$data,$map){
		$data['update_time'] = time();
		$res = $this->where($map)->update($data);
		if ($res < 1) return false;
		$this->cleanMemcache($map['goods_id']);
		return true;
	}
   /*------------------------------------------------------ */
	//-- 获取商品信息
	/*------------------------------------------------------ */
    public function info($goods_id){
		$goods = Cache::get($this->mkey.$goods_id);
		if (empty($goods)){
			$goods = $this->where('goods_id',$goods_id)->find();
			if (empty($goods) == true) return array();
			$goods = $goods->toArray();
            if ($goods['is_spec'] == 1) $this->getGoodsSku($goods);
			Cache::set($this->mkey.$goods_id,$goods,600);
		}
		$goods['is_on_sale'] = 0;
        if ($goods['is_delete']) return $goods;
        $goods['_price'] = $goods['shop_price'];
        $time = time();
        if ($goods['is_promote'] == 1 ){
            if ($goods['promote_start_date']<=$time && $goods['promote_end_date']>=$time){//判断促销是否在时间范围内
                $goods['_price'] = $goods['promote_price'];
                $update['sort_price'] = $goods['promote_price'];
            }else{//促销结束，还原排序价格
                $goods['is_promote'] == 0;
            }
            $update['sort_price'] = $goods['shop_price'];
        }
        if ($goods['is_spec'] == 1){
            $goods['exp_min_price'] = explode('.',$goods['min_price']);
            $goods['exp_max_price'] = explode('.',$goods['max_price']);
        }
		$goods['exp_price'] = explode('.',$goods['_price']);
        $goods['sale_count'] = $goods['virtual_sale'];
        $goods['collect_count'] = $goods['virtual_collect'];

		switch($goods['isputaway']){
			case 1 :/**上架**/
				$goods['is_on_sale'] = 1;												
				break;	
			case 2 :/**自动上下架**/
				if ($goods['added_time'] <= $time && $goods['shelf_time'] >=$time){
					$goods['is_on_sale'] = 1;
				}elseif ($goods['shelf_time'] < $time){
                    $update['isputaway'] = 0;
				}
			break;
		}
		if (empty($update)){
            $upmap['goods_id'] = $goods_id;
            $this->upInfo($update,$upmap);
        }
		return $goods;
	}
    /*------------------------------------------------------ */
    //-- 获取商品规格及子商品信息
    /*------------------------------------------------------ */
    public function getGoodsSku(&$goods){
        $goods['lstSKUArr'] = array();
        if ($goods['is_spec'] == 0) return $goods;
        $lstSKUVal = $products = array();
        $GoodsSkuModel = new GoodsSkuModel();
        $gsrows = $GoodsSkuModel->where('goods_id',$goods['goods_id'])->select()->toArray();
        $skuLink = $skuarr = array();
        foreach($gsrows as $row){
            $sku = $row['sku'];
            $skuval[] = $row['sku_val'];
            $row['BuyMaxNum'] = $row['goods_number'];
            if ($goods['is_promote'] == 1){
                $row['exp_price'] = explode('.',$goods['shop_price']);
            }else{
                $row['exp_price'] = explode('.',$row['shop_price']);
            }
            if ($goods['quota_amount'] > 0){
                $row['BuyMaxNum'] = $row['BuyMaxNum'] > $goods['quota_amount'] ? $goods['quota_amount'] : $row['BuyMaxNum'];
            }
            if ($row['BuyMaxNum'] > 0){
                $nkey = array();
                $sku_val = explode(':',$row['sku_val']);
                $_sval = array();
                foreach ($sku_val as $key=>$sval){
                    $_sval[] = $sval;
                    $skuLink[] = join(':',$_sval);
                    $skuarr[$sval] = 1;
                }
            }
            $goods['sub_goods'][$row['sku_val']] = $row;
        }
        unset($row);
        $skuarr = array_keys($skuarr);
        $skuval = $sku.':'.join(':',$skuval);
        $skuval = explode(':',$skuval);
        $where[] = ['id','IN',$skuval];
        $SkuCustomModel = new SkuCustomModel();
        $skurows =  $SkuCustomModel->field('id,val,speid')->where($where)->order('id ASC')->select()->toArray();
        $skuCstom = $isdef = array();
        foreach ($skurows as $row){
            $skuCstom[$row['id']] = $row['val'];
            if ($row['speid'] == 0){
                $lstSKUVal[$row['id']]['name'] = $row['val'];
            }else{
                $speid = $row['speid'];
                unset($row['speid']);
                $row['issel'] = in_array($row['id'],$skuarr) ? 1 : 0;
                if ($row['issel'] == 1 && $isdef[$speid] < 1){
                    $row['isdef'] = $isdef[$speid] = 1;
                }
                $lstSKUVal[$speid]['lstVal'][] = $row;
            }
        }
        foreach ($lstSKUVal as $row){
            $row['is_show'] = empty($row['lstVal'][1]) ? 0 : 1;
            $row['new_name'] =  $row['name'];
            $lstSKUArr[] = $row;
        }
        unset($lstSKUVal,$skuval,$isdef,$skurows);
        $goods['lstSKUArr'] = $lstSKUArr;
        $goods['skuLink'] = $skuLink;
        $goods['skuCstom'] = $skuCstom;
        return $goods;
    }
    /*------------------------------------------------------ */
    //-- 计算商品销售价
    /*------------------------------------------------------ */
    public function evalPrice(&$goods,$buyNum = 0,$spec=''){
        $u_price = $vol_price = 0;
        if ($spec){//多规格商品
            $sub_goods = $goods['sub_goods'][$spec];
            $all_price[] = $sub_goods['shop_price'];
        }else{
            $all_price[] = $goods['shop_price'];
        }

        if (empty($this->userInfo) == false) {//计算会员价格
            $GoodsPricesModel = new GoodsPricesModel();
            if ($goods['level_price_type'] > 0) {
                if ($goods['level_price_type'] == 1) {//会员等级折扣
                    $all_price[] = $goods['shop_price'] *  $this->userInfo['level']['level_pro'] / 100;
                }else{
                    $map['goods_id'] = $goods['goods_id'];
                    $map['type'] = 'level';
                    $map['by_id'] = $this->userInfo['level']['level_id'];
                    $u_price = $GoodsPricesModel->where($map)->value('price');
                    if ($u_price > 0){
                        if ($goods['level_price_type'] == 2){//自定义折扣
                            $all_price[] = $goods['shop_price'] * $u_price / 100;
                        }elseif ($goods['level_price_type'] == 3){//指定固定售价(多规格的子商品价格统一售价)
                            $all_price[] = $u_price;
                        }
                    }
                }
            }
            //计算分销身份价格
            if ($this->userInfo['role_id'] > 0) {
                $map['type'] = 'role';
                $map['by_id'] = $this->userInfo['role_id'];
                $u_price = $GoodsPricesModel->where($map)->value('price');
                if ($u_price > 0) {
                    if ($goods['role_price_type'] == 1) {//自定义折扣
                        $all_price[] =  $goods['shop_price'] * $u_price  / 100;
                    }elseif ($goods['role_price_type'] == 2){//指定固定售价(多规格的子商品价格统一售价)
                        $all_price[] = $u_price;
                    }
                }
            }
        }
        unset($map);
        $GoodsVolumePriceModel = new GoodsVolumePriceModel();
        $volume_price = $GoodsVolumePriceModel->where('goods_id',$goods['goods_id'])->select()->toArray();
        //计算阶梯价格
        foreach ($volume_price as $row){
            if ($buyNum >= $row['number']){
                if ($row['price'] > 0){
                    if ($goods['volume_price_type'] == 1) {//指定固定售价
                        $all_price[] = $row['price'];
                    }elseif ($goods['volume_price_type'] == 2) {//指定固定售价(多规格的子商品价格统一售价)
                         $all_price[] = $goods['shop_price'] * $row['price']   / 100;
                    }
                }

            }
        }
        //判断促销价
        $gmtime = time();
        if ($goods['is_promote'] == 1 && $goods['promote_start_date'] < $gmtime && $goods['promote_end_date'] > $gmtime){
            if ($goods['promote_price'] > 0 ){
                $all_price[] = $goods['promote_price'];
            }
        }
        $arr['min_price']    = min($all_price);
        $arr['u_price']       = $u_price;
        $arr['vol_price']     = $vol_price;
        $arr['promote_price'] = $goods['promote_price'];
        //计算剩下阶梯价格
        foreach ($volume_price as $key=>$row){
            if ($row['volume_price'] > $arr['min_price']){
                unset($volume_price[$key]);
            }
        }
        $arr['volume_price']  = $volume_price;
        return $arr;
    }
    /*------------------------------------------------------ */
    //-- 订单商品库存&销量处理
    /*------------------------------------------------------ */
    public function evalGoodsStore(&$goodsList = array(),$type = 'addOrder'){
        $GoodsSkuModel = new GoodsSkuModel();
        foreach($goodsList as $grow){
            $goods = $this->info($grow['goods_id']);

            if ($goods['is_spec'] == 1){//多规格商品执行
                if ($type == 'cancel'){
                    $sub_data['goods_number'] = ['INC',$grow['goods_number']];
                    $data['sale_num'] = ['DEC',$grow['goods_number']];
                }else{
                    $sub_data['goods_number'] = ['DEC',$grow['goods_number']];
                    $data['sale_num'] = ['INC',$grow['goods_number']];
                }
                $sub_map['goods_id'] = $grow['goods_id'];
                $sub_map['sku_val'] = $grow['sku_val'];
                $res = $GoodsSkuModel->where($sub_map)->update($sub_data);
                if ($res < 1) return false;
                $res = $this->where('goods_id',$grow['goods_id'])->update($data);
                if ($res < 1) return false;
                $this->cleanMemcache($grow['goods_id']);
            }else{
                if ($type == 'cancel'){
                    $data['goods_number'] = ['INC',$grow['goods_number']];
                    $data['sale_num'] = ['DEC',$grow['goods_number']];
                }else{
                    $data['goods_number'] = ['DEC',$grow['goods_number']];
                    $data['sale_num'] = ['INC',$grow['goods_number']];
                }
                $res = $this->where('goods_id',$grow['goods_id'])->update($data);
                if ($res < 1) return false;
            }
            $this->cleanMemcache($grow['goods_id']);

        }
        return true;
    }
	/*------------------------------------------------------ */
    //-- 获取搜索记录
    /*------------------------------------------------------ */
    public function searchKeys($keyword = ''){
        $keys = session('searchKeys');
        if (empty($keys)) $keys = array();
        if (empty($keyword) == false){
            foreach ($keys as $key=>$val){
                if ($val == $keyword){
                  unset($keys[$key]);
                }
            }
            if (count($keys) >=10){
                unset($keys[0]);
            }
            $keys[] = $keyword;
            session('searchKeys',$keys);
        }
        return array_reverse($keys);
    }
}
