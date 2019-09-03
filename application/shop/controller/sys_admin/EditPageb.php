<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use think\Db;
use think\facade\Cache;


use app\shop\model\ShopPageTheme;
use app\shop\model\GoodsModel;

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
    //-- 选择商品
    /*------------------------------------------------------ */
    public function selProducts(){
        $search['pIds'] = input('pIds','','trim');
        $where[] = ' store_id = 0 '; // 搜索条件
        $search['cid'] = input('cid',0,'intval');
        // 关键词搜索
        $search['key_word'] = input('keyword') ? trim(input('keyword')) : '';
        if(empty($search['key_word']) == false){
            $where[] = " (goods_name like '%".$search['key_word']."%' or goods_sn like '%".$search['key_word']."%')" ;
        }
        $GoodsModel = new GoodsModel();
        $classList = $GoodsModel->getClassList();
        if($search['cid'] > 0){
            $where[] = ' cid in ('.$classList[$search['cid']]['children'].') ';
        }
        $where = join(' and ',$where);
        $count = $GoodsModel->where($where)->count('goods_id');

        $Page  = new \lib\AjaxPage($count,5);
        $show = $Page->show();
        $goodsList = $GoodsModel->where($where)->order('goods_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select()->toArray();
        foreach ($goodsList as $key=>$goods){
            $goods['url'] = url('shop/goods/info',['id'=>$goods['goods_id']]);
            $goodsList[$key] = $goods;
        }
        $pages = $this->pshow($Page->totalRows,$Page->totalPages,'selproducts',input('p') * 1);
        $this->assign('pages',$pages);
        $this->assign('goodsList',$goodsList);
        $this->assign("classListOpt", arrToSel($classList,$search['cid']));
        $this->assign('search',$search);
        return $this->fetch();
    }
    /**
     * 分页显示输出
     * @access public
     */
    public function pshow($totalRows,$totalPages,$url,$nowPage=1,$rollPage=5) {
        if(0 == $totalRows) return '';
        $middle         =   ceil($rollPage/2); //中间位置
        // 分析分页参数        
        $parameter  =  empty($_REQUEST) ? array() : $_REQUEST;
        $varPage = 'p' ;
        $parameter[$varPage]  =   '__PAGE__';
        unset($parameter['PHPSESSID']);
        $url            =   str_replace('+','%20',url($url,$parameter));
        //上下翻页字符串
        $upRow          =   $nowPage-1;
        $downRow        =   $nowPage+1;
        if ($upRow>0){
            $upPage     =   '<li><a href="'.str_replace('__PAGE__',$upRow,$url).'">«</a></li>';
        }else{
            $upPage     =   '<li class="disabled"><span>«</span></li>';
        }
        if ($downRow <= $totalPages){
            $downPage   =   '<li><a href="'.str_replace('__PAGE__',$downRow,$url).'">»</a></li>';
        }else{
            $downPage   =   '<li class="disabled"><span>»</span></li>';
        }
        // 1 2 3 4 5
        $linkPage = "";
        if ($totalPages != 1) {
            if ($nowPage < $middle) { //刚开始
                $start = 1;
                $end = $rollPage;
            } elseif ($totalPages < $nowPage + $middle - 1) {
                $start = $totalPages - $rollPage + 1;
                $end = $totalPages;
            } else {
                $start = $nowPage - $middle + 1;
                $end = $nowPage + $middle - 1;
            }
            $start < 1 && $start = 1;
            $end > $totalPages && $end = $totalPages;
            for ($page = $start; $page <= $end; $page++) {
                if ($page != $nowPage) {
                    $linkPage .= " <li><a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a></li>";
                } else {
                    $linkPage .= "<li class='active'><span>".$page."</span></li>";
                }
            }
        }else{
            $linkPage .= "<li class='active'><span>1</span></li>";
        }
        $pageStr     =   str_replace(
            array('%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%linkPage%','%end%'),
            array($nowPage,$totalRows,$totalPages,$upPage,$downPage,$linkPage,$theEnd),'<ul class="pagination">%upPage%%linkPage%%downPage%</ul>');
        return $pageStr;
    }
    /*------------------------------------------------------ */
    //-- 选定商品
    /*------------------------------------------------------ */
    public function productsAdd(){
        $pIds = input('pIds');
        if (empty($pIds)) return $this->error('请选择商品！');
        $GoodsModel = new GoodsModel();
        $where[] = ['goods_id','in',explode(',',$pIds)];
        $goods_list = $GoodsModel->where($where)->select()->toArray();
        $this->assign('goods_list',$goods_list);
        $result['html']= $this->fetch('products_add_list');
        $result['state'] = 1;
        return $this->ajaxReturn($result);
    }







}
