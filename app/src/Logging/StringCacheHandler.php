<?php

namespace Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class StringCacheHandler extends AbstractProcessingHandler
{
    private $_initialized = false;
    private $_cache       = [];

    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        if (!$this->_initialized) {
            $this->initialize();
        }

        array_push($this->_cache, array(
            'channel' => $record['channel'],
            'level' => $record['level'],
            'message' => $record['formatted'],
            'time' => $record['datetime']->format('U')
        ));
    }

    public function getCache(){
        return $this->_cache;
    }

    public function jsonSerialize() {
        return $this->_cache;
    }

    public function __toString()
    {
        return join(array_column($this->_cache, 'message'), PHP_EOL);
    }

    private function initialize()
    {
        $this->_cache        = [];
        $this->_initialized  = true;
    }
}