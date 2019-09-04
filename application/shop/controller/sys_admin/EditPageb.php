<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use think\Db;
use think\facade\Cache;
use app\publics\model\LinksModel;


/*------------------------------------------------------ */
//-- 商城装修
/*------------------------------------------------------ */
class EditPageb extends AdminController
{
    /*------------------------------------------------------ */
    //-- 主页编辑
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign('title', '魔幻装修');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 主页编辑
    /*------------------------------------------------------ */
    public function edit()
    {
        $this->assign('title', '魔幻装修');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 保存页面
    /*------------------------------------------------------ */
    public function save()
    {
        $result['status'] = 1;
        $result['result']['id'] = 1;
        $result['result']['jump'] = url('edit',['id'=>1]);
        return $this->ajaxReturn($result);
    }
    /*------------------------------------------------------ */
    //-- 选定链接
    /*------------------------------------------------------ */
    public function links(){
        $this->assign('links', (new LinksModel)->links());
        $CategoryModel = new \app\shop\model\CategoryModel();
        $this->assign('CategoryList', $CategoryModel->getRows());
        return response($this->fetch());
    }

    /*------------------------------------------------------ */
    //-- 搜索
    /*------------------------------------------------------ */
    public function search()
    {
        $type = input('type','','trim');
        $kw = input('kw','','trim');
        if (in_array($type,['good','article']) == false){
            return $this->error('请求错误.');
        }
        if ($type == 'good'){
            $GoodsModel = new \app\shop\model\GoodsModel();
            $where[] = ['goods_name','like','%'.$kw.'%'];
            $ids = $GoodsModel->where($where)->limit(20)->column('goods_id');
            foreach ($ids as $id){
                $list[] = $GoodsModel->info($id);
            }
        }elseif($type == 'article'){
            $ArticleModel = new \app\mainadmin\model\ArticleModel();
            $where[] = ['title','like','%'.$kw.'%'];
            $list = $ArticleModel->where($where)->limit(20)->select()->toArray();
        }
        $this->assign('list',$list);
        $this->assign('kw',$kw);
        return response($this->fetch('search_'.$type));
    }

    /*------------------------------------------------------ */
    //-- 搜索 暂只用于商品模块
    /*------------------------------------------------------ */
    public function query()
    {
        $type = input('type','','trim');
        $keyword = input('keyword','','trim');
        if (in_array($type,['good','category']) == false){
            return $this->error('请求错误.');
        }
        if ($type == 'good'){
            $GoodsModel = new \app\shop\model\GoodsModel();
            $where[] = ['goods_name','like','%'.$keyword.'%'];
            $ids = $GoodsModel->where($where)->limit(20)->column('goods_id');
            foreach ($ids as $id){
                $info = $GoodsModel->info($id);
                $_info['id'] = $info['goods_id'];
                $_info['title'] = $info['goods_name'];
                $_info['thumb'] = config('config.host_path').$info['goods_thumb'];
                $_info['marketprice'] = $info['market_price'];
                $_info['productprice'] = $info['shop_price'];
                $_info['share_title'] = $info['goods_name'];
                $_info['share_icon'] = $info['goods_thumb'];
                $_info['description'] = $info['description'];
                $_info['minprice'] = $info['min_price'];
                $_info['costprice'] = $info['shop_price'];
                $_info['total'] = $info['goods_number'];
                $_info['sales'] = $info['sale_num'];
                $_info['islive'] = "0";
                $_info['liveprice'] = "0.00";
                $list[] = $_info;
            }
        }elseif($type == 'category'){
            $CategoryModel = new \app\shop\model\CategoryModel();
            $rows = $CategoryModel->getRows();
            foreach ($rows as $row){
                $_info['id'] = $row['id'];
                $_info['name'] = $row['name'];
                $_info['level'] = $row['level'];
                $_info['parentid'] = $row['pid'];
                $_info['displayorder'] = $row['sort_order'];
                $_info['enabled'] = $row['status'];
                $_info['ishome'] = $row['is_index'];
                $_info['advurl'] = '';
                $_info['isrecommand'] = "0";
                $_info['description'] = "";

                if (empty($row['pic'])){
                    $_info['thumb'] = config('config.host_path').'/static/main/img/def_img.jpg';
                }else{
                    $_info['thumb'] = config('config.host_path').$row['pic'];
                }
                if (empty($row['cover'])){
                    $_info['advimg'] = '';
                }else{
                    $_info['advimg'] = config('config.host_path').$row['cover'];
                }
                $list[] = $_info;
            }
        }
        $this->assign('list',$list);
        $this->assign('keyword',$keyword);
        return response($this->fetch('query_'.$type));
    }

}
