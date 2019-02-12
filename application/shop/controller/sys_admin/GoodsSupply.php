<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\GoodsSupplyModel;

/**
 * 商品供货商
 * Class Index
 * @package app\store\controller
 */
class GoodsSupply extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new GoodsSupplyModel(); 
    }
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
		$this->assign('list',$this->Model->getRows());
        return $this->fetch('index');
    }

  
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		$count = $this->Model->where('name',$data['name'])->count('id');
		if ($count > 0) return $this->error('操作失败:已存在相同的供货商名称，不允许重复添加！');
		$data['update_time'] = time();
		return $data;
	}

	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		if (empty($data['name']) ) return $this->error('供货商名称不能为空.');
		$map['id'] = $data['id'];
		//验证数据是否出现变化
		$dbarr = $this->Model->field(join(',',array_keys($data)))->where($map)->find()->toArray();	
		$this->checkUpData($dbarr,$data);
		$where[] = ['id','<>',$map['id']];
		$where[] = ['name','=',$data['name']];
		$count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的供货商名称，不允许重复添加！');
		$data['update_time'] = time();
		
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 删除分类
	/*------------------------------------------------------ */
	public function del(){
		$id = input('id',0,'intval');
		if ($map['id'] < 1) return $this->error('传递参数失败！');	
		$res = $this->Model->where('id',$id)->delete();
		if ($res < 1)  return $this->error(); 
		$this->Model->cleanMemcache();
		$this->_log($id,'删除商品供货商');
		return $this->success('删除成功.');
	}
	
}
