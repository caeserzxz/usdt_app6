<?php

namespace app\shop\model;

use app\BaseModel;


//*------------------------------------------------------ */
//-- 售后日志表
/*------------------------------------------------------ */

class AfterSaleLogModel extends BaseModel
{
    protected $table = 'shop_goods_log';
    public $pk = 'log_id';


}
