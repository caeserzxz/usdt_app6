<?php
namespace app\distribution\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 间隔到帐佣金日志
/*------------------------------------------------------ */
class EvalArrivalLogModel extends BaseModel
{
	protected $table = 'distribution_eval_arrival_log';
	public  $pk = 'log_id';
	
	
}
