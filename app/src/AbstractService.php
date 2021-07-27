<?php

use Psr\Log\LoggerInterface;
use Logging\Log;
use Logging\ExternalModuleLogHandler;

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
        
        if ($logger === null)
        {
            $logger = Log::getLogger();
            $logger->pushHandler(new ExternalModuleLogHandler($module));
        }

        $this->setLogger($logger);
    }
    
    /**
     * setLogger
     *
     * @param  mixed $logger
     * @return LoggerInterface
     */
    function setLogger(LoggerInterface $logger = null) : LoggerInterface 
    {
        if (is_a($logger, 'Psr\Log\LoggerInterface'))
        {
            $this->logger = $logger;
        }
        else
        {
            throw new Exception('Logger may not be null and must support the Psr log interface.');
        }
        
        return $this->logger;
    }
    
    /**
     * getLogger
     *
     * @return LoggerInterface
     */
    function getLogger() : LoggerInterface 
    {
        return $this->logger;
    }
}