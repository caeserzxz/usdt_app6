<?php
namespace app\member\controller\api;
use think\Db;
use think\facade\Cache;
use app\ApiController;
use app\member\model\UsersModel;
use app\member\model\AccountLogModel;
use app\member\model\WithdrawAccountModel;
/*------------------------------------------------------ */
//-- 提现相关API
/*------------------------------------------------------ */

class Center extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
		$this->checkLogin();//验证登陆
        $this->Model = new WithdrawAccountModel();
    }
    /*------------------------------------------------------ */
    //-- 我的签到
    /*------------------------------------------------------ */
    /*------------------------------------------------------ */
    public function signIndex()
    {
    	$user = $this->userInfo;

    	$UsersModel = new UsersModel;
        $return['code'] = 1;
    	$return['timeData'] = time();
    	$return['use_integral'] = $UsersModel->signIntegral();
        $return['is_sign']  = $UsersModel->isSign($user['user_id']);
        $return['signTime'] = $UsersModel->signTime($user['user_id'],1);
        $return['signData'] = $UsersModel->signIndex($user['user_id'],1);
		return $this->ajaxReturn($return);
    }
    //-- 签到记录
    /*------------------------------------------------------ */
    public function signInfo()
    {
        $user = $this->userInfo;

        $page  = input('page');
        $timeY = input('timeY');
        $timeM = input('timeM');
        $month = $timeY."-".$timeM;//查询年月
	    $date[0] = strtotime($month);//指定月份月初时间戳
	    $date[1] = mktime(23, 59, 59, date('m', strtotime($month))+1, 00);//指定月份月末时间戳

        $UsersModel = new UsersModel;
        $res['info'] = $UsersModel->signInfos($user['user_id'], $date, $page, 10);

        $data[0] = date('Y年m月',time());
        $data[1] = strtotime(date('Y-m-d', time()) . '00:00:00');
        $data[2] = strtotime(date('Y-m-d', time()) . '23:59:59');
        $res['data'] = $data;
        $res['code'] = 1;
        return $this->ajaxReturn($res);
    }
	/*------------------------------------------------------ */
    //-- 签到
    /*------------------------------------------------------ */
    public function signIng()
    {
    	$user = $this->userInfo;
    	$UsersModel = new UsersModel;
        $data = $UsersModel->signIng($user['user_id']);
        if ($data == true) {
        	$res['code'] = 1;
        	$res['msg'] = '签到成功';
        	return $this->ajaxReturn($res);
        }
	}
}
