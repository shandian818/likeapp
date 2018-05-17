<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/14
 * Time: 22:28
 */

namespace likephp\event;


abstract class HttpEvent extends BaseEvent
{
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $response->end('asdwwwwww');
    }
}