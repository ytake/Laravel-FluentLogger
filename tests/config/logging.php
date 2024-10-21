<?php

declare(strict_types=1);

return [
    'channels' => [
        'stack' => [
            'driver'   => 'stack',
            'channels' => ['fluent'],
        ],

        'single' => [
            'driver' => 'single',
            'path'   => __DIR__ . '/../tmp/laravel.log',
            'level'  => 'debug',
        ],
        'fluent' => [
            'driver' => 'fluent',
            'level'  => 'debug',
        ],
    ],
];
