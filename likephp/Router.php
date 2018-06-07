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

    public function routeHttp()
    {
        $router = Config::getInstance()->get('router');
        $depr = Config::getInstance()->get('app.url_pathinfo_depr');
        $url_ext = Config::getInstance()->get('app.url_ext');
        $path_info = Request::getInstance()->pathinfo;
        $method = Request::getInstance()->method;
        $uri = preg_replace('/' . $url_ext . '$/i', '', trim($path_info, $depr));
        if ('/' != $depr) {
            $uri = str_replace($depr, '/', $uri);
        }
        //
        //
        //
        //如果设置了路由，并且路由规则匹配
        //      走配置的控制器和方法
        //没有设置路由或者未匹配到路规则
        //      走智能路由（按pathinfo解析控制器和方法）
    }
}