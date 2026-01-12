<?php

namespace Application\Service;

use Psr\Log\LoggerInterface;

use Application\Service\Project\ProjectService;
use \ExternalModules\AbstractExternalModule;

use Application\Service\Cart\CartService;
use Application\Service\Cart\CartConfig;

use Application\Service\Search\SearchEngineService;

use Domain\Search\Contract\SearchEngine;
use Infrastructure\Search\SearchEngineFactory;
use Infrastructure\Search\SearchEngineConfig;

use Domain\Document\Contract\DocumentRepository;
use Infrastructure\Document\DocumentRepositoryFactory;
use Infrastructure\Document\DocumentRepositoryConfig;

/**
 * ServiceFactory
 */
class ServiceFactory
{
    private LoggerInterface $logger;
    /**
     * __construct
     */
    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * createProjectService
     */
    function createProjectService(AbstractExternalModule $module): ProjectService
    {
        return new ProjectService($this->logger, $module);
    }

    /**
     * createCartService
     */
    function createCartService(CartConfig $config): CartService
    {
        return new CartService($config);
    }

    /**
     * createSearchEngineService
     */
    function createSearchEngineService(DocumentRepositoryConfig $repositoryConfig, SearchEngineConfig $engineConfig): SearchEngineService
    {
        // Create DocumentRepository
        $repositoryFactory = new DocumentRepositoryFactory($this->logger);
        $repository = $repositoryFactory->createDocumentRepository($repositoryConfig);
        
        // Create SearchEngine
        $engineFactory = new SearchEngineFactory($this->logger);
        $engine = $engineFactory->createSearchEngine($engineConfig);

        return new SearchEngineService($this->logger, $repository, $engine);
    }
}