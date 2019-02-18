<?php
namespace distribution\upLevel;
/*------------------------------------------------------ */
//-- 基础升级方案
//-- @author iqgmy
/*------------------------------------------------------ */
$i = (isset($modules)) ? count($modules) : 0;
$modules[$i]["name"] = "基础升级方案";
$modules[$i]["explain"] = "基础升级方案，升级根据用户购买金额或指定商品，即可升级.";
$modules[$i]["val"][] = ['name'=>'buy_money','text'=>'指定金额','input'=>'text','rule'=>'ismoney','tip'=>'单次购买达到此金额升级'];
$modules[$i]["val"][] = ['name'=>'buy_money','text'=>'总购金额','input'=>'text','rule'=>'ismoney','tip'=>'用户历史购买达到此金额升级'];
$modules[$i]["val"][] = ['name'=>'buy_goods','text'=>'指定商品','input'=>'sel_goods'];
$modules[$i]["condition"] = ['all'=>'满足所有条件','one'=>'满足任一条件','buyone'=>'购买任意商品'];//达成条件
class BasalFun{	
	
}
?> 