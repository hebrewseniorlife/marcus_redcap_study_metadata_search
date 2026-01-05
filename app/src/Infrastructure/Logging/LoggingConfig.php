<?php

namespace Infrastructure\Logging;

use Monolog\Level as Level;

final class LoggingConfig
{ 
    const DATETIME_FOMAT = "Y-m-d H:i:s";
    const DEFAULT_LEVEL  = Level::Debug;
    const DEFAULT_FORMAT = '[%datetime%] [%channel%] [%level_name%] %message%'.PHP_EOL;
    const DEFAULT_STREAM = 'php://memory';
    const DEFAULT_CHANNEL = 'marcus_redcap';

    /** level
      *
      * @var int
      */
    public readonly int $level;

    /** stream
      *
      * @var string
      */
    public readonly string $stream;

    /** channel
      *
      * @var string
      */
    public readonly string $channel;

    /** format
      *
      * @var string
      */
    public readonly string $format;

    /** dateTimeFormat
      *
      * @var string
      */
    public readonly string $dateTimeFormat;

    /** __construct
      *
      * @param int    $level  The logging level
      * @param string $stream The logging stream
      */
    public function __construct(int $level = LoggingConfig::DEFAULT_LEVEL, string $stream = LoggingConfig::DEFAULT_STREAM) {
        $this->level = $level;
        $this->stream = $stream;

        // Not currently configurable, set to defaults
        $this->channel = LoggingConfig::DEFAULT_CHANNEL;
        $this->format = LoggingConfig::DEFAULT_FORMAT;
        $this->dateTimeFormat = LoggingConfig::DATETIME_FOMAT;
    }

    /** isEnabled
      *
      * @return bool True if logging is enabled, false otherwise
      */
    public function isEnabled(): bool
    {
        return $this->level > 0;
    }
}

