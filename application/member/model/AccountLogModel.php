<?php
namespace app\member\model;
use app\BaseModel;
use app\member\model\UsersModel;
use app\member\model\AccountModel;

use think\Db;
/*------------------------------------------------------ */
//-- 会员帐户变动明细
//-- Author: iqgmy
/*------------------------------------------------------ */
class AccountLogModel extends BaseModel
{
	protected $table = 'users_account_log';
	public  $pk = 'log_id';
    /*------------------------------------------------------ */
	//--  生成校验码
	/*------------------------------------------------------ */
	protected  function toKey($data){
        return md5('#r8%'.join(',',$data));
    }
	/*------------------------------------------------------ */
	//--  验证校验码
	/*------------------------------------------------------ */
	public  function checkKey($data,$md5_key){
        return ($this->toKey($data) == $md5_key);
    }
	/*------------------------------------------------------ */
	//-- 创建帐号数据
	/*------------------------------------------------------ */ 
	public function createData($data = array()){	
		return (new AccountModel)->save($data);	
	}
	/*------------------------------------------------------ */
	//-- 记录明细,更新用户帐户，更新用户信息
    //-- $data array 更新的字段数据
    //-- $user_id int 会员ID
    //-- $isTrans 是否使用事务回调，默认为开启，一般不开启即外部开启了
	/*------------------------------------------------------ */ 
	public function change($data,$user_id =0,$isTrans = true){
	    if ($user_id < 1) return false;
	    $UsersModel = new UsersModel();
        $account = $UsersModel->getAccount($user_id,false);
        if ($isTrans == true){
            Db::startTrans();
        }
		$data['user_id'] = $user_id;
		$data['change_time'] = time();	
		$data['change_ip'] = request()->ip();			
		$data['old_total_dividend'] = $account['total_dividend'];	
		$data['old_total_integral'] = $account['total_integral'];	
		$data['old_balance_money'] = $account['balance_money'];	
		$data['old_use_integral'] = $account['use_integral'];	
		$data['old_bean_value'] = $account['bean_value'];	
		$data['sign'] = $this->toKey($data);
		$res = $this->create($data);
		if ($res < 1){
            if ($isTrans == true){// 回滚事务
                Db::rollback();
            }
			return false;	
		}		
	    $AccountModel = new AccountModel();
		$upData = $AccountModel->returnUpData($data);
		if (empty($upData)){
            if ($isTrans == true){// 回滚事务
                Db::rollback();
            }
			return false;
		}
		$upData['update_time'] = $data['change_time'];
		$res = $AccountModel->where('user_id',$data['user_id'])->update($upData);
		if ($res < 1){
            if ($isTrans == true){// 回滚事务
                Db::rollback();
            }
			return false;
		}
        if ($isTrans == true){// 提交事务
            Db::commit();
        }
        $UsersModel->cleanMemcache($data['user_id']);
		return true;
		
	}
}
