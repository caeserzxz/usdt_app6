<?php

namespace app\supplyer\controller;
use think\Db;
use app\supplyer\Controller;

use app\shop\model\AfterSaleModel;
use app\shop\model\OrderGoodsModel;
use app\shop\model\OrderModel;
use app\member\model\AccountLogModel;
/**
 * 售后相关
 * Class Index

 */
class AfterSale extends Controller
{
    public $listType = '';
    //*------------------------------------------------------ */
    //-- 初始化
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new AfterSaleModel();
    }
    //*------------------------------------------------------ */
    //-- 待退货
    /*------------------------------------------------------ */
    public function wait_shipping()
    {
        $this->assign('title','待退货');
        $this->listType = 'wait_shipping';
        $this->getList(true);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 待收货
    /*------------------------------------------------------ */
    public function wait_sign()
    {
        $this->assign('title','待收货');
        $this->listType = 'wait_sign';
        $this->getList(true);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 已完成
    /*------------------------------------------------------ */
    public function complete()
    {
        $this->assign('title','已完成');
        $this->listType = 'complete';
        $this->getList(true);
        return $this->fetch('index');
    }
    //*------------------------------------------------------ */
    //-- 待审核
    /*------------------------------------------------------ */
    public function fail()
    {
        $this->assign('title','审核失败');
        $this->listType = 'fail';
        $this->getList(true);
        return $this->fetch('index');
    }
    /*------------------------------------------------------ */
    //-- 获取列表
    //-- $runData boolean 是否返回模板
    /*------------------------------------------------------ */
    public function getList($runData = false)
    {
        if ($runData == true){
            $reportrange = input('reportrange', '', 'trim');
            if (empty($reportrange) == false) {
                $reportrange = str_replace('_', '/', $reportrange);
                $dtime = explode('-', $reportrange);
                $this->assign("start_date", $dtime[0]);
                $this->assign("end_date", $dtime[1]);
            } else {
                $this->assign("start_date", date('Y/m/01', strtotime("-1 months")));
                $this->assign("end_date", date('Y/m/d'));
            }
        }
        $where[] = ['supplyer_id','=',$this->supplyer_id];
        if (empty($this->listType)){
            $this->listType = input('listType','','trim');
        }
        $reportrange = input('reportrange', '', 'trim');
        if (empty($reportrange) == false) {
            $reportrange = str_replace('_', '/', $reportrange);
            $dtime = explode('-', $reportrange);
            $where[] = ['add_time', 'between', [strtotime($dtime[0]), strtotime($dtime[1]) + 86399]];
        } else {
            $where[] = ['add_time', 'between', [strtotime("-1 months"), time()]];
        }
        $keyword = input('keyword', '', 'trim');
        switch ($keyword) {
            case 'as_sn':
                $where[] = ['as_sn', '=', $keyword ];
                break;
            case 'goods_sn':
                $where[] = ['goods_sn', '=', $keyword];
                break;
            case 'shipping_no':
                $where[] = ['shipping_no', '=', $keyword];
                break;
            case 'order_sn':
                $where[] = ['order_sn', '=',$keyword];
                break;
            case 'user_id':
                $where[] = ['user_id', '=', $keyword];
                break;
            default:
                break;
        }
        switch ($this->listType) {
            case 'wait_check':
                $where[] = ['status', '=', 0];
                break;
            case 'wait_shipping':
                $where[] = ['status', '=', 2];
                break;
            case 'wait_sign':
                $where[] = ['status', '=', 3];
                break;
            case 'complete':
                $where[] = ['status', '=', 9];
                break;
            case 'fail':
                $where[] = ['status', '=', 1];
                break;
            default:
                break;
        }
        $data = $this->getPageList($this->Model, $where);
        $OrderGoodsModel = new OrderGoodsModel();
        foreach ($data['list'] as $key=>$row){
            $row['goods'] = $OrderGoodsModel->find($row['rec_id'])->toArray();
            $data['list'][$key] = $row;
        }
        $this->assign("data", $data);
        $this->assign("listType", $this->listType);
        $this->assign("as_type", $this->Model->type);
        $this->assign("as_status", $this->Model->status);
        if ($runData == false) {
            $data['content'] = $this->fetch('list')->getContent();
            unset($data['list']);
            return $this->success('', '', $data);
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 详细页
    /*------------------------------------------------------ */
    public function asInfo($row)
    {
        $this->assign("as_status", $this->Model->status);
        $this->assign("as_type", $this->Model->type);
        $orderInfo = (new OrderModel)->info($row['order_id']);
        $this->assign('orderInfo',$orderInfo);
        $goods = (new OrderGoodsModel)->find($row['rec_id']);
        $this->assign('goods',$goods);
        $row['imgs'] = explode(',',$row['imgs']);
        return $row;
    }

    /*------------------------------------------------------ */
    //-- 售后收到退货商品确认
    /*------------------------------------------------------ */
    public function sign()
    {
        $as_id = input('as_id', 0, 'intval');
        $asInfo = $this->Model->find($as_id);
        if ($asInfo['status'] != 3){
            return $this->error('当前状态不允许此操作.');
        }
        if ($this->supplyer_id != $asInfo['supplyer_id']){
            return $this->error('无权操作.');
        }
        Db::startTrans();//启动事务
        $upData['status'] = 9;
        $upData['update_time'] = time();
        $res = $this->Model->where('as_id',$as_id)->update($upData);
        if ($res < 1){
            Db::rollback();// 回滚事务
            return $this->error('处理失败，请重试.');
        }
        $OrderModel = new OrderModel();
        $orderInfo = $OrderModel->info($asInfo['order_id']);
        if ($orderInfo['money_paid'] > 0) {
            if ($orderInfo['pay_code'] == 'balance') {
                $inData['balance_money'] = $asInfo['return_money'];
                $inData['change_type'] = 9;
                $inData['by_id'] = $asInfo['as_id'];
                $inData['change_desc'] = '售后退款到余额:' . $asInfo['return_money'];
                $res = (new AccountLogModel)->change($inData, $asInfo['user_id']);
                if ($res != true) {
                    Db::rollback();//回滚
                    return $this->error('退款到余额失败，请重试.');
                }
            } else {//在线退款
                $code = str_replace('/', '\\', "/payment/" . $orderInfo['pay_code'] . "/" . $orderInfo['pay_code']);
                $payment = new $code();
                $orderInfo['refund_amount'] = $asInfo['return_money'];
                $res = $payment->refund($orderInfo);
                if ($res !== true) {
                    Db::rollback();//回滚
                    return '请求退款接口失败：' . $res;
                }
            }
        }
        Db::commit();// 提交事务
        $log = '确定收到退，并打款给用户';
        $this->Model->_log($as_id, $log,$this->Model->status[9],'supplyer',$this->supplyer_id);
        return $this->success('处理成功.');
    }

}
