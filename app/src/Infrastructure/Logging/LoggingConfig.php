<?php

namespace Infrastructure\Logging;

use Monolog\Level as Level;
use Infrastructure\Logging\LoggingHandlerConfig;

final class LoggingConfig
{ 
    const DEFAULT_CHANNEL = 'all';

    /**
     * @var string[] $channel The logging channel name
     */
    public array $channels;

    /**
     * @var LoggingHandlerConfig[] $handlers The logging handlers
     */
    public array $handlers;

    /**
     * The default logging channel name
     * 
     */
    function __construct(array $handlers = [], array $channels = [])
    {        
      $this->channels = $channels;
      if (empty($channels))
      {
          $channels = [self::DEFAULT_CHANNEL];
      }

      $this->handlers = $handlers;
    }


    /**
     * Retrieves logging handlers configured for a specific channel.
     *
     * @param string $channel The name of the logging channel to retrieve handlers for.
     * @param bool $includeUndefined Optional. Whether to include handlers with undefined configurations.
     *                               Defaults to true.
     *
     * @return array An array of logging handlers associated with the specified channel.
     */
    function getHandlersByChannel(string $channel, bool $includeUndefined = true): array
    {
        $result = [];
        foreach ($this->handlers as $handler)
        {
            if (in_array($channel, $handler->channels) || ($includeUndefined && empty($handler->channels)))
            {
                $result[] = $handler;
            }
        }
        return $result;
    }

    /**
     * Retrieves an array of logging handlers.
     *
     * This method returns a collection of handlers that are configured
     * for the logging system. These handlers are responsible for processing
     * and outputting log records to their respective destinations.
     *
     * @return array An array of configured logging handlers
     */
    function getHandlers() : array {
        return $this->handlers;
    }
}

