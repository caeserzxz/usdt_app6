<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
use think\facade\Session;

/**
 * 管理用户模型
 * Class StoreUser
 * @package app\store\model
 */
class AdminUserModel extends BaseModel
{
	protected $table = 'main_admin_user';
	public $pk = 'user_id';
	protected static $mkey = 'main_admin_user_list';
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm(self::$mkey);
	}

	 /*------------------------------------------------------ */
	//-- 用户登录
	/*------------------------------------------------------ */ 
    public function login($data)
    {
        // 验证用户名密码是否正确
        if (!$user = self::useGlobalScope(false)->where([
            'user_name' => $data['user_name'],
            'password' => _hash($data['password'])
        ])->find()) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
		if ($user['status'] == 0){
			$this->error = '登录失败, 帐号已被封禁.';
            return false;
		}
       $this->saveDate(['user_id'=>$user['user_id'],'login_time'=>time(),'login_ip'=>request()->ip(),'last_login_time'=>$user['login_time'],'last_login_ip'=>$user['login_ip']],'login');
	   $AdminRoleModel = new AdminRoleModel();
	   $roleInfo = $AdminRoleModel->find($user['role_id']);
	   $actionArr = 'all';
	   if ($roleInfo['role_action'] != 'all'){
		   $actionArr = [];
		   $role_action = explode(',',$roleInfo['role_action']);
		   foreach ($role_action as $action){
			   $action = explode('|',$action);
			   $key = $action[0].'|'.$action[1];
			   $actionArr[$key][] = $action[2];
		   }
	   }
	    Cache::rm('main_menu_priv_list_'.$user['user_id']);//清理菜单缓存
        // 保存登录状态
        Session::set('main_admin', [
            'info' => [
                'user_id' => $user['user_id'],
                'user_name' => $user['user_name'],
				'role_action' => $actionArr
            ],
            'is_login' => true,
        ]);
        return true;
    }
	
	/*------------------------------------------------------ */
	//-- 退出登陆
	/*------------------------------------------------------ */ 
	public  function logout(){
		Cache::rm('main_menu_priv_list_'.AUID);//清理菜单缓存
		Session('main_admin',null);
		return true;
	}
 	/*------------------------------------------------------ */
	//-- 获取用户列表
	/*------------------------------------------------------ */ 
	public  function getRows(){
		$data = Cache::get(self::$mkey);
		if (empty($data) == false){
			return $data;
		}
		$rows = $this->select()->toArray();
		
		foreach ($rows as $row){
			$data[$row['user_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
  
	/*------------------------------------------------------ */
	//-- 用户信息
	/*------------------------------------------------------ */ 
    public static function info($user_id)
    {
		$data = self::getList();
        return empty($data[$user_id])?$data[$user_id]:[];
    }
	//*------------------------------------------------------ */
	//-- 更新数据
	/*------------------------------------------------------ */
	public function saveDate($data,$type = ''){		 
		 if ($type != 'login'){
			$data['update_time'] = time();
		 }
		 $res = $this->save($data,$data['user_id']);
		 if ($res == true){	
			 if ($type == 'login'){//登陆处理
				$logModel = new LogLoginModel();
				$logModel->save(['log_ip'=>$data['login_ip'],'log_time'=>$data['login_time'],'user_id'=>$data['user_id']]);
			 }elseif($type == 'editPwd'){//修改密码处理
				 $logModel = new LogSysModel();	
				 $logModel->save(['edit_id'=>$data['user_id'],'log_info'=>'修改管理员密码']);
		 	}
		 }
		 return $res;
	 }
	
	

}
