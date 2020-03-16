<?php
/*------------------------------------------------------ */
//-- 计划任务
//-- Author: MEI 2020-3-16
/*------------------------------------------------------ */
namespace app\ddkc\controller;
use app\distribution\model\DividendRoleModel;
use think\Db;
use app\ApiController;
use app\ddkc\model\MiningOrderModel;
use think\cache\driver\Redis;
use app\member\model\AccountLogModel;
use app\member\model\AccountModel;
use app\mainadmin\model\SettingsModel;
use app\member\model\UsersModel;

class Task  extends ApiController{
  
	/*------------------------------------------------------ */
    //-- 矿机&定存包每日计算
    /*------------------------------------------------------ */
    public function miningDailyReturn(){
        $time = time();
        $orderModel = new MiningOrderModel();
        $accountModel = new AccountLogModel();
        $usersModel = new UsersModel();

        # 取出所有运行中矿机
        $where[] = ['status','=',1];
        $allOrder = $orderModel->where($where)->select();
        if (!count($allOrder)) {
            $this->miningPrincipalReturn();
            exit('暂无有效数据');
        }

        Db::startTrans();
        foreach ($allOrder as $key => $value) {
            $orderUp = [];

            # 更新订单数据
            $orderUp['surplus_days'] = $value['surplus_days']-1;
            $is_ban = $usersModel->where('user_id',$value['user_id'])->value('is_ban');
            # 有效天数+1
            if (!$is_ban) $orderUp['valid_days'] = $value['valid_days']+1;
            # 是否是最后一天
            if ($orderUp['surplus_days'] <= 0) $orderUp['status'] = 2;

            $orderUp['update_time'] = $time;

            $res2 = $orderModel->where('order_id',$value['order_id'])->update($orderUp);
            if (!$res2) {
                Db::rollback();
                exit('订单处理失败');
            }
        }
        Db::commit();
        $this->miningPrincipalReturn();
    }
    /*------------------------------------------------------ */
    //-- 矿机&定存包收益到账
    /*------------------------------------------------------ */
    public function miningPrincipalReturn(){
        $time = time();
        $orderModel = new MiningOrderModel();
        $accountModel = new AccountLogModel();
        $usersModel = new UsersModel();

        # 取出所有等待返还的订单
        $where[] = ['status','=',2];
        $allOrder = $orderModel->where($where)->select();
        if (!count($allOrder)) exit('暂无有效数据');
        
        Db::startTrans();
        foreach ($allOrder as $key => $value) {
            $is_ban = $usersModel->where('user_id',$value['user_id'])->value('is_ban');
            # 账号冻结将延缓到账
            if ($is_ban) continue;

            # 计算收益 本金+（本金*单天收益*有效天数） 
            $money = round($value['pay_money'] + ($value['pay_money'] * $value['rebate_rate'] / 100 * $value['valid_days']),2);
            if ($money <= 0) continue;

            $desc = $value['type'] == 1 ? '矿机收益到账' : '增值包收益到账';
            $change_type = $value['type'] == 1 ? '105' : '106';

            # 添加金额
            $orderData['ddb_money']   = $money;
            $orderData['change_desc'] = $desc;
            $orderData['change_type'] = $change_type;
            $res = $accountModel->change($orderData, $value['user_id'], false);
            if (!$res) {
                Db::rollback();
                exit('收益返还失败');
            }

            # 更新订单表
            $orderUp['status'] = 3;
            $orderUp['update_time'] = $time;
            $res2 = $orderModel->where('order_id',$value['order_id'])->update($orderUp);
            if (!$res2) {
                Db::rollback();
                exit('订单更新失败');
            }
        }
        Db::commit();
        echo "执行成功";
    }
}?>