<?php

namespace app\ddkc\controller\api;
use think\Db;
use app\ApiController;
use app\member\model\UsersModel;
// use app\ddkc\model\MiningProfitLog;
use app\member\model\AccountLogModel;
use app\ddkc\model\DdGoodsModel;
use app\ddkc\model\MiningOrderModel;
use app\mainadmin\model\SettingsModel;
use app\distribution\model\DividendRoleModel;
// use app\ddkc\model\MiningPaymentModel;
// use app\ddkc\model\MiningQuotationModel;
// use app\ddkc\model\MiningRechargeModel;
/*------------------------------------------------------ */
//-- 矿机相关逻辑
/*------------------------------------------------------ */

class Miner extends ApiController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
		$this->checkLogin();//验证登陆
        $this->Model = new DdGoodsModel();
    }
    /*------------------------------------------------------ */
	//-- 获取所有矿机列表
	/*------------------------------------------------------ */
 	public function getMiningList(){
 		$setting = (new SettingsModel())->getRows();
		$roleModel = new DividendRoleModel();

		$this->sqlOrder = 'miner_id ASC';
		$where[] = ['is_on_sale' ,'eq' ,1];
		$where[] = ['type' ,'eq' ,input('type')];

        $data = $this->getPageList($this->Model,$where);
        if (count($data['list']) > 0) {
        	foreach ($data['list'] as $key => $value) {
        		$imgs = unserialize($value['imgs']);
        		$data['list'][$key]['img'] = $imgs[0];

                $data['list'][$key]['total_output'] = $value['price_min']+(sprintf("%.0f",$value['price_min']*$value['rebate_rate']*$value['scrap_days']/100));
                // 可购买等级
                $where = [];
                $where[] = ['role_id','IN',$value['limit_user_role']];
                $limit_buy_role = $roleModel->where($where)->column('role_name');
                $data['list'][$key]['limit_buy_role'] = implode(' | ',$limit_buy_role);
        	}
        }
        $data['is_integral_pay'] = $setting['is_integral_pay'];
		return $this->ajaxReturn($data);
	}
	/*------------------------------------------------------ */
	//-- 获取矿机信息
	/*------------------------------------------------------ */
 	public function getMiningInfo(){

 		$user = $this->userInfo;
 		if (!$user) {
			return $this->ajaxReturn(['code' => 0,'msg' => '请先登录','url' => url('passport/login')]);
		}
 		$id = input('id');
 		if (!$id) {
			return $this->ajaxReturn(['code' => 0,'msg' => '数据不合法','url' => url('miner/index')]);
		}
		$data = $this->Model->where('miner_id',$id)->find();	
		if (!$data) {
			return $this->ajaxReturn(['code' => 0,'msg' => '数据不存在','url' => url('miner/index')]);
		}
		if ($data['is_on_sale'] != 1) {
			return $this->ajaxReturn(['code' => 0,'msg' => '商品已下架','url' => url('miner/index')]);
		}
 		$setting = (new SettingsModel())->getRows();
        $QuotationModel = new MiningQuotationModel();
        #今日ZBL行情价格
        $todayMarket = $QuotationModel->todayMarket();
		$data['RMB_USDT'] =  sprintf("%.2f",1/$setting['USDT_RMB']);
		$data['RMB_ZBL']  =sprintf("%.2f",1/$todayMarket['money']*$setting['USDT_RMB']);

		return $this->ajaxReturn(['code' => 1,'result' => $data]);
	}
	/*------------------------------------------------------ */
	//-- 购买矿机
	/*------------------------------------------------------ */
 	public function buyMining(){
 		$user = $this->userInfo;
 		$userModel = new UsersModel();
 		$orderModel = new MiningOrderModel();
 		$accountModel = new AccountLogModel();

 		$data = input('post.');
 		$pay_money = $data['pay_money'];// 支付金额

		# =================================== 数据验证START ==============================
        # 是否登录
        $user = $userModel->info($this->userInfo['user_id']);
        if (!$user) {
            return $this->ajaxReturn(['code' => 0,'msg' => '请先登录','url' => url('passport/login')]);
        }
        if($user['role']['role_id']==0){
            return $this->ajaxReturn(['code' => 0,'msg' => '请先实名认证和至少上传两种收款信息']);
        }
        
 		$where_m[] = ['miner_id','=',$data['id']];
 		$where_m[] = ['is_on_sale','=',1];
 		$mining = $this->Model->where($where_m)->find();	
		if (!$mining) return $this->ajaxReturn(['code' => 0,'msg' => '产品不存在或已下架']);

		# 支付金额是否在区间内
		if ($pay_money < $mining['price_min'] || $pay_money > $mining['price_max']) return $this->ajaxReturn(['code' => 0,'msg' => '支付金额不在区间内']);

		# 资金是否足够
		$account_field = $mining['type'] == 1 ? 'balance_money' : 'ddb_money';
		if ($user['account'][$account_field] < $pay_money) {
			return $this->ajaxReturn(['code' => 0,'msg' => '当前可用余额不足']);
 		}
 		# 支付密码是否正确
 		// $payword = f_hash($data['pay_password']);
 		// if ($payword != $user['pay_password']) return $this->ajaxReturn(['code' => 0,'msg' => '支付密码错误']);
 		
 		# 信用积分是否足够
 		if ($user['account']['use_integral'] < $mining['credit_integral']) {
			return $this->ajaxReturn(['code' => 0,'msg' => '当前信用积分不足']);
 		}

 		# 当前身份是否可购买
 		if ($mining['limit_user_role']) {
 			$role_arr = explode(",",$mining['limit_user_role']);
 			if (!in_array($user['role_id'], $role_arr)) return $this->ajaxReturn(['code' => 0,'msg' => '当前身份无法购买']);
 		} 		
 		
 		# 限购验证	
 		$where_o[] = ['miner_id','=',$data['id']];
	 	$where_o[] = ['status','=',1];
	 	$where_o[] = ['user_id','=',$user['user_id']];
	 	$today = strtotime(date("Y-m-d"),time());

		# 是否超过总限购 	
 		if ($mining['limit_buy']) {
	 		$orderCount = $orderModel->where($where_o)->count('order_id');
	 		if ($orderCount >= $mining['limit_buy']) {
				return $this->ajaxReturn(['code' => 0,'msg' => '该产品总限购'.$mining['limit_buy']]);
	 		}
 		}
	 	$where_o[] = ['add_time','>=',$today];

 		# 是否超过日限购
 		if ($mining['day_limit_buy']) {
	 		$orderCount = $orderModel->where($where_o)->count('order_id');
	 		if ($orderCount >= $mining['limit_buy']) {
				return $this->ajaxReturn(['code' => 0,'msg' => '该产品当日限购'.$mining['day_limit_buy']]);
	 		}
 		}
 		
 		# 是否超过日库存
 		if ($mining['stock']) {
 			unset($where_o[2]);
	 		$orderCount = $orderModel->where($where_o)->count('order_id');
	 		if ($orderCount >= $mining['limit_buy']) {
				return $this->ajaxReturn(['code' => 0,'msg' => '该产品当日库存已售完']);
	 		}
 		}
		# ===================================== 数据验证END =============================
		
		# ==================================== 数据处理START ============================
 		Db::startTrans();
 		# 添加订单表
 		$orderData = [
 			'order_sn'         => $orderModel->getOrderSn(),
 			'user_id'          => $user['user_id'],
 			'miner_id'         => $mining['miner_id'],
 			'miner_name'       => $mining['miner_name'],
 			'original_img'     => $mining['imgs'],
 			'price_min'        => $mining['price_min'],
 			'price_max'        => $mining['price_max'],
 			'rebate_rate'      => $mining['rebate_rate'],
 			'scrap_days'       => $mining['scrap_days'],
 			'surplus_days'     => $mining['scrap_days'],
 			'pay_type'         => $mining['type'],
 			'pay_money'        => $pay_money,
 			'type'             => $mining['type'],
 			'add_time'         => time(),
 			'update_time'      => time(),
 		];
 		$res1 = $orderModel->create($orderData); 		
 		if (!$res1['order_id']) {
 			Db::rollback();
			return $this->ajaxReturn(['code' => 0,'msg' => '订单添加失败.']);
 		}

 		# 扣除相关金额添加日志
		$cData[$account_field] = $pay_money * -1;
    	$cData['by_id']        = $res1['order_id'];
 		if ($mining['type'] == 1) {
	        $cData['change_desc'] = '购买矿机';
	        $cData['change_type'] = 100;
 		}else{
 			$cData['change_desc'] = '购买定存包';
	        $cData['change_type'] = 101;
 		}
        $res2 = $accountModel->change($cData, $user['user_id'], false);        
        if ($res2 !== true) {
            Db::rollback();
			return $this->ajaxReturn(['code' => 0,'msg' => '金额扣除失败.']);
        }
        # 直推奖&团队奖
        $res3 = $orderModel->extensionProfit($res1['order_id']);
        if ($res3['code'] != 1) {
        	Db::rollback();
			return $this->ajaxReturn(['code' => 0,'msg' => $res3['msg']]);
        }
		Db::commit();
		# ===================================== 数据处理END =============================

		return $this->ajaxReturn(['code' => 1,'msg' => '购买成功.','url' => url('miner/index')]);
	}
	/*------------------------------------------------------ */
	//-- 我的矿机列表
	/*------------------------------------------------------ */
	public function getMyMiner(){
 		$orderModel = new MiningOrderModel();
 		$post = input('post.');
 		$page = 10;

 		$where[] = ['user_id','=',$this->userInfo['user_id']];
 		$where[] = ['type','=',$post['type']];
 		if ($post['status']) $where[] = ['status' ,'=',$post['status']];
 		
		$data['list'] = $orderModel
			->where($where)
			->order('order_id DESC')
			->limit($post['p']*$page,$page)
			->select();

		if (count($data['list']) > 0) {
        	foreach ($data['list'] as $key => $value) {
        		$imgs = unserialize($value['original_img']);
        		$data['list'][$key]['img'] = $imgs[0];

        		// 到期收益
        		$data['list'][$key]['total_profit'] = round($value['pay_money'] + ($value['pay_money'] * $value['rebate_rate'] / 100 * $value['scrap_days']),2);
        		
        		// 合约期限
        		$expire_data = $value['add_time'] + ($value['scrap_days']*86400+86400);
        		$data['list'][$key]['expire_data'] = date("Y.m.d",$expire_data);

        		$data['list'][$key]['img'] = $imgs[0];
        		$data['list'][$key]['add_time'] = date("Y.m.d",$value['add_time']);
        	}
        }

		return $this->ajaxReturn($data);
	}
	/*------------------------------------------------------ */
	//-- 获取矿机订单详情
	/*------------------------------------------------------ */
	public function getMiningOrderDetail(){
 		$orderModel = new MiningOrderModel();
        $profitModel = new MiningProfitLog();

		$order_id = input('post.order_id');
		$data = $orderModel->where(['order_id' => $order_id,'user_id' => $this->userInfo['user_id']])->find();
		if (!$data) {
			return $this->ajaxReturn(['code' => 0,'msg' => '数据错误','url' => url('miner/index')]);
		}
        $data['title'] = $data['status']==1 ? '运行中' : '已停止';

		// 累积产量
        $total_yield = $profitModel->where(['order_id' => $order_id,'type' => 0])->sum('profit');
		// 终止日期
		$over_time = $data['add_time']+($data['scrap_days']*24*60*60);
        
        $imgs = unserialize($data['original_img']);
        $data['img'] = $imgs[0];
        // 购买时间
        $data['add_date'] = date("Y-m-d H:i:s",$data['add_time']);
        // 每天产量
        $data['day_yield'] = round($data['miner_price']*$data['rebate_rate']/100,2);
        $data['total_yield'] = $total_yield;
        $data['next_time'] = $next_time;
        $data['over_date'] = date("Y-m-d H:i:s",$over_time);

		return $this->ajaxReturn(['code' => 1,'msg' => '获取成功','data' => $data]);
	}
	/*------------------------------------------------------ */
	//-- 领取收益
	/*------------------------------------------------------ */
	public function drawProfit(){
        $profitModel = new MiningProfitLog();
 		$orderModel = new MiningOrderModel();
 		$accountModel = new AccountLogModel();

		$order_id = input('post.order_id');
		$order = $orderModel->where(['order_id' => $order_id,'user_id' => $this->userInfo['user_id']])->find();
		if (!$order) {
			return $this->ajaxReturn(['code' => 0,'msg' => '数据错误','url' => url('miner/index')]);
		}
		if ($order['pay_type'] == 2) {
			// 开始领取收益时间
			$thaw_time = $order['add_time']+($order['returnable_day']*24*60*60);
			if ($thaw_time > time()) {
				return $this->ajaxReturn(['code' => 0,'msg' => '矿机冻结中,暂不可领取','url' => '']);
			}
		}

		$user = $this->userInfo;
		# 该订单可领取金额
		$where = ['order_id' => $order_id,'user_id' => $user['user_id'],'status' => 0];
		$profit = $profitModel->where($where)->where(['type' => 0])->sum('profit'); // 收益
		$pincipal = $profitModel->where($where)->where(['type' => 1])->sum('profit'); // 本金
		$total = $profit+$pincipal;
		if ($total <= 0) {
			return $this->ajaxReturn(['code' => 0,'msg' => '没有可领取的收益','url' => '']);
		}

        Db::startTrans();
		# 更新收益记录状态
        $res1 = $profitModel->where($where)->update(['status' => 1,'update_time' => time()]);
        if (!$res1) {
        	Db::rollback();
			return $this->ajaxReturn(['code' => 0,'msg' => '收益记录更新失败','url' => '']);
        }

		# 添加账号ZBL金额
		$ZBLData['zbl_money']   = $total;
        $ZBLData['change_desc'] = '领取收益';
        $ZBLData['change_type'] = 102;
        $ZBLData['by_id']       = $order_id;
        $res2 = $accountModel->change($ZBLData, $user['user_id'], false);        
        if ($res2 !== true) {
            Db::rollback();
			return $this->ajaxReturn(['code' => 0,'msg' => '添加ZBL失败','url' => '']);
        }

        # 由于后续奖项都以收益金额为基数 没有收益时直接返回 
        if ($profit <= 0) {
        	Db::commit();
			return $this->ajaxReturn(['code' => 1,'msg' => '领取成功','url' => '']);
        }

		# 直推奖
		if ($user['pid']) {
        	$straightAward = $profitModel->straightAward($user['user_id'],$user['pid'],$profit,$order_id);
        	if ($straightAward['code'] != 1) {
	            Db::rollback();
				return $this->ajaxReturn(['code' => 0,'msg' => $straightAward['msg'],'url' => '']);
        	}
		}

		# 团队奖
        $teamAward = $profitModel->teamAward($user['user_id'],$profit,$order_id);
        if (!$teamAward) {
        	Db::rollback();
			return $this->ajaxReturn(['code' => 0,'msg' => '团队奖返利失败','url' => '']);
        }

		# 返回数据
        Db::commit();
		return $this->ajaxReturn(['code' => 1,'msg' => '领取成功','url' => '']);
	}
	/*------------------------------------------------------ */
	//-- 我的ZBL矿机列表
	/*------------------------------------------------------ */
	public function getZblMiningList(){
 		$orderModel = new MiningOrderModel();
 		$post = input('post.');
 		$page = 10;

 		$where[] = ['user_id','=',$this->userInfo['user_id']];
 		$where[] = ['pay_type','=',1];

 		if ($post['status'] == 1) {
 			$where[] = ['status' ,'=',1];
 		}elseif ($post['status'] == 2) {
 			$where[] = ['status' ,'<>',1];
 		}
		$data['list'] = $orderModel
			->where($where)
			->order('order_id DESC')
			->limit($post['p']*$page,$page)
			->select();
		if (count($data['list']) > 0) {
        	foreach ($data['list'] as $key => $value) {
        		// 总收益
        		$day_profit = $value['miner_price']*$value['rebate_rate']/100;
        		$total_profit = round($value['scrap_days']*$day_profit,2);
        		// 剩余收益
        		$surplus_profit = round($value['surplus_days']*$day_profit,2);
        		// 已运行比例
        		$work_rate = round(100-($surplus_profit/$total_profit*100),2);

        		if ($value['status'] != 1) {
        			// 不在运行中的矿机剩余收益和运行比例固定
        			$surplus_profit = 0;
        			$work_rate = 100;
        		}else{
        			// 运行中的矿机有倒计时
        			$date_s = $value['scrap_days']*24*60*60; // 可运行总秒数
	        		$last_time = $value['add_time']+$date_s; // 到期秒数
	        		$surplus_time[] = ($last_time-time())*1000;// 剩余秒数*1000
        		}
        		$data['list'][$key]['total_profit'] = $total_profit;
        		$data['list'][$key]['surplus_profit'] = $surplus_profit;
        		$data['list'][$key]['work_rate'] = $work_rate;
        	}
        }
        $data['surplus_time'] = $surplus_time;
		return $this->ajaxReturn($data);
	}
	/*------------------------------------------------------ */
	//-- 我的团队矿机
	/*------------------------------------------------------ */
	public function getMiningTeam(){
		$roleModel = new DividendRoleModel();
		$orderModel = new MiningOrderModel();
 		$userModel = new UsersModel();
 		$AccountLogModel = new AccountLogModel();
        $userInfo  = $this->userInfo;

        $up_user = [];
        $users = DB::name('users')->where('user_id',$userInfo['user_id'])->select();
        #所有的下级
        $up_user = $this->getUpUser($users,$up_user);
 		$post = input('post.');
 		$page = 10;
 		$data['list'] = $userModel
 			->field('user_id,nick_name,headimgurl,role_id,mobile')
			->where(['pid' => $this->userInfo['user_id']])
            // ->where('user_id','in',$up_user)
 			->limit($post['p']*$page,$page)
			->select();
        $MiningPaymentModel = new MiningPaymentModel();
		foreach ($data['list'] as $key => $value) {
			if (strlen($value['nick_name']) > 15) {
				$data['list'][$key]['nick_name'] = substr($value['nick_name'],0,15).'...';
			}
			# 身份名称
			$data['list'][$key]['role_name'] = $roleModel->info($value['role_id'],true);
			# 正在运行矿机数
			$data['list'][$key]['miner_num'] = $orderModel->where(['user_id' => $value['user_id'],'status' => 1])->count();
			# 总算力
			//$data['list'][$key]['power'] = $orderModel->where(['user_id' => $value['user_id'],'status' => 1])->sum('power');
		}
		if ($post['p'] == 0) {
	 		// $subs = $userModel->where(['pid' => $this->userInfo['user_id']])->column('user_id');
			# 战队名称
//            if($this->userInfo['role']['role_id']<10){
//                $top_user = $this->getShopowner($this->userInfo['pid']);
//                if(!empty($top_user)){
//                    $where[] = ['type','=',4];
//                    $where[] = ['user_id','=',$top_user['user_id']];
//                    $pay_info = $MiningPaymentModel->where($where)->find();
//                    #是否存在微信号,存在则显示微信号,不存在则显示自己信息
//                    $data['teamInfo']['wx_number'] = $pay_info['wx_number'];
//                    $data['teamInfo']['team_name'] = $top_user['nick_name'];
//                    $data['teamInfo']['headimgurl'] = $top_user['headimgurl'];
//                }else{
//                    $data['teamInfo']['team_name'] = $this->userInfo['nick_name'];
//                    $data['teamInfo']['wx_number'] = '';
//                    $data['teamInfo']['headimgurl'] = $this->userInfo['headimgurl'];
//                }
//            }else{
                $where[] = ['type','=',4];
                $where[] = ['user_id','=',$this->userInfo['user_id']];
                $pay_info = $MiningPaymentModel->where($where)->find();

                $data['teamInfo']['team_name'] = $this->userInfo['nick_name'];
//                $data['teamInfo']['wx_number'] = $pay_info['wx_number'];
                $data['teamInfo']['headimgurl'] = $this->userInfo['headimgurl'];
//            }
//	 		$data['teamInfo']['signature'] = $this->userInfo['signature'];
//	 		$data['teamInfo']['qq'] = $this->userInfo['qq'];
			# 团队总算力
            $up_user[] = $userInfo['user_id'];
			$where_t_p[] = ['user_id','IN',$up_user];
//			$where_t_p[] = ['status','=',1];
			//团队无限层总得矿池资产
            $data['teamInfo']['bteh_frozen'] = $AccountLogModel->where($where_t_p)->sum('bteh_frozen');
            //团队无限层总的入金金额
            $where_t_p[] = ['status','gt',0];
            $where_t_p[] = ['status','lt',7];
            $MiningRechargeModel = new MiningRechargeModel();
            $data['teamInfo']['gold_entry'] = $MiningRechargeModel->where($where_t_p)->sum('money');
			// $data['teamInfo']['team_power'] = $orderModel->where($where_t_p)->sum('power');
            # 个人算力
            // $data['teamInfo']['self_power'] = $orderModel->where(['user_id' => $this->userInfo['user_id'],'status' => 1])->sum('power');
            # 团队总人数
			$data['teamInfo']['team_number'] = count($up_user);
		}
		return $this->ajaxReturn($data);
	}

	//找最近的店长
    public function getShopowner($user_id = 0){
        $userModel = new UsersModel();
        $user = $userModel->info($user_id);
        if(empty($user)){
            return '';
        }
        #店长及以上的返回信息
        if($user['role']['role_id']>9){
            return $user;
        }
        # 继续递归
        return $this->getShopowner($user['pid']);
    }

    //找所有下级
    public function getUpUser($users,$up_arr){
	    $arr = [];
        foreach ($users as $k=>$v){
           $list = DB::name('users')->where('pid',$v['user_id'])->field('user_id,pid')->select();
           foreach ($list as $i=>$j){
               $arr[] = $j;
               $up_arr [] =$j['user_id'];
           }
        }
        if(empty($arr)){
            return $up_arr;
        }
        return $this->getUpUser($arr,$up_arr);
    }
}
