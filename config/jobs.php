<?php

return [
    'default' => env('QUEUE_CONNECTION', 'redis'),

    'queues' => [
        'default' => [
            'connection' => 'redis',
            'tries' => 3,
            'timeout' => 15,
        ],
    ],
];
