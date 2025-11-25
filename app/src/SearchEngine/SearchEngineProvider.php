<?php

namespace SearchEngine;

class SearchEngineProvider {       
    /**
     * name
     *
     * @var string
     */
    public $name;
    
    /**
     * settings
     *
     * @var mixed
     */
    public $settings;
    
    /**
     * __construct
     *
     * @param  string $name
     * @return SearchEngineProvider
     */
    function __construct(string $name)
    {
        $this->name              = $name;
        $this->settings          = [];
    }
}
