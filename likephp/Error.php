<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/17
 * Time: 21:21
 */

namespace likephp;


class Error
{
    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    public function register()
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'error']);
        set_exception_handler([$this, 'exception']);
        register_shutdown_function([$this, 'shutdown']);
    }

    private function error($errno, $errstr, $errfile, $errline)
    {
        echo $errno . '||' . $errstr . '||' . $errfile . '||' . $errline;
    }

    private function exception($e)
    {
        echo $e->getMessage() . '||' . $e->getFile() . '||' . $e->getLine();
    }

    private function shutdown()
    {
        $error = error_get_last();
        if (!is_null($error) && in_array($error['type'], ['E_ERROR', 'E_CORE_ERROR', 'E_COMPILE_ERROR', 'E_RECOVERABLE_ERROR'])) {
            //发生致命错误
            echo('发生致命错误');
        }
    }
}