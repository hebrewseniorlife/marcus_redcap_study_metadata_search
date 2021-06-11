<?php

use ISearchEngine as ISearchEngine;
use Models\SearchEngineSettings;

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
    public static function create(SearchEngineSettings $settings) : ISearchEngine{
        // Future version will derive search engine class from system-level settings
        // .... only one serch engine supported currently.
        return new PhpSearchEngine($settings);
    }
}