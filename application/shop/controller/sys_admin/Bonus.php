<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;

use app\shop\model\BonusModel;
use app\shop\model\BonusListModel;
use app\member\model\AccountModel;
use app\member\model\UsersLevelModel;
/**
 * 优惠券相关
 * Class Index
 * @package app\store\controller
 */
class Bonus extends AdminController
{
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
    public function initialize(){	
   		parent::initialize();
		$this->Model = new BonusModel();
		$this->store_id = 0;//当前默认为总后台，门店值默认为0 
		
    }
	/*------------------------------------------------------ */
	//-- 主页
	/*------------------------------------------------------ */
    public function index()
    {
		$this->getList(true);
        return $this->fetch('index');
    }

  	/*------------------------------------------------------ */
    //-- 获取列表
	//-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false,$is_delete=0) {	
		$where[] = ['store_id','=',$this->store_id];
		$search['status'] = input('status',0,'intval');
		$search['send_type'] = input('send_type',-1,'intval');
		$time = time();
		switch ($search['status']){
			case 1://发放中
			  	$where[] = ['send_start_date','<',$time];
				$where[] = ['end_start_date','>',$time];
			  break;  
			case 2://可使用
			  	$where[] = ['use_start_date','<',$time];
				$where[] = ['use_end_date','>',$time];
			  break;
			case 3://已过期
				$where[] = ['use_end_date','<',$time];
			  break;
			default:
			break;
		}
		if ($search['send_type'] >= 0){
			$where[] = ['send_type','=',$search['send_type']];
		}
		$search['keyword'] =  input('keyword','','trim');
		if (empty($search['keyword']) == false){
			 $where[] = " type_name like '%".$search['keyword']."%' "; 
		}
        $data = $this->getPageList($this->Model, $where);
		
		$this->assign("data", $data);
		$this->assign("search", $search);
		$this->assign("SendType", $this->getDict('BonusSendType'));
		if ($runData == false){
			$data['content'] = $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }

	/*------------------------------------------------------ */
	//-- 添加修改前调用
	/*------------------------------------------------------ */
	public function asInfo($row){
		$this->assign("SendType", $this->getDict('BonusSendType'));		
		return $row;
	}
	
	/*------------------------------------------------------ */
	//-- 添加前调用
	/*------------------------------------------------------ */
	public function beforeAdd($row){
		if (empty($row['send_start_date']))  return $this->error('请选择发放起始日期.');
		if (empty($row['send_end_date']))  return $this->error('请选择发放结束日期.');
		if (empty($row['use_start_date']))  return $this->error('请选择使用起始日期.');
		if (empty($row['use_end_date']))  return $this->error('请选择使用结束日期.');
		$row['send_start_date'] = strtotime($row['send_start_date']);
		$row['send_end_date']   = strtotime($row['send_end_date']);
		if ($row['send_start_date'] >= $row['send_end_date']) return $this->error('发放结束日期必须大于发放起始日期！');
		$row['use_start_date']  =  strtotime($row['use_start_date']);
		$row['use_end_date']    =  strtotime($row['use_end_date']);
		if ($row['use_start_date'] >= $row['use_end_date']) return $this->error('使用结束日期必须大使用起始日期！');
		$row['add_time'] = time();
		if ($row['type_money'] > $row['min_amount']) return $this->error('优惠券金额不能大于最小订单金额！');
		return $row;
	}
	/*------------------------------------------------------ */
	//-- 添加后调用
	/*------------------------------------------------------ */
	public function afterAdd($row){
		$this->_log($row['type_id'],'添加优惠券：'.$row['type_name'].'-'.$row['type_money']);
		return $this->success('添加成功',url('index'));
	}
	/*------------------------------------------------------ */
	//-- 修改前调用
	/*------------------------------------------------------ */
	public function beforeEdit($row){
		return $this->beforeAdd($row);
	}
	/*------------------------------------------------------ */
	//-- 修改后调用
	/*------------------------------------------------------ */
	public function afterEdit($row){
		$this->_log($row['type_id'],'修改优惠券：'.$row['type_name'].'-'.$row['type_money']);
		return $this->success('修改成功',url('index'));
	}
	/*------------------------------------------------------ */
	//-- 发送红包
	/*------------------------------------------------------ */
	public function send(){
		$type_id = input('type_id',0,'intval');
		if ($type_id < 1) return $this->error('获取传值失败.');
		$row = $this->Model->find($type_id);
		if (empty($row)) return $this->error('优惠券不存在.');
		$UsersLevelModel = new UsersLevelModel(); 
		$levelList = $UsersLevelModel->getRows();
		if ($this->request->isPost()){
			if ($row['send_type'] == 3){
				$send_num = input('send_num',0,'intval');
				if ($send_num < 1) return $this->error('发送数量必须大于0.');
				if ($send_num > 2000) return $this->error('单次只允许最多发送2000.');
				$num = $this->Model->makeBonusSn($type_id,$send_num);
			}else{
				$user_level = input('user_level',-1,'intval');
				$userIds = input('user_id');
				if (empty($userIds) == false && $user_level >= 0){
                    return $this->error('按级别发放和指定会员，不能同时设置.');
                }
				if ($user_level >= 0){
					$level = $levelList[$user_level];
					$AccountModel = new AccountModel();
					$where[] = ['total_integral','between',[$level['min'],$level['max']]];
					$userIds = $AccountModel->where($where)->column('user_id');
				}
				if (empty($userIds)) return $this->error('没有找到相关可分配的会员.');
				$num = $this->Model->makeBonusSn($type_id,0,$userIds);
				
			}
			if ($num < 1) return $this->error('发送红包失败，请尝试重新提交.');
			return $this->success('操作成功.',url('index'));
		}
		$this->assign("row", $row);	
		$this->assign("levelList", $levelList);	
		return $this->fetch('send');
	}
	/*------------------------------------------------------ */
	//-- 红包列表
	/*------------------------------------------------------ */
	public function bonusList(){		
		$this->assign("start_date", date('Y/m/01',strtotime("-1 months")));
		$this->assign("end_date",date('Y/m/d'));
		$this->bList(true);
		return $this->fetch('bonusList');
	}
	/*------------------------------------------------------ */
	//-- 列表
	/*------------------------------------------------------ */ 
	public function bList($runData = false){
		$bonus_type_id = input('type_id',0,'intval');
		$this->assign("bonus", $this->Model->info($bonus_type_id));	
		$BonusListModel = new BonusListModel();
		$where[] = ['type_id','=',$bonus_type_id];
		$usd_type = input('usd_type',-1,'intval');
		if ($usd_type == 0){
			$where[] = ['order_id','=',0];
		}elseif ($usd_type == 1){
			$where[] = ['order_id','>',0];
			$reportrange = input('reportrange','','trim');
            $dtime = explode('-',$reportrange);
            $where[] = ['used_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
		}
		$keyword = input('keyword','','trim');
		if (empty($keyword) == false){
			 $where['and'][] = " user_id = '".$keyword."' OR order_sn = '".$keyword."' ";
		}
		$data = $this->getPageList($BonusListModel,$where);
		$this->assign("data", $data);
		if ($runData == false){
			$data['content'] = $this->fetch('bList');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
	}
	/*------------------------------------------------------ */
	//-- 导出线下发放的信息
	/*------------------------------------------------------ */
	public function getExcel()
	{
		$type_id = input('type_id',0,'intval');
		if ($type_id < 1) return $this->error('获取传值失败！');
		$bonus = $this->Model->info($type_id);
		/* 文件名称 */
    	
        $filename = iconv('utf-8' , 'GBK//IGNORE',$bonus['type_name']);
    	header("Content-type: application/vnd.ms-excel;");
		header("Content-Disposition: attachment; filename=$filename.xls");
		
    	/* 文件标题 */
		$title = "优惠券：".$bonus['type_name']."\t\n";
		$title .= "面额：".$bonus['type_money']."\t\n";
		$title .= "消费金额：".$bonus['min_amount']."\t\n";
		$title .= "使用开始时间：".dateTpl($bonus['use_start_date'])."\t\n";
		$title .= "使用结束时间：".dateTpl($bonus['use_end_date'])."\t\n";
		
    	echo iconv('utf-8' , 'GBK//IGNORE', $title) ;
    	/* 红包序列号, 红包金额, 类型名称(红包名称), 使用结束日期 */
    	echo iconv('utf-8' , 'GBK//IGNORE', '红包序列号') ."\t";
		echo iconv('utf-8' , 'GBK//IGNORE', '使用会员') ."\t";
		echo iconv('utf-8' , 'GBK//IGNORE', '相关订单') ."\t\n";
		$BonusListModel = new BonusListModel();
		$rows = $BonusListModel->where('type_id',$type_id)->select();
		
		$type_name = iconv('utf-8' , 'GBK//IGNORE', $row['type_name']);
		foreach ($rows as $row){
			echo $row['bonus_sn']."\t";
			echo iconv('utf-8' , 'GBK//IGNORE',dateTpl($row['used_time']))."\t";
			echo iconv('utf-8' , 'GBK//IGNORE', $row['user_id'].'-'.userInfo($row['user_id']))."\t";
			echo $row['order_sn']."\t\n";
		}
	}
}
