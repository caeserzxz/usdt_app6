<?php
namespace app\distribution\model;
use app\BaseModel;
use app\member\model\AccountLogModel;
//*------------------------------------------------------ */
//-- 身份订单表
/*------------------------------------------------------ */
class RoleOrderModel extends BaseModel
{
	protected $table = 'distribution_role_order';
	public  $pk = 'order_id';

    /*------------------------------------------------------ */
    //-- 生成订单编号
    /*------------------------------------------------------ */
    public function getOrderSn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);
        $date = date('Ymd');
        $order_sn = 'role'.$date . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $where[] = ['order_sn', '=', $order_sn];
        $where[] = ['add_time', '>', strtotime($date)];
        $count = $this->where($where)->count('order_id');
        if ($count > 0) return $this->getOrderSn();
        return $order_sn;
    }
    /*------------------------------------------------------ */
    //-- 订单支付成功处理
    /*------------------------------------------------------ */
    public function updatePay($upData = [])
    {
        $order_id = $upData['order_id'];
        unset($upData['order_id']);
        $upData['pay_status'] = 1;
        $upData['pay_time'] = time();
        $res = $this->where('order_id',$order_id)->update($upData);
        if ($res < 1) {
            return false;
        }
        $orderInfo = $this->where('order_id',$order_id)->find()->toArray();
        if ($orderInfo['pay_code'] == 'balance') {//使用余额支付扣减用户余额
            $AccountLogModel = new AccountLogModel();
            $upData['money_paid'] = $orderInfo['order_amount'];
            $upData['pay_time'] = time();
            $changedata['change_desc'] = '订单余额支付';
            $changedata['change_type'] = 3;
            $changedata['by_id'] = $order_id;
            $changedata['balance_money'] = $orderInfo['order_amount'] * -1;
            $res = $AccountLogModel->change($changedata, $this->userInfo['user_id'], false);
            if ($res !== true) {
                Db::rollback();// 回滚事务
                return '支付失败，更新余额失败.';
            }
        }
        //如果设置支付再绑定关系时执行,须优先于分佣计算前执行
        $DividendInfo = settings('DividendInfo');
        if ($DividendInfo['bind_type'] == 1){
            $UsersModel =  new \app\member\model\UsersModel();
            $UsersModel->regUserBind($orderInfo['user_id']);
        }//end

        $this->distribution($orderInfo,'pay');
        //升级，分佣处理
        return true;
    }

    /*------------------------------------------------------ */
    //-- 提成处理&升级处理
    /*------------------------------------------------------ */
    public function distribution(&$orderInfo,$type)
    {
        if (empty($orderInfo)) return false;
        $orderInfo['d_type'] = 'role_order';//身份订单
        $data = (new \app\distribution\model\DividendModel)->_eval($orderInfo,$type);
        if (is_array($data) == false){
            return false;
        }
        $this->where('order_id',$orderInfo['order_id'])->update($data);
        return true;
    }


}
