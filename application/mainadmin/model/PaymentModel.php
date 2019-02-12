<?php

namespace app\mainadmin\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 支付相关
/*------------------------------------------------------ */
class PaymentModel extends BaseModel
{
	protected $table = 'main_payment';
	public  $pk = 'pay_id';
	protected $mkey = 'main_payment_';
   /*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache(){
        Cache::rm($this->mkey);
    }
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */
	public function getRows($status = false){
		$data = Cache::get($this->mkey);
		if (empty($data)){		
			$rows = $this->order('status DESC,sort_order DESC')->select()->toArray();		
			foreach ($rows as $row){
				$data[$row['pay_id']] = $row;
			}
			Cache::set($this->mkey,$data,600);
		}
		if ($status == true){
			foreach ($data as $key=>$row){
				if ($row['status'] == 0){
					unset($data[$key]);
				}
			}
		}		
		return $data;
	}
	

}
