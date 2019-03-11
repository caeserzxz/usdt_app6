<?php

namespace app\weixin\model;
use app\BaseModel;
use think\facade\Cache;
use app\weixin\model\WeiXinModel;
use app\member\model\UsersModel;
//*------------------------------------------------------ */
//-- 微信会员
/*------------------------------------------------------ */
class WeiXinUsersModel extends BaseModel
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
	//-- 微信用户查询，不存在写入数据
	/*------------------------------------------------------ */
    public function login($access_token){
		if (empty($access_token)) return false;		
		$wx_info = $this->where('wx_openid',$access_token['openid'])->find();		
		if (empty($wx_info)== false){
			return $wx_info;
		}
		//没有数据，执行注册会员
		$WeiXinModel = new WeiXinModel();		
		//获取相关微信帐号信息
		$wx_arr = $WeiXinModel->getWxUserInfo($access_token);
		$inarr['user_id'] = 0;
		$inarr['wx_openid'] = $access_token['openid'];		
		$inarr['add_time'] = $inarr['update_time'] = time();
		$inarr['sex'] = $wx_arr['sex'];
		$inarr['subscribe'] = $wx_arr['subscribe'];
		$inarr['wx_nickname'] = $wx_arr['nickname'];
		$inarr['wx_headimgurl'] = $wx_arr['headimgurl'];
		$inarr['wx_city'] = $wx_arr['city'];
		$inarr['wx_province'] = $wx_arr['province'];	
		if (settings('register_status') == 2){//微信自动注册会员
			Db::startTrans();		
			$wxuid = $this->save($inarr);		
			if ($wxuid < 1) return false;
			$userInArr['sex'] = $wx_arr['sex'] == '男' ? 1 : 2;
			$userInArr['nick_name'] = $wx_arr['nickname'];
			$userInArr['headimgurl'] = $wx_arr['headimgurl'];
			$res = (new UsersModel)->register($userInArr,$this->wxuid);//注册会员
			if ($res != true) return $res;
			return $this->info($wxuid,'wx');
		}
		$wxuid = $this->save($inarr);		
		if ($wxuid < 1) return false;
		return $this->info($wxuid,'wx');
	}
		
	
	/*------------------------------------------------------ */
	//-- 获取微信用户信息
	/*------------------------------------------------------ */
    public function info($id=0,$type='user'){
		if (empty($id)) return false;
		$info = Cache::get($this->mkey.$type.$id);
		if ($info) return $info;
		if ($type == 'user') $where['user_id'] = ['user_id','=',$id];
		else $where[] = ['wxuid','=',$id];
		$info = $this->where($where)->find();
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
