<?php
/**
 * Created by PhpStorm.
 * User: Jiangxijun
 * Date: 2018/5/30
 * Time: 9:52
 */

namespace likephp;


class Request
{
    private static $instance;

    private $swoole_http_request;

    public $uri;
    public $pathinfo;
    public $host;
    public $port;
    public $method;
    public $clientIp;

    private $get;
    private $post;
    private $cookie;
    private $rawContent;

    public static function getInstance(\swoole_http_request $request)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static($request);
        }
        return self::$instance;
    }

    private function __construct(\swoole_http_request $request)
    {
        $this->swoole_http_request = $request;
        $this->get = isset($request->get) ? $request->get : [];
        $this->post = isset($request->post) ? $request->post : [];
        $this->cookie = isset($request->cookie) ? $request->cookie : [];
        $this->rawContent = $request->rawContent();
        $this->host = $request->header['host'];
        $this->uri = $request->server['request_uri'];
        $this->pathinfo = $request->server['path_info'];
        $this->method = $request->server['request_method'];
        $this->clientIp = $request->server['remote_addr'];
        $this->port = $request->server['server_port'];
    }

    public function get($key = null, $default_value = null)
    {
        if (!empty($key)) {
            $value = isset($this->get[$key]) ? $this->get[$key] : $default_value;
        } else {
            $value = $this->get;
        }
        return $value;
    }

    public function post($key = null, $default_value = null)
    {
        if (!empty($key)) {
            $value = isset($this->post[$key]) ? $this->post[$key] : $default_value;
        } else {
            $value = $this->get;
        }
        return $value;
    }

    public function cookie($key = null, $default_value = null)
    {
        if (!empty($key)) {
            $value = isset($this->cookie[$key]) ? $this->cookie[$key] : $default_value;
        } else {
            $value = $this->get;
        }
        return $value;
    }
}