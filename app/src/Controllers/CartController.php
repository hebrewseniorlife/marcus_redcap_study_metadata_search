<?php

namespace Controllers;

use CartService;
use DocumentHelper;
use Controllers\ApiController;
use SearchEngineService;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpFoundation\JsonResponse as JsonResponse;

class CartController extends ApiController{    
    /**
     * cart
     *
     * @var CartService
     */
    protected $cart;

     /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(object $module)
    {
        parent::__construct($module);

        $this->cart = new CartService($module);
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
            case 'add':
                return $this->add($request, $response);
                break;     
            case 'remove':
                return $this->remove($request, $response);
                break;                  
            case 'clear':
                return $this->clear($request, $response);
                break; 
            case 'getall':
                return $this->getall($request, $response);
                break; 
            case 'export':    
                return $this->export($request, $response);
                break;
            default:
                return new JsonResponse(["message" => "Action not supported."], 
                    Response::HTTP_BAD_REQUEST);
                break;                
        }
    }

    /**
     * add
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function add(Request $request, Response $response) : Response { 
        $documents  = $request->get("document", []);
        $count      = $this->cart->add($documents);    

        return new JsonResponse([
            "message" => "Document(s) added to the cart."
            ,"count" => $count
            ]);         
    }
        
    /**
     * remove
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function remove(Request $request, Response $response) : Response { 
        $documents  = $request->get("document", []);
        $count      = $this->cart->remove($documents);    

        return new JsonResponse([
            "message" => "Document(s) removed from the cart."
            ,"count" => $count
            ]);         
    }

    /**
     * getAll
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function getAll(Request $request, Response $response) : Response {
        $searchEngine = new SearchEngineService($this->module);
        $documents = $searchEngine->getDocuments($this->cart->getAll());

        $documents = array_map('DocumentHelper::flatten', $documents);

        return new JsonResponse([
            "message" => "",
            "documents" => $documents]);  
    }
    
    /**
     * clear
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function clear(Request $request, Response $response) : Response { 
        $this->cart->clear();

        return new JsonResponse([
            "message" => "Cart has been cleared.",
            "count" => 0
        ]);         

    }
    
    /**
     * export
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function export(Request $request, Response $response) : Response {       
        $searchEngine = new SearchEngineService($this->module);
        $documents = $searchEngine->getDocuments($this->cart->getAll());

        $content = DocumentHelper::writeMetadataToCsv($documents);

        // Prepar the response and return it to the caller...
        $response->setContent($content);
        $response->headers->set('Content-Type', 'text/csv');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;  
    }
}