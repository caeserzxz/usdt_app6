<?php

namespace app\mainadmin\controller;

use app\AdminController;
use app\mainadmin\model\ArticleModel;
use app\mainadmin\model\ArticleCategoryModel;
use app\shop\model\GoodsModel;

/**
 * 文章管理
 * Class Index
 * @package app\store\controller
 */
class Article extends AdminController
{
    public $_field = '';
    public $_pagesize = '';
    public $type = 0;//文章类型

    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new ArticleModel();
        $this->assign('type', $this->type);//文章类型：0-普通，1-新闻头条
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
        $cid = input('id', 0, 'intval');
        $this->getList(true);
        $this->assign("cgOpt", arrToSel($this->cg_list, $cid));
        return $this->fetch('mainadmin@article/index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        $runJson = input('runJson', 0, 'intval');
        $ArticleCategoryModel = new ArticleCategoryModel();
        $where = [];
        $where[]=['type','=',$this->type];
        $this->cg_list = $ArticleCategoryModel->getRows();
        $search['cid'] = input('cid', 0, 'intval');
        if ($search['cid'] > 0) {
            $where[] = ['cid', 'in', $this->cg_list[$search['cid']]['children']];
        }
        $search['keyword'] = input('keyword', '', 'trim');
        if (empty($search['keyword']) == false) $where[] = ['title', 'like', '%' . $search['keyword'] . '%'];

        $this->data = $this->getPageList($this->Model, $where, $this->_field, $this->_pagesize);
        $this->assign("data", $this->data);
        $this->assign("search", $search);
        $this->assign("cg_list", $this->cg_list);
        if ($runJson == 1) {
            return $this->success('', '', $this->data);
        } elseif ($runData == false) {
            $this->data['content'] = $this->fetch('list')->getContent();
            unset($this->data['list']);
            return $this->success('', '', $this->data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //--新闻头条列表
    /*------------------------------------------------------ */
    public function headline()
    {
        $cid = input('id', 0, 'intval');
        $this->getList(true);
        $this->assign("cgOpt", arrToSel($this->cg_list, $cid));
        return $this->fetch('mainadmin@article/index');
    }

    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data)
    {
        $ArticleCategoryModel = new ArticleCategoryModel();
        $catList = $ArticleCategoryModel->getRows();
        $this->assign("catList", $catList);
        $this->assign("catOpt", arrToSel($catList, $data['cid']));

        $ArticleBindType = $this->getDict('ArticleBindType');//指定绑定类型
        $this->assign("ArticleBindType", arrToSel($ArticleBindType, $data['link_type']));

        $this->assign("GoodsType", $this->getDict('BonusGoodsType'));//指定商品类型
        if (empty($data['add_time'])) $data['add_time'] = time();
        if($data['link_type']=='goods'){
            $data['link_data']= json_decode( $data['link_data'],JSON_UNESCAPED_UNICODE);
        }

        $GoodsModel = new GoodsModel();
        $ClassList = $GoodsModel->getClassList();//商品分类
        if($data['link_type']=='goods_cid'){
            $ClassListOpt = arrToSel($ClassList,$data['link_data']);
        }else{
            $ClassListOpt = arrToSel($ClassList);
        }
        $this->assign("classListOpt", $ClassListOpt);//商品分类下拉选择

        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加前调用
    /*------------------------------------------------------ */
    public function beforeAdd($row)
    {
        $row['link_data']='';
        if (empty($row['link_type']) == false) {
            switch ($row['link_type']){
                case 'link':
                    if(empty($row['link']))return $this->error('链接类型绑定关联未填写！');
                    $row['link_data']=$row['link'];
                    break;
                case 'tel':
                    if(empty($row['tel']))return $this->error('电话类型绑定关联未填写！');
                    $row['link_data']=$row['tel'];
                    break;
                case 'goods':
                    if(empty($row['goods_type']))return $this->error('商品类型绑定关联，未选择指定商品类型！');
                    $goods=[];
                    $goods['goods_type']=$row['goods_type'];
                    $goods['goods_ids']=$row['goods_id'];
                    $row['link_data'] = json_encode($goods,JSON_UNESCAPED_UNICODE);
                    break;
                case 'goods_cate':
                    if(empty($row['goods_cid']))return $this->error('商品分类类型绑定，未选择商品分类！');
                    $row['link_data']=$row['goods_cid'];
                    break;
            }
        }
        $row['add_time'] = $row['add_time'] ? strtotime($row['add_time']) : time();
        $row['update_time'] = time();
        $row['type'] = $this->type;//文章类型
        return $row;
    }
    //-- 添加后调用
    /*------------------------------------------------------ */
    public function afterAdd($row)
    {
        return $this->success('添加成功.', url('index'));
    }
    /*------------------------------------------------------ */
    //-- 修改前调用
    /*------------------------------------------------------ */
    public function beforeEdit($row)
    {
        return $this->beforeAdd($row);
    }
    /*------------------------------------------------------ */
    //-- 修改后调用
    /*------------------------------------------------------ */
    public function afterEdit($row)
    {
        $this->Model->cleanMemcache($row['id']);
        return $this->success('修改成功.');
    }
    /*------------------------------------------------------ */
    //-- 删除文章
    /*------------------------------------------------------ */
    public function del()
    {
        $map['id'] = input('id', 0, 'intval');
        if ($map['id'] < 1) return $this->error('传递参数失败！');
        $res = $this->Model->where($map)->delete();
        if ($res < 1) return $this->error();
        $this->Model->cleanMemcache();
        return $this->success('删除成功.');
    }
    /*------------------------------------------------------ */
    //-- 搜索文章
    /*------------------------------------------------------ */
    public function searchBox()
    {
        $this->_pagesize = 10;
        $this->_field = 'id,title';
        $this->getList(true);
        $result['data'] = $this->data;
        if ($this->request->isPost()) return $this->ajaxReturn($result);
        $this->assign("cgOpt", arrToSel($this->cg_list, input('cid', 0, 'intval')));
        $this->assign("_menu_index", input('_menu_index', '', 'trim'));
        $this->assign("searchType", input('searchType', '', 'trim'));
        return $this->fetch();
    }

    /*------------------------------------------------------ */
    //-- 获取已选择的商品/拼团/秒杀
    /*------------------------------------------------------ */
    public function getSelectedGoodsList()
    {
        $id = input('id', 0, 'intval');
        $article = $this->Model->info($id);
        if ($article['link_type'] != 'goods') {
            $result['code'] = 0;
            $result['list'] = [];
            return $this->ajaxReturn($result);
        }
        $goods_type = $article['link_data']['goods_type'];
        $goods_ids = $article['link_data']['goods_ids'];
        $goods_ids[]=0;
        $GoodsModel = new \app\shop\model\GoodsModel();
        if ($goods_type == 1) {//普通商品
            $where[] = ['goods_id', 'in', $goods_ids];
            $_list = $GoodsModel->where($where)->field("goods_id as id,goods_name,shop_price as show_price,is_spec,min_price,max_price,goods_sn,goods_thumb")->select();
        }
        if ($goods_type == 2) {//拼团商品
            $FightGroupModel = new \app\fightgroup\model\FightGroupModel();
            $where[] = ['fg.fg_id', 'in', $goods_ids];
            $_list = $FightGroupModel->alias('fg')
                ->join("shop_goods g", 'fg.goods_id=g.goods_id', 'left')
                ->where($where)->field("fg.fg_id as id,fg.show_price,g.goods_name,g.shop_price,g.is_spec,g.goods_sn,g.goods_thumb")->select();
        }
        if ($goods_type == 3) {//秒杀商品
            $SecondModel = new \app\second\model\SecondModel;
            $where[] = ['sg.sg_id', 'in', $goods_ids];
            $_list = $SecondModel->alias('sg')
                ->join("shop_goods g", 'sg.goods_id=g.goods_id', 'left')
                ->where($where)->field("sg.sg_id as id,sg.show_price,g.goods_name,g.shop_price,g.is_spec,g.goods_sn,g.goods_thumb")->select();
        }
        $result['code'] = 1;
        $result['list'] = $_list;
        return $this->ajaxReturn($result);
    }
}
