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
            case 'search':  
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
        $phrase = $request->get("search", "");
        $result = [];

        if (count($phrase) > 0){
            $result = $this->searchEngine->search($phrase);
        }

        $context = $this->createContext("Search", [
            "results" => $result,
            "search" => $phrase,
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