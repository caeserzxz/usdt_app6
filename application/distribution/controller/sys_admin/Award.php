<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;
use app\distribution\model\DividendAwardModel;
use app\distribution\model\DividendRoleModel;

use app\shop\model\OrderModel;
use app\shop\model\OrderGoodsModel;

/**
 * 分销奖项管理
 */
class Award extends AdminController
{	
	
	//*------------------------------------------------------ */
	//-- 初始化
	/*------------------------------------------------------ */
   public function initialize()
   {	
   		parent::initialize();
		$this->Model = new DividendAwardModel(); 		
    }
	/*------------------------------------------------------ */
    //--首页
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
    public function getList($runData = false) {
		$usersRole = (new DividendRoleModel)->getRows();//分销身份
        $data = $this->getPageList($this->Model);
		foreach ($data['list'] as $key=>$row){
			$row['limit_role'] = explode(',',$row['limit_role']);
			$limit_role_name = [];
			foreach ($row['limit_role'] as $role_id){
				$limit_role_name[] = $usersRole[$role_id]['role_name'];
			}
			$row['limit_role_name'] = join(',',$limit_role_name);
			$data['list'][$key] = $row;
		}
		
		$this->assign("data", $data);
		if ($runData == false){
			$data['content']= $this->fetch('list');
			unset($data['list']);
			return $this->success('','',$data);
		}
        return true;
    }

	/*------------------------------------------------------ */
	//-- 详细页调用
	/*------------------------------------------------------ */
    public function asInfo($data) {
		$this->assign("UsersRole", (new DividendRoleModel)->getRows());//分销身份
		$this->assign('d_level',config('config.dividend_level'));
		$data['limit_role'] = explode(',',$data['limit_role']);
		$data['buy_goods_list'] = [];
		if (empty($data['buy_goods_id']) == false){
			$where[] = ['goods_id','in',explode(',',$data['buy_goods_id'])];
			$list = (new \app\shop\model\GoodsModel)->where($where)->field('goods_id,goods_sn,goods_name')->select();
			if (empty($list) == false) $data['buy_goods_list'] = $list->toArray(); 
		}
		$data['repeat_goods_list'] = [];
		if (empty($data['repeat_goods_id']) == false){
			$where[] = ['goods_id','in',explode(',',$data['repeat_goods_id'])];
			$list = (new \app\shop\model\GoodsModel)->where($where)->field('goods_id,goods_sn,goods_name')->select();
			if (empty($list) == false) $data['repeat_goods_list'] = $list->toArray(); 
		}		
		$data['award_value'] = empty($data['award_value']) == false ? $data['award_value'] : '[]';		
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前处理
	/*------------------------------------------------------ */
    public function beforeAdd($data) {
			
		$count = $this->Model->where('award_name',$data['award_name'])->count('award_id');
		if ($count > 0) return $this->error('已存在相同的奖项名称，不允许重复添加.');	
		if (empty($data['limit_role']) == true){
			return $this->error('请设置可参与的分销身份.');	
		}
		if ($data['award_type'] == 3){
            $data['award_value'] = input('role_award_value');
        }
		if (empty($data['award_value']) == true){
			return $this->error('请设置奖项设置.');
		}
		
		if ($data['goods_limit'] > 1){
			$buy_goods_id = input('buy_goods_id');
			if (empty($buy_goods_id) == true){
				return $this->error('请指定需要购买的商品.');	
			}
			$data['buy_goods_id'] = join(',',$buy_goods_id);
		}
		if ($data['award_type'] == 3){//管理奖
			$nowNum = 0;
			foreach ($data['award_value'] as $award_value){
				if ($nowNum > $award_value['num']){
					return $this->error('.');	
				}
			}
		}
		
		
		$repeat_goods_id = input('repeat_goods_id');
		$data['repeat_goods_id'] = empty($repeat_goods_id) ? '' : join(',',$repeat_goods_id);
		$data['award_value'] = json_encode($data['award_value'],JSON_UNESCAPED_UNICODE);
		$data['limit_role'] = join(',',$data['limit_role']);
		$data['add_time'] = $data['update_time'] = time();		
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加后处理
	/*------------------------------------------------------ */
    public function afterAdd($data) {
		$this->_Log($data['award_id'],'添加分销奖项:'.$data['award_name']);
	}
	/*------------------------------------------------------ */
	//-- 修改前处理
	/*------------------------------------------------------ */
    public function beforeEdit($data){	
		$info = $this->Model->find($data['award_id'])->toArray();		
		$this->checkUpData($info,$data);
		$where[] = ' award_id <> '.$data['award_id'];
		$where[] = " award_name = '".$data['award_name']."' ";	
		$count = $this->Model->where(join(' AND ',$where))->count('award_id');
		if ($count > 0) return $this->error('已存在相同的奖项名称，不允许重复添加.');	
		if (empty($data['limit_role']) == true){
			return $this->error('请设置可参与的分销身份.');	
		}
		
		$data['update_time'] = time();
		$data['buy_goods_id'] = '';
		if ($data['goods_limit'] > 1){
			$buy_goods_id = input('buy_goods_id');
			if (empty($buy_goods_id) == true){
				return $this->error('请指定需要购买的商品.');	
			}
			$data['buy_goods_id'] = join(',',$buy_goods_id);
		}
		
		if ($data['award_type'] == 3){//管理奖
			$data['award_value'] = input('role_award_value');
			if (empty($data['award_value']) == false){
				$award_type = [];
				foreach ($data['award_value'] as $key=>$value){
					$award_type[$value['type']] = 1;
				}
				if (count($award_type) > 1){
					return $this->error('管理奖的所有奖励类型必须一致.');	
				}
			}
		}
		if (empty($data['award_value']) == true){
			return $this->error('请设置奖项设置.');	
		}
		$data['award_value'] = json_encode($data['award_value'],JSON_UNESCAPED_UNICODE);
		$repeat_goods_id = input('repeat_goods_id');
		$data['repeat_goods_id'] = empty($repeat_goods_id) ? '' : join(',',$repeat_goods_id);
	
		$data['limit_role'] = join(',',$data['limit_role']);
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['award_id'],'修改分销奖项:'.$data['award_name']);
	}
	 /*------------------------------------------------------ */
    //-- 删除
    /*------------------------------------------------------ */
    public function delete()
    {
        $award_id = input('award_id/d');
		if ($award_id < 1){
			return $this->error('传参错误.');	
		}
		$data = $this->Model->where('award_id',$award_id)->find();
		if (empty($data) == true){
			return $this->error('没有找到相关奖项.');	
		}
        $res = $this->Model->where('award_id',$award_id)->delete();
        if ($res < 1) return $this->error('删除失败，请重试.');
        $this->Model->cleanMemcache();
		$this->_Log($data['award_id'],'删除分销奖项:'.$data['award_name'],'',$data->toArray());
        return $this->success('删除成功.');
    }
}
