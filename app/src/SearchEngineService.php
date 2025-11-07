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
     * populateProjects
     *
     * @param  array $projects
     * @return void
     */
    function populateProjects(array $projects = []) : void
    {
        $count = count($projects);
        $this->logger->info("Search Engine Service: Populating all projects (n={$count}).");
        
        foreach($projects as $project)
        {
            $this->populateProject($project);
        }
    }

    /**
     * populateByProject
     *
     * @param  Project $project
     * @return void
     */
    function populateProject(Project $project = null) : void
    {
        if ($project->enabled !== true){
            $this->logger->info("Search Engine Service: Project: {$project->project_id} - {$project->title} is disabled. Skipping populate.");
            return;
        }   
        
        $this->logger->info("Search Engine Service: Populating Project: {$project->project_id} - {$project->title}");
        $this->pupulateDocuments($project->documents);
    }

    /**
     * pupulateDocuments
     *
     * @param  array $documents
     * @return void
     */
    function pupulateDocuments(array $documents = []) : void
    {
        $count = count($documents);
        $this->logger->info("Search Engine Service: Populating all documents (n={$count}) in repository.");
        
        foreach($documents as &$document)
        {
            $this->logger->debug("Search Engine Service: Populating Document: {$document->id} - {$document->key}");
            $this->repository->upsert($document);
        }
    }

    /**
     * indexProjects
     *
     * @param  array $projects
     * @return void
     */
    function indexProjects(array $projects = []) : void
    {
        $count = count($projects);
        $this->logger->info("Search Engine Service: Indexing all projects (n={$count}).");
        
        foreach($projects as $project)
        {
            $this->indexProject($project);
        }
    }

    /**
     * indexProject
     *
     * @param  Project $project
     * @return void
     */
    function indexProject(Project $project = null) : void
    {
        if ($project->enabled !== true){
            $this->logger->info("Search Engine Service: Project: {$project->project_id} - {$project->title} is disabled. Skipping index.");
            return;
        }   
        
        $this->logger->info("Search Engine Service: Indexing Project: {$project->project_id} - {$project->title}");
        $this->indexDocuments($project->documents);
    }

    /**
     * indexDocuments
     *
     * @param  array $documents
     * @return void
     */
    function indexDocuments(array $documents = []) : void
    {
        $count = count($documents);
        $this->logger->info("Search Engine Service: Indexing all documents (n={$count}). Rebuilding search engine index.");
        
        foreach($documents as $document)
        {
            $this->logger->debug("Search Engine Service: Indexing Document: {$document->id} - {$document->key}");
            $this->engine->insertDocument($document);
        }
    }

    /**
     * getAllDocuments
     *
     * @return array
     */
    function getAllDocuments() : array {
        return $this->repository->getAll();
    }
    
    /**
     * getDocumentsByProject
     *
     * @param  int $project_id
     * @return array
     */
    function getDocumentsByProject(int $project_id) : array {
        return $this->repository->findByProject($project_id);
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
        // $this->logger->info("Search Engine Service: Search By requested (see context).", ["field" => $field, "value" => $value]);      
        // $results = $this->engine->searchBy($field, $value);
        // $this->logger->debug("Search Engine Service: Search returned ".count($results)." results.");
        // $documents = [];
        // if (count($results["ids"]) > 0){
        //     $documents = $this->getDocuments($results["ids"]);
        // }
        
        $this->logger->info("Search Engine Service: Finding Documents By.", ["field" => $field, "value" => $value]);
        $documents = $this->repository->findAllBy($field, $value);

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