<?php
/*------------------------------------------------------ */
//-- 提成处理
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\distribution\model;
use app\BaseModel;
use app\member\model\AccountLogModel;
use app\shop\model\OrderModel;

class DividendModel extends BaseModel
{
    protected $table = 'distribution_dividend_log';
    public $pk = 'log_id';

     /*------------------------------------------------------ */
    //-- 计算提成并记录或更新f
    /*------------------------------------------------------ */
    public function _eval(&$orderInfo, $type = ''){
        $fun = str_replace('/', '\\', '/distribution/'.config('config.dividend_type').'/Dividend');
        $Model = new $fun($this);
        return $Model->_eval($orderInfo, $type);
    }
    /*------------------------------------------------------ */
    //-- 执行分佣到帐
    //-- order_id int 订单ID
    /*------------------------------------------------------ */
    public function evalArrival($order_id = 0, $limit_id = 0)
    {
        $time = time();
        $OrderModel = new OrderModel();
        $shop_after_sale_limit = settings('shop_after_sale_limit');//售后时间
        if ($order_id > 0) {
            $where[] = ['order_id', '=', $order_id];
            $where[] = ['status', '=', $OrderModel->config['DD_SIGN']];
            $rows = $this->where($where)->select()->toArray();
        } else {
            if ($shop_after_sale_limit > 0 ){
                $where[] = ['d.status', '=', $OrderModel->config['DD_SIGN']];
                $limit_time = $shop_after_sale_limit * 86400;
                $where[] = ['d.update_time', '<', $time - $limit_time];
                $rows = $this->alias('d')->join('shop_order o','d.order_id = o.order_id')->field('d.*,o.is_after_sale')->where($where)->select()->toArray();
            }else{
                $where[] = ['status', '=', $OrderModel->config['DD_SIGN']];
                $rows = $this->where($where)->select()->toArray();
            }
        }

        if (empty($rows)) return true;//没有找到相关佣金记录

        $AccountLogModel = new AccountLogModel();
        $log_id = [];
        foreach ($rows as $row) {
            if ($shop_after_sale_limit > 0 ){
                if ($row['is_after_sale'] == 1){
                    continue;//有售后，暂不能分佣
                }
            }
            $changedata['change_desc'] = '订单佣金到帐';
            $changedata['change_type'] = 4;
            $changedata['by_id'] = $row['order_id'];
            $changedata['balance_money'] = $row['dividend_amount'];
            $changedata['bean_value'] = $row['dividend_bean'];
            $changedata['total_dividend'] = ($row['dividend_amount'] + $row['dividend_bean']);
            $res = $AccountLogModel->change($changedata, $row['dividend_uid'], false);
            if ($res !== true) {
                return false;
            }
            $upDate['status'] = $OrderModel->config['DD_DIVVIDEND'];
            $upDate['limit_id'] = $limit_id;
            $upDate['update_time'] = $time;
            $res = $this->where('log_id', $row['log_id'])->update($upDate);
            if ($res < 1) {
                return false;
            }
            $log_id[] = $row['log_id'];
        }
        return $log_id;
    }
    /*------------------------------------------------------ */
    //-- 退回分佣到帐,只有普通订单可操作
    //-- order_id int 订单ID
    /*------------------------------------------------------ */
    public function returnArrival($order_id = 0, $type = '')
    {
        $time = time();
        $OrderModel = new OrderModel();
        $where[] = ['order_id', '=', $order_id];
        $rows = $this->where($where)->select()->toArray();
        if (empty($rows)) return true;//没有找到相关佣金记录

        $AccountLogModel = new AccountLogModel();
        foreach ($rows as $row) {
            $upDate['status'] = $type == 'unsign' ? $OrderModel->config['DD_SHIPPED'] : $OrderModel->config['DD_RETURNED'];
            $upDate['limit_id'] = 0;
            $upDate['update_time'] = $time;
            $res = $this->where('log_id', $row['log_id'])->update($upDate);
            if ($res < 1) {
                return false;
            }
            if ($row['status'] == $OrderModel->config['DD_DIVVIDEND']) {
                $changedata['change_desc'] = $type == 'unsign' ? '订单撤销签收-退回佣金' : '订单退货-退回佣金';
                $changedata['change_type'] = 4;
                $changedata['by_id'] = $row['order_id'];
                $changedata['balance_money'] = $row['dividend_amount'] * -1;
                $changedata['bean_value'] = $row['dividend_bean'] * -1;
                $changedata['total_dividend'] = ($row['dividend_amount'] + $row['dividend_bean']) * -1;
                $res = $AccountLogModel->change($changedata, $row['dividend_uid'], false);
                if ($res !== true) {
                    return false;
                }
            }
        }
        return true;
    }
}

?>
