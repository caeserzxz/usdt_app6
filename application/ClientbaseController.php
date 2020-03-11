<?php
namespace app;


/*------------------------------------------------------ */
//-- 前端客户控制器基类
//-- Author: iqgmy
/*------------------------------------------------------ */   
class ClientbaseController extends BaseController{
    /* @var array $loginInfo 登录信息 */
    protected $userInfo;
    public $is_wx = 0;
    /* @var array $allowAllAction 登录验证白名单 */
    protected $allowAllAction = [
        'member/passport/login',// 登录页面
        'member/passport/register',// 注册页面
        'member/passport/forgetpwd',// 注册页面
        
        'ddkc/passport/register',// 注册页面
        'ddkc/passport/forgetpwd',// 注册页面
        'ddkc/passport/login',// 登录页面

        'shop/index',//商城首页
        'shop/goods',//商城商品相关
        'shop/flow/cart',//购物车
        'shop/article',//文章相关
        'shop/payment',//支付相关
        'fightgroup',//拼团相关
        'second',//秒杀相关
        'publics/download',//app下载
    ];

    /* @var array $notLayoutAction 无需全局layout */
    protected $notLayoutAction = [
        'memmber/mpassport/login',// 登录页面
        'memmber/mpassport/logout',//退出登陆
    ];

	/*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
    public function initialize()
    {
		global $userInfo;
		parent::initialize();

		//判断是否微信访问
        $this->is_wx = session('is_wx');
		if (empty($this->is_wx)){
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
                $this->is_wx = 1;//微信访问
            }else{
                $this->is_wx = 2;
            }
            session('is_wx',$this->is_wx);
        }//end
        //$this->is_wx = 1;//本地测试使用
        $userInfo = $this->getLoginInfo();
        $this->userInfo = $userInfo;
         // 当前路由信息
        $this->getRouteinfo();
        //验证登陆
        $this->checkLogin();
    } 
	/*------------------------------------------------------ */
	//-- 重新额外定义模板地址
	/*------------------------------------------------------ */
    protected function fetch($template = '', $vars = [], $config = []){
		 // 全局layout
        $this->layout();
		return parent::fetch($template, $vars, $config);
    }

	/*------------------------------------------------------ */
	//-- 全局layout模板输出址
	/*------------------------------------------------------ */
    private function layout(){
        // 输出到view
        $this->assign('userInfo',$this->userInfo);//登陆会员信息
		$this->assign('setting',settings());// 系统配置
        $this->assign('navFootList', (new \app\shop\model\NavMenuModel)->getRows(2));//获取底部菜单
		$this->assign([
			'routeUri' => $this->routeUri,  // 当前uri			
		]);
        $this->assign('is_wx',$this->is_wx);//登陆会员信息
    }

    /*------------------------------------------------------ */
    //-- 验证登录状态
    /*------------------------------------------------------ */
    protected function checkLogin($isAllow = true){
        if ($this->checkLoginUser() === true){
            return true;
        }
        // 验证当前请求是否在白名单
        if ($isAllow == true && (in_array($this->module, $this->allowAllAction) || in_array($this->module.'/'.$this->routeUri, $this->allowAllAction) || in_array($this->module.'/'.$this->controller,$this->allowAllAction)))
        {
            return true;
        }
        if (empty($this->userInfo)){
		    session('REQUEST_URI',$_SERVER['REQUEST_URI']);
            return $this->redirect('member/passport/login');
        }
		
        return true;
    }

    /**
     * 验证登陆用户信息
     * @return bool
     */
    protected function checkLoginUser(){
        global $userInfo;
        if (empty($this->userInfo) == true) {
            //获取邀请码
            $share_token = input('share_token', '', 'trim');
            if (empty($share_token) == false) {
                session('share_token', $share_token);
            }
            if ($this->is_wx == 1) {//微信网页访问执行
                $wxInfo = session('wxInfo');
                $UsersModel = new \app\member\model\UsersModel();
                if (empty($wxInfo) == true) {
                    if (settings('weixin_auto_login') == 0) {
                        $access_token = (new \app\weixin\model\WeiXinModel)->getWxOpenId();// 获取微信用户WxOpenId
                        if (empty($access_token['openid']) == false) {//获取openid成功执行
                            $wxInfo = (new \app\weixin\model\WeiXinUsersModel)->login($access_token);//用户存在进行登陆，否则进行注册操作
                            if ($wxInfo === false) {
                                return false;
                            }
                            session('wxInfo', $wxInfo);
                            if ($wxInfo['user_id'] > 0) {
                                session('userId', $wxInfo['user_id']);
                                $this->userInfo = $userInfo = $UsersModel->info($wxInfo['user_id']);
                            }
                        }
                    }
                }

                if ($wxInfo['user_id'] <= 0) {//未注册，判断是否来自分享,记录分享来源
                    $share_token = session('share_token');
                    if (empty($share_token) == false) {
                        $wxlog['wxuid'] = $wxInfo['wxuid'];
                        $wxlog['user_id'] = $UsersModel->getShareUser($share_token);
                        $wxlog['share_token'] = $share_token;
                        $wxlog['add_time'] = time();
                        $res = (new \app\weixin\model\WeiXinInviteLogModel)->save($wxlog);
                        if ($res > 0) {
                            session('share_token', null);
                        }
                    }
                }

            }
        }
        if (empty($this->userInfo) == false){
            return true;
        }
        return false;
    }
    /*------------------------------------------------------ */
    //-- 退出
    /*------------------------------------------------------ */
    public function logout()
    {
        session('userId', null);
        $wxInfo = session('wxInfo');
        if (empty($wxInfo) == false){
            //如果微信对应会员信息中不存在手机号码，执行清理微信授权数据，存在则不清理，重新登陆会员，微信会自动捆绑新的会员信息
            if (empty($this->userInfo['mobile']) == true){
                session('wxInfo', null);
            }
        }
        if ($this->request->isAjax()){
            return $this->success('退出成功.');
        }
        return $this->fetch('member@center/logout');
    }
}
