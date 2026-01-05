<?php

namespace Interface\ExternalModule\Controller\Web;

use Marcus\StudyMetadataSearch\ExternalModule\ExternalModule;
use Psr\Log\LoggerInterface;
use Application\Services\Search\SearchEngineService;
use Application\Services\Cart\CartService;
use Application\Services\Cart\CartConfig;
use Application\Services\Project\ProjectService;
use Domain\Search\SearchEngineResult;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpFoundation\JsonResponse as JsonResponse;

/**
 * ProjectController
 */
class ProjectController extends AbstractWebController {     
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
    function __construct(LoggerInterface $logger, ExternalModule $module)
    {
        parent::__construct($logger, $module);
        
        $this->cart         = new CartService(new CartConfig());
        $this->searchEngine = new SearchEngineService($this->logger, $this->module);
        $this->projectService = new ProjectService($this->module);
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
            case 'list-sources':
                return $this->listSources($request, $response);
                break;
            case 'list-forms':
                return $this->listForms($request, $response);
                break;                
            case 'search-by': 
                return $this->searchBy($request, $response);
                break;
            case 'search':
                return $this->search($request, $response);
                break;
            default:
                return new Response(
                    "Unsupported project action. Please try again.",
                    Response::HTTP_BAD_REQUEST
                );               
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

        $results = new SearchEngineResult();
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

        $response = new Response(
            $content,
            Response::HTTP_OK,
            [self::REDCAP_SCOPE_HEADER => 'project']
        );     
                
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
        $results = new SearchEngineResult();
        
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

        $response = new Response(
            $content,
            Response::HTTP_OK,
            [self::REDCAP_SCOPE_HEADER => 'project']
        );        
                
        return $response;
    }

    /**
     * listSources
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function listSources(Request $request, Response $response) : Response {
        $projects = $this->projectService->getProjects();

        $indexedProjects = [];
        foreach($projects as $project)
        {
            if ($project->enabled === true)
            {
                array_push($indexedProjects, [
                    "title" => $project->title,
                    "document_count" => count($project->documents),
                    "lead" => $project->lead
                ]);
            }
        }

        return new JsonResponse([
            "message" => "",
            "sources" => $indexedProjects]);  
    }

    /**
     * listSources
     *
     * @param  mixed $request
     * @param  mixed $response
     * @return Response
     */
    function listForms(Request $request, Response $response) : Response {
        $projects = $this->projectService->getProjects();

        $indexedProjects = [];
        foreach($projects as $project)
        {
            if ($project->enabled === true)
            {
                array_push($indexedProjects, [
                    "title" => $project->title,
                    "project_id" => $project->project_id,
                    "forms" => $project->forms
                ]);
            }
        }

        return new JsonResponse([
            "message" => "",
            "projects" => $indexedProjects]);  
    }
}