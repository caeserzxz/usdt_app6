<?php

namespace app\supplyer\controller;
use think\Db;
use app\supplyer\Controller;
use app\shop\controller\sys_admin\Goods as mainGoods;
use app\shop\model\GoodsSkuModel;

/**
 * 商品相关
 * Class Index
 * @package app\store\controller
 */
class Goods extends mainGoods
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        $this->initialize_isretrun = false;
        parent::initialize();
        $Controller = new Controller();
        $this->supplyer_id = $Controller->supplyer_id;
        $this->assign('goods_status', config('config.goods_status'));

        $ext_status = input('ext_status', '', 'trim');
        if (empty($ext_status) == false) {//如要有传参时调用，用于限制商品状态
            $this->ext_status = $ext_status;
        }
    }
    //*------------------------------------------------------ */
    //-- 全部
    /*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        $this->assign('ext_status', $this->ext_status);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 上架中
    /*------------------------------------------------------ */
    public function sale()
    {
        $this->ext_status = 1;
        $this->getList(true);
        $this->assign('ext_status', $this->ext_status);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 审核相关列表
    /*------------------------------------------------------ */
    public function waitCheck()
    {
        $this->ext_status = 10;
        $this->getList(true);
        $this->assign('ext_status', $this->ext_status);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 审核失败
    /*------------------------------------------------------ */
    public function lost()
    {
        $this->ext_status = 11;
        $this->getList(true);
        $this->assign('ext_status', $this->ext_status);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 下架待销售
    /*------------------------------------------------------ */
    public function waitSale()
    {
        $this->ext_status = "0,13";
        $this->getList(true);
        $this->assign('ext_status', $this->ext_status);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 平台下架
    /*------------------------------------------------------ */
    public function platformByNo()
    {
        $this->ext_status = 12;
        $this->getList(true);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 提交商品上架审核
    /*------------------------------------------------------ */
    public function postCheck()
    {
        $goods_id = input('goods_id', 0, 'intval');
        if ($goods_id < 1) return $this->error('获取商品ID失败.');
        $goods = $this->Model->info($goods_id);
        if (empty($goods_id)) return $this->error('获取商品失败.');
        if ($goods['supplyer_id'] != $this->supplyer_id) {
            return $this->error('你无权操作此订单.');
        }
        $upData['isputaway'] = 10;
        $where['goods_id'] = $goods_id;
        $res = $this->Model->upInfo($upData, $where);
        if ($res < 1) return $this->error('处理失败.');
        $goods_status = config('config.goods_status');
        $this->Model->_log($goods_id, '提交上架审核：' . $goods['goods_name'], $goods_status[$goods['isputaway']], 'supplyer', $this->supplyer_id);
        return $this->success('提交商品上架审核成功.');
    }
    //*------------------------------------------------------ */
    //-- 下架商品
    /*------------------------------------------------------ */
    public function postUnder()
    {
        $goods_id = input('goods_id', 0, 'intval');
        if ($goods_id < 1) return $this->error('获取商品ID失败.');
        $goods = $this->Model->info($goods_id);
        if (empty($goods_id)) return $this->error('获取商品失败.');
        if ($goods['supplyer_id'] != $this->supplyer_id) {
            return $this->error('你无权操作此订单.');
        }
        $upData['isputaway'] = 13;
        $where[] = ['goods_id', '=', $goods_id];
        $res = $this->Model->upInfo($upData, $where);
        if ($res < 1) return $this->error('处理失败.');
        $goods_status = config('config.goods_status');
        $this->Model->_log($goods_id, '下架商品：' . $goods['goods_name'], $goods_status[$goods['isputaway']], 'supplyer', $this->supplyer_id);
        return $this->success('下架商品成功.');
    }
    //*------------------------------------------------------ */
    //-- 修改商品库存
    /*------------------------------------------------------ */
    public function editStore()
    {
        $goods_id = input('goods_id', 0, 'intval');
        if ($goods_id < 1) return $this->error('获取商品ID失败.');
        $goods = $this->Model->info($goods_id);
        if (empty($goods_id)) return $this->error('获取商品失败.');
        if ($goods['supplyer_id'] != $this->supplyer_id) {
            return $this->error('你无权操作此订单.');
        }
        $goods_status = config('config.goods_status');
        if ($goods['is_spec'] == 0) {
            $goods_number = input('goods_number', 0, 'intval');
            if ($goods_number < 1) {
                return $this->error('没有输入库存数量.');
            }
            $upData['goods_number'] = ['INC', $goods_number];
            $where[] = ['goods_id', '=', $goods_id];
            $res = $this->Model->upInfo($upData, $where);
            if ($res == false) {
                return $this->error('更新商品库存失败.');
            }
            $this->Model->_log($goods_id, '修改商品库存：' . $goods['goods_name'], $goods_status[$goods['isputaway']], 'supplyer', $this->supplyer_id);
            return $this->success('操作成功.');
        }
        $GoodsSkuModel = new GoodsSkuModel();
        $Products = input('post.Products');
        $time = time();
        Db::startTrans();//启动事务
        $isUp = false;//用于判断是否执行过更新
        foreach ($Products as $prow) {
            if ($prow['Store'] < 1) {
                continue;
            }
            $upData['goods_number'] = ['INC', $prow['Store'] * 1];
            $upData['update_time'] = $time;
            $res = $GoodsSkuModel->where('sku_id', $prow['SkuId'])->update($upData);
            if ($res < 1) {
                Db::rollback();// 回滚事务
                return $this->error('更新商品规格库存失败，请重试.');
            }
            $isUp = true;
        }
        Db::commit();// 提交事务
        if ($isUp == false){
            return $this->error('没有输入库存数量，请重试.');
        }
        $this->Model->_log($goods_id, '修改商品库存：' . $goods['goods_name'], $goods_status[$goods['isputaway']], 'supplyer', $this->supplyer_id);
        $this->Model->cleanMemcache($goods_id);
        return $this->success('操作成功.');
    }

}
