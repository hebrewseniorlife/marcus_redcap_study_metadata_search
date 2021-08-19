<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

$controller = new Controllers\CartController($module);
$response = $controller->handle($request, $response);

$response->prepare($request);
$response->send();
