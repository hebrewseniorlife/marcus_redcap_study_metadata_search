<?php

use VFou\Search\Engine as Engine;
use VFou\Search\Query\QuerySegment;
use VFou\Search\Query\QueryBuilder;
use VFou\Search\Tokenizers\LowerCaseTokenizer as LowerCaseTokenizer;
use VFou\Search\Tokenizers\WhiteSpaceTokenizer as WhiteSpaceTokenizer;
use VFou\Search\Tokenizers\TrimPunctuationTokenizer as TrimPunctuationTokenizer;
use VFou\Search\Tokenizers\WordSeparatorTokenizer as WordSeparatorTokenizer;
use VFou\Search\Tokenizers\IntegerSeparatorTokenizer as IntegerSeparatorTokenizer;

use Psr\Log\LoggerInterface;
use Models\SearchEngineSettings as SearchEngineSettings;
use ISearchEngine as ISearchEngine;

class PhpSearchEngine implements ISearchEngine {    
    /**
     * Engine
     *
     * @var Engine
     */
    protected $engine;
    
    /**
     * logger
     *
     * @var LoggerInterface
     */
    protected $logger;
           
    /**
     * initialize
     *
     * @param  SearchEngineSettings $settings
     * @param  LoggerInterface $logger
     * @return void
     */
    public function initialize(SearchEngineSettings $settings, LoggerInterface $logger)
    {
        $configuration = [
            "config"    => PhPSearchEngine::getConfig($settings),
            "schemas"   => [
                "document" => PhpSearchEngine::getSchema($settings),
                "example-post" => null
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
        $this->logger = (isset($logger)) ? $logger : \Logging\Log::getLogger();
    } 

    /**
     * updateDocument
     *
     * @param  Document $document
     * @return void
     */
    function updateDocument(array $document){
        $document["type"] = "document";

        $this->engine->update($document);
    }
        
    /**
     * deleteDocument
     *
     * @param  mixed $id
     * @return void
     */
    function deleteDocument(string $id){
        $this->engine->delete($id);
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
     * search
     *
     * @param  mixed $phrase
     * @param  mixed $options
     * @return void
     */
    function search(string $term, array $options) : array {
        if (!isset($options)){
            $options = PhpSearchEngine::getDefaultSearchOptions();
        }
        return $this->engine->search($term, $options);
    }
    
    /**
     * searchBy
     *
     * @param  mixed $attribute
     * @param  mixed $value
     * @return void
     */
    function searchBy(string $field, string $value) : array {
        $segment = QuerySegment::and(QuerySegment::exactSearch($field, $value));
        $term    = ""; // Open-ended search..
        $options = PhpSearchEngine::getDefaultSearchOptions();

        $query = new QueryBuilder($term, $segment);
        $query->setLimit(1000);
        
        foreach($options['facets'] as $index => $facet) {
            $query->addFacet($facet);
        } 
        
        return $this->engine->search($query);
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
     * getConfig
     *
     * @param  SearchEngineSettings $settings
     * @return array
     */
    static function getConfig(SearchEngineSettings $settings) : array {
        $tempFolder = $settings->providerSettings["temp_folder"];

        return [
            "var_dir"       => $tempFolder.DIRECTORY_SEPARATOR."marcus-search-engine".DIRECTORY_SEPARATOR,
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
    
    /**
     * getDefaultSearchOptions
     *
     * @return array
     */
    static function getDefaultSearchOptions() : array{
        return [
            'limit' => 250, 
            'facets' => ['project_title', 'entity', 'form_name']
        ];
    }
}