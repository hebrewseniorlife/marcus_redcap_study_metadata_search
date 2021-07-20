<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

$entity = $request->get("entity", "");

switch($entity){
    case 'cart':
        $controller = new Controllers\CartController($module);
        $response = $controller->handle($request, $response);
        break;        
    case 'engine':
        $controller = new Controllers\SearchEngineController($module);
        $response = $controller->handle($request, $response);
        break;    
    case 'control-center':
        $controller = new Controllers\ControlCenterController($module);
        $response = $controller->handle($request, $response);
        break;               
    case 'project':
        $controller = new Controllers\ProjectController($module);
        $response = $controller->handle($request, $response);
        break;                
    default:
        $response->setContent("Unsupported API action. Please try again.");
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        break;
}

$scope = $response->headers->get(Controllers\AppController::REDCAP_SCOPE_HEADER);
switch($scope)
{
    case 'project':
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
        echo $response->getContent();
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';        
        break;
    case 'control-center':
        require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
        echo $response->getContent();
        require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php';
        break;
    default:
        $response->prepare($request);
        $response->send();
        break;
}

