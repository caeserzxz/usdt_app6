<?php
namespace app\ddkc\controller\sys_admin;
use app\AdminController;
use app\mainadmin\model\SettingsModel;
use app\ddkc\model\TradingStageModel;
/*------------------------------------------------------ */
//-- 设置
/*------------------------------------------------------ */
class TradingStage extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize(){
        parent::initialize();
        $this->Model = new TradingStageModel();
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
        $this->order_by = 'id';
        $this->sort_by = 'DESC';
        $data = $this->getPageList($this->Model);
        $this->assign("data", $data);
        if ($runData == false){
            $data['content']= $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('','',$data);
        }
        return true;
    }

    /*------------------------------------------------------ */
    //-- 添加前处理
    /*------------------------------------------------------ */
    public function beforeAdd($data) {

        return $data;
    }
    /*------------------------------------------------------ */
    //-- 添加后处理
    /*------------------------------------------------------ */
    public function afterAdd($data) {
        $this->_Log($data['miner_id'],'矿机:'.$data['level_name']);
    }

    /*------------------------------------------------------ */
    //-- 修改前处理
    /*------------------------------------------------------ */
    public function beforeEdit($data){

        $data['time'] = $data['time'].':00';
        $data['time'] = strtotime($data['time']);
        $daybegin=strtotime(date("Ymd",$data['time']));
        $dayend=$daybegin+86400;

        $count = $this->Model->where('time','between',[$daybegin,$dayend])->where('id','neq',$data['id'])->count();
        if ($count > 0) return $this->error('操作失败:当天已存在行情，不允许重复添加！');

//        $daybegin2=strtotime(date("Ymd"));
//        $dayend2=$daybegin2+86400;
//        if($data['time']<$dayend2) return $this->error('操作失败:只允许添加今天之后的行情');

        return $data;
    }
    /*------------------------------------------------------ */
    //-- 修改后处理
    /*------------------------------------------------------ */
    public function afterEdit($data) {
//        $this->_Log($data['miner_id'],'修改矿机:'.$data['miner_name']);
    }

    /*------------------------------------------------------ */
    //-- 删除等级
    /*------------------------------------------------------ */
    public function delete(){
        $miner_id = input('id',0,'intval');
        if ($miner_id < 1)  return $this->error('传参失败！');
        $res = $this->Model->where('id',$miner_id)->delete();
        if ($res < 1) return $this->error('未知错误，删除失败！');
        return $this->success('删除成功！',url('index'));
    }

}
