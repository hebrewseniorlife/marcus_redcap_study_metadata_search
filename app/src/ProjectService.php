<?php

use Models\Document as Document;
use Models\Project as Project;
use function Stringy\create as s;

/**
 * ProjectService
 */
class ProjectService extends AbstractService {          
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
     * getDetails
     *
     * @param  mixed $pid
     * @return array
     */
    function getDetails(int $pid) : array {
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
     * getLead
     *
     * @param  array $details
     * @return array
     */
    function getLead(array $details = []) : array {
        $lead = [
            "email" => "",
            "formatted" => "NA",
            "lastname" => "",
            "firstname" => ""
        ];
        
        if (strlen($details["project_pi_email"]) > 0)
        {
            $lead["email"]      = $details["project_pi_email"];
            $lead["formatted"]  = $details["project_pi_email"];

            if (strlen($details["project_pi_lastname"]) > 0)
            {
                $lead["lastname"]   = $details["project_pi_lastname"];
                $lead["formatted"]  = $details["project_pi_lastname"];

                if (strlen($details["project_pi_firstname"]) > 0)
                {
                    $lead["firstname"] = $details["project_pi_firstname"];
                    $lead["formatted"] = $details["project_pi_lastname"].", ".$details["project_pi_firstname"];
                }
            }
        }

        return $lead;
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
        $isEnabled  = filter_var($this->module->getProjectSetting("index-enabled", $pid), FILTER_VALIDATE_BOOLEAN);
        $denyList   = $this->getFormDenyList($pid);

        $p = new Project();
        $p->project_id      = $pid;
        $p->title           = $project->getTitle();
        $p->enabled         = $isEnabled;
        $p->form_denylist   = $denyList;

        if ($includChildren && $isEnabled){
            $p->documents = $this->getProjectDocuments($p);
            $p->lead      = $this->getLead($this->getDetails($pid));
            $p->forms     = $this->getUniqueForms($p->documents);
        }

        return $p;
    }

    
    /**
     * getProjectDocuments
     *
     * @param  Project $project
     * @return array
     */
    function getProjectDocuments(Project $project) : array {
        $documents = [];

        $metadata = REDCap::getDataDictionary($project->project_id, "array");

        foreach($metadata as $field){
            $denials = array_filter($project->form_denylist, function ($value) use ($field) {
                return fnmatch($value, $field["form_name"]);
            });

            if (count($denials) > 0) continue;
            
            $document = $this->createDocument(
                $project->project_id,
                $project->title, 
                $field);
            array_push($documents, $document);
        }

        return $documents;
    }
    
    /**
     * getExcludedForms
     *
     * @param  mixed $pid
     * @return array
     */
    function getFormDenyList(int $pid) : array {
        $wildcardList = [];

        $denylist = $this->module->getProjectSetting("forms-denylist", $pid);
        if (strlen($denylist) > 0){
            $wildcardList = preg_split('/\s*,\s*/', trim($denylist)); 
        }

        return $wildcardList;
    }
        
    /**
     * getUniqueForms
     *
     * @param  mixed $documents
     * @return array
     */
    function getUniqueForms(array $documents) : array
    {
        $forms = [];

        foreach($documents as $document)
        {
            $forms[$document->form_name] = [
                    "name"  => $document->form_name,
                    "title" => $document->form_title
            ];
        }

        return array_values($forms);
    }

    /**
     * createDocument
     *
     * @param  int $pid
     * @param  mixed $field
     * @return Document
     */
    function createDocument(int $pid, string $title, array $metadata) : Document {
        $document = new Document();
        $key = join("__",array($pid, $metadata["form_name"], $metadata["field_name"]));

        $document->key          = $key;
        $document->entity       = "field";
        $document->name         = $metadata["field_name"];
        $document->label        = $metadata["field_label"];
        $document->note         = $metadata["field_note"];

        $document->project_id       = $pid;
        $document->project_title    = $title;

        $document->form_name    = $metadata["form_name"];
        $document->form_title   = (string) s($metadata["form_name"])->humanize()->titleize();
        $document->field_type   = $metadata["field_type"];

        $document->context = $metadata;

        return $document;
    }
}