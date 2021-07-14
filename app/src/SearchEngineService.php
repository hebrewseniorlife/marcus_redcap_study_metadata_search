<?php

use Models\SearchEngineSettings as SearchEngineSettings;
use SeachEngineFactory as SeachEngineFactory;
use ISearchEngine;
use Models\Project;

class SearchEngineService {    
    /**
     * module
     *
     * @var mixed
     */
    protected $module;    
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
    function __construct($module)
    {
        $this->module = $module;
        $this->engine = SeachEngineFactory::create($this->getSearchEngineSettings());
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
        $this->engine->rebuild();
        
        foreach($projects as $project){
            $this->update($project);
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
        foreach($project->documents as $document){
            $this->engine->update((array) $document); 
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
     * rebuild
     *
     * @return void
     */
    function rebuild(){
        $this->engine->rebuild();
    }
}