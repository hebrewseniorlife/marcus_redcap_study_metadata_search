<?php

namespace Controllers;

use Services\ProjectService;
use Services\SearchEngineService;
use SearchEngine\SearchEngineFactory;
use Services\CronService;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpFoundation\JsonResponse as JsonResponse;
/**
 * ControlCenterController
 */
class ControlCenterController extends AppController {    
    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(object $module)
    {
        parent::__construct($module);
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
     * view
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function view(Request $request, Response $response) : Response { 
        $searchService    = new SearchEngineService($this->module, $this->logger);
        
        $projectService   = new ProjectService($this->module, $this->logger);
        $projects         = $projectService->getProjects();

        $cronService = new CronService($this->module, $this->logger);
        $cron               = $cronService->getDetails();
        $cron["logs"]       = $cronService->getLogs();
        $cron["enabled"]    = $this->module->getSystemSetting("autorebuild-enabled");
        if ($cron["enabled"] === "enabled")
        {
            $cron["schedule"] = $cronService->getSchedule($cron["last_start_time"], $this->module->getSystemSetting("autorebuild-pattern"));
        }

        $context = $this->createContext("System View", [
            "engine"     => $searchService->getProvider(),
            "projects"   => $projects,
            "stats"      => $searchService->getStats(),
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

        $projectService = new ProjectService($this->module, $this->logger);
        $project = $projectService->getProject($project_id, true);

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

        $searchService = new SearchEngineService($this->module, $this->logger);
        $searchService->pupulateDocuments($project->documents);

        $log = \Logging\Log::getStreamContents();
        
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

        $projectService = new ProjectService($this->module, $this->logger);
        $project = $projectService->getProject($project_id, false);

        if ($project === null){
            return new JsonResponse(["message" => "Project {$project_id} does not exist."], 
                Response::HTTP_BAD_REQUEST);       
        }
        
        if ($project->enabled !== true){
            return new JsonResponse(["message" => "Project {$project_id} is disabled. Cannot index documents."], 
                Response::HTTP_BAD_REQUEST);
        }

        $searchService = new SearchEngineService($this->module, $this->logger);
        $documents = $searchService->getDocumentsByProject($project_id);

        if (count($documents) == 0){
            return new JsonResponse(["message" => "No documents found for project {$project_id}."], 
                Response::HTTP_BAD_REQUEST);       
        }

        $searchService->indexDocuments($documents);
        $log = \Logging\Log::getStreamContents();
        
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

        $searchService = new SearchEngineService($this->module, $this->logger);
        $searchService->createIndex();
        
        $log = \Logging\Log::getStreamContents();

        $context = $this->createContext("System Purge (All)", [
            "engine"     => $searchService->getProvider(),
            "projects"   => [],
            "stats"      => $searchService->getStats(),
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

        $searchService = new SearchEngineService($this->module, $this->logger);
        $searchService->purgeAll();
        
        $log = \Logging\Log::getStreamContents();

        $context = $this->createContext("System Purge (All)", [
            "engine"     => $searchService->getProvider(),
            "projects"   => [],
            "stats"      => $searchService->getStats(),
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
