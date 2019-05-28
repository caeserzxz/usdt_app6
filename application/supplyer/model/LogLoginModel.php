<?php

namespace app\supplyer\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 登陆日志
/*------------------------------------------------------ */
class LogLoginModel extends BaseModel
{
	protected $table = 'supplyer_log_login';
    public  $pk = 'log_id';

}
