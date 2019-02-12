<?php
namespace app\mainadmin\model;
use app\BaseModel;
//*------------------------------------------------------ */
//-- 支付日志
/*------------------------------------------------------ */
class PaymentLogModel extends BaseModel
{
	protected $table = 'main_payment_log';
    public  $pk = 'log_id';
    public  $statusArr = [0=>'待支付',1=>'已支付',1=>'支付关闭',9=>'已退'];
}
