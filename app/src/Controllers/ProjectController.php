<?php

namespace Controllers;

use SearchEngineService;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

/**
 * ProjectController
 */
class ProjectController extends AppController {    
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
            case 'search':
                return $this->search($request, $response);
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
        $context = $this->createContext("Search", []);
        $content = $this->template->render("@project/view.twig", $context);

        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_OK);        
        return $response;
    }

    /**
     * search
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function search(Request $request, Response $response) : Response { 

        $searchService    = new SearchEngineService($this->module);
        $results = $searchService->search($request->get("search"));

        // echo json_encode($results);
        // exit;
        
        $context = $this->createContext("Search", [
            "results" => $results
        ]);
        $content = $this->template->render("@project/view.twig", $context);

        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_OK);        
                
        return $response;
    }
}