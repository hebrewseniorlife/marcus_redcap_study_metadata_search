<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Interface\ExternalModule\Controller\Api\CartController;
use Infrastructure\Logging\LoggerFactory;

// Get the system configuration from the REDCap module
$systemConfig = $module->getSystemConfig();

// Create the request and response objects
$request  = Request::createFromGlobals();
$response = new Response();

// Create the logger
$logger = (new LoggerFactory())->createLogger($systemConfig->logging);

// Create the controller and handle the request
$controller = new CartController($logger, $module);
$response = $controller->handle($request, $response);

// Output the response
$response->prepare($request);
$response->send();
