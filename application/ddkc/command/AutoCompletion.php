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
 * 自动完成&自动取消
 *
 */

class AutoCompletion extends Command
{
    protected function configure()
    {
        $this->setName('AutoCompletion')->setDescription('自动完成&自动取消');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("自动完成&自动取消 begin");
        $BuyTradeModel  = new BuyTradeModel();
        #自动完成
        $BuyTradeModel->AutoCompletion();
        #自动取消
        $BuyTradeModel->AutomaticCancellation();
        $output->writeln("自动完成&自动取消 end");

    }
}