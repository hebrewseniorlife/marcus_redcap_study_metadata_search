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
            case 'search-by':
                return $this->searchBy($request, $response);
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
        $term = $request->get("term", "");
        $result = [];

        if (strlen($term) > 0){
            $result = $this->searchEngine->search($term);
        }
        
        return new JsonResponse([
            "message" => ""
            , "results" => $result
            , "search" => [
                "term" => $term
            ]
        ]);       
    }

    /**
     * search
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function searchBy(Request $request, Response $response) : Response { 
        $fields = ['form_name', 'field_type', 'project_id'];

        $field  = $request->get("field", "");
        $value  = $request->get("value", "");
        $result = [];
        $message = "";

        if (array_search($field, $fields) === false) 
        {
            $message = "Field supplied was invalid. No search performed.";
        }
        else if (strlen($value) == 0)
        {
            $message = "Field value was not valid. No search performed.";
        }
        else
        {
            $result = $this->searchEngine->searchBy($field, $value);
        }

        return new JsonResponse([
            "message" => $message
            , "results" => $result
            , "search" => [
                "term" => ""
                , "field" => $field
                , "value" => $value
            ]
        ]);       
    }
}