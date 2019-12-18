<?php

namespace app\shop\model;

use app\BaseModel;
use think\facade\Cache;

//*------------------------------------------------------ */
//-- 商品优惠券
/*------------------------------------------------------ */

class BonusModel extends BaseModel
{
    protected $table = 'shop_bonus_type';
    public $pk = 'type_id';
    protected $mkey = 'bonus_info_mkey_';
    /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($type_id)
    {
        Cache::rm($this->mkey . $type_id);
        Cache::rm('receivable_bonus_0');//可领劵列表-领劵中心
        Cache::rm('receivable_bonus_1');//可领劵列表-普通商品
        Cache::rm('receivable_bonus_2');//可领劵列表-拼团商品
        Cache::rm('receivable_bonus_3');//可领劵列表-秒杀商品
        if ($this->userInfo['user_id'] > 0) {
            Cache::rm($this->mkey . '_user_' . $this->userInfo['user_id']);
        }
    }
    /*------------------------------------------------------ */
    //-- 生成数量
    //-- $bonus_sum number 优惠券数量
    //-- $userIds array 会员id列表
    /*------------------------------------------------------ */
    public function makeBonusSn($type_id, $bonus_sum, $userIds = array(), $time = null)
    {
        /* 生成优惠券序列号 */
        $BonusListModel = new BonusListModel();
        $num = $BonusListModel->max('bonus_sn');
        $num = $num ? floor($num / 10000) : 100000;
        $bonus_id = array();
        $addnum = 1;
        if (empty($time)) {
            $time = time();
        }
        if (is_array($userIds) == false) {
            $userIds = explode(',', $userIds);
        }
        if (empty($userIds) == false) {
            do {
                $uid = reset($userIds);
                if ($uid < 1) continue;
                $arr['type_id'] = $type_id;
                $arr['bonus_sn'] = ($num + $addnum) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $arr['user_id'] = $uid;
                $arr['add_time'] = $time;
                $res = $BonusListModel->create($arr);
                if ($res['bonus_id'] > 0) {
                    $addnum++;
                    array_shift($userIds);
                    /* 领取数量+1 */
                    $where[] = ['type_id', '=', $type_id];
                    $this->where($where)->setInc('send_num', 1);
                }
            } while (empty($userIds) == false);
        } else {
            do {
                $arr['type_id'] = $type_id;
                $arr['bonus_sn'] = ($num + $addnum) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $arr['add_time'] = $time;
                $res = $BonusListModel->create($arr);
                if ($res['bonus_id'] > 0) $addnum++;
            } while ($addnum < $bonus_sum);
        }
        return $addnum;
    }
    /*------------------------------------------------------ */
    //-- 获取优惠券信息
    //-- $type_id 优惠券主ID
    /*------------------------------------------------------ */
    public function info($type_id = 0)
    {
        $type_id = $type_id * 1;
        $info = Cache::get($this->mkey . $type_id);
        if ($info) return $info;
        $info = $this->find($type_id)->toArray();
        $gmtime = time();
        if ($info['use_start_date'] > $gmtime) {
            $info['stauts'] = 0;
            $info['stauts_info'] = '未到使用时间';
        } elseif ($info['use_end_date'] < $gmtime) {
            $info['stauts'] = -1;
            $info['stauts_info'] = '已过期';
        } elseif ($info['use_status'] == 2) {
            $info['stauts'] = 2;
            $info['stauts_info'] = '已失效';
        } else {
            $info['stauts'] = 1;
            $info['stauts_info'] = '未使用';
        }
        $info['_use_start_date'] = date('Y.m.d', $info['use_start_date']);
        $info['_use_end_date'] = date('Y.m.d', $info['use_end_date']);
        Cache::set($this->mkey . $type_id, $info, 30);
        return $info;
    }

    /*------------------------------------------------------ */
    //-- 获取优惠券信息
    //-- bonus_id 优惠ID
    /*------------------------------------------------------ */
    public function binfo($bonus_id = 0)
    {
        $BonusListModel = new BonusListModel();
        $bonus = $BonusListModel->find($bonus_id)->toArray();
        $bonus['info'] = $this->info($bonus['type_id']);
        if ($bonus['order_id'] > 0) {//已使用
            $bonus['info']['stauts'] = 2;
            $bonus['info']['stauts_info'] = '已使用';
        }
        return $bonus;
    }
    /*------------------------------------------------------ */
    //-- 获取用户优惠券列表
    //-- $type_id 优惠券主ID
    /*------------------------------------------------------ */
    public function getListByUser($uid = 0)
    {
        if ($uid < 1) {
            $uid = $this->userInfo['user_id'] * 1;
            if ($uid < 1) return [];
        }
        $mkey = $this->mkey . '_user_' . $uid;
        $list = Cache::get($mkey);
        if (empty($list['unused']) == false || empty($list['used']) == false || empty($list['expired']) == false) {
            return $list;
        }
        $time = time();
        $exptime = time() - 15552000;//只查询半年180天内的数据
        $BonusListModel = new BonusListModel();
        $rows = $BonusListModel->where("user_id = $uid and add_time > $exptime")->select()->toArray();
        $list['unused'] = array();//可用
        $list['unusable'] = array();//不可用
        foreach ($rows as $row) {
            $row['bonus'] = $this->info($row['type_id']);
            if ($row['status'] == 1) {//已使用
                $list['unusable'][] = $row;
            } elseif ($row['status'] == 2 || $row['bonus']['use_status'] == 2) {//已失效
                $row['status'] = 2;
                $list['unusable'][] = $row;
            } elseif ($row['status'] == 0 && $row['bonus']['use_end_date'] < $time) {//过期
                $row['status'] = 3;
                $list['unusable'][] = $row;
            } elseif ($row['status'] == 0 && $row['bonus']['use_start_date'] > $time) {//未到可用时间
                $row['status'] = 4;
                $list['unusable'][] = $row;
            } else {
                $list['unused'][] = $row;
            }
        }
        $list['unusedNum'] = count($list['unused']);
        $list['unusableNum'] = count($list['unusable']);
        Cache::set($mkey, $list, 30);//缓存30秒
        return $list;
    }
    /*------------------------------------------------------ */
    //-- 获取可领取优惠券到商品详情
    //-- goods_id 商品ID
    //-- uid 用户ID
    /*------------------------------------------------------ */
    public function getListReceivable($uid = 0, $goods_type = 0, $goodsInfo = array())
    {
        $uid=$uid>0?$uid:0;
        $rows = Cache::get('receivable_bonus_' . $goods_type);
        $time = time();
        if (empty($rows) == true) {
            $where[] = ['send_type', '=', 2];
            if ($goods_type > 0) {
                $where[] = ['goods_type', '=', $goods_type];
            }
            $where[] = ['send_status', '=', 1];
            $where[] = ['send_start_date', '<', $time];
            $where[] = ['use_end_date', '>', $time];
            $rows = $this->where($where)->order('type_money DESC,use_end_date ASC')->select()->toArray();
            Cache::set('receivable_bonus_' . $goods_type, $rows, 600);
        }

        $type_ids = array_column($rows, 'type_id');
        $BonusListModel = new BonusListModel();
        $whereReceived[] = ['user_id', '=', $uid];
        $whereReceived[] = ['type_id', 'in', $type_ids];
        $userReceived = $BonusListModel->where($whereReceived)->column('type_id,bonus_id,status');//已领取的优惠券
        $userReceivedIds = array_column($userReceived, 'type_id');

        //商品分类列表
        $ClassList = (new CategoryModel)->getRows();

        $newList = [];
        foreach ($rows as $key => $row) {
            $_t = $row;
            $_t['_use_start_date'] = date("Y.m.d", $row['use_start_date']);
            $_t['_use_end_date'] = date("Y.m.d", $row['use_end_date']);

            if (in_array($row['type_id'], $userReceivedIds)) {//已领取
                if ($userReceived[$row['type_id']]['status'] > 0) {//过滤已使用或已失效
                    continue;
                }
                $_t['receive_status'] = 1;
            } elseif ($row['type_num'] > 0 && ($row['type_num'] <= $row['send_num'])) {//已领完
                $_t['receive_status'] = 2;
            } elseif ($row['use_end_date'] < $time) {//已过使用时间，清除
                continue;
            } elseif ($row['send_end_date'] < $time) {//已过领取时间时间，视为已抢光
                $_t['receive_status'] = 2;
            } else {//未领取
                $_t['receive_status'] = 0;
            }

            //指定分类可用
            if ($row['use_type'] == 1) {
                $use_by = explode(',', $row['use_by']);
                $cidInfo = [];//分类信息
                foreach ($use_by as $cid) {
                    $cidInfo[] = $ClassList[$cid]['children'];
                }
                $_t['cidInfo'] = join(',', $cidInfo);
                $_t['cidInfo'] = explode(',', $_t['cidInfo']);//转成数组
                if (empty($goodsInfo) == false && in_array($goodsInfo['cid'], $_t['cidInfo']) == false) {
                    continue;
                }
            } elseif ($row['use_type'] == 2) {
                if ($goods_type == 1) {
                    $use_by = explode($_t['use_by']);
                    if (empty($goodsInfo) == false && in_array($goodsInfo['goods_id'], $use_by) == false) {
                        continue;
                    }
                }
            }
            $newList[] = $_t;
        }
        //重新排序-按状态小到大
        $receive_status = array_column($newList, 'receive_status');
        array_multisort($receive_status, SORT_ASC, $newList);
        return $newList;
    }
    /*------------------------------------------------------ */
    //-- 获取可领取优惠券to自定义修改
    /*------------------------------------------------------ */
    public function getListDiy()
    {
        $time = time();
        $where[] = ['send_type', '=', 2];
        $where[] = ['send_status', '=', 1];
        $where[] = ['send_start_date', '<', $time];
        $where[] = ['use_end_date', '>', $time];
        return $this->where($where)->order('type_money DESC,use_end_date ASC')->select()->toArray();
    }

    /*------------------------------------------------------ */
    //-- 获取结算页可用优惠券
    //-- $user_id 用户ID
    //-- $cartInfo 购买的商品信息
    //-- $goods_type 指定商品类型，1-普通商品，2-拼团
    /*------------------------------------------------------ */
    public function getListAvailable($user_id, $cartInfo,$goods_type=1)
    {
        $time = time();
        $BonusListModel = new BonusListModel();
        $where[] = ['bl.user_id', '=', $user_id];
        $where[] = ['bl.status', '=', 0];
        $where[] = ['bt.goods_type', '=', $goods_type];
        $where[] = ['bt.use_status', '=', 1];
        $where[] = ['bt.use_start_date', '<', $time];
        $where[] = ['bt.use_end_date', '>', $time];
        $bonusList = $BonusListModel->alias('bl')
            ->join('shop_bonus_type bt', 'bl.type_id=bt.type_id')
            ->field('bl.user_id,bl.bonus_id,bl.status,bt.*')
            ->where($where)->order('type_money desc,use_end_date asc')->select()->toArray();

        $ClassList = (new CategoryModel)->getRows(); //商品分类列表

        $goodsIds = array_column($cartInfo['goodsList'], 'goods_id');//购买商品ID
        $goodsCatIds = array_column($cartInfo['goodsList'], 'cid');//购买商品分类ID
        $list = array();//可用
        foreach ($bonusList as $key => $row) {
            $row['_use_start_date'] = date("Y.m.d", $row['use_start_date']);
            $row['_use_end_date'] = date("Y.m.d", $row['use_end_date']);

            if ($row['use_type'] == 0) {//全场通用
                $list[] = $row;
            } elseif ($row['use_type'] == 1) {//指定分类可用
                $use_by = explode(',', $row['use_by']);
                $cidInfo = [];//分类信息
                foreach ($use_by as $cid) {
                    $cidInfo[] = $ClassList[$cid]['children'];
                }
                $row['cidInfo'] = join(',', $cidInfo);
                $cidInfoArr = explode(',', $row['cidInfo']);//转成数组
                if (array_intersect($cidInfoArr, $goodsCatIds)) {
                    $list[] = $row;
                }
            } elseif ($row['use_type'] == 2) {//指定商品可用
                $useGoodsIds = explode(',', $row['use_by']);//可用的商品ID
                if (array_intersect($useGoodsIds, $goodsIds)) {//有可用商品
                    $list[] = $row;
                }
            }
        }
        $bonusList = $this->splitAbleBonusList($list, $cartInfo);//拆分可用，不可用优惠券
        return $bonusList;
    }

    /*------------------------------------------------------ */
    //-- 判定优惠券是否符合使用条件，区分为可用，不可用
    /*------------------------------------------------------ */
    public function splitAbleBonusList($bonusList, $cartList)
    {
        $list['able'] = array();//可用
        $list['unable'] = array();//不可用
        foreach ($bonusList as $row) {
            if ($row['use_type'] == 0) {//全场通用
                if ($cartList['orderTotal'] >= $row['min_amount']) {//订单商品总价满足使用条件
                    $list['able'][] = $row;
                } else {
                    $list['unable'][] = $row;
                }
            } elseif ($row['use_type'] == 1) {//指定分类可用
                $TotalGoodsCatPrice = 0;//指定商品分类的购买总价
                if ($cartList['goodsList']) {
                    $cidInfoArr = explode(',', $row['cidInfo']);//转成数组
                    foreach ($cartList['goodsList'] as $goods) {
                        if (in_array($goods['cid'], $cidInfoArr)) {//符合使用分类
                            $TotalGoodsCatPrice += $goods['sale_price'] * $goods['goods_number'];
                        }
                    }
                }
                if ($TotalGoodsCatPrice >= $row['min_amount']) {//订单分类商品总价满足使用条件
                    $list['able'][] = $row;
                } else {
                    $list['unable'][] = $row;
                }
            } elseif ($row['use_type'] == 2) {
                $TotalGoodsPrice = 0;//指定商品的购买总价
                $use_by = explode(',', $row['use_by']);//可用的商品ID
                if ($cartList['goodsList']) {
                    foreach ($cartList['goodsList'] as $goods) {
                        if (in_array($goods['goods_id'], $use_by)) {//符合使用商品，拼团时goods_id=fg_id
                            $TotalGoodsPrice += $goods['sale_price'] * $goods['goods_number'];
                        }
                    }
                }
                if ($TotalGoodsPrice >= $row['min_amount']) {//指定商品总价满足使用条件
                    $list['able'][] = $row;
                } else {
                    $list['unable'][] = $row;
                }
            }
        }
        $list['ableNum'] = count($list['able']);
        $list['unableNum'] = count($list['unable']);
        return $list;
    }

    /*------------------------------------------------------ */
    //-- 注册送红包
    /*------------------------------------------------------ */
    public function sendByReg($uid = 0)
    {
        if ($uid < 1) return false;
        $time = time();
        $where[] = ['send_status', '=', 1];
        $where[] = ['send_type', '=', 4];
        $where[] = ['send_start_date', '<', $time];
        $where[] = ['send_end_date', '>', $time];
        $rows = $this->where($where)->select()->toArray();
        $bonusIds = [];
        foreach ($rows as $row) {
            if ($row['type_num'] > 0 && ($row['type_num'] <= $row['send_num'])) continue;//跳过已领完的劵
            $bonusIds[] = $row['type_id'];
        }
        foreach ($bonusIds as $bonus_id) {
            $this->makeBonusSn($bonus_id, 1, $uid);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 获取免费优惠券列表
    /*------------------------------------------------------ */
    public function getListByFree()
    {
        $mkey = $this->mkey . '_free';
        $list = Cache::get($mkey);
        if (empty($list['unused']) == false || empty($list['used']) == false || empty($list['expired']) == false) {
            return $list;
        }
        $rows = $this->where("send_type = 2 ")->select()->toArray();
        $list = array();
        foreach ($rows as $row) {
            $_t = $row;
            if (time() < $row['send_end_date']) {
                $_t['send_start_date'] = date("Y-m-d H:i", $row['send_start_date']);
                $_t['send_end_date'] = date("Y-m-d H:i", $row['send_end_date']);
                $_t['_use_start_date'] = date("Y.m.d", $row['use_start_date']);
                $_t['_use_end_date'] = date("Y.m.d", $row['use_end_date']);
                $list[] = $_t;
            }
        }
        Cache::set($mkey, $list, 30);//缓存30秒
        return $list;
    }
    /*------------------------------------------------------ */
    //-- 获取未提示的优惠券
    /*------------------------------------------------------ */
    public function getUntipBonusList($user_id, $bonus_tip)
    {
        if ($user_id < 1 || $bonus_tip == 1) {
            return false;
        }
        $where[] = ['bt.send_type', '=', 4];
        $where[] = ['is_tip', '=', 0];
        $where[] = ['user_id', '=', $user_id];
        $BonusListModel = new BonusListModel();
        $rows = $BonusListModel->alias('bl')
            ->join('shop_bonus_type bt', 'bl.type_id = bt.type_id')
            ->field('bl.bonus_id,bt.*')
            ->where($where)->select()->toArray();
        if (empty($rows)) {
            return false;
        }
        $list = array();
        foreach ($rows as $row) {
            $_t = $row;
            $_t['_use_start_date'] = date("Y.m.d", $row['use_start_date']);
            $_t['_use_end_date'] = date("Y.m.d", $row['use_end_date']);
            $list[] = $_t;
        }
        return $list;
    }

    /*------------------------------------------------------ */
    //-- 领取免费优惠卷
    /*------------------------------------------------------ */
    public function receiveFreeBonus($type_id = 0, $user_id = 0)
    {
        $BonusListModel = new BonusListModel();
        if ($user_id < 1) return false;
        $info = $this->where(['type_id' => $type_id])->find();
        if ($info['send_status'] == 2) {
            return array('code' => 2, 'msg' => '该优惠券已抢光');
        }
        if ($info['type_num'] > 0 && ($info['type_num'] <= $info['send_num'])) {
            return array('code' => 2, 'msg' => '该优惠券已抢光');
        }
        if (empty($info)) return array('code' => 0, 'msg' => '系统繁忙，稍后再试');
        $whereList[]=['type_id','=',$type_id];
        $whereList[]=['user_id','=',$user_id];
        $log = $BonusListModel->where($whereList)->find();
        if ($log) return array('code' => 0, 'msg' => '该优惠券已领取');
        /* 生成优惠券序列号 */
        $num = $BonusListModel->max('bonus_sn');
        $num = $num ? floor($num / 10000) : 100000;
        $addnum = 1;
        $time = time();
        $arr['type_id'] = $type_id;
        $arr['bonus_sn'] = ($num + $addnum) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $arr['user_id'] = $user_id;
        $arr['add_time'] = $time;
        $res = $BonusListModel->create($arr);
        if ($res['bonus_id'] < 1) return array('code' => 0, 'msg' => '未知错误，领取失败，请稍后再试');
        /* 领取数量+1 */
        $where[] = ['type_id', '=', $type_id];
        $this->where($where)->setInc('send_num', 1);
        return array('code' => 1, 'msg' => '领取成功');
    }

}
