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
		$this->assign('setting', model('app\mainadmin\model\SettingsModel')->getRows());// 系统配置
		$this->assign([
			'routeUri' => $this->routeUri,  // 当前uri			
		]);
    }

    /*------------------------------------------------------ */
    //-- 验证登录状态
    /*------------------------------------------------------ */
    protected function checkLogin($isAllow = true){
        // 验证当前请求是否在白名单
        if ($isAllow == true && in_array($this->module.'/'.$this->routeUri, $this->allowAllAction) || in_array($this->module.'/'.$this->controller,$this->allowAllAction)) {
            //记录分享
            $share_token = input('share_token','','trim');
            if (empty($share_token) == false){
                session('share_token',$share_token);
            }
            return true;
        }
        if (empty($this->userInfo)){
            if ($this->request->isAjax()){
                return $this->error('请登陆后再操作.',url('member/passport/login'));
            }
            return $this->redirect('member/passport/login');
        }
        return true;
    }

    
}
