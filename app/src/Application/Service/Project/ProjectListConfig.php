<?php

namespace Application\Service\Project;

class ProjectListConfig
{
    /**
     * @var ProjectConfig[]
     */
    private array $projectConfigs;

    public function __construct(array $projectConfigs = [])
    {
        $this->projectConfigs = $projectConfigs;
    }

    /**
     * Get all project configs
     *
     * @return ProjectConfig[]
     */
    public function getProjectConfigs(): array {
        return $this->projectConfigs;
    }

    /**
     * Get project config by pid
     */
    function getProjectConfig(int $pid) : ?ProjectConfig {
        foreach ($this->projectConfigs as $projectConfig) {
            if ($projectConfig->pid === $pid) {
                return $projectConfig;
            }
        }
        return null;
    }
}
