<?php
namespace app;
use app\BaseController;

/*------------------------------------------------------ */
//-- 前端客户控制器基类
//-- Author: iqgmy
/*------------------------------------------------------ */   
class ClientbaseController extends BaseController
{
    /* @var array $loginInfo 登录信息 */
    protected $userInfo;
    /* @var array $allowAllAction 登录验证白名单 */
    protected $allowAllAction = [
        'member/passport/login',// 登录页面
        'member/passport/register',// 注册页面
        'member/passport/forgetpwd',// 注册页面
        'shop/index',//商城首页
        'shop/goods',//商城商品相关
        'shop/flow/cart',//购物车
		'shop/flow/cart',//购物车
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
		parent::initialize();
		$share_token = input('share_token','','trim');
		if (empty($share_token) == false){
			session('share_token',$share_token);
		}
        $this->userInfo = $this->getLoginInfo();
         // 当前路由信息
        $this->getRouteinfo();
        //验证登陆
        $this->checkLogin();
    } 
	/*------------------------------------------------------ */
	//-- 重新额外定义模板地址
	/*------------------------------------------------------ */
	public function fetch($template, $data = []){
		 // 全局layout
        $this->layout();
		return parent::fetch($template, $data);
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
    }

    /*------------------------------------------------------ */
    //-- 验证登录状态
    /*------------------------------------------------------ */
    protected function checkLogin($isAllow = true){
        // 验证当前请求是否在白名单
        if ($isAllow == true && in_array($this->module.'/'.$this->routeUri, $this->allowAllAction) || in_array($this->module.'/'.$this->controller,$this->allowAllAction)) 		{
            //记录分享
            $share_token = input('share_token','','trim');
            if (empty($share_token) == false){
                session('share_token',$share_token);
            }
            return true;
        }
        if (empty($this->userInfo)){  
			//微信网页访问执行
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')){				
				$access_token = (new \app\weixin\model\WeiXinModel)->getWxOpenId();// 获取微信用户WxOpenId		
				if (empty($access_token['openid'])){//获取openid，跳转登陆
					 return $this->redirect('member/passport/login');
				}
				$wxInfo = $WeiXinUsers->login($access_token);//用户存在进行登陆，否则进行注册操作
				if (is_array($wxInfo) == true){
					session('wxInfo',$wxInfo);
					if ($wxInfo['user_id'] > 0 ){
						session('userId',$wxInfo['user_id']);
           				$this->userInfo = (new \app\member\model\UsersModel)->info($wxInfo['user_id']);					
					}
				}
				return true;
			}
			//end
            return $this->redirect('member/passport/login');
        }
		
        return true;
    }

    
}
