<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/17
 * Time: 21:07
 */

return [

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

];