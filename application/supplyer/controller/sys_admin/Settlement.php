<?php
namespace app\supplyer\controller\sys_admin;

use app\AdminController;
use app\supplyer\model\SettleListModel;
use app\supplyer\model\SupplyerModel;
use app\shop\model\OrderModel;
use app\shop\model\AfterSaleModel;
/**
 * 结算管理
 */
class Settlement extends AdminController
{
    protected function initialize()
    {
        parent::initialize();
        $this->Model = new SettleListModel();
        $this->status = -1;
    }
	/*------------------------------------------------------ */
	//-- 结算列表
	/*------------------------------------------------------ */
    public function index()
    {
        $this->assign('title','结算列表');
        $this->selmonth = date('Y-m', strtotime("-1 months"));
        $this->assign("selmonth",  $this->selmonth);
        $this->sel_status = -1;
        $this->assign("sel_status",  $this->sel_status);
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 待认领
    /*------------------------------------------------------ */
    public function wait_check()
    {
        $this->assign('title','待认领');
        $this->selmonth = date('Y-m', strtotime("-1 months"));
        $this->assign("selmonth",  $this->selmonth);
        $this->sel_status = 0;
        $this->assign("sel_status",  $this->sel_status);
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 待打款
    /*------------------------------------------------------ */
    public function wait_pay()
    {
        $this->assign('title','待打款');
        $this->selmonth = date('Y-m', strtotime("-1 months"));
        $this->assign("selmonth",  $this->selmonth);
        $this->sel_status = 1;
        $this->assign("sel_status",  $this->sel_status);
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 已完成
    /*------------------------------------------------------ */
    public function complete()
    {
        $this->assign('title','已完成');
        $this->selmonth = date('Y-m', strtotime("-1 months"));
        $this->assign("selmonth",  $this->selmonth);
        $this->sel_status = 2;
        $this->assign("sel_status",  $this->sel_status);
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false) {
        $date = input('selmonth','','trim');
        if (empty($date)){
           $date = $this->selmonth;
        }
        if (empty($date)){
            exit;
        }
        $where[] = ['st.settle_date','=',$date];
        $sel_status = input('sel_status',$this->sel_status) * 1;
        if ($sel_status >= 0){
            $where[] = ['st.status','=',$sel_status];
        }

        $viewObj = $this->Model->alias('st')->join("supplyer s", 'st.supplyer_id=s.supplyer_id', 'left')->where($where)->field('st.*,s.supplyer_name')->order('st.settle_date DESC');

        $data = $this->getPageList($this->Model,$viewObj);

        $this->assign("status", $this->Model->status);
        $this->assign("data", $data);
        if ($runData == false){
            $this->data['content'] = $this->fetch('list');
            unset($data['list']);
            return $this->success('','', $this->data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 已完成
    /*------------------------------------------------------ */
    public function evalSettlement(){
        $date = input('selmonth','','trim');
        if (empty($date)){
            return $this->error('请选择月份.');
        }
        $startTime = strtotime($date);
        $endDate = date('Y-m-t 23:59:59', $startTime);
        $endTime = strtotime($endDate);
        $OrderModel = new OrderModel();
        $AfterSaleModel = new AfterSaleModel();
        $SupplyerModel = new SupplyerModel();
        $supplyerList =$SupplyerModel->select();//获取所有供应商
        $time = time();
        foreach ($supplyerList as $supplyer){

            $where = [];
            $where[] = ['supplyer_id','=',$supplyer['supplyer_id']];
            $where[] = ['settle_date','=',$date];
            $settle = $this->Model->where($where)->find();
            if (empty($settle) == false && $settle['status'] > 0){
                continue;
            }
            $inArr = [];
            $inArr['supplyer_id'] = $supplyer['supplyer_id'];
            $inArr['settle_date'] = $date;
            $inArr['add_time'] = $time;
            $where = [];
            $where[] = ['o.supplyer_id','=',$supplyer['supplyer_id']];
            $where[] = ['o.settlement_time','between',[$startTime,$endTime]];
            $where[] = ['o.shipping_status','=',2];
            $orderList = $OrderModel->alias('o')->where($where)->select();
            $inArr['sale_order_num'] = 0;
            foreach ($orderList as $order){
                $inArr['sale_order_num'] += 1;
                $inArr['sale_amount'] += $order['settle_price'];
            }
            $inArr['sale_goods_num'] = $OrderModel->alias('o')->join("shop_order_goods og", 'o.order_id=og.order_id', 'left')->where($where)->SUM('og.goods_number');
            $where = [];
            $where[] = ['supplyer_id','=',$supplyer['supplyer_id']];
            $where[] = ['status','>',1];
            $where[] = ['check_time','between',[$startTime,$endTime]];
            $afterSale = $AfterSaleModel->where($where)->field('goods_number,return_settle_money')->select();
            $inArr['after_sale_order_num'] = 0;
            $inArr['after_sale_amount'] = 0;
            $inArr['after_sale_goods_num'] = 0;
            foreach ($afterSale as $as){
                $inArr['after_sale_order_num'] += 1;
                $inArr['after_sale_goods_num'] += $as['goods_number'];
                $inArr['after_sale_amount'] += $as['return_settle_money'];
            }
            $inArr['settle_amount'] = $inArr['sale_amount'] - $inArr['after_sale_amount'];
            if (empty($settle) == true){
                $this->Model->create($inArr);
            }else{
                unset($inArr['add_time']);
                $this->Model->where('settle_id',$settle['settle_id'])->update($inArr);
            }
        }
        return $this->success('操作成功.');
    }
    /*------------------------------------------------------ */
    //-- 已打款结算单
    /*------------------------------------------------------ */
    public function checkPay()
    {
        $settle_id = input('settle_id',0,'intval');
        if ($settle_id < 1){
            return $this->error('传参错误.');
        }
        $settle = $this->Model->find($settle_id);
        if (empty($settle)){
            return $this->error('没有找到相应结算单.');
        }
        $upDate['status'] = 2;
        $upDate['payment_time'] = time();
        $res = $this->Model->where('settle_id',$settle_id)->update($upDate);
        if ($res < 1){
            return $this->error('处理失败，请重试.');
        }
        return $this->success('操作成功.');
    }
}
