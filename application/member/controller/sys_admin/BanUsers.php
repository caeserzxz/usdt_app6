<?php
namespace app\member\controller\sys_admin;

use app\member\controller\sys_admin\Users;

/**
 * 封禁会员管理
 * Class Index
 * @package app\store\controller
 */
class BanUsers extends Users
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
		$this->is_ban = 1;
    }
	
}
