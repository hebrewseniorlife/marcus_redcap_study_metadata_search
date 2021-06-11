<?php

use Models\Project;

/**
 * ProjectService
 */
class ProjectService {        
    /**
     * module
     *
     * @var mixed
     */
    protected $module;
    
    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct($module)
    {
        $this->module = $module;
    }
    

    /**
     * getProjects
     *
     * @param  bool $includChildren
     * @return array
     */
    function getProjects(bool $includChildren = true) : array {
        $pids = $this->module->getProjectsWithModuleEnabled();

        $projects = [];
        foreach($pids as $pid){
            array_push($projects, $this->createProject($pid, $includChildren)); 
        }

        return $projects;
    }
    
    /**
     * createProject
     *
     * @param  int $pid
     * @param  bool $includChildren
     * @return Project
     */
    function createProject(int $pid, bool $includChildren = true) : Project{
        $project    = $this->module->getProject($pid);
        $isEnabled  = $this->module->getProjectSetting("index-enabled", $pid);

        $p = new Project();
        $p->pid       = $pid;
        $p->title     = $project->getTitle();
        $p->enabled   = $isEnabled;

        if ($includChildren){
            $p->documents = $this->getProjectDocuments($pid);
        }

        return $p;
    }


    function getProjectDocuments(int $pid) : array {
        $project    = $this->module->getProject($pid);
        $metadata   = REDCap::getDataDictionary($pid, "array");

        // $forms   = $this->module->query('select distinct form_name from redcap_metadata where project_id = ?', [$pid]);
        
        return $metadata;
    }
}