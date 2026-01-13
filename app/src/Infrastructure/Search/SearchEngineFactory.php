<?php

namespace Infrastructure\Search;

use Psr\Log\LoggerInterface;
use Domain\Search\Contract\SearchEngine;
use Infrastructure\Search\SearchEngineConfig;

class SearchEngineFactory
{
    /**
     * The default search engine provider used by the application.
     * This constant specifies the class name of the search engine to be used
     * when no specific provider is configured.
     */
    const DEFAULT_SEARCH_ENGINE_PROVIDER = 'TNTSearchEngine';

    /**
     * logger
     *
     * @var LoggerInterface
     */
    protected $logger;

   /**
     * __construct
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {  
        $this->logger = $logger;
    }

    /**
     * Creates and returns an instance of SearchEngine based on the provided SearchEngineConfig.
     *
     * @param SearchEngineConfig $config The configuration for the search engine.
     * @return SearchEngine|null The created search engine instance or null if creation fails.
     * @throws \Exception If the provider name is not supported.
     */
    public function createSearchEngine(SearchEngineConfig $config) : ? SearchEngine
    {
        $searchEngine = null;
        $providerName = $config->providerName;
        
        $this->logger->debug("Creating search engine using provider named: {$providerName}.");

        switch($providerName){
            case "TNTSearchEngine":
                $searchEngine = new \Infrastructure\Search\TNTSearchEngine();
                break;                
            default:
                throw new Exception("Search engine with provider name '{$providerName}' is not supported.");
        }
        
        $searchEngine->initialize($this->logger, $config);
        
        return $searchEngine;
    }
}