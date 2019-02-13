<?php
namespace app\member\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 提现帐户表
/*------------------------------------------------------ */
class WithdrawAccountModel extends BaseModel
{
	protected $table = 'users_withdraw_account';
	public $pk = 'id';
    protected $mkey = 'users_withdraw_account_mkey_';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($uid)
    {
        if ($uid < 1){
            $uid = $this->userInfo['user_id'];
            if ($uid < 1) return true;
        }
        Cache::rm($this->mkey . $uid);
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getRows($uid = 0)
    {
        if ($uid < 1){
            $uid = $this->userInfo['user_id'];
            if ($uid < 1) return [];
        }
        $list = Cache::get($this->mkey . $uid);
        if (empty($list) == false) return $list;
        $list = $this->where('user_id',$uid)->order('id DESC')->select()->toArray();
        Cache::set($this->mkey, $list, 300);
        return $list;
    }
}
