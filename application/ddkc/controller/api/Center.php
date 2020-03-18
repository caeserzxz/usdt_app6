<?php

namespace app\ddkc\controller\api;

use app\ApiController;
use app\member\model\UsersModel;
use app\mainadmin\model\ArticleModel;
use app\mainadmin\model\ArticleCategoryModel;
use app\ddkc\model\MiningUnsealingModel;
use app\member\model\UsersBindModel;
use app\distribution\model\DividendRoleModel;
use app\ddkc\model\PaymentModel;
use think\facade\Cache;

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
        $this->Model = new UsersModel();
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

    /*------------------------------------------------------ */
    //-- 团队统计
    /*------------------------------------------------------ */
    public function getTeamStatistics()
    {
        $userId = $this->userInfo['user_id'];
        $roleModel = new DividendRoleModel();
        $userBindModel = new UsersBindModel();

        # 各等级直推人数统计
        $allRole = $roleModel->field('role_id,role_name')->where(1)->select();
        foreach ($allRole as $key => $value) {
            # 该等级直推人数
            $allRole[$key]['subNum'] = $this->Model
                ->where(['pid' => $userId,'role_id' => $value['role_id']])
                ->count();
        }
        # 三层内各层级人数
        $where[] = ['pid','=',$userId];
        $where[] = ['level','<=',3];
        $threeInfo = $userBindModel->where($where)->group('level')->column('count(*) cc','level');
        $hierarchyText = ['其他','一','二','三'];


        $data['roleInfo'] = $allRole;
        $data['threeInfo'] = $threeInfo;
        $data['textInfo'] = $hierarchyText;
        return $this->ajaxReturn($data);
    }
    /*------------------------------------------------------ */
    //-- 上传头像
    /*------------------------------------------------------ */
    public function upload_user_img(){
        $userModel = new UsersModel();

        $file = $_FILES['head_pic'];
        $ios_file = input('head_pic');

        if(empty($file['name'])&&empty($ios_file)){
            return $this->ajaxReturn(['code' => 0,'msg' => '请上传头像','url' => '']);
        }

        # 通过file提交的
        if($file){
            #上传打款凭证
            $path = upload_img('head_pic');
            if($path){
                $data['head_pic'] = $path;
            }
        }

        # IOS直接上传的地址
        if($ios_file) $data['head_pic'] = $ios_file;
        $map['headimgurl'] = $data['head_pic'];

        # 更新头像
        $res = $userModel->where('user_id',$this->userInfo['user_id'])->update($map);

        if($res){
            Cache::set('user_info_mkey_'.$this->userInfo['user_id'],'',30);
            $user = $userModel->info($this->userInfo['user_id']);
            return $this->ajaxReturn(['code' => 1,'msg' => '头像修改成功','url'=> url('center/index')]);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '操作失败']);
        }
    }
}
