<?php
namespace Marcus\StudyMetadataSearch\ExternalModule;

use Infrastructure\Logging\LoggingConfig;
use Infrastructure\Configuration\SystemConfig;
use Application\Services\ProjectService;
use Application\Services\SearchEngineService;
use Application\Services\CronService;


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
	 * getSystemConfig
	 *
	 * @return SystemConfig
	 */
	public function getSystemConfig() : SystemConfig {
		$loggingConfig = $this->getLoggingConfig();
		$apiKeys	   = $this->getApiKeys();

		return new SystemConfig($loggingConfig, $apiKeys);
	}

	/**
	 * getLogConfig
	 *
	 * @return LoggingConfig
	 */
	protected function getLoggingConfig() : LoggingConfig {
		$logLevel = $this->getSystemSetting('log-level') ?? 0;
		return new LoggingConfig($logLevel);
	}

	/**
	 * getNamedApiKeys
	 *
	 * @return array
	 */
	public function getApiKeys(): array {
		$apiKeys = [];

		$keys   = $this->getSystemSetting("api-key");
        $names  = $this->getSystemSetting("api-name");

		foreach ($keys as $index => $key) {
			if (strlen($key) > 0) {
				$apiKeys[$key] = $names[$index] ?? "Unnamed Key";
			}
		}

		return $apiKeys;
	}

	public function getModuleDirectoryName() : string {
		return $this->PREFIX . '_v' . $this->VERSION;
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
				// Get all updated projects.
				$projectService = new ProjectService($this, $logger);
				$projects = $projectService->getProjects();

				// Create the search engine service, and poplulate the document repository with the updated projects.
				$searchService = new SearchEngineService($this, $logger);
				$searchService->populateProjects($projects);
				
				// Rebuild the search engine index.
				$searchService->createIndex();

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