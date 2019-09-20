<?php
/*------------------------------------------------------ */
//-- 合并图片处理
//-- @author iqgmy
/*------------------------------------------------------ */
namespace lib;

class MergeImg{
    /*------------------------------------------------------ */
    //-- 合成分享海报
    /*------------------------------------------------------ */
	public function shareImg($data = [],$fileName = ''){
	    if (empty($data)){
	        return false;
        }
        $fontfile = PATH_SEPARATOR == ';' ? app()->getRootPath().'public/static/share/msyhbd.ttc' : './static/share/msyhbd.ttc';
        $imagecreate = $this->isjpg($data['share_bg']);
        $im_bg = $imagecreate('.'.$data['share_bg']);
        $imagesx = imagesx($im_bg);
        $imagesy = imagesy($im_bg);
        imagesavealpha($im_bg, true);
        $im = imagecreatetruecolor($imagesx, $imagesy);
        imagecopy($im, $im_bg, 0, 0, 0,0,$imagesx,$imagesy);
        //头像处理
		//缩放比例
        list($width, $height) = getimagesize($data['share_avatar']);
        $imagecreate = $this->isjpg($data['share_avatar']);
        $wx_himg = $imagecreate($data['share_avatar']);
        if ($data['share_avatar_shape'] == 0){
            $wx_himg = $this->createRoundImg($wx_himg,$width, $height);
        }
        $per = round($data['share_avatar_width']/$width,3);
        $n_w = $width*$per;
        $n_h = $height*$per;
        $him = imagecreatetruecolor($n_w, $n_h);
        //2.上色
        $color = imagecolorallocate($him,255,255,255);
        //3.设置透明
        imagecolortransparent($him,$color);
        imagefill($him,0,0,$color);
        imagecopyresampled($him, $wx_himg,0, 0,0, 0,$n_w, $n_h, $width, $height);
        //缩放比例end
        $share_avatar_xy = explode(',',$data['share_avatar_xy']);
        imagecopymerge($im, $him, $share_avatar_xy[0],$share_avatar_xy[1],0, 0,$n_w,$n_h, 100);
        imagedestroy($him);
        //头像处理end

        //呢称
        $share_nick_name_xy = explode(',',$data['share_nick_name_xy']);
        $rgb = $this->hex2rgb($data['share_nick_name_color']);
        $balk = imagecolorallocate($im, $rgb['red'],  $rgb['green'], $rgb['blue']);
        imagettftext($im, $data['share_nick_name_size'], 0, $share_nick_name_xy[0] , $share_nick_name_xy[1] ,$balk, $fontfile, $data['share_nick_name']);
        //呢称

        //二维码处理
        //缩放比例end
        list($width, $height) = getimagesize($data['share_qrcode']);
        $imagecreate = $this->isjpg($data['share_qrcode']);
        $qrcode = $imagecreate($data['share_qrcode']);
        $per = round($data['share_qrcode_width']/$width,3);
        $n_w = $width*$per;
        $n_h = $height*$per;
        $qim = imagecreatetruecolor($n_w, $n_h);
        imagecopyresampled($qim, $qrcode,0, 0,0, 0,$n_w, $n_h, $width, $height);
        //缩放比例end
        $share_qrcode_xy = explode(',',$data['share_qrcode_xy']);
        imagecopymerge($im, $qim, $share_qrcode_xy[0],$share_qrcode_xy[1],0, 0,$n_w,$n_h, 100);
        imagedestroy($qim);
        if (empty($fileName)){
            ob_clean();
            //二维码处理
            header("Content-type: image/jpeg");
            // 输出合成图片
            imagejpeg($im);
            imagedestroy($im);
            exit;
        }
        if ($fileName == -1){
            ob_start();
            imagejpeg($im);
            $img = ob_get_contents();
            ob_end_clean();
            return base64_encode($img);
        }
        imagejpeg($im,$fileName,100);
        return true;
	}
    /*------------------------------------------------------ */
    //-- 判断是哪种图片
    /*------------------------------------------------------ */
    function isjpg($pic_path) {
        $pathInfo = pathinfo($pic_path);
        switch (strtolower($pathInfo['extension'])) {
            case 'jpg':
            case 'jpeg':
                $imagecreatefromjpeg = 'imagecreatefromjpeg';
                break;
            case 'png':
                $imagecreatefromjpeg = 'imagecreatefrompng';
                break;
            case 'gif':
            default:
                $imagecreatefromjpeg = 'imagecreatefromstring';
                break;
        }
        return $imagecreatefromjpeg;
    }
    /*------------------------------------------------------ */
    //-- 图片划圆
    /*------------------------------------------------------ */
    function createRoundImg($src_img,$w,$h) {
        $image = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, $bg);
        $r = $w / 2;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x-$r) * ($x-$r) + ($y-$r) * ($y-$r)) < ($r*$r))) {
                    imagesetpixel($image, $x, $y, $rgbColor);
                }
            }
        }

        return $image;
    }
    /*------------------------------------------------------ */
    //-- 颜色hex值转换成rgb
    /*------------------------------------------------------ */
    function hex2rgb( $colour = '#000') {
        if ( $colour[0] == '#' ) {
            $colour = substr( $colour, 1 );
        }
        if ( strlen( $colour ) == 6 ) {
            list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        } elseif ( strlen( $colour ) == 3 ) {
            list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
        } else {
            return false;
        }
        $r = hexdec( $r );
        $g = hexdec( $g );
        $b = hexdec( $b );
        return array( 'red' => $r, 'green' => $g, 'blue' => $b );
    }
}
?> 