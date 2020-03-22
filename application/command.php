<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [
    'PanicBuying' => 'app\\ddkc\\command\\PanicBuying',//开奖逻辑 每分钟执行一次
    'AutoCompletion' => 'app\\ddkc\\command\\AutoCompletion',//自动完成&自动取消 每秒执行一次
    'BeOverdue' => 'app\\ddkc\\command\\BeOverdue',//预约没有抢购的自动过期 每秒执行一次
    'DailyReset' => 'app\\ddkc\\command\\DailyReset',//每日重置开奖和过期情况 每天0点执行一次
];