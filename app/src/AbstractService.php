<?php

use Psr\Log\LoggerInterface;
use Logging\Log;

abstract class AbstractService
{
    /**
     * module
     *
     * @var mixed
     */
    protected $module;
        
    /**
     * logger
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     * __construct
     *
     * @param  mixed $module
     * @param  LoggerInterface $logger
     * @return void
     */
    function __construct($module, LoggerInterface $logger = null)
    {
        $this->module = $module;

        if (is_a($logger, 'Psr\Log\LoggerInterface')){
            $this->logger = $logger;
        }
        else{
            $this->logger = Log::getLogger();
        }
    }
    
    /**
     * setLogger
     *
     * @param  mixed $logger
     * @return LoggerInterface
     */
    function setLogger(LoggerInterface $logger) : LoggerInterface {
        return $this->logger = $logger;
    }
}