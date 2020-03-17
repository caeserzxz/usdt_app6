<?php
namespace app\ddkc\model;
use app\BaseModel;
use think\facade\Cache;
use app\member\model\UsersModel;
//*------------------------------------------------------ */
//-- 会员等级表
    /*------------------------------------------------------ */
class PaymentModel extends BaseModel
{
    protected $table = 'dd_payment';
    public  $pk = 'id';
    protected static $mkey = 'dd_payment_list';

    //*------------------------------------------------------ */
    //-- 获取收款信息
    // $user_id  用户id
    // $type     收款类型
    // $id       收款id
    /*------------------------------------------------------ */
    public function get_payment($user_id='',$type='',$id=''){
        $where = [];
        if(empty($user_id)==false){
            $where[] = ['user_id','=',$user_id];
        }
        if(empty($type)==false){
            $where[] = ['type','=',$type];
        }
        if(empty($id)==false){
            $where[] = ['id','=',$id];
        }
        $pay_info = $this->where($where)->find();
        return $pay_info;
    }
}