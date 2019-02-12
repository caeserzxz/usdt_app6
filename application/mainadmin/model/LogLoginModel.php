<?php

namespace app\mainadmin\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 登陆日志
/*------------------------------------------------------ */
class LogLoginModel extends BaseModel
{
	protected $table = 'main_log_login';
    public  $pk = 'log_id';

}
