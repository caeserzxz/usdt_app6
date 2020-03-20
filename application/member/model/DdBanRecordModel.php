<?php
namespace app\member\model;
use app\BaseModel;
/*------------------------------------------------------ */
//-- 封禁记录表
//-- Author: iqgmy
/*------------------------------------------------------ */
class DdBanRecordModel extends BaseModel
{
    protected $table = 'dd_ban_record';
    public  $pk = 'id';

    /*------------------------------------------------------ */
    //-- 封号处理
    /*------------------------------------------------------ */
    public function ban_user($user_id,$desc='',$order_id=''){
        $setting = settings();
        $ban_count = $this->where('user_id',$user_id)->count();
        if($ban_count>=2){
            #永久封号
            $ban['forever_ban'] = 1;
        }else{
            if($ban_count==0){
                #第一次封号
                $ban['ban_day'] = $setting['one_no_deal'];
            }else if($ban_count==1){
                #第二次封号
                $ban['ban_day'] = $setting['two_no_deal'];
            }

        }
        $ban['user_id'] = $user_id;
        $ban['ban_time'] = time();
        $ban['ban_status'] = 0;
        $ban['ban_reason'] = $desc;
        $ban['order_id'] = $order_id;

        $res = $this->insert($ban);
        return $res;
    }
}
