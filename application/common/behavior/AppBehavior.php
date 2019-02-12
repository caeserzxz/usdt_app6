<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 19-1-10
 * Time: 16:52
 */

namespace app\common\behavior;

use app\common\exception\Error;

class AppBehavior {

    public static function init() {
        // 注册自定义异常处理
        Error::register();
    }
}