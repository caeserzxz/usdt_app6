<?php
namespace app\supplyer\controller;

use app\supplyer\Controller;
use app\supplyer\model\SupplyerModel;
/**
 * 退货信息
 */
class ReturnInfo extends Controller
{
    protected function initialize()
    {
        parent::initialize();
        $this->Model = new SupplyerModel();
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
        $info = $this->Model->find($this->supplyer_id)->toArray();
        $this->assign('info',$info);
        return $this->fetch();
    }  
	/*------------------------------------------------------ */
	//-- 修改退货信息
	/*------------------------------------------------------ */
	public function edit()
    {
		$Model = new SupplyerModel();
		$data['supplyer_id'] = $this->supplyer_id;
		$data['return_consignee'] = input('return_consignee','','trim');
        $data['return_mobile'] = input('return_mobile','','trim');
        $data['return_address'] = input('return_address','','trim');
        $data['return_desc'] = input('return_desc','','trim');
		$res = $this->Model->saveDate($data,'editReturnInfo');
		if ($res < 1){
			return $this->error('未知错误，修改失败.');
		}
        $this->_log($this->supplyer_id, '修改退货信息' ,'supplyer');
        return $this->success('修改成功.');
    }  
}
