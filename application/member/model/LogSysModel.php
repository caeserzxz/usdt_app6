<?php

namespace app\member\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 会员操作日志
/*------------------------------------------------------ */
class LogSysModel extends BaseModel
{
	protected $table = 'users_log_sys';
	public  $pk = 'log_id';
    

}
