<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 19-1-21
 * Time: 9:42
 */

namespace app\shop\controller\sys_admin;

use think\facade\Request;

use app\AdminController;
use app\shop\model\GoodsCommentModel;
use app\shop\model\GoodsModel;
use app\shop\model\AvatarUserModel;
use app\shop\model\GoodsCommentImagesModel;
use app\member\model\UsersModel;

class GoodsComment extends AdminController {

    /**
     * @var ShopGoodsComment
     */
    public $Model;   

    protected function initialize() {
        parent::initialize();
        $this->Model = new GoodsCommentModel();
    }
	//*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index(){
		$this->getList(true);
		
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false,$is_delete=0) {
		$search['status'] = input('status',0,'intval');
		$search['keyword'] = input('keyword','','trim');
		$search['select_goods'] = input('select_goods',0,'intval');
		$where = array();
		if ($search['select_goods'] > 0){
			$where[] = ['goods_id','=',$search['select_goods']];
		}
		if ($search['status'] > 0){
			$where[] = ['status','=',$search['status']];
		}
		if (empty($search['keyword']) == false){		
			 $UsersModel = new UsersModel();
			 $uids = $UsersModel->where("mobile LIKE '%".$search['keyword']."%' OR user_name LIKE '%".$search['keyword']."%' OR nick_name LIKE '%".$search['keyword']."%' OR mobile LIKE '%".$search['keyword']."%'")->column('user_id');
			 $uids[] = -1;//增加这个为了以上查询为空时，限制本次主查询失效			 
			 $where[] = ['user_id','in',$uids];
		}
        $this->data = $this->getPageList($this->Model, $where);		
		$this->assign("data", $this->data);
		$this->assign("search", $search);	
		$this->assign('statusListHtml', arrToSel($this->Model->statusList,$search['status']));	
		if ($runData == false){
            $this->data['content'] = $this->fetch('list');
			unset($this->data['list']);
			return $this->success('','',$this->data);
		}
        return true;
    }
   
    /*------------------------------------------------------ */
    //-- 添加、修改评论
    /*------------------------------------------------------ */
    protected function asInfo($data) {
        $goodsCategoryList = (new GoodsModel)->getClassList();
        $this->assign('categoryOpt', arrToSel($goodsCategoryList, $data['cat_id']));
		if (empty($data['id'])){
			$data['type'] = 'goods';	
			$data['status'] = '2';
			$imgWhere[] = ['comment_id','=',0]; 
			$imgWhere[] = ['admin_id','=',AUID]; 
		}else{
			$imgWhere[] = ['comment_id','=',$data['id']]; 
		}
		$this->assign('imgs',(new GoodsCommentImagesModel)->where($imgWhere)->select()->toArray());
        return $data;
    }	
	
	 /*------------------------------------------------------ */
    //-- 上传图片
    /*------------------------------------------------------ */
    public function uploadImg(){	
		  $thumb['width'] = 250;
		  $thumb['height'] = 250;
		  $result = $this->_upload($_FILES['file'],'comment/',$thumb);
		  if ($result['error']) {
			  $data['code'] = 1;
			  $data['msg'] = $result['info'];
			  return $this->ajaxReturn($data);
		  }
		  $_root_ = Request::root();
		  $addArr['comment_id'] = input('post.comment_id',0,'intval');
		  $addArr['admin_id'] = AUID;
		  $addArr['image'] = $file_url = str_replace('./','/',$result['info'][0]['savepath'].$result['info'][0]['savename']);
		  $addArr['thumbnail'] = str_replace('.','_thumb.',$addArr['image']);
		  $GoodsCommentImagesModel =  new GoodsCommentImagesModel();
		  $GoodsCommentImagesModel->save($addArr);	
		  $img_id = $GoodsCommentImagesModel->id;
		  if ($img_id < 1){
			  @unlink($file_url);//删除刚刚上传的
			  $data['code'] = 0;
			  $data['msg'] = '图片写入数据库失败！';
			  return $this->ajaxReturn($data);
		  }
		  $data['code'] = 1;
		  $data['msg'] = "上传成功";
		  $data['image'] = array('id'=>$img_id,'thumbnail'=>$file_url,'path'=>$file_url);		 
		  return $this->ajaxReturn($data);
    }
	
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
		if ($data['type'] == 'goods' && $data['goods_id'] < 1){
			return $this->error('请选择商品.');
		}
		if ($data['type'] == 'goods_category' && $data['cat_id'] < 1){
			return $this->error('请选择分类.');
		}
		if(count($data['GoodsImages']['id']) > 3){
		    return $this->error('上传图片请少于3张');
        }
		$AvatarUserModel = new AvatarUserModel();
		if ($data['avatar_user'] == 0){
			$avatarUser = $AvatarUserModel->orderRaw('RAND()')->find();
			$data['avatar_user'] = $avatarUser['id'];
			$data['user_name'] = $avatarUser['user_name'];
			$data['headimgurl'] = $avatarUser['headimgurl'];
		}else{
			$avatarUser = $AvatarUserModel->find($data['avatar_user']);
			$data['user_name'] = $avatarUser['user_name'];
			$data['headimgurl'] = $avatarUser['headimgurl'];
		}
		$GoodsImages = input('post.GoodsImages');
		if (empty($GoodsImages) == false){
			$data['is_imgs'] = 1;
		}
		$data['create_time'] = time();
		$data['admin_id'] = AUID;
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后调用
	/*------------------------------------------------------ */
	public function afterAdd($data){
		$GoodsImages = input('post.GoodsImages');
		$GoodsCommentImagesModel = new GoodsCommentImagesModel();
		foreach ($GoodsImages['id'] as $key=>$id){
			$imgwhere = array();
			$imgwhere[] = ['id','=',$id];
			$imgwhere[] = ['comment_id','=',0];
			$imgwhere[] = ['admin_id','=',AUID];
			$upArr['sort_order'] = $key;
			$upArr['comment_id'] = $data['id'];
			$GoodsCommentImagesModel->where($imgwhere)->update($upArr);
		}
		return $this->success('添加成功',url('index'));
	}
	/*------------------------------------------------------ */
	//-- 修改前调用
	/*------------------------------------------------------ */
	public function beforEidt($data) {
		$info = $this->Model->find($data['id']);
		if ($info['status'] != $data['status']){
			$data['review_time'] = time();
			$data['review_admin_id'] = AUID;
		}
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 快捷修改
	/*------------------------------------------------------ */
	public function ajaxEdit(){
		$id = input('id',0,'intval');
        $field = input('field','','trim');
		if ($id=='' || $field=='') return $this->error('缺少必要传参.');
		$row = $this->Model->find($id);
		if ($field == 'status'){
			$upArr['status'] = $row['status'] == 2 ? 3: 2;
		}		
		$this->Model->save($upArr,['id'=>$id]);
		return $this->success();
	}
}