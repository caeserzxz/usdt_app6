<?php

namespace app\ddkc\controller\api;
use think\Db;
use app\ApiController;
use app\member\model\UsersModel;
use think\cache\driver\Redis;
use app\ddkc\model\PaymentModel;
/*------------------------------------------------------ */
//-- 会员登陆、注册、找回密码相关API
/*------------------------------------------------------ */

class Center extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
    }
    /*------------------------------------------------------ */
    //-- 上传支付宝收款信息
    /*------------------------------------------------------ */
    public function add_alipay(){
        $PaymentModel = new PaymentModel();

        $data = input('post.');
        $file = $_FILES['alipay_payment_code'];
        $ios_file = input('alipay_payment_code');
        if(empty($data['alipay_number'])){
            return $this->ajaxReturn(['code' => 0,'msg' => '支付宝账号不能为空']);
        }
        if(empty($data['alipay_user_name'])){
            return $this->ajaxReturn(['code' => 0,'msg' => '支付宝真实姓名不能为空']);
        }
        if(empty($file['name'])&&empty($ios_file)){
            return $this->ajaxReturn(['code' => 0,'msg' => '请上传支付宝收款码','url' => '']);
        }

        #通过file提交的
        if($file){
            #上传打款凭证
            $path = upload_img('alipay_payment_code');
            if($path){
                $data['alipay_payment_code'] = $path;
            }
        }

        #IOS直接上传的地址
        if($ios_file){
            $data['payment_code'] = $ios_file;
        }

        $data['type'] = 2;
        $data['add_time'] = time();
        $data['user_id'] = $this->userInfo['user_id'];

        if($data['id']){
            #这里需求是不能修改
            #更新支付宝
            $res = $PaymentModel->where('id',$data['id'])->update($data);
        }else{
            #新增支付宝
            $res = $PaymentModel->insert($data);
        }

        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '支付宝收款信息上传成功']);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '支付宝收款信息上传失败']);
        }
    }
    /*------------------------------------------------------ */
    //-- 上传微信收款信息
    /*------------------------------------------------------ */
    public function add_wxpay(){
        $PaymentModel = new PaymentModel();

        $data = input('post.');
        $file = $_FILES['wx_payment_code'];
        $ios_file = input('wx_payment_code');
        if(empty($data['wx_number'])){
            return $this->ajaxReturn(['code' => 0,'msg' => '微信账号不能为空']);
        }
        if(empty($file['name'])&&empty($ios_file)){
            return $this->ajaxReturn(['code' => 0,'msg' => '请上传微信收款码','url' => '']);
        }

        #通过file提交的
        if($file){
            #上传打款凭证
            $path = upload_img('wx_payment_code');
            if($path){
                $data['wx_payment_code'] = $path;
            }
        }

        #IOS直接上传的地址
        if($ios_file){
            $data['wx_payment_code'] = $ios_file;
        }

        $data['type'] = 3;
        $data['add_time'] = time();
        $data['user_id'] = $this->userInfo['user_id'];
        if($data['id']){
            #更新支付宝
            $res = $PaymentModel->where('id',$data['id'])->update($data);
        }else{
            #新增支付宝
            $res = $PaymentModel->insert($data);
        }

        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '微信收款信息上传成功']);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '微信收款信息上传失败']);
        }
    }
    /*------------------------------------------------------ */
    //-- 上传银行卡收款信息
    /*------------------------------------------------------ */
    public function add_bank(){
        $PaymentModel = new PaymentModel();

        $data = input('post.');
        if(empty($data['bank_name'])){
            return $this->ajaxReturn(['code' => 0,'msg' => '银行卡名称不能为空']);
        }
        if(empty($data['card_number'])){
            return $this->ajaxReturn(['code' => 0,'msg' => '银行卡卡号不能为空']);
        }
        if(empty($data['sub_branch'])){
            return $this->ajaxReturn(['code' => 0,'msg' => '银行卡支行不能为空']);
        }
        if(empty($data['bank_user_name'])){
            return $this->ajaxReturn(['code' => 0,'msg' => '银行卡姓名不能为空']);
        }

        $data['type'] = 1;
        $data['add_time'] = time();
        $data['user_id'] = $this->userInfo['user_id'];
        if($data['id']){
            #更新银行卡
            $res = $PaymentModel->where('id',$data['id'])->update($data);
        }else{
            #新增银行卡
            $res = $PaymentModel->insert($data);
        }

        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '银行卡收款信息上传成功']);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '银行卡收款信息上传失败']);
        }
    }

    /**
     **上传图片base64_decode
     **/
    public function uploadimage(){
        //$base_img是获取到前端传递的src里面的值，也就是我们的数据流文件
        $base_img = $_POST['img'];
        $img_type = $_POST['img_type'];//图片文件夹名
        $img_name = $_POST['img_name'];//图片类型

        $base_img = str_replace('data:image/png;base64,', '', $base_img);
        //设置文件路径和文件前缀名称
        $path = '../public'.UPLOAD_PATH.'/'.$img_type."/".date(Ymd,time()).'/';
        is_dir($path) OR mkdir($path, 0777, true);
        $prefix='nx_';
        $output_file = $prefix.time().'.png';
        $path = $path.$output_file;
        $ifp = fopen( $path, "wb" );
        fwrite( $ifp, base64_decode( $base_img) );
        fclose( $ifp );
        //return date(Ymd,time()).'/'.$output_file;
        $return['path'] = UPLOAD_PATH.'/'.$img_type."/".date(Ymd,time()).'/'.$output_file;
        $return['img_type'] = $img_type;
        $return['img_name'] = $img_name;
        $this->ajaxreturn($return);
        //return $return;
    }

}