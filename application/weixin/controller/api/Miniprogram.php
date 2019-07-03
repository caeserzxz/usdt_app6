<?php
/*------------------------------------------------------ */
//-- 微信小程序
//-- Author: wemk
/*------------------------------------------------------ */
namespace app\weixin\controller\api;
use app\ApiController;

use think\Db;
use app\weixin\model\MiniModel;
use app\weixin\model\MiniUsersModel;
class Miniprogram  extends ApiController{

	public function initialize(){
        parent::initialize();
    }

    /*------------------------------------------------------ */
	//-- 小程序登录
	/*------------------------------------------------------ */
	public function do_login(){
        $post = input('post.');
        if(!$post['code']){
            $this->error('参数错误!');
        }
        if($post['share_token']){
            session('share_token',$post['share_token']);
        }
        $mini = new MiniUsersModel();
        $res = $mini->login($post);
        if (is_array($res) == false) return $this->error($res);
        $data['code'] = 1;
        $data['msg'] = '登录成功.';
        if ($res[0] == 'developers'){
            $data['developers'] = $res[1];
            $data['mobile'] = $res[2]['mobile'];
        }
        return $this->ajaxReturn($data);
    }
}?>