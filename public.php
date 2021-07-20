<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

// $pid        = $request->query->getInt("pid", -1);
// $isProject  = (isset($pid) && $pid > 0);
$entity     = $request->query->get("entity", "");

switch($entity){             
    default:
        $response->setContent("Unsupported API action. Please try again.");
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        break;
}


$response->prepare($request);
$response->send();
