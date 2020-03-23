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
 * 抢购开奖
 *
 */

class PanicBuying extends Command
{
    protected function configure()
    {
        $this->setName('PanicBuying')->setDescription('开奖逻辑');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("开奖 begin");
        $BuyTradeModel  = new BuyTradeModel();
        $BuyTradeModel->PanicBuying_new();
        $output->writeln("开奖 end");

    }
}