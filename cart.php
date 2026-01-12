<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Application\Service\ServiceFactory;
use Interface\ExternalModule\Controller\Api\CartController;
use Infrastructure\Logging\LoggerFactory;

// Get the system configuration from the REDCap module
$systemConfig = $module->getSystemConfig();

// Create the request and response objects
$request  = Request::createFromGlobals();
$response = new Response();

// Create the logger
$loggerFactory = new LoggerFactory($systemConfig->logging);
$logger = $loggerFactory->createLogger();

// Initialize services
$serviceFactory = new ServiceFactory($logger);
$searchService  = $serviceFactory->createSearchEngineService($systemConfig->documentRepository, $systemConfig->searchEngine);
$cartService    = $serviceFactory->createCartService($systemConfig->cart);

// Create the controller, provide the services and handle the request
$controller = new CartController($logger, $cartService, $searchService);
$response = $controller->handle($request, $response);

// Output the response
$response->prepare($request);
$response->send();
