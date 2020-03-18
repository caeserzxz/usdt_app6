<?php
namespace app\ddkc\controller\sys_admin;
use app\AdminController;

use app\ddkc\model\SellTradeModel;
use app\ddkc\model\BuyTradeModel;
use app\ddkc\model\TradingStageModel;
/**
 * 矿机订单
 */
class SellOrder extends AdminController
{
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new SellTradeModel();
    }
    /*------------------------------------------------------ */
    //--首页
    /*------------------------------------------------------ */
    public function index()
    {
        $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
        $this->assign("end_date", date('Y/m/d'));
        $this->getList(true);
        //首页跳转时间
        $start_date = input('start_time', '0', 'trim');
        $end_date = input('end_time', '0', 'trim');
        if( $start_date || $end_date){

            $this->assign("start_date",str_replace('_', '/', $start_date));
            $this->assign("end_date",str_replace('_', '/', $end_date));
        }
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $BuyTradeModel = new BuyTradeModel();
        $this->search['keyword'] = input("keyword");
        $this->search['key_type'] = input("key_type");
        $this->search['pay_type'] = input("pay_type");
        $this->search['status'] = input("status");
        $this->search['order_type'] = input("order_type");


        $this->order_by = 'order_id';
        $this->sort_by = 'DESC';
        if (input('reportrange')) {
            $dtime = explode('-', input('reportrange'));
            $where[] = ['add_time','between',[strtotime($dtime[0]),strtotime($dtime[1])+86399]];
        }
        if ($this->search['pay_type']) {
            $where[] = ['pay_type','=',($this->search['pay_type'])];
        }
        if ($this->search['order_type']) {
            $where[] = ['pay_type','=',($this->search['order_type'])];
        }

        if ($this->search['status']) {
            $where[] = ['status','=',($this->search['status'])];
        }
        if ($this->search['keyword']) {
            if ($this->search['key_type'] == 1) {
                $where[] = ['user_id','=',($this->search['keyword'])];
            }elseif ($this->search['key_type'] == 2) {
                $where[] = ['miner_name','=',($this->search['keyword'])];
            }elseif ($this->search['key_type'] == 3) {
                $where[] = ['surplus_days','=',($this->search['keyword'])];
            }
        }
        $data = $this->getPageList($this->Model,$where);
        foreach ($data['list'] as $key => $value) {
            if($value>0){
                $buy_info = $BuyTradeModel->where('id',$value['buy_id'])->find();
                $data['list'][$key]['buy_user_id'] = $buy_info['buy_user_id'];
            }else{
                $data['list'][$key]['buy_user_id'] = '/';
            }

            $data['list'][$key]['add_date'] = date('m-d H:i',$value['add_time']);
        }
        $status = ['待出售','代付款','已付款','申诉中','交易成功','交易失败'];
        $order_type = ['其他','矿机','定存包'];

        $this->assign("status", $status);
        $this->assign("order_type", $order_type);

        $this->assign("data", $data);
        if ($runData == false){
            $data['content']= $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('','',$data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 订单信息
    /*------------------------------------------------------ */
    protected function asInfo($data) {
        $TradingStageModel = new TradingStageModel();
        $BuyTradeModel = new BuyTradeModel();
        $status = ['待出售','代付款','已付款','申诉中','交易成功','交易失败'];
        $data['status_str'] =  $status[$data['sell_status']];
        $buy_info = $BuyTradeModel->where('id',$data['buy_id'])->find();
        if(empty($buy_info['buy_user_id'])){
            $data['buy_user_id'] ='/';
        }else{
            $data['buy_user_id'] =$buy_info['buy_user_id'];
        }
        $stage_info = $TradingStageModel->where('id',$data['sell_stage_id'])->find();
        $data['stage_name'] = $stage_info["stage_name"];

        $data['status_name'] = $status[$data['status']];
        $data['matching_time'] = date('Y-m-d H:i:s',$data['matching_time']);
        $data['payment_time'] = date('Y-m-d H:i:s',$data['payment_time']);
        $data['complain_time'] = date('Y-m-d H:i:s',$data['complain_time']);
        $data['cancellation_time'] = date('Y-m-d H:i:s',$data['cancellation_time']);
        $data['sell_end_time'] = date('Y-m-d H:i:s',$data['sell_end_time']);

        return $data;
    }
}
