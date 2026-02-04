<?php

namespace Interface\ExternalModule\Project;

use Psr\Log\LoggerInterface;
use Domain\Project\Contract\ProjectRepository;
use \ExternalModules\AbstractExternalModule;
use REDCap as REDCap;

class ExternalModuleProjectRepository implements ProjectRepository
{
    /**
     * @var AbstractExternalModule
     */
    protected AbstractExternalModule $module;
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * __construct
     *
     * @param  LoggerInterface $logger
     * @param  ProjectListConfig $projectListConfig
     * @param  AbstractExternalModule $module
     * @return void
     */
    public function __construct(LoggerInterface $logger, AbstractExternalModule $module)
    {
        $this->logger = $logger;
        $this->module = $module;
    }

    /**
     * getProject
     *
     * @param  int $pid
     * @param  bool $includeChildren
     * @return Project|null
     */
    public function getProject(int $pid): array
    {
        // Get project info from REDCap
        $project = $this->module->getProject($pid);

        return [
            'pid' => $pid, 
            'title' => $project->getTitle()
        ];
    }

    /**
     * getDetails
     *
     * @param  int $pid
     * @return array
     */
    public function getDetails(int $pid): array
    {
        // return \REDCap::getProjectInfo($pid);
        $sql = "select * from redcap_projects where project_id = ?";
		
        $details = [];
        $results = $this->module->query($sql, $pid);
		if ($results && $results->num_rows > 0)
		{
			$details = $results->fetch_assoc();
		}
        return $details;
    }

    /**
     * getDataDictionary
     *
     * @param  int $pid
     * @return array
     */
    public function getDataDictionary(int $pid): array
    {
        $metadata = REDCap::getDataDictionary($pid, "array");
        return $metadata;
    }
}