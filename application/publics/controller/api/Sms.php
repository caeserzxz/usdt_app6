<?php
namespace app\publics\controller\api;
use app\ApiController;
use think\facade\Cache;
use app\mainadmin\model\SmsTplModel;
use app\member\model\UsersModel;
use think\facade\Session;
/*------------------------------------------------------ */
//-- 短信相关API
/*------------------------------------------------------ */
class Sms extends ApiController{

	/*------------------------------------------------------ */
	//-- 发送验证短信
	/*------------------------------------------------------ */
 	public function sendCode(){
 	    $this->checkPostLimit('sendCode');//验证请求次数
        $time = time();
		$type = input('type','','trim');
      	$sms_fun = settings('sms_fun');
		if ($sms_fun[$type] != 1) return $this->error('相关短信未开通.');
        $smsTpl = SmsTplModel::getRows();
        $smsTpl =  $smsTpl[$type];
        if (empty($smsTpl['sms_tpl_code'])) return $this->error('相关短信未设置.');
        $mobile = input('mobile','','trim');
        if (empty($mobile) == true){
            $mobile = $this->userInfo['mobile'];
        }else{
            if (checkMobile($mobile) == false)  return $this->error('手机号码不正确.');
        }
        $sendLogMkey = 'sendLog_'.$mobile;
        $sendLog = Cache::get($sendLogMkey);
        if (empty($sendLog)) $sendLog = ['num'=>0,'time'=>$time,'limit'=>0];
        if ($sendLog['limit'] == 1 ){//视为攻击行为，禁止指定分钟(缓存时长即禁止时长)
            return $this->error('不能频繁向同一号码发送短信.');
        }

        $codemkey = 'code_'.$type.$mobile;
        $code = Cache::get($codemkey);
        if (empty($code) == false) return $this->error('短信已发送，请稍后再试.');
        //查询手机号码是否存在
        $usersModel = new UsersModel();
        if (in_array($type,['login','forget_password'])){
            $count = $usersModel->where('mobile',$mobile)->count('user_id');
            if ($count < 1 ) return $this->error('手机号码不存在.');
        }elseif(in_array($type,['register'])){
            $count = $usersModel->where('mobile',$mobile)->count('user_id');
            if ($count > 0 ) return $this->error('手机号码已存在.');
        }
        $fun = str_replace('/','\\','/sms/'.$sms_fun['function']);
        $Class  = new $fun($sms_fun['function_val']);
        //生成随机码
        $code = rand(1000,9999);

        $res = $Class->send($mobile,$smsTpl['sms_tpl_code'],['code'=>$code]);
        if ($res !== true) $this->error($res);
        Cache::set($codemkey,$code,300);//验证码缓存

        //单个号码发送记录处理
        $sendLog['num'] += 1;
        if ($sendLog['num']>10 && $sendLog['time'] > $time - 1200){
            $sendLog['limit'] = 1;//同一号码20分钟发送达10次，视为攻击行为，禁止执行
        }
        Cache::get($sendLogMkey,$sendLog,1800);
        //单个号码发送记录处理end
        return  $this->success();
	}

}
