<?php

namespace Controllers;

use SearchEngineService;
use CartService;
use Controllers\AppController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

/**
 * ProjectController
 */
class ProjectController extends AppController {     
    /**
     * cart
     *
     * @var CartService
     */
    protected $cart;    

    /**
     * search
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
    function __construct(object $module)
    {
        parent::__construct($module);
        
        $this->cart         = new CartService($this->module);
        $this->searchEngine = new SearchEngineService($this->module);
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
            case 'search-by': 
                return $this->searchBy($request, $response);
                break;
            case 'search':
            default:
                return $this->search($request, $response);
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
    function search(Request $request, Response $response) : Response { 
        $term = $request->get("term", "");
        $results = [];

        if (strlen($term) > 0){
            $results = $this->searchEngine->search($term);
        }

        $context = $this->createContext("Search", [
            "results" => $results,
            "search" => [
                    "term" => $term
                    , "field" => ""
                    , "value" => ""
            ],
            "cart" => array (
                "documents" => $this->cart->getAll()
            )
        ]);
        
        $content = $this->template->render("@project/view.twig", $context);

        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_OK);        
                
        return $response;
    }

    /**
     * view
     *
     * @param  Request $request
     * @param  Response $response
     * @return Response
     */
    function searchBy(Request $request, Response $response) : Response { 
        $fields = ['form_name', 'field_type', 'project_id'];

        $field  = $request->get("field", "");
        $value  = $request->get("value", "");  
        $results = [];
        
        if (array_search($field, $fields) >= 0 && strlen($value) > 0){
            $results = $this->searchEngine->searchBy($field, $value);
        }

        $context = $this->createContext("Search", [
            "results" => $results,
            "search" => [
                    "term" => ""
                    , "field" => $field
                    , "value" => $value
            ],
            "cart" => array (
                "documents" => $this->cart->getAll()
            )
        ]);
        
        $content = $this->template->render("@project/view.twig", $context);

        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_OK);        
                
        return $response;
    }
}