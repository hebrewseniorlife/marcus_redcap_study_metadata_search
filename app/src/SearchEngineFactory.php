<?php

use Psr\Log\LoggerInterface;
use SearchEngines\ISearchEngine as ISearchEngine;
use SearchEngines\TNTSearchEngine as TNTSearchEngine;
use Models\SearchEngineProvider as SearchEngineProvider;
use SettingsHelper as SettingsHelper;

class SearchEngineFactory
{
    /**
     * The default search engine provider used by the application.
     * This constant specifies the class name of the search engine to be used
     * when no specific provider is configured.
     */
    const DEFAULT_SEARCH_ENGINE_PROVIDER = 'TNTSearchEngine';

    public static function createSearchEngine(SearchEngineProvider $provider, LoggerInterface $logger) : ? ISearchEngine
    {
        $searchEngine = null;
        $providerName = $provider->name;
        
        $logger->debug("Creating search engine using provider named: {$providerName}.");

        switch($providerName){
            case "TNTSearchEngine":
                $searchEngine = new TNTSearchEngine();
                break;                
            default:
                throw new Exception("Search engine with provider name '{$providerName}' is not supported.");
        }
        
        $searchEngine->initialize($provider, $logger);
        
        return $searchEngine;
    }


    /**
     * Creates and returns an instance of SearchEngineProvider for the given module.
     *
     * @param mixed $module The module for which the search engine provider is to be created.
     * @return SearchEngineProvider The created search engine provider instance.
     */
    static function createProvder($module) : SearchEngineProvider 
    {
        $defaultProvider = SearchEngineFactory::DEFAULT_SEARCH_ENGINE_PROVIDER;

        $searchProviderName = SettingsHelper::getSystemSetting($module, "search-engine-provider", $defaultProvider);
        $tempFolderPath     = SettingsHelper::getTempFolderPath($module);
        $configValue        = SettingsHelper::getSystemSetting($module,"search-engine-config", "{}");

        $provider = new SearchEngineProvider($searchProviderName);
        $provider->settings["temp_folder_path"] = $tempFolderPath;
        $provider->settings["config"] = json_decode($configValue, true);

        return $provider;
    }
}