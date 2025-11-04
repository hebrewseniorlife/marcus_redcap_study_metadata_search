<?php

namespace Controllers;

use ProjectService;
use SearchEngineService;
use SearchEngineFactory;
use CronService;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

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
            case 'populate':
                return $this->populate($request, $response);      
            break;      
            case 'index':
                return $this->index($request, $response);
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
                "index"  => $this->module->getUrl('control-center.php')."&action=index",
                "purge"  => $this->module->getUrl('control-center.php')."&action=purge",
                "populate"  => $this->module->getUrl('control-center.php')."&action=populate"
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

    function populate(Request $request, Response $response) : Response { 
        $this->logger->info("Manual populate requested from Control Center.");
        
        $projectService = new ProjectService($this->module, $this->logger);
        $projects = $projectService->getProjects();

        $searchService = new SearchEngineService($this->module, $this->logger);
        $searchService->populateProjects($projects);
        
        $log = \Logging\Log::getStreamContents();

        $context = $this->createContext("System Poluate", [
            "engine"     => $searchService->getProvider(),
            "projects"   => $projects,
            "stats"      => $searchService->getStats(),
            "log"        => $log,
            "paths"      => array(
                "view"  => $this->module->getUrl('control-center.php')."&action=view"
            )
        ]);
        $content = $this->template->render("@control-center/index.twig", $context);

        $response = new Response(
            $content,
            Response::HTTP_OK,
            [self::REDCAP_SCOPE_HEADER => 'control-center']
        );

        return $response;
    }

    function index(Request $request, Response $response) : Response { 
        $this->logger->info("Manual index requested from Control Center.");

        $searchService = new SearchEngineService($this->module, $this->logger);
        $documents = $searchService->getAllDocuments();
        $searchService->indexDocuments($documents);
        
        $log = \Logging\Log::getStreamContents();

        $context = $this->createContext("System Index", [
            "engine"     => $searchService->getProvider(),
            "projects"   => [],
            "stats"      => $searchService->getStats(),
            "log"        => $log,
            "paths"      => array(
                "view"  => $this->module->getUrl('control-center.php')."&action=view"
            )
        ]);
        $content = $this->template->render("@control-center/index.twig", $context);

        $response = new Response(
            $content,
            Response::HTTP_OK,
            [self::REDCAP_SCOPE_HEADER => 'control-center']
        );

        return $response;

    }

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
