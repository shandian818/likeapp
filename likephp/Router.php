<?php
/**
 * Created by PhpStorm.
 * User: Jiangxijun
 * Date: 2018/5/30
 * Time: 16:58
 */

namespace likephp;


class Router
{
    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    public function http()
    {

    }
}