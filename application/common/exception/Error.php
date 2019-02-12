<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 19-1-10
 * Time: 16:45
 */

namespace app\common\exception;

class Error extends \think\Error {

    /**
     * 注册异常处理
     * @access public
     * @return void
     */
    public static function register() {
        // 忽略 notice 级异常
        error_reporting(E_ALL ^ E_NOTICE);
        set_error_handler([__CLASS__, 'appError'], (E_ALL | E_STRICT) ^ E_NOTICE);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }
}