<?php
namespace Marcus\StudyMetadataSearch\ExternalModule;

use Interface\ExternalModule\Configuration\ExternalModuleConfigProvider;
use Interface\ExternalModule\Project\ExternalModuleProjectRepository;
use Application\Service\Cron\CronServiceConfig;
use Infrastructure\Logging\LoggerFactory;
use Application\Service\ServiceFactory;

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

		// Get the configuration from the module
		$provider = new ExternalModuleConfigProvider($this);
		$schedulerConfig = $provider->getSchedulerConfig();

		// Create the logger
		$loggerFactory = new LoggerFactory($provider->getLoggingConfig());
		$logger = $loggerFactory->createLogger();

		// Create the project repository
		$projectRepository = new ExternalModuleProjectRepository($logger, $this);

		// Initialize the service factory
		$serviceFactory = new ServiceFactory($logger);
		$schedulerService 	= $serviceFactory->createSchedulerService($schedulerConfig);
		$searchService      = $serviceFactory->createSearchEngineService($provider->getDocumentRepositoryConfig(), $provider->getSearchEngineConfig());
		$projectService     = $serviceFactory->createProjectService($provider->getProjectListConfig(), $projectRepository);

		$message = "The search engine index rebuild has been scheduled.";

		// Run the scheduled job to rebuild the search engine index
		$schedulerService->runScheduledJob(function() use ($logger, $projectService, $searchService, &$message) {
			try {
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
		});

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