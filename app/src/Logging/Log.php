<?php

namespace Logging;

use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler as StreamHandler;
use Monolog\Handler\BufferHandler as BufferHandler;
use Monolog\Formatter\LineFormatter as LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;

class Log {    
    const DATETIME_FOMAT = "Y-m-d H:i:s";
    const DEFAULT_LEVEL  = Logger::DEBUG;
    const DEFAULT_FORMAT = '[%datetime%] [%channel%] [%level_name%] %message%'.PHP_EOL;
    const DEFAULT_STREAM = 'php://memory';
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
	static public function getLogger($stream = Log::DEFAULT_STREAM) : Logger
	{
		if (!self::$instance) {
			self::createLogger($stream);
        }

		return self::$instance;
	}
    
    /**
     * createInstance
     *
     * @param  mixed $useBuffer
     * @return void
     */
    public static function createLogger($stream = Log::DEFAULT_STREAM)
    {
        $handler = Log::createStreamHandler($stream);
       
        $logger = new Logger(Log::DEFAULT_CHANNEL);
        $logger->pushHandler($handler);
       
		self::$instance = $logger;
    }
    
    
    /**
     * getStreamHandler
     *
     * @param  mixed $stream
     * @return StreamHandler
     */
    public static function getStreamHandler($stream = Log::DEFAULT_STREAM) : ? StreamHandler
    {
        $handler = null;

		if (!self::$instance)
        {
			return $handler;
        }

        $handlers = self::$instance->getHandlers();
        foreach($handlers as $handler)
        {
            if (get_class($handler) === StreamHandler::class)
            {
                $metadata = stream_get_meta_data($handler->getStream());
                if ($metadata['uri'] === $stream)
                {
                    return $handler;
                }
            }
        }

        return $handler;
    }
    
    /**
     * createStreamHandler
     *
     * @param  mixed $stream
     * @param  mixed $useBuffer
     * @param  mixed $level
     * @param  mixed $format
     * @return StreamHandler
     */
    public static function createStreamHandler($stream, $useBuffer = false, $level = Log::DEFAULT_LEVEL, $format = Log::DEFAULT_FORMAT) : StreamHandler{
        // Create a new line formatter based on defaults
        $formatter = new LineFormatter($format, Log::DATETIME_FOMAT);
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);

        // Create a stream handler and apply the formatter
        $stream = new StreamHandler($stream, $level);
        $stream->setFormatter($formatter);

        $handler = $stream;
        if ($useBuffer === true)
        {
            $handler = new BufferHandler($stream, Logger::DEBUG);
        }

        return $handler;
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

    public static function warning($message, array $context = []){
		self::getLogger()->warning($message, $context);
	}

    public static function notice($message, array $context = []){
		self::getLogger()->notice($message, $context);
	}

}