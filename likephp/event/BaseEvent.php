<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/14
 * Time: 22:37
 */

namespace likephp\event;


use likephp\Console;

class BaseEvent
{
    public function onStart()
    {
        Console::getInstance()->info('LikePHP服务开启成功');
    }
}