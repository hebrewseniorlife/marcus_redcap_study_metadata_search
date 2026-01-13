<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Application\Service\ServiceFactory;
use Interface\ExternalModule\Configuration\ExternalModuleConfigProvider;
use Interface\ExternalModule\Controller\Api\CartController;
use Infrastructure\Logging\LoggerFactory;

// Get the configuration from the module
$provider = new ExternalModuleConfigProvider($module);

// Create the request and response objects
$request  = Request::createFromGlobals();
$response = new Response();

// Create the logger
$loggerFactory = new LoggerFactory($provider->getLoggingConfig());
$logger = $loggerFactory->createLogger();

// Initialize services
$serviceFactory = new ServiceFactory($logger);
$searchService  = $serviceFactory->createSearchEngineService($provider->getDocumentRepositoryConfig(), $provider->getSearchEngineConfig());
$cartService    = $serviceFactory->createCartService($provider->getCartConfig());

// Create the controller, provide the services and handle the request
$controller = new CartController($logger, $cartService, $searchService);
$response = $controller->handle($request, $response);

// Output the response
$response->prepare($request);
$response->send();
