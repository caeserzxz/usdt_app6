<?php
/*------------------------------------------------------ */
//-- 计划任务
//-- Author: MEI 2020-3-16
/*------------------------------------------------------ */
namespace app\ddkc\controller;
use app\distribution\model\DividendRoleModel;
use think\Db;
use app\ApiController;
use app\ddkc\model\MiningOrderModel;
use think\cache\driver\Redis;
use app\member\model\AccountLogModel;
use app\member\model\AccountModel;
use app\mainadmin\model\SettingsModel;
use app\member\model\UsersModel;
use app\ddkc\model\BuyTradeModel;

class Task  extends ApiController{
    //开奖逻辑
    public function  PanicBuying(){
        $BuyTradeModel = new BuyTradeModel();
        $BuyTradeModel->PanicBuying();
    }
    //自动完成&自动取消
    public function AutoCompletion(){
        $BuyTradeModel = new BuyTradeModel();
        $BuyTradeModel->AutoCompletion();
    }
    //预约没有抢购的自动过期
    public function BeOverdue(){
        $BuyTradeModel = new BuyTradeModel();
        $BuyTradeModel->BeOverdue();
    }
    //每日重置开奖和过期情况 每天0点执行一次
    public function DailyReset(){
        $BuyTradeModel = new BuyTradeModel();
        $BuyTradeModel->DailyReset();
    }
}?>