<?php
namespace app\ddkc\controller\sys_admin;
use app\AdminController;
use app\ddkc\model\DdGoodsModel;
use app\distribution\model\DividendRoleModel;



/**
 * 会员等级管理
 */
class Goods extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new DdGoodsModel(); 
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
		$this->order_by = 'miner_id';
		$this->sort_by = 'DESC';
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
		if (empty($data['miner_name'])) return $this->error('操作失败:矿机名称不能为空！');
		$imgs = input('imgs');
        $data['imgs'] = '';
        if(is_array($imgs['path'])) $data['imgs'] = serialize($imgs['path']);
		
		$count = $this->Model->where('miner_name',$data['miner_name'])->count('miner_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的矿机名称，不允许重复添加！');
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后处理
	/*------------------------------------------------------ */
    public function afterAdd($data) {
		$this->_Log($data['miner_id'],'矿机:'.$data['level_name']);
	}
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
     	
		if (empty($data['miner_name'])) return $this->error('操作失败:矿机名称不能为空！');
		$where[] = ['miner_name','=',$data['miner_name']];
		$where[] = ['miner_id','<>',$data['miner_id']];
		$count = $this->Model->where($where)->count('miner_id');
		if ($count > 0) return $this->error('操作失败:已存在相同的矿机名称，不允许重复添加！');
		unset($where);
		$imgs = input('imgs');
        $data['imgs'] = '';
        if(is_array($imgs['path'])) $data['imgs'] = serialize($imgs['path']);
        if(is_array(input('limit_user_role'))) {
        	$data['limit_user_role'] = serialize(input('limit_user_role'));
        }
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['miner_id'],'修改矿机:'.$data['miner_name']);
	}
	/*------------------------------------------------------ */
	//-- 删除等级
	/*------------------------------------------------------ */
	public function delete(){
		$miner_id = input('miner_id',0,'intval');
		if ($miner_id < 1)  return $this->error('传参失败！');
		$res = $this->Model->where('miner_id',$miner_id)->delete();
		if ($res < 1) return $this->error('未知错误，删除失败！');
		$this->Model->cleanMemcache();	
		return $this->success('删除成功！',url('index'));
	}
	/*------------------------------------------------------ */
    //-- 添加、修改评论
    /*------------------------------------------------------ */
    protected function asInfo($data) {
    	$DividendRoleModel = new DividendRoleModel();
    	
		$this->assign('limit_user_role',unserialize($data['limit_user_role']));
        $this->assign("UsersRole", $DividendRoleModel->getRows());//分销身份
		$this->assign('imgs',unserialize($data['imgs']));
        return $data;
    }

     /*------------------------------------------------------ */
    //-- 上传分享海报背景图片
    /*------------------------------------------------------ */
    public function uploadImg(){
        $result = $this->_upload($_FILES['file'],'mining_goods/');
        if ($result['error']) {
            return $this->error('上传失败，请重试.');
        }
        $file_url = str_replace('./','/',$result['info'][0]['savepath'].$result['info'][0]['savename']);
        $data['code'] = 1;
        $data['msg'] = "上传成功";
        $data['image'] = array('thumbnail'=>$file_url,'path'=>$file_url);
        return $this->ajaxReturn($data);
    }
    /*------------------------------------------------------ */
    //-- 删除图片
    /*------------------------------------------------------ */
    public function removeImg() {
        $file = input('post.url','','trim');
        unlink('.'.$file);
        return $this->success('删除成功.');
    }
}
