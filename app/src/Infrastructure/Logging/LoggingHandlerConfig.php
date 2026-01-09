<?php

namespace Infrastructure\Logging;

use Monolog\Level as Level;
use Infrastructure\Logging\LoggingConfig;

class LoggingHandlerConfig
{
    const DATETIME_FOMAT = "Y-m-d H:i:s";
    const DEFAULT_LEVEL  = Level::Debug;
    const DEFAULT_FORMAT = '[%datetime%] [%channel%] [%level_name%] %message%'.PHP_EOL;
    const DEFAULT_STREAM = 'php://memory';

    /** level
      *
      * @var int
      */
    public int $level;

    /** stream
      *
      * @var string
      */
    public string $stream;

    /** channels
      *
      * @var string[]
      */
    public array $channels = [];

    /** format
      *
      * @var string
      */
    public string $format;

    /** dateTimeFormat
      *
      * @var string
      */
    public string $dateTimeFormat;

    /** __construct
      *
      * @param int    $level  The logging level
      * @param string $stream The logging stream
      */
    public function __construct(int $level, string $stream, array $channels = []) {
        $this->level = $level;
        $this->stream = $stream;
        $this->channels = $channels;

        // Not currently configurable, set to defaults
       
        $this->format           = self::DEFAULT_FORMAT;
        $this->dateTimeFormat   = self::DATETIME_FOMAT;
    }
}