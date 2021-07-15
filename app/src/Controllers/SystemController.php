<?php

namespace Controllers;

use Models\SearchEngineSettings;
use ProjectService;
use SearchEngineService;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

/**
 * SystemController
 */
class SystemController extends AppController {    
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
        $searchService    = new SearchEngineService($this->module);
        $projectService   = new ProjectService($this->module);
        $projects         = $projectService->getProjects();

        $context = $this->createContext("System View", [
            "engine"     => $searchService->getSearchEngineSettings(),
            "projects"   => $projects,
            "stats"      => $searchService->getStats(),
            "paths"      => array(
                "reindex"  => $this->module->getUrl('index.php')."&action=reindex"
            )
        ]);
        $content = $this->template->render("@system/view.twig", $context);

        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    function reindex(Request $request, Response $response) : Response { 
        $searchService = new SearchEngineService($this->module);
        $searchService->destroy();

        $projectService     = new ProjectService($this->module);

        $projects = $projectService->getProjects();
        $searchService->updateAll($projects);

        $context = $this->createContext("System Reindex", [
            "engine"     => $searchService->getSearchEngineSettings(),
            "projects"   => $projects,
            "stats"      => $searchService->getStats(),
            "paths"      => array(
                "view"  => $this->module->getUrl('index.php')."&action=view"
            )
        ]);
        $content = $this->template->render("@system/reindex.twig", $context);

        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;

    }
}