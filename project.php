<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Infrastructure\Logging\LoggerFactory;
use Infrastructure\ExternalModule\Logging\ExternalModuleLogHandler;
use Interface\ExternalModule\Controller\Web\ProjectController;

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

$controller = new ProjectController($logger, $module);
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

