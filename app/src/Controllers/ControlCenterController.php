<?php

namespace Controllers;

use ProjectService;
use SearchEngineService;
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
            case 'reindex':
                return $this->reindex($request, $response);
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
        $cron["enabled"]    = $this->module->getSystemSetting("cron-enabled");
        if ($cron["enabled"] === "enabled")
        {
            $cron["schedule"] = $cronService->getSchedule($cron["last_start_time"], $this->module->getSystemSetting("cron-pattern"));
        }

        $context = $this->createContext("System View", [
            "engine"     => $searchService->getSearchEngineSettings(),
            "projects"   => $projects,
            "stats"      => $searchService->getStats(),
            "cron"       => $cron,
            "paths"      => array(
                "reindex"  => $this->module->getUrl('index.php')."&entity=control-center&action=reindex"
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

    function reindex(Request $request, Response $response) : Response { 
        $searchService = new SearchEngineService($this->module, $this->logger);
        $searchService->destroy();

        $projectService = new ProjectService($this->module, $this->logger);

        $projects = $projectService->getProjects();
        $searchService->updateAll($projects);

        $handler = $this->logger->popHandler();
        $log = stream_get_contents($handler->getStream(), -1, 0);

        $context = $this->createContext("System Reindex", [
            "engine"     => $searchService->getSearchEngineSettings(),
            "projects"   => $projects,
            "stats"      => $searchService->getStats(),
            "log"        => $log,
            "paths"      => array(
                "view"  => $this->module->getUrl('index.php')."&entity=control-center&action=view"
            )
        ]);
        $content = $this->template->render("@control-center/reindex.twig", $context);

        $response = new Response(
            $content,
            Response::HTTP_OK,
            [self::REDCAP_SCOPE_HEADER => 'control-center']
        );

        return $response;

    }
}