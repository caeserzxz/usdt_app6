<?php
namespace app\shop\model;
use app\BaseModel;
use think\facade\Cache;
//*------------------------------------------------------ */
//-- 商品优惠券
/*------------------------------------------------------ */
class BonusModel extends BaseModel
{
	protected $table = 'shop_bonus_type';
	public  $pk = 'type_id';
	protected $mkey = 'bonus_info_mkey_';
	/*------------------------------------------------------ */
    //--  清除memcache
    /*------------------------------------------------------ */
    public function cleanMemcache($type_id){
        Cache::rm($this->mkey.$type_id);
        if ($this->userInfo['user_id'] > 0){
            Cache::rm($this->mkey.'_user_'.$this->userInfo['user_id']);
        }
    }
	/*------------------------------------------------------ */
	//-- 生成数量
	//-- $bonus_sum number 优惠券数量
	//-- $userIds array 会员id列表
	/*------------------------------------------------------ */
	public function makeBonusSn($type_id,$bonus_sum,$userIds=array()){
		/* 生成优惠券序列号 */
		$BonusListModel = new BonusListModel();
		$num = $BonusListModel->max('bonus_sn');
		$num = $num ? floor($num / 10000) : 100000;
		$bonus_id = array();
		$addnum = 1;
		$time = time();
		if (is_array($userIds) == false){
			$userIds = explode(',',$userIds);
		}
		if (empty($userIds) == false){
			do{
				$uid = reset($userIds);
				if ($uid < 1) continue;
				$arr['type_id'] = $type_id;
				$arr['bonus_sn'] = ($num + $addnum ) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
				$arr['user_id'] = $uid;
				$arr['add_time'] = $time;
				$res = $BonusListModel::create($arr);
				if ($res > 0){
					$addnum++;
					array_shift($userIds);
				}
			}while(empty($userIds) == false);	
		}else{		
			do{
				$arr['type_id'] = $type_id;
				$arr['bonus_sn'] = ($num + $addnum ) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
				$arr['add_time'] = $time;
				$res = $BonusListModel::create($arr);
				if ($res > 0) $addnum++;
			}while($addnum < $bonus_sum);			
		}
		return $addnum;
	}
	/*------------------------------------------------------ */
	//-- 获取优惠券信息
	//-- $type_id 优惠券主ID
	/*------------------------------------------------------ */
    public function info($type_id = 0){
		$type_id = $type_id * 1;
		$info = Cache::get($this->mkey.$type_id);
		if ($info) return $info;
		$info = $this->find($type_id);
		$gmtime = time();
        if ($row['order_id'] > 0) {//已使用
            $info['stauts'] = 2;
            $info['stauts_info'] = '已使用';
        }elseif ($info['use_start_date'] > $gmtime){
			$info['stauts'] = 0;
			$info['stauts_info'] = '未到使用时间';
		}elseif ($info['use_end_date'] < $gmtime){
			$info['stauts'] = -1;
			$info['stauts_info'] = '已过期';
		}else{
			$info['stauts'] = 1;
			$info['stauts_info'] = '未使用';
		}
        $info['_use_start_date'] = date('Y-m-d',$info['use_start_date']);
        $info['_use_end_date'] = date('Y-m-d',$info['use_end_date']);
		Cache::set($this->mkey.$type_id,$info,30);
    	return $info;
	}

    /*------------------------------------------------------ */
    //-- 获取优惠券信息
    //-- bonus_id 优惠ID
    /*------------------------------------------------------ */
    public function binfo($bonus_id = 0)
    {
        $BonusListModel = new BonusListModel();
        $bonus = $BonusListModel->find($bonus_id);
        $bonus['info'] = $this->info($bonus['type_id']);
        return $bonus;
    }
	/*------------------------------------------------------ */
	//-- 获取用户优惠券列表
	//-- $type_id 优惠券主ID
	/*------------------------------------------------------ */
    public function getListByUser($uid = 0){
		if ($uid < 1){
			$uid = $this->userInfo['user_id'] * 1;
			if ($uid < 1) return [];
		}
		$mkey = $this->mkey.'_user_'.$uid;
		$list = Cache::get($mkey);
		if (empty($list['unused']) == false || empty($list['used']) == false || empty($list['expired']) == false){
			return $list;
		}
		$exptime = time() - 15552000;//只查询半年180天内的数据	
		$BonusListModel = new BonusListModel();
		$rows = $BonusListModel->where("user_id = $uid and add_time > $exptime")->select()->toArray();
        $list['used'] = array();
        $list['expired'] = array();
        $list['unused'] = array();
		foreach ($rows as $row){
			$row['bonus'] = $this->info($row['type_id']);		
			if ($row['order_id'] > 0){//已使用
				$list['used'][$row['bonus_id']] = $row;
			}elseif ($row['bonus']['stauts'] == -1){//已过期的
				$list['expired'][$row['bonus_id']] = $row;
			}elseif($row['bonus']['stauts'] == 1){
				$list['unused'][$row['bonus_id']] = $row;
			}
		}
		$list['usedNum'] = count($list['used']);
		$list['expiredNum'] = count($list['expired']);
		$list['unusedNum'] = count($list['unused']);
		Cache::set($mkey,$list,60);//缓存30秒
		return $list;
	}
	/*------------------------------------------------------ */
	//-- 注册送红包
	/*------------------------------------------------------ */
	public function sendByReg($uid = 0){
		if ($uid < 1) return false;
		$time = time();
		$where[] = ['send_type','=',4];
		$where[] = ['send_start_date','<',$time];
		$where[] = ['send_end_date','>',$time];
		$bonusIds = $this->where($where)->column('type_id');
		foreach ($bonusIds as $bonus_id){
			$this->makeBonusSn($bonus_id,1,$uid);
		}
		return true;
	}
	
}
