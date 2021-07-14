<?php

namespace Controllers;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

/**
 * ApiController
 */
class ApiController {    
    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(object $module)
    {
        $this->module = $module;
    }

    function handle(Request $request, Response $reponse) : Response{
        return $reponse;
    }
}