<?php
/*------------------------------------------------------ */
//-- 文件上传管理程序
//-- @author iqgmy
/*------------------------------------------------------ */
namespace app\mainadmin\controller;
use app\AdminController;
use think\facade\Request;
use app\shop\model\GoodsImgsModel;

class Attachment extends AdminController{

  protected $_root_;

//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->_root_ = Request::root();
    }
	
    
    /**
     * 编辑器上传
     */
    public function editer_upload() {
        $file_type = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        $result = $this->_upload($_FILES['imgFile']);
        if ($result['error']) {
            echo json_encode(array('error'=>1, 'message'=>$result['info']));
        } else {
            echo json_encode(array('error'=>0, 'url'=> '/'.$result['info'][0]['savepath']. $result['info'][0]['savename']));
        }
        exit;
    }
    
    /**
     * 编辑器图片空间 
     */
    public function editer_manager() {
        $root_path = config('config._upload_');
        $root_url = config('config._upload_');
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
        
        if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
            echo "Invalid Directory name.";
            exit;
        }
		
        if ($dir_name !== '' && $dir_name != 'file') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
				mkdir($root_path);
            }
        }
        
        //根据path参数，设置各路径和URL
        if (empty($_GET['path'])) {
            $current_path = $root_path . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = $root_path . '/' . $_GET['path'];
            $current_url = $root_url . $_GET['path'];
            $current_dir_path = $_GET['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }
		
        //排序形式，name or size or type
        $this->_order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }

        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
		if ($filename{0} == '.') continue;
		$file = $current_path . $filename;
		if (is_dir($file)) {
			$file_list[$i]['is_dir'] = true; //是否文件夹
			$file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
			$file_list[$i]['filesize'] = 0; //文件大小
			$file_list[$i]['is_photo'] = false; //是否图片
			$file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
		} else {
			$file_list[$i]['is_dir'] = false;
			$file_list[$i]['has_file'] = false;
			$file_list[$i]['filesize'] = filesize($file);
			$file_list[$i]['dir_path'] = '';
			$file_arr = explode('.', trim($file));
			$file_arr = array_pop($file_arr);
			$file_ext = strtolower($file_arr);
			$file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
			$file_list[$i]['filetype'] = $file_ext;
		}
		$file_list[$i]['filename'] = $filename; //文件名，包含扩展名
		$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
		$i++;
            }
            closedir($handle);
        }
        usort($file_list, array($this, '_cmp_func'));
        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = str_replace('.','',$current_url);
		
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        //输出JSON字符串
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode($result);
        exit;
    }
    
    //排序
    private function _cmp_func($a, $b) {
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        } else {
            if ($this->_order == 'size') {
                if ($a['filesize'] > $b['filesize']) {
                    return 1;
                } else if ($a['filesize'] < $b['filesize']) {
                    return -1;
                } else {
                    return 0;
                }
            } else if ($this->_order == 'type') {
                return strcmp($a['filetype'], $b['filetype']);
            } else {
                return strcmp($a['filename'], $b['filename']);
            }
		}
    }
	/**
     * 商品上传
    */
    public function goodsUpload() {
		if ($_FILES['file']){
			$thumb['width'] = 350;
			$thumb['height'] = 300;
			$result = $this->_upload($_FILES['file'],'gimg/',$thumb);
			if ($result['error']) {
				$data['code'] = 1;
				$data['msg'] = $result['info'];
				return $this->ajaxReturn($data);
			}
			$addarr['goods_id'] = input('post.gid',0,'intval');
			$addarr['sku_val'] = input('post.sku','','trim');
			
			if (defined('AUID')){
				$addarr['admin_id'] = AUID;
				$where[]= ['admin_id','=',AUID];
			}elseif (defined('SAUID')){
				$addarr['store_admin_id'] = SAUID;
				$where[]= ['store_admin_id','=',SAUID];
			}
			
			$savepath = trim($result['info'][0]['savepath'],'.');
	
			$addarr['store_id'] = $this->store_id;
			$addarr['goods_img'] = $file_url = $savepath.$result['info'][0]['savename'];
			$addarr['goods_thumb'] = str_replace('.','_thumb.',$addarr['goods_img']);
			$GoodsImgsModel =  new GoodsImgsModel();
			//如果sku不为空，查询之前是否已上传过,则删除
			if (empty($addarr['sku_val']) == false){
				$where[] = ['goods_id','=',$addarr['goods_id']];
				$where[] = ['sku_val','=',$addarr['sku_val']];
				$imgObj = $GoodsImgsModel->where($where)->find();
				if (empty($imgObj) == false){
					unlink('.'.$imgObj['goods_thumb'],'.'.$imgObj['goods_img']);
					$imgObj->delete();
				}
			}
			$GoodsImgsModel->save($addarr);	
			$img_id = $GoodsImgsModel->img_id;
			if ($img_id < 1){
				$this->removeImg($file_url);//删除刚刚上传的
				$data['code'] = 0;
				$data['msg'] = '商品图片写入数据库失败！';
				return $this->ajaxReturn($data);
			}
			$data['code'] = 0;
            $data['msg'] = "上传成功";
            $data['image'] = array('id'=>$img_id,'thumbnail'=>$file_url,'path'=>$file_url);
			$data['savename'] = $result['info'][0]['savename'];
			$data['src'] = $file_url;
			return $this->ajaxReturn($data);
		}
		$result = $this->_upload($_FILES['imgFile'],'gdimg/');
        if ($result['error']) {
			$data['code'] = 1;
			$data['msg'] = $result['info'];
			return $this->ajaxReturn($data);
		}
		$result['url']= '/'.$result['info'][0]['savepath'].$result['info'][0]['savename'];
        return $this->ajaxReturn($result);
    }

    

	/**
     * 删除商品图片
     */
    public function removeImg($file='') {
		$img_id = input('post.id',0,'intval');
		if ($img_id > 0){
			$GoodsImgsModel = new GoodsImgsModel();
			$img = $GoodsImgsModel->find($img_id);
			if (empty($img)){
				return $this->error('没有找到相关图片.');
			}
			$file = $img->goods_img;
			$res = $img->delete();
			if ($res < 1){
				return $this->error('删除图片失败.');
			}
		}
		if (empty($file))  return $this->error('传值错误.');		
		unlink('.'.$file);
		unlink('.'.str_replace('.','_thumb.',$file));
		return $this->success('删除图片成功.');
    }
}
?>