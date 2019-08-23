<?php

namespace app\mainadmin\model;

use app\BaseModel;
use app\mainadmin\model\user;

//*------------------------------------------------------ */
//-- 文章
/*------------------------------------------------------ */
class UserMessageModel extends BaseModel
{
	protected $table = 'main_user_message';
	public  $pk = 'rec_id';

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
        $rows = $this->where('user_id',$uid)->order('is_default DESC,address_id ASC')->select()->toArray();
        foreach ($rows as $key=>$row){
            $row['key'] = $key;
            $row['_merger_name'] = str_replace(',',' ',$row['merger_name']);
            $list[$row['address_id']] = $row;
        }
        Cache::set($this->mkey, $list, 300);
        return $list;
    }
}
