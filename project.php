<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Infrastructure\Logging\LoggerFactory;
use Application\Service\ServiceFactory;
use Interface\ExternalModule\Configuration\ExternalModuleConfigProvider;
use Interface\ExternalModule\Project\ExternalModuleProjectRepository;
use Interface\ExternalModule\Controller\Web\ProjectController;

// Get the configuration from the module
$provider = new ExternalModuleConfigProvider($module);

// Create the request and response objects
$request  = Request::createFromGlobals();
$response = new Response();

// Create the logger
$loggerFactory = new LoggerFactory($provider->getLoggingConfig());
$logger = $loggerFactory->createLogger();

// Create the project repository
$projectRepository = new ExternalModuleProjectRepository($logger, $module);

// Initialize the services
$serviceFactory = new ServiceFactory($logger);
$searchService  = $serviceFactory->createSearchEngineService($provider->getDocumentRepositoryConfig(), $provider->getSearchEngineConfig());
$projectService = $serviceFactory->createProjectService($provider->getProjectListConfig(), $projectRepository);
$cartService    = $serviceFactory->createCartService($provider->getCartConfig());

// Create a new controller, wire the services and handle the response
$controller = new ProjectController($logger, $module, $projectService, $searchService, $cartService);
$response = $controller->handle($request, $response);

if ($response instanceof JsonResponse)
{
    $response->prepare($request);
    $response->send();
}
else
{
    $pid = $request->get("pid", 0);
    if ($pid > 0)
    {
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
        echo $response->getContent();
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php'; 
    }
    else
    {
        $page = new HtmlPage();
        $page->PrintHeader();
        echo '<div class="my-3">';
        echo $response->getContent();
        echo '</div>';
        $page->PrintFooter();
    }
}

