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
		$share_token = input('share_token','','trim');
		if (empty($share_token) == false){
			session('share_token',$share_token);
		}
		//判断是否微信访问
        $this->is_wx = session('is_wx');
		if (empty($this->is_wx)){
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
                $this->is_wx = 1;
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
		$this->assign([
			'routeUri' => $this->routeUri,  // 当前uri			
		]);
        $this->assign('is_wx',$this->is_wx);//登陆会员信息
    }

    /*------------------------------------------------------ */
    //-- 验证登录状态
    /*------------------------------------------------------ */
    protected function checkLogin($isAllow = true){
		global $userInfo;

		if (empty($this->userInfo)){
            $wxInfo = session('wxInfo');
            if (empty($wxInfo)){
                //微信网页访问执行
                if ($this->is_wx == 1 && settings('weixin_auto_login') == 0){
                    $access_token = (new \app\weixin\model\WeiXinModel)->getWxOpenId();// 获取微信用户WxOpenId
                    if (empty($access_token['openid'])){//获取openid，跳转登陆
                         return true;
                    }
                    $wxInfo = (new \app\weixin\model\WeiXinUsersModel)->login($access_token);//用户存在进行登陆，否则进行注册操作
                    if (empty($wxInfo) == false){
                        session('wxInfo',$wxInfo);
                        if ($wxInfo['user_id'] > 0 ){
                            session('userId',$wxInfo['user_id']);
                            $this->userInfo = $userInfo = (new \app\member\model\UsersModel)->info($wxInfo['user_id']);
                        }
                    }
                    return true;
                }
            }
        }
        // 验证当前请求是否在白名单
        if ($isAllow == true && (in_array($this->module, $this->allowAllAction) || in_array($this->module.'/'.$this->routeUri, $this->allowAllAction) || in_array($this->module.'/'.$this->controller,$this->allowAllAction)))
        {
            //记录分享
            $share_token = input('share_token','','trim');
            if (empty($share_token) == false){
                session('share_token',$share_token);
            }
            return true;
        }
        if (empty($this->userInfo)){
		    session('REQUEST_URI',$_SERVER['REQUEST_URI']);
            return $this->redirect('member/passport/login');
        }
		
        return true;
    }

    
}
