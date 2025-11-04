<?php


use Models\SearchEngineProvider as SearchEngineProvider;
use SearchEngines\ISearchEngine;
use Models\Project;
use Models\Document as Document;
use Models\SearchEngineResult as SearchEngineResult;
use Psr\Log\LoggerInterface;
use SettingsHelper as SettingsHelper;
use DocumentRepository as DocumentRepository;
use SearchEngineFactory as SearchEngineFactory;

/**
 * SearchEngineService
 */
class SearchEngineService extends AbstractService {       
    /**
     * engine
     *
     * @var ISearchEngine
     */
    protected $engine;

    /**
     * repository
     *
     * @var DocumentRepository
     */
    protected $repository;

    /**
     * provider
     *
     * @var SearchEngineProvider
     */
    protected $provider;
    
    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct($module, LoggerInterface $logger)
    {
        parent::__construct($module, $logger);

        $this->provider = SearchEngineFactory::createProvder($this->module);
        $this->engine = SearchEngineFactory::createSearchEngine($this->provider, $this->logger);

        $folderPath = SettingsHelper::getTempFolderPath($this->module);
        $this->repository = new DocumentRepository($this->module, $this->logger, $folderPath);
    }
    
    /**
     * getProvider
     *
     * @return SearchEngineProvider
     */
    function getProvider() : SearchEngineProvider {
        return $this->provider;
    }

    /**
     * updateAll
     *
     * @param  array $projects
     * @return void
     */
    function updateAll(array $projects = [])
    {
        $count = count($projects);
        $this->logger->warning("Search Engine Service: Updating all projects (n={$count}). Rebuilding search engine index.");
        
        foreach($projects as $project)
        {
            if ($project->enabled === true)
            {
                $this->logger->info("Search Engine Service: Indexing Project: {$project->project_id} - {$project->title}");
                $this->update($project);
            }
            else{
                $this->logger->info("Search Engine Service: Skipping disabled Project: {$project->project_id} - {$project->title}");
            }
        }
    }
    
    /**
     * update
     *
     * @param  Project $project
     * @return void
     */
    function update(Project $project = null)
    {
        if ($project->enabled !== true){
            throw new Exception("Project is not enabled and may not be indexed.");
        }
        
        // Must insert into repository first to get IDs.
        foreach($project->documents as &$document)
        {
            $this->repository->upsert($document);
        }

        unset($document);

        // Insert documents into search engine.
        for($i = 0; $i < count($project->documents); $i++)
        {
            $document = $project->documents[$i];
            $this->logger->debug("Indexing document: {$document->id} - {$document->key}");
            
            $this->engine->insertDocument($document);
        }

        unset($document);
    }
    
    /**
     * search
     *
     * @param  string $phrase
     * @param  array $options
     * @return SearchEngineResult
     */
    function search(string $phrase, array $options = []) : SearchEngineResult
    {
        $this->logger->info("Search Engine Service: Search requested (see context).", ["phrase" => $phrase]);

        $results = $this->engine->search($phrase, $options);
        $this->logger->debug("Search Engine Service: Search returned ".count($results)." results.");

        $documents = [];
        if (count($results["ids"]) > 0){
            $documents = $this->getDocuments($results["ids"]);
        }

        return new SearchEngineResult($documents);
    }
    
    /**
     * searchBy
     *
     * @param  mixed $attribute
     * @param  mixed $value
     * @return SearchEngineResult
     */
    function searchBy(string $field, string $value) : SearchEngineResult
    {
        $this->logger->info("Search Engine Service: Search By requested (see context).", ["field" => $field, "value" => $value]);
        
        $results = $this->engine->searchBy($field, $value);
        
        $this->logger->debug("Search Engine Service: Search returned ".count($results)." results.");

        $documents = [];
        if (count($results["ids"]) > 0){
            $documents = $this->getDocuments($results["ids"]);
        }
        
        return new SearchEngineResult($documents);
    }
    
    /**
     * getStats
     *
     * @return array
     */
    function getStats() : array{
        $stats = [
            "engine" => $this->engine->getStats(),
            "repository" => [
                "status" => "Repository operational",
                "count" => $this->repository->count()
            ]
        ];

        return $stats;
    }
        
    /**
     * getDocument
     *
     * @param  mixed $id
     * @return void
     */
    function getDocument(string $id) : ?Document{
        return $this->repository->find($id);
    }
    
    /**
     * getDocuments
     *
     * @param  mixed $ids
     * @return array
     */
    function getDocuments(array $ids) : array {
        $documents = [];
        foreach($ids as $id){
            $document = $this->getDocument($id);
            if ($document != null){
                array_push($documents, $document);
            }
        }
        return $documents;
    }
       
    /**
     * rebuild
     *
     * @return void
     */
    function purgeAll() : void {
        $this->logger->warning("Search Engine Service: Purging search engine and document repository.");
        
        // Delete all documents from the existing repository.
        $ids = $this->repository->deleteAll();

        // Delete all documents from search engine.(if it exists)
        foreach($ids as $id){
            $this->engine->deleteDocument($id);
        }   
    }
}