<?php

namespace Controllers;

use ProjectService;
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
        return $this->view($request, $response);
    }
    
    /**
     * view
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function view(Request $request, Response $response) : Response { 

        $service    = new ProjectService($this->module);
        $projects   = $service->getProjects();

        $context = $this->createContext("System View", [
            "search_provider"   => "PhpSearchEngine",
            "app_temp_path"     => APP_PATH_TEMP,
            "projects"          => $projects
        ]);
        
        $content = $this->template->render("system-view.twig", $context);

        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}