<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/17
 * Time: 21:07
 */

return [
    'server' => [
        'type' => 'http',
        'host' => '0.0.0.0',
        'port' => 9501,
        'mode' => SWOOLE_PROCESS,
        'sock_type' => SWOOLE_SOCK_TCP,
        'setting' => [
            'upload_tmp_dir' => WEB_PATH . 'upload/',
            'enable_static_handler' => true,
            'document_root' => WEB_PATH,
        ]
    ],
    'router' => [
        'GET' => [
            '/' => 'Index@index',
            '/login' => 'Public@login',
        ],
        'POST' => [
            '/doLogin' => 'Public@doLogin',
        ],
        'PUT' => [
            '/' => 'Index@index',
        ],
        'DELETE' => [
            '/' => 'Index@index',
        ]
    ],
    'app' => [
        'default_ctrl' => 'index',
        'default_act' => 'index',
    ]
];