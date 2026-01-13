<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Infrastructure\Logging\LoggerFactory;
use Application\Service\ServiceFactory;
use Interface\ExternalModule\Configuration\ExternalModuleConfigProvider;
use Interface\ExternalModule\Controller\Web\ControlCenterController;

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
$searchService      = $serviceFactory->createSearchEngineService($provider->getDocumentRepositoryConfig(), $provider->getSearchEngineConfig());
$projectService     = $serviceFactory->createProjectService($module);
$schedulerService   = $serviceFactory->createSchedulerService($provider->getSchedulerConfig());

// Create the controller and handle the request
$controller = new ControlCenterController($logger, $module, $projectService, $searchService, $schedulerService);
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





