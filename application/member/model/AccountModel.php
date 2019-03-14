<?php
namespace app\member\model;
use app\BaseModel;
/*------------------------------------------------------ */
//-- 会员帐户表
//-- Author: iqgmy
/*------------------------------------------------------ */
class AccountModel extends BaseModel
{
	protected $table = 'users_account';
	public  $pk = 'user_id';

	
	/*------------------------------------------------------ */
	//-- 处理更新的内容
	/*------------------------------------------------------ */ 
	public function returnUpData(&$data){	
	    $updata = array();	
		if (isset($data['total_dividend'])){
			$updata['total_dividend'] = ['INC',$data['total_dividend']];			
		}
		if (isset($data['total_integral'])){
			$updata['total_integral'] = ['INC',$data['total_integral']];	
		}
		if (isset($data['balance_money'])){
			$updata['balance_money'] = ['INC',$data['balance_money']];
		}
		if (isset($data['use_integral'])){
			$updata['use_integral'] = ['INC',$data['use_integral']];
		}
		if (isset($data['bean_value'])){
			$updata['bean_value'] = ['INC',$data['bean_value']];
		}
    	return $updata;
	}
}
