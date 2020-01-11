<?php
namespace app\supplyer;

use think\facade\Session;
use app\AdminController;

/**
 * 供应商后台控制器基类
 * Class BaseController
 * @package app\store\controller
 */
class Controller extends AdminController
{
    /* @var array $supplyer 登录信息 */
    protected $supplyer;


    /* @var string $route 当前菜单组名 */
    protected $menus_group = '';
    /* @var array $allowAllAction 登录验证白名单 */
    protected $allowAllAction = [
        // 登录页面
        'passport/login',
    ];

    /* @var array $notLayoutAction 无需全局layout */
    protected $notLayoutAction = [
        // 登录页面
        'passport/login',
    ];
    public $isCheckPriv = true;


    /**
     * @var BaseModel
     */
    public $Model;

    /*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    protected function initialize()
    {
        $this->initialize_isretrun = false;
        parent::initialize();

        // 商家登录信息
        $this->supplyer = Session::get('supplyer_admin');
        $this->supplyer_id = $this->supplyer['info']['supplyer_id'];
        // 当前路由信息
        $this->getRouteinfo();
        // 验证登录
        $this->checkLogin();
        // 全局layout
        $this->layout();
    }

    /*------------------------------------------------------ */
    //-- 全局layout模板输出址
    /*------------------------------------------------------ */
    private function layout()
    {
        define('SAUID',$this->supplyer['info']['supplyer_id']);
        // 验证当前请求是否在白名单,ajax调用也不执行
        if ($this->request->isAjax() == false || !in_array($this->routeUri, $this->notLayoutAction)) {
            if ($this->action == 'info' && $this->request->isPost() == true) return false;//如果是info页提交也不执行

            // 输出到view
            $this->assign([
                'menus' => $this->menus(),
                '_module' => $this->module,          // 模块名称
                'supplyer' => $this->supplyer,       // 登录信息
                'menus_group' => $this->menus_group,
            ]);
        }
    }

    /**
     * 后台菜单配置
     * @return array
     */
    private function menus()
    {
        $menus = (new model\MenuListModel)->getList();
        $data = $menus[$this->module];
        $list_type = input('list_type','','trim');
        $controller = explode('.',$this->controller);
        $controller = $controller[0];
        $shipping_tmp_supplyer = settings('shipping_tmp_supplyer');//供应商自行配置
        foreach ($data as $group => $first) {
            if ($shipping_tmp_supplyer == 0 && $first['controller'] == 'shipping_tpl'){//非供应商自行配置
                unset($data[$group]);
                continue;
            }
            if ($controller == $first['controller']){
                  $this->menus_group = $group;
            }
            // 遍历：二级菜单
            if (empty($first['list']) == false) {
                foreach ($first['list'] as $secondKey => $second) {
                    // 二级菜单：active
                    $data[$group]['list'][$secondKey]['active'] = 0;
                    if ($this->routeUri == $second['controller'].'/'.$second['action']){
                        $data[$group]['list'][$secondKey]['active'] = 1;
                    } elseif(in_array($this->routeUri, explode(',', $second['urls']))) {
                        if (empty($list_type) || $list_type == $second['action']) {
                            $data[$group]['list'][$secondKey]['active'] = 1;
                        }
                    }
                }
            }
            if ($this->routeUri == $first['controller'] . '/' . $first['action'] || in_array($this->routeUri, explode(',', $first['urls']))) {
                $data[$group]['active'] = 1;
            }elseif (empty($first['action'])){
                $data[$group]['active'] = $controller == $first['controller'];
            }elseif($controller == $first['controller'] && $this->action == $first['action'] ){
                $data[$group]['active'] = 1;
            }
        }
        return $data;
    }
    /**
     * 验证登录状态
     */
    private function checkLogin()
    {
        // 验证当前请求是否在白名单
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }

        // 验证登录状态
        if (empty($this->supplyer) || (int)$this->supplyer['is_login'] !== 1) {
            if ($this->request->isAjax()){
                return $this->error('登陆超时，请重新登陆.');
            }

            $this->redirect('/supplyer/passport/login');
            return false;
        }
        return true;
    }







}
