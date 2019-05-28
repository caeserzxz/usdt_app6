<?php

namespace app\shop\model;

use app\BaseModel;


//*------------------------------------------------------ */
//-- 订单日志表
/*------------------------------------------------------ */

class OrderLogModel extends BaseModel
{
    protected $table = 'shop_order_log';
    public $pk = 'log_id';

    /*------------------------------------------------------ */
    //-- 写入订单日志   
    /*------------------------------------------------------ */
    public static function _log(&$order, $logInfo = '')
    {
        $inArr['order_id'] = $order['order_id'];
        $inArr['admin_id'] = defined("AUID") ? AUID : 0;
        $inArr['order_status'] = $order['order_status'];
        $inArr['shipping_status'] = $order['shipping_status'];
        $inArr['pay_status'] = $order['pay_status'];
        $inArr['log_info'] = $logInfo;
        $inArr['log_time'] = time();
        return self::create($inArr);
    }
}
