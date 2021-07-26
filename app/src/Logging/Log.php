<?php

namespace Logging;

use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler as StreamHandler;
use Monolog\Formatter\LineFormatter as LineFormatter;

class Log {    
    const DATETIME_FOMAT = "Y-m-d H:i:s";
    const DEFAULT_LEVEL  = Logger::DEBUG;
    const DEFAULT_FORMAT = '[%datetime%] [%channel%] [%level_name%] %message%'.PHP_EOL;
    const DEFAULT_STREAM = 'php://output';
    const DEFAULT_CHANNEL = 'marcus_redcap';

    /**
     * instance
     *
     * @var mixed
     */
    protected static $instance;
	
	/**
	 * getLogger
	 *
	 * @return Logger
	 */
	static public function getLogger() : Logger
	{
		if (!self::$instance) {
			self::createInstance();
        }

		return self::$instance;
	}

    protected static function createInstance()
    {
        $formatter = new LineFormatter(Log::DEFAULT_FORMAT, Log::DATETIME_FOMAT);
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);

        $handler = new StreamHandler(Log::DEFAULT_STREAM, Log::DEFAULT_LEVEL);
        $handler->setFormatter($formatter);

        $logger = new Logger(Log::DEFAULT_CHANNEL);
        $logger->pushHandler($handler);
       
		self::$instance = $logger;
    }

    public static function debug($message, array $context = []){
		self::getLogger()->debug($message, $context);
	}

	public static function info($message, array $context = []){
		self::getLogger()->info($message, $context);
	}

	public static function error($message, array $context = []){
		self::getLogger()->error($message, $context);
	}
}