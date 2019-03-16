<?php
namespace app\publics\controller\api;
use app\ApiController;

use app\mainadmin\model\PaymentModel;

/*------------------------------------------------------ */
//-- 支付相关API
/*------------------------------------------------------ */
class Pay extends ApiController{

	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new PaymentModel();
    }
	/*------------------------------------------------------ */
	//-- 获取支付列表
	/*------------------------------------------------------ */
 	public function getList(){
        $payList = $this->Model->getRows(true,'pay_code');
        $type = input('type','','trim');
        if ($type == 'is_recharge'){
            foreach ($payList as $key=>$pay){
                if ($pay['is_recharge'] == 0){
                    unset($payList[$key]);
                }
            }
        }
        $return['data'] = $payList;
        $return['balance_money'] = $this->userInfo['account']['balance_money'];
		$return['code'] = 1;
		return $this->ajaxReturn($return);
	}

}
