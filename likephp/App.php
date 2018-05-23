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
        //待完善...
        //待完善...
        //待完善...
        $uri = $request->server['request_uri'];
        $method = $request->server['request_method'];
        if (array_key_exists($uri, $this->config['router'][$method])) {
            //手动路由-待完善...待完善...待完善...
            $params = explode('@', $this->config['router'][$method][$uri]);
            $ctrl = ucfirst($params[0]);
            $action = $params[1];
        } else {
            //自动路由-待完善...待完善...待完善...
            $ctrl = 'Index';
            $action = 'index';
        }
        $app_name = APP_NAME;
        $class_name = "\\apps\\{$app_name}\\ctrl\\" . ucfirst($ctrl);
        try {
            $ref = new \ReflectionClass($class_name);
            if ($ref->hasMethod($action)) {
                ob_start();
                $class = new $class_name;
                $content = $class->$action();
                if (empty($content) || $content === false || $content === true) {
                    $content = ob_get_clean();
                }
                $response->end($content);
            } else {
                $response->status(404);
                $response->end("<h1>404, Not Found</h1>");
            }
        } catch (\ReflectionException $e) {
            $response->status(404);
            $response->end("<h1>404, Not Found</h1>");
        }
    }
}