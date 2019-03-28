<?php
namespace app\publics\controller\api;
use app\ApiController;

/*------------------------------------------------------ */
//-- 公共调用
/*------------------------------------------------------ */
class Index extends ApiController
{
	/*------------------------------------------------------ */
	//-- 获取全站设置
	/*------------------------------------------------------ */
 	public function setting(){
		$data['code'] = 1;
		$data['data'] = settings();
		return $this->ajaxReturn($data);
	}
   

}
