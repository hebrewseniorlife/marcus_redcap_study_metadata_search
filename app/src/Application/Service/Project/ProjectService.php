<?php

namespace Application\Service\Project;

use Psr\Log\LoggerInterface;
use Domain\Document\Document;
use Domain\Project\Project;
use Domain\Project\Contract\ProjectRepository;
use Application\Service\Project\ProjectListConfig;
use Application\Service\Project\ProjectConfig;
use function Stringy\create as s;

/**s
 * ProjectService
 */
class ProjectService {         

    /**
     * logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * projectListConfig
     *
     * @var ProjectListConfig
     */
    protected ProjectListConfig $projectListConfig;
    
    /**
     * projectRepository
     *
     * @var ProjectRepository
     */
    protected ProjectRepository $projectRepository;

    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(LoggerInterface $logger, ProjectListConfig $projectListConfig, ProjectRepository $projectRepository)
    {
        $this->logger = $logger;
        $this->projectListConfig = $projectListConfig;
        $this->projectRepository = $projectRepository;
    }
    
    /**
     * getProjects
     *
     * @param  bool $includChildren
     * @return array
     */
    function getProjects(bool $includChildren = true) : array {
        $projectConfigs = $this->projectListConfig->getProjectConfigs();

        $projects = [];
        foreach($projectConfigs as $projectConfig){
            array_push($projects, $this->createProject($projectConfig->pid, $includChildren)); 
        }

        return $projects;
    }

    /**
     * getProject
     *
     * @param  mixed $pid
     * @param  bool $includChildren
     * @return Project
     */
    function getProject(int $pid, bool $includChildren = true) : Project {
        return $this->createProject($pid, $includChildren);
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
     * @param  bool $includeChildren
     * @return Project
     */
    function createProject(int $pid, bool $includeChildren = true) : Project{
        $project        = $this->projectRepository->getProject($pid);
        $projectConfig  = $this->projectListConfig->getProjectConfig($pid);
        
        $isEnabled  = $projectConfig ? $projectConfig->indexEnabled : false;
        $denyList   = $projectConfig ? $projectConfig->getFormDenyListAsArray() : [];

        $p = new Project();
        $p->project_id      = $pid;
        $p->title           = $project['title'] ?? "Unknown Title";
        $p->enabled         = $isEnabled;
        $p->form_denylist   = $denyList;

        if ($includeChildren && $isEnabled){
            $projectDetails = $this->projectRepository->getDetails($pid);

            $p->documents = $this->getProjectDocuments($p);
            $p->lead      = $this->getLead($projectDetails);
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

        $metadata = $this->projectRepository->getDataDictionary($project->project_id);

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