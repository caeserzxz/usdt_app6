<?php
namespace app\member\model;
use app\BaseModel;
use think\facade\Cache;

use app\shop\model\CartModel;

//*------------------------------------------------------ */
//-- 会员表
/*------------------------------------------------------ */
class UsersModel extends BaseModel
{
	protected $table = 'users';
	protected $mkey = 'user_info_mkey_';
	public  $pk = 'user_id';
	/*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($user_id){
        Cache::rm($this->mkey.$user_id);
		Cache::rm($this->mkey.'account_'.$user_id);
    }
    /*------------------------------------------------------ */
    //-- 会员登陆
    /*------------------------------------------------------ */
    public function login($data = array())
    {
        $res = $this->checkPwd($data['password']);
        if ($res !== true) return '密码不正确，格式错误.';
        $password = f_hash($data['password']);
        $mobile = $data['mobile'] * 1;
        $userInfo = $this->where('mobile',$mobile)->find();
        if (empty($userInfo)){
            return '用户不存在.';
        }
        $time = time();
        if ($userInfo['login_odd_num'] >= 10 ){
            if ($userInfo['login_odd_time'] > $time - 3600){
                return '密码错误次数过多帐号封停，解封时间：'.date('Y-m-d H:i:s',$userInfo['login_odd_time']+3600);
            }else{
                $userInfo['login_odd_num'] = 7;//如果已到解封时间，给3次机会再登陆
            }
        }
        if ($userInfo['password'] != $password){
            //记录异常登陆
            $this->where('user_id',$userInfo['user_id'])->update(['login_odd_time'=>$time,'login_odd_num'=>$userInfo['login_odd_num']+1]);
            return '用户或密码不正确.';
        }
        $upData['login_odd_num'] = 0;//登陆异常清空
        $upData['login_time'] = $time;
        $upData['login_ip'] = request()->ip();
        $upData['last_login_time'] = $userInfo['login_time'];
        $upData['last_login_ip'] = $userInfo['login_ip'];
        $this->where('user_id',$userInfo['user_id'])->update($upData);
        session('userId',$userInfo['user_id']);
        $LogLoginModel = new LogLoginModel();
        $inLog['log_ip'] = $upData['login_ip'];
        $inLog['log_time'] = $time;
        $inLog['user_id'] = $userInfo['user_id'];
        $LogLoginModel->save($inLog);
        $this->userInfo = $this->info($userInfo['user_id']);//附值全局
        $CartModel = new CartModel();
        $CartModel->loginUpCart($userInfo['user_id']);//更新购物车
        return $userInfo['user_id'];
    }

    /*------------------------------------------------------ */
    //-- 验证密码强度
    /*------------------------------------------------------ */
    private  function checkPwd($pwd){
        $pwd = trim($pwd);
        if (empty($pwd)) {
            return '密码不能为空';
        }
        if (strlen($pwd) < 8) {//必须大于8个字符
            return '密码必须大于八字符';
        }
        if (preg_match("/^[0-9]+$/", $pwd)) { //必须含有特殊字符
            return '密码不能全是数字，请包含数字，字母大小写或者特殊字符';
        }
        if (preg_match("/^[a-zA-Z]+$/", $pwd)) {
            return '密码不能全是字母，请包含数字，字母大小写或者特殊字符';
        }
        if (preg_match("/^[0-9A-Z]+$/", $pwd)) {
            return '请包含数字，字母大小写或者特殊字符';
        }
        if (preg_match("/^[0-9a-z]+$/", $pwd)) {
            return '请包含数字，字母大小写或者特殊字符';
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 生成用户唯一标识,主要用于分享后身份识别
    /*------------------------------------------------------ */
    public function getToken(){
        $token = random_str(16);
        $count = $this->where('token',$token)->count('user_id');
        if ($count >= 1) return $this->getToken();
        return $token;
    }
    /*------------------------------------------------------ */
    //-- 会员注册
    /*------------------------------------------------------ */
    public function register($inArr = array())
    {
        if (empty($inArr)){
            return '获取注册数据失败.';
        }
        if (empty($inArr['mobile'])){
            return '请填写手机号码';
        }
        if (checkMobile($inArr['mobile']) == false){
            return '手机号码不正确.';
        }
        $count = $this->where('mobile', $inArr['mobile'])->count('user_id');
        if ($count > 0) return '手机号码：'.$inArr['mobile'].'，已存在.';
        if (empty($inArr['nick_name']) == false) {//昵称不为空时，判断是否已存在
            $count = $this->where('nick_name', $inArr['nick_name'])->count('user_id');
            if ($count > 0) return '昵称：'.$inArr['nick_name'].'，已存在.';
        }
        $res = $this->checkPwd($inArr['password']);//验证密码强度
        if ($res !== true){
            return $res;
        }
        $time = time();
        $inArr['password'] = f_hash($inArr['password']);
        $inArr['token'] = $this->getToken();
        $inArr['reg_time'] = $time;
        $share_token = session('share_token');
        if (empty($share_token) == false){//分享注册
                $pInfo = $this->getShareUser($share_token,false);
                if ($pInfo['is_ban'] != 1) {
                    $inArr['pid'] = $pInfo['user_id']*1;
                }
        }
        $res = $this->save($inArr);
        if ($res < 1) return '未知错误，写入会员数据失败.';		
		
        //创建会员帐户信息
        $AccountModel = new AccountModel();
        $AccountModel->createData(['user_id'=>$this->user_id,'update_time'=>$time]);
        //edn
        //捆绑微信会员信息
        $wxuid = session('wxuid');
        if ($wxuid > 0){
            $WeiXinUsersModel = new \app\weixin\model\WeiXinUsersModel();
            $WeiXinUsersModel->bindUserId($wxuid,$this->user_id);
        } //end
        //写入等级关联
        $this->regUserBind($this->user_id,$inArr['pid']);
        return true;
    }
	/*------------------------------------------------------ */
    //-- 找回用户密码
    /*------------------------------------------------------ */
    public function forgetPwd($data = array(),&$obj)
    {
        if (empty($data)){
            return '获取数据失败.';
        }
        if (empty($data['mobile'])){
            return '请填写手机号码';
        }
        if (checkMobile($data['mobile']) == false){
            return '手机号码不正确.';
        }        
        $res = $this->checkPwd($data['password']);//验证密码强度
        if ($res !== true){
            return $res;
        }
		$user = $this->where('mobile',$data['mobile'])->find(); 
        if (f_hash($data['password']) == $user['password']){
			return '新密码与旧密码一致,请核实.';	
		}
        $upArr['password'] = f_hash($data['password']);        
        $res = $this->where('user_id',$user['user_id'])->update($upArr);
        if ($res < 1) return '未知错误，修改会员密码失败.';
		$obj->_log($res,'用户找回密码.','member');		
        return true;
    }
	/*------------------------------------------------------ */
    //-- 修改用户密码
    /*------------------------------------------------------ */
    public function editPwd($data = array(),&$obj)
    {
        if (empty($data)){
            return '获取数据失败.';
        }               
        $res = $this->checkPwd($data['password']);//验证密码强度
        if ($res !== true){
            return $res;
        }
		$user = $this->where('user_id',$this->userInfo['user_id'])->find(); 
		$oldPwd = f_hash($data['old_password']);
		if ($oldPwd != $user['password'] ){
			return '旧密码错误.';
		}
		$upArr['password'] = f_hash($data['password']);
        if ($upArr['password'] == $user['password']){
			return '新密码与旧密码一致无须修改.';	
		}                
        $res = $this->where('user_id',$user['user_id'])->update($upArr);
        if ($res < 1) return '未知错误，修改会员密码失败.';
		$obj->_log($res,'用户修改密码.','member');		
        return true;
    }
	/*------------------------------------------------------ */
	//-- 获取用户信息
	//-- val 查询值
	//-- type 查询类型
	//-- isCache 是否调用缓存  type = user_id 时，才生效
	/*------------------------------------------------------ */
	public function info($val,$type = 'user_id',$isCache = true){
		if (empty($val)) return false;
		if ($isCache == true) $info = Cache::get($this->mkey.$val);
		if (empty($info) == false) return $info;
		if ($type == 'token'){
			$info = $this->where('token',$val)->find()->toArray();
		}else{
            $info = $this->where('user_id',$val)->find()->toArray();
			$AccountModel = new AccountModel();
            $account = $AccountModel->where('user_id',$val)->find();
			if (empty($account) == true){
				//创建会员帐户信息				
				$AccountModel->createData(['user_id'=>$val,'update_time'=>time()]);
				$account = $AccountModel->where('user_id',$val)->find();				
			}
			$info['account'] = $account->toArray();
		}
		unset( $info['password']);
        $info['level'] = userLevel($info['total_integral'],false);//获取等级信息
		Cache::set($this->mkey.$val,$info,30);
		return $info;
	}
	/*------------------------------------------------------ */
	//--获取会员帐户
	/*------------------------------------------------------ */ 
	public function getAccount($user_id,$isCache = true){
		$user_id = $user_id * 1;
		if ($user_id < 1) return array();
		$mkey = $this->mkey.'account_'.$user_id;
		if ($isCache == true) $info = Cache::get($mkey);
		if (empty($info) == false) return $info;
		$info = $this->where('u.user_id',$user_id)->alias('u')->field('u.user_id,u.mobile,ua.total_integral,ua.total_dividend,ua.balance_money,ua.use_integral')->join('users_account ua','u.user_id = ua.user_id','left')->find()->toArray();
		Cache::set($mkey,$info,600);
		return $info;
	}	
	/*------------------------------------------------------ */
	//-- 更新会员信息
	/*------------------------------------------------------ */ 
	public function upInfo($user_id,$data){
		$user_id = $user_id * 1;
		$res = $this->where('user_id',$user_id)->update($data);
		$this->cleanMemcache($user_id);
    	return $res;
	}	

	/*------------------------------------------------------ */
	//-- 获取会员下级汇总
	/*------------------------------------------------------ */ 
	public function userShareStats($user_id=0,$isCache = true){
		$info = Cache::get($this->mkey.'_us_'.$user_id);	
		if ($isCache == true && empty($info) == false) return $info;
		$user_id = $user_id*1;
        $UsersBind = new UsersBindModel();
		$rows = $UsersBind->field("count('user_id') as num,level")->where('pid',$user_id)->group('level')->select();
		foreach ($rows as $row){
			$info['all_num'] += $row['num'];
			$info[$row['level']] = $row['num'];
		}
		Cache::set($this->mkey.'_us_'.$user_id,$info,30);
    	return $info;
	}
	/*------------------------------------------------------ */
	//-- 写入等级关联
	/*------------------------------------------------------ */ 
	public function regUserBind($user_id=0,$pid=0){
        $DividendSatus = settings('DividendSatus');
        if ($DividendSatus == 0) return true;//不开启推荐，不执行
        if ($user_id == 0 || $pid == 0) return false;
        $UsersBind = new UsersBind();
        $d_level = config('config.DIVIDEND_LEVEL');
        foreach ($d_level as $key=>$val){
            if ($pid < 1) break;
            $spid[] = $pid;
            if ($key<=2){
                $sendUids[$pid] = $val;
            }
            $inArr['level'] = $key;
            $inArr['user_id'] = $user_id;
            $inArr['pid'] = $pid;
            $UsersBind::create($inArr);
            $pid = $this->where('user_id',$pid)->value('pid');
        }
        $spid[] = $user_id;
        sort($spid);
        $this->where('user_id',$user_id)->update(['spid'=>$spid]);
		return true;
	}
}