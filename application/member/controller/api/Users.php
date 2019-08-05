<?php

namespace app\member\controller\api;

use app\ApiController;
use think\facade\Lang;
use think\facade\Env;
use app\member\model\UsersModel;
use app\member\model\WithdrawModel;
use app\member\model\AccountLogModel;
use app\distribution\model\DividendModel;
use app\shop\model\OrderModel;
use app\shop\model\BonusModel;
use think\Db;
use lib\Image;
use app\weixin\model\MiniModel;

/*------------------------------------------------------ */
//-- 会员相关API
/*------------------------------------------------------ */

class Users extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->checkLogin();//验证登陆
        $this->Model = new UsersModel();
    }
    /*------------------------------------------------------ */
    //-- 获取登陆会员信息
    /*------------------------------------------------------ */
    public function getInfo()
    {
        $return['info'] = $this->userInfo;
        $return['code'] = 1;
        $return['sign_in'] = settings('sign_in');
        $superior = $this->Model->getSuperior($this->userInfo['pid']);
        if ($superior) $superior['reg_time'] = date('Y-m-d', $superior['reg_time']);
        $return['superior'] = $superior;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPwd()
    {
        $res = $this->Model->editPwd(input(), $this);
        if ($res !== true) return $this->error($res);
        return $this->success('密码已重置，请用新密码登陆.');
    }
    /*------------------------------------------------------ */
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPayPwd()
    {
        $pay_password = input('password','','trim');
        if (empty($pay_password))  return $this->error('请输入新的支付密码.');
        $this->checkCode('edit_pay_pwd',$this->userInfo['mobile'],input('code'));//验证短信验证
        $data['pay_password'] = f_hash($pay_password.$this->userInfo['user_id']);
        if ($data['pay_password'] == $this->userInfo['pay_password']){
            return $this->error('新密码与旧密码一致，无需修改.');
        }
        $res = $this->Model->where('user_id', $this->userInfo['user_id'])->update($data);
        if ($res < 1) {
            return $this->error('未知错误，处理失败.');
        }
        $this->_log($this->userInfo['user_id'], '用户修改支付密码.', 'member');
        return $this->success('支付密码修改成功.');
    }
    //*------------------------------------------------------ */
    //-- 绑定会员手机
    /*------------------------------------------------------ */
    public function bindMobile()
    {
        $this->checkCode('login', input('mobile'), input('code'));//验证短信验证
        $res = $this->Model->bindMobile(input(), $this);
        if ($res !== true) return $this->error($res);
        return $this->success('绑定手机成功.');
    }
    /*------------------------------------------------------ */
    //-- 获取会员中心首页所需数据
    /*------------------------------------------------------ */
    public function getCenterInfo()
    {
        $OrderModel = new OrderModel();
        $return['orderStats'] = $OrderModel->userOrderStats($this->userInfo['user_id']);
        $return['userInfo'] = $this->userInfo;
        $BonusModel = new BonusModel();
        $bonus = $BonusModel->getListByUser();
        $return['unusedNum'] = $bonus['unusedNum'];
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取分享二维码
    /*------------------------------------------------------ */
    public function myCode()
    {
        $file_path = config('config._upload_') . 'qrcode/' . substr($this->userInfo['user_id'], -1) . '/';
        $file = $file_path . $this->userInfo['token'] . '.png';
        if (file_exists($file) == false) {
            include EXTEND_PATH . 'phpqrcode/phpqrcode.php';//引入PHP QR库文件
            $QRcode = new \phpqrcode\QRcode();
            $value = config('config.host_path') . '/?share_token=' . $this->userInfo['token'];
            makeDir($file_path);
            $png = $QRcode::png($value, $file, "L", 10, 1, 2, true);
        }
        $return['file'] = config('config.host_path') . '/' . trim($file, '.');
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取分享商品二维码
    /*------------------------------------------------------ */
    public function goodsCode()
    {
        $goods_id = input('goods_id', 0, 'intval');
        $file_path = config('config._upload_') . 'goods_qrcode/' . $goods_id . '/';
        $file = $file_path . $this->userInfo['token'] . '.png';
        if (file_exists($file) == false) {
            include EXTEND_PATH . 'phpqrcode/phpqrcode.php';//引入PHP QR库文件
            $QRcode = new \phpqrcode\QRcode();
            $value = config('config.host_path') . url('shop/goods/info', ['id' => $goods_id, 'share_token' => $this->userInfo['token']]);
            makeDir($file_path);
            $png = $QRcode::png($value, $file, "L", 10, 1, 2, true);
        }
        $return['file'] = config('config.host_path') . '/' . trim($file, '.');
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 获取会员帐号数据
    /*------------------------------------------------------ */
    public function getAccount()
    {
        $return['account'] = $this->userInfo['account'];
        //计算提现中金额，即为冻结金额
        $WithdrawModel = new WithdrawModel();
        $where[] = ['user_id', '=', $this->userInfo['user_id']];
        $where[] = ['status', '=', 0];
        $return['frozen_amount'] = $WithdrawModel->where($where)->sum('amount');
        //end
        $DividendModel = new DividendModel();
        //今天收益
        unset($where);
        $where[] = ['dividend_uid', '=', $this->userInfo['user_id']];
        $where[] = ['status', 'in', [1, 2, 3, 9]];
        $where[] = ['add_time', '>=', strtotime("today")];
        $return['today_income'] = $DividendModel->where($where)->sum('dividend_amount');
        $return['today_income'] += $DividendModel->where($where)->sum('dividend_bean');
        //end
        //本月收益
        unset($where);
        $where[] = ['dividend_uid', '=', $this->userInfo['user_id']];
        $where[] = ['status', 'in', [1, 2, 3, 9]];
        $where[] = ['add_time', '>', strtotime(date('Y-m-01', strtotime('-1 month')))];
        $return['month_income'] = $DividendModel->where($where)->sum('dividend_amount');
        $return['month_income'] += $DividendModel->where($where)->sum('dividend_bean');
        //累计收益
        unset($where);
        $where[] = ['dividend_uid','=',$this->userInfo['user_id']];
        $where[] = ['status','in',[1,2,3,9]];
        $return['end_income'] = $DividendModel->where($where)->sum('dividend_amount');
        $return['end_income'] += $DividendModel->where($where)->sum('dividend_bean');
        $return['end_income'] = number_format($return['end_income'],2);
        //end
        $return['withdraw_status'] = settings('withdraw_status');//获取是否开启提现
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取会员帐户变动日志
    /*------------------------------------------------------ */
    public function getAccountLog()
    {
        $type = input('type', 'order', 'trim');
        $time = input('time', '', 'trim');
        if (empty($time)) {
            $time = date('Y年m月');
        }
        $return['time'] = $time;
        $_time = strtotime(str_replace(array('年', '月'), array('-', ''), $time));
        $return['code'] = 1;
        $AccountLogModel = new AccountLogModel();
        $where[] = ['user_id', '=', $this->userInfo['user_id']];
        $where[] = ['change_time', 'between', array($_time, strtotime(date('Y-m-t', $_time)) + 86399)];


        $rows = $AccountLogModel->where($where)->order('change_time DESC')->select();
        foreach ($rows as $key => $row) {

            if ($row['balance_money'] > 0) {
                $return['b_income'] += $row['balance_money'];
            } else {
                $return['b_expend'] += $row['balance_money'] * -1;
            }

            if ($row['use_integral'] > 0) {
                $return['i_income'] += $row['use_integral'];
            } else {
                $return['i_expend'] += $row['use_integral'] * -1;
            }

            if ($type == 'order'){
                if ($row['change_type'] != 3){
                    continue;
                }
            }elseif($type == 'brokerage'){
                if ($row['change_type'] != 4){
                    continue;
                }
            }elseif($type == 'withdraw'){
                if ($row['change_type'] != 5){
                    continue;
                }
            }elseif($type == 'use_integral'){
                if ($row['use_integral'] == 0){
                    continue;
                }
            }

            if ($row['balance_money'] > 0) {
                $row['value'] = '+' . $row['balance_money'];
            } elseif ($row['balance_money'] < 0) {
                $row['value'] = $row['balance_money'];
            }

            if ($row['use_integral'] > 0) {
                $row['value'] = '+' . $row['use_integral'];
            } elseif ($row['use_integral'] < 0){
                $row['value'] = $row['use_integral'];
            }

            $row['_time'] = timeTran($row['change_time']);
            $return['list'][] = $row;
        }
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 获取会员佣金日志
    /*------------------------------------------------------ */
    public function getDividendLog()
    {
        $type = input('type', 'all_balance_money', 'trim');
        $time = input('time', '', 'trim');
        if (empty($time)) {
            $time = date('Y年m月');
        }
        $return['time'] = $time;
        $_time = strtotime(str_replace(array('年', '月'), array('-', ''), $time));
        $return['code'] = 1;
        $DividendModel = new DividendModel();
        $where[] = ['dividend_uid', '=', $this->userInfo['user_id']];
        switch ($type) {
            case 'all_balance_money'://佣金
                $where[] = ['add_time', 'between', array($_time, strtotime(date('Y-m-t', $_time)) + 86399)];
                $where[] = ['dividend_amount', '<>', 0];
                break;
            case 'wait_balance_money'://等待分佣金
                $where[] = ['add_time', 'between', array($_time, strtotime(date('Y-m-t', $_time)) + 86399)];
                $where[] = ['dividend_amount', '<>', 0];
                $where[] = ['status', '=', 3];
                break;
            case 'arrival_balance_money'://金币已到帐明细
                $where[] = ['add_time', 'between', array($_time, strtotime(date('Y-m-t', $_time)) + 86399)];
                $where[] = ['dividend_amount', '<>', 0];
                $where[] = ['status', '=', 9];
                break;
            case 'cancel_balance_money'://金币失效明细
                $where[] = ['add_time', 'between', array($_time, strtotime(date('Y-m-t', $_time)) + 86399)];
                $where[] = ['dividend_amount', '<>', 0];
                $where[] = ['status', 'in', [1, 4]];
                break;
            case 'dividend_bean'://旅游豆
                $where[] = ['dividend_bean', '<>', 0];
                $where[] = ['status', '=', 9];
                $where[] = ['is_hide', '=', 0];
                $where[] = ['update_time', '>', time() - 172800];//只显示最近两到帐的佣金
                break;
            default:
                return $this->error('类型错误.');
                break;
        }
        $return['income'] = 0;
        $rows = $DividendModel->where($where)->order('add_time DESC')->select();

        $lang = lang('order');
        foreach ($rows as $key => $row) {
            $income = $row['dividend_amount'] > 0 ? $row['dividend_amount'] : $row['dividend_bean'];
            $return['income'] += $income;
            $row['_time'] = timeTran($row['add_time']);
            $row['value'] = $income;
            $row['nick_name'] = userInfo($row['buy_uid']);
            $row['status'] = $lang['ds'][$row['status']];
            $return['list'][] = $row;
        }
        return $this->ajaxReturn($return);
    }
    /*------------------------------------------------------ */
    //-- 修改会员信息
    /*------------------------------------------------------ */
    public function editInfo()
    {
        $imgfile = input('imgfile');
        if (empty($imgfile) == false) {
            $file_path = config('config._upload_') . 'headimg/' . substr($this->userInfo['user_id'], -1) . '/';
            makeDir($file_path);
            $file_name = $file_path . random_str(12) . '.jpg';
            file_put_contents($file_name, base64_decode(str_replace('data:image/jpeg;base64,', '', $imgfile)));
            $upArr['headimgurl'] = trim($file_name, '.');
        }
        $upArr['nick_name'] = input('nick_name', '', 'trim');
        if (empty($upArr['nick_name']) == true) {
            return $this->error('请填写用户昵称.');
        }

        $where[] = ['nick_name', '=', $upArr['nick_name']];
        $where[] = ['user_id', '<>', $this->userInfo['user_id']];
        $count = $this->Model->where($where)->count('user_id');
        if ($count > 0) return '昵称：' . $upArr['nick_name'] . '，已存在.';
        $upArr['signature'] = input('signature', '', 'trim');
        $upArr['sex'] = input('sex', '男', 'trim');
        $upArr['sex'] = $upArr['sex'] == '男' ? 1 : 0;
        $upArr['birthday'] = input('birthday', '', 'trim');
        $upArr['show_mobile'] = input('show_mobile', 0, 'intval');
        //验证数据是否出现变化
        $dbarr = $this->Model->field(join(',',array_keys($upArr)))->where('user_id',$this->userInfo['user_id'])->find()->toArray();
        $this->checkUpData($dbarr,$upArr,true);

        $res = $this->Model->upInfo($this->userInfo['user_id'], $upArr);
        if ($res < 1) {
            @unlink($file_name);
            return $this->error('修改用户信息失败，请重试.');
        }
        return $this->success('修改成功.');
    }
    /*------------------------------------------------------ */
    //-- 获取远程会员头像到本地
    /*------------------------------------------------------ */
    public function getHeadImg()
    {
        $headimgurl = $this->userInfo['headimgurl'];
        if (empty($headimgurl) == false){
            if (strstr($headimgurl,'http')){
                $headimgurl = strstr($headimgurl,'https')?str_replace("https","http",$headimgurl):$headimgurl;
                $file_path = config('config._upload_').'headimg/'.substr($this->userInfo['user_id'], -1) .'/';
                makeDir($file_path);
                $file_name = $file_path.random_str(12).'.jpg';
                downloadImage($headimgurl,$file_name);
                $upArr['headimgurl'] = $headimgurl = trim($file_name,'.');
                (new UsersModel)->upInfo($this->userInfo['user_id'],$upArr);
            }
        }
        $return['headimgurl'] = $headimgurl;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 获取会员帐户变动日志-----改
    /*------------------------------------------------------ */
    public function AccountLog()
    {
        $type = input('type','order','trim');
        $time = input('time','','trim');
        $p = input('p',1);
        $limit = 10;
        $offset = ($p-1)*$limit;
        if (empty($time)){
            $time = date('Y年m月');
        }
        $return['time'] = $time;
        $_time = strtotime(str_replace(array('年','月'),array('-',''),$time));
        $return['code'] = 1;
        $AccountLogModel = new AccountLogModel();
        $where[] = ['user_id','=',$this->userInfo['user_id']];
        switch($type){
            case 'order'://订单相关
                $where[] = ['change_type','=',3];
                break;
            case 'brokerage'://佣金相关
                $where[] = ['change_type','=',4];
                break;
            case 'withdraw'://提现相关
                $where[] = ['change_type','=',5];
                break;
            case 'integral'://积分相关
                $where[] = ['use_integral','<>',0];
                break;
            default:
                return $this->error('类型错误.');
                break;
        }
        $where[] = ['change_time','between',array($_time,strtotime(date('Y-m-t',$_time))+86399)];
        $rows = $AccountLogModel->where($where)->limit($offset,$limit)->order('change_time DESC')->select();
        foreach ($rows as $key=>$row){
            //if ($row['bean_value'] > 0) {
            //    $return['income'] += $row['balance_money'];
            //}
            if ($row['balance_money'] != 0){
                if ( $row['balance_money'] > 0){
                    if ($row['change_type'] == 4){
                        $return['income'] += $row['balance_money'];
                    }
                    $return['expend'] += $row['balance_money'];
                    $row['value'] = '+'.$row['balance_money'];
                }else{

                    $row['value'] = $row['balance_money'];
                }
            }elseif ($row['use_integral'] != 0){
                if ( $row['use_integral'] > 0){
                    $return['expend'] += $row['use_integral'];
                    $row['value'] = '+'.$row['use_integral'];
                }else{
                    $return['income'] += $row['use_integral'];
                    $row['value'] = $row['use_integral'];
                }
            }else{
                continue;
            }
            $row['_time'] = timeTran($row['change_time']);
            $return['list'][] = $row;
        }
        $return['list'] = [];
        $return['income'] = $AccountLogModel->where($where)->where('balance_money','gt',0)->sum('balance_money');
        $return['expend'] = $AccountLogModel->where($where)->where('balance_money','lt',0)->sum('balance_money');
        $return['income'] = $return['income']?$return['income']:0;
        $return['expend'] = !empty($return['expend'])?$return['expend']:0;
        $return['list'] = $rows;
        return $this->ajaxReturn($return);
    }
    public function wechat_qrcode()
    {
        $user = $this->userInfo;
        $headimgurl = input('headimgurl','','trim');
        //判断缩略图是否存在
        $path = config('config._upload_').'qrcode/'.$user['user_id'].'/';


        $bgimages = settings('share_bg');//"./static/images/backgroundimg.jpg";

        $bgimage = substr($bgimages,1);
        $bgimg_name = strstr(end(explode('/',substr($bgimage,1))), '.', TRUE);

        $one_name =  md5($user['user_id'].'_'.date('Y')."_one").'_'.$bgimg_name.'.png';
        $two_name =  md5($user['user_id'].'_'.date('Y')."_two").'_'.$bgimg_name.'.png';
        $three_name =  md5($user['user_id'].'_'.date('Y')."_three").'_'.$bgimg_name.'.png';

        $list = [];
        $userqrcode = $this->get_user_mini_qrcode();
        $image = \think\Image::open($userqrcode);
        $qrthumbimage = $user['user_id']."_qrcode_".date("Y")."_thumb".'.png';
        $image->thumb(250, 250)->save($path.$qrthumbimage);
        $h_name = $user['user_id'].'_'.date('Y')."_head_thumb".'.png';
        $headimg = \think\Image::open('.'.$headimgurl);
        $headimg->thumb(180, 180)->save($path.$h_name);

        $one = $this->dowaterimg($bgimage,$path.$qrthumbimage,$path . $one_name,1,$path.$h_name,$user); //第一张
        $two = $this->dowaterimg($bgimage,$path.$qrthumbimage,$path . $two_name,2,$path.$h_name,$user); //第二张
        $three = $this->dowaterimg($bgimage,$path.$qrthumbimage,$path . $three_name,3,$path.$h_name,$user); //第三张
        array_push($list,$one,$two,$three);
        unlink($userqrcode);
        unlink($path.$qrthumbimage);
        unlink($path.$h_name);
        $this->deldir($path,$bgimg_name);
        $return['list'] = $list;
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    //获取用户小程序二维码
    public function get_user_mini_qrcode(){

        $user = $this->userInfo;

        //$page = 'pages/authorizeLogin/authorizeLogin';
        $page = '';
        $scene = $user['token'];
        $mini = new MiniModel();
        $qrcode = $mini->get_qrcode($page,$scene);
        $imageName = $user['user_id']."_qrcode_".date("Y").'.png';
        if (strstr($qrcode,",")){
            $qrcode = explode(',',$qrcode);
            $qrcode = $qrcode[1];
        }
        $path = config('config._upload_').'qrcode/'.$user['user_id'];
        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
            mkdir($path,0777,true);
        }
        $imageSrc= $path."/". $imageName; //图片名字
        if (is_file($imageSrc )){
            return  $imageSrc ;
        }
        file_put_contents($imageSrc, base64_decode($qrcode));//返回的是字节数

        return $imageSrc;
    }
    //生成水印图
    public function dowaterimg($bgimage,$qrcode,$pathname,$type,$header_img,$user){
        // 已经生成过这个比例的图片就直接返回了
        if (is_file($pathname)){
            return  $pathname ;
        }
        if($type == 1){
            $location1 = \think\Image::WATER_CENTER; //二维码居中
            $location2 =  [50,50];
            $location3 =  [200,140];
            $location4 =  [200,90];
        }elseif($type == 2){
            $location1 = \think\Image::WATER_SOUTH;//二维码下居中
            $location2 =  [380,50];
            $location3 =  [360,250];
            $location4 =  [360,200];
        }else{
            $location1 = [600,700];
            $location2 =  [30,900];
            $location3 =  [180,990];
            $location4 =  [180,940];
        }
        $image = \think\Image::open($bgimage);

        $image->water($qrcode, $location1,100)->save($pathname);
        if($header_img){
            $image->water($header_img, $location2,100)->save($pathname);
        }
        $image->text("昵称 ".$user['nick_name'], "hgzb.ttf", 30,"#000",$location3)->save($pathname);
        $image->text("ID ".$user['user_id'], "hgzb.ttf", 30,"#000",$location4)->save($pathname);
        return $pathname;
    }
    //清空文件夹函数和清空文件夹后删除空文件夹函数的处理
    function deldir($path,$bgimg_name){
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                //排除目录中的.和..
                if($val !="." && $val !=".."){
                    //如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        ////子目录中操作删除文件夹和文件
                        //
                        //deldir($path.$val.'/');
                        //
                        ////目录清空后删除空文件夹
                        //
                        //@rmdir($path.$val.'/');
                    }else{
                        //如果是之前生成图片直接删除
                        $code_name = strstr(end(explode('_',$val)), '.', TRUE);
                        if($bgimg_name != $code_name){
                            unlink($path.$val);
                        }
                    }
                }
            }
        }
    }

    /*------------------------------------------------------ */
    //-- 获取背景图片列表
    /*------------------------------------------------------ */
    public function getBkImgList(){
        $default_img = settings('GoodsImages');
        $arr = explode(',', $default_img);
        $return['code'] = 1;
        $return['data'] = $arr;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 获取签到数据
    /*------------------------------------------------------ */
    public function getSignData(){

        $year = input('year',date('Y'));
        $month = input('month',date('n'));

        $data = Db::table('users_sign')->where(['user_id'=>$this->userInfo['user_id'],'year'=>$year,'month'=>$month])->find();
        if(!$data){
            $data['days'] = '';
        }
        $return['code'] = 1;
        $return['data'] = $data;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 今日签到
    /*------------------------------------------------------ */
    public function doSign(){

        $year = date('Y');
        $month = date('n');
        $day = date('d');
        $AccountLogModel = new AccountLogModel();
        $sigin = settings('sign_integral');
        $user_id = $this->userInfo['user_id'];

        $data = Db::table('users_sign')->where(['user_id'=>$user_id,'year'=>$year,'month'=>$month])->find();
        if($data&&isset($data['days'])){
            $tmp_arr = explode(',',$data['days']);
            if(in_array($day, $tmp_arr)){
                $this->error('今天已经签到,请勿重复签到！');
            }
        }



        Db::startTrans();
        if($data){
            //本月数据已有,直接更新
            $upData = ['days'=>$data['days'].','.$day,'last_time'=>time(),'score'=>$sigin];
            $res = Db::table('users_sign')->where(['id'=>$data['id']])->update($upData);
        }else{
            //本月数据没有,进行插入
            $inData = [
                'user_id' => $user_id,
                'year'    => $year,
                'month'   => $month,
                'days'    => $day,
                'score'   => $sigin,
                'last_time'=> time()
            ];
            $res = Db::table('users_sign')->insert($inData);
        }

        if(!$res){
            Db::rollback();
            $this->error('签到失败,请联系管理员');
        }

        //签到成功，加积分
        $accData['change_desc'] = '签到获得积分';
        $accData['change_type'] = 10;
        $accData['by_id'] = 0;
        $accData['use_integral'] = $sigin;
        $res = $AccountLogModel->change($accData,$user_id,false);

        if(!$res){
            Db::rollback();
            $this->error('签到失败,积分处理异常');
        }

        Db::commit();
        $return['code'] = 1;
        $return['msg'] = '签到成功';
        $return['score'] = $sigin;
        return $this->ajaxReturn($return);
    }
}
