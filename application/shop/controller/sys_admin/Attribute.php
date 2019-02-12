<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\AttributeModel;
use app\shop\model\GoodsModelModel;

/**
 * 商品属性类型值
 * Class Index
 * @package app\store\controller
 */
class Attribute extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new AttributeModel(); 
    }
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
		$model_id = input('model_id',0,'intval');
		$GoodsModel = model(GoodsModelModel)->getRows();
		if (empty($GoodsModel)){
			return $this->error('请先添加商品模型.',url('sys_admin.goodsModel/index'));
		}
		if ($model_id < 1){
			$attr_keys = array_keys($GoodsModel);
			$model_id = $attr_keys[0];
		}
		$this->assign('listOpt',$GoodsModel);
		$list = $this->Model->where('model_id',$model_id)->select()->toArray();
		$this->assign('list',$list);
		$this->assign('attr_input_type',array('手工录入','列表中选择','多行文本框'));
		$this->assign('model_id',$model_id);		
        return $this->fetch('index');
    }

   /*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){
		$this->assign('listOpt',model(GoodsModelModel)->getRows());
		if ($data['attr_values']) $data['attr_values'] = str_replace(",","\n",$data['attr_values']);
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		$where[] = ['attr_name','=',$data['attr_name']];
		$where[] = ['model_id','=',$data['model_id']];
		$count = $this->Model->where($where)->count('attr_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的属性名称，不允许重复添加.');
		$data['update_time'] = time();
		$data['attr_values'] = str_replace("\n",",",trim($data['attr_values']));
		return $data;
	}

	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
		$where[] = ['attr_id','<>',$data['attr_id']];
		$where[] = ['attr_name','=',$data['attr_name']];
		$where[] = ['model_id','=',$data['model_id']];
		$count = $this->Model->where($where)->count('attr_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的属性名称，不允许重复添加.');
		$data['attr_values'] = str_replace("\n",",",trim($data['attr_values']));
		$data['update_time'] = time();
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data){
		return $this->success('修改成功.',url('index',array('type_id'=>$data['type_id'])));
	}
	/*------------------------------------------------------ */
	//-- 删除
	/*------------------------------------------------------ */
	public function del(){
		$attr_id = input('attr_id',0,'intval');
		if ($attr_id < 1) return $this->error('传递参数失败！'); 
		$_info = $this->Model->find($attr_id);
		if (empty($_info))  return $this->error('属性不存在！');
		$res = $this->Model->where('attr_id',$attr_id)->delete();
		if ($res < 1)  return $this->error(); 
		model(AttributeTypeModel)->cleanMemcache();
		model(AttributeModel)->where('attr_id',$attr_id)->delete();
		$this->_log($attr_id,'删除商品属性.');
		return $this->success('删除成功.');
	}
	
}
