<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;
use app\distribution\model\RoleGoodsModel;
use app\distribution\model\DividendRoleModel;

/*------------------------------------------------------ */
//-- 身份商品
/*------------------------------------------------------ */
class RoleGoods extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new RoleGoodsModel();
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		$this->getList(true);
        return $this->fetch();
    }
	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false,$is_delete=0) {
		$this->sqlOrder = 'rg_id DESC';
        $data = $this->getPageList($this->Model);
		$this->assign("data", $data);
		if ($runData == false){
			$data['content'] = $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
	//-- 添加修改前调用
	/*------------------------------------------------------ */
	public function asInfo($row){
	    $this->assign('role_list',(new DividendRoleModel)->getRows());
		return $row;
	}
	/*------------------------------------------------------ */
	//-- 添加前调用
	/*------------------------------------------------------ */
	public function beforeAdd($data){
		if ($data['id'] < 1){
			$data['add_time'] =  time();	
		}
		if(empty($data['goods_img'])) return $this->error('请上传商品图片.');
		return $data;
	}
	//-- 添加后调用
	/*------------------------------------------------------ */
	public function afterAdd($data){
		$this->Model->cleanMemcache();
        $this->_log($data['rg_id'], '添加身份商品');
        return $this->success('添加成功.',url('index'));
	}
	/*------------------------------------------------------ */
	//-- 修改前调用
	/*------------------------------------------------------ */
	public function beforeEdit($data){		
		return $this->beforeAdd($data);
	}
	//-- 修改后调用
	/*------------------------------------------------------ */
	public function afterEdit($data){
		$this->Model->cleanMemcache();
        $this->_log($data['rg_id'], '修改身份商品');
		return $this->success('修改成功.');
	}
	/*------------------------------------------------------ */
	//-- 删除
	/*------------------------------------------------------ */
	public function del(){
		$rg_id = input('rg_id',0,'intval');
		if ($rg_id < 1) return $this->error('传递参数失败！');
		$data = $this->Model->where('rg_id',$rg_id)->find();
		unlink('.'.$data['goods_name']);
		$res = $data->delete();
		if ($res < 1)  return $this->error(); 
		$this->Model->cleanMemcache();
        $this->_log($data['rg_id'], '删除身份商品：'.$data['goods_name']);
        return $this->success('删除成功.');
	}

}
