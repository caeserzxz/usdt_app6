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

class CleanTradingClose extends Command
{
    protected function configure()
    {
        $this->setName('CleanTradingClose')->setDescription('清除每天未打款的次数');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("清除每天未打款的次数 begin");
        $UsersModel = new UsersModel();
        $map['trading_close'] = 0;
        $UsersModel->where('is_ban',0)->where('user_id','gt',0)->update($map);
        $output->writeln("清除每天未打款的次数 end");

    }
}