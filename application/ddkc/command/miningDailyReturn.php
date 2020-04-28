<?php

namespace app\ddkc\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

use app\mainadmin\model\SettingsModel;
use app\ddkc\model\MiningOrderModel;
use app\ddkc\model\SellTradeModel;
use app\member\model\UsersModel;

use think\Db;

/**
 * 矿机&定存包每日计算
 *
 */

class miningDailyReturn extends Command
{
    protected function configure()
    {
        $this->setName('miningDailyReturn')->setDescription('矿机&定存包每日计算');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln(date('Y-m-d H:i:s',time()) . " 矿机&定存包每日计算 begin");
        $MiningOrderModel  = new MiningOrderModel();
        $MiningOrderModel->miningDailyReturn();
        $output->writeln(date('Y-m-d H:i:s',time()) . " 矿机&定存包每日计算 end");

    }
}