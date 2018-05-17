<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/17
 * Time: 22:01
 */

namespace likephp;

class Server
{
    private static $instance;

    private $server;

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

    private function initServer($config = [], $callback_name = '')
    {
        switch (strtolower($config['type'])) {
            case 'http':
                $this->server = new \swoole_http_server($config['host'], $config['port'], $config['mode'], $config['sock_type']);
                $event_list = [
                    'Start',
                    'ManagerStart',
                    'Request'
                ];
                break;
            case 'ws':
                $this->server = new \swoole_websocket_server($config['host'], $config['port'], $config['mode'], $config['sock_type']);
                $event_list = [
                    'Request',
                    'Open',
                    'Message',
                    'Close',
                    'HandShake'
                ];
                break;
            case 'tcp':
                $this->server = new \swoole_server($config['host'], $config['port'], $config['mode'], $config['sock_type']);
                $event_list = [
                    'Start',
                    'ManagerStart',
                    'ManagerStop',
                    'PipeMessage',
                    'Task',
                    'Packet',
                    'Finish',
                    'Receive',
                    'Connect',
                    'Close',
                    'Timer',
                    'WorkerStart',
                    'WorkerStop',
                    'Shutdown',
                    'WorkerError'
                ];
                break;
            default:
                Console::getInstance()->error($config['type'] . '不支持');
                die();
        }
        if (isset($config['setting']) && !empty($config['setting'])) {
            $this->server->set($config['setting']);
        }
        if (!class_exists($callback_name)) {
            Console::getInstance()->error('服务回调不存在' . $callback_name);
            die();
        }
        foreach ($event_list as $event) {
            if (method_exists($callback_name, 'on' . $event)) {
                $this->server->on($event, [$callback_name, 'on' . $event]);
            }
        }
    }

    public function run($config = [], $callback_name = '')
    {
        $this->initServer($config, $callback_name);
        $this->server->start();
    }
}