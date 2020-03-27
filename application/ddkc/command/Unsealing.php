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
 * 自动解封
 *
 */

class Unsealing extends Command
{
    protected function configure()
    {
        $this->setName('Unsealing')->setDescription('自动解封');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("自动解封 begin");
        $BuyTradeModel  = new BuyTradeModel();
        $BuyTradeModel->Unsealing();
        $output->writeln("自动解封 end");

    }
}