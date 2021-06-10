<?php

namespace Controllers;


/**
 * AppController
 */
class AppController {    
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