<?php

require_once(__DIR__."/app/bootstrap.php");

use REDCap as REDCap;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

$view = $request->get("view", "project");
switch($view) {
    case 'project':
        $controller = new Controllers\ProjectController($module);
        $response = $controller->handle($request, $response);
        break;
    case 'system':
        $controller = new Controllers\SystemController($module);
        $response = $controller->handle($request, $response);
        break;
    default:
        $response->setContent("Unknown view");
}

$chromeless = $request->query->getBoolean("chromeless", false);
$pid        = $request->query->getInt("pid", -1);

if (!$chromeless){
    if ($pid > 0){
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
        echo $response->getContent();
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
    }
    else{
        require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
        echo $response->getContent();
        require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php';
    }
}
else{
    $response->prepare($request);
    $response->send();
}

