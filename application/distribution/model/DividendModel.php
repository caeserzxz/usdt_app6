<?php
/*------------------------------------------------------ */
//-- 提成处理
//-- Author: iqgmy
/*------------------------------------------------------ */
namespace app\distribution\model;
use app\BaseModel;
use app\member\model\AccountLogModel;
use app\shop\model\OrderModel;
use app\weixin\model\WeiXinMsgTplModel;
use app\weixin\model\WeiXinUsersModel;
use app\member\model\UsersModel;
class DividendModel extends BaseModel
{
    protected $table = 'distribution_dividend_log';
    public $pk = 'log_id';

     /*------------------------------------------------------ */
    //-- 计算提成并记录或更新f
    /*------------------------------------------------------ */
    public function _eval(&$orderInfo, $type = '',$status=0){
        $fun = str_replace('/', '\\', '/distribution/'.config('config.dividend_type').'/Dividend');
        $Model = new $fun($this);
        $res = $Model->_eval($orderInfo, $type,$status);
        return $res;
    }
    /*------------------------------------------------------ */
    //-- 发送模板消息通知
    /*------------------------------------------------------ */
    public function sendMsg($type,$order_id,$order_operating=''){
        $rows = $this->where('order_id', $order_id)->select()->toArray();
        if (empty($rows)) return false;
        $WeiXinUsersModel = new WeiXinUsersModel();
        $WeiXinMsgTplModel = new WeiXinMsgTplModel();
        $buy_nick_name = (new UsersModel)->where('user_id', $rows[0]['buy_uid'])->value('nick_name');//获取购买会员昵称
        foreach ($rows as $row) {
            $row['buy_user_id'] = $row['buy_uid'];
            $sendData['order_sn'] = $row['order_sn'];
            $sendData['order_amount'] = $row['order_amount'];
            if ($type == 'pay') {
                $row['send_scene'] = 'dividend_add_msg';//佣金产生通知
            } elseif ($type == 'cancel') {
                $row['send_scene'] = 'dividend_cancel_msg';
            }elseif($type == 'sign'){
                $row['send_scene'] = 'dividend_arrival_msg';
            }
            $row['buy_nick_name'] = $buy_nick_name;
            $row['order_operating'] = $order_operating;
            $wxInfo = $WeiXinUsersModel->where('user_id', $row['dividend_uid'])->field('wx_openid,wx_nickname')->find();
            $row['openid'] = $wxInfo['wx_openid'];
            $row['send_nick_name'] = $wxInfo['wx_nickname'];
            $WeiXinMsgTplModel->send($row);//模板消息通知
        }
        return true;
    }
    /*------------------------------------------------------ */
    //-- 执行分佣到帐
    //-- order_id int 订单ID
    /*------------------------------------------------------ */
    public function evalArrival($order_id = 0, $type = '',$limit_id=0)
    {
        $time = time();
        $OrderModel = new OrderModel();
        $shop_after_sale_limit = settings('shop_after_sale_limit');//售后时间
        if ($order_id > 0) {
            $where[] = ['order_id', '=', $order_id];
            $where[] = ['status', '=', $OrderModel->config['DD_SIGN']];
            if ($type == 'role_order'){
                $where[] = ['order_type', '=', $type];
            }else{
                $where[] = ['order_type', 'in', ['order','up_back']];
            }
            $rows = $this->where($where)->select()->toArray();
        } else {
            if ($type == 'role_order'){
                $where[] = ['status', '=', $OrderModel->config['DD_SIGN']];
                $where[] = ['order_type', '=', $type];
                $rows = $this->where($where)->select()->toArray();
            }else{
                $where[] = ['order_type', '=', $type];
                if ($shop_after_sale_limit > 0 ){
                    $where[] = ['d.status', '=', $OrderModel->config['DD_SIGN']];
                    $limit_time = $shop_after_sale_limit * 86400;
                    $where[] = ['d.update_time', '<', $time - $limit_time];
                    $rows = $this->alias('d')->join('shop_order o','d.order_id = o.order_id')->field('d.*,o.is_after_sale')->where($where)->select()->toArray();
                }else{
                    $where[] = ['status', '=', $OrderModel->config['DD_SIGN']];
                    $rows = $this->where($where)->select()->toArray();
                }
            }
        }

        if (empty($rows)) return true;//没有找到相关佣金记录

        $AccountLogModel = new AccountLogModel();
        $log_id = [];
        foreach ($rows as $row) {
            if ($shop_after_sale_limit > 0 ){
                if (isset($row['is_after_sale']) && $row['is_after_sale'] == 1){
                    continue;//有售后，暂不能分佣
                }
            }

            if ($row['order_type'] == 'role_order'){
                $changedata['change_desc'] = '身份单佣金到帐';
                $changedata['change_type'] = 10;
            }else{
                $changedata['change_desc'] = '订单佣金到帐';
                $changedata['change_type'] = 4;
            }

            $changedata['by_id'] = $row['order_id'];
            $changedata['balance_money'] = $row['dividend_amount'];
            $changedata['bean_value'] = $row['dividend_bean'];
            $changedata['total_dividend'] = ($row['dividend_amount'] + $row['dividend_bean']);
            $res = $AccountLogModel->change($changedata, $row['dividend_uid'], false);
            if ($res !== true) {
                return false;
            }
            $upDate['status'] = $OrderModel->config['DD_DIVVIDEND'];
            $upDate['limit_id'] = $limit_id;
            $upDate['update_time'] = $time;
            $res = $this->where('log_id', $row['log_id'])->update($upDate);
            if ($res < 1) {
                return false;
            }
            $log_id[] = $row['log_id'];
        }
        if ($order_id > 0){
            $this->sendMsg('sign',$order_id);
        }
        return $log_id;
    }
    /*------------------------------------------------------ */
    //-- 退回分佣到帐,只有普通订单可操作
    //-- order_id int 订单ID
    /*------------------------------------------------------ */
    public function returnArrival($order_id = 0, $type = '')
    {
        $time = time();
        $OrderModel = new OrderModel();
        $where[] = ['order_id', '=', $order_id];
        $where[] = ['order_type','=','order'];
        $rows = $this->where($where)->select()->toArray();
        if (empty($rows)) return true;//没有找到相关佣金记录

        $AccountLogModel = new AccountLogModel();
        $WeiXinUsersModel = new WeiXinUsersModel();
        $WeiXinMsgTplModel = new WeiXinMsgTplModel();
        $buy_nick_name = (new UsersModel)->where('user_id', $rows[0]['buy_uid'])->value('nick_name');//获取购买会员昵称
        foreach ($rows as $row) {
            $upDate['status'] = $type == 'unsign' ? $OrderModel->config['DD_SHIPPED'] : $OrderModel->config['DD_RETURNED'];
            $upDate['limit_id'] = 0;
            $upDate['update_time'] = $time;
            $res = $this->where('log_id', $row['log_id'])->update($upDate);
            if ($res < 1) {
                return false;
            }
            if ($row['status'] == $OrderModel->config['DD_DIVVIDEND']) {
                $changedata['change_desc'] = $type == 'unsign' ? '订单撤销签收-退回佣金' : '订单退货-退回佣金';
                $changedata['change_type'] = 4;
                $changedata['by_id'] = $row['order_id'];
                $changedata['balance_money'] = $row['dividend_amount'] * -1;
                $changedata['bean_value'] = $row['dividend_bean'] * -1;
                $changedata['total_dividend'] = ($row['dividend_amount'] + $row['dividend_bean']) * -1;
                $res = $AccountLogModel->change($changedata, $row['dividend_uid'], false);
                if ($res !== true) {
                    return false;
                }
                //执行模板消息通知
                $wxInfo = $WeiXinUsersModel->where('user_id', $row['dividend_uid'])->field('wx_openid,wx_nickname')->find();
                if (empty($wxInfo) == false){
                    $row['buy_user_id'] = $row['buy_uid'];
                    $row['buy_nick_name'] = $buy_nick_name;
                    $row['order_operating'] = $type == 'unsign' ? '撤销签收' : '订单退货';
                    $row['send_scene'] = 'dividend_return_msg';//佣金退回通知
                    $row['openid'] = $wxInfo['wx_openid'];
                    $row['send_nick_name'] = $wxInfo['wx_nickname'];
                    $WeiXinMsgTplModel->send($row);//模板消息通知
                }
            }
        }
        return true;
    }
}

?>
