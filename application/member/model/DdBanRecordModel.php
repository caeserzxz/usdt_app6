<?php
namespace app\member\model;
use app\BaseModel;
use app\member\model\UsersModel;
use app\member\model\AccountLogModel;
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
        $UsersModel = new UsersModel();
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
        if($res){
            #扣除用户信用积分
            $UsersModel = new UsersModel();
            $user_info = $UsersModel->info($user_id);
            $integral = 0;
            if($user_info['account']['use_integral']<$setting['deduction_use_integral']){
                $integral = $user_info['account']['use_integral'];
            }else{
                $integral = $setting['deduction_use_integral'];
            }
            $accountModel = new AccountLogModel();
            $charge['use_integral']   = -$integral;
            $charge['change_desc'] = '封号,扣除信用积分';
            $charge['change_type'] = 11;
            $res1 =$accountModel->change($charge, $user_id, false);

            #更新用户表状态
            $save_user['is_ban'] = 1;
            $UsersModel->where('user_id',$user_id)->update($save_user);
        }
        return $res;
    }
}
