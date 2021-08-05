<?php
namespace Marcus\StudyMetadataSearch\ExternalModule;

use ProjectService;
use SearchEngineService;
use CronService;

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
	 * @return string
	 */
	public function getPrefix() : string {
		return $this->getModuleInfo()["prefix"];
	}
	
	/**
	 * getModuleInfo
	 *
	 * @return array
	 */
	public function getModuleInfo() : array {
		$directoryName = $this->getModuleDirectoryName();
		list($prefix, $releaseVersion) 			= explode('_v', $directoryName);
		list($releaseMajor, $releaseMinor) 		= explode('.', $version);

		$directoryPath = $this->getModulePath();
		$composer = json_decode(file_get_contents($directoryPath.'composer.json'), true);
		list($composerMajor, $composerMinor) = explode('.', $composer['version']);

		return [
			'prefix' 	=> $prefix,
			'release' => [
				'version' 	=> $releaseVersion,
				'major' 	=> $releaseMajor,
				'minor' 	=> $releaseMinor,
			],
			'composer'	=> [
				'version'	=> $composer['version'],
				'major' 	=> $composerMajor,
				'minor' 	=> $composerMinor
			]
		];
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

		// Set up Monolog and add the REDCap handler to it.
		$logger = \Logging\Log::getLogger('php://output');
		$logger->pushHandler(new \Logging\ExternalModuleLogHandler($this));

		// Get the system setting for automatic-reindex.		
		$enabled = $this->getSystemSetting("autorebuild-enabled");
		$logger->info("Automatic reindex is {$enabled}.");

		// // If the automatic-reindex is not enabled then exit
		if ($enabled !== "enabled")
		{
			return "Automatic reindex is not enabled.";			
		}

		// Get the cron service
		$cronService = new CronService($this, $logger);

		// Get the details inclulding the cron pattern and schedule
		$details  = $cronService->getDetails();
		$pattern  = $this->getSystemSetting('autorebuild-pattern');
		$schedule = $cronService->getSchedule($details['last_start_time'], $pattern);

		$is_due = ($schedule['is_due'] === true) ? "true" : "false"; 
		$logger->info("Automatic reindex scheduled for {$schedule['next_run_time']} (due={$is_due}).");

		// if the schedule says we are due to run then 
		if ($schedule['is_due'] === true){
			// Log the start of the cron job (in REDCap)
			$cronService->logStart();

			try
			{
				// Create the search engine service, and distory the current index
				$searchService = new SearchEngineService($this, $logger);
				$searchService->destroy();

				// Get all updated projects.
				$projectService = new ProjectService($this, $logger);
				$projects = $projectService->getProjects();

				// Update the search service using the new documents
				$searchService->updateAll($projects);

				$message = "The search engine index has been rebuilt.";
			}
			catch (\Exception $e) 
			{
				$message = "The search engine index rebuild failed: ".$e->getMessage();
			}
			finally
			{
				$logger->info($message);
			}
			
			// Log the stop of the cron job (in REDCap)
			$cronService->logStop();
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