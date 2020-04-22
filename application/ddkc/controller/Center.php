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
use app\member\model\UsersSignModel;
use app\member\model\AccountLogModel;
use app\ddkc\model\AuthenticationModel;
use app\mainadmin\model\MessageModel;
use app\ddkc\model\SlideModel;

class Center  extends ClientbaseController{
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
	public function index(){
        $userId = $this->userInfo['user_id'];
        $accountLogModel = new AccountLogModel();

        $this->assign('title', '会员中心');
        $this->assign('isUserIndex', 1);
        $this->assign('not_top_nav', true);
        $this->assign('user_center_nav_tpl', settings('user_center_nav_tpl'));
        $this->assign('navMenuList', (new \app\shop\model\NavMenuModel)->getRows(3));//获取导航菜单

        # 矿机收益
        $profit['miner'] = $accountLogModel->where(['user_id' => $userId,'change_type' => 105])->sum('ddb_money');
        # 增值包收益
        $profit['increment'] = $accountLogModel->where(['user_id' => $userId,'change_type' => 106])->sum('ddb_money');

        $where[] = ['user_id','=',$userId];
        $where[] = ['change_type','IN',[102,103]];
        $profit['award'] = $accountLogModel->where($where)->sum('ddb_money');
        $this->assign('appType',session('appType'));
        $this->assign('profit', $profit);
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
        $this->assign('sms_fun', settings('sms_fun'));//获取短信配置
        $this->assign('title', '修改登录密码');
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
        $this->assign('sms_fun', settings('sms_fun'));//获取短信配置
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
        if(empty($bank_info)){
            $bank_info['status_str'] = '';
        }else{
            if($bank_info['status']==0){
                $bank_info['status_str'] = '审核中';
            }else if($bank_info['status']==1){
                $bank_info['status_str'] = '审核通过';
            }else if($bank_info['status']==2){
                $bank_info['status_str'] = '审核失败';
            }
        }
        $this->assign('bank_info',$bank_info);

        #获取支付宝信息
        $alipay_info = $PaymentModel->get_payment($this->userInfo['user_id'],2);
        if(empty($alipay_info)){
            $alipay_info['status_str'] = '';
        }else{
            if($alipay_info['status']==0){
                $alipay_info['status_str'] = '审核中';
            }else if($alipay_info['status']==1){
                $alipay_info['status_str'] = '审核通过';
            }else if($alipay_info['status']==2){
                $alipay_info['status_str'] = '审核失败';
            }
        }
        $this->assign('alipay_info',$alipay_info);
        #获取微信信息
        $wx_info = $PaymentModel->get_payment($this->userInfo['user_id'],3);
        if(empty($wx_info)){
            $wx_info['status_str'] = '';
        }else{
            if($wx_info['status']==0){
                $wx_info['status_str'] = '审核中';
            }else if($wx_info['status']==1){
                $wx_info['status_str'] = '审核通过';
            }else if($wx_info['status']==2){
                $wx_info['status_str'] = '审核失败';
            }
        }

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
        $accountLogModel = new AccountLogModel();

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
        # 默认0个下级 防止无法循环
        if (!$threeInfo[1]) $threeInfo[1] = 0;
        if (!$threeInfo[2]) $threeInfo[2] = 0;
        if (!$threeInfo[3]) $threeInfo[3] = 0;

        $textInfo = ['其他','一','二','三'];
        # 直链收益
        $award['extension'] = $accountLogModel->where(['user_id' => $userId,'change_type' => 102])->sum('ddb_money');
        # 联代收益
        $award['team'] = $accountLogModel->where(['user_id' => $userId,'change_type' => 103])->sum('ddb_money');

        $this->assign('award', $award);
        $this->assign('roleInfo', $allRole);
        $this->assign('threeInfo', $threeInfo);
        $this->assign('textInfo', $textInfo);

        $this->assign('title', '团队管理');
        return $this->fetch('my_team');
    }
    /*------------------------------------------------------ */
    //-- 签到
    /*------------------------------------------------------ */
    public function sign(){
        $year = input('year',date('Y')) * 1;
        $month = input('month',date('n')) * 1;

        $data = (new UsersSignModel)->where(['user_id'=>$this->userInfo['user_id'],'year'=>$year,'month'=>$month])->find();
        $data['nowtime'] = time();
        $data['days_time'] = json_encode(explode(',',$data['days_time']));

        $this->assign('data', $data);
        $this->assign('title', '签到送积分');

        return $this->fetch('sign');
    }
    /*------------------------------------------------------ */
    //-- 安全中心
    /*------------------------------------------------------ */
    public function security(){
        $this->assign('title', '安全中心');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 积分日志
    /*------------------------------------------------------ */
    public function integralLog(){
        $integral_article = preg_replace("/img(.*?)src=[\"|\'](.*?)[\"|\']/", 'img class="lazy" width="750" src="/static/mobile/default/images/loading.svg" data-original="$2"', settings('integral_article'));
        $this->assign('integral_article', $integral_article);
        $this->assign('title', '信用积分');
        $this->assign('year', date('Y',time()));
        return $this->fetch();
    }
    public function sub_list($level = 1){
        $this->assign('title', '下级列表');
        $this->assign('level', $level);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 实名认证
    /*------------------------------------------------------ */
    public function authentication(){
        $AuthenticationModel = new AuthenticationModel();
        $authenInfo = $AuthenticationModel->where(['user_id' => $this->userInfo['user_id']])->find();

        $this->assign('authenInfo', $authenInfo);
        $this->assign('title', '实名认证');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 系统消息
    /*------------------------------------------------------ */
    public function message(){
        (new MessageModel)->autoReceive();//执行自动接收消息

        $this->assign('title', '系统消息');
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 叮叮交易所
    /*------------------------------------------------------ */
    public function trade_img(){
        $SlideModel = new SlideModel();
        $slideList = $SlideModel::getRows(3);
        $this->assign('slideList',$slideList);
        $this->assign('title', '叮叮交易所');
        return $this->fetch();
    }

}?>