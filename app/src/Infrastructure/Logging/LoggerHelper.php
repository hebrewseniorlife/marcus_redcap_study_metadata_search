<?php

namespace Infrastructure\Logging;

use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Infrastructure\Logging\LoggingConfig;
use Infrastructure\Logging\LoggingHandlerConfig;

class LoggerHelper
{
        /**
     * getStreamContents
     *
     * @return string
     */
    public static function getStreamContents(LoggerInterface $logger, $stream = LoggingHandlerConfig::DEFAULT_STREAM): string
    {
        $handler = LoggerHelper::getStreamHandler($logger, $stream);

        $contents = "";
        if ($handler != null) {
            $contents = stream_get_contents($handler->getStream(), -1, 0);
        }

        return $contents;
    }

    /**
     * getStreamHandler
     *
     * @param  LoggerInterface $logger
     * @param  string $stream
     * @return StreamHandler|null
     */
    public static function getStreamHandler(LoggerInterface $logger, string $stream = LoggingHandlerConfig::DEFAULT_STREAM) : ? StreamHandler
    {
        $handlers = $logger->getHandlers();
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
    

}