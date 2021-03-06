<?php

namespace app;

use think\facade\Cache;
use think\facade\Session;
include_once dirname(__DIR__) . '/application/commonAdmin.php';

/**
 * 后台控制器基类
 * Class BaseController
 * @package app\store\controller
 */
class AdminController extends BaseController
{
    /* @var array $admin 登录信息 */
    protected $admin;


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
    public $store_id = 0;//当前默认为总后台，门店值默认为0

    public $supplyer_id = 0;//指定供应商
    public $is_supplyer = false;//是否查询供应商相关
    public $initialize_isretrun = true;
    /**
     * @var BaseModel
     */
    public $Model;

    /*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    protected function initialize($check_priv = true)
    {
        parent::initialize();

        // 当前路由信息
        $this->getRouteinfo();
        if ($this->initialize_isretrun == false) {
            if (defined('AUID') == false) define('AUID', 0);
            return false;
        }
        // 商家登录信息
        $this->admin = Session::get('main_admin');
        // 验证登录
        $this->checkLogin();
        define('AUID', $this->admin['info']['user_id']);
        if ($check_priv == true){
            //自动验证权限
            $this->_priv($this->module,$this->controller,$this->action);
        }
        // 全局layout
        $this->layout();
    }

    /*------------------------------------------------------ */
    //-- 全局layout模板输出址
    /*------------------------------------------------------ */
    private function layout()
    {
        // 验证当前请求是否在白名单,ajax调用也不执行
        if ($this->request->isAjax() == false || !in_array($this->routeUri, $this->notLayoutAction)) {
            if ($this->action == 'info' && $this->request->isPost() == true) return false;//如果是info页提交也不执行

            if (in_array($this->module, config('config.shop_modules'))) {
                $this->module = 'shop';
            }
            // 输出到view
            $this->assign([
                '_action' => $this->action,                    //方法名称
                'menus' => $this->menus(),                     // 后台菜单
                'top_menus' => $this->top_menus,             // 后台菜单
                '_module' => $this->module,                   // 模块名称
                'admin' => $this->admin,                       // 登录信息
                'menus_group' => $this->menus_group,
                'baseUrl' => 'mainadmin@layouts/main_base',
            ]);
            //print_r($this->menus_list);exit;
        }
    }
    /*------------------------------------------------------ */
    //-- 权限验证
    /*------------------------------------------------------ */
    protected function _priv($module = '', $controller = '', $action = '', $isAll = true, $isReturn = false)
    {
        static $allPriv;
        if ($this->isCheckPriv == false) return true;
        $role_action = $this->admin['info']['role_action'];
        if ($role_action == 'all' || $action == 'welcome') {
            if ($isAll == true) return true;
            if ($isReturn == true) return false;
            $this->error('你无操作权限.');
        }
        if (isset($allPriv) == false) {
            $allPriv = (new \app\mainadmin\model\AdminPrivModel)->getRows();
        }

        if (in_array($module, config('config.shop_modules'))) {
            $privRows = $allPriv['shop'];
        }else{
            $privRows = $allPriv[$module];
        }
        $privIds = [];
        $module_controller = str_replace(['_','sysadmin.'],'',$module.':'.$controller.':');
        foreach ($privRows as $row){
            foreach ($row['right'] as $right){
                if (in_array($right,[$module_controller.$action,$module_controller.'allpriv'])){
                    $privIds[] = $row['id'];
                }
            }
        }
        $isTrue = empty($privIds) ? true : array_intersect($role_action,$privIds);
        if (empty($isTrue) == false) return true;
        if ($isReturn == true) return false;
        $this->error('你无操作权限.');
    }
    /*------------------------------------------------------ */
    //-- 权限验证，用于判断返回真假
    /*------------------------------------------------------ */
    public function _privIf($module = '',$controller = '', $action = '', $isAll = true)
    {
        return $this->_priv($module, $controller, $action, $isAll, true);
    }
    /*------------------------------------------------------ */
    //-- 获取有权限的菜单
    /*------------------------------------------------------ */
    private function getPrivList()
    {
        $mkey = 'main_menu_priv_list_' . AUID;
        $data = Cache::get($mkey);
        if (empty($data) == false) {
            return $data;
        }
        $rows = (new \app\mainadmin\model\MenuListModel)->where('status', 1)->order('pid DESC,sort_order DESC')->select()->toArray();
        //权限过滤
        foreach ($rows as $row) {
            if (empty($row['controller']) == false){
                if ($this->_privIf($row['group'],$row['controller'], $row['action'] ) == false){
                    continue;
                }
            }
            $menus[] = $row;
        }

        $data = [];
        $_data = [];
        foreach ($menus as $row) {
            $key = $row['pid'] < 1 ? $row['group'] : $row['id'];
            if ($row['pid'] > 0) {
                if (empty($row['controller']) == true){
                    if ($this->_privIf($row['group'],$row['controller'], $row['action'] ) == false){
                        continue;
                    }
                }
                if (empty($_data[$row['id']]) == false) {
                    $row['submenu'] = $_data[$row['id']];
                    unset($_data[$row['id']]);
                }
                $_data[$row['pid']][$key] = $row;
            } else {
                $row['list'] = $_data[$row['id']];
                if (empty($row['list']) == false){
                    $data[$key] = $row;
                }
            }
        }
       Cache::set($mkey, $data, 60);
        return $data;
    }


    /**
     * 后台菜单配置
     * @return array
     */
    private function menus()
    {
        $menus = $this->getPrivList();//获取有权限的菜单
        $this->top_menus = array();
        foreach ($menus as $group => $menu) {
            if (empty($menu['list']) == true){
                if (empty($menu['controller'])){
                    unset($menus[$group]);
                    continue;
                }
                if (empty($this->top_menus[$group])){
                    $this->top_menus[$group] = ['name' => $menu['name'], 'icon' => $menu['icon'], 'key' => $menu['group'], 'controller' => $menu['controller'], 'action' => $menu['action']];
                }
                continue;
            }
            foreach ($menu['list'] as $groupb=>$menub){
                if (empty($menub['submenu']) == true) {
                    if (empty($menub['controller'])){
                        unset($menus[$group]['list'][$groupb]);
                        continue;
                    }
                    if (empty($this->top_menus[$group])) {
                        $this->top_menus[$group] = ['name' => $menu['name'], 'icon' => $menu['icon'], 'key' => $menub['group'], 'controller' => $menub['controller'], 'action' => $menub['action']];
                    }
                    continue;
                }
                foreach ($menub['submenu'] as $groupc=>$menuc){
                    if (empty($menuc['submenu']) == true) {
                        if (empty($menuc['controller'])){
                            unset($menus[$group]['list'][$groupb]['submenu'][$groupc]);
                            continue;
                        }
                    }
                    if (empty($this->top_menus[$group])){
                        $this->top_menus[$group] = ['name' => $menu['name'], 'icon' => $menu['icon'], 'key' => $menu['group'], 'controller' => $menuc['controller'], 'action' => $menuc['action']];
                    }
                }
            }
        }

        $data = $menus[$this->module]['list'];
        $list_type = input('list_type','','trim');
        foreach ($data as $group => $first) {
            $parent = '';
            // 遍历：二级菜单
            if (isset($first['submenu'])) {
                foreach ($first['submenu'] as $secondKey => $second) {
                    // 二级菜单：active
                    $data[$group]['submenu'][$secondKey]['active'] = 0;
                    if ($this->routeUri == $second['controller'] . '/' . $second['action']) {
                        $data[$group]['submenu'][$secondKey]['active'] = 1;
                        $parent = $second['pid'];
                    }elseif(in_array($this->routeUri, explode(',', $second['urls']))) {
                        if (empty($list_type) || $list_type == $second['action']) {
                            $data[$group]['submenu'][$secondKey]['active'] = 1;
                            $parent = $second['pid'];
                        }
                    }
                    if (empty($second['submenu'])) {
                        if (empty($second['controller'])){
                            unset($data[$group]['submenu'][$secondKey]);
                            continue;
                        }
                    }else{
                        // 遍历：三级菜单
                        foreach ($second['submenu'] as $thirdKey => $third) {
                            $data[$group]['submenu'][$secondKey]['submenu'][$thirdKey]['active'] = 0;
                            if ($this->routeUri == $third['controller'] . '/' . $third['action'] || in_array($this->routeUri, explode(',', $third['urls']))) {
                                $data[$group]['submenu'][$secondKey]['submenu'][$thirdKey]['active'] = 1;
                            }
                        }
                    }
                }
            }
            if (empty($data[$group]['controller']) && empty($data[$group]['submenu'])){
                unset($data[$group]);
                continue;
            }
            if ($this->routeUri == $first['controller'] . '/' . $first['action'] || in_array($this->routeUri, explode(',', $first['urls']))) {
                $data[$group]['active'] = 1;
                $this->menus_group = $parent;
            } elseif (empty($parent) == false && $parent == $data[$group]['id']) {
                $data[$group]['active'] = 1;
                $this->menus_group = $parent;
            } else {
                $data[$group]['active'] = $data[$group]['group'] === $this->group;
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
        if (empty($this->admin) || (int)$this->admin['is_login'] !== 1) {
            if ($this->request->isAjax()) {
                return $this->error('登陆超时，请重新登陆.');
            }
            $this->redirect($_SERVER['SCRIPT_NAME'] . '/mainadmin/passport/login');
            return false;
        }
        return true;
    }

    //*------------------------------------------------------ */
    //-- 清理字典数据缓存
    /*------------------------------------------------------ */
    public function clearDict()
    {
        \app\mainadmin\model\PubDictModel::cleanMemcache(input('group'));
        echo '执行成功.';
        exit;
    }


    /*------------------------------------------------------ */
    //-- 添加/修改
    /*------------------------------------------------------ */
    public function info()
    {
        $pk = $this->Model->pk;
        if ($this->request->isPost()) {
            if (false === $data = $_POST) {
                $this->error($this->Model->getError());
            }
            if (empty($data[$pk])) {
                $this->_priv($this->module,$this->controller,'add');
                if (method_exists($this, 'beforeAdd')) {
                    $data = $this->beforeAdd($data);
                }
                unset($data[$pk]);
                $res = $this->Model->allowField(true)->save($data);
                if ($res > 0) {
                    $data[$pk] = $this->Model->$pk;
                    if (method_exists($this->Model, 'cleanMemcache')) $this->Model->cleanMemcache($res);
                    if (method_exists($this, 'afterAdd')) {
                        $result = $this->afterAdd($data);
                        if (is_array($result)) return $this->ajaxReturn($result);
                    }
                    return $this->success('添加成功.', url('index'));
                }
            } else {
                $this->_priv($this->module,$this->controller,'edit');
                if (method_exists($this, 'beforeEdit')) {
                    $data = $this->beforeEdit($data);
                }
                $res = $this->Model->allowField(true)->save($data, $data[$pk]);
                if ($res > 0) {
                    if (method_exists($this->Model, 'cleanMemcache')) $this->Model->cleanMemcache($data[$pk]);
                    if (method_exists($this, 'afterEdit')) {
                        $result = $this->afterEdit($data);
                        if (is_array($result)) return $this->ajaxReturn($result);
                    }
                    return $this->success('修改成功.', url('index'));
                }
            }
            return $this->error('操作失败.');
        }

        $id = input($pk, 0, 'intval');
        $row = ($id == 0) ? $this->Model->getField() : $this->Model->find($id);

        if ($id > 0 && empty($row) == false) {
            $row = $row->toArray();
        }
        if (method_exists($this, 'asInfo')) {
            $row = $this->asInfo($row);
        }

        $this->assign("row", $row);
        $ishtml = input('ishtml', 0, 'intval');
        if ($this->request->isAjax() && $ishtml == 0) {
            $result['code'] = 1;
            $result['data'] = $this->fetch('info')->getContent();
            return $this->ajaxReturn($result);
        }

        return $this->fetch('info');

    }

    /**
     * ajax修改单个字段值
     */
    public function ajaxEdit()
    {

        $pk = $this->Model->getPk();
        $id = input($pk, 0, 'intval');
        $field = input('field', '', 'trim');
        if ($id == '' || $field == '') return $this->error('缺少必要传参.');
        $data[$field] = input('value', '', 'trim');
        $toggle = input('toggle');
        if ($toggle) {
            $data[$field] = $toggle === 'true' || $toggle === 1 ? 1 : 0;
        }

        if (method_exists($this, 'beforeAjax')) {
            $data = $this->beforeAjax($id, $data);
        }
        $map[$pk] = $id;
        //允许异步修改的字段列表  放模型里面去 TODO
        if (false !== $this->Model->save($data, $map)) {
            if (method_exists($this, 'afterAjax')) {
                $this->afterAjax($id, $data);
            }
        }
        return $this->success();
    }


}
