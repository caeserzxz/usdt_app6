<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use think\Db;
use think\facade\Cache;
use app\publics\model\LinksModel;


/*------------------------------------------------------ */
//-- 商城装修
/*------------------------------------------------------ */
class EditPageb extends AdminController
{
    /*------------------------------------------------------ */
    //-- 主页编辑
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign('title', '魔幻装修');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 主页编辑
    /*------------------------------------------------------ */
    public function edit()
    {
        $this->assign('title', '魔幻装修');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 上传介面
    /*------------------------------------------------------ */
    public function upload()
    {
        $name = input('name','','trim');
        $this->assign('name', $name);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 上传图片
    /*------------------------------------------------------ */
    public function uploadImg(){
        $name = input('name','','trim');
        $arrType = array('image/jpg','image/gif','image/png','image/bmp','image/jpeg');
        $max_size = '500000';      // 最大文件限制（单位：byte）
        $filepath = config('config._upload_').'adorn/'.$name; //图片目录路径
        $file= $_FILES[$name];
        if(!is_uploaded_file($file['tmp_name'])){ //判断上传文件是否存在
            return $this->error('上传文件失败！');
        }
        if($file['size']>$max_size){  //判断文件大小是否大于500000字节
            return $this->error('上传文件太大！');
        }
        if(!in_array($file['type'],$arrType)){  //判断图片文件的格式
            return $this->error('上传文件格式不对！');
        }
        $ftype=explode('.',$file['name']);
        $picName= $filepath."/".date('ymdhis').rand(10,99).'.'.$ftype[1];
        makeDir($filepath);
        if(!move_uploaded_file($file['tmp_name'],$picName)){
            return $this->error('上传文件出错！');
        }
        $result['status'] = 0;
        $result['url'] = '/'.$picName;
        return $this->ajaxReturn($result);

    }
    /*------------------------------------------------------ */
    //-- 裁剪图片
    /*------------------------------------------------------ */
    public function resize(){
        $limit_width = 600;
        $file_name = input('file_name','','trim');
        $file_name = trim($file_name,'/');
        $name = input('name','','trim');
        $filepath= config('config._upload_').'adorn/'.$name;
        $cropped_width = input('w','','intval');
        $cropped_height = input('h','','intval');
        $source_x = input('x','0','float');
        $source_y = input('y','0','float');
        $source_info   = getimagesize($file_name);
        $source_width  = $source_info[0];
        $source_height = $source_info[1];
        $source_mime   = $source_info['mime'];
        $result['state'] = 0;
        if (!in_array($name,array("slide","logo","ads","exttypeset"))){
            return $this->error('上传类型错误！');
        }

        switch ($source_mime)
        {
            case 'image/gif':
                $source_image = imagecreatefromgif($file_name);
                imagesavealpha($source_image, true);
                break;
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($file_name);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($file_name);
                imagesavealpha($source_image, true);
                break;
            default:
                return $this->error('无法处理此图片！');
                break;
        }

        //超出大小限制，对图片进行放大处理后再裁剪
        if ($source_width != $limit_width){
            $default_w = $limit_width;
            $default_h = $default_w / $source_width * $source_height;
            $default_image  = imagecreatetruecolor($default_w, $default_h);

            imagealphablending($default_image,false);
            imagesavealpha($default_image,true);//这里很重要,意思是不要丢了$thumb图像的透明色;


            imagecopyresampled($default_image, $source_image, 0, 0, 0, 0, $default_w, $default_h, $source_width, $source_height);//对图片缩放为指定宽度
            $source_image = $default_image;
            unset($default_image);
        }
        $cropped_image  = imagecreatetruecolor($cropped_width, $cropped_height);
        imagesavealpha($source_image, true);
        imagealphablending($cropped_image,false);
        imagesavealpha($cropped_image,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
        imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height); //进行裁图

        if ($name == "logo"){
            $ext_h = 60;
            $ext_w = $ext_h / $cropped_height * $cropped_width;
        }

        //额外限制大小再次缩放
        if (isset($ext_h)){
            $ext_image  = imagecreatetruecolor($ext_w, $ext_h);
            imagesavealpha($cropped_image, true);
            imagealphablending($ext_image,false);
            imagesavealpha($ext_image,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
            imagecopyresampled($ext_image, $cropped_image, 0, 0, 0, 0, $ext_w, $ext_h, $cropped_width, $cropped_height);//对图片缩放为指定宽度
            $cropped_image = $ext_image;
            unset($ext_image);
        }
        @unlink($file_name);
        //end
        $file_name = end(explode('/',$file_name));
        $file_name=explode('.',$file_name);
        $file_name = $filepath.'/'.$file_name[0].'_c'.'.'.$file_name[1];
        makeDir($filepath);
        switch ($source_mime)
        {
            case 'image/gif':
                imagegif($cropped_image, $file_name);
                break;
            case 'image/jpeg':
                imagejpeg($cropped_image, $file_name);
                break;
            case 'image/png':
                imagepng($cropped_image, $file_name);
                break;
        }

        $result['status'] = 0;
        $result['url'] = '/'.$file_name;
        return $this->ajaxReturn($result);
    }

    /*------------------------------------------------------ */
    //-- 选定链接
    /*------------------------------------------------------ */
    public function links(){
        $this->assign('links', (new LinksModel)->links());
        $CategoryModel = new \app\shop\model\CategoryModel();
        $this->assign('CategoryList', $CategoryModel->getRows());
        return response($this->fetch());
    }

    /*------------------------------------------------------ */
    //-- 搜索
    /*------------------------------------------------------ */
    public function search()
    {
        $type = input('type','','trim');
        $kw = input('kw','','trim');
        if (in_array($type,['good','article']) == false){
            return $this->error('请求错误.');
        }
        if ($type == 'good'){
            $GoodsModel = new \app\shop\model\GoodsModel();
            $where[] = ['goods_name','like','%'.$kw.'%'];
            $ids = $GoodsModel->where($where)->limit(20)->column('goods_id');
            foreach ($ids as $id){
                $list[] = $GoodsModel->info($id);
            }
        }elseif($type == 'article'){
            $ArticleModel = new \app\mainadmin\model\ArticleModel();
            $where[] = ['title','like','%'.$kw.'%'];
            $list = $ArticleModel->where($where)->limit(20)->select()->toArray();
        }
        $this->assign('list',$list);
        $this->assign('kw',$kw);
        return response($this->fetch($type));
    }



}
