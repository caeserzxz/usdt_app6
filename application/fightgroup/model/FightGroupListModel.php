<?php

namespace app\fightgroup\model;

use app\BaseModel;
use think\facade\Cache;
use app\shop\model\OrderModel;
use think\Db;

//*------------------------------------------------------ */
//-- 拼团列表
/*------------------------------------------------------ */

class FightGroupListModel extends BaseModel
{
    protected $table = 'fightgroup_list';
    public $pk = 'gid';
    protected $mkey = 'fightgroup_list_mkey';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($gid = 0)
    {
        Cache::rm($this->mkey . $gid);
    }
    /*------------------------------------------------------ */
    //-- 获取拼团信息
    /*------------------------------------------------------ */
    public function info($gid)
    {
        $fgInfo = Cache::get($this->mkey . $gid);
        $OrderModel = new OrderModel;
        if (empty($fgInfo)) {
            $fgInfo = $this->where('gid', $gid)->find();
            if (empty($fgInfo) == true) return array();
            $fgInfo = $fgInfo->toArray();
            $where[] = ['o.order_type', '=', 2];
            $where[] = ['o.by_id', '=', $fgInfo['fg_id']];
            $where[] = ['o.pid', '=', $gid];
            $where[] = ['o.order_status', 'in', [0, 1]];
            $fgInfo['order'] = $OrderModel->alias('o')->join("users u", "o.user_id=u.user_id", 'left')->where($where)->field('o.order_id,o.user_id,o.order_status,u.nick_name,u.headimgurl')->select()->toArray();
            Cache::set($this->mkey . $gid, $fgInfo, 10);
        }

        $upData = [];
        if ($fgInfo['status'] == config('config.FG_WAITPAY')) {//刚创建拼团待支付
            if ($fgInfo['fail_time'] < time()) {//超时取消
                $fgInfo['status'] = $upData['status'] = config('config.FG_FAIL');
            } elseif (count($fgInfo['order']) > 0) {
                $fgInfo['status'] = $upData['status'] = config('config.FG_DOING');
            }
        } elseif ($fgInfo['status'] == config('config.FG_DOING') || $fgInfo['status'] == config('config.FG_FULL')) {//拼团中
            $order_count = count($fgInfo['order']);
            $ok_count = 0;
            foreach ($fgInfo['order'] as $order) {
                if ($order['order_status'] == 1) {
                    $ok_count += 1;
                }
            }
            if ($ok_count >= $fgInfo['success_num']) {
                $fgInfo['status'] = $upData['status'] = config('config.FG_SEUCCESS');
            } elseif ($fgInfo['fail_time'] < time()) {//超时取消
                $fgInfo['status'] = $upData['status'] = config('config.FG_FAIL');
            } else {

                if ($order_count >= $fgInfo['success_num']) {//满员处理
                    $fgInfo['status'] = $upData['status'] = config('config.FG_FULL');
                }
            }
        }
        if (empty($upData) == false) {
            $res = true;
            if ($upData['status'] == config('config.FG_FAIL')) {//拼团失败更新订单
                $owhere[] = ['order_type', '=', 2];
                $owhere[] = ['by_id', '=', $fgInfo['fg_id']];
                $owhere[] = ['pid', '=', $gid];
                $owhere[] = ['order_status', 'in', [0, 1]];
                $orderids = $OrderModel->where($owhere)->column('order_id');
                foreach ($orderids as $order_id) {
                    $orderUp['order_id'] = $order_id;
                    $orderUp['order_status'] = config('config.OS_CANCELED');//取消订单
                    $orderUp['cancel_time'] = time();
                    $_res = $OrderModel->upInfo($orderUp, 'sys');
                    if ($_res !== true){
                        $res = false;
                    }
                }
            } elseif ($upData['status'] == config('config.FG_SEUCCESS')) {//拼团成功
                $oWhere = [];
                $oWhere[] = ['pid', '=', $gid];
                $oWhere[] = ['order_type', '=', 2];
                $oWhere[] = ['order_status', '=', 1];
                $_res = $OrderModel->where($oWhere)->update(['is_success' => 1]);
                if ($_res < 1) {
                    $res = false;
                }
            }
            if ($res == true){
                $this->where('gid', $gid)->update($upData);
                Cache::set($this->mkey . $gid, $fgInfo, 10);
            }
        }
        return $fgInfo;
    }
    /*------------------------------------------------------ */
    //-- 正在拼团列表
    /*------------------------------------------------------ */
    public function getList($fg_id, $limit = 0)
    {
        $where[] = ['fgl.fg_id', '=', $fg_id];
        $where[] = ['fgl.status', '=', 1];
        $where[] = ['fgl.fail_time', '>', time()];
        $viewObj = $this->alias('fgl')->where($where)->join("users u", "fgl.head_user_id=u.user_id", 'left')->field('fgl.*,u.user_id,u.nick_name,u.headimgurl');
        if ($limit > 0) {
            $viewObj->limit($limit);
        }
        $list = $viewObj->select()->toArray();

        if (empty($list)) return [];
        $OrderModel = new OrderModel();
        foreach ($list as $key => $row) {
            $where = [];
            $where[] = ['order_type', '=', 2];
            $where[] = ['pid', '=', $row['gid']];
            $where[] = ['order_status', '=', config('config.OS_CONFIRMED')];
            $list[$key]['order_count'] = $OrderModel->where($where)->count();
        }
        return $list;
    }
    /*------------------------------------------------------ */
    //-- 取消失效拼团
    /*------------------------------------------------------ */
    public function evalFail($user_id = 0)
    {
        $time = time();
        if ($user_id > 0) {
            $where[] = ['head_user_id', '=', $user_id];
        }
        $where[] = ['status', '<', 3];
        $where[] = ['fail_time', '<', $time];
        $gids = $this->where($where)->column('gid');
        if (empty($gids)) return true;
        foreach ($gids as $gid) {
            $this->info($gid);
        }
        return true;
    }
}
