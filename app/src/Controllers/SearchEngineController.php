<?php

namespace Controllers;

use SearchEngineService;
use DocumentHelper;
use Controllers\ApiController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpFoundation\JsonResponse as JsonResponse;

class SearchEngineController extends ApiController{       
    /**
     * searchEngine
     *
     * @var SearchService
     */
    protected $searchEngine; 

     /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(object $module)
    {
        parent::__construct($module);

        $this->searchEngine = new SearchEngineService($module);
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
            default:
                return new JsonResponse(["message" => "Action not supported."], 
                    Response::HTTP_BAD_REQUEST);
                break;                
        }
    }
    
    /**
     * search
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function search(Request $request, Response $response) : Response { 
        $phrase = $request->get("phrase", "");
        $result = [];

        if (count($phrase) > 0){
            $result = $this->searchEngine->search($phrase);
        }
        
        return new JsonResponse([
            "message" => ""
            , "results" => $result
            , "phrase" => $phrase
        ]);         
    }
}