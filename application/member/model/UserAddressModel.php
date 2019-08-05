<?php

namespace app\member\model;

use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 会员收货地址
/*------------------------------------------------------ */

class UserAddressModel extends BaseModel
{
    protected $table = 'users_address';
    public $pk = 'address_id';
    protected $mkey = 'users_address_mkey_';
    /*------------------------------------------------------ */
    //-- 清除缓存
    /*------------------------------------------------------ */
    public function cleanMemcache($uid = 0)
    {
        if ($uid < 1){
            $uid = $this->userInfo['user_id'];
            if ($uid < 1) return [];
        }
        Cache::rm($this->mkey . $uid);
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    /*------------------------------------------------------ */
    public function getRows($uid = 0,$address_id = 0)
    {
        if ($uid < 1){
            $uid = $this->userInfo['user_id'];
            if ($uid < 1) return [];
        }
        
        $list = Cache::get($this->mkey . $uid);
        if (empty($list) == false) return $list;
        $rows = $this->field('*, CASE WHEN address_id='.$address_id.' THEN 0 ELSE 1 END FLAG')->where('user_id',$uid)->order('FLAG ASC,is_default DESC,address_id ASC')->select()->toArray();

        foreach ($rows as $key=>$row){
            $row['key'] = $key;
            $row['_merger_name'] = str_replace(',',' ',$row['merger_name']);
            $list[$row['address_id']] = $row;
        }
        Cache::set($this->mkey, $list, 300);
        return $list;
    }


}
