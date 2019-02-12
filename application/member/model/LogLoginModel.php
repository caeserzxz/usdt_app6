<?php

namespace app\member\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 会员登陆日志
/*------------------------------------------------------ */
class LogLoginModel extends BaseModel
{
	protected $table = 'users_log_login';
	public  $pk = 'log_id';
    

}
