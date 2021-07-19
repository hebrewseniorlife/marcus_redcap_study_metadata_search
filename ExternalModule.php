<?php
namespace Marcus\StudyMetadataSearch\ExternalModule;

use ProjectService;
use SearchEngineService;

/**
 * ExternalModule  - (required) Abstract implementation of REDCap module
 */
class ExternalModule extends \ExternalModules\AbstractExternalModule {	
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * getPrefix
	 *
	 * @return void
	 */
	public function getPrefix(){
		return "marcus_study_metadata_search";
	}
			
	/**
	 * rebuild_search_engine_index
	 *
	 * @param  mixed $cronInfo
	 * @return void
	 */
	public function rebuild_search_engine_index($cron) 
	{
		$searchService = new SearchEngineService($this);
        $searchService->destroy();

        $projectService = new ProjectService($this);
        $projects = $projectService->getProjects();

        $searchService->updateAll($projects);

		return "The {$cron['cron_name']} cron job completed successfully.";
	}

	/**
	 * redcap_module_system_enable
	 *
	 * @param  mixed $version
	 * @return void
	 */
	public function redcap_module_system_enable( $version ) 
	{
		
	}
    
    /**
     * redcap_project_home_page
     *
     * @param  mixed $project_id
     * @return void
     */
    function redcap_project_home_page ($project_id) 
    {

    }
}