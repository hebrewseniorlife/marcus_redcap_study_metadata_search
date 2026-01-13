<?php

require_once(__DIR__.'/app/bootstrap.php');

use Application\Service\ServiceFactory;
use Interface\ExternalModule\Configuration\ExternalModuleConfigProvider;
use Interface\ExternalModule\Controller\Api\SearhchEngineController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
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


// Get the named-key as provided to the API URL
$apiKeys  = $provider->getApiKeys(); 
$namedKey = array_search($apiKeys, $request->get('key', ''));

if ($namedKey !== null)
{
    $logger->notice("API Key ({$namedKey['name']}) used.", [ "api-key" => $namedKey['key'] ]);

    $entity = $request->query->get('entity', '');
    switch($entity) 
    {
        case '':
        case 'search':
            $controller = new SearhchEngineController($logger, $searchService);
            $response = $controller->handle($request, $response);
            break; 
        default:
            $response->setContent('Unsupported API entity action. Please try again.');
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            break;
    }
}
else
{
    $response->setContent('API key not specified or not valid.'); 
    $response->setStatusCode(Response::HTTP_BAD_REQUEST);
}


$response->prepare($request);
$response->send();
