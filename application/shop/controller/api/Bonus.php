<?php

namespace app\shop\controller\api;

use app\ApiController;

use app\shop\model\BonusModel;


/*------------------------------------------------------ */
//-- 优惠相关API
/*------------------------------------------------------ */

class Bonus extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new BonusModel();
    }
    /*------------------------------------------------------ */
    //-- 获取优惠券列表
    /*------------------------------------------------------ */
    public function getList()
    {
        $return['data'] = $this->Model->getListByUser();       
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    
}
