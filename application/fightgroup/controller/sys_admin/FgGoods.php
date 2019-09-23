<?php

namespace app\fightgroup\controller\sys_admin;

use think\Db;
use app\AdminController;
use app\fightgroup\model\FightGroupModel;
use app\fightgroup\model\FightGoodsModel;
use app\shop\model\GoodsModel;


/**
 * 拼团商品相关
 * Class Index
 * @package app\store\controller
 */
class FgGoods extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new FightGroupModel();
    }
    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $where = [];
        $status = input('status',0,'intval');
        $time = time();
        switch ($status)
        {
            case 1:
                $where[] = ['fg.start_date', '>', $time];
            break;
            case 2:
                $where[] = ['fg.start_date', '<', $time];
                $where[] = ['fg.end_date', '>', $time];
            break;
            case 3:
                $where[] = ['fg.end_date', '<', $time];
            break;
            default:
            break;
        }
        $viewObj = $this->Model->alias('fg')->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left');
        $viewObj->where($where)->field('fg.*,g.goods_name,g.goods_sn,g.is_spec')->order('fg_id DESC');
        $this->data = $this->getPageList($this->Model, $viewObj);
        $this->assign("data", $this->data);
        $this->assign("time", $time);
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
    public function asInfo($data)
    {
        if ($data['fg_id'] > 0){
            $goods = (new GoodsModel)->info($data['goods_id']);
            $this->assign('goods',$goods);
            if ($goods['is_spec'] == 1){
                $fg_rows = (new FightGoodsModel)->where('fg_id',$data['fg_id'])->select();
                $fg_goods = [];
                foreach ($fg_rows as $fg){
                    $fg_goods[$fg['sku_id']] = $fg;
                }
            }else{
                $fg_goods = (new FightGoodsModel)->where('fg_id',$data['fg_id'])->find();
            }

            $this->assign('fg_goods',$fg_goods);
        }
        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($row)
    {
        $row = $this->checkData($row);
        Db::startTrans();//启动事务
        $row['add_time'] = $row['update_time'] = time();
        return $row;
    }
    /*------------------------------------------------------ */
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($row)
    {
        $sku_ids = input('sku_ids');
        $fg_number = input('fg_number');
        $fg_price = input('fg_price');
        $FightGoodsModel = new FightGoodsModel();
        $inData['fg_id'] = $row['fg_id'];
        $inData['update_time'] = time();
        if (empty($sku_ids)) {
            $inData['goods_id'] = $row['goods_id'];
            $inData['fg_number'] = $fg_number * 1;
            $inData['fg_price'] = $fg_price * 1;
            if ($inData['goods_price'] < 0) {
                Db::rollback();// 回滚事务
                return $this->error('操作失败:拼团价格不能小于0.');
            }

            $res = $FightGoodsModel->create($inData);
            if ($res->gid < 1) {
                Db::rollback();// 回滚事务
                return $this->error('写入拼团商品失败.');
            }
        }else{
            foreach ($sku_ids as $key => $sku_id) {
                $inData['sku_id'] = $sku_id;
                $inData['goods_id'] = $row['goods_id'] * 1;
                $inData['fg_number'] = $fg_number[$sku_id] * 1;
                $inData['fg_price'] = $fg_price[$sku_id] * 1;
                if ($inData['fg_price'] < 0) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:拼团价格不能小于0.');
                }

                $res = $FightGoodsModel::create($inData);
                if ($res->gid < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('写入拼团商品失败.');
                }
            }
        }
        Db::commit();// 提交事务
        return $this->success('添加成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前处理
    /*------------------------------------------------------ */
    public function beforeEdit($row)
    {
        $row = $this->checkData($row);
        Db::startTrans();//启动事务
        $row['update_time'] = time();
        return $row;
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($row)
    {
        $sku_ids = input('sku_ids');
        $fg_number = input('fg_number');
        $fg_price = input('fg_price');
        $FightGoodsModel = new FightGoodsModel();
        $inData['fg_id'] = $row['fg_id'];
        $inData['update_time'] = time();
        if (empty($sku_ids)) {
            $inData['fg_number'] = $fg_number * 1;
            $inData['fg_price'] = $fg_price * 1;
            if ($inData['fg_price'] < 0) {
                Db::rollback();// 回滚事务
                return $this->error('操作失败:拼团价格不能小于0.');
            }

            $where = [];
            $where[] = ['fg_id','=',$row['fg_id']];
            $where[] = ['goods_id','=',$row['goods_id']];
            $count = $FightGoodsModel->where($where)->count();
            if ($count > 0){
                $res = $FightGoodsModel->where($where)->update($inData);
            }else{
                $inData['goods_id'] = $row['goods_id'];
                $res = $FightGoodsModel->save($inData);
            }

            if ($res < 1) {
                Db::rollback();// 回滚事务
                return $this->error('处理拼团商品失败-1.');
            }
        }else{
            foreach ($sku_ids as $key => $sku_id) {
                $inData['sku_id'] = $sku_id;
                $inData['goods_id'] = $row['goods_id'];
                $inData['fg_number'] = $fg_number[$sku_id] * 1;
                $inData['fg_price'] = $fg_price[$sku_id] * 1;
                if ($inData['goods_price'] < 0) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:拼团价格不能小于0.');
                }
                $where = [];
                $where[] = ['fg_id','=',$row['fg_id']];
                $where[] = ['goods_id','=',$row['goods_id']];
                $where[] = ['sku_id','=',$sku_id];
                $count = $FightGoodsModel->where($where)->count();
                if ($count > 0){
                    $res = $FightGoodsModel->where($where)->update($inData);
                }else{
                    $res = $FightGoodsModel::create($inData);
                    $res = $res->gid;
                }
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('处理拼团商品失败-2.');
                }
            }
        }

        Db::commit();// 提交事务
        $this->Model->cleanMemcache($row['fg_id']);
        return $this->success('修改成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 验证相关数据
    /*------------------------------------------------------ */
    public function checkData($row)
    {
        if ($row['goods_id'] < 1) {
            return $this->error('请选择需要参与拼团的商品.');
        }
        $sku_ids = input('sku_ids');
        $fg_price = input('fg_price');
        $goods = (new GoodsModel)->info($row['goods_id']);
        if ($goods['is_spec'] == 1) {
            if (empty($sku_ids) == true) {
                return $this->error('请选择需要拼团商品.');
            }
            foreach ($sku_ids as $key => $sku_id) {
                if (empty($fg_price[$sku_id]) == false){
                    $prices[] = $fg_price[$sku_id];
                }
            }
        } else {
            $prices[] = $fg_price * 1;
        }
        $row['show_price'] = min($prices);

        if (empty($row['start_date']) || empty($row['end_date'])) return $this->error('操作失败:请选择拼团时间.');
        if (!checkDateIsValid($row['start_date'])) return $this->error('操作失败:开始时间格式不合法.');
        if (!checkDateIsValid($row['end_date'])) return $this->error('操作失败:结束时间格式不合法.');
        $row['start_date'] = strtotime($row['start_date']);
        $row['end_date'] = strtotime($row['end_date']);
        if ($row['start_date'] >= $row['end_date']) return $this->error('操作失败::开始时间必须大于结束时间.');
        $row['is_usd_bonus'] = $row['is_usd_bonus'] * 1;
        return $row;
    }

    /*------------------------------------------------------ */
    //-- 获取拼团列表信息-供选择使用
    /*------------------------------------------------------ */
    public function selectFightGroup()
    {
        $this->getselectFightGroupList(true);
        return $this->fetch('select_fight_group');
    }
    /*------------------------------------------------------ */
    //-- 获取拼团列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getselectFightGroupList($runData = false)
    {
        $FightGroupModel = new FightGroupModel();
        $where = [];
        $status = input('status', 0, 'intval');
        $time = time();
        switch ($status) {
            case 1:
                $where[] = ['fg.start_date', '>', $time];
                break;
            case 2:
                $where[] = ['fg.start_date', '<', $time];
                $where[] = ['fg.end_date', '>', $time];
                break;
            case 3:
                $where[] = ['fg.end_date', '<', $time];
                break;
            default:
                break;
        }
        $search['goodsArr'] = input('goodsArr', 0, 'trim');
        if (empty($search['goodsArr']) == false) {
            $where[] = ['fg.fg_id', 'not in', $search['goodsArr']];
        }

        $viewObj = $FightGroupModel->alias('fg')->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left');
        $viewObj->where($where)->field('fg.*,g.goods_name,g.goods_sn,g.is_spec')->order('fg_id DESC');
        $this->data = $this->getPageList($FightGroupModel, $viewObj);
        $this->assign("data", $this->data);
        $this->assign("time", $time);
        if ($runData == false) {
            $this->data['content'] = $this->fetch('fight_group_list');
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }

}