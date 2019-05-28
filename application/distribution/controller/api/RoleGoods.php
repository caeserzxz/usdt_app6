<?php
namespace app\distribution\controller\api;
use app\ApiController;

use app\distribution\model\RoleGoodsModel;
use app\distribution\model\RoleOrderModel;
use app\distribution\model\DividendRoleModel;
use app\mainadmin\model\PaymentModel;

/*------------------------------------------------------ */
//-- 身份商品 相关API
/*------------------------------------------------------ */
class RoleGoods extends ApiController
{
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new RoleGoodsModel();
    }
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */
 	public function getList(){
        $where[] = ['status','=',1];
        $this->sqlOrder = 'sort_order DESC';
        $data = $this->getPageList($this->Model, $where,'*',10);
        foreach ($data['list'] as $key=>$goods){
            $goods['exp_price'] = explode('.',$goods['sale_price']);
            $return['list'][] = $goods;
        }
        $return['page_count'] = $data['page_count'];
		$return['code'] = 1;
		return $this->ajaxReturn($return);
	}
    /*------------------------------------------------------ */
    //-- 执行下单
    /*------------------------------------------------------ */
    function addOrder()
    {
        $this->checkLogin();//验证登录
        $rg_id = input('rg_id', 0, 'intval');

        if ($rg_id < 0) return $this->error('请选择需要购买的商品.');
        $rgoods = $this->Model->find($rg_id);
        if (empty($rgoods)){
            return $this->error('没有找到相关商品.');
        }
        if ($rgoods['status'] == 0){
            return $this->error('相关商品已下架.');
        }
        if ($this->userInfo['role_id'] == $rgoods['role_id']){
            return $this->error('当前身份与商品一致，无须购买.');
        }
        $DividendRoleModel = new DividendRoleModel();
        $nowRole = $DividendRoleModel->info($this->userInfo['role_id']);
        $buyRole = $DividendRoleModel->info($rgoods['role_id']);
        if ($nowRole['level'] > $buyRole['level']){
            return $this->error('你的身份比当前商品高级，无须购买.');
        }

        $inArr['real_name'] = input('real_name','','trim');
        $inArr['id_number'] = input('id_number','','trim');
        $inArr['mobile'] = input('mobile','','trim');
        if (empty($inArr['real_name'])) return $this->error('请输入真姓名.');
        if (empty($inArr['id_number'])) return $this->error('请输入身份证号码.');
        if (empty($inArr['mobile'])) return $this->error('请输入联系电话.');
        if (checkMobile($inArr['mobile']) == false) return $this->error('手机号码格式不正确.' );
        $regionIds = input('regionIds', '', 'trim');
        if (empty($regionIds)) return $this->error('请选择所在地区.');
        $regionIds = explode(',', $regionIds);
        $inArr['province'] = $regionIds[0];
        $inArr['city'] = $regionIds[1];
        $inArr['district'] = $regionIds[2];
        if ($inArr['district'] < 1) return $this->error('请选择所在地区.');
        $regionInfo = (new \app\mainadmin\model\RegionModel)->info($inArr['district']);
        $inArr['merger_name'] = $regionInfo['merger_name'];
        $inArr['address'] = input('address','','trim');

        $pay_id = input('pay_id', 0, 'intval');
        if ($pay_id < 0) return $this->error('请选择支付方式.');
        $PaymentModel = new PaymentModel();
        $paymentList = $PaymentModel->getRows(true);
        $payment = $paymentList[$pay_id];
        if (empty($payment)) return $this->error('相关支付方式不存在或已停用.');
        $time = time();

        $RoleOrderModel = new RoleOrderModel();
        $inArr['add_time'] = $time;
        $inArr['user_id'] = $this->userInfo['user_id'];
        $inArr['ipadderss'] = request()->ip();
        $inArr['rg_id'] = $rgoods['rg_id'];
        $inArr['order_amount'] = $rgoods['sale_price'];
        $inArr['goods_name'] = $rgoods['goods_name'];
        $inArr['goods_img'] = $rgoods['goods_img'];
        $inArr['role_id'] = $rgoods['role_id'];
        $inArr['pay_id'] = $payment['pay_id'];
        $inArr['pay_code'] = $payment['pay_code'];
        $inArr['pay_name'] = $payment['pay_name'];
        $inArr['last_role_id'] = $this->userInfo['role_id'];
        $inArr['order_sn'] = $RoleOrderModel->getOrderSn();
        $res = $RoleOrderModel->save($inArr);
        if ($res < 1) {
            return $this->error('未知原因，订单写入失败.');
        }
        $return['order_id'] = $RoleOrderModel->order_id;;
        $return['code'] = 1;
        return $this->ajaxReturn($return);

    }

}
