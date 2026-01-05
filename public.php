<?php

require_once(__DIR__.'/app/bootstrap.php');

use Interface\ExternalModule\Controller\Api\SearhchEngineController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Infrastructure\Logging\LoggerFactory;
use Infrastructure\ExternalModule\Logging\ExternalModuleLogHandler;

// Get the system configuration from the REDCap module
$systemConfig = $module->getSystemConfig();

// Create the request and response objects
$request  = Request::createFromGlobals();
$response = new Response();

// Create the logger
$logger = (new LoggerFactory())->createLogger($systemConfig->logging);
if ($systemConfig->logging->isEnabled())
{
    $logger->pushHandler(new ExternalModuleLogHandler($systemConfig->logging->level, true, $module));  
}

// Get the named-key as provided to the API URL
$namedKey = array_search($systemConfig->apiKeys, $request->get('key', ''));

if ($namedKey !== null)
{
    $logger->notice("API Key ({$namedKey['name']}) used.", [ "api-key" => $namedKey['key'] ]);

    $entity = $request->query->get('entity', '');
    switch($entity) 
    {
        case '':
        case 'search':
            $controller = new SearhchEngineController($logger, $module);
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
