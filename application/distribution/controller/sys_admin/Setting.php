<?php

namespace app\distribution\controller\sys_admin;

use think\Db;

use app\AdminController;
use app\mainadmin\model\SettingsModel;
use app\distribution\model\DividendModel;
use app\distribution\model\EvalArrivalLogModel;
use app\member\model\UsersModel;

/**
 * 分销设置
 * Class Index
 * @package app\store\controller
 */
class Setting extends AdminController
{
    /*------------------------------------------------------ */
    //-- 优先执行
    /*------------------------------------------------------ */
    public function initialize()
    {
        parent::initialize();
        $this->Model = new SettingsModel();
    }
    /*------------------------------------------------------ */
    //-- 主页
    /*------------------------------------------------------ */
    public function index()
    {
        $settings = settings();
        $Dividend = json_decode($settings['DividendInfo'], true);
        $Dividend['status'] = $settings['DividendSatus'];
        $Dividend['share_by_role'] = $settings['DividendShareByRole'];
        $this->assign('Dividend', $Dividend);
        $this->assign('share_bg', $settings['share_bg']);
        $this->assign('shop_after_sale_limit', $settings['shop_after_sale_limit']);
        $this->assign('setting', $settings);
        return $this->fetch();
    }

    /*------------------------------------------------------ */
    //-- 保存配置
    /*------------------------------------------------------ */
    public function save()
    {
        $Dividend = input();
        $arr = input('post.setting');
        $arr['DividendSatus'] = $Dividend['status'];
        $arr['DividendShareByRole'] = $Dividend['share_by_role'] * 1;
        unset($Dividend['setting'], $Dividend['status'], $Dividend['DividendShareByRole'], $Dividend['share_bg']);
        $arr['DividendInfo'] = json_encode($Dividend);
        $arr['share_bg'] = input('share_bg', '', 'trim');
        $res = $this->Model->editSave($arr);
        if ($res == false) return $this->error();
        return $this->success('设置成功.');
    }
    /*------------------------------------------------------ */
    //-- 手动结算
    /*------------------------------------------------------ */
    public function evalArrival()
    {
        $inData['log_time'] = time();
        $inData['admin_id'] = AUID;
        $EvalArrivalLogModel = new EvalArrivalLogModel();
        Db::startTrans();//事务启用
        $res = $EvalArrivalLogModel->save($inData);
        if ($res < 1) {
            Db::rollback();//事务回滚
            return $this->error('执行失败-1，请重试.');
        }
        $res = (new DividendModel)->evalArrival(0, $EvalArrivalLogModel->log_id);
        if ($res == false) {
            Db::rollback();//事务回滚
            return $this->error('执行失败-2，请重试.');
        }
        Db::commit();//事务提交
        return $this->success('操作成功.');
    }

    /*------------------------------------------------------ */
    //-- 佣金相关自动执行
    /*------------------------------------------------------ */
    public function autoEval()
    {
        //执行定期分佣到帐
        $DividendInfo = settings('DividendInfo');
        $WeiXinMsgTplModel = new \app\weixin\model\WeiXinMsgTplModel();
        $WeiXinUsersModel = new \app\weixin\model\WeiXinUsersModel();
        if ($DividendInfo['settlement_day'] > 0) {
            $EvalArrivalLogModel = new \app\distribution\model\EvalArrivalLogModel();
            $log_time = $EvalArrivalLogModel->order('log_id DESC')->value('log_time');//获取最近操作的时间
            if (time() > $log_time + $DividendInfo['settlement_day'] * 86400) {
                $inData['log_time'] = time();
                Db::startTrans();//事务启用
                $res = $EvalArrivalLogModel->save($inData);
                if ($res >= 1) {
                    $DividendModel = new \app\distribution\model\DividendModel();
                    $log_ids = $DividendModel->evalArrival(0, $EvalArrivalLogModel->log_id);
                    if ($res != false) {
                        Db::commit();//事务提交
                    }
                    //发送佣金到帐号通知

                    foreach ($log_ids as $log_id) {
                        $log = $DividendModel->find($log_id);
                        //执行模板消息通知
                        $log['send_scene'] = $log['dividend_bean'] > 0 ? 'dividend_bean_arrival_msg' :'dividend_arrival_msg';//佣金到帐通知
                        $wxInfo = $WeiXinUsersModel->where('user_id', $log['dividend_uid'])->field('wx_openid','wx_nickname')->find();
                        $log['openid'] = $wxInfo['wx_openid'];
                        $log['send_nick_name'] = $wxInfo['wx_nickname'];
                        $WeiXinMsgTplModel->send($log);//模板消息通知
                    }
                }
                Db::rollback();//事务回滚
            }
        }
        //end
        if ($DividendInfo['repeat_buy_msg_day'] > 0){
            $where = [];
            $where[] = ['u.last_buy_time','>',0];
            $repeat_buy_msg_time = time() - ($DividendInfo['repeat_buy_msg_day'] * 86400);
            $where[] = ['u.last_buy_time','>',$repeat_buy_msg_time];
            $where[] = ['u.send_repeat_buy_msg_time','>',$repeat_buy_msg_time];
            $UsersModel = new UsersModel();
            $rows = $UsersModel->where($where)->alias('u')->join('weixin_users wxu', 'u.user_id = wxu.user_id', 'left')->field('wxu.wx_openid,wxu.user_id,wxu.wx_nickname')->select();
            foreach ($rows as $row){
                if (empty($wx_openid) == false) continue;
                //执行模板消息通知
                $row['send_scene'] = 'repeat_buy_msg';//通知用户需要复购
                $row['openid'] = $row['wx_openid'];
                $row['send_nick_name'] = $row['wx_nickname'];
                $WeiXinMsgTplModel->send($row);//模板消息通知
                $UsersModel->upInfo($row['user_id'],['send_repeat_buy_msg_time'=>time()]);
            }
        }
    }

}
