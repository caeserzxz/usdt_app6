<?php
namespace app\distribution\controller\sys_admin;
use app\AdminController;
use app\distribution\model\DividendAwardModel;
use app\distribution\model\DividendRoleModel;


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
		
        $data = $this->getPageList($this->Model);			
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
		$data['buy_goods'] = [];
		if (empty($data['goods_ids']) == false){
			$where[] = ['goods_id','in',$data['goods_ids']];
			$list = (new \app\shop\model\GoodsModel)->where($where)->field('goods_id,goods_sn,goods_name')->select();
			if (empty($list) == false) $data['buy_goods'] = $list->toArray(); 
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
		
		if ($data['goods_limit'] == 2){
			$buy_goods = input('link_goods_id');
			if (empty($buy_goods) == true){
				return $this->error('请指定需要购买的商品.');	
			}
			$data['goods_ids'] = join(',',$buy_goods);
		}
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
		$where[] = " (award_name = '".$data['award_name']."' )";		
		$count = $this->Model->where(join(' AND ',$where))->count('award_id');
		if ($count > 0) return $this->error('已存在相同的奖项名称，不允许重复添加.');	
		$data['update_time'] = time();
		if ($data['goods_limit'] == 2){
			$buy_goods = input('link_goods_id');
			if (empty($buy_goods) == true){
				return $this->error('请指定需要购买的商品.');	
			}
			$data['goods_ids'] = join(',',$buy_goods);
		}
		$data['award_value'] = json_encode($data['award_value'],JSON_UNESCAPED_UNICODE);
		$data['limit_role'] = join(',',$data['limit_role']);
		return $data;		
	}
	/*------------------------------------------------------ */
	//-- 修改后处理
	/*------------------------------------------------------ */
    public function afterEdit($data) {
		$this->_Log($data['award_id'],'修改分销奖项:'.$data['award_name']);
	}
	
}
