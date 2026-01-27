<?php

namespace Infrastructure\Scheduler;

use Infrastructure\Scheduler\SingleJobConfig;

class SchedulerConfig {
    /**
     * @var SingleJobConfig|null
     */
    public $jobConfig = null;
    
    /**
     * Constructor
     *
     * @param bool $enabled
     * @param string $pattern
     * @param array $crons
     */
    function __construct(        
        public readonly bool $enabled = false,
        public readonly string $pattern = '',
        public readonly string $jobFolderPath = '',
    ){
        $jobKey         = 'single_job';
        $timezone       = 'UTC';
        $stateFilePath  = $this->jobFolderPath . '/' . SingleJobConfig::DEFAULT_STATE_FILENAME;
        $lockFilePath   = $this->jobFolderPath . '/' . SingleJobConfig::DEFAULT_LOCK_FILENAME;

        $this->jobConfig = new SingleJobConfig($jobKey, $pattern, $timezone, $stateFilePath, $lockFilePath);
    }
}