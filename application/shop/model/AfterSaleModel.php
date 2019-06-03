<?php
namespace app\shop\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 售后表
/*------------------------------------------------------ */
class AfterSaleModel extends BaseModel
{
	protected $table = 'shop_after_sale';
	public $pk = 'as_id';
	public $status = ['0'=>'审核中','1'=>'审核失败','2'=>'待退货','3'=>'退货待确认',9=>'已完成'];
    public $type = ['return_goods'=>'退货','change_goods'=>'换货'];
    /*------------------------------------------------------ */
    //-- 生成编号
    /*------------------------------------------------------ */
    public function getSn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);
        $date = date('Ymd');
        $as_sn = $date . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $where[] = ['as_sn', '=', $as_sn];
        $where[] = ['add_time', '>', strtotime($date)];
        $count = $this->where($where)->count('as_id');
        if ($count > 0) return $this->getSn();
        return $as_sn;
    }
    /*------------------------------------------------------ */
    //-- 写入日志
    /*------------------------------------------------------ */
    public static function _log($as_id, $logInfo = '', $status = '', $opt_source = '', $operator = 0)
    {
        $inArr['as_id'] = $as_id;
        $inArr['opt_source'] = $opt_source;
        $inArr['operator'] = $operator;
        $inArr['status'] = $status;
        $inArr['log_info'] = $logInfo;
        $inArr['log_time'] = time();
        return (new AfterSaleLogModel)::create($inArr);
    }
}
