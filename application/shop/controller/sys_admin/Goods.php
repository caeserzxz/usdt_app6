<?php

namespace app\shop\controller\sys_admin;

use think\Db;

use app\AdminController;
use app\shop\model\GoodsModel;
use app\shop\model\GoodsSkuModel;
use app\shop\model\GoodsImgsModel;
use app\shop\model\GoodsVolumePriceModel;
use app\shop\model\GoodsPricesModel;
use app\shop\model\GoodsLogModel;
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
    public $ext_status = 0;//额外默认指定商品状态
    public $is_supplyer = 0;//是否后台供应商管理
    public $_field = '';
    public $_pagesize = 0;
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new GoodsModel();
        $this->assign('is_supplyer', $this->is_supplyer);//
    }

    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->Model->autoSale();//自动上下架处理
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false, $is_delete = 0)
    {
        $this->assign('listType',$this->action);
        $runJson = input('runJson', 0, 'intval');
        if ($this->store_id > 0) {
            $where[] = ['store_id', '=', $this->store_id];
        } elseif ($this->supplyer_id > 0) {
            $where[] = ['supplyer_id', '=', $this->supplyer_id];
        } elseif ($this->is_supplyer == true) {
            $where[] = ['supplyer_id', '>', 0];
        } else {
            $where[] = ['store_id', '=', 0];
            $where[] = ['supplyer_id', '=', 0];
        }
        $where[] = ['is_delete', '=', $is_delete];
        if (empty($this->ext_status) == false) {
            $search['status'] = explode(',', $this->ext_status);
        } else {
            $search['status'] = input('status', -1, 'intval');
        }

        if ($search['status'] == 1) {
            $where[] = ['isputaway', '=', 1];
        } elseif ($search['status'] == 2) {
            $where[] = ['isputaway', '=', 0];
        } elseif ($search['status'] != -1) {
            $where[] = ['isputaway', 'in', $search['status']];

        }

        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) {
            $where['and'][] = "( goods_name like '%" . $search['keyword'] . "%')  OR ( goods_sn like '%" . $search['keyword'] . "%')";
        }

        $this->classList = $this->Model->getClassList();
        $search['cid'] = input('cat_id', 0, 'intval');
        if ($search['cid'] > 0) {
            $where[] = ['cid', 'in', $this->classList[$search['cid']]['children']];
        }
        $search['brand_id'] = input('brand_id', 0, 'intval');
        if ($search['brand_id'] > 0) {
            $where[] = ['brand_id', '=', $search['brand_id']];
        }
        $search['is_promote'] = input('is_promote', -1, 'intval');
        if ($search['is_promote'] >= 0) {
            $where[] = ['is_promote', '=', $search['is_promote']];
        }

        $data = $this->getPageList($this->Model, $where, $this->_field, $this->_pagesize);
        $this->assign("is_delete", $is_delete);
        $this->assign("data", $data);
        $this->assign("search", $search);
        $this->assign("classListOpt", arrToSel($this->classList, $search['cid']));
        $BrandList = $this->Model->getBrandList();
        $this->assign("brandListOpt", arrToSel($BrandList));

        $this->assign('tagList',(new \app\shop\model\GoodsTagModel)->getAll());//获取商品标签

        if ($runJson == 1) {
            return $this->success('', '', $data);
        } elseif ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data)
    {

        $ModelList = $this->Model->getModelList();
        $this->assign('modelListOpt', arrToSel($ModelList, $data['type_model']));//商品模型对应属性调用
        $ClassList = $this->Model->getClassList();
        $this->assign("classListOpt", arrToSel($ClassList, $data['cid']));//分类
        $BrandList = $this->Model->getBrandList();
        $this->assign("brandListOpt", arrToSel($BrandList, $data['brand_id']));//品牌
        $this->assign("skuModelOpt", arrToSel($ModelList, $data['sku_model'], $data['sku_model']));//商品模型对应sku调用
        $UsersLevelModel = new UsersLevelModel();
        $this->assign("UsersLevel", $UsersLevelModel->getRows());//会员等级
        $DividendRoleModel = new DividendRoleModel();
        $this->assign("UsersRole", $DividendRoleModel->getRows());//分销身份
        $ShippingTplModel = new ShippingTplModel();
        $this->assign("ShippingTpl", $ShippingTplModel->getRows());//运费模板

        $products = $specifications = array();
        if ($data['goods_id'] > 0) {
            if ($this->supplyer_id > 0) {
                if ($data['supplyer_id'] != $this->supplyer_id) {
                    return $this->error('你无权操作此商品..');
                }
            }
            $imgWhere[] = ['goods_id', '=', $data['goods_id']];

            if ($data['is_spec'] == 1) {//多规格
                $GoodsSkuModel = new GoodsSkuModel();
                $gsrows = $GoodsSkuModel->where('goods_id', $data['goods_id'])->select()->toArray();
                foreach ($gsrows as $arr) {
                    $sku = $arr['sku'];
                    $skuval[] = $arr['sku_val'];
                    $gsarr['sku_id'] = $arr['sku_id'];
                    $gsarr['ProductSn'] = $arr['goods_sn'];
                    $gsarr['Price'] = $arr['shop_price'];
                    $gsarr['PromotePrice'] = $arr['promote_price'];
                    $gsarr['SettlePrice'] = $arr['settle_price'];
                    $gsarr['MarketPrice'] = $arr['market_price'];
                    $gsarr['Store'] = $arr['goods_number'];
                    $gsarr['Weight'] = $arr['goods_weight'];
                    $gsarr['ProductCode'] = $arr['bar_code'];
                    $products[$arr['sku_val']] = $gsarr;
                }
                $sku = explode(':', $sku);
                foreach ($skuval as $arr) {
                    $arr = explode(':', $arr);
                    foreach ($arr as $key => $v) {
                        if (empty($specifications[$sku[$key]]) || in_array($v, $specifications[$sku[$key]]) == false) {
                            $specifications[$sku[$key]][] = $v * 1;
                        }
                    }
                }
            }
            $goodsPrices = $this->Model->getPrices($data['goods_id']);
            $this->assign('levelPrice', empty($goodsPrices['level']) ? [] : $goodsPrices['level']);
            $this->assign('rolePrice', empty($goodsPrices['role']) ? [] : $goodsPrices['role']);
            $this->assign('VolumePriceList', $this->Model->getVolumePrice($data['goods_id']));
            $this->assign('limit_user_level', explode(',', $data['limit_user_level']));
            $this->assign('limit_user_role', explode(',', $data['limit_user_role']));
            $goodsLog = (new GoodsLogModel)->where('goods_id', $data['goods_id'])->order('log_id DESC')->select()->toArray();

            $this->assign('tagList',(new \app\shop\model\GoodsTagModel)->getAll());//获取商品标签

            $this->assign("goodsLog", $goodsLog);
        } else {
            $imgWhere[] = ['goods_id', '=', 0];
            if ($this->supplyer_id > 0) {
                $imgWhere[] = ['supplyer_id', '=', $this->supplyer_id];
            } elseif ($this->store_id > 0) {
                $imgWhere[] = ['store_id', '=', $this->store_id];
            } else {
                $imgWhere[] = ['admin_id', '=', AUID];
            }

        }
        $sku_imgs = $this->Model->getImgsListAdmin($imgWhere, true);
        foreach ($sku_imgs as $arr) {
            $products[$arr['sku_val']]['ProductImgId'] = $arr['img_id'];
            $products[$arr['sku_val']]['ProductImg'] = $arr['goods_img'];
        }

        $this->assign('goods_imgs', $this->Model->getImgsListAdmin($imgWhere));
        $this->assign('specifications', json_encode($specifications));
        $this->assign('products', json_encode($products));
        $this->assign('goods_status', config('config.goods_status'));
        //自动上下架判断重置
        if ($data['shelf_time'] > time()){
            $data['isputaway'] = 2;
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 获取商品属性
    /*------------------------------------------------------ */
    public function getAttriBute($goods = array())
    {
        if (empty($goods)) {
            $model_id = input('model_id', 0, 'intval');
            if ($model_id < 0) return $this->error('商品模型传参失败.');
            $goods_id = input('goods_id', 0, 'intval');
        } else {
            $model_id = $goods['type_model'];
            $goods_id = $goods['goods_id'];
        }
        if ($goods_id > 0) {
            $goodsAttr = $this->Model->getAttributeVal($goods_id);
            $this->assign('goodsAttr', $goodsAttr);

        }
        $model_list = $this->Model->getModelList();
        $this->assign('model', $model_list[$model_id]);
        if (empty($goods) == false) return false;
        $data['content'] = $this->fetch('attribute')->getContent();
        return $this->success('', '', $data);
    }

    /*------------------------------------------------------ */
    //-- 验证商品数据
    /*------------------------------------------------------ */
    private function checkData($row)
    {

        if (empty($row['goods_name'])) {
            return $this->error('请输入商品名称.');
        }
        if ($row['cid'] < 1) {
            return $this->error('请选择商品分类.');
        }



        $GoodsImgsModel = new GoodsImgsModel();
        if ($row['goods_id'] < 1) {
            $imgwhere[] = ['goods_id', '=', 0];
            $imgwhere[] = ['sku_val', '=', ''];
            if ($this->supplyer_id > 0) {
                $imgwhere[] = ['supplyer_id', '=', $this->supplyer_id];
            } elseif ($this->store_id > 0) {
                $imgwhere[] = ['store_id', '=', $this->store_id];
            } else {
                $imgwhere[] = ['admin_id', '=', AUID];
            }

            $imgCount = $GoodsImgsModel->where($imgwhere)->count('img_id');
            if ($imgCount < 1) {
                return $this->error('请上传商品图片.');
            }
            unset($imgwhere);
        }
        $row['goods_desc'] = input('goods_desc', '', 'trim,stripslashes');
        $row['m_goods_desc'] = input('m_goods_desc', '', 'trim,stripslashes');
        if (empty($row['goods_desc']) && empty($row['m_goods_desc'])) {
            return $this->error('未上传商品详情，请上传后再保存.');
        }

        $row['is_promote'] = empty($row['is_promote']) ? 0 : 1;

        if ($row['is_spec'] == 0) {//单规格
            if ($this->is_supplyer == false) {//平台管理供应商时，不判断以下
                if (empty($row['goods_sn'])) {
                    return $this->error('请输入商品货号.');
                }
                $where[] = ['goods_sn', '=', $row['goods_sn']];
                $where[] = ['store_id', '=', $this->store_id];
                if ($row['goods_id'] > 0) {
                    $where[] = ['goods_id', '<>', $row['goods_id']];
                }
                $count = $this->Model->where($where)->count('goods_id');
                if ($count > 0) return $this->error('操作失败:已存在相同的货号，不允许重复添加.');
                unset($where);
                if ($row['bar_code']) {
                    $where[] = ['store_id', '=', $this->store_id];
                    $where[] = ['bar_code', '=', $row['bar_code']];
                    if ($row['goods_id'] > 0) {
                        $where[] = ['goods_id', '<>', $row['goods_id']];
                    }
                    $count = $this->Model->where($where)->count('goods_id');
                    if ($count > 0) return $this->error('操作失败:已存在相同的货号条形码，不允许重复添加.');
                    unset($where);
                }
                if ($row['market_price'] > 0) {
                    if ($row['market_price'] < $row['shop_price']) return $this->error('操作失败:市场价不能少于销售价.');
                } else {
                    $row['market_price'] = $row['shop_price'];//不填写市场价，默认与售价一致
                }
            }

            if ($row['shop_price'] < 0) {
                return $this->error('商品销售价不能少于0.');
            }
            if ($row['goods_number'] < 0) {
                return $this->error('商品库存不能少于0.');
            }


            if ($row['is_promote'] == 1) {
                if ($row['promote_price'] < 0) {
                    return $this->error('操作失败:促销价必须大于0.');
                }
            }
        } else {//多规格处理
            $Products = input('post.Products');
            $goods_sn = [];
            $prices = $market_price = [];
            $PromotePrice = 0;
            $goods_number = 0;
            foreach ($Products as $prow) {
                if (in_array($prow['ProductSn'], $goods_sn)) {
                    return $this->error('子商品列表中货号【' . $prow['ProductSn'] . '】重复，系统不允许货号重复.');
                }
                $goods_sn[] = $prow['ProductSn'];
                if (empty($prow['Store']) == false && $prow['Store'] != 0) {
                    $goods_number += $prow['Store'];
                }
                if ($this->supplyer_id > 0) {
                    $prices[] = $prow['SettlePrice'];
                } else {
                    $prices[] = $prow['Price'];
                }
                $market_price[] = $prow['MarketPrice'];
                //促销处理
                if ($row['is_promote'] == 1) {
                    if ($prow['PromotePrice'] < 0) {
                        return $this->error('操作失败:促销价不能小于0.');
                    }
                    $PromotePrice += $prow['PromotePrice'];
                }
            }
            $row['goods_number'] =  ['INC', $goods_number] ;
            if (empty($goods_sn)) return $this->error('未知错误，未能获取子商品数据.');
            if ($row['is_promote'] == 1 && $PromotePrice <= 0) {
                return $this->error('操作失败，开启促销至少有一个sku促销价大于0.');
            }

            if ($this->supplyer_id > 0) {
                $row['settle_price'] = min($prices);
                $row['settle_min_price'] = $row['settle_price'];
                $row['settle_max_price'] = max($prices);
            } else {
                $row['shop_price'] = min($prices);
                $row['min_price'] = $row['shop_price'];
                $row['max_price'] = max($prices);
            }

            $row['market_price'] = max($market_price);
            $GoodsSkuModel = new GoodsSkuModel();
            $where[] = ['store_id', '=', $this->store_id];
            $where[] = ['goods_sn', 'in', $goods_sn];
            if ($row['goods_id'] > 0) {
                $where[] = ['goods_id', '<>', $row['goods_id']];
            }
            $goodsSn = $GoodsSkuModel->where($where)->field('GROUP_CONCAT(goods_sn) as gsn')->find();
            if (empty($goodsSn['gsn']) == false) return $this->error('子商品货号【' . $goodsSn['gsn'] . '】与其它子商品货号重复，货号不允许重复.');
            unset($where);
        }

        if ($this->supplyer_id > 0) {
            if ($row['goods_id'] < 1){
                $row['is_alone_sale'] = 1;
            }
            $row['supplyer_id'] = $this->supplyer_id;//供应商ID
        } elseif ($this->store_id > 0) {
            $row['store_id'] = $this->store_id;//门店ID
        } else {
            $row['is_alone_sale'] = isset($row['is_alone_sale']) ? 1 : 0;

            //自动上下架处理
            if ($row['isputaway'] == 2) {
                if (empty($row['added_time']) || empty($row['shelf_time'])) return $this->error('操作失败:请选择上下架的时间.');
                if (!checkDateIsValid($row['added_time'])) return $this->error('操作失败:上下架的开始时间格式不合法.');
                if (!checkDateIsValid($row['shelf_time'])) return $this->error('操作失败:上下架的结束时间格式不合法.');
                $row['added_time'] = strtotime($row['added_time']);
                $row['shelf_time'] = strtotime($row['shelf_time']);
                if ($row['added_time'] >= $row['shelf_time']) return $this->error('操作失败:下架时间必须大于上架时间.');
            }else{
                $row['added_time'] = 0;
                $row['shelf_time'] = 0;
            }
            //促销处理
            if ($row['is_promote'] == 1) {
                if (empty($row['promote_start_date']) || empty($row['promote_end_date'])) return $this->error('操作失败:请选择促销的时间.');
                if (!checkDateIsValid($row['promote_start_date'])) return $this->error('操作失败:促销开始时间格式不合法.');
                if (!checkDateIsValid($row['promote_end_date'])) return $this->error('操作失败:促销结束时间格式不合法.');
                $row['promote_start_date'] = strtotime($row['promote_start_date']);
                $row['promote_end_date'] = strtotime($row['promote_end_date']);
                if ($row['promote_start_date'] >= $row['promote_end_date']) return $this->error('操作失败::促销开始时间必须大于促销结束时间.');
            } else {
                $row['is_promote'] = 0;
                unset($row['promote_start_date'], $row['promote_end_date']);
            }
            //限购处理
            $is_quota = input('post.is_quota', 0, 'intval');
            if ($is_quota == 0) $row['limit_num'] = 0;

            //运费模板
            $undertake = input('undertake', '0', 'intval');
            if ($undertake == 0) {
                $row['freight_template'] = 0;
            }

            //会员购买限制
            $row['limit_user_level'] = empty($row['limit_user_level']) ? '' : join(',', $row['limit_user_level']);
            //身份购买限制
            $row['limit_user_role'] = empty($row['limit_user_role']) ? '' : join(',', $row['limit_user_role']);
            $row['shop_price'] = $row['shop_price'] * 1;
        }
        //获取图片主图
        $GoodsImages = input('post.GoodsImages');
        if ($GoodsImages) {
            $row['goods_img'] = $GoodsImages['path'][0];
            $row['goods_thumb'] = str_replace('.', '_thumb.', $row['goods_img']);
        }

        //赠送积分处理
        $is_give_integral = input('is_give_integral',0,'intval');
        if ($is_give_integral <= 0){
            $row['give_integral'] = $is_give_integral;
        }
        return $row;
    }

    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($row)
    {
        $row = $this->checkData($row);
        Db::startTrans();//启动事务
        return $row;
    }
    /*------------------------------------------------------ */
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($row)
    {
        $GoodsImages = input('post.GoodsImages');
        $GoodsImgsModel = new GoodsImgsModel();
        foreach ($GoodsImages['id'] as $key => $img_id) {
            $imgwhere = array();
            $imgwhere[] = ['img_id', '=', $img_id];
            $imgwhere[] = ['goods_id', '=', 0];
            if ($this->store_id > 0) {
                $imgwhere[] = ['store_id', '=', $this->store_id];
            } elseif ($this->supplyer_id > 0) {
                $imgwhere[] = ['supplyer_id', '=', $this->supplyer_id];
            } else {
                $imgwhere[] = ['admin_id', '=', AUID];
            }
            $upData['sort_order'] = $key;
            $upData['goods_id'] = $row['goods_id'];
            $GoodsImgsModel->where($imgwhere)->update($upData);
        }
        unset($imgwhere);

        //规格
        if ($row['is_spec'] == 1) {
            $sku_model = input('sku_model', 0, 'intval');
            $GoodsSkuModel = new GoodsSkuModel();
            $specifications = input('post.specifications');
            $sku = join(':', array_values($specifications['id']));
            $Products = input('post.Products');
            foreach ($Products as $prow) {
                $_arr = $prow['Price'] . $prow['ProductSn'] . $prow['Store'];
                if (empty($_arr)) continue;//空值跳过,不执行生成sku
                $inData['sku'] = $sku;
                $inData['sku_name'] = join(',', $prow['SpecVal']['val']);
                $inData['supplyer_id'] = $this->supplyer_id * 1;
                $inData['store_id'] = $this->store_id * 1;
                $inData['goods_id'] = $row['goods_id'];
                $inData['market_price'] = $prow['MarketPrice'];
                $inData['sku_model'] = $sku_model;
                if ($this->supplyer_id > 0) {//供应商操作此字段
                    $inData['settle_price'] = $prow['SettlePrice'];
                } else {
                    $inData['shop_price'] = $prow['Price'];
                    $inData['promote_price'] = $prow['PromotePrice'];
                }
                $inData['goods_sn'] = $prow['ProductSn'];
                $inData['bar_code'] = $prow['ProductCode'];
                $inData['goods_number'] = $prow['Store'];
                $inData['goods_weight'] = $prow['Weight'];
                $inData['add_time'] = $inData['update_time'] = time();
                $sku_val = array_values($prow['SpecVal']['key']);
                $inData['sku_val'] = join(':', $sku_val);
                if (empty($inData['sku_val'])) {
                    return $this->error('操作失败:获取商品SKU值失败，请重试.');
                }
                $res = $GoodsSkuModel::create($inData);
                if ($res->sku_id < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:子商品写入失败，请重试.');
                }
                unset($upData);
                $upData['store_id'] = $this->store_id;
                $upData['goods_id'] = $row['goods_id'];
                $imgwhere = array();
                $imgwhere[] = ['goods_id', '=', 0];
                $imgwhere[] = ['sku_val', '=', $inData['sku_val']];
                $imgwhere[] = ['store_id', '=', $this->store_id];
                $imgwhere[] = ['admin_id', '=', AUID];
                $GoodsImgsModel->where($imgwhere)->update($upData);
            }
        }
        //处理优惠价格阶梯
        $volume_number = input('post.volume_number');
        if (empty($volume_number) == false) {
            $volume_price = input('post.volume_price');
            $GoodsVolumePriceModel = new GoodsVolumePriceModel();
            foreach ($volume_number as $key => $pval) {
                if ($pval < 1 || $volume_price[$key] <= 0) continue;
                unset($inData);
                $inData['goods_id'] = $row['goods_id'];
                $inData['number'] = $pval;
                $inData['price'] = $volume_price[$key];
                $res = $GoodsVolumePriceModel::create($inData);
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:写入阶梯价格失败，请重试.');
                }
            }
        }
        //处理商品属性
        $attr_value_list = input('post.attr_value_list');
        if (empty($attr_value_list) == false) {
            $AttributeValModel = new AttributeValModel();
            foreach ($attr_value_list as $key => $attrVal) {
                unset($inarr);
                $attrVal = is_array($attrVal) ? trim(join(',', $attrVal), ',') : $attrVal;
                $inData['goods_id'] = $row['goods_id'];
                $inData['model_id'] = $row['type_model'];
                $inData['attr_id'] = $key;
                $inData['attr_value'] = $attrVal;
                $res = $AttributeValModel::create($inData);
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:处理商品属性失败，请重试.');
                }
            }
        }
        $GoodsPricesModel = new GoodsPricesModel();
        //处理会员价格
        if ($row['level_price_type'] > 0) {
            $level_price = input('post.level_price');
            foreach ($level_price as $key => $price) {
                if ($price <= 0) continue;
                unset($inData);
                $inData['goods_id'] = $row['goods_id'];
                $inData['type'] = 'level';
                $inData['by_id'] = $key;
                $inData['price'] = $price * 1;
                $res = $GoodsPricesModel::create($inData);
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:处理会员价格失败，请重试.');
                }
            }
        }
        //处理身份价格
        if ($row['role_price_type'] > 0) {
            $role_price = input('post.role_price');
            foreach ($role_price as $key => $price) {
                if ($price <= 0) continue;
                unset($inData);
                $inData['goods_id'] = $row['goods_id'];
                $inData['type'] = 'role';
                $inData['by_id'] = $key;
                $inData['price'] = $price * 1;
                $res = $GoodsPricesModel::create($inData);
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:处理身份价格失败，请重试.');
                }
            }
        }
        Db::commit();// 提交事务
        $this->Model->cleanMemcache($row['goods_id']);
        $goods_status = config('config.goods_status');
        $row['isputaway'] = $row['isputaway'] * 1;
        if ($this->supplyer_id > 0) {
            $this->Model->_log($row['goods_id'], '添加商品：' . $row['goods_name'], $goods_status[$row['isputaway']], 'supplyer', $this->supplyer_id);
        } else {
            $this->Model->_log($row['goods_id'], '添加商品：' . $row['goods_name'], $goods_status[$row['isputaway']], 'admin', AUID);
        }
        return $this->success('添加成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前处理
    /*------------------------------------------------------ */
    public function beforeEdit($row)
    {
        //供应商商品时调用
        if ($this->supplyer_id > 0) {
            $goods = $this->Model->where('goods_id', $row['goods_id'])->field('supplyer_id,isputaway')->find();
            if ($goods['supplyer_id'] != $this->supplyer_id) {
                return $this->error('你无权操作此商品.');
            }
            if (in_array($goods['isputaway'], [0, 11, 12, 13]) == false) {
                return $this->error('当前商品状态不允许修改.');
            }
        }
        $row = $this->checkData($row);
        if ($row['is_spec'] == 0) {
            $goods_number = $row['goods_number'];
            unset($row['goods_number']);
            if ($goods_number > 0) {
                $row['goods_number'] = ['INC', $goods_number];
            }
            $row['shop_price'] = $row['shop_price'] * 1;
        }

        $row['is_best'] = empty($row['is_best']) ? 0 : 1;
        $row['is_hot'] = empty($row['is_hot']) ? 0 : 1;
        $row['is_new'] = empty($row['is_new']) ? 0 : 1;
        if (empty($row['sale_num']) == false){
            $row['virtual_sale'] += $row['sale_num'];
        }
        if (empty($row['collect_num']) == false){
            $row['virtual_collect'] += $row['collect_num'];
        }
        $opt = input('opt', 0, 'intval');
        $this->loginfo = '修改商品：' . $row['goods_name'];
        if ($opt > 0) {
            $opt_remark = input('opt_remark', '', 'trim');
            $row['isputaway'] = $opt;
            if ($opt == 1) {
                unset($row['market_price']);//审核不更新此值
                //上架时判断售价是否大于供货价
                if ($row['is_spec'] == 1) {
                    $Products = input('post.Products');
                    foreach ($Products as $prow) {
                        if ($prow['Price'] < $prow['SettlePrice']) {
                            return $this->error('货号：' . $prow['ProductSn'] . '，销售价小于供货价，请核实.');
                        }
                    }
                    unset($Products);
                } else {
                    if ($row['shop_price'] < $row['settle_price']) {
                        return $this->error('销售价不能小于供货价，请核实.');
                    }
                    unset($row['settle_price']);
                }
                $this->loginfo .= "允许上架，备注：" . $opt_remark;
            } elseif ($opt == 11) {
                $this->loginfo .= "拒绝上架，备注：" . $opt_remark;
            } elseif ($opt == 12) {
                if (empty($opt_remark)) {
                    return $this->error('操作下架，须填写备注.');
                }
                $this->loginfo .= "平台下架商品，备注：" . $opt_remark;
            }
            unset($row['settle_price']);
        }
        Db::startTrans();//启动事务
        return $row;
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($row)
    {
        $GoodsImages = input('post.GoodsImages');
        $GoodsImgsModel = new GoodsImgsModel();
        foreach ($GoodsImages['id'] as $key => $img_id) {
            $imgwhere = array();
            $imgwhere[] = ['img_id', '=', $img_id];
            $imgwhere[] = ['goods_id', '=', $row['goods_id']];
            $GoodsImgsModel->where($imgwhere)->update(['sort_order'=>$key]);
        }
        unset($imgwhere);
        if ($row['is_spec'] == 1) {
            $GoodsSkuModel = new GoodsSkuModel();
            //查询不同模型的sku,如果查询有删除
            $skudelwhere[] = ['goods_id', '=', $row['goods_id']];
            $skudelwhere[] = ['sku_model', '<>', $row['sku_model']];
            $sku_count = $GoodsSkuModel->where($skudelwhere)->count('sku_id');
            if ($sku_count > 0) {
                $res = $GoodsSkuModel->where($skudelwhere)->delete();
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:删除旧规格商品失败，请重试.');
                }
            }//end
            $specifications = input('post.specifications');
            $sku = join(':', array_values($specifications['id']));
            $Products = input('post.Products');
            $time = time();
            foreach ($Products as $prow) {
                $upData = [];
                if ($this->is_supplyer == true) {//平台管理供应商处理
                    $upData['shop_price'] = $prow['Price'];
                    $upData['promote_price'] = $prow['PromotePrice'];
                    $upData['update_time'] = $time;
                    $res = $GoodsSkuModel->where('sku_id', $prow['SkuId'] * 1)->update($upData);
                } else {
                    $_arr = $prow['Price'] . $prow['ProductSn'] . $prow['Store'];
                    if (empty($_arr)) continue;//空值跳过,不执行生成sku
                    $upData['sku'] = $sku;
                    $upData['sku_name'] = join(',', $prow['SpecVal']['val']);
                    $upData['market_price'] = $prow['MarketPrice'];
                    if ($this->supplyer_id > 0) {//供应商操作此字段
                        $upData['settle_price'] = $prow['SettlePrice'];
                    } else {
                        $upData['shop_price'] = $prow['Price'];
                        $upData['promote_price'] = $prow['PromotePrice'];
                    }
                    $upData['goods_sn'] = $prow['ProductSn'];
                    $upData['bar_code'] = $prow['ProductCode'];
                    $upData['goods_weight'] = $prow['Weight'];
                    $sku_val = array_values($prow['SpecVal']['key']);
                    $upData['sku_val'] = join(':', $sku_val);
                    if (empty($upData['sku_val'])) {
                        return $this->error('操作失败:获取商品SKU值失败，请重试.');
                    }
                    $upData['update_time'] = $time;

                    if ($prow['SkuId'] > 0) {
                        if ($prow['Store'] != 0) {
                            $upData['goods_number'] = ['INC', $prow['Store'] * 1];
                        }
                        $res = $GoodsSkuModel->where('sku_id', $prow['SkuId'])->update($upData);
                    } else {
                        $upData['supplyer_id'] = $this->supplyer_id * 1;
                        $upData['store_id'] = $this->store_id * 1;
                        $upData['goods_id'] = $row['goods_id'];
                        $upData['sku_model'] = $row['sku_model'];
                        $upData['add_time'] = $time;
                        $upData['update_time'] = $time;
                        $upData['goods_number'] = $prow['Store'];
                        $res = $GoodsSkuModel->create($upData);
                        $res = $res->sku_id;
                    }
                }
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作商品规格失败，请重试.');
                }
            }
            unset($skudelwhere);
            $skudelwhere[] = ['goods_id', '=', $row['goods_id']];
            $skudelwhere[] = ['update_time', '<', $time];
            $GoodsSkuModel->where($skudelwhere)->delete();//删除已取消的sku
        }
        //处理优惠价格阶梯
        $volume_number = input('post.volume_number');
        $volume_price = input('post.volume_price');
        $GoodsVolumePriceModel = new GoodsVolumePriceModel();
        $GoodsVolumePriceModel->where('goods_id', $row['goods_id'])->delete();
        foreach ($volume_number as $key => $pval) {
            if ($pval < 1) continue;
            $inData = array();
            $inData['goods_id'] = $row['goods_id'];
            $inData['number'] = $pval;
            $inData['price'] = $volume_price[$key];
            $res = $GoodsVolumePriceModel::create($inData);
            if ($res < 1) {
                Db::rollback();// 回滚事务
                return $this->error('操作失败:写入阶梯价格失败，请重试.');
            }
        }
        //处理商品属性
        $attr_value_list = input('post.attr_value_list');
        $AttributeValModel = new AttributeValModel();
        $AttributeValModel->where('goods_id', $row['goods_id'])->delete();
        if (empty($attr_value_list) == false){
            foreach ($attr_value_list as $key => $attrVal) {
                $inData = array();
                $inData['goods_id'] = $row['goods_id'];
                $inData['model_id'] = $row['type_model'];
                $inData['attr_id'] = $key;
                $inData['attr_value'] = is_array($attrVal) ? trim(join(',', $attrVal), ',') : $attrVal;
                $res = $AttributeValModel::create($inData);
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:写入商品属性失败，请重试.');
                }
            }
        }

        //处理会员价格
        $GoodsPricesModel = new GoodsPricesModel();
        $GoodsPricesModel->where('goods_id', $row['goods_id'])->delete();
        if ($row['level_price_type'] > 0) {
            $level_price = input('post.level_price');
            foreach ($level_price as $key => $price) {
                $inData = array();
                $inData['goods_id'] = $row['goods_id'];
                $inData['type'] = 'level';
                $inData['by_id'] = $key;
                $inData['price'] = $price * 1;
                $res = $GoodsPricesModel::create($inData);
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:处理会员价格失败，请重试.');
                }
            }
        }
        //处理身份价格
        if ($row['role_price_type'] > 0) {
            $role_price = input('post.role_price');
            foreach ($role_price as $key => $price) {
                if ($price <= 0) continue;
                unset($inData);
                $inData['goods_id'] = $row['goods_id'];
                $inData['type'] = 'role';
                $inData['by_id'] = $key;
                $inData['price'] = $price * 1;
                $res = $GoodsPricesModel::create($inData);
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:处理身份价格失败，请重试.');
                }
            }
        }
        Db::commit();// 提交事务
        $goods_status = config('config.goods_status');
        $info = $this->Model->info($row['goods_id']);

        if ($this->supplyer_id > 0) {
            $this->Model->_log($row['goods_id'], $this->loginfo, $goods_status[$info['isputaway']], 'supplyer', $this->supplyer_id);
        } else {
            $this->Model->_log($row['goods_id'], $this->loginfo, $goods_status[$info['isputaway']], 'admin', AUID);
        }
        return $this->success('修改成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 快捷修改
    /*------------------------------------------------------ */
    public function afterAjax($goods_id, $data)
    {
        $log = '快速修改商品';
        $this->Model->cleanMemcache($goods_id);
        $info = $this->Model->info($goods_id);
        $goods_status = config('config.goods_status');
        $this->Model->_log($goods_id, $log, $goods_status[$info['isputaway']], 'admin', AUID);
        return $this->success('修改成功');
    }
    /*------------------------------------------------------ */
    //-- 删除商品
    /*------------------------------------------------------ */
    public function del()
    {
        $goods_id = input('goods_id', 0, 'intval');
        if ($goods_id < 1) return $this->error('传递参数失败！');
        $data['is_delete'] = 1;
        $res = $this->Model->where('goods_id', $goods_id)->update($data);
        if ($res < 1) return $this->error();
        $this->Model->cleanMemcache($goods_id);
        $info = $this->Model->info($goods_id);
        $goods_status = config('config.goods_status');
        $this->Model->_log($goods_id, '删除商品放入回收站', $goods_status[$info['isputaway']], 'admin', AUID);
        return $this->success('操作成功.');
    }
    /*------------------------------------------------------ */
    //-- 商品回收站
    /*------------------------------------------------------ */
    public function trash()
    {
        $this->trashList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 商品回收站查询
    /*------------------------------------------------------ */
    public function trashList($runData = false)
    {
        return $this->getList($runData, 1);
    }
    /*------------------------------------------------------ */
    //-- 还原商品
    /*------------------------------------------------------ */
    public function revert()
    {
        $goods_id = input('goods_id', 0, 'intval');
        if ($goods_id < 1) return $this->error('传递参数失败！');
        $data['is_delete'] = 0;
        $res = $this->Model->where('goods_id', $goods_id)->update($data);
        if ($res < 1) return $this->error();
        $this->Model->cleanMemcache($goods_id);
        $info = $this->Model->info($goods_id);
        $goods_status = config('config.goods_status');
        $this->Model->_log($goods_id, '还原商品', $goods_status[$info['isputaway']], 'admin', AUID);
        return $this->success('操作成功.');
    }
    /*------------------------------------------------------ */
    //-- 搜索商品
    /*------------------------------------------------------ */
    public function searchBox()
    {
        $this->_pagesize = 10;
        $this->_field = 'goods_id,goods_sn,goods_name,is_spec,shop_price';
        $this->getList(true);
        $result['data'] = $this->data;
        if ($this->request->isPost()) return $this->ajaxReturn($result);
        $this->assign("classListOpt", arrToSel($this->classList, input('cid', 0, 'intval')));
        $this->assign("_menu_index", input('_menu_index', '', 'trim'));
        $this->assign("searchType", input('searchType', '', 'trim'));
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 根据关键字查询
    /*------------------------------------------------------ */
    public function pubSearch()
    {
        $where[] = ['is_delete', '=', 0];//过滤已删除-Lu
        $keyword = input('keyword', '', 'trim');
        if (!empty($keyword)) {
            $where = "( goods_name LIKE '%" . $keyword . "%' OR goods_sn LIKE '%" . $keyword . "%' )";
        }
        $search['cid'] = input('cid', 0, 'intval');
        if ($search['cid'] > 0) {
            $this->classList = $this->Model->getClassList();
            $where[] = ['cid', 'in', $this->classList[$search['cid']]['children']];
        }

        $_list = $this->Model->where($where)->field("goods_id,goods_name,shop_price,is_spec,goods_sn,goods_thumb")->limit(20)->select();
        foreach ($_list as $key => $row) {
            $_list[$key] = $row;
        }
        $result['list'] = $_list;
        $result['code'] = 1;
        return $this->ajaxReturn($result);
    }

    /*------------------------------------------------------ */
    //-- 弹窗选择商品
    /*------------------------------------------------------ */
    public function selectGoods()
    {
        $this->getSelectGoodsList(true);
        return $this->fetch('select_goods');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getSelectGoodsList($runData = false)
    {
        $GoodsModel = new GoodsModel();
        $this->assign('listType', $this->action);
        $runJson = input('runJson', 0, 'intval');
        $goodsArr = input('goodsArr', 0, 'trim');
        if ($this->store_id > 0) {
            $where[] = ['store_id', '=', $this->store_id];
        } elseif ($this->supplyer_id > 0) {
            $where[] = ['supplyer_id', '=', $this->supplyer_id];
        } elseif ($this->is_supplyer == true) {
            $where[] = ['supplyer_id', '>', 0];
        } else {
            $where[] = ['store_id', '=', 0];
            $where[] = ['supplyer_id', '=', 0];
        }
        if (empty($this->ext_status) == false) {
            $search['status'] = explode(',', $this->ext_status);
        } else {
            $search['status'] = input('status', -1, 'intval');
        }

        if ($search['status'] == 1) {
            $where[] = ['isputaway', '=', 1];
        } elseif ($search['status'] == 2) {
            $where[] = ['isputaway', '=', 0];
        } elseif ($search['status'] != -1) {
            $where[] = ['isputaway', 'in', $search['status']];
        }

        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) {
            $where['and'][] = "( goods_name like '%" . $search['keyword'] . "%')  OR ( goods_sn like '%" . $search['keyword'] . "%')";
        }

        $this->classList = $GoodsModel->getClassList();
        $search['cid'] = input('cid', 0, 'intval');
        if ($search['cid'] > 0) {
            $where[] = ['cid', 'in', $this->classList[$search['cid']]['children']];
        }
        $search['brand_id'] = input('brand_id', 0, 'intval');
        if ($search['brand_id'] > 0) {
            $where[] = ['brand_id', '=', $search['brand_id']];
        }
        $search['is_promote'] = input('is_promote', -1, 'intval');
        if ($search['is_promote'] >= 0) {
            $where[] = ['is_promote', '=', $search['is_promote']];
        }
        $search['goodsArr'] = input('goodsArr', 0, 'trim');
        if (empty($search['goodsArr']) == false) {
            $where[] = ['goods_id', 'not in', $search['goodsArr']];
        }

        $this->data = $this->getPageList($GoodsModel, $where, $this->_field, $this->_pagesize);
        $this->assign("data", $this->data);
        $this->assign("search", $search);
        $this->assign("classListOpt", arrToSel($this->classList, $search['cid']));
        $BrandList = $GoodsModel->getBrandList();
        $this->assign("brandListOpt", arrToSel($BrandList));
        if ($runJson == 1) {
            return $this->success('', '', $this->data);
        } elseif ($runData == false) {
            $this->data['content'] = $this->fetch('select_goods_list')->getContent();
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }

}
