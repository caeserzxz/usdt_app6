<?php
namespace app\ddkc\controller\sys_admin;
use app\AdminController;
use app\ddkc\model\DdInformationModel;

/**
 * 会员等级管理
 */
class Information extends AdminController
{	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new DdInformationModel(); 
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
		$this->order_by = 'id';
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
		/*$imgs = input('imgs');
        $data['imgs'] = '';
        if(is_array($imgs['path'])) $data['imgs'] = serialize($imgs['path']);*/
        $data['add_time'] = $data['update_time'] = time();
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后处理
	/*------------------------------------------------------ */
    public function afterAdd($data) {
		$this->_Log($data['id'],'添加资讯:'.$data['title']);
	}
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){
     	
		/*$imgs = input('imgs');
        $data['imgs'] = '';
        if(is_array($imgs['path'])) $data['imgs'] = serialize($imgs['path']);*/
        $data['update_time'] = time();
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['id'],'修改资讯:'.$data['title']);
	}
	/*------------------------------------------------------ */
	//-- 删除等级
	/*------------------------------------------------------ */
	public function delete(){
		$id = input('id',0,'intval');
		if ($id < 1)  return $this->error('传参失败！');
		$res = $this->Model->where('id',$id)->delete();
		if ($res < 1) return $this->error('未知错误，删除失败！');
		$this->Model->cleanMemcache();	
		return $this->success('删除成功！',url('index'));
	}
	/*------------------------------------------------------ */
    //-- 添加、修改评论
    /*------------------------------------------------------ */
    protected function asInfo($data) {
		// $this->assign('imgs',unserialize($data['imgs']));
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


    /*------------------------------------------------------ */
    //-- 上传视频
    /*------------------------------------------------------ */
    public function uploadapp(){
        if($_FILES['file']['size'] > 100000000){
            $result['code'] = 1;
            $result['msg'] = '最大支持 100M MB上传.';
            return $this->ajaxReturn($result);
        }
        $file_type = end(explode('.',$_FILES['file']['name']));
        if (in_array($file_type,['apk','rm','rmvb','wmv','avi','mpg','mpeg','mp4']) == false){
            $result['code'] = 1;
            $result['msg'] = '格式不对，请核实.';
            return $this->ajaxReturn($result);
        }
        $dir = config('config._upload_').'download/information/';
        makeDir($dir);
        $file_name = random_str(16).'.'.$file_type;
        move_uploaded_file($_FILES['file']['tmp_name'],$dir.$file_name);
        $result['code'] = 0;
        $result['filename'] = trim($dir.$file_name,'.');
        return $this->ajaxReturn($result);
    }
}
