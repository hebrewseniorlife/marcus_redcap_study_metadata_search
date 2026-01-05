<?php

namespace Interface\ExternalModule\Controller\Api;

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Psr\Log\LoggerInterface;
use Marcus\StudyMetadataSearch\ExternalModule\ExternalModule;

/**
 * ApiController
 */
class AbstractApiController {    
    protected $module;
    protected $logger; 

    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(LoggerInterface $logger, ExternalModule $module)
    {
        $this->module = $module; 
        $this->logger = $logger;
    }
    
    /**
     * handle
     *
     * @param  mixed $request
     * @param  mixed $reponse
     * @return Response
     */
    function handle(Request $request, Response $reponse) : Response
    {
        return $reponse;
    }
}