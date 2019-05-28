<?php
/*------------------------------------------------------ */
//-- 提成处理
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\distribution\model;
use app\BaseModel;

class DividendModel extends BaseModel
{
    protected $table = 'distribution_dividend_log';
    public $pk = 'log_id';

    /*------------------------------------------------------ */
    //-- 计算提成并记录或更新
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
    public function evalArrival($order_id = 0){
        $fun = str_replace('/', '\\', '/distribution/'.config('config.dividend_type').'/Dividend');
        $Model = new $fun($this);
        return $Model->evalArrival($order_id);
    }

}

?>
