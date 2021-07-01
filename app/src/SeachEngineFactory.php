<?php

use ISearchEngine as ISearchEngine;
use Models\SearchEngineSettings;
use PhpSearchEngine;

/**
 * SeachEngineFactory
 */
class SeachEngineFactory {    
    /**
     * create
     *
     * @param  SearchEngineSettings $config
     * @return ISearchEngine
     */
    static public function create(SearchEngineSettings $settings) : ISearchEngine {
        // Future version will derive search engine class from system-level settings
        // .... only one serch engine supported currently.
        return new PhpSearchEngine($settings);
    }
}