<?php

namespace Infrastructure\Logging;

use Psr\Log\LoggerInterface;
use Monolog\Logger as Logger;
use Monolog\Level as Level;
use Monolog\Handler\StreamHandler as StreamHandler;
use Monolog\Handler\BufferHandler as BufferHandler;
use Monolog\Handler\NullHandler as NullHandler;
use Monolog\Formatter\LineFormatter as LineFormatter;
use Infrastructure\Logging\LoggingConfig;

final class LoggerFactory
{
    /**
     * __construct
     */
    function __construct()
    {
    }

    /**
     * createInstance
     *
     * @param LogConfig $config
     * @param  mixed $useBuffer
     * @return void
     */
    public function createLogger(LoggingConfig $config, $useBuffer = false) : LoggerInterface
    {
        // By default create a null handler
        $handler = new NullHandler($config->level);

        // But, if a stream is provided then create the appropriate stream handler
        if (strlen($config->stream ?? '') > 0)
        {
            // Create the appropriate stream handler based on the config provided
            $handler = $this->createStreamHandler($config, $useBuffer);
        }

        // Create a new Monolog and give it the handler from above
        $logger = new Logger($config->channel);
        $logger->pushHandler($handler);

        return $logger;
    }
    

    /**
     * createStreamHandler
     *
     * @param  LogConfig $config
     * @param  mixed $useBuffer
     * @return StreamHandler
     */
    public function createStreamHandler(LoggingConfig $config, $useBuffer = false) : StreamHandler{
        // Create a stream handler 
        $stream = new StreamHandler($config->stream, $config->level);

        // Create a new line formatter based on defaults
        $formatter = new LineFormatter($config->format, $config->dateTimeFormat);
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);
        
        // Apply the formatter        
        $stream->setFormatter($formatter);

        // Assign the handler
        $handler = $stream;

        // But, if a buffer is required then default to a debugging handler (NOTE: BufferHandler is stream wrapper)
        if ($useBuffer === true)
        {
            $handler = new BufferHandler($stream, Level::Debug);
        }

        return $handler;
    }
}
