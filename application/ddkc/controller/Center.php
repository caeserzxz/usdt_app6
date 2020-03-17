<?php
/*------------------------------------------------------ */
//-- 会员主页
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\ddkc\controller;
use app\ClientbaseController;
use app\member\model\UsersModel;
use app\member\model\UsersBindModel;
use app\distribution\model\DividendRoleModel;
use app\ddkc\model\PaymentModel;
class Center  extends ClientbaseController{
  
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
        $this->assign('title', '会员中心');
        $this->assign('isUserIndex', 1);
        $this->assign('not_top_nav', true);
        $this->assign('user_center_nav_tpl', settings('user_center_nav_tpl'));
        $this->assign('navMenuList', (new \app\shop\model\NavMenuModel)->getRows(3));//获取导航菜单

		return $this->fetch('index');
	}
	/*------------------------------------------------------ */
    //-- 我的分享二维码
    /*------------------------------------------------------ */
    public function myCode(){
        $DividendShareByRole = settings('DividendShareByRole');
        if ($DividendShareByRole == 1 && $this->userInfo['role_id'] < 1){
            return $this->error('请升级身份后再操作.');
        }
        $default_img = settings('GoodsImages');
        $arr = explode(',', $default_img);
        $default_img = $arr[0]?$arr[0]:'';
        $this->assign('default_img',$default_img);
        $this->assign('title', '我的二维码');
        return $this->fetch('my_code');
    }
    /*------------------------------------------------------ */
    //-- 会员收货地址页
    /*------------------------------------------------------ */
    public function address(){
        $this->assign('title', '收货地址');
        return $this->fetch('address/index');
    }
    /*------------------------------------------------------ */
    //-- 会员优惠券页
    /*------------------------------------------------------ */
    public function bonus(){
        $this->assign('title', '优惠券');
        return $this->fetch('shop@bonus/index');
    }
    /*------------------------------------------------------ */
    //-- 会员设置页
    /*------------------------------------------------------ */
    public function setting(){
        $this->assign('title', '设置');
        return $this->fetch('setting');
    }
    /*------------------------------------------------------ */
    //-- 修改密码
    /*------------------------------------------------------ */
    public function editPwd(){
        $this->assign('title', '修改密码');
        return $this->fetch('edit_pwd');
    }
    /*------------------------------------------------------ */
    //-- 修改支付密码
    /*------------------------------------------------------ */
    public function editPayPwd(){
        $this->assign('sms_fun', settings('sms_fun'));//获取短信配置
        $this->assign('title', '修改支付密码');
        return $this->fetch('edit_pay_pwd');
    }
    /*------------------------------------------------------ */
    //-- 个人资料
    /*------------------------------------------------------ */
    public function userInfo(){
        $this->assign('title', '个人资料');	
		$superior = (new UsersModel)->getSuperior($this->userInfo['pid']);
		$this->assign('superior', $superior);
        return $this->fetch('user_info');
    }
    /*------------------------------------------------------ */
    //-- 我的钱包
    /*------------------------------------------------------ */
    public function wallet(){
        $this->assign('title', '我的钱包');
        return $this->fetch('wallet');
    }
    /*------------------------------------------------------ */
    //-- 提现
    /*------------------------------------------------------ */
    public function withdraw(){
        $this->assign('title', '提现');
        return $this->fetch('withdraw');
    }
    /*------------------------------------------------------ */
    //-- 添加银行卡
    /*------------------------------------------------------ */
    public function addBankCard(){
        $PaymentModel = new PaymentModel();
        #获取银行卡信息
        $bank_info = $PaymentModel->get_payment($this->userInfo['user_id'],1);
        $this->assign('bank_info',$bank_info);
        #获取支付宝信息
        $alipay_info = $PaymentModel->get_payment($this->userInfo['user_id'],2);
        $this->assign('alipay_info',$alipay_info);
        #获取微信信息
        $wx_info = $PaymentModel->get_payment($this->userInfo['user_id'],3);
        $this->assign('wx_info',$wx_info);

        $this->assign('appType',session('appType'));
        $this->assign('title', '收款信息');
        return $this->fetch('add_bank_card');
    }
    /*------------------------------------------------------ */
    //-- 团队管理
    /*------------------------------------------------------ */
    public function myTeam(){
        $userId = $this->userInfo['user_id'];
        $roleModel = new DividendRoleModel();
        $userBindModel = new UsersBindModel();
        $userModel = new UsersModel();


        # 各等级直推人数统计
        $allRole = $roleModel->field('role_id,role_name')->where(1)->select();
        foreach ($allRole as $key => $value) {
            # 该等级直推人数
            $allRole[$key]['subNum'] = $userModel
                ->where(['pid' => $userId,'role_id' => $value['role_id']])
                ->count();
        }
        # 三层内各层级人数
        $where[] = ['pid','=',$userId];
        $where[] = ['level','<=',3];
        $threeInfo = $userBindModel->where($where)->group('level')->column('count(*) cc','level');
        $textInfo = ['其他','一','二','三'];
        
        $this->assign('roleInfo', $allRole);
        $this->assign('threeInfo', $threeInfo);
        $this->assign('textInfo', $textInfo);


        $this->assign('title', '团队管理');
        return $this->fetch('my_team');
    }
}?>