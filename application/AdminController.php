<?php

namespace app;

use think\facade\Cache;
use think\facade\Session;


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
    protected function initialize()
    {
        parent::initialize();
        include_once dirname(__DIR__) . '/application/commonAdmin.php';
        // 当前路由信息
        $this->getRouteinfo();
        if ($this->initialize_isretrun == false) {
            if (defined('AUID') == false) define('AUID', 0);
            return false;
        }
        // 商家登录信息
        $this->admin = Session::get('main_admin');
        define('AUID', $this->admin['info']['user_id']);
        // 验证登录
        $this->checkLogin();
        //自动验证权限
        $this->_priv();
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

            if (in_array($this->module, ['fightgroup','integral', 'second'])) {
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
            ]);
            //print_r($this->menus_list);exit;
        }
    }
    /*------------------------------------------------------ */
    //-- 权限验证
    /*------------------------------------------------------ */
    protected function _priv($now = '', $action = '', $isAll = true, $isReturn = false)
    {
        if ($this->isCheckPriv == false) return true;
        $role_action = $this->admin['info']['role_action'];
        if ($role_action == 'all') {
            if ($isAll == true) return true;
            if ($isReturn == true) return false;
            $this->error('你无操作权限.');
        }
        if (empty($now)) {
            $now = $this->module . '|' . $this->controller;
        }
        $role_action = $role_action[$now];
        if (empty($role_action)) {
            $MenuListModel = new \app\mainadmin\model\MenuListModel();
            $noPrivList = $MenuListModel->getNoPriv();
            if (in_array($now, $noPrivList)) {
                return true;
            }
        }
        $action = empty($action) ? $this->action : $action;
        if ($action == 'info') {
            if ($this->request->isPost() == true) {
                $isTrue = array_intersect(['manage', 'edit'], $role_action);
            } else {
                $isTrue = array_intersect(['view', 'manage'], $role_action);
            }
        } elseif ($action == 'ajaxEdit') {
            $isTrue = array_intersect(['manage', 'edit'], $role_action);
        } elseif (in_array($action, array('index', 'getList', 'trashList', 'search'))) {
            $isTrue = array_intersect(['manage', 'view'], $role_action);
        } elseif (in_array($action, array('download', 'export'))) {
            $isTrue = array_intersect(['download', 'export'], $role_action);
        }else{
            $isTrue = true;
        }
        if (empty($isTrue) == false) return true;
        if ($isReturn == true) return false;
        $this->error('你无操作权限.');
    }
    /*------------------------------------------------------ */
    //-- 权限验证，用于判断返回真假
    /*------------------------------------------------------ */
    public function _privIf($now = '', $action = '', $isAll = true)
    {
        return $this->_priv($now, $action, $isAll, true);
    }
    /*------------------------------------------------------ */
    //-- 获取有权限的菜单
    /*------------------------------------------------------ */
    private function getPrivList()
    {
        $mkey = 'main_menu_priv_list_' . AUID;
        //$data = Cache::get($mkey);
        if (empty($data) == false) {
            return $data;
        }
        $rows = (new \app\mainadmin\model\MenuListModel)->where('status', 1)->order('pid DESC sort_order ASC')->select()->toArray();
        //权限过滤
        foreach ($rows as $row) {
            if (empty($row['right']) == false && $this->_privIf($row['group'] . '|' . $row['controller'], $row['action']) == false) {
                continue;
            }
            $menus[] = $row;
        }
        $data = [];
        $_data = [];
        foreach ($menus as $row) {
            $key = $row['pid'] < 1 ? $row['group'] : $row['id'];
            if ($row['pid'] > 0) {
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
            if (empty($menu['list']) == true)  continue;
            if (empty($this->top_menus[$group]) == false)  continue;
            foreach ($menu['list'] as $menub){
                if (empty($this->top_menus[$group]) == false) continue;
                if (empty($menub['submenu']) == false) {
                    $menuc = reset($menub['submenu']);
                    $this->top_menus[$group] = ['name' => $menu['name'], 'icon' => $menu['icon'], 'key' => $menuc['group'], 'controller' => $menuc['controller'], 'action' => $menuc['action']];
                    continue;
                } elseif (empty($menub) == false) {
                    if (empty($menub['controller'])){
                        continue;
                    }
                    $this->top_menus[$group] = ['name' => $menu['name'], 'icon' => $menu['icon'], 'key' => $menub['group'], 'controller' => $menub['controller'], 'action' => $menub['action']];
                    continue;
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
            $result['data'] = $this->fetch('info');
            return $this->ajaxReturn($result);
        }
        return response($this->fetch('info'));

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
