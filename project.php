<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

$controller = new Controllers\ProjectController($module);
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
        echo $response->getContent();
        $page->PrintFooter();
    }
}

