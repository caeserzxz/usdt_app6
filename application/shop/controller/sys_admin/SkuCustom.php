<?php
namespace app\shop\controller\sys_admin;
use app\AdminController;
use app\shop\model\SkuCustomModel;
use app\shop\model\SkuCategoryModel;
/**
 * 自定义的商品规格
 * Class Index
 * @package app\store\controller
 */
class SkuCustom extends AdminController
{	
	/*------------------------------------------------------ */
	//-- 优先自动执行
	/*------------------------------------------------------ */ 
	public function initialize(){
        parent::initialize();
		$this->Model = new SkuCustomModel();
	}
	/*------------------------------------------------------ */
	//-- 获取商品规格
	/*------------------------------------------------------ */
	public function skuByCategory(){
		$skuModelId = input('skuModelId',0,'intval');
		$skuList = $this->Model->getRows($skuModelId,$this->supplyer_id);
		return $this->ajaxReturn($skuList);	
	}
	/*------------------------------------------------------ */
	//-- 新建商品模型
	/*------------------------------------------------------ */
	public function addSkuCategory(){
	    if (empty($this->supplyer_id) == false){
            return $this->error('无权操作.');
        }
		if ($this->request->isPost()){
			$inarr['name'] = input('name','','trim');
			if (empty($inarr['name'])) return $this->error('请输入模型名称！');
			$SkuCategoryModel = new SkuCategoryModel();
			$count = $SkuCategoryModel->where($inarr)->count('id');
			if ($count > 0) return $this->error('已存在同名的模型！');
			$res = $SkuCategoryModel->save($inarr);
			if ($res < 1) return $this->error();
			$result['_fun'] = 'setSkuCategory';
			$result['code'] = 1;
			$result['msg'] = '添加成功.';
			$SkuCategoryModel->cleanMemcache();
			$SkuClassList = $SkuCategoryModel->getRows();
			$result['_opt'] = arrToSel($SkuClassList,$res);	
			return $this->ajaxReturn($result);
		}
		$this->assign('action',url('addSkuCategory'));
		return $this->fetch('sku_category')->getContent();
	}
	
	
	
	/*------------------------------------------------------ */
	//-- 增加规格
	/*------------------------------------------------------ */
    public function addSku(){	
		$skuModelId = input('skuModelId',0,'intval');
		$spe = input('spe','','trim');
		$speval = input('speval');
		if ($skuModelId < 0) return $this->error('商品模型ID传参错误！');
		if (empty($spe)) return $this->error('规格名称传参错误！');
		if (empty($speval)) return $this->error('规格值传参错误！');
		$supplyer_id = $this->supplyer_id * 1;
		$inarr['model_id'] = $skuModelId;
		$inarr['val'] = $spe;
        $inarr['supplyer_id'] = $supplyer_id;
		$this->Model->save($inarr);
		$key = $this->Model->id;
		if ($key < 0) return $this->error();
		$result['data']['id'] = $key;
		$result['data']['name'] = $spe;			
		$result['data']['speid'] = $key;
		$result['code'] = 1;
		$result['msg'] = '操作成功.';
		foreach ($speval as $val){
			$inarr['model_id'] = $skuModelId;
			$inarr['speid'] = $key;
			$inarr['val'] = $val;
            $inarr['supplyer_id'] = $supplyer_id;
            $keyb = $this->Model::create($inarr);
			$row['val'] = $val;
			$row['key'] = $keyb->id;
			$result['data']['spevalList'][] = $row;
		}
		$this->Model->cleanMemcache($skuModelId);
		return $this->ajaxReturn($result);
	}
	/*------------------------------------------------------ */
	//-- 增加规格选项
	/*------------------------------------------------------ */
    public function addSkuVal()
	{	
		$skuModelId = input('skuModelId',0,'intval');
		$speid = number_format(input('speid'),0,'','');
		$speval = input('speval');
		if ($skuModelId < 0) return $this->error('商品模型ID传参错误！');
		if ($speid < 0) return $this->error('规格ID传参错误！');
		if (empty($speval)) return $this->error('规格值传参错误！');
        $supplyer_id = $this->supplyer_id * 1;
		$result['data']['speid'] = $speid;		
		$result['code'] = 1;
		$result['msg'] = '操作成功.';
		foreach ($speval as $val){
			$inarr['model_id'] = $skuModelId;
			$inarr['speid'] = $speid;
			$inarr['val'] = $val;
            $inarr['supplyer_id'] = $supplyer_id;
			$res = $this->Model::create($inarr);
			$row['val'] = $val;
			$row['key'] = $res->id;
			$result['data']['spevalList'][] = $row;
		}
		$this->Model->cleanMemcache($skuModelId);
		return $this->ajaxReturn($result);
	}
	/*------------------------------------------------------ */
	//-- 删除定义规格
	/*------------------------------------------------------ */
    public function delSku(){
		$skuModelId = input('skuModelId',0,'intval');
		$speid = number_format(input('post.speid'),0,'','');
		if ($skuModelId < 0) return $this->error('商品模型ID传参错误！');
		if ($speid < 0) return $this->error('规格ID传参错误！');
        $supplyer_id = $this->supplyer_id * 1;
		$where[] = ['model_id','=',$skuModelId];
        $where[] = ['id','=',$speid];
        $where[] = ['supplyer_id','=',$supplyer_id];
		$res = $this->Model->where($where)->delete();
		if ($res < 1) return $this->error();
		unset($where);
        $where[] = ['speid','=',$speid];
        $where[] = ['supplyer_id','=',$supplyer_id];
		$this->Model->where($where)->delete();
		$this->Model->cleanMemcache($skuModelId);
		$result['data']['speid'] = $speid;		
		$result['code'] = 1;
		$result['msg'] = '操作成功.';
		return $this->ajaxReturn($result);
	}
	/*------------------------------------------------------ */
	//-- 删除定义规格值
	/*------------------------------------------------------ */
    public function delSkuVal(){
		$skuModelId = input('skuModelId',0,'intval');
		$speid = number_format(input('post.speid'),0,'','');
		$idarr = input('post.idarr');		
		if ($skuModelId < 0) return $this->error('商品模型ID传参错误！');
		if ($speid < 0) return $this->error('规格ID传参错误！');	
		if (empty($idarr)) return $this->error('删除规格值传参错误！');
        $supplyer_id = $this->supplyer_id * 1;
		foreach ($idarr as $val){
		    $where = [];
            $where[] = ['id','=',$val];
            $where[] = ['model_id','=',$skuModelId];
            $where[] = ['speid','=',$speid];
            $where[] = ['supplyer_id','=',$supplyer_id];
			$res = $this->Model->where($where)->delete();
		}
		$this->Model->cleanMemcache($skuModelId);
		$result['data']['spevalidList'] = $idarr;
		$result['data']['speid'] = $speid;		
		$result['code'] = 1;
		$result['msg'] = '删除成功.';
		return $this->ajaxReturn($result);
	}


}
