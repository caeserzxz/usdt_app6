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
                'url' => _url('member/center/index','',false,true)
            ),
            2 =>array
            (
                'id' => 3,
                'name' => '所有商品',
                'url' => _url('shop/goods/index','',false,true)
            ),
            3 =>array
            (
                'id' => 4,
                'name' => '购物车',
                'url' => _url('shop/flow/cart','',false,true)
            ),
            4 =>array
            (
                'id' => 5,
                'name' => '商品分类',
                'url' => _url('shop/index/allsort','',false,true)
            ),
            5 =>array
            (
                'id' => 6,
                'name' => '我的订单',
                'url' => _url('mobile/Order/order_list','',false,true)
            ),
            7 =>array
            (
                'id' => 8,
                'name' => '地址管理',
                'url' => _url('mobile/User/address_list','',false,true)
            ),
            8 =>array
            (
                'id' => 9,
                'name' => '我的信息',
                'url' => _url('member/center/userinfo','',false,true)
            ),
            9 =>array
            (
                'id' => 10,
                'name' => '我的粉丝',
                'url' => _url('member/my_team/index','',false,true)
            ),
            10 =>array
            (
                'id' => 11,
                'name' => '我的钱包',
                'url' => _url('member/wallet/index','',false,true)
            ),
            11 =>array
            (
                'id' => 11,
                'name' => '积分商品',
                'url' => _url('integral/goods/index','',false,true)
            ),
            12 =>array
            (
                'id' => 12,
                'name' => '身份商品',
                'url' => _url('distribution/role_goods/index','',false,true)
            )

        );

        //判断拼团模块是否存在
        if (class_exists('app\fightgroup\model\FightGroupModel')) {
            $result['data'][] = array
            (
                'id' => 90,
                'name' => '拼团活动',
                'url' => _url('fightgroup/index/index','',false,true)
            );
        }
        //判断秒杀模块是否存在
        if (class_exists('app\second\model\SecondModel')) {
            $result['data'][] = array
            (
                'id' => 91,
                'name' => '秒杀活动',
                'url' => _url('second/index/index','',false,true)
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
