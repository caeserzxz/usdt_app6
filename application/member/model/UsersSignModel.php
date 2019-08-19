<?php
namespace app\member\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 签到日志
/*------------------------------------------------------ */
class UsersSignModel extends BaseModel
{
	protected $table = 'users_sign';
	public  $pk = 'sign_id';

}
