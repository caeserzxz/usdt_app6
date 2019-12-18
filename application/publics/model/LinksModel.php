<?php

namespace app\publics\model;
use app\BaseModel;

//*------------------------------------------------------ */
//-- 商城link调用
/*------------------------------------------------------ */
class LinksModel extends BaseModel
{
	/*------------------------------------------------------ */
	//-- 链接列表
	/*------------------------------------------------------ */
	public function links(){
		$links =  [
            [
                'name' => '首页',
                'url' => config('config.host_path')
            ],[
                'name' => '用户中心',
                'url' => '/member/center/index'
            ],[
                'name' => '所有商品',
                'url' =>'/shop/goods/index'
            ],[
                'name' => '购物车',
                'url' =>'/shop/flow/cart'
            ],[
                'name' => '商品分类',
                'url' =>'/shop/index/allsort'
            ],[
                'name' => '我的订单',
                'url' =>'/shop/order/index'
            ],[
                'name' => '我的评价',
                'url' =>'/shop/comment/index'
            ],[
                'name' => '地址管理',
                'url' =>'/member/center/address'
            ],[
                'name' => '我的信息',
                'url' =>'/member/center/userinfo'
            ],[
                'name' => '我的粉丝',
                'url' =>'/member/my_team/index'
            ],[
                'name' => '我的收藏',
                'url' =>'/shop/collect/index'
            ],[
                'name' => '我的二维码',
                'url' =>'/member/center/mycode'
            ],[
                'name' => '我的优惠券',
                'url' =>'/shop/bonus/index'
            ],[
                'name' => '领劵中心',
                'url' =>'/shop/bonus/bonuscenter'
            ],[
                'name' => '我的钱包',
                'url' =>'/member/wallet/index'
            ],[
                'name' => '积分商品',
                'url' =>'/integral/goods/index'
            ],[
                'name' => '身份商品',
                'url' =>'/distribution/role_goods/index'
            ],[
                'name' => '每日签到',
                'url' =>'/member/user_sign/index'
            ],[
                'name' => '领券中心',
                'url' =>'/shop/bonus/bonuscenter'
            ]
        ];
        //判断拼团模块是否存在
        if (class_exists('app\fightgroup\model\FightGroupModel')) {
            $links[] = array
            (
                'name' => '拼团活动',
                'url' =>'/fightgroup/index/index'
            );
            $links[] = array
            (
                'name' => '我的拼团',
                'url' =>'/fightgroup/order/index'
            );
        }
        //判断秒杀模块是否存在
        if (class_exists('app\second\model\SecondModel')) {
            $links[] = array
            (
                'name' => '秒杀活动',
                'url' =>'/second/index/index'
            );
        }
        //判断限时优惠模块是否存在
        if (class_exists('app\favour\model\FavourModel')) {
            $links[] = array
            (
                'name' => '限时优惠',
                'url' =>'/favour/index/index'
            );
        }
        return $links;
	}
}
