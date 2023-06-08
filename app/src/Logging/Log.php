<?php

namespace Logging;

use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler as StreamHandler;
use Monolog\Handler\BufferHandler as BufferHandler;
use Monolog\Handler\NullHandler as NullHandler;
use Monolog\Formatter\LineFormatter as LineFormatter;

class Log {    
    const DATETIME_FOMAT = "Y-m-d H:i:s";
    const DEFAULT_LEVEL  = Logger::INFO;
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
	static public function getLogger($stream = Log::DEFAULT_STREAM, $level = Log::DEFAULT_LEVEL) : Logger
	{
        // If the class instance does not already have a loggger then create a new one using the stream and level provided.
		if (!self::$instance) {
			self::createLogger($stream, $level);
        }

		return self::$instance;
	}
    
    /**
     * createInstance
     *
     * @param  mixed $useBuffer
     * @return void
     */
    public static function createLogger($stream = Log::DEFAULT_STREAM, $level = Log::DEFAULT_LEVEL, $format = Log::DEFAULT_FORMAT, $useBuffer = false)
    {
        // If the stream is explicitly null then create a null handler
        if ($stream === null)
        {
            $handler = new NullHandler($level);
        }
        else
        {
            // Otherwise, create a stream handler based on the basic details (level, format, etc.)
            $handler = Log::createStreamHandler($stream, $level, $format, $useBuffer);
        }

        // Create a new Monolog and give it the handler from above
        $logger = new Logger(Log::DEFAULT_CHANNEL);
        $logger->pushHandler($handler);

        // Assign the Monolog to the instance of the class
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
     * getStreamContents
     *
     * @return string
     */
    public static function getStreamContents($stream = Log::DEFAULT_STREAM): string
    {
        $handler = \Logging\Log::getStreamHandler($stream);

        $contents = "";
        if ($handler != null) {
            $contents = stream_get_contents($handler->getStream(), -1, 0);
        }

        return $contents;
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
    public static function createStreamHandler($stream, $level = Log::DEFAULT_LEVEL, $format = Log::DEFAULT_FORMAT, $useBuffer = false) : StreamHandler{
        // Create a stream handler 
        $stream = new StreamHandler($stream, $level);

        // Create a new line formatter based on defaults
        $formatter = new LineFormatter($format, Log::DATETIME_FOMAT);
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);
        
        // Apply the formatter        
        $stream->setFormatter($formatter);

        // Assign the handler
        $handler = $stream;

        // But, if a buffer is required then default to a debugging handler (NOTE: BufferHandler is stream wrapper)
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