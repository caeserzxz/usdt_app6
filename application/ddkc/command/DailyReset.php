<?php

namespace app\ddkc\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

use app\mainadmin\model\SettingsModel;
use app\ddkc\model\BuyTradeModel;
use app\ddkc\model\SellTradeModel;
use app\member\model\UsersModel;

use think\Db;

/**
 * 重置开奖和过期情况
 *
 */

class DailyReset extends Command
{
    protected function configure()
    {
        $this->setName('DailyReset')->setDescription('重置开奖和过期情况');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("重置开奖和过期情况 begin");
        $BuyTradeModel  = new BuyTradeModel();
        $BuyTradeModel->DailyReset();
        $output->writeln("重置开奖和过期情况 end");

    }
}