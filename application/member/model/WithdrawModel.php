<?php

namespace app\member\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 提现日志
/*------------------------------------------------------ */
class WithdrawModel extends BaseModel
{
	protected $table = 'users_withdraw_log';
	public  $pk = 'log_id';
    

}
