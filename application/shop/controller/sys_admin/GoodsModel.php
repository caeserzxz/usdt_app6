<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\GoodsModelModel;
use app\shop\model\AttributeValModel;
use app\shop\model\AttributeModel;
/**
 * 商品模型
 * Class Index
 * @package app\store\controller
 */
class GoodsModel extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new GoodsModelModel(); 
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
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){
		if (empty($data['attr_group']) == false) $data['attr_group'] = str_replace(",","\n",$data['attr_group']);
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		$count = $this->Model->where('name',$data['name'])->count('id');
		if ($count > 0) return $this->error('操作失败:已存在相同的模型名称，不允许重复添加！');
		$data['update_time'] = time();
		return $data;
	}

	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		if (empty($data['name']) ) return $this->error('模型名称不能为空.');
		$map['id'] = $data['id'];
		//验证数据是否出现变化
		$dbarr = $this->Model->field(join(',',array_keys($data)))->where($map)->find()->toArray();	
		$this->checkUpData($dbarr,$data);
		$where[] = ['id','<>',$map['id']];
		$where[] = ['name','=',$data['name']];
		$count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的模型名称，不允许重复添加！');
		$data['update_time'] = time();
		
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 删除分类
	/*------------------------------------------------------ */
	public function del(){
		$id = input('id',0,'intval');
		if ($id < 1) return $this->error('传递参数失败！');
		$list = $this->Model->getRows();
		$info = $list[$id];
		if (empty($info))  return $this->error('模型不存在！');
		if ($info['attr_list']){
			$res = model(AttributeValModel)->where('model_id',$id)->delete();
			if ($res < 1)  return $this->error(); 
			$res = model(AttributeModel)->where('model_id',$id)->delete();
			if ($res < 1)  return $this->error(); 			
		}
		$res = $this->Model->where('id',$id)->delete();
		if ($res < 1)  return $this->error(); 
		$this->Model->cleanMemcache();
		$this->_log($id,'删除商品模型');
		return $this->success('删除成功.');
	}
	/*------------------------------------------------------ */
	//-- 快速修改后
	/*------------------------------------------------------ */
	public function afterAjax(){
		$this->Model->cleanMemcache();
	}
	
}
