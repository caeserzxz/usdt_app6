<?php

namespace app\weixin\model;
use app\BaseModel;
use think\facade\Cache;
use think\Db;
use app\weixin\model\MiniModel;
use app\member\model\UsersModel;
use app\member\model\LogLoginModel;
//*------------------------------------------------------ */
//-- 微信会员
/*------------------------------------------------------ */
class MiniUsersModel extends BaseModel
{
	protected $table = 'weixin_users';
	public  $pk = 'wxuid';
	protected $mkey = 'weixin_users_mkey_';
	/*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($wxuid=0){
        Cache::rm($this->mkey.'_'.$wxuid);
    }
	/*------------------------------------------------------ */
	//-- 微信小程序用户查询，不存在则写入数据
	/*------------------------------------------------------ */
    public function login($data){
		if (empty($data)) return '参数不能为空';
		$mini = new MiniModel();
		$minidata = $mini->GetOpenidFromMp($data['code']);

		if(!$minidata['openid']){
			return 'openid获取失败，请联系管理员';
		}

		$wx_info = Db::table('weixin_users')->where('wx_openid',$minidata['openid'])->find();

		if($wx_info){
			$userInfo = Db::table('users')->where('user_id',$wx_info['user_id'])->find();
			if($userInfo){
				return $this->do_login($userInfo['user_id'],$data['source']);
	    	}else{
	    		$userInArr['sex'] = $data['gender'];
				$userInArr['nick_name'] = $data['nickName'];
				$userInArr['headimgurl'] = $data['avatarUrl'];
				$res = (new UsersModel)->register($userInArr,$this->wxuid);//注册会员
				if ($res != true) return $res;
				//缓存微信账户数据 
				$this->info($this->wxuid,'wx');
				$wx_info = Db::table('weixin_users')->where('wxuid',$this->wxuid)->find();
				return $this->do_login($wx_info['user_id'],$data['source']);
	    	}
		}
		$sex_arr = ['未知','男','女'];
		//没有数据，执行注册会员
		$inarr['user_id'] = 0;
		$inarr['wx_openid'] = $minidata['openid'];		
		$inarr['add_time'] = $inarr['update_time'] = time();
		$inarr['sex'] = $sex_arr[$data['gender']];
		$inarr['subscribe'] = 0;
		$inarr['wx_nickname'] = $data['nickName'];
		$inarr['wx_headimgurl'] = $data['avatarUrl'];
		$inarr['wx_city'] = $data['city'];
		$inarr['wx_province'] = $data['province'];	
		Db::startTrans();		
		$res = $this->save($inarr);
		if ($res < 1) return '数据入库失败';

		$userInArr['sex'] = $data['gender'];
		$userInArr['nick_name'] = $data['nickName'];
		$userInArr['headimgurl'] = $data['avatarUrl'];
		$res = (new UsersModel)->register($userInArr,$this->wxuid);//注册会员
		if ($res != true) return $res;
		//缓存微信账户数据 
		$this->info($this->wxuid,'wx');
		$wx_info = Db::table('weixin_users')->where('wxuid',$this->wxuid)->find();
		return $this->do_login($wx_info['user_id'],$data['source']);
	}

	/*------------------------------------------------------ */
	//-- 小程序登录兼容系统登录
	/*------------------------------------------------------ */
	protected function do_login($user_id,$source=''){
		$userInfo = Db::table('users')->where('user_id',$user_id)->find();
		$time = time();
		$upData['login_odd_num'] = 0;//登陆异常清空
        $upData['login_time'] = $time;
        $upData['login_ip'] = request()->ip();
        $upData['last_login_time'] = $userInfo['login_time'];
        $upData['last_login_ip'] = $userInfo['login_ip'];
        Db::table('users')->where('user_id', $userInfo['user_id'])->update($upData);
        session('userId', $userInfo['user_id']);
        $LogLoginModel = new LogLoginModel();
        $inLog['log_ip'] = $upData['login_ip'];
        $inLog['log_time'] = $time;
        $inLog['user_id'] = $userInfo['user_id'];
        $LogLoginModel->save($inLog);
        $userModel = new UsersModel();
        $this->userInfo = $userModel->info($userInfo['user_id']);//附值全局

        //判断订单模块是否存在
        if (class_exists('app\shop\model\OrderModel')) {
            //执行订单自动签收
            (new \app\shop\model\OrderModel)->autoSign($userInfo['user_id']);
            (new \app\shop\model\CartModel)->loginUpCart($userInfo['user_id']);//更新购物车
        }


        $data['source'] = $source;
        if ($data['source']){
            if ($data['source'] == 'developers'){
                $devtoken = random_str(10).date(s);
                Cache::set('devlogin_'.$devtoken,$userInfo['user_id'],86400 * 7);
                return [$data['source'],$devtoken,$userInfo];
            }
        }

        return ['H5',$userInfo['user_id']];
	}
		
	
	/*------------------------------------------------------ */
	//-- 获取微信用户信息
	/*------------------------------------------------------ */
    public function info($id=0,$type='user'){
		if (empty($id)) return false;

		$info = Cache::get($this->mkey.$type.$id);
		if ($info) return $info;
		if ($type == 'user'){
            $where[] = ['user_id','=',$id];
        }else{
            $where[] = ['wxuid','=',$id];
        }
		$info = $this->where($where)->find();
		if (empty($info)) return [];
		$info = $info->toArray();
		Cache::set($this->mkey.$type.$id,$info,60);
		return $info;//如何存直接返回
	}
	/*------------------------------------------------------ */
	//-- 微信关联用户ID
	/*------------------------------------------------------ */
    public function bindUserId($wxuid,$user_id = 0){
		if ($user_id < 1 || $wxuid < 1) return false;
		$uparr['update_time'] = time();
		$uparr['user_id'] = $user_id;
		return $this->where('wxuid',$wxuid)->update($uparr);
	}
}
