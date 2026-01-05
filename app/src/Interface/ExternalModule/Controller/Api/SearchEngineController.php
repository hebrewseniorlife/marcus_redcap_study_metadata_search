<?php

namespace Interface\ExternalModule\Controller\Api;

use Marcus\StudyMetadataSearch\ExternalModule\ExternalModule;
use Application\Services\Search\SearchEngineService;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpFoundation\JsonResponse as JsonResponse;
use Psr\Log\LoggerInterface;

class SearchEngineController extends AbstractWebController{       
    /**
     * searchEngine
     *
     * @var SearchEngineService
     */
    protected $searchEngine; 

     /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(LoggerInterface $logger, ExternalModule $module)
    {
        parent::__construct($module, $logger);

        $this->searchEngine = new SearchEngineService($this->module, $logger);
    }

    /**
     * handle
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function handle(Request $request, Response $response) : Response {
        $action = $request->get("action", "");
        switch($action){
            case '': // No action provided...
            case 'search':
                return $this->search($request, $response);
                break;     
            case 'search-by':
                return $this->searchBy($request, $response);
                break; 
            default: // An unknown action is provided
                return new JsonResponse(["message" => "Action specified ({$action}) not supported."], 
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
        $results = [];
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
            $results = $this->searchEngine->searchBy($field, $value);
        }

        return new JsonResponse([
            "message" => $message
            , "results" => $results
            , "search" => [
                "term" => ""
                , "field" => $field
                , "value" => $value
            ]
        ]);       
    }
}