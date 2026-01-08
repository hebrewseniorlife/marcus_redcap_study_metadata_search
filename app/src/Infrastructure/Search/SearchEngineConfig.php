<?php

namespace Infrastructure\Search;

class SearchEngineConfig
{
    /**
     * providerName
     *
     * @var string
     */
    public $providerName;

    /**
     * settings
     *
     * @var mixed
     */
    public $settings;

    /**
     * Constructor for SearchEngineConfig.
     */
    public function __construct(string $providerName){  
        $this->providerName = $providerName;
        $this->settings     = [];
    }
}