<?php
namespace app\supplyer\model;
use app\BaseModel;

//*------------------------------------------------------ */
//-- 结算表
/*------------------------------------------------------ */
class SettleListModel extends BaseModel
{
	protected $table = 'supplyer_settle_list';
	public  $pk = 'settle_id';
    public  $status = ['0'=>'待认领','1'=>'已认领待打款','2'=>'已打款'];
}
