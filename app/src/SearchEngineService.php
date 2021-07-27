<?php

use Models\SearchEngineSettings as SearchEngineSettings;
use SeachEngineFactory as SeachEngineFactory;
use ISearchEngine;
use Models\Project;
use Psr\Log\LoggerInterface;

class SearchEngineService extends AbstractService {       
    /**
     * engine
     *
     * @var ISearchEngine
     */
    protected $engine;
    
    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct($module, LoggerInterface $logger)
    {
        parent::__construct($module, $logger);
        
        $this->engine = self::createSearchEngine($this->getSearchEngineSettings(), $this->logger);
    }
    
    /**
     * getSearchEngineSettings
     *
     * @return SearchEngineSettings
     */
    function getSearchEngineSettings() : SearchEngineSettings 
    {
        $searchProviderName = $this->module->getSystemSetting("search-provider");
        if (count($searchProviderName) == 0){
            $searchProviderName = 'PhpSearchEngine';
        }

        $tempFolder = $this->module->getSystemSetting("temp-folder");
        switch($tempFolder){
            case 'system':
                $tempFolderPath = sys_get_temp_dir();
                break;
            case 'redcap':
            default:
                $tempFolderPath = constant("APP_PATH_TEMP");
                break;
        }

        $settings = new SearchEngineSettings($searchProviderName);
        $settings->providerSettings["temp_folder"] = $tempFolderPath;

        return $settings;
    }
    
    /**
     * updateAll
     *
     * @param  array $projects
     * @return void
     */
    function updateAll(array $projects = [])
    {
        $this->logger->warning("Updating all projects (n={count($projects)}). Rebuilding search engine index.");
        $this->engine->rebuild();
        
        foreach($projects as $project)
        {
            if ($project->enabled === true)
            {
                $this->update($project);
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

        foreach($project->documents as $document)
        {
            $this->engine->updateDocument((array) $document); 
        }
    }
    
    /**
     * search
     *
     * @param  string $phrase
     * @param  array $options
     * @return array
     */
    function search(string $phrase, array $options = []) : array
    {
        return $this->engine->search($phrase, $options);
    }
    
    /**
     * searchBy
     *
     * @param  mixed $attribute
     * @param  mixed $value
     * @return array
     */
    function searchBy(string $field, string $value) : array
    {
        return $this->engine->searchBy($field, $value);
    }
    
    /**
     * getStats
     *
     * @return array
     */
    function getStats() : array{
        return $this->engine->getStats();
    }
        
    /**
     * getDocument
     *
     * @param  mixed $id
     * @return void
     */
    function getDocument(string $id) {
        return $this->engine->getDocument($id);
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
     * destroy
     *
     * @return void
     */
    function destroy(){
        // Get all documents;
        $results = $this->engine->search("", []);

        $documentCount = count($results["documents"]);
        $this->logger->warning("Destroying search engine containing $documentCount.");

        // For each of the documents, delete it from the index
        foreach($results["documents"] as $key => $value){
            $this->engine->deleteDocument($key);
        }

        // Rebuild the index, which will clear the cache and index...
        $this->logger->warning("Rebuilding search engine.");
        $this->engine->rebuild();
    }

    /**
     * create
     *
     * @param  SearchEngineSettings $config
     * @return ISearchEngine
     */
    static public function createSearchEngine(SearchEngineSettings $settings, LoggerInterface $logger) : ISearchEngine {
        $provider = $settings->providerName;
        
        $logger->debug("Creating search engine using provider named: {$provider}.");

        switch($provider){
            case "PhpSearchEngine":
                $searchEngine = new PhpSearchEngine();
                break;
            default:
                throw new Exception("Search engine with provider name '{$provider}' is not supported.");
        }
        
        $searchEngine->initialize($settings, $logger);
        
        return $searchEngine;
    }
}