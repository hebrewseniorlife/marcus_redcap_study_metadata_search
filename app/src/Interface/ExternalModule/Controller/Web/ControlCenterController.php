<?php

namespace Interface\ExternalModule\Controller\Web;

use Marcus\StudyMetadataSearch\ExternalModule\ExternalModule;
use Psr\Log\LoggerInterface;
use Infrastructure\Logging\LoggerHelper;
use Infrastructure\Logging\JsonLogReader as LogReader;
use Application\Service\Project\ProjectService;
use Application\Service\Search\SearchEngineService;
use Application\Service\Scheduler\SchedulerService;
use Interface\ExternalModule\Configuration\ExternalModuleConfigProvider;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpFoundation\JsonResponse as JsonResponse;

/**
 * ControlCenterController
 */
class ControlCenterController extends AbstractWebController {    
    
    protected SearchEngineService $searchService;
    protected ProjectService $projectService;
    protected SchedulerService $schedulerService;


    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(
        LoggerInterface $logger, 
        ExternalModule $module, 
        ProjectService $projectService, 
        SearchEngineService $searchService,
        SchedulerService $schedulerService)
    {
        parent::__construct($logger, $module);

        $this->projectService = $projectService;
        $this->searchService = $searchService;
        $this->schedulerService = $schedulerService;
    }
    
    /**
     * handle
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function handle(Request $request, Response $response) : Response {
        switch($request->get("action")){
            case 'populate-project':
                return $this->populateProject($request, $response);
            break;
            case 'index-project':
                return $this->indexProject($request, $response);
            break;
            case 'create-index':
                return $this->createIndex($request, $response);
            break;            
            case 'purge':
                return $this->purge($request, $response);
            break;
            case 'view':
            default:
                return $this->view($request, $response);
            break;                
        }
    }

    /**
     * Retrieves log entries from the system.
     *
     * @return array An array containing log entries
     */
    private function getLogEntries() : array {
        $provider = new ExternalModuleConfigProvider($this->module);
        $logging  = $provider->getLoggingConfig();

        $logEntries = [];
        foreach ($logging->handlers as $handler) {
            if (is_file($handler->stream)){
                $reader = new LogReader($handler->stream);
                $logEntries = array_merge($logEntries, $reader->since('2 hours'));
            }
        }

        return $logEntries;
    }
    
    /**
     * view
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function view(Request $request, Response $response) : Response { 
        $projects = $this->projectService->getProjects();

        $schedulerConfig = $this->schedulerService->getConfig();

        $cron               = []; // $this->cronService->getDetails();
        $cron["logs"]       = $this->getLogEntries();
        $cron["enabled"]    = ($schedulerConfig->enabled) ? "enabled" : "disabled";
        if ($schedulerConfig->enabled)
        {
            $cron["schedule"] = []; // $this->cronService->getSchedule($cron["last_start_time"], $this->module->getSystemSetting("autorebuild-pattern"));
        }

        $context = $this->createContext("System View", [
            "engine"     => $this->searchService->getSearchEngine()->getConfig(),
            "projects"   => $projects,
            "stats"      => $this->searchService->getStats(),
            "cron"       => $cron,
            "paths"      => array(
                "purge"  => $this->module->getUrl('control-center.php')."&action=purge",
                "create_index"  => $this->module->getUrl('control-center.php')."&action=create-index",
                "index_project" => $this->module->getUrl('control-center.php')."&action=index-project",
                "populate_project" => $this->module->getUrl('control-center.php')."&action=populate-project"
            )
        ]);
        
        $content = $this->template->render("@control-center/view.twig", $context);

        $response = new Response(
            $content,
            Response::HTTP_OK,
            [self::REDCAP_SCOPE_HEADER => 'control-center']
        );

        return $response;
    }

    /**
     * populateProject
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function populateProject(Request $request, Response $response) : Response { 
        $project_id = $request->get("project_id", -1);
        if ($project_id == -1){
            return new JsonResponse(["message" => "No project ID provided."], 
                Response::HTTP_BAD_REQUEST);       
        }

        $this->logger->info("Manual populate documents requested from Control Center.");

        $project = $this->projectService->getProject($project_id, true);

        if ($project === null){
            return new JsonResponse(["message" => "Project {$project_id} does not exist."], 
                Response::HTTP_BAD_REQUEST);       
        }

        if ($project->enabled !== true){
            return new JsonResponse(["message" => "Project {$project_id} is disabled. Cannot populate documents."], 
                Response::HTTP_BAD_REQUEST);
        }        

        if (count($project->documents) == 0){
            return new JsonResponse(["message" => "No documents found for project {$project_id}."], 
                Response::HTTP_BAD_REQUEST);       
        }

        $this->searchService->pupulateDocuments($project->documents);

        $log = LoggerHelper::getStreamContents($this->logger);
        
        return new JsonResponse([
            "message" => "Document repository for project {$project_id} has been updated.",
            "log" => $log
        ]);       
    }

    /**
     * indexProject
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function indexProject(Request $request, Response $response) : Response { 
        $project_id = $request->get("project_id", -1);
        if ($project_id == -1){
            return new JsonResponse(["message" => "No project ID provided."], 
                Response::HTTP_BAD_REQUEST);       
        }

        $this->logger->info("Manual index documents requested from Control Center.");

        $project = $this->projectService->getProject($project_id, false);

        if ($project === null){
            return new JsonResponse(["message" => "Project {$project_id} does not exist."], 
                Response::HTTP_BAD_REQUEST);       
        }
        
        if ($project->enabled !== true){
            return new JsonResponse(["message" => "Project {$project_id} is disabled. Cannot index documents."], 
                Response::HTTP_BAD_REQUEST);
        }

        $documents = $this->searchService->getDocumentsByProject($project_id);

        if (count($documents) == 0){
            return new JsonResponse(["message" => "No documents found for project {$project_id}."], 
                Response::HTTP_BAD_REQUEST);       
        }

        $this->searchService->indexDocuments($documents);
        $log = LoggerHelper::getStreamContents($this->logger);
        
        return new JsonResponse([
            "message" => "Search index for project {$project_id} has been updated.",
            "log" => $log
        ]);       
    }

    /**
     * purge
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function createIndex(Request $request, Response $response) : Response { 
        $this->logger->info("Manual create index from Control Center.");
        $this->searchService->createIndex();
        
        $log = LoggerHelper::getStreamContents($this->logger);

        $context = $this->createContext("System Purge (All)", [
            "engine"     => $this->searchService->getSearchEngine()->getConfig(),
            "projects"   => [],
            "stats"      => $this->searchService->getStats(),
            "log"        => $log,
            "paths"      => array(
                "view"  => $this->module->getUrl('control-center.php')."&action=view"
            )
        ]);
        $content = $this->template->render("@control-center/create-index.twig", $context);

        $response = new Response(
            $content,
            Response::HTTP_OK,
            [self::REDCAP_SCOPE_HEADER => 'control-center']
        );

        return $response;

    }        

    /**
     * purge
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function purge(Request $request, Response $response) : Response { 
        $this->logger->info("Manual purge requested from Control Center.");
        $this->searchService->purgeAll();
        
        $log = LoggerHelper::getStreamContents($this->logger);

        $context = $this->createContext("System Purge (All)", [
            "engine"     => $this->searchService->getSearchEngine()->getConfig(),
            "projects"   => [],
            "stats"      => $this->searchService->getStats(),
            "log"        => $log,
            "paths"      => array(
                "view"  => $this->module->getUrl('control-center.php')."&action=view"
            )
        ]);
        $content = $this->template->render("@control-center/purge.twig", $context);

        $response = new Response(
            $content,
            Response::HTTP_OK,
            [self::REDCAP_SCOPE_HEADER => 'control-center']
        );

        return $response;

    }    
}
