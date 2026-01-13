<?php
namespace Marcus\StudyMetadataSearch\ExternalModule;

use Interface\ExternalModule\Configuration\ExternalModuleConfigProvider;
use Application\Service\Cron\CronServiceConfig;
use Infrastructure\Logging\LoggerFactory;
use Application\Service\Project\ProjectService;
use Application\Service\Search\SearchEngineService;
use Application\Service\Cron\CronService;


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
	 * getCronConfig
	 *
	 * @return CronConfig
	 */
	public function getCronServiceConfig() : CronServiceConfig {
		// Get the autorebuild settings
		$enabledSetting = $this->getSystemSetting('autorebuild-enabled') ?? 'disabled';
		$enabled = ($enabledSetting === 'enabled') ? true : false;

		// Get the autorebuild pattern
		$pattern = $this->getSystemSetting('autorebuild-pattern') ?? '';

		// Get the cron jobs (see config.json)
		$config = $this->getConfig();

		// Create and return the cron config
		return new CronServiceConfig($enabled, $pattern, $config["crons"]);
	}

	/**
	 * getModuleDirectoryName
	 *
	 * @return string
	 */
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

		// Get the system configuration from the REDCap module
		$systemConfig = $this->getSystemConfig();
		$cronConfig   	= $systemConfig->cron;

		// Modify the logging config to log to output
		$loggingConfig 	= $systemConfig->logging;
		$loggingConfig->stream = 'php://output';

		// Create the logger
		$loggerFactory = new LoggerFactory();
		$logger = $loggerFactory->createLogger($loggingConfig);

		// Add the REDCap log handler if logging is enabled.
		if ($systemConfig->logging->isEnabled())
		{
			$logger->pushHandler(new ExternalModuleLogHandler($systemConfig->logging->level, true, $this));  
		}

		// Log whether automatic reindex is enabled or disabled.
		$enabled = $cronConfig->enabled ? "enabled" : "disabled";
		$logger->info("Automatic reindex is $enabled.");	


		// Get the cron service
		$cronService = new CronService($logger, $cronConfig, $this);

		// Get the details inclulding the cron pattern and schedule
		$details  = $cronService->getDetails();
		$pattern  = $cronConfig->pattern;
		$schedule = $cronService->getSchedule($details['last_start_time'], $pattern);

		$is_due = ($schedule['is_due'] === true) ? "true" : "false"; 
		$logger->info("Automatic reindex scheduled for {$schedule['next_run_time']} (due={$is_due}).");

		// if the schedule says we are due to run then 
		if ($schedule['is_due'] === true){
			// Log the start of the cron job (in REDCap)
			$cronService->logStart();

			try
			{
				// Initialize document repository
				$documentRepositoryFactory = new DocumentRepositoryFactory($logger);
				$documentRepository = $documentRepositoryFactory->createDocumentRepository($systemConfig->documentRepository);
				
				// Create search engine
				$searchEngineFactory = new SearchEngineFactory($logger);
				$searchEngine = $searchEngineFactory->createSearchEngine($systemConfig->searchEngine);				

				// Initialize services
				$searchService    = new SearchEngineService($logger, $documentRepository, $searchEngine);
				$projectService   = new ProjectService($module);

				// Populate the projects into the search engine
				$projects = $projectService->getProjects();
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