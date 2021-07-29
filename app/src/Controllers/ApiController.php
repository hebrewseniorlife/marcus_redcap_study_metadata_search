<?php

namespace Controllers;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Monolog\Logger;
use Logging\ExternalModuleLogHandler;

/**
 * ApiController
 */
class ApiController {    
    protected $module;
    protected $logger; 

    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(object $module)
    {
        $this->module = $module;
        $this->logger = \Logging\Log::getLogger();
        $this->logger->pushHandler(new ExternalModuleLogHandler($this->module, Logger::INFO));  

    }

    function handle(Request $request, Response $reponse) : Response{
        return $reponse;
    }
}