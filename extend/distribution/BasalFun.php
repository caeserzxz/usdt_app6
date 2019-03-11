<?php
namespace distribution;
use app\member\model\UsersModel;
/*------------------------------------------------------ */
//-- 基础升级方案
//-- @author iqgmy
/*------------------------------------------------------ */
$i = (isset($modules)) ? count($modules) : 0;
$modules[$i]["name"] = "升级方案";
$modules[$i]["explain"] = "基础升级方案，须完全满足以下条件才能升级，为0则不限制.";
$modules[$i]["val"][] = ['name'=>'referral','text'=>'直推','input'=>'sel_role','tip'=>'个'];
//$modules[$i]["val"][] = ['name'=>'xxx','text'=>'A','input'=>'text','tip'=>'个'];
$modules[$i]["val"][] = ['name'=>'buy_type','text'=>'指定类型','input'=>'radio','selval'=>[1=>'购买以下任意指定分销商品',2=>'购买以下全部指定分销商品']];
$modules[$i]["val"][] = ['name'=>'buy_goods','text'=>'指定分销商品','input'=>'sel_goods'];


class BasalFun{	
	/*------------------------------------------------------ */
	//-- 执行级别更新
	// $user_id int 会员ID
	// $role array 当前准备提升的分销身份
	// $orderInfo array 订单信息
	// $goodsList array 订单商品
	/*------------------------------------------------------ */ 
	public function judgeIsUp($user_id,&$role,&$orderInfo,&$goodsList){	
		$order_goods_ids = [];
		foreach ($goodsList as $goods){
			$order_goods_ids[] = $goods['goods_id'];
		}
		$upleveValue = $role['upleve_value'];
		//购买会员执行
		if ($orderInfo['user_id'] == $user_id){
			
			//现模式购买者只能进行购买指定商品进行升级
			if (empty($upleveValue['buy_goods'])){//找到高级身份再没有指定商品，升级处理结束
				return false;
			}
			$isOk = false;
			foreach ($upleveValue['buy_goods'] as $goods_id){
				if (in_array($goods_id,$order_goods_ids) == false){//购买商品列表未包括全部指定商品
					if ($upleveValue['buy_type'] == 2){//设定须购买全部分销商品，一个不达标，终止
						return false;
					}
				}else{
					$isOk = true;
					continue;
				}
			}
			if ($isOk == false) return false;//未满足购买限制，终止
			
			return true;
		}		
		//购买会员执行end
		
		$UsersModel = new UsersModel();
		$isOk = true;
		$allReferralNum = 0;
		foreach ($upleveValue['referral'] as $key=>$referralNum){
			if ($referralNum < 1) continue;//小于1为不限制跳过
			$allReferralNum += $referralNum;
			//查询所有下级分销身份数量
			$where[] = ['pid','=',$user_id];
			$where[] = ['role_id','=',$key];//注：现方法未统计下级比当前判断的等级的会员，正常逐级升级限制不受影响
			$count = $UsersModel->where($where)->count();
			if ($count < $referralNum){
				$isOk = false;
			}
		}
		if ($allReferralNum < 1) return false;
		return $isOk;
		
	}
}
?> 