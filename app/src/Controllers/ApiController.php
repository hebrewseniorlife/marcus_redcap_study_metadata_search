<?php

namespace Controllers;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

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
    }

    function handle(Request $request, Response $reponse) : Response{
        return $reponse;
    }
}