<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

$pid        = $request->query->getInt("pid", -1);
$isProject  = (isset($pid) && $pid > 0);
$entity     = $request->query->get("entity", "cart");

switch($entity){
    case 'cart':
        $controller = new Controllers\CartController($module);
        $response = $controller->handle($request, $response);
        break;        
    case 'engine':
        $controller = new Controllers\SearchEngineController($module);
        $response = $controller->handle($request, $response);
        break;        
    default:
        $response->setContent("Unsupported API action. Please try again.");
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        break;
}


$response->prepare($request);
$response->send();
