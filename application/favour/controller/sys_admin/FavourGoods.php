<?php

namespace app\favour\controller\sys_admin;

use think\Db;
use app\AdminController;
use  app\favour\model\FavourModel;
use  app\favour\model\FavourGoodsModel;
use  app\favour\model\FavourGoodsInfoModel;
use app\shop\model\GoodsModel;

/**
 * 限时优惠-商品相关
 * Class Index
 * @package app\store\controller
 */
class FavourGoods extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize($isretrun = true)
    {
        parent::initialize();
        $this->Model = new FavourGoodsModel();
    }
    //*------------------------------------------------------ */
    //-- 首页
    /*------------------------------------------------------ */
    public function index()
    {
        $reportrange = input('reportrange', '', 'trim');
        if (empty($reportrange) == false) {
            $reportrange = str_replace('_', '/', $reportrange);
            $dtime = explode('-', $reportrange);
            $this->assign("start_date", $dtime[0]);
            $this->assign("end_date", $dtime[1]);
        } else {
            $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
            $this->assign("end_date", date('Y/m/d'));
        }
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $where = [];
        $search['fa_id'] = input('fa_id', 0, 'intval');
        $search['date_sel'] = input('date_sel', 0, 'trim');
        $search['week_sel'] = input('week_sel', -1, 'intval');
        $search['cycle_sel'] = input('cycle_sel', 0, 'trim');
        if (empty($search['fa_id']) == false) {
            $where[] = ['fa_id', '=', $search['fa_id']];
        }

        $list = $this->Model->where($where)->order('fa_id DESC')->select();

        $GoodsModel = new GoodsModel();
        $FavourGoodsInfoModel = new FavourGoodsInfoModel();
        $data['list'] = [];
        foreach ($list as $key => $row) {
            //过滤不符合筛选条件
            if (empty($search['date_sel']) == false) {
                $date_slot = explode(',', $row['date_slot']);
                $date_slot[] = 0;
                if (in_array($search['date_sel'], $date_slot) == false) {
                    continue;
                }
            }
            if ($search['week_sel'] > -1) {
                $date_slot = explode(',', $row['week_slot']);
                $date_slot[] = 0;
                if (in_array($search['week_sel'], $date_slot) == false) {
                    continue;
                }
            }
            if (empty($search['cycle_sel']) == false) {
                $date_slot = explode(',', $row['time_slot']);
                $date_slot[] = 0;
                if (in_array($search['cycle_sel'], $date_slot) == false) {
                    continue;
                }
            }
            $goods = $GoodsModel->info($row['goods_id']);
            $row['goods'] = $goods;
            if ($goods['is_spec'] == 1) {
                $fg_rows = $FavourGoodsInfoModel->where('fg_id', $row['fg_id'])->select();
                $fg_goods_info = [];
                foreach ($fg_rows as $fg) {
                    $fg_goods_info[$fg['sku_id']] = $fg;
                }
            } else {
                $fg_goods_info = $FavourGoodsInfoModel->where('fg_id', $row['fg_id'])->find();
            }
            $row['fg_goods_info'] = $fg_goods_info;

            $data['list'][] = $row;
        }

        $priceType = array(
            1 => '固定价格',
            2 => '折扣',
            3 => '减免金额',
        );
        $this->assign("priceType", $priceType);
        $this->assign("data", $data);
        if ($runData == false) {
            $this->data['content'] = $this->fetch('list')->getContent();
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }


    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function setGoods()
    {
        $favour_type = input('favour_type', 1, 'intval');
        $this->assign('favour_type', $favour_type);
        return $this->fetch('set_goods');
    }

    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data)
    {
        $FavourModel = new FavourModel();
        $FavourGoodsInfoModel = new FavourGoodsInfoModel();
        if ($data['fg_id'] > 0) {
            $goods = (new GoodsModel)->info($data['goods_id']);
            $this->assign('goods', $goods);
            $fg_goods = $this->Model->where('fg_id', $data['fg_id'])->find();
            $this->assign('fg_goods', $fg_goods);

            if ($goods['is_spec'] == 1) {
                $fg_rows = $FavourGoodsInfoModel->where('fg_id', $data['fg_id'])->select();
                $fg_goods_info = [];
                foreach ($fg_rows as $fg) {
                    $fg_goods_info[$fg['sku_id']] = $fg;
                }
            } else {
                $fg_goods_info = $FavourGoodsInfoModel->where('fg_id', $data['fg_id'])->find();
            }
            $this->assign('fg_goods_info', $fg_goods_info);
        }
        $favourInfo = input('');
        //日期列表
        $dateList = $FavourModel->splitDates($favourInfo['start_date'], $favourInfo['end_date']);
        $this->assign('dateList', $dateList);

        //档期列表
        $cycleList = $FavourModel->getCycleList();
        $this->assign('cycleList', $cycleList);

        $this->assign('favourInfo', $favourInfo);

        $data['week_slot'] = empty($data['week_slot']) ? [] : explode(',', $data['week_slot']);

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
        $goods_number = input('goods_number');
        $price_num = input('price_num');
        $price_type = input('price_type');
        $limit_num = input('limit_num');
        $goods_price = $row['goods_price'];
        $FavourGoodsInfoModel = new FavourGoodsInfoModel();
        $inData['fg_id'] = $row['fg_id'];
        $inData['update_time'] = time();
        if (empty($sku_ids)) {
            $inData['goods_id'] = $row['goods_id'];
            $inData['goods_number'] = $goods_number * 1;
            $inData['price_num'] = $price_num * 1;
            $inData['price_type'] = $price_type * 1;
            $inData['goods_price'] = $goods_price * 1;
            $inData['limit_num'] = $limit_num * 1;
            if ($inData['goods_number'] <= 0) {
                Db::rollback();// 回滚事务
                return $this->error('操作失败:活动库存必须大于0.');
            }
            $res = $FavourGoodsInfoModel::create($inData);
            if ($res < 1) {
                Db::rollback();// 回滚事务
                return $this->error('写入活动商品失败.');
            }
        } else {
            foreach ($sku_ids as $key => $sku_id) {
                $inData['sku_id'] = $sku_id;
                $inData['goods_id'] = $row['goods_id'] * 1;
                $inData['goods_number'] = $goods_number[$sku_id] * 1;
                $inData['price_num'] = $price_num[$sku_id] * 1;
                $inData['price_type'] = $price_type[$sku_id] * 1;
                $inData['goods_price'] = $goods_price[$sku_id];
                $inData['limit_num'] = $limit_num[$sku_id] * 1;
                if ($inData['goods_number'] <= 0) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:活动库存必须大于0.');
                }
                $res = $FavourGoodsInfoModel::create($inData);
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('写入活动商品失败.');
                }
            }
        }
        Db::commit();// 提交事务
        $this->Model->cleanGoodscache($row['goods_id']);
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
        $goods_number = input('goods_number');
        $price_num = input('price_num');
        $price_type = input('price_type');
        $limit_num = input('limit_num');
        $goods_price = $row['goods_price'];
        $FavourGoodsInfoModel = new FavourGoodsInfoModel();
        $time = time();
        $inData['update_time'] = $time;
        if (empty($sku_ids)) {

            $inData['goods_id'] = $row['goods_id'];
            $inData['goods_number'] = $goods_number * 1;
            $inData['price_num'] = $price_num * 1;
            $inData['price_type'] = $price_type * 1;
            $inData['goods_price'] = $goods_price;
            $inData['limit_num'] = $limit_num * 1;

            if ($inData['goods_price'] < 0) {
                Db::rollback();// 回滚事务
                return $this->error('操作失败:活动价格不能小于0.');
            }

            $where = [];
            $where[] = ['fg_id', '=', $row['fg_id']];
            $where[] = ['goods_id', '=', $row['goods_id']];
            $count = $FavourGoodsInfoModel->where($where)->count();
            if ($count > 0) {
                $res = $FavourGoodsInfoModel->where($where)->update($inData);
            } else {
                $res = $FavourGoodsInfoModel::create($inData);
            }

            if ($res < 1) {
                Db::rollback();// 回滚事务
                return $this->error('处理秒杀商品失败.');
            }
        } else {
            foreach ($sku_ids as $key => $sku_id) {
                $inData['sku_id'] = $sku_id;
                $inData['goods_id'] = $row['goods_id'] * 1;
                $inData['goods_number'] = $goods_number[$sku_id] * 1;
                $inData['price_num'] = $price_num[$sku_id] * 1;
                $inData['price_type'] = $price_type[$sku_id] * 1;
                $inData['limit_num'] = $limit_num[$sku_id] * 1;
                $inData['goods_price'] = $goods_price[$sku_id];

                if ($inData['goods_price'] < 0) {
                    Db::rollback();// 回滚事务
                    return $this->error('操作失败:活动价格不能小于0.');
                }

                $where = [];
                $where[] = ['fg_id', '=', $row['fg_id']];
                $where[] = ['goods_id', '=', $row['goods_id']];
                $where[] = ['sku_id', '=', $sku_id];
                $count = $FavourGoodsInfoModel->where($where)->count();
                if ($count > 0) {
                    $res = $FavourGoodsInfoModel->where($where)->update($inData);
                } else {
                    $res = $FavourGoodsInfoModel::create($inData);
                }
                if ($res < 1) {
                    Db::rollback();// 回滚事务
                    return $this->error('处理秒杀商品失败.');
                }
            }
        }
        //删除没定义的数据
        $where = [];
        $where[] = ['fg_id', '=', $row['fg_id']];
        $where[] = ['goods_id', '=', $row['goods_id']];
        $where[] = ['update_time', '<', $time];
        $FavourGoodsInfoModel->where($where)->delete();
        Db::commit();// 提交事务
        $this->Model->cleanMemcache($row['fg_id']);
        $this->Model->cleanGoodscache($row['goods_id']);
        return $this->success('修改成功', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 验证相关数据
    /*------------------------------------------------------ */
    public function checkData($row)
    {
        if ($row['goods_id'] < 1) {
            return $this->error('请先择需要参与活动的商品.');
        }
        $sku_ids = input('sku_ids');
        $price_num = input('price_num');
        $price_type = input('price_type');
        $goods_number = input('goods_number');
        $goods = (new GoodsModel)->info($row['goods_id']);

        $stock = 0;
        if ($goods['is_spec'] == 1) {
            if (empty($sku_ids) == true) {
                return $this->error('请先择需要参与活动的规格.');
            }
            $sub_goods = $goods['sub_goods'];
            $temp_key = array_column($sub_goods, 'sku_id');  //键值
            $sub_goods = array_combine($temp_key, $sub_goods);
            foreach ($sku_ids as $key => $sku_id) {
                $price = $this->countPrice($sub_goods[$sku_id]['shop_price'], $price_num[$sku_id], $price_type[$sku_id]);
                $stock += $goods_number[$sku_id] * 1;
                $priceArr[] = $price;
                $goods_price[$sku_id] = $price;
                if ($price > $sub_goods[$sku_id]['shop_price']) {
                    return $this->error("商品规格【{$sub_goods[$sku_id]['sku_name']}】的活动价格不能大于该规格售价");
                }
            }
        } else {
            $price = $this->countPrice($goods['shop_price'], $price_num, $price_type);
            $priceArr[] = $price;
            $goods_price = $price;
            $stock = $goods_number * 1;
            if ($price > $goods['shop_price']) {
                return $this->error('商品的活动价格不能大于商品售价');
            }
        }
        $row['goods_price'] = $goods_price;
        $row['show_price'] = min($priceArr);
        $row['stock'] = $stock;

        if ($row['show_price'] <= 0) {
            return $this->error('操作失败:活动价格必须大于0.');
        }
        if ($row['favour_type'] == 1) {
            if (empty($row['date_slot'])) return $this->error('操作失败:请选择参与活动的日期.');
            $row['date_slot'] = implode(',', $row['date_slot']);
        }
        if ($row['favour_type'] == 2) {
            if (empty($row['week_slot'])) return $this->error('操作失败:请选择参与活动的星期.');
            $row['week_slot'] = implode(',', $row['week_slot']);
        }
        if (empty($row['time_slot'])) return $this->error('操作失败:请选择参与活动的档期.');
        $row['time_slot'] = implode(',', $row['time_slot']);
        return $row;
    }

    /*------------------------------------------------------ */
    //-- 计算优惠后价格
    /*------------------------------------------------------ */
    public function countPrice($old_price, $num, $price_type)
    {
        $price = 0;
        switch ($price_type) {
            case 1:
                $price = $num;
                break;
            case 2:
                $price = $old_price * $num / 100;
                break;
            case 3:
                $price = $old_price - $num;
                break;
        }
        return $price;
    }

    /*------------------------------------------------------ */
    //-- ajax快速修改
    //-- id int 修改ID
    //-- data array 修改字段
    /*------------------------------------------------------ */
    public function afterAjax($fg_id, $data)
    {
        if (isset($data['status'])) {
            $info = $this->Model->info($fg_id);
            $this->Model->cleanMemcache($fg_id);
            $this->Model->cleanGoodscache($info['goods_id']);
        }
    }

    /*------------------------------------------------------ */
    //-- 删除活动商品
    /*------------------------------------------------------ */
    public function del()
    {
        $map['fg_id'] = input('fg_id', 0, 'intval');
        if ($map['fg_id'] < 1) return $this->error('传递参数失败！');
        $goods = $this->Model->info($map['fg_id']);
        if (empty($goods)) return $this->error('活动商品不存在！');
        $res = $this->Model->where($map)->delete();
        if ($res < 1) return $this->error();
        $res = (new FavourGoodsInfoModel)->where($map)->delete();
        $this->Model->cleanMemcache($map['fg_id']);
        $this->Model->cleanGoodscache($goods['goods_id']);
        return $this->success('操作成功.');
    }
}