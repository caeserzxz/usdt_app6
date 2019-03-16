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
        Cache::rm($this->mkey.'pay_id');
		Cache::rm($this->mkey.'pay_code');
    }
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */
	public function getRows($status = false,$type='pay_id'){
		$data = Cache::get($this->mkey.$type);
		if (empty($data)){		
			$rows = $this->field('*,pay_id AS id,pay_name AS name')->order('is_pay DESC,sort_order DESC')->select()->toArray();
			foreach ($rows as $row){
				if ($type == 'pay_id'){
					$data[$row['pay_id']] = $row;
				}else{
					$data[$row['pay_code']] = $row;
				}
			}
			Cache::set($this->mkey.$type,$data,600);
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
