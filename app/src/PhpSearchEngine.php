<?php

use VFou\Search\Engine as Engine;
use VFou\Search\Tokenizers\LowerCaseTokenizer as LowerCaseTokenizer;
use VFou\Search\Tokenizers\WhiteSpaceTokenizer as WhiteSpaceTokenizer;
use VFou\Search\Tokenizers\TrimPunctuationTokenizer as TrimPunctuationTokenizer;
use VFou\Search\Tokenizers\WordSeparatorTokenizer as WordSeparatorTokenizer;
use VFou\Search\Tokenizers\IntegerSeparatorTokenizer as IntegerSeparatorTokenizer;


use Models\SearchEngineSettings as SearchEngineSettings;
use ISearchEngine as ISearchEngine;

class PhpSearchEngine implements ISearchEngine {    
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
            "schemas"   => [
                "document" => PhpSearchEngine::getSchema($settings)
            ],
            "types" => [
                "_default" => [
                    LowerCaseTokenizer::class,
                    WhiteSpaceTokenizer::class,
                    TrimPunctuationTokenizer::class
                ],
                "key" => [
                    WordSeparatorTokenizer::class,
                    LowerCaseTokenizer::class,
                    WhiteSpaceTokenizer::class,
                    TrimPunctuationTokenizer::class
                ],
                "integer" => [
                    IntegerSeparatorTokenizer::class   
                ]
            ]
        ];

        $this->engine = new Engine($configuration);
    }
    
    /**
     * update
     *
     * @param  Document $document
     * @return void
     */
    function update(array $document){
        $document["type"] = "document";

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
        return $this->engine->search($phrase, ['limit' => 100, 'facets' => ['project_title', 'entity', 'form_name']]);
    }
    
    /**
     * rebuild
     *
     * @return void
     */
    function rebuild(){
        $this->engine->getIndex()->rebuild();
    }
        
    /**
     * getStats
     *
     * @return array
     */
    function getStats() : array {
        return $this->engine->getIndex()->getStats();
    }
    
    /**
     * getDocument
     *
     * @param  string $id
     * @return void
     */
    function getDocument(string $id) {
        return $this->engine->getIndex()->getDocument($id);
    }

    /**
     * getConfig
     *
     * @param  SearchEngineSettings $settings
     * @return array
     */
    static function getConfig(SearchEngineSettings $settings) : array {
        $tempFolder = $settings->providerSettings["temp_folder"];

        return [
            "var_dir"       => $tempFolder."marcus-search-engine".DIRECTORY_SEPARATOR,
            "index_dir"     => "index",
            "documents_dir" => "documents",
            "cache_dir"     => "cache",
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
            "name" => [
                "_type" => "key",
                "_indexed" => true,
                "_boost" => 5
            ],
            "label" => [
                "_type" => "_default",
                "_indexed" => true,
                "_boost" => 10
            ],
            "entity" => [
                "_type" => "_default",
                "_indexed" => true,
                "_filterable" => true,
                "_boost" => 2
            ],
            "project_title" => [
                "_type" => "_default",
                "_indexed" => true,
                "_filterable" => true,
                "_boost" => 2
            ],
            "field_type" => [
                "_type" => "_default",
                "_indexed" => true,
                "_filterable" => true,
                "_boost" => 2
            ],
            "form_name" => [
                "_type" => "key",
                "_indexed" => true,
                "_filterable" => true,
                "_boost" => 3
            ],
            "project_id" => [
                "_type" => "integer",
                "_indexed" => false,
                "_filterable" => false
            ],            
            "context" => [
                "_type" => "_default",
                "_indexed" => false,
                "_filterable" => false
            ]
        ];
    }
}