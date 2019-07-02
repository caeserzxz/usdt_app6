<?php
namespace app\publics\controller\sys_admin;
use app\AdminController;
use app\shop\model\GoodsModel;
use app\mainadmin\model\ArticleCategoryModel;
/**
 * 设置
 * Class Index
 * @package app\store\controller
 */
class Links extends AdminController
{
    /*------------------------------------------------------ */
    //-- link
    /*------------------------------------------------------ */
    public function index(){
        $result['status']= 0;
        $result['data'] =Array(
            0 =>array
            (
                'id' => 1,
                'name' => '首页',
                'url' => config('config.host_path')
            ),
            1 =>array
            (
                'id' => 2,
                'name' => '用户中心',
                'url' => '/member/center/index'
            ),
            2 =>array
            (
                'id' => 3,
                'name' => '所有商品',
                'url' =>'/shop/goods/index'
            ),
            3 =>array
            (
                'id' => 4,
                'name' => '购物车',
                'url' =>'/shop/flow/cart'
            ),
            4 =>array
            (
                'id' => 5,
                'name' => '商品分类',
                'url' =>'/shop/index/allsort'
            ),
            5 =>array
            (
                'id' => 6,
                'name' => '我的订单',
                'url' =>'/shop/order/index'
            ),
            7 =>array
            (
                'id' => 8,
                'name' => '地址管理',
                'url' =>'/member/center/address'
            ),
            8 =>array
            (
                'id' => 9,
                'name' => '我的信息',
                'url' =>'/member/center/userinfo'
            ),
            9 =>array
            (
                'id' => 10,
                'name' => '我的粉丝',
                'url' =>'/member/my_team/index'
            ),
            10 =>array
            (
                'id' => 11,
                'name' => '我的钱包',
                'url' =>'/member/wallet/index'
            ),
            11 =>array
            (
                'id' => 11,
                'name' => '积分商品',
                'url' =>'/integral/goods/index'
            ),
            12 =>array
            (
                'id' => 12,
                'name' => '身份商品',
                'url' =>'/distribution/role_goods/index'
            )

        );

        //判断拼团模块是否存在
        if (class_exists('app\fightgroup\model\FightGroupModel')) {
            $result['data'][] = array
            (
                'id' => 90,
                'name' => '拼团活动',
                'url' =>'/fightgroup/index/index'
            );
        }
        //判断秒杀模块是否存在
        if (class_exists('app\second\model\SecondModel')) {
            $result['data'][] = array
            (
                'id' => 91,
                'name' => '秒杀活动',
                'url' =>'/second/index/index'
            );
        }
        $this->assign("_menu_index", input('_menu_index','','trim'));
        $this->assign("searchType", input('searchType','','trim'));
        $this->assign('links', $result['data']);
        $GoodsModel = new GoodsModel();
        $classList = $GoodsModel->getClassList();
        $this->assign('classList',$classList);
        $ArticleCategoryModel = new ArticleCategoryModel();
        $this->assign("ArticleCatOpt", arrToSel($ArticleCategoryModel->getRows()));
        return $this->fetch();
    }


}
