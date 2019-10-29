<?php
namespace app\member\controller\sys_admin;
use app\AdminController;
use app\member\model\UsersLevelModel;


/**
 * 会员等级管理
 */
class Level extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new UsersLevelModel(); 
    }
	/*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
		$this->getList(true);		
        return $this->fetch('index');
    }  
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
		$this->order_by = 'min';
		$this->sort_by = 'ASC';
        $data = $this->getPageList($this->Model);			
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		if (empty($data['level_name'])) return $this->error('操作失败:等级名称不能为空！');		
		if ($data['min'] >= $data['max'] && $data['max'] > 0 ) return $this->error('操作失败:积分上限必须大于积分下限！');
		
		$count = $this->Model->where('level_name',$data['level_name'])->count('level_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的等级名称，不允许重复添加！');
		
		//判断积分等级是否冲突
		$where = $data['min']." BETWEEN  min AND max ";
		if ($data['max'] > 0) $where .= " OR ".$data['max']." BETWEEN  min AND max ";
		$count = $this->Model->where($where)->count('level_id');
		if ($count > 0) $this->error('操作失败:积分范围发生冲突！');
		$count = $this->Model->where('max',0)->count('level_id');
		if ($count > 0) $this->error('操作失败:已存在上限为0的等级，不能重复添加！');
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后处理
	/*------------------------------------------------------ */
    public function afterAdd($data) {
		$this->_Log($data['level_id'],'添加会员等级:'.$data['level_name']);
	}
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){	
		if (empty($data['level_name'])) return $this->error('操作失败:等级名称不能为空！');		
		if ($data['min'] >= $data['max'] && $data['max'] > 0 ) return $this->error('操作失败:积分上限必须大于积分下限！');
		$info = $this->Model->find($data['level_id']);		
		$this->checkUpData($info,$data);
		$where[] = ['level_name','=',$data['level_name']];
		$where[] = ['level_id','<>',$data['level_id']];
		$count = $this->Model->where($where)->count('level_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的等级名称，不允许重复添加！');
		unset($where);
		//判断积分等级是否冲突
		$where[] = "level_id <> '".$data['level_id']."'";
		$whereOr[] = $data['min']." BETWEEN  min AND max ";
		if ($data['max'] > 0) $whereOr[] = $data['max']." BETWEEN  min AND max ";
		$where[] = '('.join(' OR ',$whereOr).')';
		$count = $this->Model->where(join(' AND ',$where))->count('level_id');
		if ($count > 0) $this->error('操作失败:积分范围发生冲突！');
		unset($where);	
		if ($data['max'] == 0){
			$where[] = ['max','=',0];
			$where[] = ['level_id','<>',$data['level_id']];
			$count = $this->Model->where($where)->count('level_id');
			if ($count > 0) $this->error('操作失败:已存在上限为0的等级，不能重复添加！');
		}		
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['role_id'],'修改会员等级:'.$data['level_name']);
	}
	/*------------------------------------------------------ */
	//-- 删除等级
	/*------------------------------------------------------ */
	public function delete(){
		$level_id = input('level_id',0,'intval');
		if ($level_id < 1)  return $this->error('传参失败！');
		$res = $this->Model->where('level_id',$level_id)->delete();
		if ($res < 1) return $this->error('未知错误，删除失败！');
		$this->Model->cleanMemcache();	
		return $this->success('删除成功！',url('index'));
	}
}
