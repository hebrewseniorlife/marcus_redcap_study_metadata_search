<?php

namespace Application\Service;

use Psr\Log\LoggerInterface;

use Application\Service\Project\ProjectService;
use Application\Service\Project\ProjectListConfig;
use Domain\Project\Contract\ProjectRepository;

use Application\Service\Cart\CartService;
use Application\Service\Cart\CartConfig;

use Application\Service\Search\SearchEngineService;

use Domain\Search\Contract\SearchEngine;
use Infrastructure\Search\SearchEngineFactory;
use Infrastructure\Search\SearchEngineConfig;

use Domain\Document\Contract\DocumentRepository;
use Infrastructure\Document\DocumentRepositoryFactory;
use Infrastructure\Document\DocumentRepositoryConfig;

use Application\Service\Scheduler\SchedulerService;
use Infrastructure\Scheduler\SchedulerConfig;

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
     * Creates and returns a new instance of ProjectService.
     *
     * @param ProjectListConfig $projectListConfig The project list configuration used to initialize the ProjectService.
     * @param ProjectRepository $projectRepository The project repository used to initialize the ProjectService.
     * @return ProjectService A configured ProjectService instance.
     */
    function createProjectService(ProjectListConfig $projectListConfig, ProjectRepository $projectRepository): ProjectService
    {
        return new ProjectService($this->logger, $projectListConfig, $projectRepository);
    }


    /**
     * Creates a CartService instance with the provided configuration.
     *
     * @param CartConfig $config The configuration object for the CartService
     * @return CartService The newly created CartService instance
     */
    function createCartService(CartConfig $config): CartService
    {
        return new CartService($config);
    }

    
    /**
     * Creates and returns a new SchedulerService instance.
     *
     * @param SchedulerConfig $config The configuration object for the scheduler service
     * @return SchedulerService A configured scheduler service instance
     */
    function createSchedulerService(SchedulerConfig $config): SchedulerService
    {
        return new SchedulerService($this->logger, $config);
    }


    /**
     * Creates a SearchEngineService instance with the provided configurations.
     *
     * @param DocumentRepositoryConfig $repositoryConfig The configuration for the document repository
     * @param SearchEngineConfig $engineConfig The configuration for the search engine
     *
     * @return SearchEngineService A configured instance of SearchEngineService
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