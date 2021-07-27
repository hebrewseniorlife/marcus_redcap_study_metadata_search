<?php

namespace Logging;

use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler as StreamHandler;
use Monolog\Handler\BufferHandler as BufferHandler;
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
	 * @param  mixed $useBuffer
	 * @return Logger
	 */
	static public function getLogger(bool $useBuffer = true) : Logger
	{
		if (!self::$instance) {
			self::createInstance($useBuffer);
        }

		return self::$instance;
	}
    
    /**
     * createInstance
     *
     * @param  mixed $useBuffer
     * @return void
     */
    protected static function createInstance(bool $useBuffer = true)
    {
        $formatter = new LineFormatter(Log::DEFAULT_FORMAT, Log::DATETIME_FOMAT);
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);

        $stream = new StreamHandler(Log::DEFAULT_STREAM, Log::DEFAULT_LEVEL);
        $stream->setFormatter($formatter);

        $handler = $stream;
        if ($useBuffer === true)
        {
            $handler = new BufferHandler($stream, Logger::DEBUG);
        }
        
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