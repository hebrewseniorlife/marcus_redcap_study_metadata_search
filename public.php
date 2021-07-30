<?php

require_once(__DIR__.'/app/bootstrap.php');

use Controllers\ApiController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

// Instantiate a new logger and allow it to log to REDCAP database
$logger = \Logging\Log::getLogger();
$logger->pushHandler(new \Logging\ExternalModuleLogHandler($module));

// Get the named-key as provided to the API URL
$namedKey = ApiController::getModuleNamedKey($module, $request->get('key', ''));

if ($namedKey !== null)
{
    $logger->notice("API Key ({$namedKey['name']}) used.", [ "api-key" => $namedKey['key'] ]);

    $entity = $request->query->get('entity', '');
    switch($entity) 
    {
        case '':
        case 'search':
            $controller = new \Controllers\SearchEngineController($module, $logger);
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
