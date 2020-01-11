<?php
//运费模板管理
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\ShippingTplModel;
use app\shop\model\ShippingModel;
use app\mainadmin\model\RegionModel;

class ShippingTpl extends AdminController
{
    public $supplyer_id = 0;//当前操作的供应商ID
	/*------------------------------------------------------ */
	//-- 优先执行
	/*------------------------------------------------------ */
	public function initialize(){
        parent::initialize();
        $this->Model = new ShippingTplModel();
        $this->assign('supplyer_id',$this->supplyer_id);
    }
   /*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
        $where[] = ['supplyer_id','=',$this->supplyer_id];
		$list = $this->Model->where($where)->order('is_default DESC')->select();
        $this->assign("list", $list);
        $this->assign("shippingList", ShippingModel::getToSTRows());
		return $this->fetch();
    }
	/*------------------------------------------------------ */
	//-- 选择配送区域
	/*------------------------------------------------------ */
	public function selRegion(){	
		$d_region = [
				['name'=>'华东','ids'=>[310000,320000,330000,340000,350000,360000,370000]],
				['name'=>'华北','ids'=>[150000,140000,130000,120000,110000]],
				['name'=>'华中','ids'=>[410000,420000,430000]],
				['name'=>'华南','ids'=>[440000,450000,460000]],
				['name'=>'东北','ids'=>[210000,220000,230000]],
				['name'=>'西北','ids'=>[610000,620000,630000,640000,650000]],
				['name'=>'西南','ids'=>[500000,510000,520000,530000,540000]],
				['name'=>'港澳台','ids'=>[710000,810000,820000,900000]],
		];	
		$_rows = array();
		$RegionModel = new RegionModel();
		foreach ($d_region as $key=>$region){
			$rows = $RegionModel->where([['id','in',$region['ids']]])->field('id,name,pid,short_name')->select();
			$d_region[$key]['plist'] = $rows;
			$rows = $RegionModel->where([['pid','in',$region['ids']]])->field('id,name,pid,short_name')->select();
			foreach ($rows as $row){
				$d_region[$key]['clist'][$row['pid']][] = $row;
				
			}
		}
		unset($rows,$row);
		return $d_region;
	}
 	/*------------------------------------------------------ */
	//-- 信息页调用
	//-- $data array 自动读取对应的数据
	/*------------------------------------------------------ */
	public function asInfo($data){
	    if ($this->supplyer_id > 0 && data['supplyer_id'] > 0 && $data['supplyer_id'] != $this->supplyer_id){
            return $this->error('操作无效.');
        }
		$this->assign("d_region", $this->selRegion());
		$data['sf_info'] =  ($data['sf_id']>0) ? json_decode($data['sf_info'],true) : [];	
		$this->assign("shippingList", ShippingModel::getToSTRows());
		return $data;
	}
	/*------------------------------------------------------ */
	//-- 添加前调用
	/*------------------------------------------------------ */
	public function beforeAdd($row){
        if (empty($row['delivery'])) return $this->error('请选择运送方式！');
        if ($this->supplyer_id > 0){//供应商管理
            $where[] = ['supplyer_id','=',$this->supplyer_id];
            $count = $this->Model->where($where)->count('sf_id');
            if ($count>=3){
                return $this->error('只能设置三套运费模板.');
            }
        }
        $sf_info = array();
        foreach ($row['delivery'] as $val){
            $sf_info[$val] = $row[$val];
            unset($row[$val]);
        }
        $row['sf_info'] = json_encode($sf_info);
        unset($row['delivery']);
		$row['add_time'] = $row['update_time'] = time();
        $row['supplyer_id'] = $this->supplyer_id;
		return $row;
	}
	/*------------------------------------------------------ */
	//-- 添加后调用
	/*------------------------------------------------------ */
	public function afterAdd($row){
		$info = '添加运费模板：'.$row['sf_name'];
		if ($row['is_default'] == 1){
            $where[] = ['supplyer_id','=',$this->supplyer_id];
			$where[] = ['is_default','=',1];
			$where[] = ['sf_id','<>',$row['sf_id']];
			$this->Model->where($where)->update(['is_default'=>0]);
			$info .= '，当前模板设为默认模板.';
		}
		$this->_Log($row['sf_id'],$info);//记录日志
		
	}
	/*------------------------------------------------------ */
	//-- 修改前调用
	/*------------------------------------------------------ */
	public function beforeEdit($row){
		if (empty($row['delivery'])) return $this->error('请选择运送方式！');
		$this->oldData = $this->Model->find($row['sf_id'])->toArray();
		if ($this->oldData['supplyer_id'] != $this->supplyer_id){
            return $this->error('操作无效.');
        }
		if ($this->oldData['is_default'] == 1 && $row['is_default'] == 0){
			return $this->error('默认模板不能直接设为非默认，请修改其它模板为默认.');
		}

		$sf_info = array();
		foreach ($row['delivery'] as $val){
			$sf_info[$val] = $row[$val];
			unset($row[$val]);
		}
		$row['sf_info'] = json_encode($sf_info);
		unset($row['delivery']);
		$this->checkUpData($this->oldData,$row);
		$row['update_time'] = time();
		return $row;
	}
	/*------------------------------------------------------ */
	//-- 修改后调用
	/*------------------------------------------------------ */
	public function afterEdit($row){
		$info = '修改运费模板：'.$row['sf_name'];
		if ($this->oldData['is_default'] == 0 && $row['is_default'] == 1){
			$where[] = ['is_default','=',1];
			$where[] = ['sf_id','<>',$row['sf_id']];
			$this->Model->where($where)->update(['is_default'=>0]);
			$info .= '，当前模板设为默认模板.';
		}		
		$this->_Log($row['sf_id'],$info);//记录日志		
	}
	/*------------------------------------------------------ */
	//-- 删除
	/*------------------------------------------------------ */
	public function delete(){
        $sf_id = input('sf_id',0,'intval');
		if ($sf_id<1) return $this->error('传递参数失败！');
        $where[] = ['sf_id','=',$sf_id];
        if ($this->supplyer_id > 0) {//供应商管理
            $where[] = ['supplyer_id','=',$this->supplyer_id];
        }
		$res = $this->Model->where($where)->delete();
		if ($res < 1) return $this->error();
		$this->Model->cleanMemcache();
		$this->_Log($sf_id,'删除运费模板');//记录日志
		return $this->success('删除成功',url('index'));
	}
}
