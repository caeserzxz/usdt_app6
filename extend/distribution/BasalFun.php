<?php
namespace distribution;
/*------------------------------------------------------ */
//-- 基础升级方案
//-- @author iqgmy
/*------------------------------------------------------ */
$i = (isset($modules)) ? count($modules) : 0;
$modules[$i]["name"] = "升级方案";
$modules[$i]["explain"] = "基础升级方案，须完全满足以下条件才能升级，为0则不限制.";
$modules[$i]["val"][] = ['name'=>'referral_vip','text'=>'推荐VIP会员','input'=>'text','rule'=>'integer','tip'=>'直推VIP会员数量达指定个数','value'=>0];
$modules[$i]["val"][] = ['name'=>'referral_partner','text'=>'推荐合伙人','input'=>'text','rule'=>'integer','tip'=>'直推合伙人数量达指定个数','value'=>0];
$modules[$i]["val"][] = ['name'=>'referral_gold','text'=>'推荐金牌合伙人','input'=>'text','rule'=>'integer','tip'=>'直推金牌合伙人数量达指定个数','value'=>0];
$modules[$i]["val"][] = ['name'=>'buy_goods','text'=>'指定商品','input'=>'sel_goods'];


class BasalFun{	
	/*------------------------------------------------------ */
	//-- 执行级别更新
	/*------------------------------------------------------ */ 
	public function evalUp(&$orderInfo,$role){	
		$UsersModel = new \app\member\model\UsersModel();
		
		
	}
}
?> 