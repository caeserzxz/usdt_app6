<?php

namespace app\integral\controller\api;

use app\shop\controller\api\Flow as ShopFlow;

/*------------------------------------------------------ */
//-- 购物相关API
/*------------------------------------------------------ */

class Flow extends ShopFlow
{

    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        $this->is_integral = 1;
        parent::initialize();
    }

}
