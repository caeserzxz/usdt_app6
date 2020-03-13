<?php
namespace app\ddkc\model;
use app\BaseModel;
use think\facade\Cache;
use app\member\model\UsersModel;
use app\member\model\AccountLogModel;
use app\distribution\model\DividendRoleModel;

//*------------------------------------------------------ */
//-- 矿机订单表
/*------------------------------------------------------ */
class MiningOrderModel extends BaseModel
{
	protected $table = 'dd_mining_order';
	public  $pk = 'order_id';
	protected static $mkey = 'mining_order_list';
	
	 /*------------------------------------------------------ */
	//-- 清除缓存
	/*------------------------------------------------------ */ 
	public function cleanMemcache(){
		Cache::rm(self::$mkey);
	}
	/*------------------------------------------------------ */
	//-- 获取列表
	/*------------------------------------------------------ */ 
	public  function getRows(){
		$data = Cache::get(self::$mkey);
		if (empty($data) == false){
			return $data;
		}
		$rows = $this->field('*,order_id as id')->order('order_id DESC')->select()->toArray();		
		foreach ($rows as $row){
			$data[$row['order_id']] = $row;
		}
		Cache::set(self::$mkey,$data,600);
		return $data;
	}
	/*------------------------------------------------------ */
    //-- 生成订单编号
    /*------------------------------------------------------ */
    public function getOrderSn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);
        $date = date('Ymd');
        $order_sn = $date . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $where[] = ['order_sn', '=', $order_sn];
        $where[] = ['add_time', '>', strtotime($date)];
        $count = $this->where($where)->count('order_id');
        if ($count > 0) return $this->getOrderSn();
        return $order_sn;
    }
    /*------------------------------------------------------ */
    //-- 购买矿机后升级
    /*------------------------------------------------------ */
    public function upgrade($user_id){
    	if (!$user_id) return true;

    	$userModel = new UsersModel();
    	$roleModel = new DividendRoleModel();
        $accountModel = new AccountLogModel();

 		$user = $userModel->info($user_id,'',false);
    	# 所有可升的身份 降序
 		$senior = $roleModel->where([['role_id','>',$user['role_id']]])->order('role_id DESC')->select();
 		# 已是最高级
 		if (!$senior) return $this->upgrade($user['pid']);

    	# 验证是否具备升级条件
 		foreach ($senior as $key => $value) {
 			$checkStatus = $this->checkMeetingConditions($user_id,$value);
 			if ($checkStatus) {
 				# 具备升级条件
 				$upRole = $value;
 				break;
 			}
 		}
 		# 更新等级
 		if ($upRole) {
 			$res = $userModel->where(['user_id' => $user_id])->update(['role_id' => $upRole['role_id']]);
 			if (!$res) return false;
 		}
    	# 递归上级升级
    	return $this->upgrade($user['pid']);
    }
    /*------------------------------------------------------ */
    //-- 检索用户是否满足升级条件
    //-- user_id 用户id
    //-- role 要升级的身份信息
    //-- MEI 2019-11-07
    /*------------------------------------------------------ */
    public function checkMeetingConditions($user_id,$role){
    	$conditionOne = $conditionTwo = $conditionThree = $conditionFour = true;
    	# 条件一 自购X台矿机
    	if ($role['buy_mining']) {
    		$conditionOne = $this->checkConditionOne($user_id,$role['buy_mining']);
    		if (!$conditionOne) return false;
    	}
    	
    	# 条件二 直推X人投资矿机
    	if ($role['direct_pusher']) {
    		$conditionTwo = $this->checkConditionTwo($user_id,$role['direct_pusher']);
    		if (!$conditionTwo) return false;
    	}

    	# 条件三 团队算力
    	if ($role['count_power']) {
    		$conditionThree = $this->checkConditionThree($user_id,$role['count_power']);
    		if (!$conditionThree) return false;
    	}

    	# 条件四 团队X名X身份
    	if ($role['team_lower_level']) {
    		$conditionFour = $this->checkConditionFour($user_id,$role['team_lower_level'],$role['up_level']);
    		if (!$conditionFour) return false;
    	}
    	if (!$conditionOne || !$conditionTwo || !$conditionThree || !$conditionFour) return false;
    	return true;
    }
    /*------------------------------------------------------ */
    //-- 检索升级条件一是否满足
    //-- 条件描述:自购任意X台矿机
    //-- user_id 用户id
    //-- num 数量
    //-- MEI 2019-11-07
    /*------------------------------------------------------ */
    public function checkConditionOne($user_id,$num){
    	# 自投矿机数
    	$hasNume = $this->where(['user_id' => $user_id])->count();
    	# 是否满足
    	if ($hasNume < $num) return false;
    	return true;
    }
    /*------------------------------------------------------ */
    //-- 检索升级条件二是否满足
    //-- 条件描述:直推X人投资矿机
    //-- user_id 用户id
    //-- num 数量
    //-- MEI 2019-11-07
    /*------------------------------------------------------ */
    public function checkConditionTwo($user_id,$num){
    	$userModel = new UsersModel();
    	# 所有直推下级
    	$allDirectPush = $userModel->where(['pid' => $user_id])->column('user_id');

    	# 直推下级投资矿机的用户数
    	$hasNume = $this->where([['user_id','IN',$allDirectPush]])->group('user_id')->count();

    	# 是否满足
    	if ($hasNume < $num) return false;

    	return true;
    }
    /*------------------------------------------------------ */
    //-- 检索升级条件三是否满足
    //-- 条件描述:团队算力
    //-- user_id 用户id
    //-- num 数量
    //-- MEI 2019-11-07
    /*------------------------------------------------------ */
    public function checkConditionThree($user_id,$num){
    	# 获取团队所有用户
    	$allSub = $this->getTeamAllUser($user_id);
    	# 二维数组合并成一维数组
        $allSubs = [];
        array_walk_recursive($allSub, function($value) use (&$allSubs) {
            array_push($allSubs, $value);
        });
        # 团队算力包含自己
        $allSubs[] = $user_id;

    	# 团队有效算力
    	$hasNume = $this->where([['user_id','IN',$allSubs],['status','=',1]])->sum('power');

    	# 是否满足
    	if ($hasNume < $num) return false;
    	return true;
    }
    /*------------------------------------------------------ */
    //-- 检索升级条件四是否满足
    //-- 条件描述:团队X名X身份
    //-- user_id 用户id
    //-- num 数量
    //-- role 身份
    //-- nowNum 当前满足数量
    //-- MEI 2019-11-07
    /*------------------------------------------------------ */
    public function checkConditionFour($user_id = 0,$num = 0,$role = 0,$nowNum = 0){
    	if (!$user_id) return false;

    	$userModel = new UsersModel();
    	# 当前层级等级达标的用户数量
    	$satisfyNum = $userModel->where([['pid','IN',$user_id],['role_id','>=',$role]])->count();
    	# 累加上历史等级达标数量
    	$nowNum = $nowNum+$satisfyNum;
    	# 总数量达标直接返回
 		if ($nowNum >= $num) return true;

    	# 当前层级不满足的用户继续递归找下一层
    	$nextLayer = $userModel->where([['pid','IN',$user_id],['role_id','<',$role]])->column('user_id');
    	# 递归找下一层
 		return $this->checkConditionFour($nextLayer,$num,$role,$nowNum);
    }
    /*------------------------------------------------------ */
    //-- 获取用户所有满足身份的下级
    //-- user_id 用户id
    //-- role_id 身份
    //-- MEI 2019-11-07
    /*------------------------------------------------------ */
    public function getTeamAllUser($user_id = 0,$role_id = 0,$arr = []){
    	if (!$user_id) return $arr;

    	$userModel = new UsersModel();
        $pid = $userModel->where([['pid','IN',$user_id],['role_id','>',$role_id]])->column('user_id');
        # 没有下一级直接返回
        if (!$pid) return $arr;
        # 追加数组
        $arr[] = $pid;
        # 当前层级继续递归
        return $this->getTeamAllUser($pid,$role_id,$arr);     
    }


    /*------------------------------------------------------ */
    //-- 推广收益
    //-- order_id 订单id
    //-- MEI 2020-3-13
    /*------------------------------------------------------ */
    public function extensionProfit($order_id = 0){
        $accountModel = new AccountLogModel();

        $order = $this->where('order_id',$order_id)->find();
        if (!$order || $order['status'] != 1) return ['code' => 0,'msg' => '订单错误'];

        $userModel = new UsersModel();
        $user = $userModel->info($order['user_id'],'',false);
        if (!$user['pid']) return ['code' => 1];

        $pid = $user['pid'];
        $data['by_id']     = $order_id;
        $data['change_desc'] = '推广收益';
        $data['change_type'] = 102;
        for ($i=1; $i <= 2; $i++) {
            // 上级信息
            $leaderInfo = $userModel->info($pid,'',false);
            if (!$leaderInfo) continue;

            $ratio = $leaderInfo['role']['exten_profit_'.$i];

            // 获拥金额
            $money = round($order['pay_money'] * $ratio / 100,2);
            if ($money <= 0) continue;

            // 添加金额
            $data['ddb_money'] = $money;
            $res = $accountModel->change($data, $pid, false);   
            if (!$res) return ['code' => 0,'msg' => '推广收益执行失败'];

            // 再上级信息
            $pid = $leaderInfo['pid'];
        } 

        # 团队收益
        return $this->teamProfit($order_id); 
    }
    /*------------------------------------------------------ */
    //-- 团队奖
    //-- order_id 订单id
    //-- MEI      2020-3-13
    /*------------------------------------------------------ */ 
    public function teamProfit($order_id){
        $roleModel = new DividendRoleModel();
        $accountModel = new AccountLogModel();

        $order = $this->where('order_id',$order_id)->find();
        if (!$order || $order['status'] != 1) return ['code' => 0,'msg' => '订单错误'];

        # 所有获佣上级
        $winningUsers = $this->getTeamWinningUsers($order['user_id']);
        if (!$winningUsers) return ['code' => 1];

        $lastRatio = 0;
        $data['change_desc'] = '团队奖励';
        $data['change_type'] = 103;
        $data['by_id']       = $order_id;
        # 循环返奖
        foreach ($winningUsers as $key => $value) {
            $nowRole = $roleModel->info($value['role_id']);
            // 减掉上一获奖用户的比例
            $ratio = $nowRole['team_profit']-$lastRatio;
            $money = round($order['pay_money'] * $ratio / 100,2);

            if ($money <= 0) continue;

            // 记录比例 便于计算下一级差
            $lastRatio = $nowRole['team_profit']; 

            # 添加资金
            $data['ddb_money'] = $money;
            $res = $accountModel->change($data, $value['user_id'], false); 

            if (!$res) return ['code' => 0,'msg' => '团队奖执行失败'];
        }
        return ['code' => 1];
    }
    /**
     * 获得团队奖用户 级差形式
     * @param $user_id 用户id $role 当前身份
     * @return array 获佣用户
     */
    public function getTeamWinningUsers($user_id = 0,$role = 0,$arr = []){
        $userModel = new UsersModel();

        $pid = $userModel->where(['user_id'=>$user_id])->value('pid');
        $superior = $userModel->field('user_id,role_id')->where(['user_id'=>$pid])->find();
        if (!$pid || !$superior) return $arr;
        
        $superiorRole = $superior['role_id'];
        $recursionRole = $role;

        if ($superiorRole > $recursionRole) {
            # 遇高级才有资格拿佣金
            $arr[] = ['user_id' => $superior['user_id'],'role_id' => $superiorRole];
            # 拿当前最高等级去递归 避免遇低级再遇高级后重复返 例:4-2-3
            $recursionRole = $superiorRole;
        }
        # 继续递归
        return $this->getTeamWinningUsers($superior['user_id'],$recursionRole,$arr);
    }
}
