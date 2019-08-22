<?php

namespace app\shop\model;

use app\BaseModel;


//*------------------------------------------------------ */
//-- 订单日志表
/*------------------------------------------------------ */

class PrintTemplateModel extends BaseModel
{
    protected $table = 'shop_print_template';
    public $pk = 'template_id';

}
