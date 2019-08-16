<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\BrandModel;
use app\shop\model\CategoryModel;
use app\mainadmin\model\ArticleModel;
/**
 * 商品品牌
 * Class Index
 * @package app\store\controller
 */
class Brand extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new BrandModel(); 
    }
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
		$rows = $this->Model->order('sort_order ASC')->select()->toArray();
		
		$this->assign('list',$rows);		
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 信息页调用
    //-- $data array 自动读取对应的数据
    /*------------------------------------------------------ */
    public function asInfo($data){
        $CategoryModel = new CategoryModel();
        $this->assign('cidOpt', arrToSel($CategoryModel->getRows(),$data['cid']));
        return $data;
    }
   
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		if (empty($data['name']) ) return $this->error('品牌名称不能为空.');
		$map['name'] = $data['name'];
		$count = $this->Model->where($map)->count();
		if ($count > 0) return $this->error('已存在相同的品牌名称，不允许重复添加.');		
		$map['add_time'] = time();	
		return $data;
	}

	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		if (empty($data['name']) ) return $this->error('品牌名称不能为空.');
		$map['id'] = $data['id'];
		//验证数据是否出现变化
		$dbarr = $this->Model->field(join(',',array_keys($data)))->where($map)->find()->toArray();		
		$this->checkUpData($dbarr,$data);
		$where[] = ['id','<>',$map['id']];
		$where[] = ['name','=',$data['name']];
		$count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的品牌名称，不允许重复添加！');
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 删除分类
	/*------------------------------------------------------ */
	public function del(){
		$map['id'] = input('id',0,'intval');
		if ($map['id']<1) return $this->error('传递参数失败！'); 
		$list = $this->Model->getRows();
		$catinfo = $list[$map['id']];
		if (empty($catinfo))  return $this->error('分类不存在！');
		$map_b['cid'] = array('in',$catinfo['children']);
		// $ArticleModel = new ArticleModel();
		// $tcount = $ArticleModel->where($map_b)->count('id');
		// if ($tcount > 0 ) return $this->error('分类下存在文章不允许删除！'); 
		$map['id'] = array('in',$catinfo['children']);
		$res = $this->Model->where($map)->delete();
		if ($res < 1)  return $this->error(); 
		$this->Model->cleanMemcache('class');
		return $this->success('操作成功.');
	}
	/*------------------------------------------------------ */
	//-- 修改分类排序与关联
	/*------------------------------------------------------ */
	public function saveSort(){
		$data = input('data','[]','trim');
		$data = json_decode(stripslashes($data),true);
		
		foreach ($data as $keya=>$rowa){	
			$uparr['sort_order'] = $keya;		
			$uparr['update_time'] = time();
			$res = $this->Model->where(['id'=>$rowa['id']])->update($uparr);
			
		}
		$this->Model->cleanMemcache();
		return $this->success('操作成功.');
	}
}
