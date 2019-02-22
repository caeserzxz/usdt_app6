<?php
namespace app;
use app\BaseController;
use think\facade\Cache;
use think\facade\Session;

error_reporting(E_ERROR | E_PARSE );


/**
 * API控制器基类
 * Class BaseController
 * @package app\store\controller
 */
class ApiController extends BaseController
{
  
    /**
     * 后台初始化
     */
    public function initialize()
    {
        global $userInfo;
        //记录请求数量，如果10秒，请求超过30次，限制1分钟不允许请求
        $ajaxDataLog = Session::get('ajaxDataLog');
        $time = time();
        if (empty($ajaxDataLog)==false) {
            if ($ajaxDataLog['limit'] == 1 && $ajaxDataLog['time'] > $time - 60){
                return $this->ajaxReturn(['code'=>0,'msg'=>'请求太频繁，请休息一下再操作..']);
            }
            if ($ajaxDataLog['time'] > $time - 10){
                $ajaxDataLog['num']++;
                if ($ajaxDataLog['num'] >= 30){
                    $ajaxDataLog['limit'] = 1;
                }
            }else{
                $ajaxDataLog['limit'] = 0;
                $ajaxDataLog['num'] = 0;
                $ajaxDataLog['time'] = $time;
            }
            Session::set('ajaxDataLog',$ajaxDataLog);
        }else{
            session::set('ajaxDataLog',['time'=>$time,'num'=>1,'limit'=>0]);
        }
        $userInfo = $this->getLoginInfo();
        $this->userInfo = $userInfo;
		parent::initialize();
    }

    /*------------------------------------------------------ */
    //-- 验证登录状态
    /*------------------------------------------------------ */
    public function checkLogin(){
        if (empty($this->userInfo) || $this->userInfo['user_id'] < 1) return $this->error('请登陆后再操作.');
        return true;
    }
    /*------------------------------------------------------ */
    //-- 验证短信验证码
    /*------------------------------------------------------ */
    public function checkCode($type,$mobile,$code){
        $sms_fun = settings('sms_fun');
        if ($sms_fun[$type] == 0) {//未开启，即成为验证成功
            return true;
        }
        $codeCache = Cache::get('code_'.$type.$mobile);
        if (empty($codeCache) || empty($code) ||  $code != $codeCache ){
            return $this->error('验证码错误.');
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 验证各类请求次数
    /*------------------------------------------------------ */
    public function checkPostLimit($type){
        $postLimit = session('checkPostLimit_'.$type);
        $time = time();
        if (empty($postLimit)==false) {
            if ($postLimit['limit'] == 1 && $postLimit['time'] > $time - 1800) {//封停30分钟
                return $this->error('请求次数过多，请休息一下.');
            }
            if ($postLimit['time'] > $time - 60){
                $postLimit['num']++;
                if ($postLimit['num'] >= 10){
                    $postLimit['limit'] = 1;
					$postLimit['time'] = $time;
                }
            }else{
                $postLimit['limit'] = 0;
                $postLimit['num'] = 0;
                $postLimit['time'] = $time;
            }
            session('checkPostLimit_'.$type,$postLimit);
        }else{
            session('checkPostLimit_'.$type,['time'=>$time,'num'=>1,'limit'=>0]);
        }
        return true;
    }
}
