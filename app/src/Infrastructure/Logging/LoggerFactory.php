<?php

namespace Infrastructure\Logging;

use Psr\Log\LoggerInterface;
use Monolog\Logger as Logger;
use Monolog\Level as Level;
use Monolog\Handler\StreamHandler as StreamHandler;
use Monolog\Handler\BufferHandler as BufferHandler;
use Monolog\Handler\NullHandler as NullHandler;
use Monolog\Formatter\JsonFormatter as JsonFormatter;
use Monolog\Formatter\LineFormatter as LineFormatter;
use Infrastructure\Logging\LoggingConfig;
use Infrastructure\Logging\LoggingHandlerConfig;

final class LoggerFactory
{
    private LoggingConfig $config;

    /**
     * __construct
     */
    function __construct(LoggingConfig $config)
    {
        $this->config = $config;
    }

    /**
     * createInstance
     *
     * @param LogConfig $config
     * @param  mixed $useBuffer
     * @return void
     */
    public function createLogger(string $channel = LoggingConfig::DEFAULT_CHANNEL) : LoggerInterface
    {
        $logger = new Logger($channel);

        // Get the handlers for the requested channel and include undefined channel handlers
        $handlers = $this->config->getHandlersByChannel($channel, true);

        // If no handlers are defined, then add a null handler to discard all logs
        if (count($handlers) == 0)
        {
            $logger->pushHandler(new NullHandler());
        }

        // For each handler defined for the channel, create and add it to the logger
        foreach ($handlers as $handlerConfig)
        {
            $handler = $this->createStreamHandler($handlerConfig);
            $logger->pushHandler($handler);
        }

        return $logger;
    }
    

    /**
     * createStreamHandler
     *
     * @param  LogConfig $config
     * @param  mixed $useBuffer
     * @return StreamHandler
     */
    public function createStreamHandler(LoggingHandlerConfig $config) : StreamHandler{
        // Create a stream handler 
        $stream = new StreamHandler($config->stream, $config->level);

        // Create a new line formatter based on defaults
        $formatter = null;
        // Choose formatter based on stream type
        if (str_contains($config->stream, 'php://'))
        {
            $formatter = new LineFormatter($config->format, $config->dateTimeFormat);
            $formatter->ignoreEmptyContextAndExtra(true);
            $formatter->allowInlineLineBreaks(true);
        }
        else
        {
            // Use JSON formatter for file streams
            $formatter = new JsonFormatter();
        }
        
        // Apply the formatter        
        $stream->setFormatter($formatter);

        // Assign the handler
        $handler = $stream;

        return $handler;
    }
}
