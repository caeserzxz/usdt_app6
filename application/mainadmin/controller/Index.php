<?php
namespace app\mainadmin\controller;
use app\AdminController;
/**
 * 后台首页
 * Class Index
 * @package app\store\controller
 */
class Index extends AdminController
{
	

    public function index()
    {
        return $this->fetch('index');
    }

  


}
