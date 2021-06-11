<?php

namespace Models;

class SearchEngineSettings {       
    /**
     * providerName
     *
     * @var string
     */
    public $providerName;
    
    /**
     * providerSettings
     *
     * @var mixed
     */
    public $providerSettings;
    
    /**
     * __construct
     *
     * @param  string $name
     * @return SearchEngineSettings
     */
    function __construct(string $providerName)
    {
        $this->providerName     = $providerName;
        $this->providerSettings = [];
    }
}