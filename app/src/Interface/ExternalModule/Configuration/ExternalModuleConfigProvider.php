<?php

namespace Interface\ExternalModule\Configuration;

use Infrastructure\Configuration\Contract\ConfigProvider;
use Infrastructure\Document\DocumentRepositoryConfig;
use Infrastructure\Logging\LoggingConfig;
use Infrastructure\Logging\LoggingHandlerConfig;
use Infrastructure\Search\SearchEngineConfig;
use Infrastructure\FileSystem\FileSystemConfig;
use Infrastructure\Scheduler\SchedulerConfig;
use Application\Service\Cart\CartConfig;
use \ExternalModules\AbstractExternalModule;

class ExternalModuleConfigProvider implements ConfigProvider {
    
    private AbstractExternalModule $module;

    /**
     * Constructor for ExternalModuleConfigProvider.
     *
     * Initializes the configuration provider with the specified external module instance.
     *
     * @param AbstractExternalModule $module The external module instance to associate with this configuration provider.
     */
    public function __construct(AbstractExternalModule $module) {
        $this->module = $module;
    }

    /**
     * Retrieves the configuration settings for the search engine.
     *
     * @return SearchEngineConfig The configuration object for the search engine.
     */
    public function getSearchEngineConfig(): SearchEngineConfig {
        $providerName = $this->module->getSystemSetting('search-engine-provider') ?? 'TNTSearchEngine';
        $configValue  = $this->module->getSystemSetting('search-engine-config') ?? '{}';
        $tempFolderPath = $this->getTempFolder();

        $config = new SearchEngineConfig($providerName);
        $config->settings["config"] = json_decode($configValue, true);
        $config->settings["temp_folder_path"] = $tempFolderPath;

        return $config;
    }

    /**
     * Retrieve the filesystem configuration for this external module.
     *
     * Returns a FileSystemConfig instance containing filesystem-related settings
     * used by the module (base paths, storage options, permissions, and other
     * IO-related configuration).
     *
     * @return FileSystemConfig The resolved filesystem configuration.
     */
    public function getFileSystemConfig() : FileSystemConfig {
        $tempFolder = $this->getTempFolder();

        return new FileSystemConfig($tempFolder);
    }

    /**
     * Retrieves the logging configuration for the external module.
     *
     * @return LoggingConfig The configuration object containing logging settings.
     */
    public function getLoggingConfig(): LoggingConfig {
        $logLevel = $this->module->getSystemSetting('log-level') ?? 0;
        $tempFolder = $this->getTempFolder();
        $prefix = $this->getPrefix();

        $handlerConfigs = [
            new LoggingHandlerConfig(
                level: $logLevel,
                stream: 'php://memory',
                channels: []
            ),
            new LoggingHandlerConfig(
                level: $logLevel,
                stream: $tempFolder . DIRECTORY_SEPARATOR . $prefix . '.ndjson',
                channels: [LoggingConfig::DEFAULT_CHANNEL]
            )
        ];

        $config = new LoggingConfig(handlers: $handlerConfigs);
        
        return $config;
    }


    /**
     * Retrieves the document repository configuration.
     *
     * @return DocumentRepositoryConfig The configuration object for the document repository
     */
    public function getDocumentRepositoryConfig(): DocumentRepositoryConfig {
        $tempFolder = $this->getTempFolder();
        $dsn = "sqlite:" . $tempFolder . DIRECTORY_SEPARATOR . "documents.sqlite";

        return new DocumentRepositoryConfig($tempFolder, $dsn);
    }

    /**
     * Retrieves the cart configuration for the external module.
     *
     * @return CartConfig The cart configuration object containing settings and properties
     *                    for managing shopping cart functionality within the external module.
     */
    public function getCartConfig() : CartConfig {
        return new CartConfig();
    }

    /**
     * Retrieves the scheduler configuration for the external module.
     *
     * @return SchedulerConfig The scheduler configuration object containing scheduling settings
     *                         and parameters for the external module.
     */
    public function getSchedulerConfig() : SchedulerConfig {
        // Get the autorebuild settings
		$enabledSetting = $this->module->getSystemSetting('autorebuild-enabled') ?? 'disabled';
		$enabled = ($enabledSetting === 'enabled') ? true : false;

		// Get the autorebuild pattern
		$pattern = $this->module->getSystemSetting('autorebuild-pattern') ?? '';

        return new SchedulerConfig($enabled, $pattern);
    }

    /**
     * Retrieves the API keys configuration.
     *
     * @return array An associative array containing API keys and their configurations
     */
	public function getApiKeys(): array {
		$apiKeys = [];

		$keys   = $this->module->getSystemSetting("api-key");
        $names  = $this->module->getSystemSetting("api-name");

		foreach ($keys as $index => $key) {
			if (strlen($key) > 0) {
				$apiKeys[$key] = $names[$index] ?? "Unnamed Key";
			}
		}

		return $apiKeys;
	}

    /**
     * Retrieves the path to the temporary folder used by the module.
     *
     * @return string The absolute path to the temporary folder.
     */
    private function getTempFolder(): string {
        $tempFolder = $this->module->getSystemSetting("temp-folder") ?? null;
        switch($tempFolder){
            case 'custom' :
                $customFolderPath = $this->module->getSystemSetting("custom-temp-folder");
                $tempFolderPath = realpath($customFolderPath);
                break;
            case 'system':
                $tempFolderPath = sys_get_temp_dir();
                break;
            case 'redcap':
            default:
                $tempFolderPath = constant("APP_PATH_TEMP");
                break;
        }
        
        if (!is_dir($tempFolderPath)) {
            throw new \Exception("Temp folder ($tempFolderPath) is not a directory. See system-level module configuration.");
        }                

        return $tempFolderPath . DIRECTORY_SEPARATOR . $this->getPrefix(); 
    }

    /**
     * Retrieves the prefix string used for configuration.
     *
     * @return string The configuration prefix.
     */
    private function getPrefix(): string {
        return $this->module->getModuleInfo()["prefix"];
    }
}