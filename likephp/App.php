<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/23
 * Time: 21:10
 */

namespace likephp;

class App
{
    private $config;

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
        $this->config = Config::getInstance()->get();
    }

    public function httpRun(\swoole_http_request $request, \swoole_http_response $response)
    {
        Request::getInstance()->parseHttp($request);
        Router::getInstance()->routeHttp();

    }
}