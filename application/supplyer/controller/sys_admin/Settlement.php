<?php
namespace app\supplyer\controller\sys_admin;

use app\AdminController;
use app\supplyer\model\SettleListModel;
/**
 * 结算管理
 */
class Settlement extends AdminController
{
    protected function initialize()
    {
        parent::initialize();
        $this->Model = new SettleListModel();
    }
	/*------------------------------------------------------ */
	//-- 待结算
	/*------------------------------------------------------ */
    public function wait()
    {
        $this->assign("settl_month", date('Y-m', strtotime("-1 months")));
        $this->assign('title','待结算');
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 待认领
    /*------------------------------------------------------ */
    public function wait_check()
    {
        $this->assign("settl_month", date('Y-m', strtotime("-1 months")));
        $this->assign('title','待认领');
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 待打款
    /*------------------------------------------------------ */
    public function wait_pay()
    {
        $this->assign("settl_month", date('Y-m', strtotime("-1 months")));
        $this->assign('title','待打款');
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 已完成
    /*------------------------------------------------------ */
    public function complete()
    {
        $this->assign("settl_month", date('Y-m', strtotime("-1 months")));
        $this->assign('title','已完成');
        return $this->fetch('index');
    }
}
