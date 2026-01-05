<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Infrastructure\Logging\LoggerFactory;
use Infrastructure\ExternalModule\Logging\ExternalModuleLogHandler;
use Interface\ExternalModule\Controller\Web\ControlCenterController;


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

// Create the controller and handle the request
$controller = new ControlCenterController($logger, $module);
$response = $controller->handle($request, $response);

// Output the response
$action = $request->get("action");
switch ($action) {
    case 'populate-project':
    case 'index-project':
        header('Content-Type: application/json');
        echo $response->getContent();
        break;
    default:
        require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
        echo $response->getContent();
        require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php';
        break;
}





