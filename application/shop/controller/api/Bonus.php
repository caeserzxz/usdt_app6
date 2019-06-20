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

    /*------------------------------------------------------ */
    //-- 获取优惠券列表
    /*------------------------------------------------------ */
    public function getFreeList()
    {
        $return['data'] = $this->Model->getListByFree();
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }

    /*------------------------------------------------------ */
    //-- 领取优惠卷
    /*------------------------------------------------------ */
    public function receiveFree(){
        $id = input('id',0,'intval');
        $user_id = $this->userInfo['user_id'];

        $result = $this->Model->receiveFreeBonus($id,$user_id);
        if ($result !== true) {
            return $this->error($result);
        }
        $return['msg'] = '领取成功.';
        $return['code'] = 1;
        return $this->ajaxReturn($return);
    }
    
}
