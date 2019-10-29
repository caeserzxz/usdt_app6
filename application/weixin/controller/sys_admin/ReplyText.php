<?php
namespace app\weixin\controller\sys_admin;

use app\AdminController;
use think\Cache;
use think\Db;

use app\weixin\model\WeiXinKeywordsModel;
/**
 * 文本素材
 * Class Index
 * @package app\store\controller
 */
class ReplyText extends AdminController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new WeiXinKeywordsModel();		
    }
	/*------------------------------------------------------ */
	//-- 主页
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
		$where[] = ['type','=','text'];	
		$search['keyword'] =  input('keyword','','trim');
		if (empty($search['keyword']) == false) $where[] = ['keyword','like','%'.$search['keyword'].'%'];	
		
        $this->data = $this->getPageList($this->Model, $where,$this->_field,$this->_pagesize);			
		$this->assign("data", $this->data);
		$this->assign("search", $search);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
	//-- 添加前调用
	/*------------------------------------------------------ */
	public function beforeAdd($data){
		if (empty($data['keyword'])) return $this->error('关键字不能为空！');
		$data['keyword'] = str_replace('，',',',$data['keyword']);
		$keyword = explode(',',$data['keyword']);
		foreach ($keyword as $key){			
			$where[] = ['','exp',Db::raw("FIND_IN_SET('".$key."',keyword)")];
			if ($data['id'] > 0) $where[] = ['id','<>',$data['id']];
			
			$count = $this->Model->where($where)->count('id');
			if ($count > 0) return $this->error($key.' - 已存在相同的关键字，不允许重复添加！');
		}		
		$data['status'] = input('post.status',0,'intval');
		$data['type'] = 'text';
		$data['data'] = input('post.reply_text','','trim');
		$data['add_time'] = time();
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后调用
	/*------------------------------------------------------ */
	public function afterAdd($data){
		$this->_log($data['id'],'添加关键字:'.$data['keyword']);//记录日志		
	}
	/*------------------------------------------------------ */
	//-- 修改前调用
	/*------------------------------------------------------ */
	public function beforeEdit($data){
		$data = $this->beforeAdd($data);
		unset($data['add_time']);
		$data['update_time'] = time();
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 修改后调用
	/*------------------------------------------------------ */
	public function afterEdit($data){
		$this->_log($data['id'],'微信文本素材,修改:'.$data['keyword']);//记录日志		
	}
	/*------------------------------------------------------ */
	//-- ajax快速修改
	//-- id int 修改ID
	//-- data array 修改字段
	/*------------------------------------------------------ */
	public function afterAjax($id,$data){
		if (isset($data['status'])){
			$info = '微信文本素材,快速修改关键字状态:'.($data['status']==1?'启用':'停用');
		}elseif (isset($data['subscribe'])){
			$info = '微信文本素材快速修改关注回复:'.($data['subscribe']==1?'启用':'停用');
			if ($data['subscribe']==1){
				$where[] = ['id','<>',$id];
				$where['subscribe'] = ['subscribe','=',1];
				$uparr['subscribe'] = 0;
				$uparr['update_time'] = time();
				$this->Model->where($where)->update($uparr);
			}
		}elseif (isset($data['default'])){
			$info = '微信文本素材,快速修改默认回复:'.($data['default']==1?'启用':'停用');
			if ($data['default'] == 1){
				$where[] = ['id','<>',$id];
				$where['default'] = ['default','=',1];
				$uparr['default'] = 0;
				$uparr['update_time'] = time();
				$this->Model->where($where)->update($uparr);
			}
		}else{
			return false;	
		}		
		$this->_log($id,$info);//记录日志
		return true;
	}
	/*------------------------------------------------------ */
	//-- 删除
	/*------------------------------------------------------ */
    public function delete(){  
        $id = input('id') * 1;
        if (empty($id))  return $this->error('缺少传值.');
		$row = $this->Model->find($id);
		if ($this->Model->where('id',$id)->delete() < 1) return $this->error('操作失败.');
		$this->Model->cleanMemcache($row['keyword']);
		return $this->success('删除成功.');		
    }
	/*------------------------------------------------------ */
	//-- 搜索文章
	/*------------------------------------------------------ */
	public function searchBox(){
		$this->_pagesize = 10;
		$this->_field = 'id,keyword';
		$this->getList(true);
		$result['data'] = $this->data;
		if ($this->request->isPost()) return $this->ajaxReturn($result);
		$this->assign("cgOpt", arrToSel($this->cg_list,input('cid',0,'intval')));
		$this->assign("_menu_index", input('_menu_index','','trim'));
		$this->assign("searchType", input('searchType','','trim'));
		return $this->fetch()->getContent();
	}
}
