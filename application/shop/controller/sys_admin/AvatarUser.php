<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\AvatarUserModel;
/*------------------------------------------------------ */
//-- 虚拟会员
/*------------------------------------------------------ */
class AvatarUser extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new AvatarUserModel();		
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
    public function getList($runData = false) {		
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
		
		return $row;
	}
	/*------------------------------------------------------ */
	//-- 添加前调用
	/*------------------------------------------------------ */
	public function beforeAdd($data){				
		if(empty($data['user_name'])) return $this->error('请输入会员名称！');
		$where[] = ['user_name','=',$data['user_name']];
		if ($data['id'] < 1){
			$data['add_time'] =  time();	
		}else{
			$where['id'] = ['id','<>',$data['id']];
		}
		$count = $this->Model->where($where)->count();
		if ($count > 0) return $this->error('已存在相同的会员名称，不允许重复添加.');		
		return $data;
	}
	//-- 添加后调用
	/*------------------------------------------------------ */
	public function afterAdd($data){
		$this->Model->cleanMemcache();
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
		return $this->success('修改成功.');
	}
	/*------------------------------------------------------ */
	//-- 根据关键字查询
	/*------------------------------------------------------ */
	public function pubSearch() {
		 $_list = $this->Model->orderRaw('RAND()')->limit(20)->select();
		
		$result['list'] = $_list;
		$result['code'] = 1;
		return $this->ajaxReturn($result);
	}

}
