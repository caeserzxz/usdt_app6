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
 * 预约没有抢购的自动过期
 *
 */

class BeOverdue extends Command
{
    protected function configure()
    {
        $this->setName('BeOverdue')->setDescription('预约没有抢购的自动过期');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("预约没有抢购的自动过期 begin");
        $BuyTradeModel  = new BuyTradeModel();
        $BuyTradeModel->BeOverdue();
        $output->writeln("预约没有抢购的自动过期 end");

    }
}