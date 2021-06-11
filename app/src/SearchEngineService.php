<?

use Models\SearchEngineSettings as SearchEngineSettings;
use SeachEngineFactory as SeachEngineFactory;
use ISearchEngine;

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
    function getSearchEngineSettings() : SearchEngineSettings {
        // Future version will get settings from system-level module settings...
        return new SearchEngineSettings("PhpSearchEngine");
    }

    function updateAll(array $project_ids){
        
    }

    function update(int $pid){
        
    }
}