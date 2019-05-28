<?php
namespace distribution\sdtydw;
use app\member\model\UsersModel;
/*------------------------------------------------------ */
//-- 基础升级方案
//-- @author iqgmy
/*------------------------------------------------------ */
$i = (isset($modules)) ? count($modules) : 0;
$modules[$i]["name"] = "升级方案";
$modules[$i]["explain"] = "基础升级方案，购买身份商品或购买普通商品累计达指定金额.";
//$modules[$i]["val"][] = ['name'=>'referral','text'=>'直推','input'=>'sel_role','tip'=>'个'];
$modules[$i]["val"][] = ['name'=>'is_auto','text'=>'升级方式','input'=>'radio','selval'=>[1=>'满足条件升级',9=>'手动调整']];
$modules[$i]["val"][] = ['name'=>'total_consume','text'=>'累计消费','input'=>'text','tip'=>'元'];
//$modules[$i]["val"][] = ['name'=>'buy_type','text'=>'指定类型','input'=>'radio','selval'=>[1=>'购买以下任意指定商品',2=>'购买以下全部指定商品']];
//$modules[$i]["val"][] = ['name'=>'buy_goods','text'=>'指定商品','input'=>'sel_goods'];


class BasalFunLevel{
	/*------------------------------------------------------ */
	//-- 执行级别更新
	// $user_id int 会员ID
	// $role array 当前准备提升的分销身份
	// $orderInfo array 订单信息
	// $goodsList array 订单商品
	/*------------------------------------------------------ */ 
	public function judgeIsUp($user_id,&$role,&$orderInfo,&$goodsList){
        static $UsersModel;
        if (!isset($UsersModel)){
            $UsersModel = new UsersModel();
        }
        if ($orderInfo['user_id'] != $user_id) return false;
        $upLeveValue = $role['upleve_value'];
		if ($upLeveValue['is_auto'] == 9) return false;
        //购买指定身份商品
        if ($orderInfo['d_type'] == 'role_order'){
            if ($role['role_id'] == $orderInfo['role_id']){
                return true;
            }
        }elseif ($upLeveValue['total_consume'] > 0) { //判断普通订单累计消费金额
            $userInfo = $UsersModel->info($user_id);
            if ($userInfo['total_consume'] + $orderInfo['order_amount'] >= $upLeveValue['total_consume']) {
                return true;
            }
        }
		//购买会员执行end
        return false;
	}
}
?> 