<?php

namespace app\supplyer\controller;
use app\supplyer\Controller;
use app\supplyer\model\SupplyerModel;

/**
 * 登陆
 * Class Passport
 * @package app\store\controller
 */
class Passport extends Controller
{

    /**
     * 后台登录
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        if ($this->request->isAjax()) {
            $model = new SupplyerModel;
            if ($model->login(input('post.User'))) {
                return $this->success('登录成功', url('index/index'));
            }
            return $this->error($model->getError() ?: '登录失败');
        }
        $this->view->engine->layout(false);
        return $this->fetch('login');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
       $model = new SupplyerModel;
	   $model->logout();
       $this->redirect('passport/login');
    }

}
