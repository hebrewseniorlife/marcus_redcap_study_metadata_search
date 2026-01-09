<?php
namespace Marcus\StudyMetadataSearch\ExternalModule;

use Infrastructure\Configuration\SystemConfig;
use Infrastructure\Logging\LoggingConfig;
use Infrastructure\Logging\LoggingHandlerConfig;
use Infrastructure\Document\DocumentRepositoryConfig;
use Infrastructure\Search\SearchEngineConfig;
use Application\Service\Cron\CronServiceConfig;

use Infrastructure\Logging\LoggerFactory;
use Infrastructure\ExternalModule\Logging\ExternalModuleLogHandler;

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
	 * getSystemConfig
	 *
	 * @return SystemConfig
	 */
	public function getSystemConfig() : SystemConfig {
		$loggingConfig 		= $this->getLoggingConfig();
		$apiKeys	   		= $this->getApiKeys();
		$tempFolder	   		= $this->getTempFolder();
		$searchEngineConfig = $this->getSearchEngineConfig();
		$documentRepoConfig = $this->getDocumentRepositoryConfig();
		$cronServiceConfig	= $this->getCronServiceConfig();

		return new SystemConfig($loggingConfig, $tempFolder, $documentRepoConfig, $searchEngineConfig, $cronServiceConfig, $apiKeys);
	}

	/**
	 * getLogConfig
	 *
	 * @return LoggingConfig
	 */
	protected function getLoggingConfig() : LoggingConfig {
		$logLevel 	= $this->getSystemSetting('log-level') ?? 0;
		$tempFolder = $this->getTempFolder();
		$prefix	 	= $this->getPrefix();

		$handlerConfigs = [
			new LoggingHandlerConfig(
				level: $logLevel,
				stream: 'php://memory',
				channels: []
			),
			new LoggingHandlerConfig(
				level: $logLevel,
				stream: $tempFolder.DIRECTORY_SEPARATOR.$prefix.'.ndjson',
				channels: [LoggingConfig::DEFAULT_CHANNEL]
			)
		];

		$config = new LoggingConfig(handlers: $handlerConfigs);
		
		return $config;
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

	/**
	 * getTempDir
	 *
	 * @return string
	 */
	public function getTempFolder() : string {
        $tempFolder = $this->getSystemSetting("temp-folder") ?? null;
        switch($tempFolder){
            case 'custom' :
                $customFolderPath   = $this->getSystemSetting("custom-temp-folder");
                $tempFolderPath     = realpath($customFolderPath); // May need to be upgraded in future version...
                break;
            case 'system':
                $tempFolderPath = sys_get_temp_dir();
                break;
            case 'redcap':
            default:
                $tempFolderPath = constant("APP_PATH_TEMP");
                break;
        }
        
        if (!is_dir($tempFolderPath))
        {
            throw new Exception("Temp folder ($tempFolderPath) is not a directory. See system-level module configuration.");
        }                

        return $tempFolderPath . DIRECTORY_SEPARATOR . $this->PREFIX; 
	}


	/**
	 * getSearchEngineConfig
	 *
	 * @return SearchEngineConfig
	 */
	public function getSearchEngineConfig() : SearchEngineConfig {
		$providerName = $this->getSystemSetting('search-engine-provider') ?? 'TNTSearchEngine';
		$configValue  = $this->getSystemSetting('search-engine-config') ?? '{}';
		$tempFolderPath = $this->getTempFolder();

		$config = new SearchEngineConfig($providerName);
		$config->settings["config"] = json_decode($configValue, true);
		$config->settings["temp_folder_path"] = $tempFolderPath;

		return $config;
	}

	/**
	 * getDocumentRepositoryConfig
	 *
	 * @return array
	 */
	public function getDocumentRepositoryConfig() : DocumentRepositoryConfig {
		$tempFolder = $this->getTempFolder();
		$dsn 		= "sqlite:".$tempFolder.DIRECTORY_SEPARATOR."documents.sqlite";

		return new DocumentRepositoryConfig($tempFolder, $dsn);
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