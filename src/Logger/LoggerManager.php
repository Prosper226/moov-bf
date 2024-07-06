<?php

// src/Logger/LoggerManager.php

namespace lab\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerManager {
    private static $loggers = [];

    public static function getLogger($channel = 'app') {
        if (!isset(self::$loggers[$channel])) {
            $config = include('logging.php');
            $channelConfig = $config['channels'][$channel];
            $logger = new Logger($channel);
            $handler = new $channelConfig['handler'](
                $channelConfig['with']['stream'],
                $channelConfig['with']['level']
            );
            $logger->pushHandler($handler);
            self::$loggers[$channel] = $logger;
        }
        return self::$loggers[$channel];
    }

    public static function getErrorLogger() {
        return self::getLogger('error');
    }
}




?>