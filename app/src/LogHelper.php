<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Logger as Logger;
use Monolog\Handler\BrowserConsoleHandler as BrowserConsoleHandler;
use StringCacheHandler as StringCacheHandler;

class LogHelper {
    static function createLogger(array $config = []) : Logger {   
        $logger = new Logger('study_metadata_search');

        $cacheHandle = new StringCacheHandler(Logger::DEBUG);
        $cacheHandle->setFormatter(new LineFormatter('[%datetime%] [%level_name%] %message%'));
        $logger->pushHandler($cacheHandle);

        return $logger;
    }
}