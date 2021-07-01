<?php

namespace Controllers;

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
}