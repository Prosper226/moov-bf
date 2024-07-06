<?php

// config/logging.php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return [
    'default' => 'app',

    'channels' => [
        'app' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => dirname(__DIR__, 2).'/logs/app.log',
                'level' => Logger::DEBUG,
            ],
        ],
        'error' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => dirname(__DIR__, 2). '/logs/error.log',
                'level' => Logger::ERROR,
            ],
        ],
    ],
];





?>