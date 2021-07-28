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
		require_once(__DIR__."/app/bootstrap.php");

		$message = "";

		try 
		{		
			// Create a log handler to store logs to REDCap database.
			$moduleLogHandler = new \Logging\ExternalModuleLogHandler($this);
			$moduleLogHandler->setFormatter(new \Monolog\Formatter\LineFormatter('%message%'));

			// Get the default logger and add the REDCap log to it.
			$logger = \Logging\Log::getLogger();
			$logger->pushHandler($moduleLogHandler);

			// Create the search engine service, and distory the current index
			$searchService = new SearchEngineService($this, $logger);
			$searchService->destroy();
	
			// Get all updated projects.
			$projectService = new ProjectService($this, $logger);
			$projects = $projectService->getProjects();
	
			// Update the search service using the new documents
			$searchService->updateAll($projects);

			$message = "The {$cron['cron_name']} cron job service completed.";

		}
		catch (\Exception $e) {
			$message = "The {$cron['cron_name']} cron job service failed: ".$e->getMessage();
		}
		
		return $message;
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