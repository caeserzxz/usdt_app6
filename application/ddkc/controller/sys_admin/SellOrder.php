<?php
namespace app\ddkc\controller\sys_admin;
use app\AdminController;
use think\Db;

use app\ddkc\model\SellTradeModel;
use app\ddkc\model\BuyTradeModel;
use app\ddkc\model\TradingStageModel;
use app\member\model\AccountLogModel;
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
        $this->search['sell_status'] = input("sell_status");
        $this->search['time_type'] = input("time_type");
        $this->assign("search", $this->search);

        $this->order_by = 'id';
        $this->sort_by = 'DESC';
        $time_type = input('time_type', '', 'trim');


        $reportrange = input('reportrange');

        if (empty($reportrange) == false) {
            $dtime = explode('-', $reportrange);
        }
        switch ($time_type) {
            case 'sell_start_time':
                $where[] = ' sell_start_time between ' . strtotime($dtime[0]) . ' AND ' . (strtotime($dtime[1]) + 86399);
                break;
            case 'matching_time':
                $where[] = ' matching_time between ' . strtotime($dtime[0]) . ' AND ' . (strtotime($dtime[1]) + 86399);
                break;
            case 'payment_time':
                $where[] = ' payment_time between ' . strtotime($dtime[0]) . ' AND ' . (strtotime($dtime[1]) + 86399);
                break;
            case 'complain_time':
                $where[] = ' complain_time between ' . strtotime($dtime[0]) . ' AND ' . (strtotime($dtime[1]) + 86399);
                break;
            case 'burning_time':
                $where[] = ' burning_time between ' . strtotime($dtime[0]) . ' AND ' . (strtotime($dtime[1]) + 86399);
                break;
            case 'sell_end_time':
                $where[] = ' sell_end_time between ' . strtotime($dtime[0]) . ' AND ' . (strtotime($dtime[1]) + 86399);
                break;
            default:
                break;
        }
        if ($this->search['sell_status']) {
            $where[] = ' sell_status ='.($this->search['sell_status']-1);
        }
        if ($this->search['keyword']) {
            $where[] = " id = '" . ($this->search['keyword']) . "' or sell_order_sn = '" . $this->search['keyword']."' ";
        }
        $viewObj = $this->Model->where(join(' AND ', $where))->order($this->order_by . ' ' . $this->sort_by);
        $data = $this->getPageList($this->Model,$viewObj);
        foreach ($data['list'] as $key => $value) {
            if($value>0){
                $buy_info = $BuyTradeModel->where('id',$value['buy_id'])->find();
                $data['list'][$key]['buy_user_id'] = $buy_info['buy_user_id'];
            }else{
                $data['list'][$key]['buy_user_id'] = '/';
            }

            $data['list'][$key]['add_date'] = date('m-d H:i',$value['add_time']);
        }
        $status = ['待出售','待付款','已付款','申诉中','交易成功','交易失败','已销毁'];
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
        $status = ['待出售','待付款','已付款','申诉中','交易成功','交易失败','已销毁'];
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
//        $data['matching_time'] = date('Y-m-d H:i:s',$data['matching_time']);
//        $data['payment_time'] = date('Y-m-d H:i:s',$data['payment_time']);
//        $data['complain_time'] = date('Y-m-d H:i:s',$data['complain_time']);
//        $data['cancellation_time'] = date('Y-m-d H:i:s',$data['cancellation_time']);
//        $data['sell_end_time'] = date('Y-m-d H:i:s',$data['sell_end_time']);

        return $data;
    }
    /*------------------------------------------------------ */
    //-- 交易审核
    /*------------------------------------------------------ */
    public function examine(){
        $BuyTradeModel = new BuyTradeModel();
        $accountModel = new AccountLogModel();

        $id = input('id');
        $type = input('type');
        $time = time();
        $sell_info = $this->Model->where('id',$id)->find();
        $buy_info = $BuyTradeModel->where('id',$sell_info['buy_id'])->find();

        Db::startTrans();
        if($type==1){
            #申诉通过
            #修改订单状态
            $sell_save['sell_status'] = 5;
            $sell_save['sell_end_time'] =$time;
            $res = $this->Model->where('id',$id)->update($sell_save);
            #对买家封号处理
            if($res){
                $ban['user_id'] = $buy_info['buy_user_id'];
                $ban['ban_time'] = $time;
                $ban['ban_day'] = 10000;
                $ban['ban_status'] = 0;
                $ban['ban_reason'] = "后台审核申诉结果";
                $ban['order_id'] = $id;

                $res1 = Db::name('dd_ban_record')->insert($ban);
                if($res1==false){
                    Db::rollback();
                    return $this->ajaxReturn(['code' => 0,'msg' => '添加封号记录失败','url' => '']);
                }
                #更新卖家叮叮
                $charge['balance_money']   = $sell_info['sell_number'];
                $charge['change_desc'] = '申诉成功';
                $charge['change_type'] = 13;
                $res1 =$accountModel->change($charge, $sell_info['sell_user_id'], false);
            }else{
                Db::rollback();
                return $this->ajaxReturn(['code' => 0,'msg' => '更新订单失败','url' => '']);
            }
        }else{
            #拒绝审核
            #修改订单状态
            $sell_save['sell_status'] = 4;
            $sell_save['sell_end_time'] =$time;
            $res = $this->Model->where('id',$id)->update($sell_save);
            #更新叮叮到买家账户
            if($res){
                #更新买家叮叮
                $charge['balance_money']   = $sell_info['sell_number'];
                $charge['change_desc'] = '交易成功';
                $charge['change_type'] = 13;
                $res1 =$accountModel->change($charge, $buy_info['buy_user_id'], false);
            }else{
                Db::rollback();
                return $this->ajaxReturn(['code' => 0,'msg' => '更新订单失败','url' => '']);
            }

        }
        Db::commit();
        return $this->ajaxReturn(['code' => 1,'msg' => '操作成功','url' => url('index')]);
    }

    /*------------------------------------------------------ */
    //-- 指定/禁止用户id
    /*------------------------------------------------------ */
    public function save_sell(){
        $SellTradeModel = new SellTradeModel();
        if ($this->request->isPost()) {
            $inArr = input('post.');

            $res =$SellTradeModel->where('id',$inArr['id'])->update($inArr);
            if($res){
                return $this->success('操作成功.',url('index'));
            }else{
                return $this->error('操作失败.');
            }
        }
        $id = input('id');
        $info = $SellTradeModel->where('id',$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }

    public function burning(){
        $SellTradeModel = new SellTradeModel();
        $id = input();

        #修改订单状态
        $sell_save['sell_status'] = 6;
        $sell_save['burning_time'] = time();
        $where['id'] =$id;
        $res = $this->Model->where($where)->update($sell_save);
        if($res){
            return $this->ajaxReturn(['code' => 1,'msg' => '操作成功','url' => url('index')]);
        }else{
            return $this->ajaxReturn(['code' => 0,'msg' => '更新订单失败','url' => '']);
        }

    }
}
