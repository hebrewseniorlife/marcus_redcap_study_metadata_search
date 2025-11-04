<?php

class SettingsHelper {        
    const MODULE_NAME = 'marcus_redcap_study_metadata_search';

    /**
     * getTempFolderPath
     *
     * @param  mixed $module
     * @return string
     */
    static function getTempFolderPath($module = null, $includeModuleName = true) : ?string{
        
        $tempFolder = $module?->getSystemSetting("temp-folder") ?? null;
        switch($tempFolder){
            case 'custom' :
                $customFolderPath   = $module->getSystemSetting("custom-temp-folder");
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

        return $includeModuleName ? $tempFolderPath . DIRECTORY_SEPARATOR . self::MODULE_NAME : $tempFolderPath;         
    }    

    /**
     * getSystemSetting
     *
     * @param  Module $module
     * @param  string $settingName
     * @param  mixed $defaultValue
     * @return mixed
     */ 
    static function getSystemSetting($module, string $settingName, $defaultValue = null) {
        $settingsValue = $module?->getSystemSetting($settingName) ?? null;
        if ($settingsValue === null) {
            return $defaultValue;
        }
        return $settingsValue;
    }
}