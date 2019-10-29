<?php
namespace app\weixin\controller\sys_admin;

use app\AdminController;
use think\Cache;
use think\Db;

use app\weixin\model\WeiXinKeywordsModel;

/**
 * 图文素材
 * Class Index
 * @package app\store\controller
 */
class ReplyNews extends AdminController
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
		$where[] = ['type','=','news'];
		$where[] = ['pid','=','0'];
		$search['keyword'] =  input('keyword','','trim');
		if (empty($search['keyword']) == false) $where[] = ['keyword','like','%'.$search['keyword'].'%'];	
		
        $data = $this->getPageList($this->Model, $where);			
		$this->assign("data", $data);
		$this->assign("search", $search);
		if ($runData == false){
			$data['content']= $this->fetch('list')->getContent();
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }
	/*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
    public function asInfo($data){		
		$WeixinReplyType = $this->getDict('WeixinReplyType');		
		$this->assign("option", arrToSel($WeixinReplyType));		
		if ($data['id'] > 0){
			 $map['pid'] = $data['id'];
			 $data['row_sun'] = $this->Model->where($map)->select()->toArray();
			 foreach ($data['row_sun'] as $key=>$val){
				$val['option'] = arrToSel($WeixinReplyType,$val['bind_type']);
				$data['row_sun'][$key] = $val;
				
			 }
		}
		$data['option'] = arrToSel($WeixinReplyType,$data['bind_type']);		
		return $data;
	}
	
	/*------------------------------------------------------ */
	//-- 保存
	/*------------------------------------------------------ */
	public function save(){
		$id = input('id',0,'intval');
		$data['status'] = input('status',0,'intval');
		$data['description'] = input('description','','trim');            
		$data['imgurl'] = input('imgurl','','trim');
		$data['keyword'] =  input('keyword','','trim');
		$data['title'] = input('title','','trim');
		$data['bind_type'] = input('bind_type','','trim');
		$data['data'] = input('type_val','','trim');
		$data['ext_id'] = input('type_val_key',0,'intval');
		
		$arr['bind_type'] = input('bind_type','','trim');
		if (empty($data['keyword'])) return $this->error('关键词没有填写！');	
		if (empty($data['title'])) return $this->error('标题没有填写！');
		if (empty($data['description'])) return $this->error('请填写简介！');
		if (empty($data['imgurl'])) return $this->error('请选择图文的图片！');
		if (empty($data['bind_type'])) return $this->error('请设置回复类型！');
		if (empty($data['data'])) return $this->error('绑定关联不能为空！');          
		$data['type'] = 'news';
		
		$data['keyword'] = str_replace('，',',',$data['keyword']);
		$keyword = explode(',',$data['keyword']);
		foreach ($keyword as $key){
			$where[] = ['','EXP',Db::raw("FIND_IN_SET('".$key."',keyword)")];
			if ($id > 0) $where[] = ['id','<>',$id];
			$count = $this->Model->where($where)->count('id');
			if ($count > 0) return $this->error($key.' - 已存在相同的关键字，不允许重复添加！');
		}
		unset($where);				
		
	   
		$ndata = input('ndata');	
		$this->check_data($ndata);
		$new = input('new');
		$this->check_data($new);
		$data['is_more'] = empty($data) && empty($new)?0:1;
		if ($id > 0){
			$map['id'] = $id;
			$data['update_time'] = time();
			$this->Model->save($data,$map);
		}else{				
			$data['add_time'] = $data['update_time'] = time();
			$id = $this->Model->save($data,false);
		}
		unset($data);
		if (empty($ndata['id']) == false){
			foreach ($ndata['id'] as $key=>$val){
				$map['id'] = $val;
				$arr = [];		
				$arr['title'] = $ndata['title'][$key];
				$arr['imgurl'] = $ndata['imgurl'][$key];
				$arr['bind_type'] = $ndata['bind_type'][$key];
				$arr['data'] = $ndata['type_val'][$key];
				$arr['ext_id'] = $ndata['type_val_key'][$key];
				$arr['type'] = 'news';
				$arr['update_time'] = time();				
				$this->Model->where($map)->update($arr);
			}
		}
		unset($arr);
		if (empty($new['title']) == false){
			foreach ($new['title'] as $key=>$val){				
				$arr['pid'] = $id; 
				$arr['title'] = $val;
				$arr['imgurl'] = $new['imgurl'][$key];
				$arr['bind_type'] = $new['bind_type'][$key];
				$arr['data'] = $new['type_val'][$key];
				$arr['ext_id'] = $new['type_val_key'][$key];
				$arr['type'] = 'news';
				$arr['add_time'] = $arr['update_time'] = time();
				$this->Model->create($arr);
			}
		}
		return $this->success('保存成功！');
	}
	
	/*------------------------------------------------------ */
	//-- 验证数据
	/*------------------------------------------------------ */
	public function check_data($data = array()){
		if (empty($data)) return false;
		foreach ($data as $key=>$row){
			foreach ($row as $val){
				if (empty($val)){
					 if ( $key == 'title') return $this->error('小图文 - 标题没填写！');
					 elseif ( $key == 'imgurl') return $this->error('小图文 - 请选择图文的图片！');
					 elseif ( $key == 'bind_type') return $this->error('小图文 - 请设置回复类型！');
					 elseif ( $key == 'type_val') return $this->error('小图文 - 绑定关联不能为空！');
				}
			}
		}
		return false;
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
}
