<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

$controller = new Controllers\ControlCenterController($module);
$response = $controller->handle($request, $response);

require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
echo $response->getContent();
require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php';


