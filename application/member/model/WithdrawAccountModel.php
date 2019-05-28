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
	public $pk = 'account_id';
    protected $mkey = 'users_withdraw_account_mkey_';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($uid = 0)
    {
        if ($uid < 1){
            $uid = $this->userInfo['user_id'];
            if ($uid < 1) return true;
        }
        Cache::rm($this->mkey . $uid.'_0');
		Cache::rm($this->mkey . $uid.'_1');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getRows($uid = 0,$is_del = 0)
    {
        if ($uid < 1){
            $uid = $this->userInfo['user_id'];
            if ($uid < 1) return [];
        }
		$mkey = $this->mkey . $uid.'_'.$is_del;
        $list = Cache::get($mkey);
        if (empty($list) == false) return $list;
		$where[] = ['user_id','=',$uid];
		$where[] = ['is_del','=',$is_del];
        $list = $this->where($where)->order('account_id DESC')->select()->toArray();
        Cache::set($mkey, $list, 300);
        return $list;
    }
}
