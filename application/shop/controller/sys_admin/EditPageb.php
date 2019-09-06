<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;

use app\publics\model\LinksModel;

use app\shop\model\ShopPageTheme;
/*------------------------------------------------------ */
//-- 商城装修
/*------------------------------------------------------ */
class EditPageb extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new ShopPageTheme();
    }
    /*------------------------------------------------------ */
    //-- 主页编辑
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign('title', '魔幻装修');
        $this->getList(true);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $data = $this->getPageList($this->Model);
        $this->assign("data", $data);
        if ($runData == false) {
            $data['content'] = $this->fetch('list');
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 添加/修改页面
    /*------------------------------------------------------ */
    public function info()
    {
        $this->assign('title', '添加魔幻装修');
        $id = input('id',0,'intval');
        $this->assign('id', $id);
        return $this->fetch('info');
    }
    /*------------------------------------------------------ */
    //-- 编辑
    /*------------------------------------------------------ */
    public function edit()
    {
        $id = input('id',0,'intval');
        if ($id > 0){
            $info = $this->Model->find($id);
            $data = empty($info['page'])?'null':$info['page'];
            $name = $info['theme_name'];
        }else{
            $data = 'null';
            $name = '商城';
        }
        $this->assign('id', $id);
        $this->assign('name', $name);
        $this->assign('data', $data);
        $this->assign('title', '魔幻装修');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 保存页面
    /*------------------------------------------------------ */
    public function save()
    {
        $id = input('id',0,'intval');
        $data = input('data');
        if (empty($data['items'])){
            $result['status'] = 0;
            $result['result']['message'] = '请设置排版内容后再操作.';
            return $this->ajaxReturn($result);
        }
        $tmpData['is_new'] = 1;
        $tmpData['theme_name'] = $data['page']['name'];
        $tmpData['theme_type'] = 'index';
        $tmpData['page'] = json_encode($data,JSON_UNESCAPED_UNICODE);
        if ($id < 1){
            $tmpData['add_time'] = time();
            $tmpData['update_time'] = time();
            $res = $this->Model->save($tmpData);
            $id = $res;
        }else{
            $tmpData['update_time'] = time();
            $res = $this->Model->where('st_id',$id)->update($tmpData);
        }
        if ($res < 1){
            $result['status'] = 0;
            $result['result']['message'] = '操作失败，请重试.';
            return $this->ajaxReturn($result);
        }
        $result['status'] = 1;
        $result['result']['id'] = $id;
        $result['result']['jump'] = url('edit',['id'=>$id]);
        return $this->ajaxReturn($result);
    }

    /*------------------------------------------------------ */
    //-- 预览
    /*------------------------------------------------------ */
    public function preview()
    {
        $id = input('id',0,'intval');
        $this->assign('id', $id);
        return $this->fetch();
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
