<?php
namespace app\mainadmin\controller;
use app\AdminController;
use app\mainadmin\model\ArticleCategoryModel;
use app\mainadmin\model\ArticleModel;

/**
 * 文章分类
 * Class Index
 * @package app\store\controller
 */
class ArticleCategory extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new ArticleCategoryModel(); 
    }
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
		$rows = $this->Model->order('sort_order,pid ASC')->select()->toArray();
		$list = returnRecArr($rows);
		if (empty($list)) $list[0] = array();
		$this->assign('list',$list);		
        return $this->fetch('index');
    }

   /*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){
		$cgList = $this->Model->getRows();		
		if (empty($data['id'])){
			$data['pid'] = input('pid',0,'intval');
		}else{
			 unset($cgList[$data['id']]);	
			 $data['pid'] = $data['pid'];
		}
		$this->assign('select', arrToSel($cgList,$data['pid'],false,2));	
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		if (empty($data['name']) ) return $this->error('分类名称不能为空.');
        $where[] = ['pid','=',$data['pid']];
        $where[] = ['name','=',$data['name']];
        $count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的分类名称，不允许重复添加.');		
		$map['add_time'] = time();	
		return $data;
	}

	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		if (empty($data['name']) ) return $this->error('分类名称不能为空.');

		//验证数据是否出现变化
		$dbarr = $this->Model->field(join(',',array_keys($data)))->where('id',$data['id'])->find()->toArray();
		$this->checkUpData($dbarr,$data);
		$where[] = ['id','<>',$data['id']];
		$where[] = ['name','=',$data['name']];
		$where[] = ['pid','=',$data['pid']];
		$count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的分类名称，不允许重复添加！');
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
		$ArticleModel = new ArticleModel();
		$tcount = $ArticleModel->where($map_b)->count('id');
		if ($tcount > 0 ) return $this->error('分类下存在文章不允许删除！'); 
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
			$uparr['pid'] = 0;
			$uparr['update_time'] = time();
			$res = $this->Model->where(['id'=>$rowa['id']])->update($uparr);
			if (empty($rowa['children'])) continue;
			foreach ($rowa['children'] as $keyb=>$rowb){
				$uparr['sort_order'] = $keyb;
				$uparr['pid'] = $rowa['id'];
				$uparr['update_time'] = time();
				$res = $this->Model->where(['id'=>$rowb['id']])->update($uparr);
				if (empty($rowb['children'])) continue;
				foreach ($rowb['children'] as $keyc=>$rowc){
					$uparr['sort_order'] = $keyc;
					$uparr['pid'] = $rowb['id'];
					$uparr['update_time'] = time();
					$res = $this->Model->where(['id'=>$rowc['id']])->update($uparr);
				}
			}
		}
		$this->Model->cleanMemcache();
		return $this->success('操作成功.');
	}
}
