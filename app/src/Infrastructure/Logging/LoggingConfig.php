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
    private array $handlers;

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
     * getHandlersByChannel
     *
     * @param  string $channel
     * @return LoggingHandlerConfig[]
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
}

