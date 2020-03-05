<?php

namespace app\mining\controller\api;

use app\ApiController;
use app\member\model\UsersModel;
use app\mainadmin\model\ArticleModel;
use app\mainadmin\model\ArticleCategoryModel;
use app\mining\model\MiningUnsealingModel;
/*------------------------------------------------------ */
//-- 会员登陆、注册、找回密码相关API
/*------------------------------------------------------ */

class Passport extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new UsersModel();
    }
   
    /*------------------------------------------------------ */
    //-- 用户登陆
    /*------------------------------------------------------ */
    public function login()
    {
        $this->checkPostLimit('login');//验证请求次数
        $this->checkCode('login',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->login(input());
        if (is_array($res) == false) return $this->error($res);
        $data['code'] = 1;
        $data['msg'] = '登录成功.';
        if ($res[0] == 'developers'){
            $data['developers'] = $res[1];
        }
//        $data['url'] = session('REQUEST_URI');
        $data['url'] = url('mining/index/index');
        return $this->ajaxReturn($data);
    }

    /*------------------------------------------------------ */
    //-- 注册用户
    /*------------------------------------------------------ */
    public function register()
    {
		$register_status = settings('register_status');
		if ($register_status != 1){
			return $this->error('暂不开放注册.');
		}        
        $this->checkCode('register',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->register(input());
        if ($res !== true) return $this->error($res);
        $appType = session('appType');
        if($appType=='IOS'||$appType=='Android'){
            return $this->success('注册成功.',url('mining/Passport/login'));
        }else{
            $this->success('注册成功.',url('publics/download/app'));
        }
    }
	/*------------------------------------------------------ */
    //-- 找回用户密码
    /*------------------------------------------------------ */
    public function forgetPwd()
    {
        $this->checkCode('forget_password',input('mobile'),input('code'));//验证短信验证
        $res = $this->Model->forgetPwd(input(),$this);
        if ($res !== true) return $this->error($res);		
        return $this->success('密码已重置，请用新密码登陆.');
    }
    /*------------------------------------------------------ */
    //-- 文章咨询列表
    /*------------------------------------------------------ */
    public function getArticleList(){
        $articleModel = new ArticleModel();
        $categoryModel = new ArticleCategoryModel();

        $post = input('post.');
        $cid = $post['cid'];
        $page = 10;
        $data['list'] = $articleModel
            ->where(['cid' => $cid,'is_show' => 1])
            ->order('id DESC')
            ->limit($post['p']*$page,$page)
            ->select();
        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['add_date'] = date('Y-m-d H:i:s',$value['add_time']);
        }
        # 四大分类名称
        $data['category_list'] = $categoryModel->field('id,name')->where([['id','>=',15],['id','<=',18]])->select();
        return $this->ajaxReturn($data);
    }
    /*------------------------------------------------------ */
    //-- 文章详情
    /*------------------------------------------------------ */
    public function getArticleDetail(){
        $id = input('id');
        $articleModel = new ArticleModel();
        $data = $articleModel->where(['id' => $id])->find();
        $data['add_date'] = date('Y-m-d H:i:s',$data['add_time']);
        $data['add_date'] = date('Y-m-d H:i:s',$data['add_time']);
        $data['code'] = 1;
        if (!$data) $data['code'] = 0;
        return $this->ajaxReturn($data);
    }

    /*------------------------------------------------------ */
    //-- 请求帮助解封
    /*------------------------------------------------------ */
    public function unsealing(){
        $UsersModel = new UsersModel();
        $MiningUnsealingModel = new MiningUnsealingModel();
        $unsealing_man = input('unsealing_man');//解封人
        $help_people = input('help_people');//帮助人
        $user_man = $UsersModel->where('mobile',$unsealing_man)->find();
        $user_help = $UsersModel->where('mobile',$help_people)->find();
        if(empty($user_man)){
            return ['code' => 0,'msg' => '解封账号不存在'];
        }
        if(empty($user_help)){
            return ['code' => 0,'msg' => '帮助人账号不存在'];
        }
        if($user_help['is_ban']==1){
            return ['code' => 0,'msg' => '帮助人账号已被封禁'];
        }

        $unsealing_num = $MiningUnsealingModel->where('frozen_user_id',$user_man['user_id'])->where('status',0)->count();
        if(!($unsealing_num<5)){
            return ['code' => 0,'msg' => '最多只能请五个帮助人解封'];
        }
        $is_unsealing = $MiningUnsealingModel->where(['frozen_user_id'=>$user_man['user_id'],'help_user_id'=>$user_help['user_id'],'status'=>0,])->count();
        if($is_unsealing>0){
            return ['code' => 0,'msg' => '请勿重复向一个用户请求帮助'];
        }

        $inArr['frozen_user_id'] = $user_man['user_id'];
        $inArr['help_user_id'] = $user_help['user_id'];
        $inArr['status'] = 0;
        $inArr['add_time'] = time();

        $res = $MiningUnsealingModel->insertGetId($inArr);
        if($res){
            return ['code' => 1,'msg' => '请求帮助成功,请耐心等待','url' => _url('Passport/login')];
        }else{
            return ['code' => 0,'msg' => '请求帮助失败'];
        }
    }
}
