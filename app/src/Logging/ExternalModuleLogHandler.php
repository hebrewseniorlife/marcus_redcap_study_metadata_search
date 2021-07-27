<?php

namespace Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Formatter\LineFormatter;

class ExternalModuleLogHandler extends AbstractProcessingHandler
{    
    const DEFAULT_LEVEL  = Logger::DEBUG;
    const DEFAULT_FORMAT = '%message%';

    /**
     * module
     *
     * @var mixed
     */
    protected $module;

    public function __construct($module, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->module = $module;

        // Create a default formatter...
        $formatter = new LineFormatter(ExternalModuleLogHandler::DEFAULT_FORMAT);
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);

        $this->setFormatter($formatter);
    }

    protected function write(array $record): void
    {
        $parameters = array_merge($record['context'], [
            'channel' => $record['channel'],
            'level' => $record['level']
        ]);
        
        $this->module->log($record['formatted'], $parameters);
    }
}