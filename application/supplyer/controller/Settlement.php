<?php
namespace app\supplyer\controller;

use app\supplyer\Controller;
use app\supplyer\model\SettleListModel;
/**
 * 结算管理
 */
class Settlement extends Controller
{
    protected function initialize()
    {
        parent::initialize();
        $this->Model = new SettleListModel();
    }
	/*------------------------------------------------------ */
	//-- 首页
	/*------------------------------------------------------ */
    public function index()
    {
        $this->getList(true);
        return $this->fetch();
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $where[] = ['supplyer_id','=',$this->supplyer_id];
        $this->sqlOrder = 'settle_date DESC';
        $this->data = $this->getPageList($this->Model, $where);
        $this->assign("status", $this->Model->status);
        $this->assign("data", $this->data);
        if ($runData == false){
            $this->data['content'] = $this->fetch('list')->getContent();
            unset($this->data['list']);
            return $this->success('','', $this->data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 认领结算单
    /*------------------------------------------------------ */
    public function check()
    {
        $settle_id = input('settle_id',0,'intval');
        if ($settle_id < 1){
            return $this->error('传参错误.');
        }
        $settle = $this->Model->find($settle_id);
        if (empty($settle)){
            return $this->error('没有找到相应结算单.');
        }
        if ($settle['supplyer_id'] != $this->supplyer_id){
            return $this->error('你没有权限操作此结算单');
        }
        $upDate['status'] = 1;
        $upDate['claim_time'] = time();
        $res = $this->Model->where('settle_id',$settle_id)->update($upDate);
        if ($res < 1){
            return $this->error('处理失败，请重试.');
        }
        return $this->success('认领成功.');
    }
}
