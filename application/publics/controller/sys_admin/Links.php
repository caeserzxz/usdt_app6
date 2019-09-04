<?php
namespace app\publics\controller\sys_admin;
use app\AdminController;
use app\shop\model\GoodsModel;
use app\mainadmin\model\ArticleCategoryModel;
use app\publics\model\LinksModel;
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
        $result['data'] = (new LinksModel)->links();


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
