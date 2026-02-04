<?php

namespace Infrastructure\Configuration\Contract;

use Application\Service\Cart\CartConfig;
use Infrastructure\Document\DocumentRepositoryConfig;
use Infrastructure\Logging\LoggingConfig;
use Infrastructure\Search\SearchEngineConfig;
use Infrastructure\FileSystem\FileSystemConfig;
use Infrastructure\Scheduler\SchedulerConfig;
use Application\Service\Project\ProjectListConfig;

/**
 * Provides configuration objects required by infrastructure factories/services.
 */
interface ConfigProvider
{
    
    /**
     * Retrieves the configuration settings for the search engine.
     *
     * @return SearchEngineConfig The configuration object containing search engine settings.
     */
    public function getSearchEngineConfig(): SearchEngineConfig;

    /**
     * Retrieves the logging configuration settings.
     *
     * @return LoggingConfig The configuration object containing logging settings.
     */
    public function getLoggingConfig(): LoggingConfig;


    /**
     * Retrieves the configuration for the document repository.
     *
     * @return DocumentRepositoryConfig The configuration object for the document repository.
     */
    public function getDocumentRepositoryConfig(): DocumentRepositoryConfig;

    /**
     * Retrieves the file system configuration.
     *
     * @return FileSystemConfig The configuration settings for the file system.
     */
    public function getFileSystemConfig() : FileSystemConfig;


    /**
     * Retrieves the shopping cart configuration.
     *
     * @return CartConfig The cart configuration object containing settings and properties
     *                    related to shopping cart functionality.
     */
    public function getCartConfig() : CartConfig;

    /**
     * Retrieves the scheduler configuration.
     *
     * @return SchedulerConfig The scheduler configuration object containing scheduling settings and parameters.
     */
    public function getSchedulerConfig() : SchedulerConfig;


    /**
     * Retrieves the project list configuration.
     *
     * @return ProjectListConfig The project list configuration object containing project settings.
     */
    public function getProjectListConfig() : ProjectListConfig;
}
