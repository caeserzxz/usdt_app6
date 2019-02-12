<?php
/*------------------------------------------------------ */
//-- 提成处理
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\distribution\model;
use app\BaseModel;
use think\facade\Cache;
class DividendModel extends BaseModel {
	protected $table = 'distribution_dividend_log'; 
    /*------------------------------------------------------ */
	//-- 计算提成并记录
	/*------------------------------------------------------ */ 
	public function evalInLog($order_id=0){		
		
	}
	/*------------------------------------------------------ */
	//-- 获取等级提成
	/*------------------------------------------------------ */ 
	public function level($Dividend = array()){
		if (empty($Dividend)) $Dividend = settings('DividendInfo');
		if (empty($Dividend['LevelRow'])) return array();
		return $Dividend['LevelRow'];
	}
	/*------------------------------------------------------ */
	//-- 更新分成
	/*------------------------------------------------------ */ 
	public function upDividend($data){
		$map = $data['map'];
		unset($data['map']);
		$data['update_time'] = time();
		return $this->where($map)->update($data);
	}
	
	/*------------------------------------------------------ */
	//-- 批量执行签收提成到帐操作
	/*------------------------------------------------------ */ 
	public function batchEvalDividend(){
		
		
	}
}?>
