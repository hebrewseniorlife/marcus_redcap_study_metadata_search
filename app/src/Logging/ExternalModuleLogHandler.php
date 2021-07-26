<?php

namespace Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class ExternalModuleLogHandler extends AbstractProcessingHandler
{    
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