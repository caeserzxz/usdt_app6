<?php
namespace app\publics\controller\api;
use app\ApiController;

use app\mainadmin\model\RegionModel;


/*------------------------------------------------------ */
//-- 区域信息获取
/*------------------------------------------------------ */
class Region extends ApiController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new RegionModel();		
    }
	/*------------------------------------------------------ */
	//-- 加载区域信息
	/*------------------------------------------------------ */
 	public function getList(){	
		$pid = input('pid',0,'intval');
		$data['code'] = 1;
		$data['list'] = $this->Model->getBySel($pid);
		return $this->ajaxReturn($data);
	}
   

}
