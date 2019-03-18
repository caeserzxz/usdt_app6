<?php
namespace app\mainadmin\controller;
use app\AdminController;
use app\mainadmin\model\ArticleModel;
use app\mainadmin\model\ArticleCategoryModel;
/**
 * 文章管理
 * Class Index
 * @package app\store\controller
 */
class Article extends AdminController
{
	public $_field = '';
	public $_pagesize = '';
	
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
		$this->Model = new ArticleModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
 	public function index()
    {
		$cid = input('id',0,'intval');
		$this->getList(true);
		$this->assign("cgOpt", arrToSel($this->cg_list,$cid));
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $runJson = input('runJson',0,'intval');
		$ArticleCategoryModel = new ArticleCategoryModel();        
		$where = [];
		$this->cg_list = $ArticleCategoryModel->getRows();
		$search['cid'] = input('cid',0,'intval');
		if ($search['cid'] > 0 ){
			 $where[] = ['cid','in',$this->cg_list[$search['cid']]['children']];
		}
		$search['keyword'] =  input('keyword','','trim');
		if (empty($search['keyword']) == false) $where[] = ['title','like','%'.$search['keyword'].'%'];	
		
        $this->data = $this->getPageList($this->Model, $where,$this->_field,$this->_pagesize);			
		$this->assign("data", $this->data);
		$this->assign("search", $search);
		$this->assign("cg_list", $this->cg_list);
        if ($runJson == 1){
            return $this->success('','',$this->data);
        }elseif ($runData == false){
            $this->data['content'] = $this->fetch('list');
			unset($this->data['list']);
			return $this->success('','', $this->data);
		}
        return true;
    }

    /*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){
		$ArticleCategoryModel = new ArticleCategoryModel(); 
		$catList = $ArticleCategoryModel->getRows();
		$this->assign("catList", $catList);
		$this->assign("catOpt", arrToSel($catList,$data['cid']));
		if (empty($data['add_time'])) $data['add_time'] = time();
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前调用
	/*------------------------------------------------------ */
	public function beforeAdd($row){
		$row['add_time'] = $row['add_time'] ? strtotime($row['add_time']) : time();	
		$row['update_time'] = time();
		return $row;
	}
	//-- 添加后调用
	/*------------------------------------------------------ */
	public function afterAdd($row){		
		return $this->success('添加成功.',url('index'));
	}
	/*------------------------------------------------------ */
	//-- 修改前调用
	/*------------------------------------------------------ */
	public function beforeEdit($row){		
		return $this->beforeAdd($row);
	}
	/*------------------------------------------------------ */
	//-- 修改后调用
	/*------------------------------------------------------ */
	public function afterEdit($row){		
		return $this->success('修改成功.');
	}
	/*------------------------------------------------------ */
	//-- 删除文章
	/*------------------------------------------------------ */
	public function del(){
		$map['id'] = input('id',0,'intval');
		if ($map['id']<1) return $this->error('传递参数失败！'); 
		$res = $this->Model->where($map)->delete();
		if ($res < 1)  return $this->error(); 
		return $this->success('删除成功.');
	}
	/*------------------------------------------------------ */
	//-- 搜索文章
	/*------------------------------------------------------ */
	public function searchBox(){
		$this->_pagesize = 10;
		$this->_field = 'id,title';
		$this->getList(true);
		$result['data'] = $this->data;
		if ($this->request->isPost()) return $this->ajaxReturn($result);
		$this->assign("cgOpt", arrToSel($this->cg_list,input('cid',0,'intval')));
		$this->assign("_menu_index", input('_menu_index','','trim'));
		$this->assign("searchType", input('searchType','','trim'));
		return response($this->fetch());
	}
}
