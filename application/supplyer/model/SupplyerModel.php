<?php
namespace app\supplyer\model;
use app\BaseModel;
use think\facade\Cache;
use think\facade\Session;
//*------------------------------------------------------ */
//-- 供货商
/*------------------------------------------------------ */
class SupplyerModel extends BaseModel
{
	protected $table = 'supplyer';
	public  $pk = 'supplyer_id';
	protected $mkey = 'supplyer_mkey';
     /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */
	public function cleanMemcache(){
		Cache::rm($this->mkey);
	}
    /*------------------------------------------------------ */
    //-- 用户登录
    /*------------------------------------------------------ */
    public function login($data)
    {
        // 验证用户名密码是否正确
        if (!$supplyer = self::useGlobalScope(false)->where([
            'supplyer_name' => $data['user_name'],
            'password' => _hash($data['password'])
        ])->find()) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        if ($supplyer['is_ban'] == 1){
            $this->error = '登录失败, 帐号已被封禁.';
            return false;
        }
        $this->saveDate(['supplyer_id'=>$supplyer['supplyer_id'],'login_time'=>time(),'login_ip'=>request()->ip(),'last_login_time'=>$supplyer['login_time'],'last_login_ip'=>$supplyer['login_ip']],'login');


        // 保存登录状态
        Session::set('supplyer_admin', [
            'info' => [
                'supplyer_id' => $supplyer['supplyer_id'],
                'supplyer_name' => $supplyer['supplyer_name']
            ],
            'is_login' => true,
        ]);
        return true;
    }

    /*------------------------------------------------------ */
    //-- 退出登陆
    /*------------------------------------------------------ */
    public  function logout(){
        Session('supplyer_admin',null);
        return true;
    }
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
    public function getRows(){
		$list = Cache::get($this->mkey);
		if (empty($list) == false) return $list;
		$rows = $this->order('update_time DESC')->select()->toArray();
		if (empty($rows)) return array();
		foreach ($rows as $row){
			$list[$row['supplyer_id']] = $row;
		}
		Cache::set($this->mkey,$list,3600);
		return $list;
	}
    //*------------------------------------------------------ */
    //-- 更新数据
    /*------------------------------------------------------ */
    public function saveDate($data,$type = ''){
        if ($type != 'login'){
            $data['update_time'] = time();
        }
        $res = $this->save($data,$data['supplyer_id']);
        if ($res == true){
            if ($type == 'login'){//登陆处理
                (new LogLoginModel)->save(['log_ip'=>$data['login_ip'],'log_time'=>$data['login_time'],'supplyer_id'=>$data['supplyer_id']]);
            }
        }
        return $res;
    }
}
