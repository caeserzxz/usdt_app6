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
    public function register($access_token,$sessionkey = ''){
		if (empty($access_token)) return false;		
		$wx_info = $this->where('wx_openid',$access_token['openid'])->find();		
		if (empty($wx_info)== false){
			return $wx_info;
		}
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
		if ($type == 'user') $map['user_id'] = $id;	
		else $map['wxuid'] = $id;
		$info = $this->where($map)->find();
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
