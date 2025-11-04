<?php

namespace Controllers;

use CartService;
use DocumentHelper;
use Controllers\ApiController;
use SearchEngineService;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpFoundation\JsonResponse as JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse as BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils as HeaderUtils;

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
    function __construct(object $module, $logger = null)
    {
        parent::__construct($module, $logger);

        $this->cart = new CartService($this->module, $logger);
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
            case 'reorder':
                return $this->reorder($request, $response);
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
        $searchEngine = new SearchEngineService($this->module, $this->logger);

        $documents = $searchEngine->getDocuments($this->cart->getAll());
        
        DocumentHelper::setFieldOrder($documents);
        DocumentHelper::flattenAll($documents);

        // Fix problem with non-UTF8 characters found in REDCap
        return new Response(json_encode([
            "message" => "",
            "documents" => $documents
        ], JSON_UNESCAPED_UNICODE));

        // return new JsonResponse([
        //     "message" => "",
        //     "documents" => $documents]);  
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
     * reorder
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function reorder(Request $request, Response $response) : Response { 
        $documents  = $request->get("document", []);
        $count      = $this->cart->reorder($documents);    

        return new JsonResponse([
            "message" => "The cart order has been saved."
            ,"count" => $count
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
        $searchEngine   = new SearchEngineService($this->module, $this->logger);
        $documents      = $searchEngine->getDocuments($this->cart->getAll());

        DocumentHelper::setFieldOrder($documents);

        $exportDate     = date("Ymd");

        $format = $request->get("format");
        switch($format){
            case 'csv':
                DocumentHelper::flattenAll($documents);
                
                $contentType    = 'text/csv';
                $content        = DocumentHelper::writeToCsv($documents);
                $filename       = 'study_metdata_cart_'.$exportDate.'.csv';
                break;
            case 'json':
                $contentType    = 'application/json';
                $content        = json_encode($documents);
                $filename       = 'study_metdata_cart_'.$exportDate.'.json';
                break;
            case 'zip':
                $contentType    = 'application/zip';
                $content        = null;
                $filename       = 'study_metdata_cart_'.$exportDate.'.zip';

                $zipFilePath    = DocumentHelper::writeMetadataToZip($documents);
                $response       = new BinaryFileResponse($zipFilePath);
                break;
            case 'metadata':
            default:
                $contentType    = 'text/csv';
                $content        = DocumentHelper::writeMetadataToCsv($documents);
                $filename       = 'data_dictionary_'.$exportDate.'.csv';
                break;
        }
        
        // Prepar the response and return it to the caller...
        $response->setContent($content);
        $response->headers->set('Content-Type', $contentType);
        
        $download = strtolower($request->get("download", "true"));
        if ($download === "true" || $download === "t" || $download === "1")
        {
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $filename
            );
            $response->headers->set('Content-Disposition', $disposition);
        }

        $response->setStatusCode(Response::HTTP_OK);

        return $response;  
    }
}