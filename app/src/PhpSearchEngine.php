<?php

use VFou\Search\Engine as Engine;
use Models\SearchEngineSettings as SearchEngineSettings;

class PhpSearchEngine extends ISearchEngine {    
    /**
     * Engine
     *
     * @var mixed
     */
    protected $engine;
    
    /**
     * __construct
     *
     * @param  SearchEngineSettings $settings
     * @return PhpSearchEngine
     */
    function __construct(SearchEngineSettings $settings)
    {
        $configuration = [
            "config"    => PhPSearchEngine::getConfig($settings),
            "schemas"   => PhpSearchEngine::getSchema($settings)
        ];
        $this->engine = new Engine($configuration);
    }
    
    /**
     * update
     *
     * @param  mixed $document
     * @return void
     */
    function update(array $document){
        $this->engine->update($document);
    }
    
    /**
     * search
     *
     * @param  mixed $phrase
     * @param  mixed $options
     * @return void
     */
    function search(string $phrase, array $options){
        return $this->engine->search($phrase);
    }


    
    /**
     * getConfig
     *
     * @param  SearchEngineSettings $settings
     * @return array
     */
    static function getConfig(SearchEngineSettings $settings) : array {
        return [
            "var_dir"       => APP_PATH_TEMP."marcus-searchengine".DIRECTORY_SEPARATOR."var",
            "index_dir"     => APP_PATH_TEMP."marcus-searchengine".DIRECTORY_SEPARATOR."index",
            "documents_dir" => APP_PATH_TEMP."marcus-searchengine".DIRECTORY_SEPARATOR."documents",
            "cache_dir"     => APP_PATH_TEMP."marcus-searchengine".DIRECTORY_SEPARATOR."cache",
            "fuzzy_cost" => 1,
            "connex" => [
                'threshold' => 0.9,
                'min' => 3,
                'max' => 10,
                'limitToken' => 20,
                'limitDocs' => 10
            ],
            "serializableObjects" => [
                DateTime::class => function($datetime) { /** @var DateTime $datetime */ return $datetime->getTimestamp(); }
            ]
        ];
    }
    
    /**
     * getSchema
     *
     * @param  SearchEngineSettings $settings
     * @return array
     */
    static function getSchema(SearchEngineSettings $settings) : array {
        return [
            "title" => [
                "_type" => "string",
                "_indexed" => true,
                "_boost" => 10
            ],
            "content" => [
                "_type" => "text",
                "_indexed" => true,
                "_boost" => 0.5
            ],
            "date" => [
                "_type" => "datetime",
                "_indexed" => true,
                "_boost" => 2
            ],
            "categories" => [
                "_type" => "list",
                "_type." => "string",
                "_indexed" => true,
                "_filterable" => true,
                "_boost" => 6
            ]
        ];
    }
}